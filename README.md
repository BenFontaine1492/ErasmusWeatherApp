# ErasmusWeatherApp


## Startup 

To start the App use the "make startup" command in the terminal,
for restarting the containers and further commands please refer to the Makefile. 

"make install" (included in make startup) will install required python and js dependencies.

To run the make commands you will need Linux or WSL (Ne, Jakob. Zwinkersmiley) 

## Development

Once the database container is running you can connect to it locally with following login data: 

host: localhost
port: 3306
user: user etc.
password: pass 

The data on the DB will be created using the following commands : 
```
create table weather_data_fin (
    id int auto_increment primary key,
    temp float,
    hum float,
    time datetime,
    pressure float,
    location varchar(100)
); 
```
```
create table weather_data_ger (
    id int auto_increment primary key,
    temp float,
    hum float,
    time datetime,
    pressure float,
    location varchar(100)
); 
```

If you do not yet have an ESP32 that pushes data to a broker you are subscribed to,
you will need to insert example data to your database. 

If you want to have a basic frontend,
you will need to uncomment the frontend docker container in the docker-compose.yml
The frontend can be reached under http://localhost:8080

## TechStack 

### Frontend 
- [JavaScript](https://developer.mozilla.org/en-US/docs/Web/JavaScript)
- [ChartJS](https://www.chartjs.org/docs/latest/)

### Backend 
- [PHP](https://www.php.net/docs.php)
- [Docker](https://docs.docker.com/)
- [Python](https://docs.python.org/3/)

### Database 
- [MySQL](https://dev.mysql.com/doc/)

