create table weather_data_fin (
    id int auto_increment primary key,
    temp float,
    hum float,
    time datetime,
    pressure float,
    location varchar(100)
    warnings text
);
create table weather_data_ger (
    id int auto_increment primary key,
    temp float,
    hum float,
    time datetime,
    pressure float,
    location varchar(100)
    warnings text
);