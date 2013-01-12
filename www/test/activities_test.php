<?php ini_set( 'display_errors', true ); error_reporting(E_ALL);

// Test-Parameter definieren
$user = "IneX";
$catcher = "[z]domi";
$gameID = 717;
$dwz = 17;



// ENV definiert eine Superglobale, das Array ist somit global verfügbar
$_ENV['$activities_HZ'] =
	array(
		'HZ' => array(
			1	=>	"$user hat ein neues Hunting z Spiel eröffnet.",
					"$catcher hat Mr. Z in <a href=\"/smarty.php?tpl=103&game=$gameID\">diesem Hz Spiel</a> gefangen"
		)
	);

$_ENV['$activities_AD'] =
	array(
		'AD' => array(
			1	=>	"$user hat $catcher im Addle geschlagen und $dwz DWZ Punkte gewonnen"
		)
	);


// Test Funktion zur Ausgabe einer Activity
function echoActivity($area,$code)
{
	$activities = $_ENV['$activities_HZ'] + $_ENV['$activities_AD'];
	
	//print_r(array_keys($activities));
	//print_r(get_defined_vars());
	//foreach (array_keys($activities) as $activity_area) {
	/*foreach (get_defined_vars() as $activity_area) {
		echo $activity_area."<br>";
	}*/
	?><pre><?php
	print_r(array_values($_ENV));
	?></pre><?php
	
	//echo $activities[$area][$code];
}

// Activity ausgeben
echoActivity("HZ",2);

?>