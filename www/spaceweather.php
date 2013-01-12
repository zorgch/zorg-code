<?PHP
	require_once($_SERVER['DOCUMENT_ROOT'].'/includes/main.inc.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/layout.inc.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/apod.inc.php');


$sql = "SELECT * FROM spaceweather";
$result = $db->query($sql,__LINE__,__FILE__);
while($rs = $db->fetch($result)) {
	
	$sw[$rs['name']] = $rs['wert'];	
}




$solarwind = 
"<table class='border'><tr><td align='center' colspan='2'><b>Solarwind</b></td></tr>
<tr><td align='left'>Geschwindigkeit: </td><td align='left'>".$sw['solarwind_speed']." km/s</td></tr>
<tr><td align='left'>Dichte: </td><td align='left'>".$sw['solarwind_density']." Protonen/cm<sup>3</sup></td></tr>
</table>";



$sonnenflackern = 
"<table class='border'><tr><td align='center'><b>Sonnenflackern</b></td></tr>
<tr><td align='left'>in den letzten sechs Stunden um ".date("H:i",strtotime($sw['solarflares_6hr_time']))." UT ein Klasse ".$sw ['solarflares_6hr_typ']." flackern</td></tr>
<tr><td align='left'>in den letzten 24 Stunden um ".date("H:i",strtotime($sw['solarflares_24hr_time']))." UT ein Klasse ".$sw ['solarflares_24hr_typ']." flackern</td></tr>
</table>";



$sunspot = 
"<table class='border'><tr><td align='center'><b>relative Anzahl Sonnenflecken</b></td></tr>
<tr><td align='left'>".$sw['sunspot_number']."</td></tr>
</table>";



$magnetfeld = 
"<table class='border'><tr><td align='center' colspan='2'><b>Magnetfeld</b></td></tr>
<tr><td align='left'>Stärke:</td><td align='left'>".$sw['magnetfield_btotal']." nT</td></tr>
<tr><td align='left'>Richtung:</td><td align='left'>".$sw['magnet_z_unit']."</td></tr>
<tr><td align='left'>Stärke/Richtung:</td><td align='left'>".$sw['magnet_bz_value']." nT</td></tr>
</table>";



$sonnenflackern_war =  "
<table class='border'><tr><td align='center' colspan='3'><b>Sonnenflackern Wahrscheinlichkeit</b></td></tr>
<tr><td align='left'>&nbsp;</td><td align='left'>in 24h</td><td align='left'>in 48h</td></tr>
<tr><td align='left'>Klasse M</td><td align='left'>".$sw['solarflares_percent_24hr_M_percent']."%</td><td align='left'>".$sw['solarflares_percent_48hr_M_percent']."%</td></tr>
<tr><td align='left'>Klasse X</td><td align='left'>".$sw['solarflares_percent_24hr_X_percent']."%</td><td align='left'>".$sw['solarflares_percent_48hr_X_percent']."%</td></tr>
<tr><td align='left' colspan='3'><small>
X: Strahlungsstürme, radio blackouts <br>
M: Strahlungsstürme, radio blackouts in den Polarregionen<br>
C: wenige wahrnehmbaren Konsequenzen <br>
B: keine wahrnehmbaren Konsequenzen
</small>
</td></tr>
</table>";


$magstorm = 
"<table class='border'><tr><td align='center' colspan='2'>
<b>Magnetsturm Wahrscheinlichkeiten</b></td></tr>
<tr><td align='left'><b>Mittlererbreitengrad</b></td><td align='left'><b>Hoherbreitengrad</b></td></tr>
<tr><td align='center'>
	<table class='border'>
	<tr>
	<td align='left'>&nbsp;</td>
	<td align='left'>in 24h</td>
	<td align='left'>in 48h</td>
	</tr>
	<tr>
	<td align='left'>Normal:</td>
	<td align='left'>".$sw['magstorm_mid_active_24hr']."%</td>
	<td align='left'>".$sw['magstorm_mid_active_48hr']."%</td>
	</tr>
	<tr>
	<td align='left'>Mittel:</td>
	<td align='left'>".$sw['magstorm_mid_minor_24hr']."%</td>
	<td align='left'>".$sw['magstorm_mid_minor_48hr']."%</td>
	</tr>
	<tr>
	<td align='left'>Stark:</td>
	<td align='left'>".$sw['magstorm_mid_severe_24hr']."%</td>
	<td align='left'>".$sw['magstorm_mid_severe_48hr']."%</td>
	</tr>
	</table>
