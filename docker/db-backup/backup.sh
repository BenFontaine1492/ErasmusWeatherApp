#!/bin/bash

# PATH setzen
export PATH=/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin

# Log-Datei
DEBUG_LOG="/backup/debug_script.log"
echo "Backup-Skript gestartet am $(date)" >> $DEBUG_LOG

# Setze das Backup-Verzeichnis
BACKUP_DIR="/backup/$(date +'%Y-%m-%d_%H-%M-%S')"
echo "Backup-Verzeichnis: $BACKUP_DIR" >> $DEBUG_LOG

# Verzeichnis erstellen
mkdir -p "$BACKUP_DIR" 2>> $DEBUG_LOG
if [ $? -ne 0 ]; then
    echo "FEHLER: Konnte Backup-Verzeichnis nicht erstellen!" >> $DEBUG_LOG
    exit 1
fi

# Führe das InfluxDB-Backup aus
echo "Starte InfluxDB-Backup..." >> $DEBUG_LOG
influx backup --host http://db:8086 "$BACKUP_DIR" >> $DEBUG_LOG 2>&1
if [ $? -ne 0 ]; then
    echo "FEHLER: InfluxDB-Backup-Befehl fehlgeschlagen!" >> $DEBUG_LOG
    exit 1
else
    echo "InfluxDB-Backup erfolgreich abgeschlossen." >> $DEBUG_LOG
fi

# Alte Backups löschen (optional)
echo "Lösche alte Backups..." >> $DEBUG_LOG
find /backup/* -mtime +30 -exec rm -rf {} \; >> $DEBUG_LOG 2>&1
if [ $? -ne 0 ]; then
    echo "FEHLER: Fehler beim Löschen alter Backups!" >> $DEBUG_LOG
else
    echo "Alte Backups erfolgreich gelöscht." >> $DEBUG_LOG
fi

echo "Backup-Skript abgeschlossen am $(date)" >> $DEBUG_LOG