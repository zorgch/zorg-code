<?php
//=============================================================================
// includes
//=============================================================================
require_once( __DIR__ .'/includes/main.inc.php');

$geaechtet = array();

//=============================================================================
// Functions
//=============================================================================

function exec_newpassword() {
	global $db, $user;
	if($_POST['old_pass'] && $_POST['new_pass'] && $_POST['new_pass_2']) {
		$crypted_old_pass = crypt_pw($_POST['old_pass']);

		if($crypted_old_pass == $user->userpw) {
			if($_POST['new_pass'] == $_POST['new_pass_2']) {
				$crypted_new_pass = crypt_pw($_POST['new_pass']);
				$sql = "UPDATE user set userpw = '$crypted_new_pass'
				WHERE id = '$_SESSION[user_id]'";
				$db->query($sql, __FILE__, __LINE__);
				$error = "Dein Passwort wurde erfolgreich ge&auml;ndert!";
			} else {
				$error = "Du hast dich vertippt, bitte wiederholen! (Tipp? chasch au n? ?)";
			}
		} else {
			$error = "Das alte Passort ist falsch! (bisch du h?ng?, hai echt)";
		}
	}
	return $error;
}

function exec_changeprofile() {
	global $db, $user, $geaechtet;
	$error[0] = FALSE;
	if(count($_POST) && $_POST['email']) {
		if(check_email($_POST['email']) && !$geaechtet[$_SESSION['user_id']]) {
			if (!$_POST['addle']) $_POST['addle'] = '0';
			if (!$_POST['chess']) $_POST['chess'] = '0';
			if (!$_POST['zorger']) $_POST['zorger'] = '0';

			$m_e = $db->query("SELECT * FROM menus WHERE name='$_POST[mymenu]'", __FILE__, __LINE__);
			$m = $db->fetch($m_e);
			if ($m) $_POST['mymenu'] = $m['tpl_id'];
			else $_POST['mymenu'] = '';

			for($i = 0; $i < count($_POST['boards']); $i++) {
				$forum_boards .= $_POST['boards'][$i].',';
			}

			$sql = "
			UPDATE user
			SET clan_tag = '".htmlentities($_POST['clan_tag'])."',
			email = '$_POST[email]',
			forummaxthread = '$_POST[forummaxthread]',
			addle = '$_POST[addle]',
			chess = '$_POST[chess]',
			email_notification = '$_POST[email_notification]',
			menulayout = '$_POST[menulayout]',
			mymenu = '$_POST[mymenu]',
			icq = '$_POST[icquin]',
			forum_boards_unread = '".$forum_boards."',
			zorger = '$_POST[zorger]'
			WHERE id = '$_SESSION[user_id]'";
			$db->query($sql, __FILE__, __LINE__);
			$error[0] = TRUE;
		} else {
			$error[1] = "Diese E-Mail Adresse ist ung&uuml;ltig! (Wotsch wieder cheat&auml; ?)";
		}
	}
	return $error;
}

