<?php
/**
 * Gallery Funktionen
 *
 * Beinhaltet alle Funktionen der Gallery.
 *
 * @author [z]biko
 * @package zorg\Gallery
 * @version 3.0
 * @since 1.0 File & functions added
 * @since 2.0 Added code documentations, polished & optimized various functions
 * @since 3.0 `09.08.2018` `IneX` Refactored picPath() & createPic(), added APOD specific specials
 *
 * @TODO MyPic-Markierung von Bildern
 * @TODO Wasserzeichen(?)
 */
/**
 * File includes
 * @include config.inc.php
 * @include forum.inc.php
 * @include util.inc.php
 * @include usersystem.inc.php
 */
require_once dirname(__FILE__).'/config.inc.php';
include_once INCLUDES_DIR.'forum.inc.php';
require_once INCLUDES_DIR.'util.inc.php';
require_once INCLUDES_DIR.'usersystem.inc.php';

/**
 * @const set_time_limit	Maximale Zeit in Sekunden, welche das Script laufen darf
 * @const FTP_UPDIT			FTP-Serveraddress and Directory-Path to Gallery Upload Dir
 * @const DIR				Path to Gallery directory on the server
 * @const UPDIR				Path to the Upload directory on the server
 * @const ZENSUR			If the User is a Member, he can see censored Pics. Otherwise the SQL-Query addition will filter them out.
 */
set_time_limit(600);
define('FTP_UPDIR', 'ftp://zooomclan@zorg.ch/data/gallery/upload/incoming/'); // @DEPRECATED
define('DIR', $_SERVER['DOCUMENT_ROOT'].'/../data/gallery/'); // @DEPRECATED Replaced with GALLERY_DIR in config.inc.php
define('UPDIR', $_SERVER['DOCUMENT_ROOT'].'/../data/upload/'); // @DEPRECATED Replaced with GALLERY_UPLOAD_DIR in config.inc.php
define('ZENSUR', ( $user->typ >= USER_MEMBER ? '' : 'AND p.zensur="0"' ));

/**
 * Globals
 * @var array $MAX_PIC_SIZE	The maximum width & height for pictures
 * @var array $THUMBPAGE		The image size for Thumbnail pictures
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
 * @since 1.0 function added
 *
 * @uses ZENSUR
 * @param string $state Aktueller Status des Albums, z.B. wenn es gerade bearbeitet wird
 * @param string $error (Fehler-)Meldung, welche auf der Gallery-Seite angezeigt werden soll
 * @global object $db Globales Class-Object mit allen MySQL-Methoden
 * @global object $user Globales Class-Object mit den User-Methoden & Variablen
 * @global array $MAX_PIC_SIZE Variable mit den Werten aus $MAX_PIC_SIZE
 * @global array $THUMBPAGE Variable mit den Werten aus $THUMBPAGE
 * @return string HTML-Code der Gallery-Seite
 */
function galleryOverview ($state="", $error="")
{
	global $db, $user, $MAX_PIC_SIZE, $THUMBPAGE, $smarty;

	/** Error Output (function backwards compatibility) */
	if ((isset($state) || isset($error)) && $user->typ >= USER_MEMBER)
	{
		$smarty->assign('error', ['type' => 'warn', 'dismissable' => 'false', 'title' => (isset($state) ? $state : $error), 'message' => (isset($error) ? $error : '')]);
	}

	$sidebarHtml = null;

	/* Galleries Query */
	$sql = 'SELECT
				 a.id
				, a.name
				, COUNT(p.id) anz
				, UNIX_TIMESTAMP(created) AS created_at
			 FROM gallery_albums a, gallery_pics p
			 WHERE p.album = a.id '.ZENSUR.'
			 GROUP BY p.album
			 ORDER BY name ASC, created_at DESC';
	$query = $db->query($sql, __FILE__, __LINE__, __FUNCTION__);
	$numresult = $db->num($query);
	if 	($numresult > 0)
	{
		while ($d = $db->fetch($query))
		{
			$alphabeticalGroup = strtolower(mb_substr($d['name'], 0, 1));
			$alphabeticalList[$alphabeticalGroup][] = [
				 'id' => $d['id']
				,'name' => $d['name']
				,'created' => ($d['created_at'] === '0000-00-00 00:00:00' ? '' : $d['created_at'])
				,'numpics' => $d['anz']
			];
			$seen[] = $d['id']; // Add Gallery-ID to 'seen'-List
		}

		/* List of Galleries */
		$smarty->assign('galleriesIdList', $seen);
		$smarty->assign('galleriesOverviewGrouped', $alphabeticalList);

		/* List empty Galleries */
		if ($user->typ >= USER_MEMBER)
		{
			$emptylistSqlWhere = null;
			foreach ($seen as $key => $galleryid) {
				if ($key === array_key_first($seen)) $emptylistSqlWhere .= 'WHERE id NOT IN (';
				$emptylistSqlWhere .= $galleryid.($key != array_key_last($seen) ? ',' : ')');
			}
			//$where = substr($where, 0, -5);
			$emptylistSql = 'SELECT id, name, UNIX_TIMESTAMP(created) AS created_at FROM gallery_albums'.(!empty($emptylistSqlWhere) ? ' '.$emptylistSqlWhere : '');
			$result = $db->query($emptylistSql, __FILE__, __LINE__, __FUNCTION__);
			$numempty = $db->num($result);
			if 	($numempty > 0)
			{
				while ($rs = $db->fetch($result))
				{
					$emptyGalleriesList[] = [
						 'id' => $rs['id']
						,'name' => $rs['name']
						,'created' => ($rs['created_at'] === '0000-00-00 00:00:00' ? '' : $rs['created_at'])
					];
				}
				$smarty->assign('galleriesEmptyIdList', $emptyGalleriesList);
			}
		}
	}
	$sidebarHtml = $smarty->fetch('file:layout/partials/gallery/block_sidebarlist.tpl');
	if (!empty($sidebarHtml)) $smarty->assign('sidebarHtml', $sidebarHtml);

	return true;
}

/**
 * Album Thumbnails anzeigen
 *
 * @version 1.5
 * @since 1.0 function added
 * @since 1.5 moved pagination to new Sidebar, output it via $smarty
 *
 * @global array $MAX_PIC_SIZE Variable mit den Werten aus $MAX_PIC_SIZE
 * @global array $THUMBPAGE Variable mit den Werten aus $THUMBPAGE
 * @uses ZENSUR
 * @param integer $id ID des Albums von welchem die Thumbnails angezeigt werden sollen
 * @param integer $page Aktuelle Seite des Albums, deren Thumbnails angezeigt werden sollen
 */
