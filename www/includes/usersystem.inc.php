<?php
/**
 * Zorg Usersystem
 *
 * Enthält alle User Funktionen von Zorg
 *
 * @author [z]biko
 * @package Zorg
 * @subpackage Usersystem
 */
/**
 * File Includes
 * @include	colors.inc.php 	Colors
 * @include util.inc.php 	Various Helper Functions
 * @include mysql.inc.php 	MySQL-DB Connection and Functions
 * @include strings.inc.php Text strings to be replaced within code functions etc.
 * @include	activities.inc.php 	Activities Functions and Stream
 */
include_once( __DIR__ .'/colors.inc.php');
require_once( __DIR__ .'/util.inc.php');
require_once( __DIR__ .'/mysql.inc.php');
require_once( __DIR__ .'/strings.inc.php');
require_once( __DIR__ .'/activities.inc.php');
//require_once( __DIR__ .'/main.inc.php');

/**
 * Defines
 */
define('USER_ALLE', 0);
define('USER_USER', 1);
define('USER_MEMBER', 2);
define('USER_SPECIAL', 3);
//define('USER_EINGELOGGT', 0);
//define('USER_MEMBER', 1);
//define('USER_NICHTEINGELOGGT', 2);
//define('USER_ALLE', 3);
define('USER_IMGEXTENSION',  '.jpg');
define('USER_IMGPATH',  __DIR__ .'/../../data/userimages/');
define('USER_IMGPATH_PUBLIC', '/data/userimages/');
define('USER_IMGSIZE_LARGE', 427);
define('USER_IMGSIZE_SMALL', 150);
define('USER_IMGPATH_DEFAULT', 'none.jpg');
define('USER_TIMEOUT', 200);
define('USER_OLD_AFTER', 60*60*24*30*3); // 3 Monate
define('DEFAULT_MAXDEPTH', 10);
define('AUSGESPERRT_BIS', 'ausgesperrt_bis');
//if (!defined('FILES_DIR')) define('FILES_DIR', rtrim($_SERVER['DOCUMENT_ROOT'],'/\\').'/../data/files/'); // /data/files/ directory outside the WWW-Root
if (!defined('ZORG_EMAIL')) define('ZORG_EMAIL', 'info@'.SITE_HOSTNAME, true);

/**
 * Usersystem Klasse
 *
 * @author [z]biko
 * @author IneX
 * @version 4.0
 * @package Zorg
 * @subpackage Usersystem
 */
class usersystem {
	/*=========================================================================
	DOCU
	===========================================================================

	Verschlüsselungs Möglichkeiten:
	CRYPT_STD_DES - Standard DES-Schlüssel mit 2-Zeichen Salt
	CRYPT_EXT_DES - Erweiterter DES-Schlüssel mit einem 9-Zeichen Salt
	CRYPT_MD5 - MD5-Schlüssel mit 12-Zeichen Salt, beginnend mit $1$
	CRYPT_BLOWFISH - Erweiterter DES-Schlüssel, 16-Zeichen Salt, beginnend mit $2$

	User Typen:
	1 = Normaler User ##################### 0 isch nöd so cool wil wenns nöd gsetzt isch chunt jo au 0
	2 = [z]member und sch?ne
	0 = nicht eingeloggt ##################### Aber Weber: wenn typ = 2, gits $user jo gar nöd?! -> doch s'usersystem isch jo immer verfügbar
	verf?gbar ?ber $user->typ

	User Vars:
	$user->maxdepth = Forumanzeigeschwelle
	$user->email = Benutzer E-Mail Adresse
	$user->username = Benutzername (ohne Clan Tag)
	$user->clantag = Clan Tag
	$user->password = User passwort
	$user->lastlogin = Letzer Login (Timestamp)
	$user->currentlogin = Aktueller login (Timestamp)
	$user->activity = aktivit?t (Timestamp)
	$user->image = Benutzer bild (vollst?ndiger Pfad, falls kein Bild: "none.jpg")
	$user->typ = Benutzer typ
	$user->member = Member (bool)
	$user->icq = Icq Nummer
	$user->show_comments = Ob die Comments auf den smarty-pages angezeigt werden sollen (=1) oder nicht (=0)
	$user->id = user_ID
	$user->mail_userpw = Message User Passwort
	$user->mail_username = Message Username ohne Umlaute und so
	$user->addle = ob user addle spielen will.
	$user->menulayout = welches menu layout der user eingestellt hat.
	$user->zorger = hat der user zooomclan.org gewählt? sonst zorg.ch anzeigen
	$user->is_mobile = wenn 1, dann nutzt User einen mobilen Browser, bei 0 = Desktop-Browser

	===========================================================================
	CLASS CONFIG
	=========================================================================*/

	var $table_name = 'user';
	var $dbc;
	var $field_username = 'username';
	var $field_clantag = 'clan_tag';
	var $field_userpw = 'userpw';
	var $field_email = 'email';
	var $field_lastlogin = 'lastlogin';
	var $field_maxdepth = 'forummaxthread';
	var $field_usertyp = 'usertype';

	//pugin vars
	var $field_bild = 'image';
	var $field_activity = 'activity';
	var $field_last_ip = 'last_ip';
	var $field_sessionid = 'sessionid';
	var $field_currentlogin = 'currentlogin';
	var $field_ausgesperrt_bis = 'ausgesperrt_bis';
	var $field_regdate = 'regdate';
	var $field_regcode = 'regcode';
	var $field_user_active = 'active';
	var $field_mail_userpw = 'mail_userpw';
	var $field_mail_username = 'mail_username';
	var $field_show_comments = 'show_comments';
	var $field_email_notification = 'email_notification';
	var $field_sql_tracker = 'sql_tracker';
	var $field_addle = 'addle';
	var $field_chess = 'chess';
	var $field_menulayout = 'menulayout';
	var $field_mymenu = 'mymenu';
	var $field_zorger = 'zorger';
	var $field_from_mobile = 'from_mobile';

	//auto einloggen mit cookie
	var $use_cookie = TRUE;

	//wird benoetigt um nicht gesichteten content hervorzuheben
	var $use_current_login = TRUE;

	//wird ben?tigt um ein account von einem user zweifelsfrei aufzuschalten
	var $use_registration_code = TRUE;

	//unterstuetzung einer "wer-ist-alles-online-liste"
	var $use_online_list = TRUE;

	//jeder user kann ein bild von sich hochladen
	var $use_user_picture = TRUE;

	// =========================================================================
	// CONSTRUCTOR
	// =========================================================================