function exec_uploadimage() {

	global $user, $db;

	$error[0] = false;
	if (!$_FILES[image][name]) return $error;
	if($_FILES[image][error] != 0) {
		$error[1] = "Das Bild konnte nicht &uuml;bertragen werden!<br />";
		return $error;
	}
	if ($_FILES[image][type] != "image/jpeg" && $_FILES[image][type] != "image/pjpeg") {
		 $error[1] = "Dies ist kein JPEG Bild! (Mu&auml;sch n&ouml;d mein&auml;!)<br />";
		 return $error;
	}

	// Zuerst altes Bild archivieren...
	$currtimestamp = time();
	$oldfilename = $user->id.".jpg";
	$oldfile = $_SERVER['DOCUMENT_ROOT'].USER_IMGPATH.$user->id.".jpg";
	$oldfile_tn = $_SERVER['DOCUMENT_ROOT'].USER_IMGPATH.$user->id."_tn.jpg";
	$archiv = $_SERVER['DOCUMENT_ROOT'].USER_IMGPATH."archiv/".$user->id.$currtimestamp.".jpg"; // (mit timestamp versehen, damits keine pics Ÿberschreibt
	$archiv_tn = $_SERVER['DOCUMENT_ROOT'].USER_IMGPATH."archiv/".$user->id.$currtimestamp."_tn.jpg"; // (mit timestamp versehen, damits keine pics Ÿberschreibt
	
	// Aber nur wenn bereits ein Pic raufgeladen wurde (und nicht das Standartpic gesetzt ist)
	if (file_exists($oldfile)) {
		if (!copy($oldfile, $archiv)) { // zuerst das grosse...
			print('oldfile: '.$oldfile.'<br />archiv: '.$archiv.'<br /><br /><font color="red">Fehler beim archivieren!</font><br />');
			$error[1] = "Original Bild konnte nicht archiviert werden. ";
			return $error;
		}
		if (!copy($oldfile_tn, $archiv_tn)) { // ...und dann noch das kleine
			print('oldfile_tn: '.$oldfile_tn.'<br />archiv_tn: '.$archiv_tn.'<br />');
			$error[1] = "Thumbail Bild konnte nicht archiviert werden.";
			return $error;
		}
		// DEAKTIVIERT WEIL ZUVOR NOCH ALLE PHP-FILES †BERPR†FT WERDEN M†SSEN, OB DA NOCH WAS BEZ†GLICH USERPICS DRIN IST, WEGEN DER NEUEN NAMENGEBUNG!
		/*$sql = "SELECT * FROM userpics
			WHERE user_id = $user->id AND image_name = '".$oldfilename."'";
		if ($db->query($sql, __FILE__, __LINE__)) {
			$sql = "UPDATE userpics
				SET image_replaced = $currtimestamp
				WHERE user_id = $user->id AND image_name = '".$oldfilename."'
				";
			$db->query($sql, __FILE__, __LINE__);
		} else {
			$sql = "INSERT INTO userpics
				(user_id, image_name, image_title, image_added, image_replaced)
				VALUES
				($user->id, $oldfilename, $oldfilename, now(), now())
				";
			$db->query($sql, __FILE__, __LINE__);
		}*/
	}



	// ...danach das neue raufladen und db-gschmŠus machen
	$sql = "INSERT INTO userpics
			(user_id, image_name, image_title, image_added)
			VALUES
			($user->id, '".$_FILES[image][name]."', '".$_FILES[image][name]."', now())
			";
	$db->query($sql, __FILE__, __LINE__);

	$tmpfile = $_SERVER['DOCUMENT_ROOT'].USER_IMGPATH."upload/$user->id.jpg";
	if (!move_uploaded_file($_FILES[image][tmp_name], $tmpfile)) {
		$error[1] = "Bild konnte nicht bearbeitet werden.";
		return $error;
	}

	$e = createPic($tmpfile, $_SERVER['DOCUMENT_ROOT'].USER_IMGPATH.$user->id."_tn.jpg", 150, 150, array(0,0,0));
	if ($e[error]) {
		$error[1] = $e[error];
		return $error;
	}

	$e = createPic($tmpfile, $_SERVER['DOCUMENT_ROOT'].USER_IMGPATH.$user->id.".jpg", 500, 500);
	if ($e[error]) {
		$error[1] = $e[error];
		return $error;
	}

	@unlink($tmpfile);
	$user->image = USER_IMGPATH_PUBLIC.$user->id."_tn.jpg";
	$error[0] = true;
	return $error;
}





//=============================================================================
// Layout & code
//=============================================================================

Messagesystem::execActions();



if($user->id && $_GET['do'] == "view" && !$_GET['user_id']) {
		$error = exec_changeprofile();
}


if(!$_GET['do'] || $_SESSION['user_id']) {
	$pagetitle = $user->id2user($_GET['user_id'], TRUE);
} elseif($_GET['regcode']) {
	$pagetitle = 'Account bestätigung';
} else {
	$pagetitle = 'Userlist';
}

