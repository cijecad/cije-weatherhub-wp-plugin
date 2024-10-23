#include <WiFi.h>
#include <HTTPClient.h>
#include <DHT.h>
#include <Adafruit_BME280.h>
#include "esp_sleep.h"

// Replace with your registered station_id and passkey
const int station_id = 0; // Use your assigned station ID number
const char* passkey = "station_passkey";  // Replace with your actual passkey

// Replace with your network credentials
const char* ssid = "local_ssid";
const char* password = "ssid_password";

// Server URL
const char* serverName = "https://www.cijeweatherhub.site/wp-content/plugins/cije-weather-hub-wp-plugin/core/includes/classes/post-weather-data.php"; // Enter ServerName here

// Sensor Setup
// #define USE_DHT // Comment this out if using BME280
#ifdef USE_DHT
  #define DHTPIN 17
  #define DHTTYPE DHT22
  DHT dht(DHTPIN, DHTTYPE);
#else
  #define BME280_I2C_ADDR 0x76
  Adafruit_BME280 bme;
#endif

// Placeholder for wind speed (from anemometer)
float wind_speed = 0.0;

// Placeholder for rain sensor value
int rain_sensor_pin = 34; // Assuming the rain sensor is connected to analog pin 34
float rain_inches = 0.0;

// Variable for sleep interval (in seconds)
const uint64_t sleep_interval_seconds = 6 * 3600; // 6 hours in seconds
const uint64_t sleep_interval_us = sleep_interval_seconds * 1000000ULL;

void setup() {
  Serial.begin(115200);

  // Connect to Wi-Fi
  WiFi.begin(ssid, password);
  while (WiFi.status() != WL_CONNECTED) {
    delay(1000);
    Serial.println("Connecting to WiFi...");
  }
  Serial.println("Connected to WiFi");

  // Initialize sensors
  #ifdef USE_DHT
    dht.begin();
  #else
    if (!bme.begin(BME280_I2C_ADDR)) {
      Serial.println("Could not find a valid BME280 sensor, check wiring!");
      while (1);
    }
  #endif
}

void loop() {
  // Read sensor data
  float temperature, humidity, pressure;
  #ifdef USE_DHT
    temperature = dht.readTemperature();
    humidity = dht.readHumidity();
    pressure = 0.0; // DHT does not provide pressure
  #else
    temperature = bme.readTemperature();
    humidity = bme.readHumidity();
    pressure = bme.readPressure() / 100.0F; // Convert Pa to hPa
  #endif

  // Read rain sensor value
  int rain_sensor_value = analogRead(rain_sensor_pin);
  rain_inches = (rain_sensor_value / 1024.0) * 1.57; // Convert analog value to inches

  // Print sensor data
  Serial.print("Temperature: ");
  Serial.print(temperature);
  Serial.print(" °C, Humidity: ");
  Serial.print(humidity);
  Serial.print(" %, Pressure: ");
  Serial.print(pressure);
  Serial.print(" hPa, Wind Speed: ");
  Serial.print(wind_speed);
  Serial.print(" m/s, Rain: ");
  Serial.print(rain_inches);
  Serial.println(" inches");

  // Send data to server
  if (WiFi.status() == WL_CONNECTED) {
    HTTPClient http;
    http.begin(serverName);
    http.addHeader("Content-Type", "application/x-www-form-urlencoded");

    String httpRequestData = "station_id=" + String(station_id)
                           + "&passkey=" + String(passkey)
                           + "&temperature=" + String(temperature)
                           + "&humidity=" + String(humidity)
                           + "&pressure=" + String(pressure)
                           + "&wind_speed=" + String(wind_speed)
                           + "&rain_inches=" + String(rain_inches);

    int httpResponseCode = http.POST(httpRequestData);

    if (httpResponseCode > 0) {
      String response = http.getString();
      Serial.println(httpResponseCode);
      Serial.println(response);
    } else {
      Serial.print("Error on sending POST: ");
      Serial.println(httpResponseCode);
    }

    http.end();
  } else {
    Serial.println("WiFi Disconnected");
  }

  // Sleep
  esp_sleep_enable_timer_wakeup(sleep_interval_us);
  esp_deep_sleep_start();
} 