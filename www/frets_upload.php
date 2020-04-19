<?php
/**
 * Frets on zorg
 * @package zorg\Games\Frets on zorg
 */
/**
 * File includes
 */
require_once dirname(__FILE__).'/includes/main.inc.php';

/** fetch GET Data */
$scores_frets = $_GET['scores'];
$songName = 	$_GET['songName'];
$songHash = 	$_GET['songHash'];
$action = 		$_GET['action'];

/** test data */
//$scores_frets = "63657265616c310a370a646963740a6c6973740a7475706c650a340a6934393730300a69340a73360a6b65657033727334300a653863646533396538663765613338633033383762346134636266623930646666613763653036657475706c650a340a6934353833360a69340a73360a6b65657033727334300a326130383431356637306263383935333333363539303234643436633361623766376635656530357475706c650a340a6934343735330a69340a73360a6b65657033727334300a616161643338366338636237656330313664316334383133656531643530333434323861343633317475706c650a340a6933353539340a69330a73360a6b65657033727334300a613533343038373636313563393332376531396635613938616534306535303938336563363463637475706c650a340a6933353533360a69330a73360a6b65657033727334300a30626663373635393237343130386236316235323139356464616430666638613664316536323138310a72310a69320a350a72320a72330a72340a72350a72360a72300a";

$output = shell_exec('python frets_py/fretsonzorg.py '.$scores_frets);
if (!$output) { exit; }

// einzelne score einträge trennen
$scores = split("eof", $output);

// überflüssigen score eintrag entfernen
unset($scores[count($scores)-1]);

foreach($scores as $a) {

    list ($score, $stars, $name, $hash, $difficulty) = split(' ', $a);
    $hash2 = sha1(str_replace("\n","","$difficulty"."$score"."$stars"."$name"));

    //echo "Punkte: $score; Sterne: $stars; Nick: $name; Hash: $hash; Hash2: $hash2; Schwierig: $difficulty $hashword<br />\n";

    if ($hash == $hash2){
        insert_score($name, $score, $stars, $difficulty, $songName);
    }
}

function insert_score($name, $score, $stars, $difficulty, $song)
{
    global $db;

	/*
	$sql = "SELECT id FROM fretsonzorg WHERE song = '$song' AND difficulty = '$difficulty' AND name = '$name' AND score > '$score'";
	$result = $db->query($sql, __FILE__, __LINE__);
    $rs = $db->fetch($result, __FILE__, __LINE__);

	if (!$rs[id]) {

    	$sql = "INSERT INTO fretsonzorg (name, score, stars, difficulty, song) VALUES('$name', '$score', '$stars', '$difficulty', '$song')";
        $result = $db->query($sql, __FILE__, __LINE__);
        echo "new score $score set from $name on zooomclan.org";
    }
	*/

	// anzahl highscores in diesem song und stufe
	$sql = "SELECT COUNT(id) AS quantity FROM fretsonzorg WHERE song = '$song' AND difficulty = '$difficulty'";
	$result = $db->query($sql, __FILE__, __LINE__);
    $rsq = $db->fetch($result, __FILE__, __LINE__);
    
    // sind schon 15 highscores eingetragen?
   // if ($rsq[quantity]) >= 15) {
	
    //$sql = "SELECT COUNT(id) AS number, MIN(score) AS minmum_score, song, difficulty FROM fretsonzorg WHERE song = '$song' AND difficulty = '$difficulty' GROUP by song, difficulty"
    $sql = "SELECT id, MIN(score) FROM fretsonzorg WHERE song = '$song' AND difficulty = '$difficulty' GROUP by id ORDER by score ASC";
    $result = $db->query($sql, __FILE__, __LINE__);
    $rs = $db->fetch($result, __FILE__, __LINE__);
    
    // ist highscore würdig
    if ($rs[score] < $score) {

	   	// hat der user schon eine highscore?
	   	$sql = "SELECT id, score FROM fretsonzorg WHERE song = '$song' AND difficulty = '$difficulty' AND name = '$name'";
       	$result = $db->query($sql, __FILE__, __LINE__);
       	$rs2 = $db->fetch($result, __FILE__, __LINE__);
        
        
		// user hat noch keine score zu diesem level
       	if (!$rs2){
       		// schlechtester eintrag wird updatet
        	if ($rsq[quantity] >= 10) {
                $sql = "UPDATE fretsonzorg SET name = '$name', score = '$score', stars = '$stars' WHERE id = '$rs[id]'";
                $result = $db->query($sql, __FILE__, __LINE__);
                echo "2. new score $score set from $name in song $song with difficulty $difficulty on zooomclan.org<br>";
        	// neue score einfügen
        	} else {
        		$sql = "INSERT INTO fretsonzorg (name, score, stars, difficulty, song) VALUES('$name', '$score', '$stars', '$difficulty', '$song')";
            	$result = $db->query($sql, __FILE__, __LINE__);
            	echo "3. new score $score set from $name in song $song with difficulty $difficulty on zooomclan.org<br>";
        	}

        // vorhandene score ist kleiner, update
        } elseif ($rs2[score]<$score) {
		    $sql = "UPDATE fretsonzorg SET name = '$name', score = '$score', stars = '$stars' WHERE id = '$rs2[id]'";
            $result = $db->query($sql, __FILE__, __LINE__);
        	echo "1. new score $score set from $name in song $song with difficulty $difficulty on zooomclan.org<br>";
        }
    }
}
