{capture append=sidebarHtmlArr}
	<div class="center">
		<h2>Astronomy Pic of the Day</h2>
		{apod}
	</div>
{/capture}
{foreach from=$sidebarHtmlArr item=sidebarHtmlEle name=sidebarElements}
	{assign var='sidebarHtml' value=$sidebarHtmlEle}
{/foreach}

{include file='file:layout/head.tpl'}

<h1>Spacewetter</h1>
<table>
	<tr>
		<td align="left" valign="top">
			<h3>Solarwind</h3>
			<table class="border">
				<tr>
					<td align="left">Geschwindigkeit:</td>
					<td align="left">{$spawe.solarwind_speed} km/s</td>
				</tr>
				<tr>
					<td align="left">Dichte:</td>
					<td align="left">{$spawe.solarwind_density} Protonen/cm<sup>3</sup></td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td align="left" valign="top">
			<h3>relative Anzahl Sonnenflecken</h3>
			<table class="border">
				<tr>
					<td align="left">{$spawe.sunspot_number}</td>
				</tr>
			</table>
			<h3>Sonnenflackern</h3>
			<table class="border">
				<tr>
					<td align="left">in den letzten sechs Stunden um {$solarflares_6hr_time} UT ein Klasse {$spawe.solarflares_6hr_typ} flackern</td>
				</tr>
				<tr>
					<td align="left">in den letzten 24 Stunden um {$solarflares_24hr_time} UT ein Klasse {$spawe.solarflares_24hr_typ} flackern</td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td align="left" valign="top">
			<h3>Magnetsturm Wahrscheinlichkeiten</h3>
			<table class="border">
				<tr>
					<td align="left"><b>Mittlererbreitengrad</b></td>
					<td align="left"><b>Hoherbreitengrad</b></td>
				</tr>
				<tr>
					<td align="center">
						<table class="border">
							<tr>
								<td align="left">&nbsp;</td>
								<td align="left">in 24h</td>
								<td align="left">in 48h</td>
							</tr>
							<tr>
								<td align="left">Normal:</td>
								<td align="left">{$spawe.magstorm_mid_active_24hr}%</td>
								<td align="left">{$spawe.magstorm_mid_active_48hr}%</td>
							</tr>
							<tr>
								<td align="left">Mittel:</td>
								<td align="left">{$spawe.magstorm_mid_minor_24hr}%</td>
								<td align="left">{$spawe.magstorm_mid_minor_48hr}%</td>
							</tr>
							<tr>
								<td align="left">Stark:</td>
								<td align="left">{$spawe.magstorm_mid_severe_24hr}%</td>
								<td align="left">{$spawe.magstorm_mid_severe_48hr}%</td>
							</tr>
						</table>
					</td>
					<td align="center">
						<table class="border">
							<tr>
								<td align="left">&nbsp;</td>
								<td align="left">in 24h</td>
								<td align="left">in 48h</td>
							</tr>
							<tr>
								<td align="left">Normal:</td>
								<td align="left">{$spawe.magstorm_high_active_24hr}%</td>
								<td align="left">{$spawe.magstorm_high_active_48hr}%</td>
							</tr>
							<tr>
								<td align="left">Mittel:</td>
								<td align="left">{$spawe.magstorm_high_minor_24hr}%</td>
								<td align="left">{$spawe.magstorm_high_minor_48hr}%</td>
							</tr>
							<tr>
								<td align="left">Stark:</td>
								<td align="left">{$spawe.magstorm_high_severe_24hr}%</td>
								<td align="left">{$spawe.magstorm_high_severe_48hr}%</td>
							</tr>
						</table>
					</td>
				</tr>
				<!--tr>
					<td align="left" colspan="2"><small>Nordlichter</small></td>
				</tr-->
			</table>
		</td>
	</tr>
	<tr>
		<td align="left" valign="top">
			<h3>Magnetfeld</h3>
			<table class="border">
				<tr>
					<td align="left">Stärke:</td>
					<td align="left">{$spawe.magnetfield_btotal} nT</td>
				</tr>
				<tr>
					<td align="left">Richtung:</td>
					<td align="left">{$spawe.magnet_z_unit}</td>
				</tr>
				<tr>
					<td align="left">Stärke/Richtung:</td>
					<td align="left">{$spawe.magnet_bz_value} nT</td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td align="left" valign="top">
			<h3>Sonnenflackern Wahrscheinlichkeit</h3>
			<table class="border">
				<tr>
					<td align="left">&nbsp;</td>
					<td align="left">in 24h</td>
					<td align="left">in 48h</td>
				</tr>
				<tr>
					<td align="left">Klasse M</td>
					<td align="left">{$spawe.solarflares_percent_24hr_M_percent}%</td>
					<td align="left">{$spawe.solarflares_percent_48hr_M_percent}%</td>
				</tr>
				<tr>
					<td align="left">Klasse X</td>
					<td align="left">{$spawe.solarflares_percent_24hr_X_percent}%</td>
					<td align="left">{$spawe.solarflares_percent_48hr_X_percent}%</td>
				</tr>
				<tr>
					<td align="left" colspan="3"><small>X: Strahlungsstürme, radio blackouts<br>
					M: Strahlungsstürme, radio blackouts in den Polarregionen<br>
					C: wenige wahrnehmbaren Konsequenzen<br>
					B: keine wahrnehmbaren Konsequenzen</small></td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td align="left" valign="top">
			<h3>Asteroiden</h3>
			<table class="border">
				<tr>
					<td align="left">Potenziell gefährliche Asteroiden: {$spawe.PHA}<br>
					<small>Asteroiden die mindestens 100m gross sind<br>
					und näher als 0.05 AU an die Erde herankommen</small></td>
				</tr>
				<tr>
					<td align="center">
						<h4>Asteroidenbegegnungen</h4>
						<table class="border" cellpadding="3">
							<tr>
								<td align="center"><b>Asteroid</b></td>
								<td align="center"><b>Datum</b></td>
								<td align="center"><b>Distanz</b></td>
								<td align="center"><b>Magnetischegrösse</b></td>
							</tr>
							{section name=i loop=$asteroids}
							<tr>
								<td align="left"><a href="http://neo.jpl.nasa.gov/cgi-bin/db?name={$asteroids[i]}" target="_blank">{$asteroids[i]}</a></td>
								<td align="left">{$asteroids[i].date}</td>
								<td align="left">{$asteroids[i].distance}</td>
								<td align="left">{$asteroids[i].mag}</td>
							</tr>
							{/section}
							<tr>
								<td align="left" colspan="4"><small>LD = Lunar Distance, 1 LD = 384,401 km<br>
								1 LD = 0.00256 AU</small></td>
							</tr>
						</table>
					</td>
				</tr>
			</table>
		</td>
	</tr>
</table>
<div class="small center">Source: <a href="https://www.spaceweather.com" target="_blank">www.spaceweather.com</a></div>

{include file='file:layout/footer.tpl'}