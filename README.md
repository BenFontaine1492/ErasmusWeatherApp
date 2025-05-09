# 🌤️ ErasmusWeatherApp

A weather data tracking application using Docker, PHP, Python, and MySQL. This project collects and visualizes environmental data via MQTT-connected ESP32 devices.

---

## 🚀 Getting Started

### Startup

To start the app, run:

```bash
make startup
```

This command installs all necessary Python and JavaScript dependencies (via `make install`) and spins up the Docker containers.

> 💡 Make sure you're running on **Linux** or **WSL** (Ne, Jakob Zwinkersmiley).

For other commands (restarting containers, rebuilding, etc.), refer to the `Makefile`.

---

## 🛠️ Development

### Database Access

Once the database container is running, connect locally with:

- **Host:** `localhost`  
- **Port:** `3306`  
- **User:** `user` *(replace with actual)*  
- **Password:** `pass` *(replace with actual)*  

### Table Setup

The following queries will be used to create the tables:

```sql
CREATE TABLE weather_data_fin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    temp FLOAT,
    hum FLOAT,
    time DATETIME,
    pressure FLOAT,
    location VARCHAR(100)
);
```

```sql
CREATE TABLE weather_data_ger (
    id INT AUTO_INCREMENT PRIMARY KEY,
    temp FLOAT,
    hum FLOAT,
    time DATETIME,
    pressure FLOAT,
    location VARCHAR(100)
);
```

### ESP32 Note

If you don't have an ESP32 device pushing data to your MQTT broker, you'll need to manually insert example data into your database.

### Frontend Access

To enable the frontend:

1. Uncomment the relevant container in `docker-compose.yml`.
2. Visit the frontend at: [http://localhost:8080](http://localhost:8080)

---

## 🧰 Tech Stack

### 🌐 Frontend

- [JavaScript](https://developer.mozilla.org/en-US/docs/Web/JavaScript)
- [Chart.js](https://www.chartjs.org/docs/latest/)

### 🖥️ Backend

- [PHP](https://www.php.net/docs.php)
- [Python](https://docs.python.org/3/)
- [Docker](https://docs.docker.com/)

### 🗄️ Database

- [MySQL](https://dev.mysql.com/doc/)
