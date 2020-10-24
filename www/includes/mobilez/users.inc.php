<?php
/**
 * User Management
 * Various functions to handle user management
 * like login, logout, profiles, etc.
 * 
 * @author IneX
 * @date 16.01.2016
 * @version 1.0
 * @package zorg\Mobilezorg
 */
class UserManagement
{
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
}
