// #include <WiFi.h>
// #include <HTTPClient.h>
// #include <WiFiManager.h>
// #include <ModbusMaster.h>

// #define RXD2 3
// #define TXD2 4
// #define BUTTON_PIN 2

// HardwareSerial RS485Serial(1);
// ModbusMaster node;
// WiFiManager wm;

// String serverName = "http://10.239.107.1/Product/receive.php";

// float tempSamples[15];
// float moistureSamples[15];
// float ecSamples[15];

// int locationIndex = 0;

// void setup()
// {
//   Serial.begin(115200);

//   pinMode(BUTTON_PIN, INPUT_PULLUP);

//   RS485Serial.begin(4800, SERIAL_8N1, RXD2, TXD2);
//   node.begin(1, RS485Serial);

//   Serial.println("Starting WiFi Manager...");

//   bool res = wm.autoConnect("SoilSensor_Setup");

//   if (!res)
//   {
//     Serial.println("WiFi Failed");
//     ESP.restart();
//   }

//   Serial.println("WiFi Connected!");
// }

// void loop()
// {
//   if (digitalRead(BUTTON_PIN) == LOW)
//   {
//     Serial.print("Sampling location: ");
//     Serial.println(locationIndex + 1);

//     delay(1000);

//     float temp_sum = 0;
//     float moisture_sum = 0;
//     float ec_sum = 0;

//     int sampleCount = 0;

//     unsigned long startTime = millis();

//     while (millis() - startTime < 3000)
//     {
//       uint8_t result = node.readHoldingRegisters(0x0000, 3);

//       if (result == node.ku8MBSuccess)
//       {
//         float temperature = node.getResponseBuffer(0) / 10.0;
//         float moisture = node.getResponseBuffer(1) / 10.0;
//         float ec = node.getResponseBuffer(2);

//         temp_sum += temperature;
//         moisture_sum += moisture;
//         ec_sum += ec;

//         sampleCount++;
//       }

//       delay(200);
//     }

//     if (sampleCount > 0)
//     {
//       tempSamples[locationIndex] = temp_sum / sampleCount;
//       moistureSamples[locationIndex] = moisture_sum / sampleCount;
//       ecSamples[locationIndex] = ec_sum / sampleCount;

//       Serial.println("Location sample stored");

//       locationIndex++;
//     }

//     delay(2000);

//     if (locationIndex == 15)
//     {
//       calculateFinalAverage();
//       locationIndex = 0;
//     }
//   }
// }

// void calculateFinalAverage()
// {
//   float tempTotal = 0;
//   float moistureTotal = 0;
//   float ecTotal = 0;

//   for (int i = 0; i < 15; i++)
//   {
//     tempTotal += tempSamples[i];
//     moistureTotal += moistureSamples[i];
//     ecTotal += ecSamples[i];
//   }

//   float finalTemp = tempTotal / 15;
//   float finalMoisture = moistureTotal / 15;
//   float finalEC = ecTotal / 15;

//   Serial.println("Final Average Ready");

//   sendToServer(finalTemp, finalMoisture, finalEC);
// }

// void sendToServer(float temp, float moisture, float ec)
// {
//   if (WiFi.status() != WL_CONNECTED)
//   {
//     Serial.println("WiFi reconnecting...");
//     WiFi.reconnect();
//     delay(2000);
//   }

//   HTTPClient http;

//   String url = serverName +
//                "?device=SOIL001" +
//                "&moisture=" + String(moisture) +
//                "&ec=" + String(ec) +
//                "&temp=" + String(temp);

//   Serial.println("Sending to:");
//   Serial.println(url);

//   http.begin(url);
//   http.setTimeout(10000);   // important fix

//   int httpResponseCode = http.GET();

//   if (httpResponseCode > 0)
//   {
//     String response = http.getString();
//     Serial.print("Server Response: ");
//     Serial.println(response);
//   }
//   else
//   {
//     Serial.print("HTTP Error: ");
//     Serial.println(httpResponseCode);
//   }

//   http.end();
// }










#include <WiFi.h>
#include <HTTPClient.h>
#include <WiFiManager.h>
#include <ModbusMaster.h>

// RS485 pins
#define RXD2 3
#define TXD2 4

// Button and LED pins
#define BUTTON_PIN 2
#define LED_PIN 8

