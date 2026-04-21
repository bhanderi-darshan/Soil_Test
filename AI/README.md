# NPK Soil Nutrient Prediction Model

Predicts **Nitrogen (N)**, **Phosphorus (P)**, and **Potassium (K)** values
from three live sensor readings:

| Sensor input | Unit |
|---|---|
| Moisture | % |
| EC (Electrical Conductivity) | dS/m |
| Soil Temperature | °C |
| Soil Type *(optional)* | Clay / Loamy / Sandy / Peaty / Silty |

---

## File Structure

```
npk_model/
├── train_model.py      ← train models on your dataset
├── predictor.py        ← load models, make predictions
├── api_server.py       ← REST API (Flask)
├── requirements.txt    ← Python dependencies
├── model_N.pkl         ← trained N model  (generated after training)
├── model_P.pkl         ← trained P model  (generated after training)
├── model_K.pkl         ← trained K model  (generated after training)
├── label_encoder.pkl   ← soil type encoder (generated after training)
└── metadata.json       ← metrics + config  (generated after training)
```

---

## Step 1 — Install dependencies

```bash
pip install -r requirements.txt
```

---

## Step 2 — Train on your dataset

Your CSV must have these column names:

```
Moisture, EC, Soil_Temperature, Soil_Type, N, P, K
```

Run training:

```bash
python train_model.py --data your_dataset.csv
```

This saves `model_N.pkl`, `model_P.pkl`, `model_K.pkl`, `label_encoder.pkl`,
and `metadata.json` in the same folder.

---

## Step 3 — Use in Python directly

```python
from predictor import NPKPredictor

model = NPKPredictor()

# single prediction
result = model.predict(
    moisture=37.9,
    ec=1.77,
    temperature=28.9,
    soil_type="Clay"   # optional, default is "Loamy"
)

print(result["N"], result["N_class"])   # e.g. 72.43  High
print(result["P"], result["P_class"])   # e.g. 38.21  Medium
print(result["K"], result["K_class"])   # e.g. 55.10  Medium

# batch prediction (multiple sensors at once)
readings = [
    {"moisture": 37.9, "ec": 1.77, "temperature": 28.9, "soil_type": "Clay"},
    {"moisture": 55.0, "ec": 2.10, "temperature": 31.0},
]
results = model.predict_batch(readings)
```

---

## Step 4 — Run the REST API (for dashboard / IoT integration)

```bash
python api_server.py
# Server starts on http://0.0.0.0:5000
```

### API Endpoints

#### `POST /predict` — single reading

```bash
curl -X POST http://localhost:5000/predict \
     -H "Content-Type: application/json" \
     -d '{
           "moisture": 37.9,
           "ec": 1.77,
           "temperature": 28.9,
           "soil_type": "Clay"
         }'
```

Response:
```json
{
  "N": 72.43,  "N_class": "High",
  "P": 38.21,  "P_class": "Medium",
  "K": 55.10,  "K_class": "Medium",
  "inputs": {
    "moisture": 37.9,
    "ec": 1.77,
    "temperature": 28.9,
    "soil_type": "Clay"
  },
  "timestamp": "2024-01-15T10:30:00Z"
}
```

#### `POST /predict/batch` — multiple readings

```bash
curl -X POST http://localhost:5000/predict/batch \
     -H "Content-Type: application/json" \
     -d '{
           "readings": [
             {"moisture": 37.9, "ec": 1.77, "temperature": 28.9},
             {"moisture": 55.0, "ec": 2.10, "temperature": 31.0, "soil_type": "Sandy"}
           ]
         }'
```

#### `GET /health` — check server status

```bash
curl http://localhost:5000/health
```

#### `GET /model/info` — model metrics

```bash
curl http://localhost:5000/model/info
```

---

## Model Details

| Model | Algorithm | Features used |
|---|---|---|
| N predictor | Random Forest (150 trees) | Moisture, EC, Temperature, Soil Type |
| P predictor | Random Forest (150 trees) | Moisture, EC, Temperature, Soil Type |
| K predictor | Random Forest (150 trees) | Moisture, EC, Temperature, Soil Type |

### NPK Classification thresholds

| Nutrient | Low | Medium | High |
|---|---|---|---|
| N | < 35 | 35 – 65 | > 65 |
| P | < 30 | 30 – 55 | > 55 |
| K | < 28 | 28 – 55 | > 55 |

---

## Connecting from your dashboard / IoT device

From any device that sends sensor data, simply POST to the `/predict` endpoint:

```python
import requests

sensor_data = {
    "moisture": read_moisture_sensor(),
    "ec": read_ec_sensor(),
    "temperature": read_temp_sensor(),
    "soil_type": "Clay"
}

response = requests.post("http://YOUR_SERVER_IP:5000/predict", json=sensor_data)
npk = response.json()

print(f"N={npk['N']} ({npk['N_class']})")
print(f"P={npk['P']} ({npk['P_class']})")
print(f"K={npk['K']} ({npk['K_class']})")
```
