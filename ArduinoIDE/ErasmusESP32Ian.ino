// Includes for all libraries
#include <Wire.h>
#include <Adafruit_Sensor.h>
#include <Adafruit_BME280.h>
#include <Adafruit_GFX.h>
#include <Adafruit_SH110X.h>
#include <WiFi.h>
#include <MQTT.h>
#include <ArduinoJson.h>

// I2C addresses
#define BME280_ADDRESS 0x76
#define SCREEN_ADDRESS 0x3C

// OLED dimensions
#define SCREEN_WIDTH 128
#define SCREEN_HEIGHT 64

// WiFi/MQTT credentials
// SSID and MQTT Server IPs must be changed if conected to a new Network or Broker
const char* ssid = "FBIT.IoT.Router7";
const char* password = "WueLoveIoT";
const char* mqttServer = "192.168.108.14";
const int mqttPort = 1883;
bool sent;

// Daylightoffset now 0 due to Mariehamn
// NTP config for UTC (no offset)
const char* ntpServer = "pool.ntp.org";
const long  gmtOffset_sec = 0;
const int   daylightOffset_sec = 0;

// Sensor data
float tempC;
float humidity;
float pressure;

// Timers
unsigned long lastDisplayUpdate = 0;
unsigned long lastDataSend = 0;
const unsigned long displayInterval = 1000;
const unsigned long dataInterval = 300000;

// Create hardware objects
Adafruit_BME280 bme;
Adafruit_SH1106G display(SCREEN_WIDTH, SCREEN_HEIGHT, &Wire, -1);
WiFiClient net;
MQTTClient client;


// Function connecting the ESP to the Wifi Specified in the constants
void connectWiFi() {
  WiFi.setHostname("ESPDev1");
  WiFi.begin(ssid, password);

  Serial.print("Connecting to WiFi...");
  while (WiFi.status() != WL_CONNECTED) {
    Serial.print(".");
    delay(1000);
  }

  Serial.println("\nWiFi connected.");
}


// Function connecting to the MQTT Broker
void connectMQTT() {
  if (!client.connected()) {
    client.begin(mqttServer, mqttPort, net);
    Serial.print("Connecting to MQTT...");
    while (!client.connect("ArduinoClient")) {
      Serial.print(".");
      delay(1000);
    }
    Serial.println("\nConnected to MQTT broker!");
  }
}

// Time formatting function
String getTime(bool useUtcPlus2) {
  time_t now = time(nullptr);
  struct tm* timeinfo;

  if (useUtcPlus2) {
    now += 3 * 3600;  // Add 2 hours in seconds
  }

  timeinfo = gmtime(&now);  // Use gmtime for UTC

  if (timeinfo == nullptr) {
    Serial.println("Failed to obtain time");
    return "Time unavailable";
  }

  char timeStr[25];
  snprintf(timeStr, sizeof(timeStr), "%04d-%02d-%02d %02d:%02d:%02d",
           timeinfo->tm_year + 1900, timeinfo->tm_mon + 1, timeinfo->tm_mday,
           timeinfo->tm_hour, timeinfo->tm_min, timeinfo->tm_sec);
  return String(timeStr);
}


// JSON Formatter and constructor of the data information used by the db after fetching from broker
bool sendJSON() {
  String ti = getTime(false);

  JsonDocument doc;
  doc["temp"] = String(tempC);
  doc["hum"] = String(humidity);
  doc["time"] = ti;
  doc["pressure"] = String(pressure);
  doc["location"] = "Mariehamn";

  String output;
  serializeJson(doc, output);

  sent = client.publish("Wuerzburg3/KOS/A105/data", output);
  Serial.println("JSON Sent: " + output + " " + String(sent));
  return sent;
}


// Standard Setup function, sets the bme, the OLED, wifi, ntp, starts the first reding and 
void setup() {
  Serial.begin(115200);
  delay(1000);

  Wire.begin();

  if (!bme.begin(BME280_ADDRESS)) {
    Serial.println("Could not find BME280 sensor!");
    while (1);
  }

  if (!display.begin(SCREEN_ADDRESS, true)) {
    Serial.println("SH1106 OLED init failed");
    while (1);
  }

  display.clearDisplay();
  display.setTextSize(1);
  display.setTextColor(SH110X_WHITE);
  display.setCursor(0, 0);
  display.println("BME280 + OLED Ready!");
  display.display();
  delay(1000);

  connectWiFi();
  configTime(gmtOffset_sec, daylightOffset_sec, ntpServer); // Set UTC time

  // Wait for time to be set (max 10 seconds)
  Serial.print("Waiting for NTP time sync");
  time_t now = time(nullptr);
  int retries = 0;
  while (now < 100000 && retries < 10) {
    delay(1000);
    Serial.print(".");
    now = time(nullptr);
    retries++;
  }
  Serial.println();

  if (now < 100000) {
    Serial.println("Failed to sync NTP time.");
  } else {
    Serial.println("NTP time synced successfully.");
  }

  connectMQTT();

  // Read sensor data and send initial JSON
  tempC = bme.readTemperature();
  humidity = bme.readHumidity();
  pressure = bme.readPressure() / 100.0F;
  sendJSON();
  lastDataSend = millis();  // Reset timer so next send happens in 5 minutes
}


// the loooooooooop
void loop() {
  unsigned long currentMillis = millis();

  // Update OLED display
  if (currentMillis - lastDisplayUpdate >= displayInterval) {
    lastDisplayUpdate = currentMillis;
    
    String ti = getTime(true);
    tempC = bme.readTemperature();
    humidity = bme.readHumidity();
    pressure = bme.readPressure() / 100.0F;

    display.clearDisplay();
    display.setCursor(0, 0);
    display.println("BME280 Sensor Data:");

    display.print("Temp: ");
    display.print(tempC, 1);
    display.println(" C");

    display.print("Humidity: ");
    display.print(humidity, 1);
    display.println(" %");

    display.print("Pressure: ");
    display.print(pressure, 1);
    display.println(" hPa");

    display.print(ti);

    display.display();
  }

  // Send JSON data via MQTT
  if (currentMillis - lastDataSend >= dataInterval) {
    lastDataSend = currentMillis;
    connectMQTT();  // Reconnect if needed
    sendJSON();
  }

  client.loop(); // Maintain MQTT connection
}