HardwareSerial RS485Serial(1);
ModbusMaster node;
WiFiManager wm;

// Server URL
String serverName = "http://10.239.107.1/Product/receive.php";

// Storage arrays for 15 locations
float tempSamples[15];
float moistureSamples[15];
float ecSamples[15];

int locationIndex = 0;


void setup()
{
  Serial.begin(115200);

  pinMode(BUTTON_PIN, INPUT_PULLUP);
  pinMode(LED_PIN, OUTPUT);
  digitalWrite(LED_PIN, LOW);

  // Start RS485
  RS485Serial.begin(4800, SERIAL_8N1, RXD2, TXD2);
  node.begin(1, RS485Serial);

  Serial.println("Starting WiFi Manager...");

  bool res = wm.autoConnect("SoilSensor_Setup");

  if (!res)
  {
    Serial.println("WiFi Failed");
    ESP.restart();
  }

  Serial.println("WiFi Connected!");
  Serial.print("IP Address: ");
  Serial.println(WiFi.localIP());
}


void loop()
{
  if (digitalRead(BUTTON_PIN) == LOW)
  {
    Serial.print("Sampling location: ");
    Serial.println(locationIndex + 1);

    digitalWrite(LED_PIN, HIGH);   // LED ON during sampling

    delay(1000);   // stabilization delay

    float temp_sum = 0;
    float moisture_sum = 0;
    float ec_sum = 0;

    int sampleCount = 0;

    unsigned long startTime = millis();

    // 3-second sampling window
    while (millis() - startTime < 3000)
    {
      uint8_t result = node.readHoldingRegisters(0x0000, 3);

      if (result == node.ku8MBSuccess)
      {
        float temperature = node.getResponseBuffer(0) / 10.0;
        float moisture = node.getResponseBuffer(1) / 10.0;
        float ec = node.getResponseBuffer(2);

        temp_sum += temperature;
        moisture_sum += moisture;
        ec_sum += ec;

        sampleCount++;
      }

      delay(200);
    }

    digitalWrite(LED_PIN, LOW);   // LED OFF after sampling

    if (sampleCount > 0)
    {
      tempSamples[locationIndex] = temp_sum / sampleCount;
      moistureSamples[locationIndex] = moisture_sum / sampleCount;
      ecSamples[locationIndex] = ec_sum / sampleCount;

      Serial.println("Location sample stored");

      locationIndex++;
    }

    delay(2000);   // debounce delay


    // After collecting 15 locations
    if (locationIndex == 15)
    {
      calculateFinalAverage();

      locationIndex = 0;
    }
  }
}


void calculateFinalAverage()
{
  float tempTotal = 0;
  float moistureTotal = 0;
  float ecTotal = 0;

  for (int i = 0; i < 15; i++)
  {
    tempTotal += tempSamples[i];
    moistureTotal += moistureSamples[i];
    ecTotal += ecSamples[i];
  }

  float finalTemp = tempTotal / 15;
  float finalMoisture = moistureTotal / 15;
  float finalEC = ecTotal / 15;

  Serial.println("Final Average Ready");

  sendToServer(finalTemp, finalMoisture, finalEC);
}


void sendToServer(float temp, float moisture, float ec)
{
  if (WiFi.status() != WL_CONNECTED)
  {
    Serial.println("WiFi reconnecting...");
    WiFi.reconnect();
    delay(2000);
  }

  HTTPClient http;

  String url = serverName +
               "?device=SOIL001" +
               "&moisture=" + String(moisture) +
               "&ec=" + String(ec) +
               "&temp=" + String(temp);

  Serial.println("Sending to:");
  Serial.println(url);

  http.begin(url);
  http.setTimeout(10000);

  int httpResponseCode = http.GET();

  if (httpResponseCode > 0)
  {
    String response = http.getString();

    Serial.print("Server Response: ");
    Serial.println(response);

    // Blink LED 3 times after success
    for(int i=0;i<3;i++)
    {
      digitalWrite(LED_PIN, HIGH);
      delay(200);
      digitalWrite(LED_PIN, LOW);
      delay(200);
    }
  }
  else
  {
    Serial.print("HTTP Error: ");
    Serial.println(httpResponseCode);
  }

  http.end();
}