	/**
	 * CONSTRUCTOR
	 *
	 * Klassen Konstruktor
	 *
	 * @TODO Will be deprecated in PHP7! -> http://php.net/manual/de/migration70.deprecated.php
	 *
	 * @return usersystem
	 */
	function usersystem()
	{
		global $db;

		session_name('z');
		$this->typ = USER_ALLE;

		// Session init'en
		if((isset($_GET['z']) && $_GET['z'] != '') || (isset($_POST['z']) && $_POST['z'] != '') || (isset($_COOKIE['z']) && $_COOKIE['z'] != ''))
		{
			session_start();

			try {
				$sql = 'SELECT *, UNIX_TIMESTAMP('.$this->field_activity.') as '.$this->field_activity.',
				UNIX_TIMESTAMP('.$this->field_lastlogin.') as '.$this->field_lastlogin.',
				UNIX_TIMESTAMP('.$this->field_currentlogin.') as '.$this->field_currentlogin.'
				FROM '.$this->table_name.' WHERE id = "'.$_SESSION['user_id'].'"';
				$result = $db->query($sql, __FILE__, __LINE__);
				$rs = $db->fetch($result);

				if ($rs[$this->field_maxdepth]) {
					$this->maxdepth = $rs[$this->field_maxdepth];
				}else{
					$this->maxdepth = DEFAULT_MAXDEPTH;
				}
				$this->email = $rs[$this->field_email];
				$this->username = $rs[$this->field_username];
				$this->clantag = $rs[$this->field_clantag];
				$this->userpw = $rs[$this->field_userpw];
				$this->last_ip = $rs[$this->field_last_ip];
				$this->lastlogin = $rs[$this->field_lastlogin];
				$this->currentlogin = $rs[$this->field_currentlogin];
				$this->ausgesperrt_bis = $rs[$this->field_ausgesperrt_bis];
				$this->activity = $rs[$this->field_activity];
				$this->typ = ($rs[$this->field_usertyp] != '' ? $rs[$this->field_usertyp] : USER_ALLE);
				$this->show_comments = $rs[$this->field_show_comments];
				$this->email_notification = $rs[$this->field_email_notification];
				$this->sql_tracker = $rs[$this->field_sql_tracker];
				$this->addle = $rs[$this->field_addle];
				$this->chess = $rs[$this->field_chess];
				$this->icq = $rs['icq'];
				$this->id = $_SESSION['user_id'];
				$this->menulayout = $rs[$this->field_menulayout];
				$this->mymenu = $rs[$this->field_mymenu];
				$this->zorger = $rs[$this->field_zorger];
				$this->image = self::userImage(intval($_SESSION['user_id']));

				$this->forum_boards = explode(',', $rs['forum_boards']);
				$this->forum_boards_unread = explode(',', $rs['forum_boards_unread']);

				$this->mail_userpw = $rs[$this->field_mail_userpw];
				$this->mail_username = $rs[$this->field_mail_username];

				$rs[$this->field_usertyp] >= 1 ? $this->member = 1 : $this->member = 0;

				// User Agent suchen - Loginart (normal / mobile) festlegen - wird nur in Session geadded, nicht in DB gespeichert
				//isMobileClient($_SERVER['HTTP_USER_AGENT']) <> '' ? $this->is_mobile = 1 : $this->is_mobile = 0;
				$this->from_mobile = ( isMobileClient($_SERVER['HTTP_USER_AGENT']) ? true : false );
				if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> isMobileClient(): %s => %s', __METHOD__, __LINE__, $_SERVER['HTTP_USER_AGENT'], ( $this->from_mobile ? 'true' : 'false')));
			}
			catch(Exception $e) {
				user_error($e->getMessage(), E_USER_ERROR);
			}

			try {
				$sql = 'UPDATE '.$this->table_name.' SET '.$this->field_activity.' = now(),
				'.$this->field_last_ip.' = "'.$_SERVER['REMOTE_ADDR'].'", '.$this->field_from_mobile.' = "'.$this->from_mobile.'"
				WHERE id = "'.$_SESSION['user_id'].'"';
				$db->query($sql, __FILE__, __LINE__);
			}
			catch(Exception $e) {
				user_error($e->getMessage(), E_USER_ERROR);
			}
		}
	}

	// =========================================================================
	// MAIN FUNCTIONS
	// =========================================================================
	/**
	 * User Login
	 *
	 * Erstellt eine Session (login)
	 *
	 * @TODO Fix redirect bei Cookie-Session-Login auf Home (anstatt jeweils aktuelle Seite)!
	 *
	 * @see crypt_pw()
	 * @return string error
	 * @param $username string Benutzername
	 * @param $password string Passwort
	 * @param $use_cookie int cookie
	 */
	function login($username, $password="", $use_cookie = 0) {
		global $db;

		//erstellt sql string fuer user ueberpruefung
		$sql = "SELECT id, ".$this->field_userpw." FROM ".$this->table_name." WHERE "
		.$this->field_username." = '".$username."'";
		$result = $db->query($sql, __FILE__, __LINE__);

		//ueberpruefe ob der user besteht
		if($db->num($result)) {
			$rs = $db->fetch($result);

			//verschluesslet uebergebenes passwort
			$crypted_pw = crypt_pw($password);
			if($_COOKIE['autologin_pw'] != '' && $password == "") {
				$crypted_pw = $_COOKIE['autologin_pw'];
			}

			//erstellt sql string fuer passwort ueberpruefung
			$sql =
				"
					SELECT
						id
						, ".$this->field_user_active."
						, UNIX_TIMESTAMP(".AUSGESPERRT_BIS.") as ".AUSGESPERRT_BIS."
					FROM ".$this->table_name."
					WHERE "
							.$this->field_username." = '".$username."'
						AND
							".$this->field_userpw." = '".$crypted_pw."'
				"
			;
			$result = $db->query($sql, __FILE__, __LINE__);

			//ueberprueft ob passwort korrekt ist
			if($db->num($result)) {
				$rs = $db->fetch($result);

				// ueberpruefe ob user aktiviert wurde
				if($rs[$this->field_user_active]) {

					// überprüfe ob User nicht ausgesperrt ist
					if($rs[AUSGESPERRT_BIS] < time()) {
						session_start();
						$_SESSION['user_id'] = $rs['id'];

						//wenn cookie aktiviert und user gewuenscht
						if($this->use_cookie == TRUE && $use_cookie) {

							//autologin cookies setzen
							setcookie("autologin_id",$username,time()+(86400*14));
							setcookie("autologin_pw",$crypted_pw,time()+(86400*14));
						}

						//Last Login update
						$sql = "UPDATE ".$this->table_name."
						set ".$this->field_lastlogin." = ".$this->field_currentlogin."
						WHERE id = '".$rs['id']."'";
						$db->query($sql, __FILE__, __LINE__);

						//current login update
						$sql = "UPDATE ".$this->table_name."
						set ".$this->field_currentlogin." = now(),
						".$this->field_last_ip." = '".$_SERVER['REMOTE_ADDR']."'
						WHERE id = '".$rs['id']."'";
						$db->query($sql, __FILE__, __LINE__);

						/** @TODO Fix redirect bei Cookie-Session-Login auf Home (anstatt jeweils aktuelle Seite)! */
						header("Location: ".$_SERVER['PHP_SELF']."?". session_name(). "=". session_id());
						exit;
					} else {
						echo t('lockout-message', 'user', date("d.m.Y", $rs[AUSGESPERRT_BIS]));
						exit;
					}

				} else { $error = t('account-inactive', 'user'); }
			} else {
				$this->logerror(1,$rs['id']);
				$error = t('authentication-failed', 'user'); // nicht gegen aussen exponieren, dass es einen Useraccount gibt aber falsches PW
			}
		} else {
			$error = t('authentication-failed', 'user'); // nicht gegen aussen exponieren, dass es einen Useraccount gibt aber falsches PW
		}
		return $error;
	}


