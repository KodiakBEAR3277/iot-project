#pragma once
#include <DHT.h>
#include "config.h"

DHT dht(PIN_DHT, DHT_TYPE);

struct DHTReading {
    float temperature;
    float humidity;
    bool valid;
};

void initSensors() {
    dht.begin();
    delay(2000);  // DHT11 needs 2s after power-up before first read
    pinMode(PIN_PIR, INPUT);
}

DHTReading readDHT() {
    DHTReading r;
    delay(250);  // small delay before read
    r.temperature = dht.readTemperature();
    r.humidity    = dht.readHumidity();
    r.valid       = !isnan(r.temperature) && !isnan(r.humidity);
    if (!r.valid) Serial.println("[DHT] Read failed");
    return r;
}

bool readPIR() {
    return digitalRead(PIN_PIR) == HIGH;
}