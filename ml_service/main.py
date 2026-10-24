"""
main.py — FastAPI ML Microservice
----------------------------------
Exposes the trained delay prediction model via a REST API.

Usage:
    uvicorn main:app --reload --port 8001

Endpoints:
    GET  /health              → liveness check
    GET  /feature-importance  → feature importances (Random Forest only)
    POST /predict             → delay probability for a given delivery
"""

import os
import joblib
import numpy as np
import pandas as pd
from fastapi import FastAPI, HTTPException
from fastapi.middleware.cors import CORSMiddleware
from pydantic import BaseModel, Field

# ── App setup ──────────────────────────────────────────────────────────────────
app = FastAPI(
    title="TransitHub ML — Delay Prediction API",
    description=(
        "Predicts the probability of freight delivery delay "
        "using a scikit-learn model trained with class_weight='balanced'."
    ),
    version="2.0.0",
)

app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],   # lock down in production
    allow_methods=["*"],
    allow_headers=["*"],
)

# ── Load model once at startup ────────────────────────────────────────────────
BASE_DIR   = os.path.dirname(__file__)
MODEL_PATH = os.path.join(BASE_DIR, "delay_model.pkl")
META_PATH  = os.path.join(BASE_DIR, "model_meta.pkl")

if not os.path.exists(MODEL_PATH):
    raise RuntimeError(
        "delay_model.pkl not found. Run `python train.py` first."
    )

model = joblib.load(MODEL_PATH)
meta  = joblib.load(META_PATH) if os.path.exists(META_PATH) else {}
best_model_name = meta.get("best_model_name", "Unknown")
print(f"[INFO] Model loaded: {best_model_name}")

# ── Valid categorical values ───────────────────────────────────────────────────
VALID_CARGO   = {"electronics", "food", "hazmat", "furniture", "general"}
VALID_WEATHER = {"clear", "rain", "fog", "storm"}
VALID_VEHICLE = {"good", "fair", "poor"}

FEATURE_NAMES = [
    "distance_km",
    "hour_of_departure",
    "day_of_week",
    "driver_experience_years",
    # OneHotEncoded — cargo_type
    "cargo_type_electronics",
    "cargo_type_food",
    "cargo_type_furniture",
    "cargo_type_general",
    "cargo_type_hazmat",
    # OneHotEncoded — weather
    "weather_clear",
    "weather_fog",
    "weather_rain",
    "weather_storm",
    # OneHotEncoded — vehicle_condition
    "vehicle_condition_fair",
    "vehicle_condition_good",
    "vehicle_condition_poor",
]

# ── Request / Response schemas ────────────────────────────────────────────────
class DeliveryInput(BaseModel):
    distance_km: float = Field(..., ge=1, le=10000, example=450.0,
                                description="Total route distance in km")
    cargo_type: str = Field(..., example="electronics",
                             description="One of: electronics, food, hazmat, furniture, general")
    weather: str = Field(..., example="rain",
                          description="One of: clear, rain, fog, storm")
    hour_of_departure: int = Field(..., ge=0, le=23, example=14,
                                    description="Hour of departure (0–23)")
    day_of_week: int = Field(..., ge=0, le=6, example=2,
                              description="Day of week: 0=Monday … 6=Sunday")
    vehicle_condition: str = Field(..., example="fair",
                                    description="One of: good, fair, poor")
    driver_experience_years: int = Field(..., ge=0, le=50, example=5,
                                          description="Years of experience of the driver")


class PredictionOutput(BaseModel):
    delay_probability: float = Field(..., description="Probability of delay (0.0 – 1.0)")
    delay_predicted: bool    = Field(..., description="True if delay is likely (threshold ≥ 0.5)")
    model_used: str          = Field(..., description="Name of the best trained classifier")
    model_version: str       = Field(default="2.0.0")


# ── Helpers ───────────────────────────────────────────────────────────────────
def _validate_categoricals(payload: DeliveryInput):
    if payload.cargo_type not in VALID_CARGO:
        raise HTTPException(422, f"Invalid cargo_type. Valid: {sorted(VALID_CARGO)}")
    if payload.weather not in VALID_WEATHER:
        raise HTTPException(422, f"Invalid weather. Valid: {sorted(VALID_WEATHER)}")
    if payload.vehicle_condition not in VALID_VEHICLE:
        raise HTTPException(422, f"Invalid vehicle_condition. Valid: {sorted(VALID_VEHICLE)}")


# ── Endpoints ─────────────────────────────────────────────────────────────────

@app.get("/health", tags=["System"])
def health():
    """Liveness check — returns 200 OK when the service is up."""
    return {
        "status": "ok",
        "model_loaded": model is not None,
        "model_used": best_model_name,
    }


@app.post("/predict", response_model=PredictionOutput, tags=["Prediction"])
def predict(payload: DeliveryInput):
    """
    Predict delay probability for a given delivery.

    Returns a float probability (0–1) and a boolean decision at threshold 0.5.
    """
    _validate_categoricals(payload)

    input_df = pd.DataFrame([{
        "distance_km":             payload.distance_km,
        "cargo_type":              payload.cargo_type,
        "weather":                 payload.weather,
        "hour_of_departure":       payload.hour_of_departure,
        "day_of_week":             payload.day_of_week,
        "vehicle_condition":       payload.vehicle_condition,
        "driver_experience_years": payload.driver_experience_years,
    }])

    prob = float(model.predict_proba(input_df)[0][1])

    return PredictionOutput(
        delay_probability=round(prob, 4),
        delay_predicted=prob >= 0.5,
        model_used=best_model_name,
    )


@app.get("/feature-importance", tags=["Explainability"])
def feature_importance():
    """
    Return the feature importances of the trained model.

    - For Random Forest: uses built-in `feature_importances_` (Gini impurity).
    - For Logistic Regression: uses absolute values of coefficients as a proxy.

    Sorted descending by importance — useful to explain WHY the model predicts delays.
    """
    try:
        clf = model.named_steps["clf"]
        pre = model.named_steps["pre"]

        # Get feature names after the ColumnTransformer
        num_names = pre.transformers_[0][2]  # numeric feature names
        cat_enc   = pre.transformers_[1][1]  # OneHotEncoder
        cat_names = list(cat_enc.get_feature_names_out(pre.transformers_[1][2]))
        all_names = list(num_names) + cat_names

        # Extract importances
        if hasattr(clf, "feature_importances_"):
            # Random Forest
            importances = clf.feature_importances_.tolist()
            method = "gini_impurity_reduction"
        elif hasattr(clf, "coef_"):
            # Logistic Regression — use abs(coef) as proxy
            importances = np.abs(clf.coef_[0]).tolist()
            method = "absolute_coefficient"
        else:
            raise HTTPException(500, "Model does not expose feature importances.")

        # Build sorted list
        ranked = sorted(
            zip(all_names, importances),
            key=lambda x: x[1],
            reverse=True,
        )

        return {
            "model_used": best_model_name,
            "method": method,
            "feature_importances": [
                {"feature": name, "importance": round(imp, 6)}
                for name, imp in ranked
            ],
        }
    except Exception as e:
        raise HTTPException(500, f"Could not compute feature importances: {str(e)}")