function albumThumbs ($id, $page=0) {
	global $db, $THUMBPAGE, $MAX_PIC_SIZE, $user, $smarty;

	if (!$id || !is_numeric($id))
	{
		user_error('Missing Parameter <i>id</i>', E_USER_ERROR);
		exit;
	}

	$pagepics = $THUMBPAGE['width'] * $THUMBPAGE['height'];
	$e = $db->query('SELECT count(id) anz FROM gallery_pics p WHERE album='.$id.' '.ZENSUR.' GROUP BY album', __FILE__, __LINE__, __FUNCTION__);
	$d = mysqli_fetch_array($e);
	$anz = $d['anz'];

	if (!empty($d) && $d['anz'] > 0)
	{
		$htmlOutput = null;

		$e = $db->query('SELECT g.*, e.name eventname
						FROM gallery_albums g
						LEFT JOIN events e ON e.gallery_id=g.id
						WHERE g.id='.$id, __FILE__, __LINE__, __FUNCTION__);
		$d = $db->fetch($e);
		$htmlOutput .= '<table width="80%" align="center"><tr><td align="center" class="bottom_border">'
			.'<h1>'.($d['eventname'] ? $d['eventname'] : $d['name']).($user->typ == USER_MEMBER ? ' <span class="small">[<a href="/gallery.php?albID='.$id.'&show=editAlbum">edit</a>]</span>' : '').'</h1>'
			.'</td></tr></table><br><br>';

		$e = $db->query('SELECT * FROM gallery_pics p WHERE album='.$id.' '.ZENSUR.' ORDER BY p.id LIMIT '.($page*$pagepics).', '.$pagepics, __FILE__, __LINE__, __FUNCTION__);
		$htmlOutput .= '<table cellspacing="0" cellpadding="0" style="border-collapse:collapse">';
		$hgt = $MAX_PIC_SIZE['tnHeight'] + 2 * $THUMBPAGE['padding'];
		$wdt = $MAX_PIC_SIZE['tnWidth'] + 2 * $THUMBPAGE['padding'];
		$rows = 0;
		while ($d = $db->fetch($e))
		{
			$comments = Thread::getNumPosts('i', $d['id']);
			$unread = Thread::getNumUnread('i', $d['id']);

			if ($rows==0) $htmlOutput .= '<tr>';
			$htmlOutput .= '<td class="border" cellpadding="'.$THUMBPAGE['padding'].'" height="'.$hgt.'", width="'.$wdt.'" style="text-align:center" valign="middle">'
							.'<a href="?show=pic&picID='.$d['id'].'">'.($d['name']?$d['name'].'<br>':'').'<img border="0" src="'.imgsrcThum($d['id']).'" style="width: 100%;max-width: 100%;">';

			if ($comments) {
				$htmlOutput .= "<br>$comments Comments ";
				if ($unread) $htmlOutput .= "<br>($unread unread) ";
			}

			$htmlOutput .= '</a></td>';
			if (++$rows == $THUMBPAGE['width']) {
				$rows = 0;
				$htmlOutput .= '</tr>';
			}
		}

		for ($i=$rows; $i<$THUMBPAGE['width']; $i++)
		{
			$htmlOutput .= '<td>&nbsp;</td>';
		}
		if ($rows) $htmlOutput .= '</tr>';
		$htmlOutput .= '</table>';

		/** Pagination */
		$paginationHtml = '<h3>Seiten</h3><font size="4">';
		for ($i=0; $i<$anz/$pagepics; $i++)
		{
			$paginationHtml .= ($page==$i ? '<b>['.($i+1).']</b>' : '<a href="?show=albumThumbs&albID='.$id.'&page='.$i.'">'.($i+1).'</a>').' &nbsp; ';
		}
		$paginationHtml .= '</font>';
	}

	/** Invalid / not found Album-ID */
	else {
		$smarty->assign('error', ['type' => 'warn', 'dismissable' => 'false', 'title' => t('error-invalid-album', 'gallery')]);
	}

	if (!empty($paginationHtml)) $smarty->assign('sidebarHtml', $paginationHtml);
	$smarty->display('file:layout/head.tpl');
	echo $htmlOutput;
}


/**
 * Bild anzeigen
 *
 * @author [z]biko
 * @author IneX
 * @date 21.10.2013
 * @version 2.1
 * @since 1.0 `21.10.2013` `[z]biko` function added
 * @since 2.0 `IneX` APOD Special: statt Pic ein Video embedden
 * @since 2.1 `01.10.2019` `IneX` responsive scaling `img` and <iframe> tags
 * @since 2.2 `04.12.2020` `IneX` fixed fallback to show Album Name on Pic
 * @since 2.3 `04.12.2020` `IneX` switched file_exists() to is_file() and fixed PHP notice for undefined exif data
 *
 * @param integer $id ID des Albums von welchem die Thumbnails angezeigt werden sollen
 * @param integer $page Aktuelle Seite des Albums, deren Thumbnails angezeigt werden sollen
 * @global array $MAX_PIC_SIZE Variable mit den Werten aus $MAX_PIC_SIZE
 * @global array $THUMBPAGE Variable mit den Werten aus $THUMBPAGE
 * @uses ZENSUR
 */
function pic ($id)
{
	global $user, $db, $THUMBPAGE;

	if (!$id) user_error('Missing Parameter <i>id</i>', E_USER_ERROR);

	$e = $db->query('SELECT *, UNIX_TIMESTAMP(pic_added) as timestamp FROM gallery_pics WHERE id='.$id, __FILE__, __LINE__, __FUNCTION__);
	$cur = $db->fetch($e);

	if($cur == false) {
		echo 'Bild '.$id.' existiert nicht!';
		exit;
	}

	$e = $db->query('SELECT * FROM gallery_pics p WHERE album='.$cur['album'].' AND id<'.$id.' '.ZENSUR.' ORDER BY id DESC LIMIT 0,1', __FILE__, __LINE__, __FUNCTION__);
	$last = mysqli_fetch_array($e);

	$e = $db->query('SELECT * FROM gallery_pics p WHERE album='.$cur['album'].' AND id>'.$id.' '.ZENSUR.' ORDER BY id ASC LIMIT 0,1', __FILE__, __LINE__, __FUNCTION__);
	$next = mysqli_fetch_array($e);

	$e = $db->query('SELECT album, a.id, count(p.id) anz, a.name, e.gallery_id, e.name eventname
					 FROM gallery_pics p, gallery_albums a
						LEFT JOIN events e
						 ON e.gallery_id = a.id
					 WHERE p.id<='.$id.' AND p.album='.$cur['album'].' AND a.id='.$cur['album'].' '.ZENSUR.'
					 GROUP BY album, eventname', __FILE__, __LINE__, __FUNCTION__);
	$d = mysqli_fetch_array($e);
	$page = floor($d['anz'] / ($THUMBPAGE['width'] * $THUMBPAGE['height']));
	echo '<br><table width="80%" align="center"><tr>
	<td align="center" class="bottom_border"><h3>'
	.($d['eventname'] ? $d['eventname'] : $d['name'])
	.'</h3></div></td></tr></table><br><br>';

	if ($cur['zensur'] && $user->typ != USER_MEMBER) {
		echo '<b><font color="red">Access denied for this picture</font></b><br><br>';
		return;
	}

	if (isset($_GET['editFotoTitle']) && $_GET['editFotoTitle'] && $user->typ >= USER_MEMBER) {
		echo '<form method="post" action="?do=editFotoTitle&'.url_params().'">';
			echo '<fieldset style="display: flex;white-space: nowrap;align-items: center; margin: 0;">';
			echo '<input type="text" name="frm[name]" class="text" style="flex: 1.5;" value="'.$cur['name'].'" placeholder="Gib ems Fötli Name!"> ';
			echo '<input type="submit" style="flex: 0.5;" value=" OK " class="button">';
			echo '&nbsp;<a class="small" href="?show=pic&picID='.$id.'">cancel</a>';
			echo '</fieldset>';
		echo "</form>";
	} else {
		if (!$cur['name'] && $user->typ >= USER_MEMBER) {
			echo '<form method="post" action="?do=editFotoTitle&'.url_params().'">';
				echo '<fieldset style="display: flex;white-space: nowrap;align-items: center; margin: 0;">';
				echo '<input type="text" name="frm[name]" class="text" style="flex: 1.5;" placeholder="Gib ems Fötli Name!">';
				echo '<input type="submit" style="flex: 0.5;" value=" OK " class="button">';
				echo '</fieldset>';
			echo "</form>";
		} elseif ($cur['name']) {
			echo '<h1>'.$cur['name'].($user->typ >= USER_MEMBER ? ' <span class="small"><a href="?editFotoTitle=1&'.url_params().'">[edit]</a></span>' : '').'</h1>';
		}
	}

	$pic_filepath = picPath($cur['album'], $id, '.jpg');

	if (is_file($pic_filepath) !== false)
	{
		/** APOD Special: use pic_added from database, instead of filemtime */
		if ($cur['album'] == APOD_GALLERY_ID && !empty($cur['timestamp'])) {
			$pic_date = '<p>Bild von '.datename($cur['timestamp']).'</p>';
		}
		/** Regular zorg Pics */
		else {
			/** Check for EXIF data */
			$exif_data = exif_read_data($pic_filepath, 1, true);
			if ($exif_data !== false && isset($exif_data['FILE.FileDateTime'])) {
				$pic_date = '<p>Bild erstellt am '.date('d. F Y H:i', $exif_data['FILE.FileDateTime']).'</p>';
			}
			/** Fallback: Datum aus dem filemtime() des Files */
			else {
				$pic_date = '<p>Bild Upload von '.datename(filemtime($pic_filepath)).'</p>';
			}
		}
		echo $pic_date;
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
			echo '<form name="f_benoten" method="post" action="'.$_SERVER['PHP_SELF'].'?do=benoten&amp;'.url_params().'" class="voteform" style="display: flex;">'
					.'<input name="picID" type="hidden" value="'.$cur['id'].'">'
					.'<span>Benoten:</span>'
					.'<label class="scorevalue" style="display: flex;margin-right: 1em;">'
						.'<input type="radio" name="score" onClick="document.f_benoten.submit();" value="1"></label>'
						//.'1</label>'
					.'<label class="scorevalue" style="display: flex;margin-right: 1em;">'
						.'<input type="radio" name="score" onClick="document.f_benoten.submit();" value="2"></label>'
						//.'2</label>'
					.'<label class="scorevalue" style="display: flex;margin-right: 1em;">'
						.'<input type="radio" name="score" onClick="document.f_benoten.submit();" value="3"></label>'
						//.'3</label>'
					.'<label class="scorevalue" style="display: flex;margin-right: 1em;">'
						.'<input type="radio" name="score" onClick="document.f_benoten.submit();" value="4"></label>'
						//.'4</label>'
					.'<label class="scorevalue" style="display: flex;margin-right: 1em;">'
						.'<input type="radio" name="score" onClick="document.f_benoten.submit();" value="5"></label>'
						//.'5</label>'
					.'<label class="scorevalue" style="display: flex;margin-right: 1em;">'
						.'<input type="radio" name="score" onClick="document.f_benoten.submit();" value="6"></label>'
						//.'6</label>'
					//.'<input class="button" type="submit" value="benoten">'
				.'</form>';
		}
	} else {
		$anz_votes = getNumVotes($cur['id']);
		$votes = (($anz_votes > 1) || ($anz_votes == 0)) ? $anz_votes." Votes" : $anz_votes." Vote";
		echo '<p>Bild Note: '.getScore($cur['id']).' <small>('.$votes.')</small></p>';
	}

	echo '<div align="center"><table border="0" cellspacing="0" cellpadding="0">';//.$cur['picsize'].'>';

	echo '<tr style="font-size: 20px; font-weight: bold;"><td align="left" width="30%">';
	if ($last) echo '<a href="?show=pic&picID='.$last['id'].'">previous</a>';
	else echo '&lt;- last';
	echo '</td><td style="text-align:center"><a href="'.$_SERVER['PHP_SELF'].'?show=albumThumbs&albID='.$cur['album'].'&page='.$page.'">overview</a></td>';
	echo '<td style="text-align:right" width="30%">';
	if ($next) {
		echo '<a href="?show=pic&picID='.$next['id'].'">next</a>';
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

	/** Normale Pic Anzeige (wenn nicht APOD UND Pic-Extension nicht mit '.' anfängt...) */
	if ($cur['album'] != APOD_GALLERY_ID || mb_substr($cur['extension'],0,1,'utf-8') == '.')
	{
		// Wenn User eingeloggt & noch nicht auf Bild markiert ist, Formular anzeigen...
		if ($user->typ == USER_MEMBER && !checkUserToPic($user->id, $id))
		{ // NOTE: %% = needed to prevent printf() stripping e.g. '100%' into '100'
			printf('
			<form action="%1$s" method="post" onsubmit="return markAsMypic()">
				<input type="hidden" name="picID" value="%2$s" />
				<input type="image" name="mypic" src="%3$s" alt="Klicken um als MyPic zu markieren" title="Dich auf dem Bild markieren?" style="width: 100%%;max-width: 100%%;" />
			</form>'
				,'?do=mypic&amp;'.url_params()
				,$id
				,imgsrcPic($id)
			);
		// ...sonst Bild normal ohne Markierungs-Formular ausgeben (auch für Nicht Eingeloggte)
		} else {
			echo '<img border="0" src="'. imgsrcPic($id). '" style="width: 100%;max-width: 100%;">';
		}

	/** APOD Special: statt Pic ein Video embedden */
	} else {
		switch ($cur['extension'])
			{
				case 'youtube':
					echo '<iframe src="'.$cur['picsize'].'" frameborder="0" scrolling="0" allow="autoplay; encrypted-media" allowfullscreen style="width: 800px;height: 450px;max-width: 100%;"></iframe>';
					break;
				case 'vimeo':
					echo '<iframe src="'.$cur['picsize'].'" frameborder="0" scrolling="0" webkitallowfullscreen mozallowfullscreen allowfullscreen style="width: 800px;height: 450px;max-width: 100%;"></iframe>';
					break;
				case 'website':
					echo '<iframe src="'.$cur['picsize'].'" frameborder="0" scrolling="0" importance="low" style="overflow:hidden;" style="width: 800px;height: 800px;max-width: 100%;"></iframe>';
					break;
			}
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
		echo '<table align="center" class="border" cellspacing="0" cellpadding="10"><tr><td valign="top"><br>';
		if ($cur['zensur']) {
			echo '<font color="red">Bild ist zensiert</font>';
			$val = "Zensur aufheben";
		}else{
			echo '<font color="green">Bild ist nicht zensiert</font>';
			$val = "zensieren";
		}
		echo '</td><td valign="top"><br><form action="'.$_SERVER['PHP_SELF'].'?do=zensur&show=pic&picID='.$cur['id'].'" method="post">'
		.'<input type="submit" class="button" value="'.$val.'"></form></td>';

		echo '<td valign="top" style="text-align:right" width="250"><br>'
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
		$frm = mysqli_fetch_array($e);

		echo '<h2>Album #'.$id.' bearbeiten</h2>';
	}else {
		echo '<h2>Neues Album erstellen</h2>';
		$id = 0;
	}


	if ($done == "editAlbum" || $done == "delAlbum") {
		$editError = $error;
		$editState = $state;
	}else {
		$uploadError = $error;
		$uploadState = $state;
	}

	if ($editState) echo "<font color='green'><b>$editState</b></font><br><br>"; // FIXME Undefined variable: editState
	if ($editError) echo "<font color='red'><b>$editError</b></font><br><br>"; // FIXME Undefined variable: editError

	echo '<a href="/gallery.php?albID='.$id.'&show=albumThumbs">&hookleftarrow; go to Album</a><br><br>';
	?>
	<table class="border" cellspacing="3">
	<form action="<?php echo $_SERVER['PHP_SELF']?>?show=editAlbum&albID=<?php echo $id?>&do=editAlbum" method="post">
		<tr>
		<td align="left">ID: </td>
		<td align="left"><?php echo $id?>
		</tr>
		<tr>
		<td align="left">Name: </td>
		<td align="left"><input type="text" class="text" size="50" name="frm['name']" value="<?php echo $frm['name']?>"></td><?php /* FIXME Trying to access array offset on value of type null*/ ?>
		</tr>
		<tr>
		<td colspan='2' align="center">
			<br>
			<input class="button" type="submit" value="   OK   ">
			<br><br>
		</td>
		</tr>
	</form>
	</table>
	<br>


	<?php
	if ($id)
	{ ?>
		<table class="border"><tr><td>
		<form <?php echo 'action="'.$_SERVER['PHP_SELF'].'?show=editAlbum&albID='.$id.'&do=delAlbum"'?> method="post">
		Album l&ouml;schen: <br>(Gib <i>OK</i> ins Feld ein, um zu best&auml;tigen)<br><br>
			<input class="text" name="del" value="" size="4"> &nbsp;
			<input type="submit" class="button" value="   l&ouml;schen   ">
		</form>
		</td></tr></table>
		<br>
		<?php

		echo '<h2>Picture Upload</h2>';

		if ($uploadState) echo "<font color='green'><b>$uploadState</b></font><br><br>";
		if ($uploadError) echo "<font color='red'><b>$uploadError</b></font><br><br>";
		?>

		<table class="border"><tr><td>
		<form action="<?php echo $_SERVER['PHP_SELF']?>?show=editAlbum&albID=<?php echo $id?>&do=mkUploadDir" method="post">
		Upload-Ordner erstellen (in /data/gallery/upload/):<br><br>
		<input type="text" class="text" name="frm[folder]" value="<?php echo $frm['folder']?>"> &nbsp; &nbsp;<?php /* FIXME Trying to access array offset on value of type null */ ?>
		<input type="submit" class="button" value="   erstellen   ">
		</form>
		</td></tr></table>
		<br>

		<?php
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
		<form <?php echo 'action="'.$_SERVER['PHP_SELF'].'?show=editAlbum&albID='.$id.'&do=delUploadDir"'?> method="post">
		Upload-Ordner l&ouml;schen:<br><br>
		<select size="1" class="text" name="frm[folder]">
			<?php
			for ($i=0; $i<sizeof($fileoptions); $i++) {
				echo $fileoptions[$i];
			}
			?>
		</select> &nbsp; &nbsp;
		<input type="submit" class="button" value="   l&ouml;schen   ">
		</form>
		</td></tr>
		<tr><td style="text-align:right">
		<a <?php echo 'href="'.$_SERVER['PHP_SELF'].'?show=editAlbum&albID='.$id.'"'?>>--> Refresh Ordnerliste</a>
		</td></tr></table>
		<br>

		<table class="border"><tr><td align="left" width="450">
		<form <?php echo 'action="'.$_SERVER['PHP_SELF'].'?show=editAlbum&albID='.$id.'&do=upload"'?> method="post" enctype="multipart/form-data">
		Lade die Pics (<b>.jpg oder .gif</b>) per FTP in ein Upload-Ordner. Achte darauf, dass du den Pics die
		Rechte 0664 gibst.
		(<?php echo '<a target="_new" href="'.FTP_UPDIR.'">'.FTP_UPDIR.'</a>'?>). <br><br>
		W&auml;hle den Ordner hier aus, um die Pics zu indizieren: <br><br>
		<input type="checkbox" checked name="frm[delPics]" value="1">
		Erfolgreich indizierte Bilder aus Upload-Ordner l&ouml;schen<br>
		<input type="checkbox" name="frm[delFiles]" value="1">
		nicht indizierte Files aus Upload-Ordner l&ouml;schen
		<br><br>
		<select size="1" class="text" name="frm[folder]">
			<?php
			for ($i=0; $i<sizeof($fileoptions); $i++) {
			echo $fileoptions[$i];
			}
			?>
		</select> &nbsp; &nbsp;
		<input class="button" type="submit" value="   upload   "><br>
		</form>
		</td></tr>
		<tr><td style="text-align:right">
		<a <?php echo 'href="'.$_SERVER['PHP_SELF'].'?show=editAlbum&albID='.$id.'"'?>>--> Refresh Ordnerliste</a>
		</td></tr></table>
		<br>
		<?php
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
		$notDone .= "- $file (ist kein g&uuml;ltiges Bild)<br>";
		if ($frm[delFiles]) {
			if (!@unlink(UPDIR.$frm[folder].$file)) $error .= "- $file konnte nicht gel&ouml;scht werden<br>";
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
			if (!@unlink(UPDIR.$frm[folder].$file)) $error .= "- $file konnte nicht gel&ouml;scht werden<br>";
		}

		$notDone .= "(".$t[error].")<br>";
		continue;
		}else{
		$picSize = "width=".$t[width]." height=".$t[height];
		}

		// create thumbnail
		$t = createPic(UPDIR.$frm[folder].$file, tnPath($id, $picid, extension($file)), $MAX_PIC_SIZE[tnWidth], $MAX_PIC_SIZE[tnHeight]);
		if ($t == -1) {
		$db->query("DELETE FROM gallery_pics WHERE id=$picid");
		unlink(picPath($id, $picid, extension($file)));
		$notDone .= "- $file (keine Rechte) <br>";
		if ($frm[delFiles]) {
			if (!@unlink(UPDIR.$frm[folder].$file)) $error .= "- $file konnte nicht gel&ouml;scht werden<br>";
		}
		continue;
		}else{
		$tnSize = "width=".$t[width]." height=".$t[height];
		}

		// update sizes in DB
		$db->query("UPDATE gallery_pics SET tnsize='$tnSize', picsize='$picSize' WHERE id=$picid", __FILE__, __LINE__, __FUNCTION__);

		// del uploaded pic, if requested
		if ($frm[delPics]) {
		if (!@unlink(UPDIR.$frm[folder].$file)) $error .= "- $file konnte nicht gel&ouml;scht werden<br>";
		}

		$done .= "- $file <br>";
	}
	closedir($directory);

	// delete directory if empty
	if (countFiles(UPDIR.$frm[folder]) == 0) {
		if (!@rmdir(UPDIR.$frm[folder]))
		$error .= "- Upload-Ordner ".UPDIR.$frm[folder]." konnte nicht gel&ouml;scht werden <br>";
	}

	if ($notDone) $notDone = "Folgende Files konnten nicht indiziert werden:<br>".$notDone;
	if ($done) {
		$done = "Folgende Files wurden indiziert:<br>".$done;
	}else{
		$notDone = "Es konnten keine Files indiziert werden! <br><br>".$notDone;
	}

return array('error'=>$notDone."<br>".$error, 'state'=>$done);
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
	$d = mysqli_fetch_array($e);
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
 * @version 1.0
 * @since 1.0 function added
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
		if ($user->id != 59) { Activities::addActivity($user->id, 0, 'hat sich auf <a href="'.$_SERVER['PHP_SELF'].'?show=pic&picID='.$pic_id.'">diesem Bild</a> markiert.<br/><br><a href="'.$_SERVER['PHP_SELF'].'?show=pic&picID='.$pic_id.'"><img src="'.imgsrcThum($pic_id).'" /></a>', 'i'); }
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
 * Prüft ob ein Benutzer bereits auf einem bestimmten Bild markiert wurde
 *
 * @author [z]keep3r
 * @author IneX
 * @date 19.08.2008
 * @version 1.1
 * @since 1.0 `19.08.2008` `keep3r` function added
 * @since 1.1 `20.08.2018` `IneX` minor SQL-Query improvements
 *
 * @param integer $picID ID des betroffenen Bildes
 * @global object $db Globales Class-Object mit allen MySQL-Methoden
 * @return boolean Gibt true/false zurück, je nachdem ob User<->Bild Verknüpfung gefunden wurde
 */
function checkUserToPic($userID, $picID)
{
	global $db;

	$sql = 'SELECT * FROM gallery_pics_users WHERE user_id = '.$userID.' AND pic_id = '.$picID;
	$result = $db->num($db->query($sql, __FILE__, __LINE__, __FUNCTION__));

	return ($result > 0 ? true : false);
}


/**
 * Markierte User holen
 *
 * Holt die Benutzer, welche auf einem bestimmten Bild markiert wurden
 *
 * @author IneX
 * @date 19.08.2008
 * @version 1.0
 * @since 1.0 function added
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
 * @author IneX
 * @date 18.10.2013
 * @version 1.1
 * @since 1.0 `18.10.2013` `IneX` function added
 * @since 1.1 `15.09.2019` `IneX` HTML-output & general code optimized
 *
 * @param integer $userid ID des Users dessen Bilder angezeigt werden sollen
 * @param integer $limit Maximale Anzahl von Bildern
 * @global object $db Globales Class-Object mit allen MySQL-Methoden
 * @global object $user Globales Class-Object mit den User-Methoden & Variablen
 * @return array Gibt ein Array mit allen Usern aus, welche auf dem Bild markiert sind
 */
function getUserPics($userid, $limit=1)
{
	global $db, $user;

	$html_out = null;
	$i = 1;
	$table_style = 'border-collapse: collapse; border-width:1px; border-style: solid; border-color: #CBBA79; text-align: center;';
	$td_style = 'border-collapse: collapse; border-width:1px; border-style: solid; border-color: #CBBA79; padding: 10px;';

	if ($userid > 0)
	{
		$sql = 'SELECT * FROM gallery_pics_users
				WHERE user_id='.(integer)$userid.'
				ORDER BY id ASC'
				.($limit > 0 ? ' LIMIT '.$limit : null); // LIMIT only when LIMIT Parameter given; 0 = all pics
		$result = $db->query($sql, __FILE__, __LINE__, __FUNCTION__);

		/** Hey, we've got some pics! */
		if ($db->num($result) > 0)
		{
			$html_out .= '<h3>'.$user->id2user($userid).'\'s Pics</h3>';
			$html_out .= '<table style="'.$table_style.'" width="100%">';

			while($rs = $db->fetch($result))
			{
				$img_id = $rs['pic_id'];
				$img_name = imgName($img_id);
				$file = imgsrcThum($img_id);
				if ($i == 1) {
					$html_out .= '<tr>';
				}
				$html_out .= '<td style="'.$td_style.'">'
							 .'<a href="/gallery.php?show=pic&picID='.$img_id.'">';

				$html_out .= '<img border="0" src="'.$file.'" style="width: 100%;max-width: 100%;"><br>';
				if ($img_name) $html_out .= $img_name.'</a>';
				$html_out .= '</td>';
				$i++;
				if ($i == 4) {
					$html_out .= '</tr>';
					$i = 1;
				}
			}
			$html_out .= '</table>';
		}

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
 * @since 1.0 `IneX` function added
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
	$sternli = '';
	for ($s=0;$s<$score;$s++){$sternli .= '*';} // Sternli mache
	if ($user_id != BARBARA_HARRIS) { Activities::addActivity($user->id, 0, 'hat <a href="'.$_SERVER['PHP_SELF'].'?show=pic&picID='.$pic_id.'">ein Bild</a> mit <b>'.$sternli.'</b> bewertet.<br/><br><a href="'.$_SERVER['PHP_SELF'].'?show=pic&picID='.$pic_id.'"><img src="'.imgsrcThum($pic_id).'" /></a>', 'i'); }
}


function doDelPic ($id) {
	global $db;

	if (!$id) user_error("Missing Parameter <i>id</i>", E_USER_ERROR);

	$e = $db->query("SELECT * FROM gallery_pics WHERE id='$id'", __FILE__, __LINE__, __FUNCTION__);
	$d = mysqli_fetch_array($e);
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
	$d = mysqli_fetch_array($e);


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

/**
 * @author [z]deep
 * @author IneX
 * @version 2.0
 * @since 1.0 function added
 * @since 2.0 added support for PNG image file types
 */
function isPic($file) {
	if (!is_file($file)) return 0;
	if (extension($file) == '.jpg') return 1;
	if (extension($file) == '.jpeg') return 1;
	if (extension($file) == '.gif') return 1;
	if (extension($file) == '.png') return 1;
	else return 0;
}

/**
 * @deprecated Use pathinfo($file, PATHINFO_EXTENSION);
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

/**
 * Count number of files in a directory
 *
 * @version 2.0
 * @since 1.0 function added
 * @since 2.0 `12.12.2022` `IneX` refactored to use glob() instead of a filestream
 *
 * @param string Internal Directory-Path to where count files, ending with a slash '/'
 * @usedby editAlbum(), doUpload()
 * @return int Number of files counted in $directory
 */
function countFiles ($directory_path)
{
	/** Add missing - but required - slash '/' to $directory_path */
	if (substr($directory_path, -1) != '/') $directory_path .= '/';

	if (!is_dir($directory_path)) $directory_filecount = -1; // To not break historic dependencies on this function...
	else $directory_filecount = count(glob($directory_path."*"));

	return $directory_filecount;
}

/**
 * Get internal Pic filepath
 *
 * @version 1.1
 * @since 1.0 function added
 * @since 1.1 `12.12.2022` `IneX` added validation of $extension string format (prefixing dot)
 *
 * @param int $albID ID of Pic's Gallery-Album
 * @param int $id The Pic's ID
 * @param string $extension The Pic's File Extension with a prefixed dot: '.jpg', '.png'
 * @return string Internal Filepath to Gallery Pic
 */
function picPath($albID, $id, $extension) {
	/** Fix missing - but required - '.' dot prefixing the $extension string */
	if (mb_substr($extension, 0, 1) != '.') $extension = '.'.$extension;
	return GALLERY_DIR.$albID.'/pic_'.$id.$extension;
}

/**
 * Get internal Pic-Thumbnail filepath
 *
 * @version 2.1
 * @since 1.0 function added
 * @since 2.0 `15.09.2018` added switch-case for handling non-image entries from gallery_pics
 * @since 2.1 `12.12.2022` `IneX` added validation of $extension string format (prefixing dot)
 *
 * @param int $albID ID of Pic's Gallery-Album
 * @param int $id The Pic's ID
 * @param string $extension The Pic's File Extension with a prefixed dot: '.jpg', '.png'
 * @return string Internal Filepath to Gallery Pic-Thumbnail
 */
function tnPath($albID, $id, $extension) {
	/** Fix missing - but required - '.' dot prefixing the $extension string */
	if (mb_substr($extension, 0, 1) != '.') $extension = '.'.$extension;

	switch ($extension)
	{
		case 'website': return GALLERY_DIR.$albID.'/tn_'.$id.'.png';
		case 'youtube': return GALLERY_DIR.$albID.'/tn_'.$id.'.jpg';
		case 'vimeo': return GALLERY_DIR.$albID.'/tn_'.$id.'.jpg';
		default: return GALLERY_DIR.$albID.'/tn_'.$id.$extension;
	}
}

/**
 * Get relative public Path to Gallery Pic
 *
 * @version 1.1
 * @since 1.0 function added
 * @since 1.1 `deep` ersetzt wegen mod_rewrite   return "/includes/gallery.readpic.php?id=$id";
 *
 * @return string External relative URL-path to Gallery Pic
 */
function imgsrcPic($id) {
	return "/gallery/".$id;
}

/**
 * Get relative public Path to Gallery Pic-Thumbnail
 *
 * @version 1.1
 * @since 1.0 function added
 * @since 1.1 `deep` ersetzt wegen mod_rewrite   return "/includes/gallery.readpic.php?id=$id&type=tn";
 *
 * @return string External relative URL-path to Gallery Pic-Thumbnail
 */
function imgsrcThum($id) {
	return "/gallery/thumbs/".$id;
}

/**
 * @deprecated replace all references to this function with new picHasTitle()
 * @see picHasTitle()
 * @todo replace all references to this function with new picHasTitle()
 */
function imgName($id) {
	global $db;

	$e = $db->query("SELECT id, name FROM gallery_pics WHERE id = $id", __FILE__, __LINE__, __FUNCTION__);
	$cur = mysqli_fetch_array($e);

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


/**
 * Create a z-Gallery ready Imagefile
 *
 * @author [z]deep
 * @author IneX
 * @version 4.0
 * @since 1.0 function added
 * @since 2.0 updated pathes
 * @since 3.0 major overhaul - added better error handling, added debugging infos
 * @since 4.0 added support for PNG image file types
 *
 * @FIXME getImageSize() funktioniert nicht mit .gif-Files
 */
function createPic($srcFile, $dstFile, $maxWidth, $maxHeight, $bgcolor=0)
{
	// errors
	if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> createPic(): %s, %s, %s, %s, %s', __FUNCTION__, __LINE__, $srcFile, $dstFile, $maxWidth, $maxHeight, $bgcolor));
	if (!isPic($srcFile)) {
		error_log(sprintf('<%s:%d> Wrong File Type: %s', __FUNCTION__, __LINE__, $srcFile));
		return false;
	}
	if (extension($srcFile) != extension($dstFile)) {
		error_log(sprintf('<%s:%d> Source- and Destination-Files have mismatching File Types: %s vs. %s', __FUNCTION__, __LINE__, $srcFile, $dstFile));
		return false;
	}

	$ext = extension($srcFile);
	if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> $ext: %s', __FUNCTION__, __LINE__, $ext));

	/** calc new pic size */
	if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> calc new pic size', __FUNCTION__, __LINE__));
	$img_size = getImageSize($srcFile);
	if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> $img_size: %s', __FUNCTION__, __LINE__, print_r($img_size,true)));
	if (!$img_size) return array('error'=>'keine Rechte');
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
	if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> $picWidth & $picHeight: %d x %d', __FUNCTION__, __LINE__, $picWidth, $picHeight));

	/** Create new Pic */
	if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> create new pic', __FUNCTION__, __LINE__));
	switch ($ext)
	{
		case '.jpg':
			if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> ImageCreateFromJPEG(): %s', __FUNCTION__, __LINE__, $srcFile));
			$src = ImageCreateFromJPEG($srcFile);
			if ($src === null) {
				error_log(sprintf('[ERROR] <%s:%d> Bild konnte nicht erzeugt werden', __FUNCTION__, __LINE__));
				return false;
			}
			if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> ImageCreateFromJPEG: %s', __FUNCTION__, __LINE__, ($src != null ? 'OK' : 'ERROR')));
			break;

		case '.gif':
			if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> ImageCreateFromGIF(): %s', __FUNCTION__, __LINE__, $srcFile));
			$src = ImageCreateFromGIF($srcFile);
			if ($src === null) {
				error_log(sprintf('[ERROR] <%s:%d> Bild konnte nicht erzeugt werden', __FUNCTION__, __LINE__));
				return false;
			}
			if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> ImageCreateFromGIF: %s', __FUNCTION__, __LINE__, ($src != null ? 'OK' : 'ERROR')));
			break;

		case '.png':
			if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> ImageCreateFromPNG(): %s', __FUNCTION__, __LINE__, $srcFile));
			$src = ImageCreateFromPNG($srcFile);
			if ($src === null) {
				error_log(sprintf('[ERROR] <%s:%d> Bild konnte nicht erzeugt werden', __FUNCTION__, __LINE__));
				return false;
			}
			if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> ImageCreateFromPNG: %s', __FUNCTION__, __LINE__, ($src != null ? 'OK' : 'ERROR')));
			break;

		default:
			error_log(sprintf('<%s:%d> Wrong File Type', __FUNCTION__, __LINE__));
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
			error_log(sprintf('[ERROR] <%s:%d> Bild konnte nicht modifiziert werden', __FUNCTION__, __LINE__));
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
			error_log(sprintf('[ERROR] <%s:%d> ImageCopyResampled: %s => %s', __FUNCTION__, __LINE__, $src, $dst));
			return false;
		}

		$ret = array('width'=>$picWidth, 'height'=>$picHeight);
	}

	switch ($ext) {
		case '.jpg':
			if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> ImageJPEG(%s, %s)', __FUNCTION__, __LINE__, $dst, $dstFile));
			if (!ImageJPEG($dst, $dstFile)) {
				error_log(sprintf('[ERROR] <%s:%d> ImageJPEG: %s => %s', __FUNCTION__, __LINE__, $dst, $dstFile));
				return false;
			}
			if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> ImageJPEG() OK', __FUNCTION__, __LINE__));
			break;

		case '.gif':
			if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> ImageGIF(%s, %s)', __FUNCTION__, __LINE__, $dst, $dstFile));
			if (!ImageGIF($dst, $dstFile)) {
				error_log(sprintf('[ERROR] <%s:%d> ImageGIF: %s => %s', __FUNCTION__, __LINE__, $dst, $dstFile));
				return false;
			}
			if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> ImageGIF() OK', __FUNCTION__, __LINE__));
			break;

		case '.png':
			if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> ImagePNG(%s, %s)', __FUNCTION__, __LINE__, $dst, $dstFile));
			if (!ImagePNG($dst, $dstFile)) {
				error_log(sprintf('[ERROR] <%s:%d> ImagePNG: %s => %s', __FUNCTION__, __LINE__, $dst, $dstFile));
				return false;
			}
			if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> ImagePNG() OK', __FUNCTION__, __LINE__));
			break;

		default:
			error_log(sprintf('[ERROR] <%s:%d> Wrong File Type', __FUNCTION__, __LINE__));
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

function getAlbumLinkRandomThumb($album_id, $showAlbumName=false, $HQimage='normal') {
	global $db, $user;

	$result = $db->query('SELECT id, name, (SELECT name FROM gallery_albums WHERE id='.$album_id.') albumname FROM gallery_pics p WHERE album='.$album_id.' '.ZENSUR.' ORDER BY RAND() LIMIT 1', __FILE__, __LINE__, __FUNCTION__);
	$rs = $db->fetch($result);
	$file = ($HQimage === 'high' ? imgsrcPic($rs['id']) : imgsrcThum($rs['id']));

	$html =
		'<a href="/gallery.php?show=albumThumbs&albID='.$album_id.'" class="center">'
		.($showAlbumName === true ? '<h3>'.remove_html($rs['albumname']).'</h3>' : '')
		.'<img border="0" src="'.$file.'" itemprop="image" style="width: 100%;max-width: 100%;">'
		.(!empty($rs['name']) ? '<p>'.text_width(remove_html($rs['name']), 80, '...').'<p>' : '')
		.'</a>'
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
 * Get Daily Pic
 *
 * @author [z]biko
 * @author IneX
 * @version 3.0
 * @since 1.0
 * @since 2.0 `19.03.2018` `IneX` added Telegram Notification (photo message)
 * @since 2.1 Telegram will send now high-res photo, instead of low-res thumbnail
 * @since 2.2 changed to new Telegram Send-Method
 * @since 3.0 `18.08.2018` `IneX` function now only returns Daily Pic, generating a new Daily Pic is now done in setNewDailyPic()
 *
 * @see formatGalleryThumb()
 * @global object $db Globales Class-Object mit allen MySQL-Methoden
 * @return string|boolean HTML-formatted String with Daily Pic Thumbnail, or false if something went wrong
 */
function getDailyThumb()
{
	global $db;

	/** Get current Daily Pic */
	$e = $db->query('SELECT g.* FROM gallery_pics g
					 INNER JOIN periodic p ON p.id=g.id
					 WHERE p.name="daily_pic"', __FILE__, __LINE__, __FUNCTION__);
	$d = $db->fetch($e);

	/** If Daily Pic ID is available */
	if (!empty($d) && $d['id'] > 0)
	{
		return formatGalleryThumb($d);

	/** Daily Pic not found... */
	} else {
		error_log(sprintf('[NOTICE] <%s:%d> %s Daily Pic not found', __FILE__, __LINE__, __FUNCTION__));
		return false;
	}
}


/**
 * Set a new random Daily Pic
 *
 * @author IneX
 * @date 18.08.2018
 * @version 1.7
 * @since 1.0 `18.08.2018` function added
 * @since 1.1 `20.08.2018` minor code updates after extracting function from getDailyThumb()
 * @since 1.5 `10.09.2018` excluded APOD Gallery-Pics from being assigned as Daily Pic
 * @since 1.6 `04.12.2018` Bug #xxx: added Gallery-Name to Telegram-Notification for Daily Pic
 * @since 1.7 `14.11.2019` Added "&token="-Param for Telegram-Bot API on $telegram->send->photo()
 *
 * @uses SITE_URL
 * @uses imgsrcPic()
 * @uses picHasTitle()
 * @uses Telegram::send::photo()
 * @global object $db Globales Class-Object mit allen MySQL-Methoden
 * @global object $telegram Globales Class-Object mit den Telegram-Methoden
 * @return boolean Returns true or false, depending if new Daily Pic was set or not
 */
function setNewDailyPic()
{
	global $db, $telegram;

	/** Check if current Daily Pic is still from Today... */
	$sql = $db->query('SELECT id, TO_DAYS(p.date)-TO_DAYS(NOW()) upd
					 FROM periodic p
					 WHERE p.name="daily_pic"', __FILE__, __LINE__, __FUNCTION__);
	$currdp = $db->fetch($sql);

	/** If current Daily Pic is old - generate a new one: */
	if (!$currdp || $currdp['upd'] < 0)
	{
		/** Randomly select a new Gallery-Pic */
		$sql = $db->query('SELECT id, (SELECT name FROM gallery_albums WHERE id = album) galleryname FROM gallery_pics WHERE zensur="0" AND id<>'.$currdp['id'].' AND album<>'.APOD_GALLERY_ID.' ORDER BY RAND() LIMIT 1', __FILE__, __LINE__, __FUNCTION__);
		$newdp = $db->fetch($sql);

		if (!empty($newdp) || $newdp['id'] > 0)
		{
			/** Add the new Daily-Pic into the `periodic` Database-Table */
			$db->query('REPLACE INTO periodic (name, id, date) VALUES ("daily_pic", '.$newdp['id'].', NOW())', __FILE__, __LINE__, __FUNCTION__);
			if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> new Daily Pic generated: %d from album "%s"', __FUNCTION__, __LINE__, $newdp['id'], $newdp['galleryname']));

			/**
			 * Telegram Notification auslösen
			 *     url = URL to the Pic
			 *     caption = "Daily Pic: {Title - if available} [Gallery-Name]"
			 */
			$imgAuthToken = md5(TELEGRAM_API_URI);
			$imgUrl = SITE_URL.imgsrcPic($newdp['id']).'?token='.$imgAuthToken;
			$picTitle = picHasTitle($newdp['id']);
			$picGallery = $newdp['galleryname'];
			$imgCaption = t('telegram-dailypic-notification', 'gallery', [ (empty($picTitle) ? ' ' : $picTitle), (empty($picGallery) ? ' ' : $picGallery) ]);
			$telegram->send->photo('group', $imgUrl, $imgCaption, ['disable_notification' => 'true']);

			return true;

		/** Error updating Daily Pic */
		} else {
			if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> Updating Daily Pic: ERROR', __FUNCTION__, __LINE__));
			return false;
		}

	/** Daily Pic for today is already set */
	} else {
		if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> Daily Pic for today already set', __FUNCTION__, __LINE__));
		return false;
	}
}

/**
 * Get amount n of best rated pics
 *
 * @author IneX
 * @version 2.0
 * @since 1.0 `23.06.2007` function added as part of Bug #609
 * @since 2.0 `02.01.2019` fixed Bug #770: "'Bestes Pic' soll nur Pics berücksichtigen mit >1 Votes"
 *
 * @see imgsrcThum(), imgName(), getNumVotes()
 * @global	object	$db			Globales Class-Object mit allen MySQL-Methoden
 * @global	object	$user		Globales Class-Object mit den User-Methoden & Variablen
 * @param	integer	$album_id	Album-ID für welches die Top Pics angezeigt werden sollen. Default: null (none)
 * @param	integer	$limit		Max. Anzahl anzuzeigender Top Pics. Default: 5
 * @param	boolean	$ranking_list	(Optional) Zusätzliche Settings für Darstellung der Top Pics als HTML-Tabelle mit Ranking. Default: false
 * @return	string	HTML-Code mit den Top Gallery-Pic Thumbnails des $album_id
 */
function getTopPics($album_id=null, $limit=5, $ranking_list=false)
{
	global $db, $user;

	$i=1;
	$html_out = '';

	if ($ranking_list === true) $html_out .= '<table class="border">';

	/**
	 * $album_id specified
	 * show best rated pics from this Gallery only
	 */
	if (!empty($album_id) && is_numeric($album_id) && $album_id > 0)
	{
		$sql = 'SELECT
				 p.id,
				 p.album,
				 p.name,
				 p_vote.pic_id,
				 p.zensur,
				 AVG(p_vote.score) as avgScore,
				 COUNT(p_vote.pic_id) as numVotes
			 FROM
			 	gallery_pics p LEFT OUTER
			 JOIN gallery_pics_votes p_vote ON p.id = p_vote.pic_id
			 WHERE p.album = '.$album_id.' AND p_vote.score > 0 AND p.zensur != "1"
			 GROUP BY p_vote.pic_id
			 HAVING numVotes >= 3
			 ORDER BY avgScore DESC, numVotes DESC, p.id ASC
			 LIMIT '.$limit;
		$result = $db->query($sql, __FILE__, __LINE__, __FUNCTION__);

		while($rs = $db->fetch($result)) {
			$file = imgsrcThum($rs['pic_id']);

			$color = ($i % 2 == 0) ?  BACKGROUNDCOLOR : TABLEBACKGROUNDCOLOR;

			//$anz_votes = getNumVotes($rs['pic_id']);
			$anz_votes = $rs['numVotes'];

			if ($ranking_list === true) {
				$html_out .=
					'<tr bgcolor="'.$color.'">'
					.'<td align="left">'.$i.'.</td>'
					.'<td align="center">';
			}

			$html_out .= '<a href="/gallery.php?show=pic&picID='.$rs['pic_id'].'">';

			if ($rs['name']) $html_out .= $rs['name'].'<br>';

			$html_out .=
					'<img border="0" src="'.$file.'" style="width: 100%;max-width: 100%;"></a>'
					.'<br>Bild Note: '.round($rs['avgScore'],1).' '
			;

			$votes = (($anz_votes > 1) || ($anz_votes == 0)) ? $anz_votes." Votes" : $anz_votes." Vote";
			$html_out .=
				'<small>('.$votes.')</small>'
				.'<br>'
			;

			if ($ranking_list === true) $html_out .= '</td></tr>';

			$i++;
		}
	}

	/**
	 * No $album_id given
	 * Show Top Pics over all Gallery Albums
	 */
	else {
		$sql = 'SELECT pic_id, AVG(score) as avgScore, COUNT(pic_id) as numVotes,
					(SELECT zensur FROM gallery_pics WHERE id = pic_id) zensiert
				 FROM gallery_pics_votes
				 WHERE score > 0
				 GROUP BY pic_id
				 HAVING (zensiert IS NULL OR zensiert = 0) AND numVotes >= 3
				 ORDER BY avgScore DESC, numVotes DESC, pic_id ASC
				 LIMIT '.$limit;
		$result = $db->query($sql, __FILE__, __LINE__, __FUNCTION__);

		while($rs = $db->fetch($result)) {
			$file = imgsrcThum($rs['pic_id']);

			$color = ($i % 2 == 0) ?  BACKGROUNDCOLOR : TABLEBACKGROUNDCOLOR;

			$anz_votes = $rs['numVotes'];

			if ($ranking_list === true) {
				$html_out .=
					'<tr bgcolor="'.$color.'">'
					.'<td align="left">'.$i.'.</td>'
					.'<td align="center">';
			}

			$html_out .= '<a href="/gallery.php?show=pic&picID='.$rs['pic_id'].'">';

			$pic_name = imgName($rs['pic_id']);
			if ($pic_name) $html_out .= $pic_name.'<br>';

			$html_out .=
					'<img border="0" src="'.$file.'" style="width: 100%;max-width: 100%;"></a>'
					.'<br>Bild Note: '.round($rs['avgScore'],1).' '
			;

			$votes = $anz_votes.' '.(($anz_votes > 1) || ($anz_votes == 0) ? 'Votes' : $anz_votes.'Vote');
			$html_out .= '<small>('.$votes.')</small>'.'<br>';

			if ($ranking_list === true) $html_out .= '</td></tr>';

			$i++;
		}
	}

	if ($ranking_list === true) $html_out .= '</table>';

	return $html_out;
}

/**
 * Format Gallery-Pic Thumbnail (HTML Output)
 *
 * @author [z]biko
 * @author IneX
 * @version 2.0
 * @since 1.0 function added
 * @since 2.0 `09.09.2018` Resolved Bug #759: added Pic-Title to HTML-Output & refactored function a bit
 *
 * @uses text_width()
 * @uses remove_html()
 * @see Thread::getNumPosts()
 * @global	object	$db		Globales Class-Object mit allen MySQL-Methoden
 * @global	object	$user	Globales Class-Object mit den User-Methoden & Variablen
 * @param	object	$rs		DB-Query Result Object containing all gallery_pics rows & values for one image
 * @return	string	HTML-Code for the Gallery-Pic Thumbnail
 */
function formatGalleryThumb($rs)
{
	global $db, $user;

	$file = imgsrcThum($rs['id']);

	/** HTML-Markup for Pic Thumbnail */
	$html = '<a href="/gallery.php?show=pic&picID='.$rs['id'].'">' // Link
			.text_width(remove_html($rs['name']), 80, '...').'<br>' // Pic-Title
			.'<img border="0" src="'.$file.'" style="width: 100%;max-width: 100%;"><br>' // Image-Tag
			.Thread::getNumPosts('i', $rs['id']).' Comments</a>'; // No. of Comments

	/** Comment-Unreads bei Member holen & anzeigen... */
	if ($user->typ == USER_MEMBER) {
		if (!$user_id) { $user_id = $user->id; }
		$sql = 'SELECT count(c.id) anz
				FROM comments c, comments_unread u
				WHERE c.board = "i" AND c.thread_id='.$rs['id'].' AND u.comment_id=c.id AND u.user_id='.$user_id;
		$e = $db->query($sql, __FILE__, __LINE__, __FUNCTION__);
		$d = $db->fetch($e);

		/** Wenn das Pic 1 oder mehr Unreads hat... */
		if ($d['anz'] > 0) $html .= ' <small>('.$d['anz'].' unread)</small>'; // Unread Comments on Pic
	}

	return $html;
}

/**
 * Updates a Pic's Title
 *
 * @global object $db Globales Class-Object mit allen MySQL-Methoden
 * @return void
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
 * Checks if Pic has a Title - if yes, return it
 *
 * @author IneX
 * @date 21.01.2017
 *
 * @global object $db Globales Class-Object mit allen MySQL-Methoden
 * @return string|bool If set, returns the Pic's title - otherwise 'false'
 */
function picHasTitle($picID)
{
	global $db;

	if (is_numeric($picID) && $picID > 0) {
		$e = $db->query('SELECT name FROM gallery_pics WHERE id='.$picID.' LIMIT 1', __FILE__, __LINE__, __FUNCTION__);
		$d = $db->fetch($e);
	}
	if ($d) return $d['name'];
	else return false;
}

/**
 * Flip an image vertically/horizontally
 *
 * @TODO use native PHP Image Flip function! http://php.net/manual/function.imageflip.php
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

/**
 * Findet und returned die Album-ID zu welcher ein Pic gehört
 *
 * @author IneX
 * @date 11.09.2018
 * @version 1.0
 * @since 1.0 `11.09.2018` function added
 *
 * @param integer $id ID des Pics für welches das Album geholt werden soll
 * @global object $db Globales Class-Object mit allen MySQL-Methoden
 * @return integer|boolean Album-ID des Pics - oder false
 */
function pic2album($id)
{
	global $db;

	/** Validate passed $id parameter */
	if ($id <= 0 || !is_numeric($id)) user_error('Missing Parameter "id"', E_USER_ERROR);

	$sql = $db->query('SELECT album FROM gallery_pics WHERE id='.$id.' LIMIT 0,1', __FILE__, __LINE__, __FUNCTION__);
	$picAlbum = $db->fetch($sql, __FILE__, __LINE__, __FUNCTION__);

	return (!empty($picAlbum['album']) ? $picAlbum['album'] : false);
}

/**
 * Video Thumbnail von YouTube & Vimeo holen
 *
 * Each YouTube & Vimeo video has multiple sizes of generated images. They are predictably formatted.
 * @link https://stackoverflow.com/questions/2068344/how-do-i-get-a-youtube-video-thumbnail-from-the-youtube-api
 * @link https://stackoverflow.com/questions/1361149/get-img-thumbnails-from-vimeo
 *
 * @author IneX
 * @date 14.09.2018
 * @version 1.0
 * @since 1.0 `14.09.2018` function added
 *
 * @TODO If required (later), use $format Parameter to dynamically grab URLs from API response, instead of hard-coded $thumbnailUrl Path
 *
 * @see cURLfetchUrl()
 * @param string $service Name der Plattform, gültige Werte: 'youtube' oder 'vimeo'
 * @param string $video_id Video-ID für welches ein Thumbnail geholt werden soll
 * @param string $image_size (Optional) Angabe als string, gültige Werte: 'small', 'medium' oder 'large' - default: 'small'
 * @param string $output_to (Optional) Angabe als string wie das Thumbnail ausgegeben werden soll, gültige Werte: 'display'=nur anzeigen oder 'datei-zielpfad'=download auf dem server
 * @return string|boolean Gibt die Bild-URL zurück wenn $output_to='display' - oder true/false wenn $output_to='datei-zielpfad'
 */
function getVideoThumbnail($service, $video_id, $image_size='small', $output_to='display')
{
	/** Validate & format passed parameters */
	if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> getVideoThumbnail(): %s, %s, %s, %s', __FUNCTION__, __LINE__, $service, $video_id, $image_size, $output_to));
	if (is_array($service) || is_array($video_id) || is_array($image_size) || is_array($output_to)) return false;
	if (is_numeric($service) || is_numeric($image_size) || is_numeric($output_to)) return false;
	if (strpos($video_id, '?') > 0) $video_id = strtok($video_id, '?');
	$service = strtolower($service);

	$service_data =  [
					 'youtube' => [
									 'url' =>	 'https://img.youtube.com/vi/%s/%s.jpg'
									,'size' =>	 [
													 'small' => 'default'
													,'medium' => 'mqdefault'
													,'large' => 'hqdefault'
												 ]
								 ]
					,'vimeo' => [
									 'url' =>	 'https://i.vimeocdn.com/video/%s_%s.jpg'
									,'size' =>	 [
													 'small' => '100x75'
													,'medium' => '200x150'
													,'large' => '640'
												 ]
								 ]
					 ];
	$thumbnailUrl = sprintf($service_data[$service]['url'], $video_id, $service_data[$service]['size'][$image_size]);
	if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> $thumbnailUrl: %s', __FUNCTION__, __LINE__, $thumbnailUrl));

	/** Download Video-Thumbnail from URL to path as specified in $output_to */
	if ($output_to != 'display')
	{
		/** Fetch and save the Thumbnail image to $output_to */
		return (cURLfetchUrl($thumbnailUrl, $output_to) ? true : false);

	} else {
		/** Return URL to Video-Thumbnail */
		return $thumbnailUrl;
	}
}
