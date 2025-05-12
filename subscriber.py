import paho.mqtt.client as mqtt
import mysql.connector
import json 
import logging

# Configure logging
logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s [%(levelname)s] %(message)s',
    datefmt='%Y-%m-%d %H:%M:%S'
)

logging.info("🚀 MQTT subscriber started and running...")

BROKER = "192.168.108.14"
PORT = 1883
TOPIC = "Wuerzburg3/#"

# Database config
DB_CONFIG = {
    'host': 'db',
    'database': 'weather',
    'user': 'user',
    'password': 'pass'
}
        
# Connect to the database
def connect_db():
    try:
        conn = mysql.connector.connect(**DB_CONFIG)
        logging.info("✅ Connected to database")
        return conn
    except mysql.connector.Error as err:
        logging.error(f"❌ Database connection error: {err}")
        raise

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

        # Extract fields
        temp = float(data.get("temp", 0.0))
        hum = float(data.get("hum", 0.0))
        time = data.get("time", None)
        pressure = float(data.get("pressure", 0.0))
        location = data.get("location", "Unknown")
    
        # Validation thresholds
        thresholds = {
            "temp_max": 50.0,       # Max 50°C
            "temp_min": -20.0,      # Min -20°C
            "hum_max": 80.0,        # Max 80%
            "hum_min": 0.0,         # Min 0%
            "pressure_max": 1050.0, # Max 1050 hPa
            "pressure_min": 800.0   # Min 800 hPa
        }

        warnings = {}

        # Temperature checks
        if temp > thresholds["temp_max"]:
            warnings["temp_high"] = f"Temperature too high: {temp}°C"
        elif temp < thresholds["temp_min"]:
            warnings["temp_low"] = f"Temperature too low: {temp}°C"

        # Humidity checks
        if hum > thresholds["hum_max"]:
            warnings["hum_high"] = f"Humidity too high: {hum}%"
        elif hum < thresholds["hum_min"]:
            warnings["hum_low"] = f"Humidity too low: {hum}%"

        # Pressure checks
        if pressure > thresholds["pressure_max"]:
            warnings["pressure_high"] = f"Pressure too high: {pressure} hPa"
        elif pressure < thresholds["pressure_min"]:
            warnings["pressure_low"] = f"Pressure too low: {pressure} hPa"

        # Insert into DB
        conn = connect_db()
        cursor = conn.cursor()

        city = location.lower()
        if city == "mariehamn":
            table_name = "weather_data_fin"
        elif city == "wuerzburg":
            table_name = "weather_data_ger"
        else:
            logging.warning(f"⚠️ Unknown location '{location}'")
            

        insert_query = f"""
            INSERT INTO {table_name} (temp, hum, time, pressure, location, warnings)
            VALUES (%s, %s, %s, %s, %s, %s)
        """

        values = (temp, hum, time, pressure, location, json.dumps(warnings))

        logging.info(f"📝 Inserting into DB: {values}")
        cursor.execute(insert_query, values)
        conn.commit()
        cursor.close()
        conn.close()

        logging.info("💾 Data inserted into database successfully.")

    except json.JSONDecodeError as e:
        logging.error(f"❌ Failed to decode JSON: {e}")
    except mysql.connector.Error as e:
        logging.error(f"❌ MySQL error: {e}")
    except Exception as e:
        logging.error(f"❌ Unexpected error: {e}")

# Setup client
client = mqtt.Client()
client.on_connect = on_connect
client.on_message = on_message

try:
    client.connect(BROKER, PORT)
    logging.info(f"🚀 Connecting to {BROKER}:{PORT}")
    client.loop_forever()
except Exception as e:
    logging.error(f"❌ MQTT connection error: {e}")
