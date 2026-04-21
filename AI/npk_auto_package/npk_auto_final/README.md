# NPK Auto-Trainer & Predictor

This package provides an automated pipeline for training and using soil nutrient (NPK) prediction models.

## Installation

To enable all features and the most powerful training strategy (XGBoost), install the following dependencies:

```bash
pip install pandas numpy scikit-learn joblib xgboost
```

## How to Train

Run the auto-trainer to find the best models for your dataset. The script will automatically clean outliers and use feature engineering (pH, Moisture, EC, Temperature).

```bash
python auto_train.py --data Soil.csv
```

## How to Predict

Use the `NPKPredictor` class in your Python application:

```python
from predictor import NPKPredictor

# Initialize predictor
m = NPKPredictor()

# Make a prediction (requires pH, Moisture, EC, Temperature, Soil_Type)
results = m.predict(
    moisture=37.9, 
    ph=6.65, 
    ec=1.77, 
    temperature=28.9, 
    soil_type="Clay"
)

print(results)
```

## Advanced Configuration

You can tune the accuracy target and strategies in the `CONFIG` block at the top of `auto_train.py`. The trainer will continue to loop through different algorithms until the target R2 is reached or the maximum number of loops is exceeded.
