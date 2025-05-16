# Start mit dem minimalen Alpine-Image
FROM alpine:3.16.2

# Installiere cron und die InfluxDB CLI für Backups
RUN apk add --no-cache bash curl tzdata cronie

# Kopiere den Cronjob und den Backup-Skript
COPY docker/db-backup/backup-cronfile /etc/crontabs/root
COPY docker/db-backup/backup.sh /usr/local/bin/backup.sh

# Stelle sicher, dass die Crontab-Datei die richtigen Rechte hat
RUN chmod 600 /etc/crontabs/root && chown root:root /etc/crontabs/root

# Sicherstellen, dass das Skript ausführbar ist
RUN chmod +x /usr/local/bin/backup.sh

# Installiere die InfluxDB CLI
RUN wget https://dl.influxdata.com/influxdb/releases/influxdb2-client-2.7.0-linux-amd64.tar.gz && \
    tar xvzf influxdb2-client-2.7.0-linux-amd64.tar.gz && \
    mv influx /usr/local/bin/ && \
    rm influxdb2-client-2.7.0-linux-amd64.tar.gz

# Starte den cron-Dienst
CMD ["crond", "-n", "-x", "sch,proc,pars", "-m", "off"]