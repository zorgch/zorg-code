# Daten Scripts
Diese Scripts sind zur Server-side Erledigung von unterschiedlichen Tasks oder Bereinigungen.

## APOD
- `apod_download_image` - Unix shell script to manually fetch an unprocessed APOD image

## BOINC
- `SETI Get Stats` - XML-Stats eines SETI Teams holen (DEPRECATED)
  + Example: [https://setiathome.berkeley.edu/team_lookup.php?team_id=30893](https://setiathome.berkeley.edu/team_lookup.php?team_id=30893)
  ```
  <?xml version="1.0" encoding="ISO-8859-1" ?>
	<team>
	    <id>30893</id>
	    <create_time>920246400</create_time>
	    <userid>245902</userid>
	    <name>zooomclan.org</name>
	    <url>www.zooomclan.org</url>
	    <type>1</type>
	    <country>Switzerland</country>
	    <total_credit>12187462.5141329</total_credit>
	    <expavg_credit>326.311839852673</expavg_credit>
	    <expavg_time>1587103090.34215</expavg_time>
	</team>
  ```
