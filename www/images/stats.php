<?PHP	
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/main.inc.php');

$monate = array(1 => "Jan", 2 => "Feb", 3 => "Mar", 4 => "Apr", 5 => "Mai", 6 => "Jun", 7 => "Jul", 8 => "Aug", 9 => "Sep", 10 => "Okt", 11 => "Nov", 12 => "Dez");

$jahre = array();

if($_GET['user_id']) {


	$img = new Line();

	$rr = hexdec(substr(BACKGROUNDCOLOR,0,2));
	$gg = hexdec(substr(BACKGROUNDCOLOR,2,2));
	$bb = hexdec(substr(BACKGROUNDCOLOR,4,2));
	imagecolordeallocate($img->image,$img->bgCol);
	$img->bgCol = imagecolorallocate($img->image,$rr,$gg,$bb);


	$rr = hexdec(substr(FONTCOLOR,0,2));
	$gg = hexdec(substr(FONTCOLOR,2,2));
	$bb = hexdec(substr(FONTCOLOR,4,2));
	imagecolordeallocate($img->image,$img->titleCol);
	$img->titleCol = imagecolorallocate($img->image,$rr,$gg,$bb);
	$img->SetAxesColor($rr,$gg,$bb);

	if(is_numeric($_GET['user_id'])) {
		$sql = "
		SELECT username FROM user WHERE id = '$_GET[user_id]'";
		$result = $db->query($sql,__FILE__,__LINE__);
		$rs = $db->fetch($result);
		$img->SetTitle("Post/Monat - ".$rs['username']);
		$add_q1 = " WHERE user_id = '$_GET[user_id]' ";
		$add_q2 = " user_id = '$_GET[user_id]' AND ";
	} else {
		$img->SetTitle("Post/Monat - Total");
		$add_q1 = "";
		$add_q2 = "";
	}

	$sql = "
	SELECT
		YEAR(date) as jahr
	FROM
	comments
	".$add_q1."
	GROUP by jahr
	ORDER by jahr ASC";
	$result = $db->query($sql,__FILE__,__LINE__);
	while($rs = $db->fetch($result)) {

		$sql = "
		SELECT
			YEAR( date ) AS jahr,
			MONTH( date ) AS monat,
			count( id ) AS num
		FROM comments
		WHERE
		".$add_q2."
		YEAR(date) = '$rs[jahr]'
		GROUP BY jahr, monat
		ORDER by monat ASC";
		$resulti = $db->query($sql,__FILE__,__LINE__);

		//$img->SetBarColor(0,0,0);

		while($rs = $db->fetch($resulti)) {
			$jahr = substr($rs['jahr'],2);
			$img->AddValue($monate[$rs['monat']],array($rs['num']),$jahre[$rs['jahr']]);
		}
	}

	$img->spit("png");
}

?>