	/**
	 * User Logout
	 *
	 * Logt einen User aus!
	 *
	 * @return void
	 */
	function logout() {
		// Session destroy
		unset($_SESSION['user_id']);
		session_destroy();

		// cookie killen
		setcookie("autologin_id",'',time()-(86400*14));
		setcookie("autologin_pw",'',time()-(86400*14));

		header("Location: ". $_SERVER['PHP_SELF']);
		exit;
	}

	
	/**
	 * Speichert ob User zorg oder zooomclan Layout haben will
	 *
	 * @param integer $user_id User-ID
	 * @param boolean $zorg Zorg-Layout
	 * @param boolean $zooomclan Zooomclan-Layout
	 */
	function set_page_style($user_id, $zorg=TRUE, $zooomclan=FALSE) {
		global $db, $zorg, $zooomclan;

		if ($zorg == true) {
			$sql = "UPDATE ".$this->table_name."
					set ".$this->field_zorger." = 1
					WHERE id = '".$user_id."'";
					$db->query($sql, __FILE__, __LINE__);
		} elseif ($zooomclan == true) {
			$sql = "UPDATE ".$this->table_name."
					set ".$this->field_zorger." = 0
					WHERE id = '".$user_id."'";
					$db->query($sql, __FILE__, __LINE__);
		}
	}


	/**
	 * Neues Passwort
	 *
	 * Generiert ein Passwort für einen bestehenden User
	 *
	 * @see crypt_pw()
	 * @return string error
	 * @param $email string E-Mail
	 */
	function new_pass($email) {
		global $db;
		if($email) {

			// E-mailadresse validieren
			if(check_email($email)) {
				$sql = "SELECT id, username FROM user WHERE email = '$email'";
				$result = $db->query($sql, __FILE__, __LINE__);

				//überprüfe ob user mit email existiert
				if($db->num($result)) {
					$rs = $db->fetch($result);

					//generiere passwort
					$new_pass = $this->password_gen($rs['username']);

					//verschlüssle passwort
					$crypted = crypt_pw($new_pass);

					//trage aktion in errors ein
					$this->logerror(3,$rs['id']);

					//update user table
					$sql = "UPDATE user set userpw = '$crypted' WHERE id = '$rs[id]'";
					$db->query($sql, __FILE__, __LINE__);

					//versende email
					@mail($email, t('message-newpass-subject', 'user'), t('message-newpass', 'user', [ $rs['username'], $crypted ]), "From: ".ZORG_EMAIL."\n");
					$error = t('newpass-confirmation', 'user');

				} else {
					$error = t('invalid-email', 'user');
				}
			} else {
				$error = t('invalid-email', 'user');
			}
		}
		return $error;
	}

	/**
	 * Benutzer erstellen
	 *
	 * Erstellt einen Neuen Benutzer
	 *
	 * @see crypt_pw()
	 * @return string error
	 * @param $username string Benutzername
	 * @param $pw string Passwort
	 * @param $pw2 string Passwortwiederholung
	 * @param $email string E-Mail
	 */
	function create_newuser($username, $pw, $pw2, $email) {
		global $db;
		if($username) {
			$email_name = emailusername($username);
			$sql = "SELECT id FROM ".$this->table_name."
			WHERE ".$this->field_username." = '$username' OR ".
			$this->field_mail_username." = '$email_name'";
			$result = $db->query($sql, __FILE__, __LINE__);

			//überprüfe ob user bereits existiert
			if(!$db->num($result)) {

				// E-mailadresse validieren
				if(check_email($email)) {
					$sql = "SELECT id FROM ".$this->table_name."
					WHERE ".$this->field_email." = '$email'";
					$result = $db->query($sql, __FILE__, __LINE__);

					//?berpr?fe ob user mit email bereits existiert
					if(!$db->num($result)) {

						//?berpr?fe passwort ?bereinstimmung
						if($pw == $pw2) {

							//erstelle regcode
							$key = $this->regcode_gen($username);

							//verschl?ssle passwort
							$crypted_pw = crypt_pw($pw);

							//user eintragen
							$sql = "INSERT into ".$this->table_name."
							(".$this->field_regcode.", ".$this->field_regdate.",
							".$this->field_userpw.",".$this->field_username.",
							".$this->field_email.", ".$this->field_usertyp.")
							VALUES ('".$key."',now(),'".$crypted_pw."',
							'".$username."','".$email."', 1)";
							$db->query($sql, __FILE__, __LINE__);

							//userdir erstellen
							mkdir($_SERVER['DOCUMENT_ROOT']."/users/".emailusername($username),0777);
							chmod($_SERVER['DOCUMENT_ROOT']."/users/".emailusername($username),0777);

							//email versenden
							@mail($email, t('message-newaccount-subject', 'user'), t('message-newaccount', 'user', [ $username, SITE_URL, $key ]), 'From: '.ZORG_EMAIL."\n");

							$error = t('account-confirmation', 'user');
						} else {
							$error = t('authentication-failed', 'user');
						}
					} else {
						$error = t('invalid-email', 'user');
					}
				} else {
					$error = t('invalid-email', 'user');
				}
			} else {
				$error = t('invalid-username', 'user');
			}
		}
		return $error;
	}

	// =========================================================================
	// MISC FUNCTIONS
	// =========================================================================