</td><td align='center'>
	<table class='border'>
	<tr>
	<td align='left'>&nbsp;</td>
	<td align='left'>in 24h</td>
	<td align='left'>in 48h</td>
	</tr>
	<tr>
	<td align='left'>Normal:</td>
	<td align='left'>".$sw['magstorm_high_active_24hr']."%</td>
	<td align='left'>".$sw['magstorm_high_active_48hr']."%</td>
	</tr>
	<tr>
	<td align='left'>Mittel:</td>
	<td align='left'>".$sw['magstorm_high_minor_24hr']."%</td>
	<td align='left'>".$sw['magstorm_high_minor_48hr']."%</td>
	</tr>
	<tr>
	<td align='left'>Stark:</td>
	<td align='left'>".$sw['magstorm_high_severe_24hr']."%</td>
	<td align='left'>".$sw['magstorm_high_severe_48hr']."%</td>
	</tr>
	</table>
</td></tr>
<tr><td align='left' colspan='2'><small>Nordlichter</small></td></tr>
</table>";
$apod =  "<br><table class='border'><tr><td align='center'><b>Astronomy Pic of the Day:</b></td></tr><tr><td align='center'>".formatGalleryThumb(get_apod_id())."</td></tr></table>";



$asteroids = 
"<table class='border'><tr><td align='center'><b>Asteroiden</b></td></tr>
<tr><td align='left'>Potentiel gefährliche Asteroiden: ".$sw['PHA']."
<br /><small>Asteroiden die mindestens 100m gross sind <br>und näher als 0.05 AU an die Erde herankommen</small></td></tr>
<tr><td align='center'><b>Asteroidenbegnungen</b></td></tr>
<tr><td align='center'>
	<table class='border' cellpadding='3'>
	<tr>
	<td align='center'><b>Asteroid</b></td>
	<td align='center'><b>Datum</b></td>
	<td align='center'><b>Distanz</b></td>
	<td align='center'><b>Magnetischegrösse</b></td>
	</tr>";
	$sql = "SELECT *, UNIX_TIMESTAMP(datum) as ddatum FROM spaceweather_pha WHERE MONTH(datum) = MONTH(now()) AND YEAR(datum) = YEAR(now())";
	$result = $db->query($sql,__LINE__,__FILE__);
	while($rs = $db->fetch($result)) {
		$asteroids .= "<tr>
		<td align='left'>
		<a href='http://neo.jpl.nasa.gov/cgi-bin/db?name=".$rs['asteroid']."' target='_blank'>".$rs['asteroid']."</a>
		</td>
		<td align='left'>".date("M d.",$rs['ddatum'])."</td>
		<td align='left'>".$rs['distance']."</td>
		<td align='left'>".round($rs['mag'])."</td>
		</tr>";	
	}	
	$asteroids .= "
	<tr><td align='left' colspan='4'><small>
	LD = Lunar Distance, 1 LD = 384,401 km <br>
	1 LD = 0.00256 AU
	</small></td></tr>
	</table>
</td></tr>
</table>";

echo head(85);
echo menu('main');
echo menu('mischt');

echo "<table width='100%'><tr><td align='center' colspan='2'><b><h2>Spacewetter</h2></b></td></tr>";
echo "<tr><td align='left' valign='top'>";
echo $solarwind;
echo "</td><td align='left' valign='top'>";
echo $magnetfeld;
echo "</td></tr><tr><td align='left' valign='top'>";
echo $sonnenflackern;
echo "</td><td align='left' align='left' valign='top'>";
echo $sonnenflackern_war;
echo "</td></tr><tr><td align='left' valign='top'>";
echo $magstorm.$apod;
echo "</td><td align='left' valign='top'>";
echo $asteroids;
echo "</td></tr>";
echo "<tr><td align='center' colspan='2'><small><a href='http://www.spaceweather.com' target='_blank'>www.spaceweather.com</a></small></td></tr>";
echo "</table>";
echo foot(1);

?>