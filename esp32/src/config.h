#pragma once

// --- WiFi ---
#define WIFI_SSID     "your_wifi"
#define WIFI_PASSWORD "wifipass"

// --- Backend ---
#define SERVER_IP     "192.168.1.10"   // your machine's local IP
#define SERVER_PORT   8000
#define API_BASE      "http://192.168.1.10:8000/api"

// --- Pin definitions ---
#define PIN_DHT       4     // DHT11 data pin
#define PIN_PIR       13    // PIR signal pin
#define PIN_BUZZER    25    // Buzzer pin
#define OLED_SDA      21    // I2C SDA (default on ESP32)
#define OLED_SCL      22    // I2C SCL (default on ESP32)

// --- DHT ---
#define DHT_TYPE      DHT11

// --- OLED ---
#define OLED_WIDTH    128
#define OLED_HEIGHT   64
#define OLED_ADDRESS  0x3C

// --- Timing (milliseconds) ---
#define INTERVAL_SENSOR_POST   2000   // how often to POST sensor data
#define INTERVAL_ACTUATOR_POLL 2000   // how often to poll for commands