	/**
	 * Online Users
	 *
	 * Gibt Online Users als HTML aus
	 *
	 * @TODO HTML can be returned using new function usersystem::userpage_link()
	 *
	 * @return string html
	 * @param $sec int Sekunden
	 */
	function online_users($pic=FALSE) {
		global $db, $sun;
		$sql = "
			SELECT id, username, clan_tag
			FROM user
			WHERE	UNIX_TIMESTAMP(activity) > (UNIX_TIMESTAMP(now()) - ".USER_TIMEOUT.")
			ORDER by activity DESC
		";
		$result = $db->query($sql, __FILE__, __LINE__);
		$i = 0;
		while($rs = $db->fetch($result)) {
			if($pic == FALSE) {
				//$html .= usersystem::link_userpage($rs[id], FALSE).', ';
				$html .= '<a href="/profil.php?user_id='.$rs['id'].'">'.$rs['clan_tag'].$rs['username'].'</a>';
				if(($i+1) < $db->num($result)) $html .= ', ';
			} else {

				$html .=
					'<table bgcolor="'.TABLEBACKGROUNDCOLOR.'" border="0"><tr><td><a href="/profil.php?user_id='.$rs['id'].'">'
					.'<img border="0" src="'.USER_IMGPATH_PUBLIC.$rs['id'].'.jpg" title="'.$rs['clan_tag'].$rs['username'].'">'
					.'</a></td></tr>'
					.'<tr>'
					.'<td align="center">'
					.'<a href="/profil.php?user_id='.$rs['id'].'">'.$rs['clan_tag'].$rs['username'].'</a>'
					.'</td></tr></table><br />'
				;
			}
			$i++;
		}
		return $html;
	}

	/**
	 * User aktivieren
	 *
	 * Aktiviert einen User
	 *
	 * @return string error
	 * @param $regcode string RegistrationsCode
	 */
	function activate_user($regcode) {
		global $db;
		$sql = "SELECT id, ".$this->field_username."
		FROM ".$this->table_name." WHERE ".$this->field_regcode." = '$regcode'";
		$result = $db->query($sql, __FILE__, __LINE__);
		if($db->num($result)) {
			$rs = $db->fetch($result);
			$username = $rs[$this->field_username];
			$sql = "UPDATE ".$this->table_name." set ".$this->field_user_active." = 1
			WHERE id = '$rs[id]'";
			$db->query($sql, __FILE__, __LINE__);
			$error = t('account-activated', 'user');
			
			Activities::addActivity($rs['id'], 0, t('activity-newuser', 'user' ), 'u');
		} else {
			$this->logerror(2,0);
			$error = t('invalid-regcode', 'user');
		}
		return $error;
	}

	/**
	 * Error loggen
	 *
 	 * Speichert ein Fehler des Users in der DB ab.
	 *
	 * @return void
	 * @param $do int Aktion
	 * @param $user_id int User ID
	 */
	function logerror($do,$user_id) {
		global $db;
		$do_array = array(
			1 => t('authentication-failed', 'user'),
			2 => t('invalid-regcode', 'user'),
			3 => t('newpass-confirmation', 'user')
		);

		$sql = "INSERT into error (user_id, do, ip, date)
		VALUES ('".$user_id."', '".$do_array[$do]."','".$_SERVER['REMOTE_ADDR']."', now())";
		$db->query($sql, __FILE__, __LINE__);
	}

	/**
	 * Registrationscode generieren
	 *
	 * Erstellt einen Registrationscode für einen Benutzer
	 *
	 * @return string hash
	 * @param $username string
	 */
	function regcode_gen($username) {
		$hash = md5($username);
		return $hash;
	}

	/**
	* Pürfen ob User eingeloggt
	*
	* Überprüft ob der User eingeloggt ist
	*
	* @return bool
	*/

	function islogged_in() {
		if($_SESSION['user_id']) {
			return TRUE;
		} else {
			return FALSE;
		}
	}

	/**
	* Passwort-Generator
	*
	* Erstellt ein zufälliges Passwort
	*
	* @return string Passwort
	* @param $username string Benutzername
	*/
	function password_gen($username) {
		for($i=0;$i<strlen($username);$i++) {
			srand(microtime()*1000000);
			$rand .= strtolower(chr(rand(65,90)));
		}
		return $rand;
	}

	/**
	* Userpic prüfen
	*
	* Überprüft ob ein Bild zum User existiert
	*
	* @author ?
	* @author IneX
	* @date 11.07.2018
	* @version 2.0
	* @since 1.0
	* @since 2.0 11.07.2018 added check for locally cached Gravatar, replaced 'file_exists' with 'stream_resolve_include_path'
	* @since 3.0 16.07.2018 Method now returns path to userpic (or queried Gravatar result) as string, instead of true.
	*
	* @TODO this function is f*cking SLOW!!!! // Jan 2016, IneX => fix0red 11.07.2018
	*
	* @see USER_IMGPATH
	* @see USER_IMGEXTENSION
	* @param $userid int User ID
	* @return string|bool Returns userimage path as string, or false if not found
	*/
	function checkimage($userid)
	{
		/** Image-Path to check */
		$user_imgpath_custom = USER_IMGPATH.$userid.USER_IMGEXTENSION;
		$user_imgpath_gravatar = USER_IMGPATH.$userid.'_gravatar'.USER_IMGEXTENSION;

		/** Check for cached Gravater */
		if (stream_resolve_include_path($user_imgpath_gravatar) !== false)
		{
			if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> userImage GRAVATAR exists/cached: %s', __METHOD__, __LINE__, $user_imgpath_gravatar));
			return $user_imgpath_gravatar;

		/** Check for custom Userpic */
		} elseif (stream_resolve_include_path($user_imgpath_custom) !== false) {
			if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> userImage ZORG exists/cached: %s', __METHOD__, __LINE__, $user_imgpath_custom));
			return $user_imgpath_custom;

		/** Return false if no userpic cached */
		} else {
			if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> userImage not cached: querying Gravatar', __METHOD__, __LINE__));
			return false;
		}
	}


