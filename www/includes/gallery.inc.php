<?php
/**
 * Gallery Funktionen
 * 
 * Beinhaltet alle Funktionen der Gallery.
 *
 * @author [z]biko
 * @package Zorg
 * @subpackage Gallery
 * @version 3.0
 * @since 1.0 File & functions added
 * @since 2.0 Added code documentations, polished & optimized various functions
 * @since 3.0 09.08.2018 Refactored picPath() & createPic(), added APOD specific specials
 *
 * @TODO MyPic-Markierung von Bildern
 * @TODO Wasserzeichen(?)
 */
/**
 * File includes
 * @include config.inc.php
 * @include colors.inc.php
 * @include forum.inc.php
 * @include util.inc.php
 */
include_once( __DIR__ .'/config.inc.php');
include_once( __DIR__ .'/colors.inc.php');
include_once( __DIR__ .'/forum.inc.php');
include_once( __DIR__ .'/util.inc.php');

/**
 * @const set_time_limit	Maximale Zeit in Sekunden, welche das Script laufen darf
 * @const FTP_UPDIT			FTP-Serveraddress and Directory-Path to Gallery Upload Dir
 * @const DIR				Path to Gallery directory on the server
 * @const UPDIR				Path to the Upload directory on the server
 * @const ZENSUR			If the User is a Member, he can see censored Pics. Otherwise the SQL-Query addition will filter them out.
 */
set_time_limit(600);
define('FTP_UPDIR', 'ftp://zooomclan@zorg.ch/data/gallery/upload/incoming/');
define('DIR', $_SERVER['DOCUMENT_ROOT'].'/../data/gallery/');
define('UPDIR', $_SERVER['DOCUMENT_ROOT'].'/../data/upload/');
define('ZENSUR', ( $user->typ >= USER_MEMBER ? '' : 'AND p.zensur="0"' ));

/**
 * Globals
 * @global array $MAX_PIC_SIZE	The maximum width & height for pictures
 * @global array $THUMBPAGE		The image size for Thumbnail pictures
 */
$MAX_PIC_SIZE = array('picWidth'=>800, 'picHeight'=>800, 'tnWidth'=>150, 'tnHeight'=>150);
$THUMBPAGE = array('width'=>4, 'height'=>3, 'padding'=>10);


// ********************************** LAYOUT FUNCTIONS ***************************************************************************
/**
 * Gallery Hauptseite anzeigen
 * 
 * Zeigt die Gallery-Übersicht mit allen Alben
 * 
 * @author [z]biko
 * @version 1.0
 * @package Zorg
 * @subpackage Gallery
 *
 * @see ZENSUR
 * @param string $state Aktueller Status des Albums, z.B. wenn es gerade bearbeitet wird
 * @param string $error (Fehler-)Meldung, welche auf der Gallery-Seite angezeigt werden soll
 * @global object $db Globales Class-Object mit allen MySQL-Methoden
 * @global object $user Globales Class-Object mit den User-Methoden & Variablen
 * @global string $MAX_PIC_SIZE String der Variable MAX_PIC_SIZE
 * @global string $THUMBPAGE String der Variable THUMBPAGE
 * @return string HTML-Code der Gallery-Seite
 */
function galleryOverview ($state="", $error="") {
	global $db, $user, $MAX_PIC_SIZE, $THUMBPAGE;
	
	$out = '';
	$sql =
		"
		SELECT
			a.*
			, COUNT(p.id) anz
			, UNIX_TIMESTAMP(created) AS created_at
		FROM gallery_albums a, gallery_pics p
		WHERE p.album = a.id ".ZENSUR."
		GROUP BY p.album
		ORDER BY name ASC, created_at DESC
		"
	;
	$e = $db->query($sql, __FILE__, __LINE__, __FUNCTION__);
	$seen = Array();
	$i = 0;
	
	$out .= '<b class="titlebar">Galleries</b><br /><br />';
	
	$out .=
		'<table cellspacing="0" cellpadding="0" style="border-collapse: collapse; border-width:1px; border-style: solid; border-color: #'.BORDERCOLOR.';" width="100%">'
		//.'<tr class="title"><td align="center"colspan="4">Galleries</td></tr><tr>'
	;
	while ($d = mysql_fetch_array($e)) {
		$seen[$i++] = $d['id'];
		$out .= '<td align="center"
		style="border-collapse: collapse; border-width:1px; border-style: solid; border-color: #'.BORDERCOLOR.'; padding: 10px;"
		valign="middle" width="25%">';
		$out .= '<b>';
		if ($user->typ == USER_MEMBER) {
				$out .= '<a href="'.$_SERVER['PHP_SELF'].'?show=editAlbum&albID='.$d['id'].'">';
				$out .= $d['name'];
		$out .= '</a>';
		} else {
			$out .= $d['name'];
		}
	
		$out .= '</b>';
		$out .= '<br />';
		//$out .= '<tr><td align="center" valign="middle" width="'.($MAX_PIC_SIZE[tnWidth]+2*$THUMBPAGE[padding]).'">';
		$out .= getAlbumLinkRandomThumb($d['id']);
		//('.$d['anz'].' Pics)
		//$out .= '</td></tr>';
		//$out .= '</table>';
		$out .= '</td>';
	
		if($i % 4 == 0) {
			$out .= '</tr><tr>';
		}
	}
	
	$out .= '</table></div>';
	
	if ($user->typ == USER_MEMBER) {  // member
		if ($state) echo '<b><font color="green">'.$state.'</font></b> <br/><br/>';
		if ($error) echo '<b><font color="red">'.$error.'</font></b><br/><br/>';
		$out .= '
	   	<br />
	   	<div align="center" width="100%">
	   	<form action="gallery.php?show=editAlbum&albID=0" method="post">
			<input type="submit" class="button" value="   neues Album erstellen   ">
	   	</form>
	   	<br />
		';
	}
	
	$new = '';
	if ($user->typ == USER_MEMBER) {
		$newexists = 0;
		$out .= '<b>Leere Gallerys:</b><br/><br/>';
		$out .= '<table border="0" cellspacing="0" cellpadding="0">';
		$where = "WHERE ";
		foreach ($seen as $key => $val) {
		$where .= "id != $val AND ";
		}
		$where = substr($where, 0, -5);
		$e = $db->query("SELECT * FROM gallery_albums $where", __FILE__, __LINE__, __FUNCTION__);
		while ($d = mysql_fetch_array($e)) {
		$newexists = 1;
		$out .= '<tr>';
		$out .= '<td align="left">- '.$d['name'].' &nbsp; &nbsp; </td>';
		$out .= '<td align="left"><a href="'.$_SERVER['PHP_SELF'].'?show=editAlbum&albID='.$d['id'].'">[EDIT]</a></td>';
		$out .= '</tr>';
		}
		$out .= '</table><br/>';
		if (!$newexists) $new = '';
	}


	return $out;
}

/**
 * Thumbnails anzeigen
 *
 * @package Zorg
 * @subpackage Gallery
 *
 * @param integer $id ID des Albums von welchem die Thumbnails angezeigt werden sollen
 * @param integer $page Aktuelle Seite des Albums, deren Thumbnails angezeigt werden sollen
 * @see $THUMBPAGE, $MAX_PIC_SIZE, ZENSUR
 */
