# zorg Cron jobs
Mittels diversen Server-seitigen Cron jobs werden regelmässig notwendige Tasks ausgeführt.

Zum Beispiel: Daily Pic setzen, Daily Quote setzen, Gravater Userimages cachen, Stockbroker Aktienkurse aktualiseren, usw.

### Cron jobs speichern
Über das `crontab` werden die verschiedenen Cron jobs anhand deren Wiederholungsrate festgelegt:

```
$ sudo crontab -e
 *      15 7 * * * php -f /path/to/cron/[file].php > /path/wher/to/log/cron_[cadence].log
```

## Minutely
- `stockbroker_minute`: Stockbroker Aktienkurse aktualisieren

## Hourly
- `stunde`: Upcoming Events check, Gravatar Cache aktualisieren
- `stockbroker_stunde`: Stockbroker Tradings aktualisieren

## Daily
- `tag`: Daily Pic & Quote setzen, APOD holen, Spaceweather & BOINC Stats update, alte kompilierte Comment Templates freigeben, Addle Games älter als 15 Wochen löschen, nächsten Hunting z Zug weitergeben

## Weekly
- `woche`: Entfernen von unread Comments älter als 30 Tage