	/**
	* Userpic Pfad
	*
	* Gibt den Pfad zum Bild des Users. Falls kein Bild: none.jpg
	*
	* @version 2.0
	* @since 1.0 initial function
	* @since 2.0 Check & load cached Gravatar, optimized if-else
	*
	* @see USER_IMGPATH
	* @see USER_IMGPATH_PUBLIC
	* @see USER_IMGSIZE_SMALL
	* @see USER_IMGSIZE_LARGE
	* @see usersystem::checkimage()
	* @see usersystem::get_gravatar()
	* @param int $userid User ID
	* @param boolean $large Large image true/false
	* @return string URL-Pfad zum Bild des Users
	*/
	function userImage($userid, $large=false)
	{
		/** Check if userpic-file exists, and return it */
		$user_imgpath = self::checkimage($userid);
		if (!empty($user_imgpath))
		{
			/** Make internal USER_IMGPATH to external USER_IMGPATH_PUBLIC */
			$user_imgpath = str_replace(USER_IMGPATH, USER_IMGPATH_PUBLIC, $user_imgpath);

			/** Add Thumbnail shortcut, if $large is NOT set */
			if (empty($large)) $user_imgpath = str_replace(USER_IMGEXTENSION, '_tn' . USER_IMGEXTENSION, $user_imgpath);

			return $user_imgpath;

		/** If no userpic-file exists, query Gravatar with USER_IMGPATH_DEFAULT as fallback image */
		} else {
			if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> userImage not cached for $userid %d', __METHOD__, __LINE__, $userid));
			return self::get_gravatar(
				 						 self::id2useremail($userid)
				 						,($large ? USER_IMGSIZE_LARGE : USER_IMGSIZE_SMALL)
				 						,USER_IMGPATH_PUBLIC.USER_IMGPATH_DEFAULT
				 					);
		}
	}

	/**
	 * Retrieve list of Users for Notification-Messages in Comments or Personal Messages
	 * 
	 * @author IneX
	 * @date 26.12.2017
	 * 
	 * @TODO remove this function 'getFormFieldUserlist()' & make sure to remove all references in corresponding files pointing to it
	 * 
	 * @DEPRECATED
	*/
	function getFormFieldUserlist($name, $size, $users_selected=0, $tabindex=10) {
		global $db;

		// Wenn User ganz neue Message schreibt
		if ($users_selected == 0) $users_selected = Array();

		// check and make an Array, if necessary
		if (strpos($users_selected, ',') !== false && !is_array($users_selected))
		{
			$users_selected = explode(',', $users_selected);
		}
		// Remove any duplicate User-IDs
		$users_selected = array_unique($users_selected);

		$sql =
			"SELECT id, clan_tag, username FROM user"
			." WHERE UNIX_TIMESTAMP(lastlogin) > (UNIX_TIMESTAMP(now())-".(USER_OLD_AFTER*2).")"
			." OR z_gremium = '1' OR (vereinsmitglied != '0' AND vereinsmitglied != '')"
			." ORDER BY clan_tag DESC, username ASC"
		;
		$result = $db->query($sql, __FILE__, __LINE__);

		$html = '<select multiple="multiple" name="'.$name.'" size="'.$size.'" tabindex="'.$tabindex.'">';
		while ($rs = mysql_fetch_array($result)) {
			$html .=
				'<option value="'.$rs['id'].'"'
				.(in_array($rs['id'], $users_selected) || $rs['id'] == $users_selected[0] ? ' selected' : '')
				//.($rs['id'] == $users_selected[0] ? ' selected="selected"' : '')
				.'>'
				.$rs['clan_tag'].$rs['username'].'</option>'
			;
		}
		$html .= '</select>';

		return $html;
	}


	/**
	* Convert ID to Username/Userpic
	*
	* Konvertiert eine ID zum entsprechenden Username (wahlweise inkl. Clantag oder ohne), oder dem HTML-Code zur Anzeige des Userpics
	*
	* @author IneX
	* @version 4.0
	* @since 1.0
	* @since 2.0
	* @since 3.0 Method now really only resolves an ID to a Username, redirects other features
	* @since 4.0 changed output to new function usersystem::userprofile_link()
	*
	* @TODO 20.07.2018 Find out & fix issue with Query failing on id=$id instead of id="$id"...
	*
	* @see usersystem::userprofile_link()
	* @global	object	$db	Globales Class-Object mit allen MySQL-Methoden
	* @param integer $id User ID
	* @param boolean $clantag Username mit Clantag true/false
	* @param boolean $pic DEPRECATED Anstatt Username das Userpic HTML-Code ausgeben true/false
	* @return string Username (mit/ohne Clantag) oder Userpic HTML-Code
	*/
	function id2user($id, $clantag=FALSE, $pic=FALSE)
	{
		global $db;
		static $_users = array();

		/** If given User-ID is not valid (not numeric), show a User Error */
		if (!empty($id) && !is_numeric($id)) user_error(t('invalid-id', 'user'), E_USER_WARNING);
		$clantag = (empty($clantag) || $clantag === 'false' || $clantag === 0 ? FALSE : TRUE);

		if (!isset($_users[$id]) || !isset($_users[$id]['clan_tag']))
		{
			try {
				if ($clantag === TRUE) {
					$sql = 'SELECT clan_tag, username FROM user WHERE id="'.$id.'" LIMIT 0,1'; // ATTENTION: Query fails when $id is not quoted with "$id"!
				} else {
					$sql = 'SELECT username FROM user WHERE id="'.$id.'" LIMIT 0,1';
				}
		  		$result = $db->query($sql, __FILE__, __LINE__);
		  		while ($rs = mysql_fetch_array($result)) {
		  		   $_users[$id] = $rs;
		  		}
		  	} catch(Exception $e) {
				return $e->getMessage();
			}
		}

		/** Set string with Username */
		$username = $_users[$id]['username'];

		/** If applicable, prefix Username with the Clantag */
		if ($clantag == TRUE)
		{
			/** ...but only if the user really HAS a Clantag! */
			if (!empty($_users[$id]['clan_tag'])) {
				$username = $_users[$id]['clan_tag'].$username;
			}
		}

		/**
		 * Return Userpic HTML
		 * @DEPRECATED
		 */
		/*
		if($pic == TRUE)
		{
			$us =
				'<img alt="'.$us.'" border="0" src="'.usersystem::userImage($id).'" title="'.$us.'"'
			;

			if ($zorg == true) {
				$us .= ' height="65">';
			} else {
				$us .= '>';
			}
			$us .= $this->userpic($id);
		}*/
		return $username;
	}

	/**
	* Get User ID based on Username
	*
	* Konvertiert einen Username zur dazugehörigen User ID
	*
	* @version 2.0
	* @since 1.0 initial function
	* @since 2.0 optimized sql-query
	* @author IneX
	*
	* @global	object	$db	Globales Class-Object mit allen MySQL-Methoden
	* @param $username string Username
	* @return int User ID oder 0
	*/
	function user2id ($username) {
		global $db;
		$e = $db->query("SELECT id FROM user WHERE username='$username' LIMIT 1", __FILE__, __LINE__, __METHOD__);
		$d = $db->fetch($e);
		return ($d ? $d['id'] : 0);
	}


