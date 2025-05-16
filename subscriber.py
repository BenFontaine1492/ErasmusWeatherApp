import paho.mqtt.client as mqtt
import json
import logging
from datetime import datetime
from influxdb_client import InfluxDBClient, Point, WritePrecision
from influxdb_client.client.write_api import SYNCHRONOUS

# Logging setup
logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s [%(levelname)s] %(message)s',
    datefmt='%Y-%m-%d %H:%M:%S'
)

logging.info("🚀 MQTT subscriber started and running...")

# MQTT broker settings
BROKER = "192.168.108.14"
PORT = 1883
TOPIC = "Wuerzburg3/#"

# InfluxDB configuration
INFLUXDB_URL = "http://db:8086"
INFLUXDB_TOKEN = "your-token"
INFLUXDB_ORG = "your-org"
INFLUXDB_BUCKET = "weather"

client_influx = InfluxDBClient(
    url=INFLUXDB_URL,
    token=INFLUXDB_TOKEN,
    org=INFLUXDB_ORG
)

write_api = client_influx.write_api(write_options=SYNCHRONOUS)

# Thresholds for warning evaluation
thresholds = {
    "temp_max": 50.0,
    "temp_min": -20.0,
    "hum_max": 80.0,
    "hum_min": 0.0,
    "pressure_max": 1050.0,
    "pressure_min": 800.0
}

def on_connect(client, userdata, flags, rc):
    logging.info(f"🔗 Connected to broker with result code {rc}")
    if rc == 0:
        client.subscribe(TOPIC)
        logging.info(f"📡 Successfully subscribed to topic: {TOPIC}")
    else:
        logging.error("❌ Connection failed")

def on_message(client, userdata, msg):
    try:
        payload = msg.payload.decode()
        logging.info(f"📥 Raw message: {payload}")
        data = json.loads(payload)
        logging.info(f"✅ Parsed JSON: {data}")

        # Extract data
        temp = float(data.get("temp", 0.0))
        hum = float(data.get("hum", 0.0))
        pressure = float(data.get("pressure", 0.0))
        time_str = data.get("time")
        location = data.get("location", "Unknown").lower()

        # Convert to datetime object if time provided
        timestamp = datetime.utcnow()
        if time_str:
            try:
                timestamp = datetime.fromisoformat(time_str.replace("Z", "+00:00"))
            except Exception:
                logging.warning("⚠️ Invalid time format, using UTC now")

        # Validation thresholds
        thresholds = {
            "temp_max": 50.0, "temp_min": -20.0,
            "hum_max": 80.0,  "hum_min": 0.0,
            "pressure_max": 1050.0, "pressure_min": 800.0
        }

        # Warning checks
        warnings = {}
        if temp > thresholds["temp_max"]:
            warnings["temp_high"] = f"Temperature too high: {temp}°C"
        elif temp < thresholds["temp_min"]:
            warnings["temp_low"] = f"Temperature too low: {temp}°C"
        if hum > thresholds["hum_max"]:
            warnings["hum_high"] = f"Humidity too high: {hum}%"
        elif hum < thresholds["hum_min"]:
            warnings["hum_low"] = f"Humidity too low: {hum}%"
        if pressure > thresholds["pressure_max"]:
            warnings["pressure_high"] = f"Pressure too high: {pressure} hPa"
        elif pressure < thresholds["pressure_min"]:
            warnings["pressure_low"] = f"Pressure too low: {pressure} hPa"

        # Determine measurement name
        if location == "mariehamn":
            measurement = "weather_data_fin"
        elif location == "wuerzburg":
            measurement = "weather_data_ger"
        else:
            logging.warning(f"⚠️ Unknown location: {location}, defaulting to 'weather_data'")
            measurement = "weather_data"

        # Build and write point
        point = (
            Point(measurement)
            .tag("location", location)
            .field("temp", temp)
            .field("hum", hum)
            .field("pressure", pressure)
            .field("warnings", json.dumps(warnings))
            .time(timestamp)
        )

        logging.info(f"📤 Writing point to InfluxDB: {point.to_line_protocol()}")
        write_api.write(bucket=INFLUXDB_BUCKET, org=INFLUXDB_ORG, record=point)
        logging.info("✅ Successfully written to InfluxDB.")

    except json.JSONDecodeError as e:
        logging.error(f"❌ Failed to decode JSON: {e}")
    except Exception as e:
        logging.error(f"❌ Unexpected error: {e}")

# Setup MQTT client
client = mqtt.Client()
client.on_connect = on_connect
client.on_message = on_message

try:
    client.connect(BROKER, PORT)
    client.loop_forever()
except Exception as e:
    logging.error(f"❌ MQTT connection error: {e}")