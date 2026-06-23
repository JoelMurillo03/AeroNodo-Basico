#include <WiFi.h>
#include <HTTPClient.h>
#include <ArduinoJson.h>
#include <Wire.h>
#include <Adafruit_Sensor.h>
#include <Adafruit_BME280.h>

// ===== Credenciales WiFi =====
const char* ssid = "TU_SSID";
const char* password = "TU_PASSWORD";

// ===== URL de la API (reemplazar IP) =====
const char* serverUrl = "http://192.168.1.100:8080/api/sensores";

// ===== Pines =====
#define BME_SCL 22
#define BME_SDA 21
#define MQ135_AO 35      // Salida analógica del MQ-135
#define LED_R 25
#define LED_G 26
#define LED_B 27

// ===== Objetos =====
Adafruit_BME280 bme;

// ===== Variables =====
unsigned long lastSend = 0;
const unsigned long sendInterval = 30000; // 30 segundos

// ===== Configuración =====
void setup() {
    Serial.begin(115200);

    // Inicializar BME280
    Wire.begin(BME_SDA, BME_SCL);
    if (!bme.begin(0x76)) {
        Serial.println("Error: BME280 no encontrado");
        while (1);
    }

    // Configurar LED RGB
    pinMode(LED_R, OUTPUT);
    pinMode(LED_G, OUTPUT);
    pinMode(LED_B, OUTPUT);
    apagarLEDs();

    // Conectar WiFi
    WiFi.begin(ssid, password);
    Serial.print("Conectando a WiFi");
    while (WiFi.status() != WL_CONNECTED) {
        delay(500);
        Serial.print(".");
    }
    Serial.println("\nWiFi conectado. IP: " + WiFi.localIP().toString());

    // Enviar estado inicial al servidor
    actualizarEstadoDispositivo(true, WiFi.RSSI());
}

// ===== Loop principal =====
void loop() {
    if (millis() - lastSend >= sendInterval) {
        lastSend = millis();

        // Verificar WiFi
        if (WiFi.status() != WL_CONNECTED) {
            Serial.println("WiFi desconectado. Reconectando...");
            WiFi.reconnect();
            delay(1000);
            actualizarEstadoDispositivo(false, 0);
            return;
        }

        // Leer sensores
        float temp = bme.readTemperature();
        float hum = bme.readHumidity();
        float pres = bme.readPressure() / 100.0F;
        int calidad = analogRead(MQ135_AO); // valor 0-4095, aproximado a ppm
        // Escala simple para simular ppm (ajustar según calibración)
        calidad = map(calidad, 0, 4095, 0, 1000);

        // Calcular índice (ejemplo)
        float indice = temp * 0.4 + hum * 0.3 + (calidad / 1000.0) * 10;
        indice = constrain(indice, 0, 100);

        // Construir JSON
        StaticJsonDocument<256> doc;
        doc["temperatura"] = temp;
        doc["humedad"] = hum;
        doc["presion"] = pres;
        doc["calidad_aire"] = calidad;
        doc["estado_wifi"] = true;
        doc["indice"] = indice;

        String jsonData;
        serializeJson(doc, jsonData);

        // Enviar POST
        HTTPClient http;
        http.begin(serverUrl);
        http.addHeader("Content-Type", "application/json");
        int httpCode = http.POST(jsonData);

        // Control de LEDs según respuesta
        if (httpCode == 201) {
            Serial.println("Datos enviados correctamente");
            // LED Verde (conexión API ok)
            setColor(0, 255, 0);
        } else {
            Serial.printf("Error HTTP: %d\n", httpCode);
            // LED Rojo
            setColor(255, 0, 0);
        }
        http.end();

        // LED de calidad del aire (se actualiza en la siguiente iteración)
        // Se controla de forma independiente con la variable 'calidad'
        actualizarLEDCalidad(calidad);

        // Actualizar estado del dispositivo en el servidor
        actualizarEstadoDispositivo(true, WiFi.RSSI());
    }
    delay(100);
}

// ===== Funciones auxiliares =====
void apagarLEDs() {
    digitalWrite(LED_R, LOW);
    digitalWrite(LED_G, LOW);
    digitalWrite(LED_B, LOW);
}

void setColor(int r, int g, int b) {
    digitalWrite(LED_R, r);
    digitalWrite(LED_G, g);
    digitalWrite(LED_B, b);
}

void actualizarLEDCalidad(int valor) {
    // Verde: ≤500, Amarillo: 501-800, Rojo: >800
    if (valor <= 500) {
        setColor(0, 255, 0);      // Verde
    } else if (valor <= 800) {
        setColor(255, 255, 0);    // Amarillo (R+G)
    } else {
        setColor(255, 0, 0);      // Rojo
    }
}

void actualizarEstadoDispositivo(bool estado, int intensidad) {
    HTTPClient http;
    String url = "http://";
    url += "192.168.1.100"; // Cambiar por la IP del servidor
    url += ":8080/api/dispositivo/update";
    http.begin(url);
    http.addHeader("Content-Type", "application/json");

    StaticJsonDocument<128> doc;
    doc["estado_wifi"] = estado;
    doc["intensidad_wifi"] = intensidad;
    String jsonData;
    serializeJson(doc, jsonData);

    int httpCode = http.POST(jsonData);
    if (httpCode == 200) {
        Serial.println("Estado dispositivo actualizado");
    } else {
        Serial.printf("Error al actualizar estado: %d\n", httpCode);
    }
    http.end();
}