	/**
	 * Userpic (klein) ausgeben
	 *
	 * @DEPRECATED
	 *
	 * @author IneX
	 * @date 02.10.2009
	 * @version 2.0
	 * @since 1.0 initial function
	 * @since 2.0 changed output to new function usersystem::userprofile_link()
	 *
	 * @TODO there is no $clantag passed to this function?!
	 *
	 * @see usersystem::userprofile_link()
	 * @param	integer	$id				User-ID
	 * @param	boolean	$displayName	Zeigt Usernamen unter dem Bild an
	 * @global	object	$db				Globales Class-Object mit allen MySQL-Methoden
	 * @global	object	$user			Globales Class-Object mit den User-Methoden & Variablen
	 * @global	object	$smarty			Globales Class-Object mit allen Smarty-Methoden
	 * @static	array	$_users
	 * @return	string					Link zum Userpic
	 */
	function userpic($id, $displayName=FALSE)
	{
		/** DEPRECATED
		global $db, $user;
		static $_users = array();

		$us = '';

		if ($displayName) {
			if (!isset($_users[$id])) {
				try {
					$sql = "SELECT clan_tag, username FROM user WHERE id='$id'";
					$result = $db->query($sql, __FILE__, __LINE__);
					while ($rs = mysql_fetch_array($result)) {
						$_users[$id] = $rs;
					}
				} catch(Exception $e) {
					return $e->getMessage();
				}
			}
			$us = $_users[$id]['username'];
			if($clantag == TRUE) {
				$us = $_users[$id]['clan_tag'].$us;
			}
		}

		$us =
			'<a href="/profil.php?user_id='.$id.'">'.
			'<img alt="'.$us.'" border="0" src="'.usersystem::userImage($id).'" title="'.$us.'" height="65">'.
			'</a>'
		;
		*/

		/** Because method is DEPRECATED => Redirect to new usersystem::userprofile_link() */
		return self::userprofile_link($id, ['pic' => TRUE, 'link' => TRUE, 'username' => FALSE, 'clantag' => FALSE]);
	}


	/**
	 * Gravatar Userpic
	 *
	 * Get either a Gravatar URL or complete image tag for a specified email address.
	 *
	 * @source http://gravatar.com/site/implement/images/php/
	 * @author IneX
	 * @date 24.07.2014
	 * @version 3.0
	 * @since 1.0 24.07.2014
	 * @since 2.0 11.01.2017 Fixed Gravatar-URL to https using SITE_PROTOCOL
	 * @since 3.0 16.07.2018 Removed possibility to return <img>-Tag
	 *
	 * @see SITE_PROTOCOL
	 * @param string $email The email address
	 * @param string $s Size in pixels, defaults to 80px [ 1 - 2048 ]
	 * @param string $d Default imageset to use [ 404 | mm | identicon | monsterid | wavatar ]
	 * @param string $r Maximum rating (inclusive) [ g | pg | r | x ]
	 * @param boolean $img True to return a complete IMG tag False for just the URL
	 * @param array $atts Optional, additional key/value attributes to include in the IMG tag
	 * @return string String containing URL to gravatar.com Profilepic
	 */
	function get_gravatar( $email, $s = 150, $d = '404', $r = 'x' )
	{
		/** HTTP-request to Gravatar */
		$url = SITE_PROTOCOL.'://www.gravatar.com/avatar/';
		$url .= md5( strtolower( trim( $email ) ) );
		if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> $email: %s', __METHOD__, __LINE__, $email));
		$url .= "?s=$s&d=$d&r=$r";
		if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> $url_check: %s', __METHOD__, __LINE__, $url));
		$url_check = @get_headers($url); // Get response headers of $url
		if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> get_headers() response: %s', __METHOD__, __LINE__, print_r($url_check, true)));
		$url_parse = parse_url(trim($d)); // For eventual fallback: parse URL of Default image
		if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> parse_url() %s', __METHOD__, __LINE__, $d));
		if(strpos($url_check[0],'200')===false) return $url_parse['path']; // If $url response header is NOT 200, fallback to local image
		return $url;
	}


	/**
	 * Fetch Gravatar images for Userlist
	 *
	 * @author IneX
	 * @date 12.07.2018
	 * @version 1.0
	 * @since 1.0 12.07.2018 function added
	 *
	 * @param integer|string $userScope Scope for whom to get the Gravatar image for: a single User-ID integer, or 'all' string for all Useraccounts.
	 * @global object $db Globales Class-Object mit allen MySQL-Methoden
	 * @return bool Returns true/false depening on if a successful execution was possible, or not
	 */
	function cacheGravatarImages($userScope)
	{
		global $db;

		/** Validate passed $userScope variable */
		if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> $userScope: %s', __METHOD__, __LINE__, $userScope));
		if (empty($userScope)) return false;

		/** Get the Gravatar image for a User or a List of Users */
		switch ($userScope)
		{
			/** (integer)USER: If $userScope = User-ID: try to get the User's Gravatar-Image */
			case is_numeric($userScope) && $userScope > 0:
				if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> Checking for User-ID: %d', __METHOD__, __LINE__, $userScope));
				//$sql = 'SELECT email FROM user WHERE id = ' . $userid;
				//$user_emaillist = $db->query($sql, __FILE__, __LINE__, __METHOD__);
				if (self::exportGravatarImages([$userScope])) return true;

			/** (array)LIST: If $userScope = User-ID list: try to get Gravatar-Image for all of them */
			case $userScope === 'all':
				if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> Checking for %s User-IDs', __METHOD__, __LINE__, $userScope));
				try {
					$sql = 'SELECT id FROM user WHERE email IS NOT NULL AND email <> "" AND active = 1';
					$userids_list = $db->query($sql, __FILE__, __LINE__, __METHOD__);
					while ($result = mysql_fetch_array($userids_list))
					{
						$userids[] = $result['id'];
					}
					if (self::exportGravatarImages($userids)) return true;

				} catch (Exception $e) {
					error_log(sprintf('[DEBUG] <%s:%d> %s', __METHOD__, __LINE__, $e->getMessage()));
					return false;
				}

			/** DEFAULT: stop execution */
			default:
				error_log( t('invalid-id', 'user') );
				return false;
		}
	}


