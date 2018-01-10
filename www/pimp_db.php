<?php
//coded by [z]keep3r
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/main.inc.php');

//echo head(40);
$smarty->assign('tplroot', array('page_title' => 'pimp db'));
$smarty->display('file:layout/head.tpl');

// pimp in db speichern
function insert_pimp($prefix, $suffix,$db) {

	$sql = "SELECT * FROM pimp WHERE prefix = '$prefix'";
	$result = $db->query($sql);
	$rs = $db->fetch($result);
	
	if (!$rs) {
	
        $sql2 = "SELECT * FROM pimp WHERE suffix = '$suffix'";
        $result2 = $db->query($sql2);
        $rs2 = $db->fetch($result2);
        
		if (!$rs2){
            $sql = "INSERT INTO pimp(
                    prefix,
                    suffix

                    )VALUES(

                    '$prefix',
                    '$suffix'
                    )";
            $db->query($sql,__FILE__, __LINE__);
		}
	}
}

function PostToHost($host, $path, $referer, $data_to_send) {
  $fp = fsockopen($host,80);
  //printf("Open!\n");
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

function get_pimp() {
	
	$first = "Q";
	$last = "Q";
	$data = "First=$first&Last=$last&Pimpify=Pimpify!";
	$x = PostToHost(
              "www.playerappreciate.com",
              "/pimphandle.asp",
              "www.playerappreciate.com/pimphandle.asp",
              $data);

	$exp = "/Your Pimp Name is:\s<\/b><\/font><br><br><center><b><u\sstyle='color:darkred'>(.*)<\/u><\/center>/";

	preg_match($exp, $x, $output);
	return $output[1];
}

for ($i=0;$i<500;$i++){
    $exp = "/(.*)\sQ?.\s(.*)/";
    preg_match($exp, get_pimp(), $pimp);

    echo "$pimp[0]<br>";

    insert_pimp($pimp[1],$pimp[2],$db);
}

//echo foot(52);
$smarty->display('file:layout/footer.tpl');
?>