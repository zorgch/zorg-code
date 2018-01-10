<?php
//coded by [z]keep3r
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/main.inc.php');

//echo head(47, "pimp");
$smarty->assign('tplroot', array('page_title' => 'pimp'));
$smarty->display('file:layout/head.tpl');


function PostToHost($host, $path, $referer, $data_to_send) {
  $fp = fsockopen($host,80);
  fputs($fp, "POST $path HTTP/1.1\n");
  fputs($fp, "Host: $host\n");
  fputs($fp, "Referer: $referer\n");
  fputs($fp, "Content-type: application/x-www-form-urlencoded\n");
  fputs($fp, "Content-length: ".strlen($data_to_send)."\n");
  fputs($fp, "Connection: close\n\n");
  fputs($fp, "$data_to_send\n");
  while(!feof($fp)) {
      $res .= fgets($fp, 128);
  }
  fclose($fp);

  return $res;
}

// random name
function rnd_name($first, $last) {

	$id = rnd_id(6);

	// first
	if ($id == 1) {
		return $first;

	// last
	}elseif ($id == 2) {
		return $last;

	// first 1
	}elseif ($id == 3) {
		$first = strtoupper($first);
		$first = substr($first, 0, 1);
		return $first;

	// first 1 mit .
	}elseif ($id == 4) {
		$first = strtoupper($first);
		$first = substr($first, 0, 1);
		return $first.".";

	// last 1
	}elseif ($id == 5) {
		$last = strtoupper($last);
		$last = substr($last, 0, 1);
		return $last;

	// last 1 mit .
	}elseif ($id == 6) {
		$last = strtoupper($last);
		$last = substr($last, 0, 1);
		return $last.".";
	}
}

// random id
function rnd_id($total) {
	srand((double)microtime()*1000000);
	$seed = rand(1000000,9999999);
	srand((double)microtime()*$seed);
	return rand (1,$total);
}

$first = $_POST['first'];
$last = $_POST['last'];

// pimpern von playerappreciate.com
if($_POST['do'] == "pimpme"){

	$data = "First=$first&Last=$last&Pimpify=Pimpify!";

	$x = PostToHost(
              "www.playerappreciate.com",
              "/pimphandle.asp",
              "www.playerappreciate.com/pimphandle.asp",
              $data);

	$exp = "/Your Pimp Name is:\s<\/b><\/font><br><br><center><b><u\sstyle='color:darkred'>(.*)<\/u><\/center>/";

	preg_match($exp, $x, $output);
	echo ("<center><b>$output[1]</b><br><br></center>");

// pimpern aus zoomscher db
} elseif ($_POST['do'] == "pimpme2"){

	$sql = "SELECT count(*) as anzahl FROM pimp";
	$result = $db->query($sql);
	$rs = $db->fetch($result);
	$total = $rs[anzahl];
	$id = rnd_id($total);

	$sql = "SELECT * FROM pimp WHERE id = $id";
	$result = $db->query($sql);
	$rs = $db->fetch($result);

	$prefix = $rs[prefix];

	$id = rnd_id($total);

	$sql = "SELECT * FROM pimp WHERE id = $id";
	$result = $db->query($sql);
	$rs = $db->fetch($result);

	$suffix = $rs[suffix];

	$name = rnd_name($first, $last);

	echo ("<center><b>$prefix $name $suffix</b><br><br></center>");
}

echo ("<center><table><tr><form name='pimpform' action='$_SERVER[PHP_SELF]' method='post'><td align='center'>"
     ."First Name:&nbsp;<input type='text' class='text' name='first' value='$first'><br>"
     ."Last Name:&nbsp;<input type='text' class='text' name='last' value='$last'><br>"
     ."<input type='hidden' name='do' value='pimpme2'>"
     ."<input type='submit' class='button' name='send' value='pimp me'>"
	 ."</td></tr></table></center>");

//echo foot(52);
$smarty->display('file:layout/footer.tpl');
?>