	/**
	 * Fetch & save Gravatar Userpics to local Filecache
	 *
	 * Downloads & stores Gravatar images locally using cURL, so we don't query gravatar.com all the time
	 * @link https://en.gravatar.com/site/implement/images/
	 *
	 * @author IneX
	 * @date 11.07.2018
	 * @version 1.0
	 * @since 1.0 11.07.2018 function added
	 *
	 * @TODO read & compare Gravatar image based on Gravatar's filename? See description for full response header retrieved.
	 * @TODO wenn die self::id2useremail() Funktion gefixt ist (nicht nur eine response wenn E-Mail Notifications = true), dann Query ersetzen mit Methode
	 *
	 * @see SITE_PROTOCOL, USER_IMGPATH, USER_IMGSIZE_LARGE, USER_IMGSIZE_SMALL, USER_IMGEXTENSION
	 * @see cURLfetchUrl()
	 * @param array $userid Single or List of User ID(s) as Array
	 * @global object $db Globales Class-Object mit allen MySQL-Methoden
	 * @return bool Returns true/false depening on if a successful execution was possible, or not
	 */
	function exportGravatarImages(array $userids)
	{
		global $db;
		
		if ( empty($userids) || count($userids) <= 0 ) return false;

		$index = 0;
		foreach($userids as $userid)
		{
			try {
				/**
				 * Check for a valid user e-mail
				 * @TODO wenn die self::id2useremail() Funktion gefixt ist (nicht nur eine response wenn E-Mail Notifications = true), dann Query ersetzen mit Methode
				 */
				//$useremail = self::id2useremail($userid);
				$queryresult = $db->fetch($db->query('SELECT email FROM user WHERE id = '.$userid.' LIMIT 0,1', __FILE__, __LINE__, __METHOD__));
				$useremail = $queryresult['email'];
			} catch(Exception $e) {
				return $e->getMessage();
			}
			if (!empty($useremail))
			{
				$gravatar_baseurl = SITE_PROTOCOL.'://www.gravatar.com/avatar/';
				$gravatar_useremail = md5( strtolower( trim( $useremail ) ) );
				/** d=404: return http 404 response, r=x: all ratings of images */
				$gravatar_urlparam = '?d=404&r=x';
	
				try {
					/**
					 * Loop twice to get large & small image size
					 * !Important! no 'return" within the for-loop - otherwise the function will exit & not finish processing both request!
					 */
					for ($i = 1; $i <= 2; $i++)
					{
						/** Switch image size while looping, s=pixelsize */
						$gravatar_imgsize = '&s=' . ($i === 1 ? USER_IMGSIZE_LARGE : USER_IMGSIZE_SMALL);
						$user_imgpath_gravatar = USER_IMGPATH.$userid.'_gravatar'.($i === 1 ? '' : '_tn').USER_IMGEXTENSION;
	
						/** Build full URL for request */
						$gravatar_request = $gravatar_baseurl . $gravatar_useremail . $gravatar_urlparam . $gravatar_imgsize;
						$curl_httpresources[$index++] = [ $gravatar_request, $user_imgpath_gravatar ];
					}

				/** Handle exception */
				} catch (Exception $e) {
					error_log(sprintf('[DEBUG] <%s:%d> %s', __METHOD__, __LINE__, $e->getMessage()));
					return false;
				}
			}
			$index++;
		}
		
		/** cURL all request from the $curl_httpresources Array */
		if (count($curl_httpresources) > 0)
		{
			if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> cURLfetchUrl(): START', __METHOD__, __LINE__));
			foreach ($curl_httpresources as $resource)
			{
				cURLfetchUrl($resource[0], $resource[1]);
			}
			if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> cURLfetchUrl(): SUCCESS', __METHOD__, __LINE__));
			return true;
		} else {
			if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> cURLfetchUrl(): ERROR', __METHOD__, __LINE__));
			return false;
		}
	}


	/**
	* User Mobile Agent
	*
	* Prüft eine User-ID, ob der User von einem Mobilen Browser zugreift
	*
	* @return string last Mobile Useragent
	* @param $id int User ID
	*/
	function ismobile ($id)
	{
		global $db;
		$e = $db->query("SELECT id, from_mobile FROM user WHERE id='$id' LIMIT 1", __FILE__, __LINE__);
		$d = $db->fetch($e);
		if ($d) return $d['from_mobile'];
		else return '';
	}


	/**
	* ID zu Username
	*
	* Wandelt eine User ID in Username um
	*
	* @return string username
	* @param $id int User ID
	*/
	function id2mailuser($id) {
		global $db;
		$sql = "SELECT mail_username FROM user WHERE id = ".$id;
		$result = $db->query($sql, __FILE__, __LINE__);
		$rs = $db->fetch($result);
		return $rs['mail_username'];
	}


	/**
	* ID zu User E-Mail
	*
	* Gibt aufgrund einer User ID dessen E-Mailadresse zurück.
	* @TODO soll nur geprüft werden, ob der User E-Mailbenachrichtigung erlaubt hat.
	*
	* @author IneX
	* @version 3.0
	* @date 17.03.2018
	*
	* @param $id int User-ID
	* @return string EMail-Adresse oder false
	*/
	function id2useremail($id) {
		global $db;

		try {
			$sql = "SELECT email, email_notification FROM user WHERE id = $id LIMIT 0,1";
			$result = $db->query($sql, __FILE__, __LINE__);
			$rs = $db->fetch($result);

			if (!empty($rs['email_notification']) && $rs['email_notification'] > 0)
			{
				if (!empty($rs['email'])) {
					return $rs['email'];
				} else {
					return false;
				}
			} else {
				return false;
			}
			//$value = (!empty($rs['email_notification']) ? $rs['email'] : false);
			//return $value;
		} catch(Exception $e) {
			return $e->getMessage();
		}
	}



	/**
	* Link zum Userprofil
	*
	* Gibt eine User ID als link zur userpage aus
	*
	* @DEPRECATED
	*
	* @author milamber
	* @author IneX
	* @version 2.0
	* @since 1.0 initial version
	* @since 2.0 changed output to new function usersystem::userprofile_link()
	*
	* @see usersystem::userprofile_link()
	* @param int $user_id User ID
	* @param bool $pic Userpic mitausgeben
	* @return string html
	*/
	function link_userpage($user_id, $pic=FALSE)
	{
		/** @DEPRECATED */
		/*if($user_id != '') {

			$html =
		  	'<a href="/profil.php?user_id='.$user_id.'">'
		  	.$this->id2user($user_id, TRUE, $pic)
		  	.'</a>'
		  ;
		}*/

		/** Validate & set $pic parameter to real boolean */
		if ($pic === 'false' || $pic === 0) $pic = FALSE;
		elseif ($pic === 'true' || $pic === 1) $pic = TRUE;

		/** Because method is DEPRECATED => Redirect to new usersystem::userprofile_link() */
		return self::userprofile_link($user_id, ['pic' => $pic, 'link' => TRUE, 'username' => TRUE, 'clantag' => TRUE]);
	}

