"""
generate_dataset.py
-------------------
Generates a synthetic but realistic CSV dataset for freight delay prediction.
800 rows with meaningful correlations so the ML model can learn real patterns.
"""

import numpy as np
import pandas as pd

# Reproducibility
np.random.seed(42)
N = 800

# --- Feature generation ---

distance_km = np.random.uniform(50, 2000, N)

cargo_types = ["electronics", "food", "hazmat", "furniture", "general"]
cargo_type = np.random.choice(cargo_types, N, p=[0.15, 0.25, 0.10, 0.20, 0.30])

weather_conditions = ["clear", "rain", "fog", "storm"]
weather = np.random.choice(weather_conditions, N, p=[0.50, 0.25, 0.15, 0.10])

hour_of_departure = np.random.randint(0, 24, N)  # 0..23
day_of_week = np.random.randint(0, 7, N)          # 0=Mon..6=Sun

vehicle_conditions = ["good", "fair", "poor"]
vehicle_condition = np.random.choice(vehicle_conditions, N, p=[0.55, 0.30, 0.15])

driver_experience_years = np.random.randint(0, 21, N)  # 0..20

# --- Target variable: delay (0 or 1) ---
# Built using a logistic function of features so the model can actually learn.

def logistic(x):
    return 1 / (1 + np.exp(-x))

# Map categoricals to numeric risk weights
weather_risk = {"clear": 0.0, "rain": 0.5, "fog": 0.8, "storm": 1.5}
cargo_risk   = {"general": 0.0, "furniture": 0.1, "food": 0.3, "electronics": 0.5, "hazmat": 1.0}
vehicle_risk = {"good": 0.0, "fair": 0.6, "poor": 1.5}

wr = np.array([weather_risk[w] for w in weather])
cr = np.array([cargo_risk[c] for c in cargo_type])
vr = np.array([vehicle_risk[v] for v in vehicle_condition])

# Night departures (22h-6h) are riskier
night_bonus = np.where((hour_of_departure >= 22) | (hour_of_departure <= 5), 0.5, 0.0)

# Weekend bonus (0-indexed: 5=Sat, 6=Sun)
weekend_bonus = np.where(day_of_week >= 5, 0.3, 0.0)

# Long distance increases risk
dist_risk = (distance_km - 50) / (2000 - 50) * 0.8

# Experienced drivers reduce risk
exp_reduction = -driver_experience_years * 0.06

# Logistic score
score = -1.0 + wr + cr + vr + night_bonus + weekend_bonus + dist_risk + exp_reduction

# Convert to probability and sample delay
prob_delay = logistic(score)
delay = np.random.binomial(1, prob_delay, N)

print(f"Dataset generated: {N} rows — {delay.sum()} delayed ({delay.mean()*100:.1f}%)")

# --- Build DataFrame ---
df = pd.DataFrame({
    "distance_km":             distance_km.round(2),
    "cargo_type":              cargo_type,
    "weather":                 weather,
    "hour_of_departure":       hour_of_departure,
    "day_of_week":             day_of_week,
    "vehicle_condition":       vehicle_condition,
    "driver_experience_years": driver_experience_years,
    "delay":                   delay,
})

df.to_csv("dataset.csv", index=False)
print("Saved → dataset.csv")
