"""
train.py
--------
Trains Logistic Regression and Random Forest classifiers on the freight delay
dataset, compares metrics, saves the best model as delay_model.pkl, and exports
the ROC curve as roc_curve.png.

Improvements (v2):
  - class_weight='balanced'  → handles ~30% class imbalance correctly
  - 5-fold cross-validation  → proves generalisation beyond one train/test split

Usage:
    python generate_dataset.py   # (once, creates dataset.csv)
    python train.py
"""

import os
import joblib
import numpy as np
import pandas as pd
import matplotlib
matplotlib.use("Agg")   # Non-interactive backend — safe in terminal / headless
import matplotlib.pyplot as plt

from sklearn.model_selection import train_test_split, cross_val_score, StratifiedKFold
from sklearn.pipeline import Pipeline
from sklearn.compose import ColumnTransformer
from sklearn.preprocessing import StandardScaler, OneHotEncoder
from sklearn.linear_model import LogisticRegression
from sklearn.ensemble import RandomForestClassifier
from sklearn.metrics import (
    accuracy_score, precision_score, recall_score, f1_score,
    roc_auc_score, roc_curve, classification_report,
)

# ── 0. Load dataset ────────────────────────────────────────────────────────────
if not os.path.exists("dataset.csv"):
    print("[INFO] dataset.csv not found — generating it now...")
    import generate_dataset  # noqa: F401  (runs the script as a side-effect)

df = pd.read_csv("dataset.csv")
print(f"[INFO] Loaded {len(df)} rows | Delay rate: {df['delay'].mean()*100:.1f}%\n")

X = df.drop(columns=["delay"])
y = df["delay"]

# ── 1. Preprocessing pipeline ─────────────────────────────────────────────────
numeric_features     = ["distance_km", "hour_of_departure", "day_of_week", "driver_experience_years"]
categorical_features = ["cargo_type", "weather", "vehicle_condition"]

preprocessor = ColumnTransformer(transformers=[
    ("num", StandardScaler(), numeric_features),
    ("cat", OneHotEncoder(handle_unknown="ignore"), categorical_features),
])

# ── 2. Train / test split ─────────────────────────────────────────────────────
X_train, X_test, y_train, y_test = train_test_split(
    X, y, test_size=0.20, random_state=42, stratify=y
)

# ── 3. Model definitions  (class_weight='balanced' for imbalanced classes) ────
models = {
    "Logistic Regression": Pipeline([
        ("pre", preprocessor),
        ("clf", LogisticRegression(
            max_iter=1000,
            random_state=42,
            class_weight="balanced",   # ← handles ~30% minority class
        )),
    ]),
    "Random Forest": Pipeline([
        ("pre", preprocessor),
        ("clf", RandomForestClassifier(
            n_estimators=200,
            random_state=42,
            n_jobs=-1,
            class_weight="balanced",   # ← handles ~30% minority class
        )),
    ]),
}

# ── 4. 5-fold Cross-validation (generalisation check) ─────────────────────────
cv = StratifiedKFold(n_splits=5, shuffle=True, random_state=42)

print("=" * 68)
print("Cross-Validation Results (5-Fold Stratified, scoring=F1)")
print("=" * 68)
for name, pipe in models.items():
    cv_scores = cross_val_score(pipe, X, y, cv=cv, scoring="f1", n_jobs=-1)
    print(f"  {name:<22}  mean={cv_scores.mean():.3f}  std=±{cv_scores.std():.3f}  folds={np.round(cv_scores, 3)}")
print()

# ── 5. Final train on full train split, evaluate on held-out test ──────────────
results = {}
print("=" * 68)
print(f"{'Model':<22} {'Acc':>6} {'Prec':>6} {'Rec':>6} {'F1':>6} {'AUC':>6}")
print("=" * 68)

for name, pipe in models.items():
    pipe.fit(X_train, y_train)
    y_pred = pipe.predict(X_test)
    y_prob = pipe.predict_proba(X_test)[:, 1]

    acc  = accuracy_score(y_test, y_pred)
    prec = precision_score(y_test, y_pred, zero_division=0)
    rec  = recall_score(y_test, y_pred, zero_division=0)
    f1   = f1_score(y_test, y_pred, zero_division=0)
    auc  = roc_auc_score(y_test, y_prob)

    results[name] = {"pipe": pipe, "prob": y_prob, "auc": auc, "f1": f1}
    print(f"{name:<22} {acc:>6.3f} {prec:>6.3f} {rec:>6.3f} {f1:>6.3f} {auc:>6.3f}")

print("=" * 68)

# ── 6. Detailed classification report ─────────────────────────────────────────
for name, res in results.items():
    print(f"\n── {name} (Test Set) ──")
    print(classification_report(y_test, res["pipe"].predict(X_test),
                                target_names=["On Time", "Delayed"]))

# ── 7. Pick the best model (by F1 on test set) ────────────────────────────────
best_name = max(results, key=lambda k: results[k]["f1"])
best_pipe = results[best_name]["pipe"]
print(f"\n✅ Best model: {best_name} (F1={results[best_name]['f1']:.3f})\n")

# ── 8. Save model & metadata ──────────────────────────────────────────────────
joblib.dump(best_pipe, "delay_model.pkl")
# Save which model was selected (used by FastAPI /feature-importance endpoint)
joblib.dump({"best_model_name": best_name}, "model_meta.pkl")
print("Saved → delay_model.pkl")
print("Saved → model_meta.pkl")

# ── 9. ROC curve comparison ────────────────────────────────────────────────────
fig, ax = plt.subplots(figsize=(8, 6))
colors = {"Logistic Regression": "#6366F1", "Random Forest": "#10B981"}

for name, res in results.items():
    fpr, tpr, _ = roc_curve(y_test, res["prob"])
    ax.plot(fpr, tpr, label=f"{name} (AUC={res['auc']:.3f})", color=colors[name], lw=2)

ax.plot([0, 1], [0, 1], "k--", lw=1, alpha=0.5, label="Random Baseline")
ax.fill_between([0, 1], [0, 1], alpha=0.04, color="gray")
ax.set_xlabel("False Positive Rate", fontsize=13)
ax.set_ylabel("True Positive Rate", fontsize=13)
ax.set_title("ROC Curve — Freight Delay Prediction (class_weight=balanced)", fontsize=13, fontweight="bold")
ax.legend(fontsize=11)
ax.grid(alpha=0.3)
fig.tight_layout()
fig.savefig("roc_curve.png", dpi=150)
print("Saved → roc_curve.png")
plt.show()
