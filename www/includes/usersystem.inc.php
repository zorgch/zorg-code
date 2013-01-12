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
 */
include_once($_SERVER['DOCUMENT_ROOT'].'includes/colors.inc.php');
include_once($_SERVER['DOCUMENT_ROOT'].'includes/util.inc.php');
include_once($_SERVER['DOCUMENT_ROOT'].'includes/mysql.inc.php');

/**
 * Defines
 */
define(USER_ALLE, 0);
define(USER_USER, 1);
define(USER_MEMBER, 2);
define(USER_SPECIAL, 3);
//define(USER_EINGELOGGT, 0);define(USER_MEMBER, 1);
//define(USER_NICHTEINGELOGGT, 2);
//define(USER_ALLE, 3);
define(USER_IMGPATH, "/images/userimages/");
define(USER_TIMEOUT, 200);
define(USER_OLD_AFTER, 60*60*24*30*3); // 3 Monate
define(DEFAULT_MAXDEPTH, 10);
define(AUSGESPERRT_BIS, "ausgesperrt_bis");


/**
 * Usersystem Klasse
 * 
 * @author [z]biko
 * @version 2.0
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

	var $table_name = "user";
	var $dbc;
	var $field_username = "username";
	var $field_clantag = "clan_tag";
	var $field_userpw = "userpw";
	var $field_email = "email";
	var $field_lastlogin = "lastlogin";
	var $field_maxdepth = "forummaxthread";
	var $field_usertyp = "usertype";
	var $crypt_salt = "CRYPT_BLOWFISH";

	//pugin vars
	var $field_bild = "image";
	var $field_activity = "activity";
	var $field_last_ip = "last_ip";
	var $field_sessionid = "sessionid";
	var $field_currentlogin = "currentlogin";
	var $field_ausgesperrt_bis = "ausgesperrt_bis";
	var $field_regdate = "regdate";
	var $field_regcode = "regcode";
	var $field_user_active = "active";
	var $field_mail_userpw = "mail_userpw";
	var $field_mail_username = "mail_username";
	var $field_show_comments = "show_comments";
	var $field_email_notification = "email_notification";
	var $field_sql_tracker = "sql_tracker";
	var $field_addle = "addle";
	var $field_chess = 'chess';
	var $field_menulayout = "menulayout";
	var $field_mymenu = "mymenu";
	var $field_zorger = "zorger";
	var $field_from_mobile = "from_mobile";

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
	 * @return usersystem
	 */
	function usersystem() {
		global $db;
		session_name("z");
		$this->typ = USER_ALLE;
		
		
		// Session init'en
		if($_GET['z'] != '' || $_POST['z'] != '' || $_COOKIE['z'] != '') {
			session_start();
			$sql = "SELECT *, UNIX_TIMESTAMP(".$this->field_activity.") as ".$this->field_activity.",
			UNIX_TIMESTAMP(".$this->field_lastlogin.") as ".$this->field_lastlogin.",
			UNIX_TIMESTAMP(".$this->field_currentlogin.") as ".$this->field_currentlogin."
			FROM ".$this->table_name." WHERE id = '$_SESSION[user_id]'";
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
			if (usersystem::checkimage($_SESSION[user_id])) {
			   $this->image = USER_IMGPATH.$_SESSION[user_id]."_tn.jpg";
			}else{
			   $this->image = USER_IMGPATH."none.jpg";
			}
			
			$this->forum_boards = explode(",", $rs['forum_boards']);
			$this->forum_boards_unread = explode(",", $rs['forum_boards_unread']);
			
			$this->mail_userpw = $rs[$this->field_mail_userpw];
			$this->mail_username = $rs[$this->field_mail_username];

			if(file_exists($_SERVER['DOCUMENT_ROOT']."/images/users/".$_SESSION['user_id'].".jpg")) {
				$this->image = $_SESSION['user_id'].".jpg";
			}

			$rs[$this->field_usertyp] >= 1 ? $this->member = 1 : $this->member = 0;
			
			// User Agent suchen - Loginart (normal / mobile) festlegen - wird nur in Session geadded, nicht in DB gespeichert
			//isMobileClient($_SERVER['HTTP_USER_AGENT']) <> '' ? $this->is_mobile = 1 : $this->is_mobile = 0;
			$this->from_mobile = isMobileClient($_SERVER['HTTP_USER_AGENT']);

			$sql = "UPDATE ".$this->table_name." SET ".$this->field_activity." = now(),
			".$this->field_last_ip." = '".$_SERVER['REMOTE_ADDR']."', ".$this->field_from_mobile." = '".$this->from_mobile."'
			WHERE id = '$_SESSION[user_id]'";
			$db->query($sql, __FILE__, __LINE__);
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
			$crypted_pw = $this->crypt_pw($password);
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
	
						header("Location: ".$_SERVER['PHP_SELF']."?". session_name(). "=". session_id());
					} else {
						echo "Du bist ausgesperrt! (bis ".date("d.m.Y", $rs[AUSGESPERRT_BIS]).")";
						exit;
					}

				} else { $error = "Dein Account wurde noch nicht aktiviert"; }
			} else {
				$this->logerror(1,$rs['id']);
				$error = "Dieses Passwort ist falsch!";
			}
		} else { $error = "Dieser Benutzer existiert nicht!"; }
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
	}
	
	
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
	 * @return string error
	 * @param $email string E-Mail
	 */
	function new_pass($email) {
		global $db;
		if($email) {

			//?berp?fe email
			if($this->check_email($email)) {
				$sql = "SELECT id, username FROM user WHERE email = '$email'";
				$result = $db->query($sql, __FILE__, __LINE__);

				//?berpr?fe ob user mit email existiert
				if($db->num($result)) {
					$rs = $db->fetch($result);

					//generiere passwort
					$new_pass = $this->password_gen($rs['username']);
				
					//verschl?ssle passwort
					$crypted = $this->crypt_pw($new_pass);

					//trage aktion in errors ein
					$this->logerror(3,$rs['id']);

					//update user table
					$sql = "UPDATE user set userpw = '$crypted' WHERE id = '$rs[id]'";
					$db->query($sql, __FILE__, __LINE__);

					$body = "Neues Passwort fuer den Benutzer: ".$rs['username']."\
					Passwort: ".$new_pass."\n
					Dieses Passwort kannst du auf unserer Website unter mein Profil wieder aendern.\n
					Weiterhin wuenschen wir dir viel Spass auf www.zooomclan.org\n
					zooomclan.org";

					//versende email
					@mail($email,"Neues Passwort",$body,"From: info@zooomclan.org\n");
					$error = "Ein neues Passwort wurde generiert und dir zugestellt!";

				} else {
					$error = "Es existiert kein Benutzer mit dieser E-Mail Adresse!";
				}
			} else {
				$error = "Diese E-Mail Adresse ist ung&uuml;ltig!";
			}
		}
		return $error;
	}

	/**
	 * Benutzer erstellen
	 * 
	 * Erstellt einen Neuen Benutzer
	 * 
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

			//?berpr?fe ob user bereits existiert
			if(!$db->num($result)) {

				//?berpr?fe korrektheit der mail adresse
				if($this->check_email($email)) {
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
							$crypted_pw = $this->crypt_pw($pw);

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

							$body = "Willkommen bei www.zooomclan.org, deine Benutzerdaten sind:\n
							Benutzername: ".$username."
							Passwort: ".$pw."\n
							Wir bitten dich deinen Account noch freizuschalten, dazu musst du lediglich folgende Website aufrufen:
							http://www.zooomclan.org/profil.php?menu_id=13&regcode=".$key."\n

							Wir wünschen dir viel Spass!\n

							zooomclan.org";

							//email versenden
							@mail($email,"Benutzerdaten zooomclan.org",$body,"From: info@zooomclan.org\n");

							$error = "Dein Account wurde erfolgreich erstellt, du wirst in k&uuml;rze eine E-Mail mit weiteren Informationen bekommen!";
						} else {
							$error = "<font color='red'>Du hast dich vertippt, bitte wiederholen!</font>";
						}
					} else {
						$error = "<font color='red'>Es besteht bereits ein Benutzer mit dieser E-Mail Adresse!</font>";
					}
				} else {
					$error = "<font color='red'>Diese E-Mail Adresse ist ung&uuml;ltig!</font>";
				}
			} else {
				$error = "<font color='red'>Es besteht bereits ein Benutzer mit diesem Namen!</font>";
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
					'<table bgcolor="#'.TABLEBACKGROUNDCOLOR.'" border="0"><tr><td><a href="/profil.php?user_id='.$rs['id'].'">'
					.'<img border="0" src="/images/userimages/'.$rs['id'].'.jpg" title="'.$rs['clan_tag'].$rs['username'].'">'
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
	 * Passwort encryption
	 * 
	 * Verschlüsselt ein Passwort
	 * 
	 * @return string crypted Passwort
	 * @param $password string Plaintext Passwort
	 */
	function crypt_pw($password) {
		return crypt($password,$this->crypt_salt);
	}

	/**
	 * E-Mailadresse prüfen
	 * 
	 * Überprüft eine E-Mail Adresse
	 * 
	 * @return bool
	 * @param $email string E-Mail
	 */
	function check_email($email) {
		if(eregi("^[a-z0-9\._-]+@+[a-z0-9\._-]+\.+[a-z]{2,3}$", $email)) return TRUE;
		else return FALSE;

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
			$error = "Dein Account wurde soeben aktiviert!";
		} else {
			$this->logerror(2,0);
			$error = "Unbekannter Registrierungscode!";
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
		1 => "Falsches Passwort!",
		2 => "Unbekannter Registrierungscode!",
		3 => "Neues Passwort gesetzt!");

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
	* @return bool
	* @param $id int User ID
	*/
	function checkimage($id) {
		if(file_exists($_SERVER['DOCUMENT_ROOT'].USER_IMGPATH.$id.".jpg")) {
			return TRUE;
		} else {
			return FALSE;
		}
	}
	
	
	/**
	* Userpic Pfad
	* 
	* Gibt den Pfad zum Bild des Users. Falls kein Bild: none.jpg
	* 
	* @return string Pfad zum Bild des Users
	* @param $id int User ID
	*/
	function userImage($id, $large=0) {
	   if (usersystem::checkimage($id)) {
	   	if ($large) return USER_IMGPATH.$id.'.jpg';
	   	else return USER_IMGPATH.$id.'_tn.jpg';
	   }else{
	      return USER_IMGPATH."none.jpg";
	   }
	}
	
	function getFormFieldUserlist($name, $size, $users_selected=0, $tabindex=10) {
		global $db;
		
		// Wenn User ganz neue Message schreibt
		if ($users_selected == 0) $users_selected = Array();
		
		$sql = 
			"SELECT id, clan_tag, username FROM user"
			." WHERE UNIX_TIMESTAMP(lastlogin) > (UNIX_TIMESTAMP(now())-".(USER_OLD_AFTER*2).")"
			." ORDER BY clan_tag DESC, username ASC"
		;
		$result = $db->query($sql, __FILE__, __LINE__);
		
		$html = '<select multiple="multiple" name="'.$name.'" size="'.$size.'" tabindex="'.$tabindex.'">';
		while ($rs = mysql_fetch_array($result)) {
			$html .= 
				'<option value="'.$rs['id'].'"'
				//.(in_array($rs['id'], $users_selected) ? ' selected="selected"' : '')
				.($rs['id'] == $users_selected[0] ? ' selected="selected"' : '')
				.'>'
				.$rs['clan_tag'].$rs['username'].'</option>'
			;
		}
		$html .= '</select>';
		
		return $html;
	}
	
	
	function id2user($id, $clantag=FALSE, $pic=FALSE) {
		global $db, $zorg, $zooomclan;
		static $_users = array();
		
   		if (!isset($_users[$id])) {
      		$sql = "SELECT clan_tag, username FROM user WHERE id='$id'";
      		$result = $db->query($sql, __FILE__, __LINE__);
      		while ($rs = mysql_fetch_array($result)) {
      		   $_users[$id] = $rs;
      		}
   		}
   		$us = $_users[$id][username];
   		if($clantag == TRUE) {
   			$us = $_users[$id]['clan_tag'].$us;
   		}
   		
   		
		if($pic == TRUE) {
   			$us = 
   				'<img alt="'.$us.'" border="0" src="'.usersystem::userImage($id).'" title="'.$us.'"'
   			;
   			
   			if ($zorg == true) {
   				$us .= ' height="65">';
   			} else {
   				$us .= '>';
   			}
   		}
   		return $us;
	}
	
	
	function user2id ($username) {
		global $db;
		$e = $db->query("SELECT id FROM user WHERE username='$username' LIMIT 1", __FILE__, __LINE__);
		$d = $db->fetch($e);
		if ($d) return $d['id'];
		else return 0;
	}
	
	
	/**
	 *
	 * Userpic (klein) ausgeben
	 *
	 * @author IneX
	 * @date 02.10.2009
	 * 
	 * @param	$id				User-ID
	 * @param	$displayName	Zeigt Usernamen unter dem Bild an
	 * @return	string			Link zum Userpic
	 * 
	 */
	function userpic($id, $displayName=FALSE)
		{
		global $db;
		static $_users = array();
		
		if ($displayName) {
	   		if (!isset($_users[$id])) {
	      		$sql = "SELECT clan_tag, username FROM user WHERE id='$id'";
	      		$result = $db->query($sql, __FILE__, __LINE__);
	      		while ($rs = mysql_fetch_array($result)) {
	      		   $_users[$id] = $rs;
	      		}
	   		}
	   		$us = $_users[$id][username];
	   		if($clantag == TRUE) {
	   			$us = $_users[$id]['clan_tag'].$us;
	   		}
	   	}
   		
   		
		$us =
			'<a href="/profil.php?user_id='.$id.'">'.
			'<img alt="'.$us.'" border="0" src="'.usersystem::userImage($id).'" title="'.$us.'" height="65">'.
			'</a>'
		;
		
   		return $us;
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
	* Gibt aufgrund einer User ID dessen E-Mailadresse zurück
	* 
	* @return string email
	* @param $id int User ID
	*/
	function id2useremail($id) {
		global $db;
		$sql = "SELECT email FROM user WHERE id = ".$id." AND email_notification = 1"; // Nur wenn User E-Mail Notification aktiviert hat!
		$result = $db->query($sql, __FILE__, __LINE__);
		$rs = $db->fetch($result);
		return $rs['email'];
	}
	
	
	
	/**
	* Link zum Userprofil
	* 
	* Gibt eine User ID als link zur userpage aus
	* 
	* @return string html
	* @param $user_id int User ID
	* @param $image bool
	*/
	function link_userpage($user_id, $pic=FALSE) {
		if($user_id != '') {

			$html =
		  	'<a href="/profil.php?user_id='.$user_id.'">'
		  	.usersystem::id2user($user_id, TRUE, $pic)
		  	.'</a>'
		  ;
		}
		return $html;
	}

	
	function userpagelink($userid, $clantag, $username) {
		$name = $clantag.$username;
		
		// Dreadwolfs spezieller Nick
		//if($userid == 307) $name = '<b style="background-color: green; color: white;">&otimes; '.$name.' &oplus;</b>';
		
		return '<a href="/profil.php?user_id='.$userid.'">'.$name.'</a>';
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
			$total = $rs[anzahl];

			mt_srand((double)microtime()*1000000);
		    $rnd = mt_rand(1, $total);
			$sql = "SELECT * FROM quotes WHERE user_id = ".$user_id;
			$result = $db->query($sql, __FILE__, __LINE__);

			for ($i=0;$i<$rnd;$i++){
				$rs = $db->fetch($result);
			}
			$quote = $rs[text];
			return $quote;
		}
	}
	
	function userSelectBox() {
	}
}

$user = new usersystem();
if($_POST['username'] != '') {
	$_POST['cookie'] ? $auto = TRUE : $auto = FALSE;
	$login_error = $user->login($_POST['username'], $_POST['password'], $auto);
}
// LOGIN mit cookie (autologin)
if($_COOKIE['autologin_id'] != '' && !$_SESSION['user_id']) {
	$login_error = $user->login($_COOKIE['autologin_id'],"",1);
}
// LOGOUT?
if($_POST['logout']) {
	$user->logout();
}
?>
