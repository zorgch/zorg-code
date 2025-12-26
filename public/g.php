<?php
/**
 * GALAXY-NETWORK Browsergame - Taktik speichern
 *
 * @link http://www.galaxy-network.net/game/login.php
 *
 * @package zorg\Games\GalaxyNetwork
 */
/**
 * File includes
 * @include main.inc.php required
 */
require_once dirname(__FILE__).'/includes/main.inc.php';

if(isset($_POST['taktik']) && !empty($_POST['taktik']))
{
	$html = strip_tags($_POST['taktik'],'<tr><td><table><th>');
	$html = str_replace('  ','',$html);
	$html = str_replace("\n",'',$html);
	$array = explode('</TD>',$html);
	$m = 0;
	$x = 0;
	for($i = 10;$i<=count($array);$i++)
	{
		if($m == 10) {
			$m = 0;
			$x++;
		}
		//echo $i." = ".htmlentities($array[$i])."<br><br><br>";
		$member[$x][$m] = [ $i ];
		$m++;	
	}
	$desc = array(
		'sektor',
		'name',
		'attet',
		'attet_zeit',
		'defft',
		'defft_zeit',
		'wird_geattet',
		'wird_geattet_zeit',
		'wird_gedefft',
		'wird_gedefft_zeit'
	);
	$t = TRUE;
	$gala = trim(substr($member[0][0],0,strpos($member[0][0],":")));
	for($i = 0;$i<$x-1;$i++) {
		if(!preg_match('(\d+:\d+)',$member[$i][0],$out)) {
			$t = FALSE;
		}
		if($t) {
			echo $i.'<br>';
			$sql[$i] = '
			REPLACE INTO taktik 
				(sektor, name, 
				attet, attet_zeit, 
				defft, defft_zeit, 
				wird_geattet, wird_geattet_zeit, 
				wird_gedefft, wird_gedefft_zeit,
				gala_id, datum)
			VALUES (';
			for($in = 0;$in<10;$in++) {
				$data = strip_tags($member[$i][$in]);
				$data = trim(str_replace('&nbsp;',' ',$data));
				$data = str_replace(' *','',$data);
				$sql[$i] .= '"'.$data.'" ,';
				echo $desc[$in].' = '.htmlentities($data).'<br>';	
			}
			$sql[$i] .= trim($gala).' , now())';
			echo $sql[$i].'<br>';
			echo '<hr>';
		}
	}
} else {
	echo 'Taktik-Daten fehlen!';
}

echo '<br><br><br>
<form action="?" method="post">
<textarea name="taktik" cols="10" rows="2">
</textarea>
<input type="submit">
</form>';
