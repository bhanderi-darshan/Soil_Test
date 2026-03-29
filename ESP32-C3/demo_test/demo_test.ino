#include <WiFi.h>

const char* ssid = "Bhanderi";
const char* password = "12112005";

float soilMoisture = 45.2;
float ecValue = 1.3;
float temperature = 29.5;

void setup() {

  Serial.begin(115200);

  WiFi.begin(ssid, password);

  Serial.print("Connecting WiFi");

  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }

  Serial.println("Connected");

}

void loop() {

  Serial.println("Dummy Soil Data");

  Serial.print("Moisture: ");
  Serial.println(soilMoisture);

  Serial.print("EC: ");
  Serial.println(ecValue);

  Serial.print("Temperature: ");
  Serial.println(temperature);

  delay(5000);

}