function albumThumbs ($id, $page=0) {
	global $db, $THUMBPAGE, $MAX_PIC_SIZE, $user;
	
	if (!$id) user_error("Missing Parameter <i>id</i> ", E_USER_ERROR);
	
	if (!$page) $page = 0;
	
	$pagepics = $THUMBPAGE['width'] * $THUMBPAGE['height'];
	$e = $db->query("SELECT count(id) anz FROM gallery_pics p WHERE album=$id ".ZENSUR." GROUP BY album", __FILE__, __LINE__, __FUNCTION__);
	$d = mysql_fetch_array($e);
	$anz = $d['anz'];
	
	$e = $db->query(
		"SELECT g.*, e.name eventname
		FROM gallery_albums g
		LEFT JOIN events e ON e.gallery_id=g.id
		WHERE g.id=$id", __FILE__, __LINE__, __FUNCTION__);
	$d = mysql_fetch_array($e);
	echo '<br /><table width="80%" align="center"><tr>
	<td align="center" class="bottom_border"><b class="titlebar">'
	.($d['eventname'] ? $d['eventname'] : $d['name'])
	.'</b></div></td></tr></table><br /><br />';
	
	$e = $db->query("SELECT * FROM gallery_pics p WHERE album=$id ".ZENSUR." ORDER BY p.id LIMIT ".($page*$pagepics).", $pagepics", __FILE__, __LINE__, __FUNCTION__);
	echo '<table cellspacing="0" cellpadding="0" style="border-collapse:collapse">';
	$hgt = $MAX_PIC_SIZE['tnHeight'] + 2 * $THUMBPAGE['padding'];
	$wdt = $MAX_PIC_SIZE['tnWidth'] + 2 * $THUMBPAGE['padding'];
	$rows = 0;
	while ($d = mysql_fetch_array($e)) {
		$comments = Thread::getNumPosts('i', $d['id']);
		$unread = Thread::getNumUnread('i', $d['id']);
	
		if ($rows==0) echo '<tr>';
		echo '<td class="border" cellpadding="'.$THUMBPAGE['padding'].'" height="'.$hgt.'", width="'.$wdt.'" style="text-align:center" valign="middle">'
		.'<a href="'.$_SERVER['PHP_SELF'].'?show=pic&picID='.$d['id'].'">'.($d['name']?$d['name'].'<br />':'').'<img border="0" src="'.imgsrcThum($d['id']).'">';
	
		if ($comments) {
			echo "<br />$comments Comments ";
			if ($unread) echo "<br />($unread unread) ";
		}
	
		echo '</a></td>';
		if (++$rows == $THUMBPAGE['width']) {
		$rows = 0;
		echo '</tr>';
		}
	}
	
	for ($i=$rows; $i<$THUMBPAGE['width']; $i++) {
		echo '<td>&nbsp;</td>';
	}
	if ($rows) echo '</tr>';
	echo '</table><br /><font size="4">Seite: ';
	
	
	for ($i=0; $i<$anz/$pagepics; $i++) {
		if ($page==$i) {
		echo '<b>['.($i+1).']</b> &nbsp; ';
		}else{
		echo '<a href="'.$_SERVER['PHP_SELF'].'?show=albumThumbs&albID='.$id.'&page='.$i.'">'.($i+1).'</a> &nbsp; ';
		}
	}
	
	echo '</font><br /><br />';
	
	if ($user->typ == USER_MEMBER)
	echo "<a href='/gallery.php?albID=$id&show=editAlbum'>edit Album</a><br /><br />";
}


/**
 * Bild anzeigen
 *
 * @author Zorg, IneX
 * @date 21.10.2013
 * @version 2.0
 * @since 1.0
 * @package Zorg
 * @subpackage Gallery
 *
 * @param integer $id ID des Albums von welchem die Thumbnails angezeigt werden sollen
 * @param integer $page Aktuelle Seite des Albums, deren Thumbnails angezeigt werden sollen
 * @see $THUMBPAGE, $MAX_PIC_SIZE, ZENSUR
 */
function pic ($id) {
	global $user, $db, $THUMBPAGE;
	
	if (!$id) user_error('Missing Parameter <i>id</i>', E_USER_ERROR);
	
	$e = $db->query('SELECT *, UNIX_TIMESTAMP(pic_added) as timestamp FROM gallery_pics WHERE id='.$id, __FILE__, __LINE__, __FUNCTION__);
	$cur = mysql_fetch_array($e);
	
	if($cur == false) {
		echo 'Bild '.$id.' existiert nicht!';
		exit;
	}
	
	$e = $db->query('SELECT * FROM gallery_pics p WHERE album='.$cur['album'].' AND id<'.$id.' '.ZENSUR.' ORDER BY id DESC LIMIT 0,1', __FILE__, __LINE__, __FUNCTION__);
	$last = mysql_fetch_array($e);
	
	$e = $db->query('SELECT * FROM gallery_pics p WHERE album='.$cur['album'].' AND id>'.$id.' '.ZENSUR.' ORDER BY id ASC LIMIT 0,1', __FILE__, __LINE__, __FUNCTION__);
	$next = mysql_fetch_array($e);
	
	$e = $db->query("SELECT a.*, count(p.id) anz, e.name eventname
				FROM gallery_pics p, gallery_albums a
							LEFT JOIN events e ON e.gallery_id = a.id
				WHERE p.id<='$id' AND p.album=$cur[album] AND a.id=$cur[album] ".ZENSUR."
				GROUP BY album", __FILE__, __LINE__, __FUNCTION__);
	$d = mysql_fetch_array($e);
	$page = floor($d['anz'] / ($THUMBPAGE['width'] * $THUMBPAGE[height]));
	echo '<br /><table width="80%" align="center"><tr>
	<td align="center" class="bottom_border"><b class="titlebar">'
	.($d['eventname'] ? $d['eventname'] : $d['name'])
	.'</b></div></td></tr></table><br /><br />';
	
	if ($cur['zensur'] && $user->typ != USER_MEMBER) {
		echo '<b><font color="red">Access denied for this picture</font></b><br /><br />';
		return;
	}
	
	if ($_GET['editFotoTitle'] && $user->typ == USER_MEMBER) {
		echo '<form method="post" action="'.$_SERVER['PHP_SELF'].'?do=editFotoTitle&'.url_params().'">';
			echo 'Foto-Titel: <input name="frm[name]" size="30" class="text" value="'.$cur['name'].'"> ';
			echo '<input type="submit" value=" OK " class="button">';
		echo "</form>";
	} else {
		if (!$cur['name']) {
			echo '<form method="post" action="'.$_SERVER['PHP_SELF'].'?do=editFotoTitle&'.url_params().'">';
				echo 'Foto-Titel: <input name="frm[name]" size="30" class="text">';
				echo '<input type="submit" value=" OK " class="button">';
			echo "</form>";
		}elseif ($cur['name']) {
			echo '<h2>'.$cur['name'].'</h2>';
		}
	
		if ($cur['name'] && $user->typ == USER_MEMBER) {
			echo '<small><a href="'.$_SERVER['PHP_SELF'].'?editFotoTitle=1&'.url_params().'">[edit Foto-Titel]</a></small>';
		}
	}
	
	//$exif_data = exif_read_data(picPath($cur[album], $id, '.jpg'), 1, true); PHP wurde anscheinend ohne EXIF-Support kompiliert
	//echo "<p>Bild erstellt am ".date('d. F Y H:i', filemtime(picPath($cur[album], $id, '.jpg')))."</p>";
	$pic_filepath = picPath($cur['album'], $id, '.jpg');
	$exif_data = exif_read_data($pic_filepath, 1, true);
	if ($exif_data['FILE.FileDateTime'] != false) {
		echo '<p>Bild erstellt am '.date('d. F Y H:i', $exif_data['FILE.FileDateTime']).'</p>';
	} elseif ($cur['album'] == APOD_GALLERY_ID && !empty($cur['timestamp'])) { // APOD Special: use pic_added from database, instead of filemtime
		echo '<p>Bild von '.datename($cur['timestamp']).'</p>';
	} else {
		echo '<p>Bild Upload von '.datename(filemtime($pic_filepath)).'</p>';
	}
	
	// Image Rotating... deaktiviert weil doRotatePic()-Script das Bild nicht dreht.
	/*if ($user->typ == USER_MEMBER) {
		echo "<form method='post' action='$_SERVER['PHP_SELF']?do=doRotatePic&".url_params()."'><p>";
			echo "<input type='radio' class='text' name='rotatedir' value='left' checked /> 90&deg; links&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
			echo "<input type='radio' class='text' name='rotatedir' value='right' /> 90&deg; rechts&nbsp;&nbsp;";
			echo "<input type='submit' class='button' name='rotatebutton' value='Bild drehen' /></p>";
		echo "</form>";
	}*/
	
	
	## PIC SCORE FORMULAR ##
	if ($user->typ >= USER_USER) {
		if (hasVoted($user->id, $cur['id'])) {
			$anz_votes = getNumVotes($cur['id']);
			$votes = (($anz_votes > 1) || ($anz_votes == 0)) ? $anz_votes." Votes" : $anz_votes." Vote";
			echo '<p>Bild Note: '.getScore($cur['id']).' <small>('.$votes.')</small></p>';
		} else {
			echo '
			<br /><form action="'.$_SERVER['PHP_SELF'].'?do=benoten&amp;'.url_params().'" method="post" name="f_benoten">
			<input name="picID" type="hidden" value="'.$cur['id'].'">
			<input name="score" onClick="document.f_benoten.submit();" type="radio" value="1">1
			<input name="score" onClick="document.f_benoten.submit();" type="radio" value="2">2
			<input name="score" onClick="document.f_benoten.submit();" type="radio" value="3">3
			<input name="score" onClick="document.f_benoten.submit();" type="radio" value="4">4
			<input name="score" onClick="document.f_benoten.submit();" type="radio" value="5">5
			<input name="score" onClick="document.f_benoten.submit();" type="radio" value="6">6
			<input class="button" type="submit" value="benoten">
			</form><br />
			';
		}
	} else {
		$anz_votes = getNumVotes($cur['id']);
		$votes = (($anz_votes > 1) || ($anz_votes == 0)) ? $anz_votes." Votes" : $anz_votes." Vote";
		echo '<p>Bild Note: '.getScore($cur['id']).' <small>('.$votes.')</small></p>';
	}
	
	
	echo '<div align="center"><table border="0" cellspacing="0" cellpadding="0" '.$cur['picsize'].'>';
	
	echo '<tr style="font-size: 20px; font-weight: bold;"><td align="left" width="30%">';
	if ($last) echo '<a href="'.$_SERVER['PHP_SELF'].'?show=pic&picID='.$last['id'].'">previous</a>';
	else echo '&lt;- last';
	echo '</td><td style="text-align:center"><a href="'.$_SERVER['PHP_SELF'].'?show=albumThumbs&albID='.$cur['album'].'&page='.$page.'">overview</a></td>';
	echo '<td style="text-align:right" width="30%">';
	if ($next) {
		echo '<a href="'.$_SERVER['PHP_SELF'].'?show=pic&picID='.$next['id'].'">next</a>';
	} else {
		echo 'next';
	}
	echo '</td></tr>';
	/* die DIVs machen mich verrückt... damn
	echo '<tr><td colspan="3"><div style="position:static; width:800; height:600;"><div name="thepic" style="position:absolute;"><img border="0" src="'. imgsrcPic($id). '"></div>';
	getUsersOnPic($cur['id']); // MyPic Markierungen laden
	echo '</div></td></tr>';
	*/
	
	echo '<tr><td colspan="3">';
	
	// Wenn User eingeloggt & noch nicht auf Bild markiert ist, Formular anzeigen...
	if ($user->typ == USER_MEMBER && !checkUserToPic($user->id, $id))
	{
		printf('
		<form action="%1$s" method="post" onsubmit="return markAsMypic()">
			<input type="hidden" name="picID" value="%2$s" />
			<input type="image" name="mypic" src="%3$s" alt="Bild als MyPic markieren" title="Bild markieren?" />
		</form>'
				,$_SERVER['PHP_SELF'].'?do=mypic&amp;'.url_params()
				,$id
				,imgsrcPic($id)
		);
	// ...sonst Bild normal ohne Markierungs-Formular ausgeben (auch für Nicht Eingeloggte)
	} else {
		echo '<img border="0" src="'. imgsrcPic($id). '">';
	}
	
	echo '</td></tr>';
	
	/*echo '<tr><td clspan="3">';
	getUsersOnPic($cur['id']);  // MyPic Markierungen laden
	while($i < count($theusers))
		{
			echo $theusers[$i].', ';
		}
	echo '</td></tr>';
	*/
	echo '<tr><td colspan="3">';
	
	// Commenting (Das hier reicht schon :-) ) ------------------------------
	Forum::printCommentingSystem('i', $id);
	// End Commenting -------------------------------------------------------
	
	echo '</td></tr></table></div>';
	
	if ($user->typ == USER_MEMBER) { // member
		echo '<table align="center" class="border" cellspacing="0" cellpadding="10"><tr><td valign="top"><br />';
		if ($cur['zensur']) {
			echo '<font color="red">Bild ist zensiert</font>';
			$val = "Zensur aufheben";
		}else{
			echo '<font color="green">Bild ist nicht zensiert</font>';
			$val = "zensieren";
		}
		echo '</td><td valign="top"><br /><form action="'.$_SERVER['PHP_SELF'].'?do=zensur&show=pic&picID='.$cur['id'].'" method="post">'
		.'<input type="submit" class="button" value="'.$val.'"></form></td>';
	
		echo '<td valign="top" style="text-align:right" width="250"><br />'
		.'<form action="'.$_SERVER['PHP_SELF'].'?do=delPic&show=albumThumbs&albID='.$cur['album'].'" method="post">'
		.'<input type="submit" class="button" value="l&ouml;schen"><input type="hidden" name="picID" value="'.$cur['id'].'">'
		.'</form></td></tr></table>';
	}
}


// ====================================================
// |                   Pic Rating
// ====================================================
function getScore($pic_id) {
	global $db;

	$sql =
		"SELECT AVG(score) as score"
		." FROM gallery_pics_votes"
		." WHERE pic_id = ".$pic_id
	;
	$result = $db->query($sql, __FILE__, __LINE__, __FUNCTION__);
	$rs = $db->fetch($result, __FILE__, __LINE__, __FUNCTION__);

	return round($rs['score'], 1);
}

function getNumVotes($pic_id) {
	global $db;

	$sql =
		"SELECT pic_id
		FROM gallery_pics_votes
		WHERE pic_id = ".$pic_id
	;
	$result = $db->query($sql, __FILE__, __LINE__, __FUNCTION__);

	return $db->num($result, __FILE__, __LINE__, __FUNCTION__);
}

function hasVoted($user_id, $pic_id) {
	global $db;

	$sql =
		"SELECT *"
		." FROM gallery_pics_votes"
		." WHERE pic_id = '".$pic_id."' AND user_id =".$user_id
	;
	$result = $db->query($sql, __FILE__, __LINE__, __FUNCTION__);

	return $db->num($result, __FILE__, __LINE__, __FUNCTION__);
}
// ====================================================
// |                 END Pic Rating
// ====================================================



function editAlbum ($id, $done="", $state="", $error="", $frm="")
{
	global $db;
	
	if (!$frm) $frm = array();
	
	if ($id) {
		$e = $db->query("SELECT * FROM gallery_albums WHERE id='$id'", __FILE__, __LINE__, __FUNCTION__);
		$frm = mysql_fetch_array($e);
	
		echo '<table width="80%" align="center"><tr>
		<td align="center" class="bottom_border"><b class="titlebar">Album #'.$id.' bearbeiten</b></div></td></tr></table><br /><br />';
	}else {
		echo '<table width="80%" align="center"><tr>
		<td align="center" class="bottom_border"><b class="titlebar">Neues Album erstellen</b></div></td></tr></table><br /><br />';
		$id = 0;
	}
	
	
	if ($done == "editAlbum" || $done == "delAlbum") {
		$editError = $error;
		$editState = $state;
	}else {
		$uploadError = $error;
		$uploadState = $state;
	}
	
	if ($editState) echo "<font color='green'><b>$editState</b></font><br /><br />";
	if ($editError) echo "<font color='red'><b>$editError</b></font><br /><br />";
	
	echo "<a href='/gallery.php?albID=$id&show=albumThumbs'>go to Album</a><br /><br />";
	?>
	<table class="border" cellspacing="3">
	<form action="<?=$_SERVER['PHP_SELF']?>?show=editAlbum&albID=<?=$id?>&do=editAlbum" method="post">
		<?
		?>
		<tr>
		<td align="left">ID: </td>
		<td align="left"><?=$id?>
		</tr>
		<tr>
		<td align="left">Name: </td>
		<td align="left"><input type="text" class="text" size="50" name="frm['name']" value="<?=$frm['name']?>"></td>
		</tr>
		<tr>
		<td colspan='2' align="center">
			<br />
			<input class="button" type="submit" value="   OK   ">
			<br /><br />
		</td>
		</tr>
	</form>
	</table>
	<br />
	
	
	<?
	if ($id) {
		?>
		<table class="border"><tr><td>
		<form <?='action="'.$SERVER['PHP_SELF'].'?show=editAlbum&albID='.$id.'&do=delAlbum"'?> method="post">
		Album l&ouml;schen: <br />(Gib <i>OK</i> ins Feld ein, um zu best&auml;tigen)<br /><br />
			<input class="text" name="del" value="" size="4"> &nbsp;
			<input type="submit" class="button" value="   l&ouml;schen   ">
		</form>
		</td></tr></table>
		<br />
		<?
	
	
		echo '<br/><br/><table width="80%" align="center"><tr>
		<td align="center" class="bottom_border"><b class="titlebar">Picture Upload</b></div></td></tr></table><br /><br />';
	
		if ($uploadState) echo "<font color='green'><b>$uploadState</b></font><br /><br />";
		if ($uploadError) echo "<font color='red'><b>$uploadError</b></font><br /><br />";
		?>
	
		<table class="border"><tr><td>
		<form action="<?=$_SERVER['PHP_SELF']?>?show=editAlbum&albID=<?=$id?>&do=mkUploadDir" method="post">
		Upload-Ordner erstellen (in /data/gallery/upload/):<br /><br />
		<input type="text" class="text" name="frm[folder]" value="<?=$frm['folder']?>"> &nbsp; &nbsp;
		<input type="submit" class="button" value="   erstellen   ">
		</form>
		</td></tr></table>
		<br />
	
		<?
		$d = opendir(UPDIR);
		$fileoptions = array();
		$i = 0;
		while (false !== ($f = readdir($d))) {
		if (is_dir(UPDIR.$f) && $f!="." && $f!="..") {
			$f .= "/";
			$anz = countFiles(UPDIR.$f);
			if ($anz == -1) {
			$count = "keine Rechte";
			}elseif ($anz == 0) {
			$count = "leerer Ordner";
			}else{
			$sub = opendir(UPDIR.$f);
			$cpics = 0;
			$cfiles = 0;
			while (false !== ($subf = readdir($sub))) {
				if (isPic(UPDIR.$f.$subf)) {
				$cpics++;
				}elseif (is_file(UPDIR.$f.$subf)) {
				$cfiles++;
				}
			}
			if (!$cfiles) {
				$count = $cpics." Bilder";
			}else{
				$count = $cpics." Bilder, ".$cfiles." andereFiles";
			}
			closedir($sub);
			}
			$fileoptions[$i++] = '<option value="'.$f.'">'.$f.' &nbsp; ('.$count.')</option>';
		}
		}
		closedir($d);
		sort($fileoptions);
	
		?>
	
		<table class="border"><tr><td>
		<form <?='action="'.$_SERVER['PHP_SELF'].'?show=editAlbum&albID='.$id.'&do=delUploadDir"'?> method="post">
		Upload-Ordner l&ouml;schen:<br /><br />
		<select size="1" class="text" name="frm[folder]">
			<?
			for ($i=0; $i<sizeof($fileoptions); $i++) {
				echo $fileoptions[$i];
			}
			?>
		</select> &nbsp; &nbsp;
		<input type="submit" class="button" value="   l&ouml;schen   ">
		</form>
		</td></tr>
		<tr><td style="text-align:right">
		<a <?='href="'.$_SERVER['PHP_SELF'].'?show=editAlbum&albID='.$id.'"'?>>--> Refresh Ordnerliste</a>
		</td></tr></table>
		<br />
	
		<table class="border"><tr><td align="left" width="450">
		<form <?='action="'.$_SERVER['PHP_SELF'].'?show=editAlbum&albID='.$id.'&do=upload"'?> method="post" enctype="multipart/form-data">
		Lade die Pics (<b>.jpg oder .gif</b>) per FTP in ein Upload-Ordner. Achte darauf, dass du den Pics die
		Rechte 0664 gibst.
		(<?='<a target="_new" href="'.FTP_UPDIR.'">'.FTP_UPDIR.'</a>'?>). <br /><br />
		W&auml;hle den Ordner hier aus, um die Pics zu indizieren: <br /><br />
		<input type="checkbox" checked name="frm[delPics]" value="1">
		Erfolgreich indizierte Bilder aus Upload-Ordner l&ouml;schen<br />
		<input type="checkbox" name="frm[delFiles]" value="1">
		nicht indizierte Files aus Upload-Ordner l&ouml;schen
		<br /><br />
		<select size="1" class="text" name="frm[folder]">
			<?
			for ($i=0; $i<sizeof($fileoptions); $i++) {
			echo $fileoptions[$i];
			}
			?>
		</select> &nbsp; &nbsp;
		<input class="button" type="submit" value="   upload   "><br />
		</form>
		</td></tr>
		<tr><td style="text-align:right">
		<a <?='href="'.$_SERVER['PHP_SELF'].'?show=editAlbum&albID='.$id.'"'?>>--> Refresh Ordnerliste</a>
		</td></tr></table>
		<br />
		<?
	}
}


// ************************************** ACTION FUNCTIONS *************************************************************************

function doEditAlbum ($id, $frm)
{
	global $db;
	
	// function errors
	if (!is_array($frm)) user_error("Wrong Parameter-type for <i>frm</i>", E_USER_ERROR);
	
	$frm['name'] = htmlspecialchars($frm['name'], ENT_NOQUOTES);
	
	// save data
	if (!$id) {
		$id = $db->insert("gallery_albums", $frm, __FILE__, __LINE__, __FUNCTION__);
	}else{
		$db->update("gallery_albums", $id, $frm, __FILE__, __LINE__, __FUNCTION__);
	}
	
	return array('id'=>$id, 'state'=>"Database updated", 'frm'=>$frm);
}


function doUpload($id, $frm)
{
	global $db, $MAX_PIC_SIZE;
	
	if (!$id) user_error("Missing Parameter <i>id</i>", E_USER_ERROR);
	if (!is_array($frm)) user_error("Wrong Parameter-type <i>frm</i>", E_USER_ERROR);
	
	if (countFiles(UPDIR.$frm[folder]) == 0) return array("error"=>"Gew&auml;hlter Ordner '$frm[folder]' ist leer");
	if (countFiles(UPDIR.$frm[folder]) == -1) return array('error'=>"Keine Rechte auf den Ordner '$frm[folder]'");
	
	if (!is_dir(DIR.$id)) mkdir(DIR.$id, 0775); //system("mkdir ".DIR.$id." -m 0775");
	//system("chmod 0775 ".UPDIR.$frm[folder]);
	chmod(UPDIR.$frm[folder], 0775);
	
	$directory = opendir(UPDIR.$frm[folder]);
	$notDone = "";
	$done = "";
	$error = "";
	$picSize = "";
	$tnSize = "";
	while (false !== ($file = readdir ($directory))) {
		// checks
		if ($file=="." || $file=="..") continue;
	
		if (!isPic(UPDIR.$frm[folder].$file)) {
		$notDone .= "- $file (ist kein g&uuml;ltiges Bild)<br />";
		if ($frm[delFiles]) {
			if (!@unlink(UPDIR.$frm[folder].$file)) $error .= "- $file konnte nicht gel&ouml;scht werden<br />";
		}
		continue;
		}
	
		// writing DB
		$picid = $db->insert("gallery_pics", array("album"=>$id, "extension"=>extension($file)), __FILE__, __LINE__, __FUNCTION__);
	
		// create pic
		$t = createPic(UPDIR.$frm[folder].$file, picPath($id, $picid, extension($file)), $MAX_PIC_SIZE[picWidth], $MAX_PIC_SIZE[picHeight]);
		if ($t[error]) {
		$db->query("DELETE FROM gallery_pics WHERE id=$picid");
		$notDone .= "- $file ";
		if ($frm[delFiles]) {
			if (!@unlink(UPDIR.$frm[folder].$file)) $error .= "- $file konnte nicht gel&ouml;scht werden<br />";
		}
	
		$notDone .= "(".$t[error].")<br />";
		continue;
		}else{
		$picSize = "width=".$t[width]." height=".$t[height];
		}
	
		// create thumbnail
		$t = createPic(UPDIR.$frm[folder].$file, tnPath($id, $picid, extension($file)), $MAX_PIC_SIZE[tnWidth], $MAX_PIC_SIZE[tnHeight]);
		if ($t == -1) {
		$db->query("DELETE FROM gallery_pics WHERE id=$picid");
		unlink(picPath($id, $picid, extension($file)));
		$notDone .= "- $file (keine Rechte) <br />";
		if ($frm[delFiles]) {
			if (!@unlink(UPDIR.$frm[folder].$file)) $error .= "- $file konnte nicht gel&ouml;scht werden<br />";
		}
		continue;
		}else{
		$tnSize = "width=".$t[width]." height=".$t[height];
		}
	
		// update sizes in DB
		$db->query("UPDATE gallery_pics SET tnsize='$tnSize', picsize='$picSize' WHERE id=$picid", __FILE__, __LINE__, __FUNCTION__);
	
		// del uploaded pic, if requested
		if ($frm[delPics]) {
		if (!@unlink(UPDIR.$frm[folder].$file)) $error .= "- $file konnte nicht gel&ouml;scht werden<br />";
		}
	
		$done .= "- $file <br />";
	}
	closedir($directory);
	
	// delete directory if empty
	if (countFiles(UPDIR.$frm[folder]) == 0) {
		if (!@rmdir(UPDIR.$frm[folder]))
		$error .= "- Upload-Ordner ".UPDIR.$frm[folder]." konnte nicht gel&ouml;scht werden <br />";
	}
	
	if ($notDone) $notDone = "Folgende Files konnten nicht indiziert werden:<br />".$notDone;
	if ($done) {
		$done = "Folgende Files wurden indiziert:<br />".$done;
	}else{
		$notDone = "Es konnten keine Files indiziert werden! <br /><br />".$notDone;
	}
	
return array('error'=>$notDone."<br />".$error, 'state'=>$done);
}


function doDelAlbum ($id, $del)
{
	global $db;
	
	if (!$id) user_error("Missing Parameter <i>$id</i>", E_USER_ERROR);
	
	if (strtolower($del) != "ok") {
		return array('show'=>"editAlbum", 'error'=>"L&ouml;schen wurde nicht best&auml;tigt <br/>Album wurde nicht gel&ouml;scht");
	}
	
	$db->query("DELETE FROM gallery_pics WHERE album='$id'", __FILE__, __LINE__, __FUNCTION__);
	$db->query("DELETE FROM gallery_albums WHERE id='$id'", __FILE__, __LINE__, __FUNCTION__);
	
	if (!delDir(DIR.$id)) return array('show'=>"", 'error'=>"Verzeichnis <i>".DIR.$id."</i> konnte nicht gel&ouml;scht werden.");
	
	return array('show'=>"", 'state'=>"Album wurde gel&ouml;scht");
}


function doZensur ($picID)
{
	global $db;
	if (!$picID) user_error("Missing Parameter <i>picID</i>", E_USER_ERROR);
	$e = $db->query("SELECT zensur FROM gallery_pics WHERE id='$picID'", __FILE__, __LINE__, __FUNCTION__);
	$d = mysql_fetch_array($e);
	if ($d[zensur]) {
		$db->query("UPDATE gallery_pics SET zensur='0' WHERE id='$picID'", __FILE__, __LINE__, __FUNCTION__);
		Thread::setRights('i', $picID, USER_ALLE);
	}else{
		$db->query("UPDATE gallery_pics SET zensur='1' WHERE id='$picID'", __FILE__, __LINE__, __FUNCTION__);
		Thread::setRights('i', $picID, USER_MEMBER);
	}
}


// ====================================================
// |                  MyPic markierung
// ====================================================
/**
 * Bild als MyPic markieren
 * 
 * Ein User kann ein Bild als MyPic markieren, womit er
 * quasi sagt, dass er auf dem Bild abgebildet ist. Dazu
 * werden auch die X- & Y-Koordinaten seines Klicks gespeichert
 * (= detailliertere Anzeige, wo auf dem Bild der User
 * zu sehen ist).
 *
 * @author IneX
 * @date 13.08.2007
 * @version 0.9
 * @since 2.0
 * @package Zorg
 * @subpackage Gallery
 *
 * @param integer $pic_id ID des betroffenen Bildes
 * @param integer $pic_x X-Koordinaten wo der User geklickt hat
 * @param integer $pic_y Y-Koordinaten wo der User geklickt hat
 * @global object $db Globales Class-Object mit allen MySQL-Methoden
 * @global object $user Globales Class-Object mit den User-Methoden & Variablen
 */
function doMyPic($pic_id, $pic_x, $pic_y) {
	global $db, $user;
	
	if (!$pic_id) user_error("Missing Parameter <i>picID</i>", E_USER_ERROR);
	
	// Sicherstellen dass nur eingeloggte markieren
	if ($user->typ >= USER_USER) {
		$sql =
			"
			REPLACE INTO
				gallery_pics_users 
				(pic_id, user_id, pos_x, pos_y, datum)
			VALUES (
				$pic_id,
				".$user->id.",
				$pic_x,
				$pic_y,
				now()
			)";
				
		$db->query($sql, __FILE__, __LINE__, __FUNCTION__);
			
		// Activity Eintrag auslösen (ausser bei der Bärbel)
		if ($user->id != 59) { Activities::addActivity($user->id, 0, 'hat sich auf <a href="'.$_SERVER['PHP_SELF'].'?show=pic&picID='.$pic_id.'">diesem Bild</a> markiert.<br/><br /><a href="'.$_SERVER['PHP_SELF'].'?show=pic&picID='.$pic_id.'"><img src="'.imgsrcThum($pic_id).'" /></a>', 'i'); }
	} else {
		user_error("Das dörfsch DU nöd - isch nur für igloggti User!", E_USER_ERROR);
	}
}

	
/* Bild markieren 
 *
 * sets the connection between a user and a picture
 * @author keep3r
 * @date 02.04.2007
 * @since 1.5
 * @deprecated 2.0
 * @see doMyPic
 */
/*function doMark ($picID) {
	global $db, $user;
	if (!$picID) user_error("Missing Parameter <i>picID</i>", E_USER_ERROR);

	$sql = "SELECT * FROM gallery_user WHERE user_id = '$user->id' AND pic_id = '$picID'";
	$query = $db->query($sql);
  	$result = $db->fetch($query);
	
  	//delete
	if ($result) {
		$sql = "DELETE FROM gallery_user WHERE user_id = '$user->id' AND pic_id = '$picID'";
		$query = $db->query($sql);
		$result = $db->fetch($query);
	
	//insert
	} else {
		$data = array($user->id, $picID);
		$sql = "INSERT INTO gallery_user (user_id, pic_id) VALUES ('$user->id', '$picID')";
		$query = $db->query($sql);
		$result = $db->fetch($query);
	}
}*/


/**
 * Check User<-->Bild Verknüpfung
 * 
 * Prüft ob ein Benutzer bereits auf einem bestimmten Bild markiert wurde
 * 
 * @author keep3r, IneX
 * @date 19.08.2008
 * @version 2.0
 * @since 2.0
 * @package Zorg
 * @subpackage Gallery
 *
 * @param integer $picID ID des betroffenen Bildes
 * @global object $db Globales Class-Object mit allen MySQL-Methoden
 *
 * @return boolean Gibt true/false zurück, je nachdem ob User<->Bild Verknüpfung gefunden wurde
 */
function checkUserToPic($userID, $picID)
{
	global $db;
	
	$sql = "SELECT * FROM gallery_pics_users WHERE user_id = '$userID' AND pic_id = '$picID'";
	$result = $db->query($sql, __FILE__, __LINE__, __FUNCTION__);
	//return $db->fetch($query);
	
	return ($db->num($result) > 0 ? true : false);
}


/**
 * Markierte User holen
 * 
 * Holt die Benutzer, welche auf einem bestimmten Bild markiert wurden
 * 
 * @author IneX
 * @date 19.08.2008
 * @version 1.0
 * @since 2.0
 * @package Zorg
 * @subpackage Gallery
 *
 * @param integer $picID ID des betroffenen Bildes
 * @global object $db Globales Class-Object mit allen MySQL-Methoden
 * @return array Gibt ein Array mit allen Usern aus, welche auf dem Bild markiert sind
 */
function getUsersOnPic($pic_id) {
	global $db, $user;
	$usersonpic = array();
	$html = '';
	
	$sql =
		"SELECT *
		FROM gallery_pics_users
		WHERE pic_id = ".$pic_id
	;
	$result = $db->query($sql, __FILE__, __LINE__, __FUNCTION__);
	
	while ($mp = $db->fetch($result)) {
		array_push($usersonpic, $user->link_userpage($mp[user_id], FALSE));
		/* für DIV basierte positionierung/Ausgabe:
		$html .= '
		<div name="'.$user->id2user($mp[user_id], FALSE).'" style="position:absolute; left:'.$mp[pos_x].'; top:'.$mp[pos_y].'; z-index:'.$mp['id'].'">'.$user->id2user($mp[user_id], FALSE).'<div>
		';*/
	}
	
	return $usersonpic;
	//echo $html;
}


/**
 * Alle Bilder eines Users
 * 
 * Markierte Bilder eines bestimmten Benutzers ausgeben
 *
 * @author IneX <IneX@gmx.net>
 * @date 18.10.2013
 * @version 1.0
 * @since 2.0
 * @package Zorg
 * @subpackage Gallery
 * 
 * @param integer $userid ID des Users dessen Bilder angezeigt werden sollen
 * @param integer $limit Maximale Anzahl von Bildern
 * @global object $db Globales Class-Object mit allen MySQL-Methoden
 * @global object $user Array mit allen User-Variablen 
 * @return array Gibt ein Array mit allen Usern aus, welche auf dem Bild markiert sind
 */
function getUserPics($userid, $limit=1)
{
	global $db, $user;
	
	$html_out = '';
	$i = 1;
	$table_style = 'border-collapse: collapse; border-width:1px; border-style: solid; border-color: #CBBA79; text-align: center;';
	$td_style = 'border-collapse: collapse; border-width:1px; border-style: solid; border-color: #CBBA79; padding: 10px;';
	
	if ($userid > 0) {
		
		$sql =
			"
			SELECT *
			FROM gallery_pics_users
			WHERE user_id=$userid
			ORDER BY id ASC"
			.($limit > 0 ? " LIMIT ".$limit : ""); // LIMIT only when LIMIT Parameter given; 0 = all pics
		;
		$result = $db->query($sql, __FILE__, __LINE__, __FUNCTION__);
		
		$html_out .=
				'<table style="'.$table_style.'" width="100%">'
				.'	<thead>'
				.'	<tr><th colspan="4">'.$user->id2user($userid, FALSE).'\'s Pics</th></tr>'
				.'	</thead>';
		
		while($rs = $db->fetch($result)) {
			$img_id = $rs['pic_id'];
			$img_name = imgName($img_id);
			$file = imgsrcThum($img_id);
			
			if ($i == 1) {
				$html_out .= '<tr>';
			}
			
			$html_out .=
					'	<td style="'.$td_style.'">'
					.'		<a href="/gallery.php?show=pic&picID='.$img_id.'">'
			;
			
			$html_out .=
					'		<img border="0" src="'.$file.'" /><br />'
			;
			if ($img_name) {
				$html_out .= $img_name.'</a>';
			}
			
			$html_out .= '	</td>';
			
			$i++;
			
			if ($i == 4) {
				$html_out .='</tr>';
				$i = 1;
			}
		}
		
		$html_out .= '</table>';
		
		return $html_out;
		
	}
}
/*====================================================
*|                    END MyPic
*====================================================*/
 


/**
 * Bild benoten
 * 
 * Benotet ein Bild mit einer vom User gewählten Score (1-5)
 * 
 * @author IneX
 * @version 1.0
 * @since 1.5
 * @package Zorg
 * @subpackage Gallery
 *
 * @param integer $pic_id ID des betroffenen Bildes
 * @param integer $score Bewertung (1-5) welche der User dem Bild gegeben hat
 * @global object $db Globales Class-Object mit allen MySQL-Methoden
 * @global object $db Globales Class-Object mit den User-Methoden & Variablen
 */
function doBenoten($pic_id, $score) {
	global $db, $user;
	
	if (!$pic_id) user_error("Fehlender Parameter <i>pic_id</i>", E_USER_ERROR);
	if (!$score) user_error("Fehlender Parameter <i>score</i>", E_USER_ERROR);
	
	$sql = "
		REPLACE INTO gallery_pics_votes (pic_id, user_id, score)"
		." VALUES ("
		.$pic_id.', '
		.$user->id.', '
		.$score
		.")"
	;
	
	$db->query($sql, __FILE__, __LINE__, __FUNCTION__);
	//header("Location: ".base64_decode($_POST['url']));
	//return array('state'=>"Pic $pic_id benotet");
	
	// Activity Eintrag auslösen (ausser bei der Bärbel)
	if ($user_id != 59) { Activities::addActivity($user->id, 0, 'hat <a href="'.$_SERVER['PHP_SELF'].'?show=pic&picID='.$pic_id.'">ein Bild</a> mit der Note <b>'.$score.'/6</b> bewertet.<br/><br /><a href="'.$_SERVER['PHP_SELF'].'?show=pic&picID='.$pic_id.'"><img src="'.imgsrcThum($pic_id).'" /></a>', 'i'); }
}


function doDelPic ($id) {
	global $db;
	
	if (!$id) user_error("Missing Parameter <i>id</i>", E_USER_ERROR);
	
	$e = $db->query("SELECT * FROM gallery_pics WHERE id='$id'", __FILE__, __LINE__, __FUNCTION__);
	$d = mysql_fetch_array($e);
	if (!@unlink(picPath($d[album], $id, $d[extension]))) return array('error'=>"Bild konnte nicht gel&ouml;scht werden");
	@unlink(tnPath($d[album], $id, $d[extension]));
	$db->query("DELETE FROM gallery_pics WHERE id='$id'", __FILE__, __LINE__, __FUNCTION__);
	return array('state'=>"Pic $id gel&ouml;scht");
}


function doDelUploadDir($folder) {
	if (!$folder) {
		return array('error'=>"Ordner ausw?hlen!");
	}
	
	if (!file_exists(UPDIR.$folder)) {
		return array('error'=>"Ordner '$folder' existiert nicht");
	}
	
	if (@delDir(UPDIR.$folder)) {
		return array('state'=>"Ordner '$folder' wurde gel&ouml;scht");
	}else{
		return array('error'=>"Ordner '$folder' konnte nicht gel&ouml;scht werden");
	}
}


function doMkUploadDir ($frm) {
	
	if (!is_array($frm)) user_error("Illegal Argument Type for <i>frm</i>", E_USER_ERROR);
	
	foreach ($frm as $key => $val) {
		$frm[$key] = htmlspecialchars($frm[$key], ENT_QUOTES);
	}
	
	if (!$frm[folder]) return array('frm'=>$frm, 'error'=>"Gib einen Ordner an");
	
	if (file_exists(UPDIR.$frm[folder]))
		return array('frm'=>$frm, 'error'=>"Ordner '$frm[folder]' existiert schon.");
	
	//system("mkdir ".UPDIR.$frm[folder]." -m 0775");
	mkdir(UPDIR.$frm[folder], 0775);
	if (!is_dir(UPDIR.$frm[folder]))
		return array('frm'=>$frm, 'error'=>"Ordner '$frm[folder]' konnte nicht erstellt werden.");
	
	return array('state'=>"Ordner $frm[folder] wurde erstellt");
}


function doRotatePic($picID, $direction) {
	global $db;

	$e = $db->query("SELECT * FROM gallery_pics WHERE id='$picID'", __FILE__, __LINE__, __FUNCTION__);
	$d = mysql_fetch_array($e);


	$origimage = picPath($d[album], $picID, $d[extension]);
	$origimage_tn = tnPath($d[album], $picID, $d[extension]);
	$backupimage = DIR.$d[album]."/pic_".$picID."_".time().$d[extension];
	$backupimage_tn = DIR.$d[album]."/tn_".$picID."_".time().$d[extension];

	switch ($direction) {
		case 'left':
			$degrees = 270;
			break;

		case 'right':
			$degrees = 90;
			break;
	}

	// This sets the image type to .jpg but can be changed to png or gif
	header('Content-type: image/jpeg');


	// Backup old image before proceeding...
	if (!copy($origimage, $backupimage)) {
		return array('error'=>"Backup des Bildes '$origimage' konnte nicht erstellt werden.");
	} else {
		if (!copy($origimage_tn, $backupimage_tn)) {
			return array('error'=>"Backup des Thumbnail '$origimage_tn' konnte nicht erstellt werden.");
		} else {

			// $imgSrc - GD image handle of source image
			// $angle - angle of rotation. Needs to be positive integer
			// angle shall be 0,90,180,270, but if you give other it
			// will be rouned to nearest right angle (i.e. 52->90 degs,
			// 96->90 degs)
			// returns GD image handle of rotated image.
			//function ImageRotateRightAngle( $imgSrc, $angle ) {
				// ensuring we got really RightAngle (if not we choose the closest one)
				//$angle = min( ( (int)(($angle+45) / 90) * 90), 270 );
				$angle = $degrees;
				// no need to fight
				//if( $angle == 0 )
				//return( $imgSrc );

				// dimenstion of source image
				$srcX = imagesx( $origimage );
				$srcY = imagesy( $origimage );
				$srcX_tn = imagesx( $origimage_tn );
				$srcY_tn = imagesy( $origimage_tn );

				switch( $angle ) {
					case 90:
						$destimage = imagecreatetruecolor( $srcY, $srcX );
						//$destimage_tn = imagecreatetruecolor( $srcY_tn, $srcX_tn );
						for( $x=0; $x<$srcX; $x++ )
						for( $y=0; $y<$srcY; $y++ )
						//for( $x_tn=0; $x_tn<$srcX_tn; $x_tn++ )
						//for( $y_tn=0; $y_tn<$srcY_tn; $y_tn++ )
						if(imagecopy($destimage, $origimage, $srcY-$y-1, $x, $x, $y, 1, 1)) {
							//if (imagecopy($destimage_tn, $origimage_tn, $srcY_tn-$y_tn-1, $x_tn, $x_tn, $y_tn, 1, 1)) {
								//print("<p>Bild '$origimage' &amp; Thumbnail '$origimage_tn' wurden gedreht</p>");
								return array('state'=>"Bild '$origimage' &amp; Thumbnail '$origimage_tn' wurden gedreht");
							//} else {
								//print("<p>Thumbnail '$origimage_tn' konnte nicht bearbeitet werden.</p>");
							//	return array('error'=>"Thumbnail '$origimage_tn' konnte nicht bearbeitet werden.");
							//}
						} else {
							//print("<p>Bild '$origimage' konnte nicht bearbeitet werden.</p>");
							return array('error'=>"Bild '$origimage' konnte nicht bearbeitet werden.");
						}
						break;

					/*case 180:
						//$imgDest = doImageFlip( $origimage, $backupimage, IMAGE_FLIP_BOTH );
						if (doImageFlip( $origimage, $backupimage, IMAGE_FLIP_BOTH )) {
							return array('state'=>"Bild '$newimage' wurde gedreht");
						}
						break;*/

					case 270:
						$destimage = imagecreatetruecolor( $srcY, $srcX );
						//$destimage_tn = imagecreatetruecolor( $srcY_tn, $srcX_tn );
						for( $x=0; $x<$srcX; $x++ )
						for( $y=0; $y<$srcY; $y++ )
						//for( $x_tn=0; $x_tn<$srcX_tn; $x_tn++ )
						//for( $y_tn=0; $y_tn<$srcY_tn; $y_tn++ )
						if (imagecopy($destimage, $origimage, $y, $srcX-$x-1, $x, $y, 1, 1)) {
							//if (imagecopy($destimage_tn, $origimage_tn, $y_tn, $srcX_tn-$x_tn-1, $x_tn, $y_tn, 1, 1)) {
								return array('state'=>"Bild '$origimage' &amp; Thumbnail '$origimage_tn' wurden gedreht");
							//} else {
							//	return array('error'=>"Thumbnail '$origimage_tn' konnte nicht bearbeitet werden.");
							//}
						} else {
							return array('error'=>"Bild '$origimage' konnte nicht bearbeitet werden.");
						}
						break;
				}

				return ($destimage);
				//return( $imgDest );
			//}
		}
	}
}


// ************************************ FUNCTIONS *********************************************************************************

function isPic($file) {
	if (!is_file($file)) return 0;
	
	$ext = strtolower(substr($file, -4));
	if (extension($file) == ".jpg") return 1;
	if (extension($file) == ".gif") return 1;
	else return 0;
	
	if ($ok) {
		return 1;
	}else{
		return 0;
	}
}

/**
 * @DEPRECATED Use pathinfo($file, PATHINFO_EXTENSION);
 */
function extension($file) {
	$found = 0;
	for ($i=1; $i<strlen($file); $i++) {
		if (substr($file, -$i, 1) == ".") {
		$found = 1;
		break;
		}
	}
	
	if ($found) return strtolower(substr($file, -$i));
	else return "";
}

function countFiles ($directory) {
	if (!is_dir($directory)) user_error("Parameter <i>directory</i> is not an existing Directory", E_USER_ERROR);
	
	$dir = @opendir($directory);
	if (!$dir) return -1;
	
	$i = -2;  // wegen './' und '../'
	while (false !== ($file = readdir($dir))) {
		$i++;
	}
	closedir($dir);
	
	return $i;
}

function picPath($albID, $id, $extension) {
	return GALLERY_DIR.$albID."/pic_".$id.$extension;
}

function tnPath($albID, $id, $extension) {
	return GALLERY_DIR.$albID."/tn_".$id.$extension;
}

function imgsrcPic($id) {
	// deep: ersetzt wegen mod_rewrite   return "/includes/gallery.readpic.php?id=$id";
		return "/gallery/".$id;
}

function imgsrcThum($id) {
	// deep: ersetzt wegen mod_rewrite   return "/includes/gallery.readpic.php?id=$id&type=tn";
		return "/gallery/thumbs/".$id;
}


function imgName($id) {
	global $db;
	
	$e = $db->query("SELECT id, name FROM gallery_pics WHERE id = $id", __FILE__, __LINE__, __FUNCTION__);
	$cur = mysql_fetch_array($e);
	
	return $cur['name'];
}


function delDir ($dir) {
	if (!is_dir($dir)) return 0;
	
	if (substr($dir, -1) != "/") $dir .= "/";
	
	$handle = @opendir($dir);
	if (!$handle) return 0;
	$done = 1;
	while (false !== ($f = readdir($handle))) {
		if ($f=="." || $f=="..") continue;
		if (is_dir($dir.$f)) {
		if (!delDir($dir.$f)) $done = 0;
		}else{
		if (!@unlink($dir.$f)) $done = 0;
		}
	}
	if (!@rmdir($dir)) $done = 0;
	return $done;
}

function createPic($srcFile, $dstFile, $maxWidth, $maxHeight, $bgcolor=0) {
	// errors
	if (!isPic($srcFile)) user_error("Wrong File Type", E_USER_ERROR);
	if (extension($srcFile) != extension($dstFile))
		user_error("Source- and Destination-Files doesn't have the same File Types.", E_USER_ERROR);

	$ext = extension($srcFile);
	if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> $ext: %s', __FUNCTION__, __LINE__, $ext));

	// calc new pic size
	if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> calc new pic size', __FUNCTION__, __LINE__));
	$img_size = getImageSize($srcFile);
	if (!$img_size) return array('error'=>"keine Rechte");
	$width = $img_size[0];
	$height = $img_size[1];
	
	if ($width >= $height && $width > $maxWidth) {
		$picWidth = $maxWidth;
		$picHeight = round($height * $picWidth / $width, 0);
	}elseif ($height > $width && $height > $maxHeight) {
		$picHeight = $maxHeight;
		$picWidth = round($width * $picHeight / $height, 0);
	}else{
		$picHeight = $height;
		$picWidth = $width;
	}

	/** Create new Pic */
	if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> create new pic', __FUNCTION__, __LINE__));
	switch ($ext)
	{
		case '.jpg':
			if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> ImageCreateFromJPEG(): %s', __FUNCTION__, __LINE__, $srcFile));
			$src = ImageCreateFromJPEG($srcFile);
			if ($src === null) {
				error_log(sprintf('<%s:%d> %s Bild konnte nicht erzeugt werden', __FILE__, __LINE__, __FUNCTION__));
				return false;
			}
			if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> ImageCreateFromJPEG: %s', __FUNCTION__, __LINE__, ($src != null ? 'OK' : 'ERROR')));
			break;
		case '.gif':
			if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> ImageCreateFromJPEG(): %s', __FUNCTION__, __LINE__, $srcFile));
			$src = ImageCreateFromGIF($srcFile);
			if ($src === null) {
				error_log(sprintf('<%s:%d> %s Bild konnte nicht erzeugt werden', __FILE__, __LINE__, __FUNCTION__));
				return false;
			}
			if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> ImageCreateFromGIF: %s', __FUNCTION__, __LINE__, ($src != null ? 'OK' : 'ERROR')));
			break;
		default:
			error_log(sprintf('<%s:%d> %s Wrong File Type', __FILE__, __LINE__, __FUNCTION__));
			return false;
			break;
	}

	/** Modify Pic */
	if (is_array($bgcolor) && sizeof($bgcolor) == 3)
	{
		if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> $bgcolor: %s', __FUNCTION__, __LINE__, print_r($bgcolor, true)));
		$dst = ImageCreateTrueColor ($maxWidth, $maxHeight);  // GD 2.0.1
		//$dst = ImageCreate($picWidth, $picHeight);  			// GD 1.6
		if (!$dst) {
			error_log(sprintf('<%s:%d> %s Bild konnte nicht erzeugt werden', __FILE__, __LINE__, __FUNCTION__));
			return false;
		}
		$bg = imagecolorallocate($dst, $bgcolor[0], $bgcolor[1], $bgcolor[2]);

		$x = round(($maxWidth-$picWidth) / 2);
		$y = round(($maxHeight-$picHeight) / 2);

		if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> imagecopyresized()', __FUNCTION__, __LINE__));
		imagecopyresized($dst, $src, $x,$y,0,0, $picWidth, $picHeight, $width, $height);

		$ret = array('width'=>$maxWidth, 'height'=>$maxHeight);
	} else {
		$dst = ImageCreateTrueColor ($picWidth, $picHeight);  // GD 2.0.1
		//$dst = ImageCreate($picWidth, $picHeight);  			// GD 1.6
		if (!$dst) return array('error'=>"Bild konnte nicht erzeugt werden");

		if (ImageCopyResampled($dst, $src, 0,0,0,0, $picWidth, $picHeight, $width, $height)) {
			if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> ImageCopyResampled OK', __FUNCTION__, __LINE__));
		} else {
			error_log(sprintf('<%s:%d> ImageCopyResampled ERROR: %s => %s', __FUNCTION__, __LINE__, $src, $dst));
			return false;
		}

		$ret = array('width'=>$picWidth, 'height'=>$picHeight);
	}

	switch ($ext) {
		case '.jpg':
			if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> ImageJPEG(%s, %s)', __FUNCTION__, __LINE__, $dst, $dstFile));
			if (!ImageJPEG($dst, $dstFile)) {
				error_log(sprintf('<%s:%d> ImageJPEG ERROR: %s => %s', __FUNCTION__, __LINE__, $dst, $dstFile));
				return false;
			}
			if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> ImageJPEG() OK', __FUNCTION__, __LINE__));
			break;

		case '.gif':
			if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> ImageGIF(%s, %s)', __FUNCTION__, __LINE__, $dst, $dstFile));
			if (!ImageGIF($dst, $dstFile)) {
				error_log(sprintf('<%s:%d> ImageGIF ERROR: %s => %s', __FUNCTION__, __LINE__, $dst, $dstFile));
				return false;
			}
			if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> ImageGIF() OK', __FUNCTION__, __LINE__));
			break;

		default:
			error_log(sprintf('<%s:%d> %s Wrong File Type', __FILE__, __LINE__, __FUNCTION__));
			return false;
			break;
	}
	/*if ($ext == '.jpg') ImageJPEG($dst, $dstFile);
	elseif ($ext == '.gif') ImageGIF($dst, $dstFile);*/
	//system("chmod 0664 ".$dstFile);
	chmod($dstFile, 0664);

	ImageDestroy($src);
	ImageDestroy($dst);
	if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> ImageDestroy() OK', __FUNCTION__, __LINE__));

	return $ret;
}

function getAlbumLinkRandomThumb($album_id) {
	global $db, $user;

	$result = $db->query("SELECT * FROM gallery_pics p WHERE album=".$album_id." ".ZENSUR." ORDER BY RAND() LIMIT 1", __FILE__, __LINE__, __FUNCTION__);
	$rs = $db->fetch($result, __FILE__, __LINE__, __FUNCTION__);
	$file = imgsrcThum($rs['id']);

	$html =
		'<a href="/gallery.php?show=albumThumbs&albID='.$album_id.'">'
		.'<img border="0" src="'.$file.'"/></a>'
	;

	return $html;
}

function getRandomThumb() {
	global $db, $user;

	if ($user->typ != USER_MEMBER) $zensur = "WHERE zensur='0'";
	else $zensur = "";

	$result = $db->query("SELECT * FROM gallery_pics $zensur ORDER BY RAND() LIMIT 1", __FILE__, __LINE__, __FUNCTION__);
	$rs = $db->fetch($result);

	return formatGalleryThumb($rs);
}


/**
 * Get new Daily Pic
 *
 * @author ?
 * @author IneX
 * @date 19.03.2018
 * @version 2.2
 * @since 1.0
 * @since 2.0 added Telegram Notification (photo message)
 * @since 2.1 Telegram will send now high-res photo, instead of low-res thumbnail
 * @since 2.2 changed to new Telegram Send-Method
 *
 * @see SITE_URL
 * @see imgsrcThum()
 * @see Telegram::send::photo()
 * @global object $db		MySQL-Datenbank Objekt aus mysql.inc.php
 * @global object $user		User-Objekte aus usersystem.inc.php
 * @global object $telegram	Globales Class-Object mit den Telegram-Methoden
 */
function getDailyThumb () {
	global $db, $user, $telegram;
	$name = 'daily_pic';

	try {
		/**
		 * Check if current Daily Pic is still from Today…
		 */
		$e = $db->query("SELECT g.*, TO_DAYS(p.date)-TO_DAYS(NOW()) upd
								FROM periodic p, gallery_pics g
								WHERE p.name='$name' AND g.id=p.id", __FILE__, __LINE__, __FUNCTION__);
		$d = $db->fetch($e);

		/**
		 * If current Daily Pic is old - generate a new one:
		 */
		if (!$d || $d['upd'])
		{
			/** Randomly get new Gallery-Pic */
			$e = $db->query("SELECT * FROM gallery_pics WHERE zensur='0' ORDER BY RAND() LIMIT 1", __FILE__, __LINE__, __FUNCTION__);
			$d = $db->fetch($e);
			/** Add the new Daily-Pic into the periodic Database-Table */
			$db->query('REPLACE INTO periodic (name, id, date) VALUES ("'.$name.'", '.$d['id'].', NOW())', __FILE__, __LINE__, __FUNCTION__);
			if (DEVELOPMENT) error_log("[DEBUG] ".__FUNCTION__." new Daily Pic generated: ".$d['id']);

			/** Telegram Notification auslösen */
			// url = URL to the Pic, caption = "Daily Pic(: Title - if available) [<a href="img-url">aluegä</a>]"
			$imgUrl = SITE_URL.imgsrcPic($d['id']);
			$imgCaption = 'Daily Pic' . (!picHasTitle($d['id']) ? '' : ': '.picHasTitle($d['id']));
			$telegram->send->photo('group', $imgUrl, $imgCaption, ['disable_notification' => 'true']);
		}

		return formatGalleryThumb($d);

	} catch (Exception $e) {
		error_log($e->getMessage());
	}
}


function getTopPics($album_id, $limit, $options) {
	global $db, $user;
	
	//if ($user->typ != USER_MEMBER) $zensur = "WHERE zensur='0'";
	//else $zensur = "";
	
	//$sql = "SELECT *, AVG(score) FROM gallery_pics_votes WHERE score > 0 GROUP BY pic_id ORDER BY score DESC LIMIT $limit";
	//echo $sql;
	
	
	$i=1;
	
	if ($options == 'ranking-list') {
		$html_out .=
				'<table class="border">'
		;
	}
	
	
	if ($album_id == 0) {
		$sql =
			"
			SELECT *, AVG(score) as avgScore, COUNT(pic_id) as numVotes
			FROM gallery_pics_votes
			WHERE score > 0
			GROUP BY pic_id
			ORDER BY avgScore DESC, numVotes DESC
			LIMIT $limit"
		;
		$result = $db->query($sql, __FILE__, __LINE__, __FUNCTION__);
		
		while($rs = $db->fetch($result)) {
			$file = imgsrcThum($rs['pic_id']);
			
			$color = ($i % 2 == 0) ?  BACKGROUNDCOLOR : TABLEBACKGROUNDCOLOR;
			
			//$anz_votes = getNumVotes($rs['pic_id']);
			$anz_votes = $rs['numVotes'];
			
			if ($options == 'ranking-list') {
				$html_out .=
					'<tr bgcolor="'.$color.'">'
					.'<td align="left">'.$i.'.</td>'
					.'<td align="center">';
			}
			
			$html_out .=
					'<a href="/gallery.php?show=pic&picID='.$rs['pic_id'].'">'
			;
			
			$pic_name = imgName($rs['pic_id']);
			if ($pic_name) {
				$html_out .=
					$pic_name.'<br />'
				;
			}
			
			$html_out .=
					'<img border="0" src="'.$file.'" /></a>'
					.'<br />Bild Note: '.round($rs['avgScore'],1).' '
			;
			
			$votes = (($anz_votes > 1) || ($anz_votes == 0)) ? $anz_votes." Votes" : $anz_votes." Vote";
			$html_out .=
				'<small>('.$votes.')</small>'
				.'<br />'
			;
			
			if ($options == 'ranking-list') {
				$html_out .=
					'</td>'
					.'</tr>'
				;
			}
			
			$i++;
		}
		
		
	} elseif ($album_id > 0) {
		$sql =
			"
			SELECT
				p.id,
				p.album,
				p.name,
				p_vote.pic_id,
				AVG(p_vote.score) as avgScore,
				COUNT(p_vote.pic_id) as numVotes
			FROM
			gallery_pics p LEFT OUTER
			JOIN gallery_pics_votes p_vote ON p.id = p_vote.pic_id
			WHERE p.album = ".$album_id." AND p_vote.score > 0
			GROUP BY p_vote.pic_id
			ORDER BY avgScore DESC, numVotes DESC
			LIMIT $limit"
		;
		$result = $db->query($sql, __FILE__, __LINE__, __FUNCTION__);
		
		while($rs = $db->fetch($result)) {
			//$file = imgsrcThum($rs['id']);
			$file = imgsrcThum($rs['pic_id']);
			
			$color = ($i % 2 == 0) ?  BACKGROUNDCOLOR : TABLEBACKGROUNDCOLOR;
			
			//$anz_votes = getNumVotes($rs['pic_id']);
			$anz_votes = $rs['numVotes'];
			
			if ($options == 'ranking-list') {
				$html_out .=
					'<tr bgcolor="'.$color.'">'
					.'<td align="left">'.$i.'.</td>'
					.'<td align="center">';
			}
			
			$html_out .=
					'<a href="/gallery.php?show=pic&picID='.$rs['pic_id'].'">'
			;
			
			if ($rs['name']) {
				$html_out .=
					$rs['name'].'<br />'
				;
			}
			
			$html_out .=
					'<img border="0" src="'.$file.'" /></a>'
					.'<br />Bild Note: '.round($rs['avgScore'],1).' '
			;
			
			$votes = (($anz_votes > 1) || ($anz_votes == 0)) ? $anz_votes." Votes" : $anz_votes." Vote";
			$html_out .=
				'<small>('.$votes.')</small>'
				.'<br />'
			;
			
			if ($options == 'ranking-list') {
				$html_out .=
					'</td>'
					.'</tr>'
				;
			}
			
			$i++;
		}
		
		
	} else {
		$sql =
			"
			SELECT *, AVG(score) as avgScore, COUNT(pic_id) as numVotes
			FROM gallery_pics_votes
			WHERE score > 0
			GROUP BY pic_id
			ORDER BY avgScore DESC, numVotes DESC
			LIMIT $limit"
		;
		$result = $db->query($sql, __FILE__, __LINE__, __FUNCTION__);
		
		while($rs = $db->fetch($result)) {
			$file = imgsrcThum($rs['pic_id']);
			
			$color = ($i % 2 == 0) ?  BACKGROUNDCOLOR : TABLEBACKGROUNDCOLOR;
			
			//$anz_votes = getNumVotes($rs['pic_id']);
			$anz_votes = $rs['numVotes'];
			
			if ($options == 'ranking-list') {
				$html_out .=
					'<tr bgcolor="'.$color.'">'
					.'<td align="left">'.$i.'.</td>'
					.'<td align="center">';
			}
			
			$html_out .=
					'<a href="/gallery.php?show=pic&picID='.$rs['pic_id'].'">'
			;
			
			$pic_name = imgName($rs['pic_id']);
			if ($pic_name) {
				$html_out .=
					$pic_name.'<br />'
				;
			}
			
			$html_out .=
					'<img border="0" src="'.$file.'" /></a>'
					.'<br />Bild Note: '.round($rs['avgScore'],1).' '
			;
			
			$votes = (($anz_votes > 1) || ($anz_votes == 0)) ? $anz_votes." Votes" : $anz_votes." Vote";
			$html_out .=
				'<small>('.$votes.')</small>'
				.'<br />'
			;
			
			if ($options == 'ranking-list') {
				$html_out .=
					'</td>'
					.'</tr>'
				;
			}
			
			$i++;
		}
	}
	
	if ($options == 'ranking-list') {
		$html_out .= '</table>';
	}
	
	return $html_out;
	
}


function formatGalleryThumb($rs)
{
	global $db, $user;

	$file = imgsrcThum($rs['id']);

	if ($user->typ == USER_MEMBER) { // schauen dass wirs nur bei membern machen...
		if (!$user_id) { $user_id = $user->id; }

		$e = $db->query(
		"SELECT count(c.id) anz
		FROM comments c, comments_unread u
		WHERE c.board = 'i' AND c.thread_id=".$rs['id']." AND u.comment_id=c.id AND u.user_id=".$user_id,
		__FILE__, __LINE__
		);
		$d = $db->fetch($e);

		if ($d['anz'] > 0) {
			return
			'<a href="/gallery.php?show=pic&picID='.$rs['id'].'">'
			.'<img border="0" src="'.$file.'" /><br />'.Thread::getNumPosts('i', $rs['id']).' Comments</a>'
			.' <small>('.$d['anz'].' unread)</small>'
			;
		} else {
			return
			'<a href="/gallery.php?show=pic&picID='.$rs['id'].'">'
			.'<img border="0" src="'.$file.'" /><br />'.Thread::getNumPosts('i', $rs['id']).' Comments</a>'
			;
		}
	} else { // wenns ein Gast ist...
		return
		'<a href="/gallery.php?show=pic&picID='.$rs['id'].'">'
		.'<img border="0" src="'.$file.'" /><br />'.Thread::getNumPosts('i', $rs['id']).' Comments</a>'
		;
	}
}

/**
 * Updates a Pic's Title
 *
 * @global object $db Globales Class-Object mit allen MySQL-Methoden
 * @return none
 */
function doEditFotoTitle($picID, $frm)
{
	global $db;

	$frm['name'] = htmlentities($frm['name'], ENT_NOQUOTES);
	if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> $frm[name]: ', __FUNCTION__, __LINE__, $frm['name']));
	$db->update('gallery_pics', $picID, $frm, __FILE__, __LINE__, __FUNCTION__);

	unset($_GET['editFotoTitle']);
}

/**
 * Checks if Pic has a Title
 *
 * @author IneX
 * @date 21.01.2017
 *
 * @global object $db	Database Class Object
 * @return string 		If set, returns the Pic's title
 */
function picHasTitle($picID)
{
	global $db;

	if (is_numeric($picID) && $picID > 0) {
		$e = $db->query("SELECT name FROM gallery_pics WHERE id='$picID' LIMIT 1", __FILE__, __LINE__, __FUNCTION__);
		$d = $db->fetch($e);
	}
	if ($d) return $d['name'];
	else return false;
}

/**
 * Flip an image vertically/horizontally
 *
 * @todo use native PHP Image Flip function! http://php.net/manual/function.imageflip.php
 *
 * @see http://php.net/manual/function.imageflip.php
 * @see doRotatePic()
 */
function doImageFlip($imgsrc, $imgout, $type)
{
   $width = imagesx($imgsrc);
   $height = imagesy($imgsrc);

   $imgdest = imagecreatetruecolor($width, $height);

   switch( $type )
 	{
 	// mirror wzgl. osi
 	case IMAGE_FLIP_HORIZONTAL:
     	for( $y=0 ; $y<$height ; $y++ )
   		imagecopy($imgdest, $imgout, 0, $height-$y-1, 0, $y, $width, 1);
     	break;

 	case IMAGE_FLIP_VERTICAL:
     	for( $x=0 ; $x<$width ; $x++ )
   		imagecopy($imgdest, $imgout, $width-$x-1, 0, $x, 0, 1, $height);
     	break;

 	case IMAGE_FLIP_BOTH:
     	for( $x=0 ; $x<$width ; $x++ )
   		imagecopy($imgdest, $imgout, $width-$x-1, 0, $x, 0, 1, $height);

     	$rowBuffer = imagecreatetruecolor($width, 1);
     	for( $y=0 ; $y<($height/2) ; $y++ )
   		{
   		imagecopy($rowBuffer, $imgdest  , 0, 0, 0, $height-$y-1, $width, 1);
   		imagecopy($imgdest  , $imgdest  , 0, $height-$y-1, 0, $y, $width, 1);
   		imagecopy($imgdest  , $rowBuffer, 0, $y, 0, 0, $width, 1);
   		}

     	imagedestroy( $rowBuffer );
     	break;
 	}

   return( $imgdest );
}
