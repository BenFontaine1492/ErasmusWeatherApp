import paho.mqtt.client as mqtt
import mysql.connector
import json 


BROKER = "test.mosquitto.org"
PORT = 1883
TOPIC = "Wuerzburg/#"

# Database config
DB_CONFIG = {
    'host': 'localhost',
    'database': 'weather',
    'user': 'user',
    'password': 'pass'
}

# Connect to the database
def connect_db():
    return mysql.connector.connect(**DB_CONFIG)

def on_connect(client, userdata, flags, rc):
    print(f"🔗 Connected with result code {rc}")
    client.subscribe(TOPIC)
    print(f"📡 Subscribed to {TOPIC}, listening for messages...")

def on_message(client, userdata, msg):
    try:
        data = json.loads(msg.payload.decode())
        print(f"✅ Received: {data}")

        # Extract fields with fallback defaults
        temp = float(data.get("temp", 0.0))
        hum = float(data.get("hum", 0.0))
        time = data.get("time", None)
        pressure = float(data.get("pressure", 0.0))
        location = data.get("location", "Unknown")

        # Insert into DB
        conn = connect_db()
        cursor = conn.cursor()

        cursor.execute("""
            INSERT INTO weather_data_ger (temp, hum, time, pressure, location)
            VALUES (%s, %s, %s, %s, %s)
        """, (temp, hum, time, pressure, location))

        conn.commit()
        cursor.close()
        conn.close()

        print("💾 Data inserted into database.")

    except Exception as e:
        print(f"❌ Error: {e}")


client = mqtt.Client() 
client.on_connect = on_connect
client.on_message = on_message

client.connect(BROKER, PORT)
client.loop_forever()