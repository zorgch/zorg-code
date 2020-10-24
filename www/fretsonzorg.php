<?php
/**
 * Frets on Zorg
 *
 * "Frets on Fire" Hi-scores für zorg.
 * coded by [z]keep3r
 *
 * @author [z]keep3r
 * @package zorg\Games\Fretsonzorg
 */

/**
 * File includes
 */
require_once dirname(__FILE__).'/includes/main.inc.php';
require_once MODELS_DIR.'core.model.php';

/**
 * Initialise MVC Model
 */
$model = new MVC\Fretsonzorg();

//echo head(35, "fretsonzorg");
//$smarty->assign('tplroot', array('page_title' => 'fretsonzorg'));
$model->showOverview($smarty);
$smarty->display('file:layout/head.tpl');
//echo menu('main');
//echo menu('games');
//echo menu('fretsonzorg');

echo "<h2>Bang Bang, Mystery Man</h2>";
echo "<table style='width: 100%'><tr>";

print_score_table("bangbang",2);
print_score_table("bangbang",1);
print_score_table("bangbang",0);

echo "<td></tr></table>";
echo "<h2>Defy The Machine</h2>";
echo "<table style='width: 100%'><tr>";

print_score_table("defy",2);
print_score_table("defy",1);
print_score_table("defy",0);

echo "<td></tr></table>";
echo "<h2>This Week I've Been Mostly Playing Guitar</h2>";
echo "<table style='width: 100%'><tr>";

print_score_table("twibmpg",2);
print_score_table("twibmpg",1);
print_score_table("twibmpg",0);

echo "<td></tr></table>";

//echo foot();
$smarty->display('file:layout/footer.tpl');

function print_stars($stars) {

	// imagesource
    $star1 = '<img src="/images/star1.png">';
    $star2 = '<img src="/images/star2.png">';
    
    for ($i=0;$i<$stars;$i++){
    	$star=$star.$star2;
    }
    for ($i=0;$i<(5-$stars);$i++){
    	$star=$star.$star1;
    }
	return $star;
}

function print_score_table($song,$difficulty){
	
	global $db;
	
	switch ($difficulty) {
    case 0:
		$difficulty_name = "Amazing";
		break;
    case 1:
       $difficulty_name = "Medium";
       break;
    case 2:
       $difficulty_name = "Easy";
       break;
    }
    
	echo "<td style='width: 33%; vertical-align: top; border-left: solid thin #200; padding-left: 1em'><h3>$difficulty_name</h3>";
	echo "<table>";
	
	$sql = "SELECT * FROM fretsonzorg WHERE song = '$song' AND difficulty = '$difficulty' ORDER BY score DESC LIMIT 0,10";
  	$result = $db->query($sql, __FILE__, __LINE__);

	$i=1;
	while($rs = $db->fetch($result, __FILE__, __LINE__)) {
	
		$starpic = print_stars($rs[stars]);
		echo "<tr><td style='width: 2em' valign='top'>$i.</td><td style='width: 5em' valign='top'>$rs[score]</td><td style='width: 10em' valign='top'>$starpic</td><td valign='top'>$rs[name]</td></tr></tr>";
		$i++;
	}
	
	echo "</table></td>";

}
