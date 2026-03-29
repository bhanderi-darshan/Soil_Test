import sys
import pickle
import numpy as np

# load trained crop model

model = pickle.load(open("crop_model.pkl","rb"))

# input from PHP

N = float(sys.argv[1])
P = float(sys.argv[2])
K = float(sys.argv[3])
soil_type = sys.argv[4]

# simple soil encoding example

soil_map = {
"Black Soil":0,
"Red Soil":1,
"Alluvial Soil":2,
"Sandy Soil":3,
"Laterite Soil":4
}

soil_encoded = soil_map.get(soil_type,0)

# prepare input

input_data = np.array([[N,P,K,soil_encoded]])

# predict crop

prediction = model.predict(input_data)

print(prediction[0])