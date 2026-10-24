import joblib
import pandas as pd
import matplotlib.pyplot as plt
import seaborn as sns
from sklearn.metrics import confusion_matrix
from sklearn.model_selection import train_test_split
from sklearn.linear_model import LogisticRegression
from sklearn.ensemble import RandomForestClassifier

# ── 1. Charger le dataset ─────────────────────────────────────────
df = pd.read_csv('dataset.csv')  # adapte le nom
X = df.drop('delay', axis=1)
y = df['delay']
X = pd.get_dummies(X)

# ── 2. Split ──────────────────────────────────────────────────────
X_train, X_test, y_train, y_test = train_test_split(
    X, y, test_size=0.2, random_state=42, stratify=y
)

# ── 3. Entraîner les deux modèles ─────────────────────────────────
lr = LogisticRegression(class_weight='balanced', max_iter=1000)
rf = RandomForestClassifier(class_weight='balanced', n_estimators=100, random_state=42)

lr.fit(X_train, y_train)
rf.fit(X_train, y_train)

# ── 4. Prédictions ────────────────────────────────────────────────
y_pred_lr = lr.predict(X_test)
y_pred_rf = rf.predict(X_test)

# ── 5. Matrices de confusion ──────────────────────────────────────
cm_lr = confusion_matrix(y_test, y_pred_lr)
cm_rf = confusion_matrix(y_test, y_pred_rf)

print("📊 Régression Logistique :")
print(cm_lr)
print("\n📊 Random Forest :")
print(cm_rf)

# ── 6. Figure côte à côte ─────────────────────────────────────────
fig, axes = plt.subplots(1, 2, figsize=(14, 6))

labels = ['On Time', 'Delayed']

# Matrice Régression Logistique
sns.heatmap(
    cm_lr, annot=True, fmt='d', cmap='Blues',
    xticklabels=labels, yticklabels=labels,
    linewidths=2, linecolor='white',
    annot_kws={"size": 18, "weight": "bold"},
    ax=axes[0]
)
axes[0].set_title('Régression Logistique\n✅ Modèle Retenu',
                   fontsize=13, fontweight='bold', pad=15, color='#1B3A6B')
axes[0].set_ylabel('Valeur Réelle', fontsize=11)
axes[0].set_xlabel('Valeur Prédite', fontsize=11)

# Matrice Random Forest
sns.heatmap(
    cm_rf, annot=True, fmt='d', cmap='Oranges',
    xticklabels=labels, yticklabels=labels,
    linewidths=2, linecolor='white',
    annot_kws={"size": 18, "weight": "bold"},
    ax=axes[1]
)
axes[1].set_title('Random Forest\n❌ Non Retenu',
                   fontsize=13, fontweight='bold', pad=15, color='#8B3A00')
axes[1].set_ylabel('Valeur Réelle', fontsize=11)
axes[1].set_xlabel('Valeur Prédite', fontsize=11)

plt.suptitle(
    'Comparaison des Matrices de Confusion — RAGHD TMS',
    fontsize=15, fontweight='bold', y=1.02
)

plt.tight_layout()
plt.savefig('confusion_matrix_comparaison.png', dpi=300, bbox_inches='tight')
print("\n✅ Image sauvegardée : confusion_matrix_comparaison.png")