$smarty->assign('tplroot', array('page_title' => $pagetitle));
$smarty->display('file:layout/head.tpl');
echo menu("zorg");
echo menu("user");
echo "<br />";

if(!$_GET['do'] && !$_GET['user_id'] && !$_GET['regcode']) {
	$smarty->display('tpl:219');
// der untenstehende Code wurde im Smarty Template 219 realisiert
/*$sql = "
		SELECT
			u.id,
			u.username,
			u.clan_tag,
			u.usertype,
			UNIX_TIMESTAMP(u.currentlogin) as currentlogin,
			u.button_use,
			u.posts_lost,
			count(c.comment_id) as unread
		FROM user u
		LEFT JOIN comments_unread c
			ON u.id = c.user_id
		GROUP by u.id
		ORDER by u.currentlogin DESC
	";
	$result = $db->query($sql);
	echo(
		'<table width="60%" class="border" cellpadding="2" cellspacing="0">'
		.'<tr class="title">'
		//.'<td align="left"></td>'
		.'<td>Username</td>'
		.'<td>lastlogin</td>'
		.'<td>chnopf</td>'
		.'<td>lost</td>'
		.'<td>unread</td>'
		.'</tr>'
	);
	$i = 0;
	while($rs = $db->fetch($result)) {
		if(($i % 2) == 0) {
			$bgcol = " bgcolor=#".TABLEBACKGROUNDCOLOR." ";
		} else {
			$bgcol = "";
		}
		echo(
			'<tr>'
			.'<td '.$bgcol.'>'
			.'<a href="'.$_SERVER['PHP_SELF'].'?user_id='.$rs['id'].'">'
			.$rs['clan_tag'].$rs['username']
			.'</a></td><td align="left" '.$bgcol.'>'
			.datename($rs['currentlogin'])
			.'</td><td align="left" '.$bgcol.'>'
			.$rs['button_use']
			.'</td><td align="left" '.$bgcol.'>'
			.$rs['posts_lost'].' posts'
			.'</td><td align="left" '.$bgcol.'>'
			.number_format($rs['unread'],"","","'")
			.'<td></tr>'
		);
		$i++;
	}
	echo '</table>';*/
}