	/**
	* Link zu einem Userprofil
	* @TODO wird diese Methode usersystem::userpagelink() noch benötigt irgendwo? Sonst: raus!
	* @DEPRECATED
	* @see usersystem::userpage_link()
	*/
	function userpagelink($userid, $clantag, $username) {
		/** DEPRECATED
		$name = $clantag.$username;

		// Dreadwolfs spezieller Nick
		//if($userid == 307) $name = '<b style="background-color: green; color: white;">&otimes; '.$name.' &oplus;</b>';

		return '<a href="/user/'.$userid.'">'.$name.'</a>';
		*/
		
		/** Because method is DEPRECATED => Redirect to new usersystem::userprofile_link() */
		return self::userprofile_link($userid, ['link' => TRUE, 'username' => TRUE, 'clantag' => TRUE]);
	}

	/**
	 * Show Userprofile for a User ID
	 *
	 * Gibt eine User ID als Username aus - mit diversen Darstellungsmöglichkeiten:
	 *	- Username: ja/nein?
	 *	- Clantag im Username: ja/nein?
	 *	- Userpic: ja/nein?
	 *	- Verlinkung auf Userprofil: ja/nein?
	 *
	 * @author IneX
	 * @date 05.07.2018
	 * @version 1.0
	 * @since 1.0 initial version (output from Smarty-Template)
	 *
	 * @see usersystem::userImage()
	 * @see usersystem::id2user()
	 * @see userprofile_link.tpl
	 * @param int $userid User ID
	 * @param array $params Parameters as Array which define the output using true/false
	 * @global object $smarty Globales Class-Object mit allen Smarty-Methoden
	 * @return string Fetched Smarty-Template String (usually HTML-formatted) for output
	 */
	function userprofile_link($userid, array $params)
	{
		global $smarty;

		/** Check & set $params parameters to valid values */
		$pic = (empty($params['pic']) || $params['pic'] === 'false' || $params['pic'] === 0 ? FALSE : TRUE);
		$username = (empty($params['username']) || $params['username'] === 'false' || $params['username'] === 0 ? FALSE : TRUE);
		$clantag = (empty($params['clantag']) || $params['clantag'] === 'false' || $params['clantag'] === 0 ? FALSE : TRUE);
		$link = (empty($params['link']) || $params['link'] === 'false' || $params['link'] === 0 ? FALSE : TRUE);

		$smarty->assign('show_profilepic', ($pic ? 'true' : 'false'));
		if ($pic) $smarty->assign('profilepic_imgsrc', self::userImage($userid));
		$smarty->assign('show_username', ($username ? 'true' : 'false'));
		if ($username) $smarty->assign('username', self::id2user($userid, $clantag));
		$smarty->assign('show_profile_link', ($link ? 'true' : 'false'));

		return $smarty->fetch('file:layout/partials/profile/userprofile_link.tpl');
	}

	/**
	* User Quote (?)
	*
	* Gibt ein random Quote zurück.
	* Falls mit user_id wird es ein quote dieses users sein<br><br>
	* <b>Milamber: Warum ist dies nicht im quotes.inc.php? Und wir brauchen das nicht mal?!</b>
	*
	* @return string quote
	* @param $user_id int User ID
	*/
	function quote($user_id) {
		global $db;
		if($user_id != '') {

			$sql = "SELECT count(*) as anzahl FROM quotes WHERE user_id = $user_id";
			$result = $db->query($sql, __FILE__, __LINE__);
			$rs = $db->fetch($result);
			$total = $rs['anzahl'];

			mt_srand((double)microtime()*1000000);
			$rnd = mt_rand(1, $total);
			$sql = "SELECT * FROM quotes WHERE user_id = ".$user_id;
			$result = $db->query($sql, __FILE__, __LINE__);

			for ($i=0;$i<$rnd;$i++){
				$rs = $db->fetch($result);
			}
			$quote = $rs['text'];
			return $quote;
		}
	}
	
	/**
	 * User specific /data/files/
	 * Check if User's /files/{$user_id}/ Directory exists, if not, create it
	 *
	 * @author IneX
	 * @date 27.01.2016
	 * @since 3.0
	 * @version 1.0
	 */
	function get_and_create_user_files_dir($user_id)
	{
		$files_dir = rtrim($_SERVER['DOCUMENT_ROOT'],'/\\').'/../data/files/';
		$user_files_dir = $files_dir.$user_id.'/'; //FILES_DIR.$user_id.'/';
		if (!file_exists($user_files_dir)) { // User Files folder doesn't exist yet...
			if (mkdir($user_files_dir, 0775)) { // ...so create it!
				return $user_files_dir;
			} else {
				return false;
			}
		} else {
			return $user_files_dir; // User Files folder already exists, return it!
		}
	}
	
	/**
	 * Get User Telegram Chat-ID
	 *
	 * Prüft ob der User-ID einen Telegram Messenger Chat-ID eingetragen hat
	 * -> wenn ja, wird die Telegram Chat-ID zurückgegeben
	 *
	 * @author IneX
	 * @date 22.01.2017
	 * @since 4.0
	 * @version 2.0
	 *
	 * @param $user_id interger User-ID
	 * @return integer The User's Telegram Chat-ID
	 */
	function userHasTelegram($user_id)
	{
		global $db;

		/** Validte $user_id - valid integer & not empty/null */
		if (empty($user_id) || $user_id === NULL || $user_id <= 0) return false;

		try {
			$query = $db->query('SELECT telegram_chat_id tci FROM user WHERE id='.$user_id.' LIMIT 1', __FILE__, __LINE__, __METHOD__);
			$result = $db->fetch($query);
			return ( $result ? $result['tci'] : false );
		}
		catch(Exception $e) {
			error_log($e->getMessage());
			return false;
		}
	}
}

$user = new usersystem();

if(isset($_POST['username']) && $_POST['username'] != '') {
	$_POST['cookie'] ? $auto = TRUE : $auto = FALSE;
	$login_error = $user->login($_POST['username'], $_POST['password'], $auto);
}
// LOGIN mit cookie (autologin)
if(isset($_COOKIE['autologin_id']) && $_COOKIE['autologin_id'] != '' && !$_SESSION['user_id']) {
	$login_error = $user->login($_COOKIE['autologin_id'],"",1);
}
// LOGOUT?
if(isset($_POST['logout'])) {
	$user->logout();
}
