<?PHP
	require_once($_SERVER['DOCUMENT_ROOT'].'/includes/main.inc.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/apod.inc.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/includes/gallery.inc.php');
if($_GET['pw'] == "schmelzigel") {
	get_spaceweather();
}
if($_GET['pw'] == "osterhase") {
	get_apod();
}
echo spaceweather_ticker();

$url = "http://antwrp.gsfc.nasa.gov/apod/";
	$source = "http://antwrp.gsfc.nasa.gov/apod/astropix.html";
	//$source = "http://antwrp.gsfc.nasa.gov/apod/ap041022.html";
	$file = @file($source);
	if($file) {
		$html = join("",$file);
		$html = strip_tags($html,"<img> <a> <b>");

		//Image file & BIG Image file
		$pattern = "((<(a|A)\s(HREF|href)=\"image/(.*)\")|(<(IMG|img)\s(SRC|src)=\"(.*)\"))";
		preg_match_all($pattern,$html,$out);
		$image_big = $url."image/".$out[4][0];
		foreach ($out[8] as $key => $val){
			if($val != "") {
				$img = $out[8][$key];
			}	
		}
		$img_url = $url.$img;
		
		//desc
		$pattern = "(<b>(.*)</b>)";
		preg_match_all($pattern,$html,$out);
		$image_desc = ltrim($out[1][0]);
		
		//explanation
		$image_expl = substr($html,strpos($html,"Explanation:"),strrpos($html,"Tomorrow's picture:")-strpos($html,"Explanation:"));
		//externe links bauen
		$image_expl = preg_replace("/\/(\n)/i","/",$image_expl);
		$image_expl = preg_replace("/\"(\n)/i","\"",$image_expl);
		$image_expl = preg_replace("/href=(\n)/i","href=",$image_expl);
		$image_expl = str_replace("<b>","",$image_expl);
		$image_expl = preg_replace("/href=\"([^http].*)\"/i","href=\"".$url."\$1\"",$image_expl);

	}	
	
	echo "IMAGE:".extension($img_url)."<br>";
	echo "IMAGE_BIG:".$image_big."<br>";
	echo "DESC:".addslashes($image_desc)."<br>";
	echo "EXPL:".$image_expl;
	
	

	
	
		
?>