// Mein Profil aendern ---------------------------------------
if($_SESSION['user_id']) {
	if($_GET['do'] == "view" && !$_GET['user_id']) {
		$img_error = exec_uploadimage();
		$error2 = exec_newpassword()."<br />";
		//profile
		echo "<form action='$_SERVER[PHP_SELF]?do=view' method='post' enctype='multipart/form-data'>";
		echo "<input type='hidden' name='do' value='update' />";
		echo "<table width='650' align='center' class='case'>
			 <tr><td align='center' colspan='3' class='title'>
			 <b>Mein Profil</b>
			 </td>
			 </tr>";
			 
		if($error[0] != TRUE && $_POST['do'] <> "update" || $img_error[0] != TRUE && $_POST['do'] <> "update") {
			$m_e = $db->query("SELECT * FROM menus WHERE tpl_id='$user->mymenu'", __FILE__, __LINE__);
			$m = $db->fetch($m_e);
			$mymenu = $m['name'];
			
			//username
			echo "<tr><td align='left'><b>Benutzer:</b></td>
				  <td align='left'>
				  <input type='text' class='text' value='$user->username' readonly size='32'>
				  </td>";
			
			//image	  
			echo "<td rowspan='10' align='center' valign='top'>";
			
			$img = "<p><img src='$user->image'></p>";

			echo  "<table class='case'>
				  <tr><td align='left' valign='top'>
				  $img
				  <b>neues Bild:</b> <input type='file' name='image' class='text' size='18'>
				  </td></tr>
				  </table>";
			
			echo  "</td>
			 	  </tr>";
			 	  
			//email
			echo "<tr><td align='left'><b>E-Mail:</b></td>
				  <td align='left'>
				  <input type='text' name='email' class='text' value='$user->email' size='32'>
				  </td></tr>";
			
			if ($user->email_notification) $email_notification_checked = 'checked';
			else $email_notification_checked = '';
			echo "<tr><td colspan='2' align='center'><br/>
				<input type='checkbox' name='email_notification' value='1' $email_notification_checked>
				<b>E-Mail Benachrichtigungen empfangen</b><br/><br/>
				</td></tr>";
			
			//clan tag
			echo "<tr><td align='left'><b>Clan Zeichen</b>:</td>
				  <td align='left'>
				  <input type='text' name='clan_tag' class='text' value='$user->clantag' size='12'>
				  </td></tr>";
				  
			// icq uin
			echo "<tr><td align='left'><b>ICQ</b>:</td>
				  <td align='left'>
				  <input type='text' name='icquin' class='text' value='$user->icq' size='10'>
				  </td></tr>";
				  
			//forummaxthread
			echo "<tr><td align='left'><b>Anzeige Tiefe im Forum:</b></td>
				  <td align='left'>
				  <input type='text' name='forummaxthread' class='text' size='4' value='$user->maxdepth'>
				  </td></tr>";
				  
			// zorger
			if ($user->zorger) $zorger_checked = "checked";
			else $zorger_checked = "";
			echo  "<tr><td><b>Zooomclan Layout?</b></td><td align='left'>
				  <input type='checkbox' name='zorger' value='1' $zorger_checked>
				  </td></tr>";
			
			// menulayout
			echo
				"<tr>".
					"<td align='left'><b>Menu Layout</b></td>".
					"<td align='left'>".
						"<select name='menulayout' size=1>".
							"<option value='' ";if($user->menulayout=='')echo "selected"; echo ">Default</option>".
							"<option value='1' ";if($user->menulayout==1)echo "selected"; echo ">Menu Layout 1</option>".
							"<option value='2' ";if($user->menulayout==2)echo "selected"; echo ">Menu Layout 2</option>".
						"</select>".
					"</td>".
				"</tr>"
			;

			// my menu
			echo
				"<tr>".
					"<td align='left'><b>My Menu</b><br /><small>(Dieses Menu wird dir immer angezeigt)</small></td>".
					"<td align='left' valign='top'><input type='text' class='text' name='mymenu' value='$mymenu'></td>".
				"</tr>"
			;

			// addle
			if ($user->addle) $addle_checked = "checked";
			else $addle_checked = "";
			echo "<tr><td>&nbsp;</td><td align='left'>
				<input type='checkbox' name='addle' value='1' $addle_checked>
				Ich will Addle spielen.
				</td></tr>";

			// chess
			if ($user->chess) $chess_checked = 'checked';
			else $chess_checked = '';
			echo "<tr><td>&nbsp;</td><td align='left'>
				<input type='checkbox' name='chess' value='1' $chess_checked>
				Ich will Schach spielen.
				</td></tr>";
				
			// Forumboards	
			echo(
				'<tr><td colspan="3">Forum-Boards verfolgen:<br />'
				.Forum::getForumBoards($user->forum_boards_unread)
				.'</td></tr>'
			);
			
			// Formular senden
			echo "<tr>
				  <td align='left' colspan='3'>".$img_error[1]
				  .$error[1]."<br />
				  <input type='submit' name='senden' class='button' value='speichern'>
				  </td></tr>";
			echo "</table>";
			echo "</form>";
				
				
		} else {
			echo "<tr><td align='left' colspan='3'>
				 &Auml;nderungen wurden erfolgreich gespeichert!<br /><br />
				 <a href='$_SERVER[PHP_SELF]?do=view'>Profil anzeigen</a>
				 </td></tr></table></form>";
		}

		// Passwort-Formular ------------------------------------------------------
		echo "<form action='$_SERVER[PHP_SELF]?do=view' method='post'>";
		echo "<table width='600' class='case' align='center' >
			  <tr><td align='center' colspan='2' class='title'>
			 <b> Passwort &auml;ndern</b>
			  </td></tr>";
		echo "<tr><td align='left'><b>altes Passwort:</b></td>
			  <td align='left'>
			  <input type='password' class='text' size='20' name='old_pass'>
			  </td></tr>";
		echo "<tr><td align='left'><b>neues Passwort:</b></td>
			  <td align='left'>
			  <input type='password' class='text' size='20' name='new_pass'>
			  </td></tr>";
		echo "<tr><td align='left'><b>neues Passwort wiederholen:</b></td>
			  <td align='left'>
			  <input type='password' class='text' size='20' name='new_pass_2'>
			  </td></tr>";
		echo "<tr>
			  <td align='left' colspan='2'>".$error2."
			  <input type='submit' class='button' name='send' value='speichern'>
				</td></tr>";
		echo "</table>";
		echo "</form>";


		// Aussperren-Formular ----------------------------------------------------
		echo $smarty->fetch("tpl:189");
	}
}


	if($_GET['user_id']) {
		$sql = "SELECT * FROM user WHERE id = '$_GET[user_id]'";
		$result = $db->query($sql, __FILE__, __LINE__);
		$rs = $db->fetch($result);

		echo(
			'<table class="case" width="100%">'
			.'<tr><td style="text-align: center" valign="top">'
			.'<h1>'.$rs['clan_tag'].$rs['username'].'</h1>'
			.'<img src="'.$user->userImage($_GET['user_id'], 1).'">'
			.'</td><td style="text-align: left" valign="top">'
			.$smarty->fetch("tpl:211")
			.'</td></tr>'
			.'<tr><td style="text-align: center" colspan="2">'
		);

		if ($user->id > 0 && $_GET['user_id'] != $user->id && $rs['addle']) {
			?>
			<br />
			<form action="/addle.php?show=overview&do=new" method='post'>
				<INPUT type="hidden" name="id" value="<?=$_GET['user_id']?>">
				<input type='submit' class='button' value=" <?=$rs[username]?> zum Addle herausfordern ">
			</FORM>
			<br />
			<?
		}

		if($user->id > 0) {
			if($_GET['user_id'] == $user->id) { // User ist der eigene
				if(isset($_GET['newmsg'])) { // User will eine neue Message senden
					if(isset($_GET['msgusers']) && isset($_GET['msgsubject'])) {
						echo Messagesystem::getFormSend($_GET['msgusers'],$_GET['msgsubject'],'');
					} else {
						echo Messagesystem::getFormSend(0,'','');
					}
				} else { // User will Inbox sehen
					echo Messagesystem::getInboxHTML($_GET['box'], $pagesize=11, $_GET['page']);
				}
			} else {
				echo Messagesystem::getFormSend(array($_GET['user_id']), '', '');
			}
		}

		echo "<br><img src='/images/stats.php?user_id=".$_GET['user_id']."'><br>"; // Post-Statistik ausgeben

		echo
			'<br />'
			.Forum::getLatestCommentsbyUser($_GET['user_id'])
			.'</td></tr>'
			.'</table>'
		;
		
		if ($user->typ != USER_NICHTEINGELOGGT) {
		echo
			'<br />'
			.getUserPics($_GET['user_id'], 0)
		; }
	}


if(!$user->id) {
	if(!$_GET['regcode'] && !$_SESSION['user_id'] && $_GET['do'] == "anmeldung")  {
		
		// reCAPTCHA v2 initialisieren (inkl. keys)
		require('includes/g-recaptcha-src/autoload.php');
		$siteKey = '6Ld_MgYAAAAAAMiFFL65_-QSB8P2e4Zz2FbSHfUv';
		$secret = '6Ld_MgYAAAAAADDFApargrMpDLE0g1m4X-q0oOL-';
		$lang = 'de-CH'; // reCAPTCHA supported 40+ languages listed here: https://developers.google.com/recaptcha/docs/language
		$recaptcha = new \ReCaptcha\ReCaptcha($secret);
		
		//captcha validieren
		if (isset($_POST['g-recaptcha-response']))
		{
			$recaptcha = new \ReCaptcha\ReCaptcha($secret);
			$resp = $recaptcha->verify($_POST['g-recaptcha-response'], $_SERVER['REMOTE_ADDR']);
			
			//captcha VALID
			if ($resp->isSuccess()) {
				$error = "<b class='small'>".$user->create_newuser(htmlentities($_POST['new_username']), $_POST['new_password'],$_POST['new_password2'],$_POST['new_email'])."</b>";
			
			//captcha UNGÜLTIG
			} else {
				//foreach ($resp->getErrorCodes() as $code) {
                	$error = "<font color='red'><b>Das reCAPTCHA wurde FALSCH eingegeben oder leer gelassen.<br />Bitte versuch es nochmal!</b></font>";//."<br />(reCAPTCHA error codes: " . $code . ")";
            	//}
			}
		}
		echo "<form action='$_SERVER[PHP_SELF]?do=anmeldung' method='post'>";
		echo "<table width='600' class='case' align='center'>
			<tr><td align='center' class='title' colspan='2'>
			Anmeldung
			</td></tr>";
		//gewuenschter username
		echo "<tr><td align='left'>
			<b>Gew&uuml;nschter Benutzername:</b><br />
			<b class='small'>(Clan Zeichen kann seperat angegeben werden)</b>
			</td><td align='left'>
			<input type='text' class='text' name='new_username' size='30' value='$_POST[new_username]'>
			</td></tr>";
		//gewuenschtes passwort
		echo "<tr><td align='left'>
			<b>Passwort:</b>
			</td><td align='left'>
			<input type='password' class='text' name='new_password' size='30'>
			</td></tr><tr><td align='left'>
			<b>Passwort wiederholen:</b>
			</td><td align='left'>
			<input type='password' class='text' name='new_password2' size='30'>
			</td></tr>";
		//email adresse:
		echo "<tr><td align='left'>
			<b>E-Mail Adresse:</b><br />
			<b class='small'>(du bekommst einen Aktivierungscode per E-Mail zugeschickt)</b>
			</td><td align='left'>
			<input type='text' name='new_email' class='text' size='30' value='$_POST[new_email]'>
			</td></tr>";
		// reCAPTCHA v2 form
		echo '<tr><td align="left" colspan="2">
			<div style="display:inline-block;" class="g-recaptcha" data-sitekey="'.$siteKey.'"></div>
            <script type="text/javascript"
                    src="https://www.google.com/recaptcha/api.js?hl='.$lang.'">
            </script>
            </td></tr>';
		//submit button
		echo "<tr><td align='left' colspan='2'>
			<input type='submit' name='newuser' class='button' value='absenden'>
			<br /><br />$error</b>
			</td></tr>
			</table>";
		echo "</form>";
		echo "<br /><br />";

		
		//neues passwort zusenden
		$error = $user->new_pass($_POST['email']);


		echo "<form action='$_SERVER[PHP_SELF]?do=anmeldung' method='post'>";
		echo "<table width='600' class='case' align='center'>
			<tr><td align='center' class='title' colspan='2'>
			<b>Passwort vergessen ?</b>
			</td></tr><tr><td align='left'>
			<b>E-Mail Adresse:</b>
			</td><td align='left'>
			<input type='text' name='email' size='40' class='text'>
			</td></tr><tr><td align='left' colspan='2'>
			<b class='small'>Achtung!<br />Hiermit wird dir ein neues Passwort gesetzt und zugesendet, dieses kannst du sp&auml;ter wieder &auml;ndern!</b>
			<br /><br />
			<font color='red'><b>$error</b></font><br />
			<input type='submit' name='send' value='neues Passwort zusenden' class='button'>
			</td></tr></table>";

		echo "</form>";
	//user reaktivieren
	} elseif($_GET['regcode']) {
		$new_user = $user->activate_user($_GET['regcode']);
		$sql = "UPDATE user set active = 1 WHERE username='$new_user'";
		$db->query($sql,"profil.php",297);
		echo "<b>".$new_user."</b>";
	}
}
//echo foot(1);
$smarty->display('file:layout/footer.tpl');

?>
