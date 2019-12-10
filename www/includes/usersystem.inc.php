<?php
/**
 * zorg Usersystem
 *
 * Enthält alle User Funktionen von zorg
 *
 * @author [z]biko
 * @package zorg\Usersystem
 */
/**
 * File includes
 * @include	config.inc.php 		Include required global site configurations
 * @include	colors.inc.php 		Colors
 * @include util.inc.php 		Various Helper Functions
 * @include mysql.inc.php 		MySQL-DB Connection and Functions
 * @include strings.inc.php 	Text strings to be replaced within code functions etc.
 * @include	activities.inc.php	Activities Functions and Stream
 */
require_once( __DIR__ .'/config.inc.php');
include_once( __DIR__ .'/colors.inc.php');
require_once( __DIR__ .'/util.inc.php');
require_once( __DIR__ .'/mysql.inc.php');
require_once( __DIR__ .'/strings.inc.php');
require_once( __DIR__ .'/activities.inc.php');

/**
 * Usersystem Klasse
 *
 * Verschlüsselungs Möglichkeiten:
 * CRYPT_STD_DES - Standard DES-Schlüssel mit 2-Zeichen Salt
 * CRYPT_EXT_DES - Erweiterter DES-Schlüssel mit einem 9-Zeichen Salt
 * CRYPT_MD5 - MD5-Schlüssel mit 12-Zeichen Salt, beginnend mit $1$
 * CRYPT_BLOWFISH - Erweiterter DES-Schlüssel, 16-Zeichen Salt, beginnend mit $2$
 * 
 * User Typen:
 * 1 = Normaler User ##################### 0 isch nöd so cool wil wenns nöd gsetzt isch chunt jo au 0
 * 2 = [z]member und schöne
 * 0 = nicht eingeloggt ##################### Aber Weber: wenn typ = 2, gits $user jo gar nöd?! -> doch s'usersystem isch jo immer verfügbar
 * verfügbar über $user->typ
 *
 * @author [z]biko
 * @author IneX
 * @package zorg\Usersystem
 * @version 5.0
 * @since 1.0 class added
 * @since 2.0 additional methods added
 * @since 3.0 code optimizations and new methods
 * @since 4.0 10.12.2018 major refactorings & migrated methods from profil.php as part of the usersystem()-class
 * @since 5.0 26.12.2018 Bug #769: 'usertyp'-Spalte entspricht neu einer Usergruppe aus dem Table 'usergroups' (quasi als Foreign-Key)
 */
class usersystem
{
	/**
	 * Usersystem Configs
	 *
	 * @var bool $use_cookie Auto-einloggen mit Cookie aktivieren
	 * @var bool $use_current_login Wird benötigt um nicht gesichteten content hervorzuheben
	 * @var bool $use_registration_code Wird benötigt um ein Account von einem User zweifelsfrei aufzuschalten
	 * @var bool $use_online_list Unterstützung einer "wer-ist-alles-online-liste"
	 * @var bool $use_user_picture Jeder User kann ein Bild von sich hochladen
	 * @var string $table_name DB-Table wo die User-Daten gespeichert sind, wird für die SQL-Queries benötigt
	 */
	var $use_cookie = TRUE;
	var $use_current_login = TRUE;
	var $use_registration_code = TRUE;
	var $use_online_list = TRUE;
	var $use_user_picture = TRUE;
	var $table_name = 'user';

	/**
	 * Var to map User Fields
	 */
	var $field_activities_allow = 'activities_allow';
	var $field_activity = 'activity';
	var $field_addle = 'addle';
	var $field_ausgesperrt_bis = 'ausgesperrt_bis';
	var $field_bild = 'image';
	var $field_chess = 'chess';
	var $field_clantag = 'clan_tag';
	var $field_currentlogin = 'currentlogin';
	var $field_email = 'email';
	var $field_from_mobile = 'from_mobile';
	var $field_irc = 'irc_username';
	var $field_last_ip = 'last_ip';
	var $field_lastlogin = 'lastlogin';
	var $field_maxdepth = 'forummaxthread';
	var $field_menulayout = 'menulayout';
	var $field_mymenu = 'mymenu';
	var $field_notifications = 'notifications';
	var $field_regcode = 'regcode';
	var $field_regdate = 'regdate';
	var $field_sessionid = 'sessionid';
	var $field_show_comments = 'show_comments';
	var $field_sql_tracker = 'sql_tracker';
	var $field_telegram = 'telegram_chat_id';
	var $field_user_active = 'active';
	var $field_username = 'username';
	var $field_userpw = 'userpw';
	var $field_usertyp = 'usertype';
	var $field_zorger = 'zorger';
	var $field_z_gremium = 'z_gremium';
	var $field_vereinsmitglied = 'vereinsmitglied';
	var $field_firstname = 'firstname';
	var $field_lastname = 'lastname';

	/**
	 * Default Userprofile Settings
	 * @see usersystem::exec_changeprofile()
	 */
	var $default_clan_tag = null; // none
	var $default_activities_allow = '1'; // enabled
	var $default_telegram_chat_id = null; // none
	var $default_irc_username = null; // none
	var $default_addle = '1'; // enabled
	var $default_chess = '1'; // enabled
	var $default_forum_boards = '["b","e","f","o","r","t"]'; // Bugtracker, Events, Forum, Go, Tauschbörse, Templates
	var $default_forum_boards_unread = '["b","e","f","g","h","i","o","t"]'; // Bugtracker, Events, Forum, Hunting z, Gallery, Tauschbörse, Templates
	var $default_forummaxthread = 10; // depth: 10
	var $default_menulayout = ''; // none (String, because ENUM='')
	var $default_mymenu = null; // none
	var $default_notifications = '{"bugtracker":{"message":"true","email":"true"},"games":{"email":"true"},"mentions":{"email":"true"},"messagesystem":{"email":"true"},"subscriptions":{"message":"true"}}';
	var $default_show_comments = '1'; // enabled
	var $default_sql_tracker = '0'; // disabled
	var $default_usertype = 0; // nicht-eingeloggt
	var $default_zorger = '0'; // zorg-Layout
	var $default_vereinsmitglied = '0'; // kein Mitglied
	var $default_z_gremium = ''; // no
	var $default_firstname = null; // none
	var $default_lastname = null; // none
	
	/**
	 * Object Vars
	 * @var string (Optional) Error-Message, see: self::activate_user()
	 */
	var $error_message;

	/**
	 * Klassen Konstruktor
	 *
	 * @TODO Will be deprecated in PHP7! -> http://php.net/manual/de/migration70.deprecated.php
	 *
	 * @author [z]biko
	 * @author IneX
	 * @version 4.1
	 * @since 1.0 method added
	 * @since 2.0 <inex> 20.11.2018 code & query optimizations, updated Cookie & Session info taken from config.inc.php
	 * @since 3.0 <inex> 27.11.2018 refactored User-Object instantiation if $_SESSION[user_id] is missing but Session-Cookie is there
	 * @since 4.0 <inex> 10.12.2018 adjusted reading the Autologin-Cookies (cannot be dependent on the Session-Cookie, doh!)
	 * @since 4.1 <inex> 02.11.2019 fixed ENUM("0")-Values from User DB-Record wrongfully set=true instead of =false
	 *
	 * @see ZORG_SESSION_ID, ZORG_COOKIE_SESSION, ZORG_COOKIE_USERID, ZORG_COOKIE_USERPW
	 * @see usersystem::login(), usersystem::invalidate_session(), timestamp()
	 * @return object usersystem()-Class object
	 */
	function usersystem()
	{
		global $db;

		/**
		 * Session init'en
		 */
		session_name(ZORG_SESSION_ID);
		session_start();
		$this->typ = USER_ALLE; // grundsätzlich ist jeder zuerst mal "Gast"

		/**
		 * User Session konfigurieren
		 *
		 * Nur wenn...
		 * - ...ZORG_SESSION_ID => gesetzt
		 * - ...oder ZORG_COOKIE_SESSION => gesetzt
		 */
		if (!empty($_GET[ZORG_SESSION_ID]) || !empty($_POST[ZORG_SESSION_ID]) || !empty($_COOKIE[ZORG_COOKIE_SESSION]))
		{
			//session_start();

			if (!empty($_SESSION['user_id']))
			{
				/** Query User Infos in der DB */
				$sql = 'SELECT
							 *,
							 UNIX_TIMESTAMP('.$this->field_activity.') as '.$this->field_activity.',
							 UNIX_TIMESTAMP('.$this->field_lastlogin.') as '.$this->field_lastlogin.',
							 UNIX_TIMESTAMP('.$this->field_currentlogin.') as '.$this->field_currentlogin.'
						FROM '.$this->table_name.' 
						WHERE
							 id = '.$_SESSION['user_id'];
				$result = $db->query($sql, __FILE__, __LINE__);
				$rs = $db->fetch($result);

				/**
				 * Assign User Infos to Vars of the Class-Object
				 *
				 * @var integer $id user_ID
			 	 * @var string $activity Letzte Aktivität (Timestamp) des Users
				 * @var string $addle Ob user addle spielen will.
				 * @var string $clantag Clan Tag
				 * @var string $currentlogin Aktueller login (Timestamp)
				 * @var string $email Benutzer E-Mail Adresse
				 * @var string $from_mobile wenn <>"", dann nutzt User einen mobilen Browser, bei 0 oder 'false' Desktop-Browser
				 * @var string $image Benutzer bild (vollst?ndiger Pfad, falls kein Bild: "none.jpg")
				 * @var string $lastlogin Letzer Login (Timestamp)
				 * @var integer $maxdepth Forumanzeigeschwelle
				 * @var integer $member Member (bool)
				 * @var string $menulayout welches menu layout der user eingestellt hat.
				 * @var string $password User passwort
				 * @var string $show_comments Ob die Comments auf den smarty-pages angezeigt werden sollen (=1) oder nicht (=0)
				 * @var integer $typ Benutzer typ
				 * @var string $username Benutzername (ohne Clan Tag)
				 * @var string $zorger hat der user zooomclan.org (retro) gewählt? sonst zorg.ch (modern) anzeigen
				 * @var string $vereinsmitglied Vereinsmitglied-Status des user
				 */
				$this->id = $_SESSION['user_id'];
				$this->email = $rs[$this->field_email];
				$this->username = $rs[$this->field_username];
				$this->clantag = $rs[$this->field_clantag];
				$this->userpw = $rs[$this->field_userpw];
				$this->typ = ($rs[$this->field_usertyp] != '' ? $rs[$this->field_usertyp] : USER_ALLE);
				$rs[$this->field_usertyp] >= 1 ? $this->member = 1 : $this->member = 0;
				$this->image = self::userImage(intval($_SESSION['user_id']));
				$this->telegram = ($rs[$this->field_telegram] === '0' ? null : $rs[$this->field_telegram]);
				$this->irc = $rs[$this->field_irc];
				$this->activity = $rs[$this->field_activity];
				$this->lastlogin = $rs[$this->field_lastlogin];
				if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> $user->lastlogin: %s', __METHOD__, __LINE__, $this->lastlogin));
				$this->currentlogin = $rs[$this->field_currentlogin];
				if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> $user->currentlogin: %s', __METHOD__, __LINE__, $this->currentlogin));
				$this->ausgesperrt_bis = $rs[$this->field_ausgesperrt_bis];
				if ($this->ausgesperrt_bis > time()) $_geaechtet[] = $this->id;
				$this->last_ip = $rs[$this->field_last_ip];
				$this->activities_allow = ($rs[$this->field_activities_allow] === '0' ? false : true);
				$this->show_comments = ($rs[$this->field_show_comments] === '0' ? false : true);
				$this->notifications = json_decode( (!empty($rs[$this->field_notifications]) ? $rs[$this->field_notifications] : $this->default_notifications), true); // JSON-Decode to Array
				$this->sql_tracker = ($rs[$this->field_sql_tracker] === '0' ? false : true);
				$this->addle = ($rs[$this->field_addle] === '0' ? false : true);
				$this->chess = ($rs[$this->field_chess] === '0' ? false : true);
				$this->forum_boards = json_decode($rs['forum_boards'], true);//explode(',', $rs['forum_boards']);
				$this->forum_boards_unread = json_decode($rs['forum_boards_unread'], true);//explode(',', $rs['forum_boards_unread']);
				$this->maxdepth = ($rs[$this->field_maxdepth] ? $rs[$this->field_maxdepth] : $this->maxdepth = DEFAULT_MAXDEPTH);
				$this->menulayout = $rs[$this->field_menulayout];
				$this->mymenu = $rs[$this->field_mymenu];
				$this->zorger = ($rs[$this->field_zorger] === '0' ? false : true);
				$this->vereinsmitglied = $rs[$this->field_vereinsmitglied];
				$this->z_gremium = $rs[$this->field_z_gremium];

				/** Nur für Vereinsmitglieder */
				if (!empty($this->vereinsmitglied)) $this->firstname = $rs[$this->field_firstname];
				if (!empty($this->vereinsmitglied)) $this->lastname = $rs[$this->field_lastname];

				/**
				 * Mobile User Agent abfragen & speichern
				 * wenn nicht Mobile, dann in der DB '' (leer) und im $user Object 'false'
				 */
				$userMobileClientAgent = isMobileClient($_SERVER['HTTP_USER_AGENT']);
				$this->from_mobile = (!empty($userMobileClientAgent) ? reset($userMobileClientAgent) : false );
				if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> isMobileClient(): %s => %s', __METHOD__, __LINE__, $_SERVER['HTTP_USER_AGENT'], ( $this->from_mobile ? $this->from_mobile : 'false')));

				/**
				 * Update last user activity
				 * @TODO Activity nur updaten wenn vorherige & aktuelle Page-URL (z.B. Referrer vs. ...) nicht identisch sind?
				 */
				$db->update($this->table_name, ['id', $this->id], [
					$this->field_activity => timestamp(false),
					$this->field_last_ip => $_SERVER['REMOTE_ADDR'],
					$this->field_from_mobile => ($this->from_mobile === false ? '' : $this->from_mobile), // because 'ENUM'-fieldtype
				], __FILE__, __LINE__, __METHOD__);
			}

			/** Falls Session-Cookies existieren */
			elseif (!empty($_COOKIE[ZORG_COOKIE_USERID]) && !empty($_COOKIE[ZORG_COOKIE_USERPW]))
			{
				$this->login($_COOKIE[ZORG_COOKIE_USERID]);
			}
		}

		/** Oder Login-Passthrough - falls Autologin-Cookies existieren */
		elseif (!empty($_COOKIE[ZORG_COOKIE_USERID]) && !empty($_COOKIE[ZORG_COOKIE_USERPW]))
		{
			$this->login($_COOKIE[ZORG_COOKIE_USERID], $_COOKIE[ZORG_COOKIE_USERPW], true);
		}

		/** Ansonsten falls keine Session: zur Sicherheit Session-Cookie(s) & Session-Paramter in URL invalidieren */
		else {
			$this->invalidate_session();
		}
	}

	/**
	 * User Login
	 *
	 * Erstellt eine Session (login)
	 *
	 * @author [z]biko, IneX
	 * @version 4.1
	 * @since 1.0 method added
	 * @since 2.0 12.11.2018 code & query optimizations
	 * @since 3.0 21.11.2018 Fixed redirect bei Login auf jeweils aktuelle Seite, nicht immer Home
	 * @since 4.0 10.12.2018 Improved Cookie-Settings (secure and stuff)
	 * @since 4.1 21.12.2018 Fixed redirect auf ursprüngliche Seite bei Cookie-Login ohne Session
	 *
	 * @see ZORG_SESSION_ID, ZORG_COOKIE_SESSION, ZORG_COOKIE_USERID, ZORG_COOKIE_USERPW
	 * @see crypt_pw(), timestamp(), usersystem::invalidate_session()
	 * @see $login_error
	 * @param string $username Benutzername
	 * @param string $password Passwort-Hash
	 * @param boolean $use_cookie Use Cookie 'true' oder 'false' - default: false
	 * @return void|string Location-Redirect (via header), oder Error-message als string
	 */
	function login($username, $password=null, $use_cookie=false)
	{
		global $db;

		/** erstellt sql string für User überprüfung */
		$sql = sprintf('SELECT id, %s FROM %s WHERE %s = "%s" LIMIT 0,1', $this->field_userpw, $this->table_name, $this->field_username, $username);
		$result = $db->query($sql, __FILE__, __LINE__, __METHOD__);

		/** überprüfe ob der User existiert */
		if($db->num($result)) {
			$rs = $db->fetch($result);

			/**
			 * Verschlüsslet das übergebenes Passwort
			 * a) ...wie übergeben vom Login-Formular
			 * b) ...aus dem - wenn vorhanden - Brwoser-Cookie
			 */
			if (!empty($password) && empty($_COOKIE[ZORG_COOKIE_USERPW]))
			{
				/** a) Login-Form Password Check */
				if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> !empty($password)', __METHOD__, __LINE__));
				$crypted_pw = crypt_pw($password);
			}

			elseif (!empty($_COOKIE[ZORG_COOKIE_USERPW]))
			{
				/** Cookie-Passwort Plausibilisierung gegen User DB-Eintrag */
				if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> !empty($_COOKIE[ZORG_COOKIE_USERPW])', __METHOD__, __LINE__));
				if ($_COOKIE[ZORG_COOKIE_USERPW] === $rs['userpw'])
				{
					/** b) Cookie Check */
					if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> $_COOKIE[ZORG_COOKIE_USERPW] === $rs[userpw]', __METHOD__, __LINE__));
					$crypted_pw = $_COOKIE[ZORG_COOKIE_USERPW];
				}

				/** Cookie Password vs. User DB-Eintrag matchen NICHT! */
				else {
					if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> User Cookie Password vs. User DB-Eintrag matchen NICHT!', __METHOD__, __LINE__));
					$this->invalidate_session();
					http_response_code(403); // Set response code 403 (forbidden)
					return user_error(t('invalid-cookie', 'user'), E_USER_WARNING); // Warnung ausgeben
				}
			}

			if (!empty($crypted_pw))
			{
				/** erstellt SQL string für Passwort-überprüfung */
				$sql = sprintf('SELECT
									 id,
									 %1$s,
									 UNIX_TIMESTAMP(%2$s) %2$s,
									 %3$s,
									 UNIX_TIMESTAMP(%4$s) %4$s
								FROM
									 %5$s
								WHERE 
									 %6$s = "%7$s"
									 AND %8$s = "%9$s"
								LIMIT 0,1',
								$this->field_user_active,
								$this->field_ausgesperrt_bis,
								$this->field_currentlogin,
								$this->field_lastlogin,
								$this->table_name,
								$this->field_username,
								$username,
								$this->field_userpw,
								$crypted_pw
						);
				if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> login: $db->query($sql) => %s', __METHOD__, __LINE__, print_r($sql,true)));
				$result = $db->query($sql, __FILE__, __LINE__, __METHOD__);

				/** überprüft ob passwort korrekt ist */
				if($db->num($result))
				{
					if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> login: password matches=>%d', __METHOD__, __LINE__, $db->num($result)));
					$rs = $db->fetch($result);

					/** überprüfe ob user aktiviert wurde */
					if($rs[$this->field_user_active] !== null && $rs[$this->field_user_active] !== 0 && $rs[$this->field_user_active] !== '0' && $rs[$this->field_user_active] !== false)
					{
						/** überprüfe ob User nicht ausgesperrt ist */
						if($this->is_lockedout($rs[$this->field_ausgesperrt_bis]) === false)
						{
							$_SESSION['user_id'] = $rs['id'];

							/** Wenn "Autologin" (mit Cookie) aktiviert wurde vom User */
							if($this->use_cookie == TRUE && $use_cookie)
							{
								/**
								 * Cookie settings:
								 * - name: Der Name des Cookies
								 * - value: Der Wert des Cookies
								 * - expires: Der Zeitpunkt, an dem das Cookie ungültig wird. time()+60*60*24*14 wird das Cookie in 30 Tagen ablaufen lassen
								 * - path: Der Pfad auf dem Server, für welchen das Cookie verfügbar sein wird. '/' = innerhalb der gesamten domain
								 * - domain*: Die (Sub)-Domain, der das Cookie zur Verfügung steht.
								 * - secure: Gibt an, dass das Cookie vom Client nur über eine sichere HTTPS-Verbindung übertragen werden soll.
								 * - httponly:  Wenn auf TRUE gesetzt, ist das Cookie nur via HTTP-Protokoll zugreifbar (z.B. nicht mehr via JavaScript auslesbar/veränderbar)
								 * 
								 * *Important remark for 'domain': domain names must contain at least two dots (.),
								 * hence 'localhost' is invalid and the browser will refuse to set the cookie! instead for localhost you should use false. 
								 * @link http://php.net/manual/de/function.setcookie.php
								 * @link http://php.net/manual/de/function.setcookie.php#73107
								 */
								$cookieTimeout = time()+60*60*24*7; // 1 Woche
								$cookieSecure = (SITE_PROTOCOL === 'https' ? true : false);
								/** PHP7.x ready
								$cookieSettings = [
													 'expires' => $cookieTimeout
													,'path' => '/'
													,'domain' => SITE_HOSTNAME
													,'secure' => $cookieSecure
													,'httponly' => true
												  ];
								setcookie(ZORG_COOKIE_USERID, $username, $cookieSettings);
								setcookie(ZORG_COOKIE_USERPW, $crypted_pw, $cookieSettings);
								*/
								setcookie(ZORG_COOKIE_USERID, $username, $cookieTimeout, '/', SITE_HOSTNAME, $cookieSecure);
								setcookie(ZORG_COOKIE_USERPW, $crypted_pw, $cookieTimeout, '/', SITE_HOSTNAME, $cookieSecure);
							}

							/** Last Login & current Login updaten */
							if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> Login update(user): %s=>%s | %s=>%s', __METHOD__, __LINE__, $this->field_lastlogin, timestamp(false, $rs[$this->field_lastlogin]), $this->field_currentlogin, timestamp(false, $rs[$this->field_currentlogin])));
							$db->update($this->table_name, ['id', $rs['id']], [
								$this->field_lastlogin => $rs[$this->field_currentlogin],
								$this->field_currentlogin => timestamp(false),
								$this->field_last_ip => $_SERVER['REMOTE_ADDR'],
							], __FILE__, __LINE__, __METHOD__);

							$loginRedirectUrl = (isset($_POST['redirect']) ? base64_decode($_POST['redirect']) : htmlspecialchars($_SERVER['PHP_SELF']).'?'.$_SERVER['QUERY_STRING']);
							if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> login: redirect url => %s', __METHOD__, __LINE__, $loginRedirectUrl));
							//header('Location: '.changeURL( (isset($_POST['redirect']) ? base64_decode($_POST['redirect']) : $_SERVER['PHP_SELF']), session_name().'='.session_id() ));
							header('Location: '.$loginRedirectUrl);
							exit;
						} else {
							echo t('lockout-message', 'user', date('d.m.Y', $rs[$this->field_ausgesperrt_bis]));
							exit;
						}
					} else {
						$error = t('account-inactive', 'user');
					}
				} else {
					if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> login: $db->num($result)=>ERROR for %s', __METHOD__, __LINE__, $db->num($result)));
					$this->logerror(1,$rs['id']);
					$error = t('authentication-failed', 'user'); // nicht gegen aussen exponieren, dass es einen Useraccount gibt aber falsches PW
				}
			} else {
				$error = t('authentication-failed', 'user');
			}
		} else {
			$error = t('authentication-failed', 'user');
		}
		return $error;
	}

	/**
	 * User Logout
	 *
	 * Logt einen User aus!
	 *
	 * @author [z]biko, IneX
	 * @version 3.0
	 * @since 1.0 method added
	 * @since 2.0 fixed "If you put a date too far in the past, IE will bark and igores it, i.e. the value will not be removed"
	 * @since 3.0 21.11.2018 Fixed redirect bei Logout auf jeweils aktuelle Seite, nicht immer Home
	 *
	 * @link https://stackoverflow.com/questions/686155/remove-a-cookie
	 * @see invalidate_session()
	 * @return void
	 */
	static function logout()
	{
		/** Session destroy & Cookies killen */
		self::invalidate_session();

		/** Redirect user back to last page */
		if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> logout: redirect url => %s', __METHOD__, __LINE__, base64_decode($_POST['redirect'])));
		header('Location: '. (isset($_POST['redirect']) ? base64_decode($_POST['redirect']) : $_SERVER['PHP_SELF']));
		exit;
	}

	/**
	 * Session & Cookies invalidieren
	 *
	 * @author IneX
	 * @version 1.0
	 * @since 1.0 <inex> 28.11.2018 method added
	 *
	 * @return void
	 */
	static function invalidate_session()
	{
		/** Session destroy */
		if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> Destroying Session for user %d', __METHOD__, __LINE__, $_SESSION['user_id']));
		unset($_SESSION['user_id']);
		session_destroy();

		/** Cookies killen - einmal unsetten & danach invalidieren */
		unset($_GET[ZORG_SESSION_ID]); // Session-Parameter unsetten
		unset($_POST[ZORG_SESSION_ID]); // Session-Parameter unsetten
		unset($_COOKIE[ZORG_COOKIE_SESSION]); // zorg Session-Cookie unsetten
		unset($_COOKIE[ZORG_COOKIE_USERID]); // Login-Cookie unsetten
		unset($_COOKIE[ZORG_COOKIE_USERPW]); // Password-Cookie unsetten
		setcookie(ZORG_COOKIE_SESSION, '', time()-3600); // zorg Session-Cookie invalidieren
		setcookie(ZORG_COOKIE_USERID, '', time()-3600); // Login-Cookie invalidieren
		setcookie(ZORG_COOKIE_USERPW, '', time()-3600); // Password-Cookie invalidieren
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
					$db->query($sql, __FILE__, __LINE__, __METHOD__);
		} elseif ($zooomclan == true) {
			$sql = "UPDATE ".$this->table_name."
					set ".$this->field_zorger." = 0
					WHERE id = '".$user_id."'";
					$db->query($sql, __FILE__, __LINE__, __METHOD__);
		}
	}

	/**
	 * Neues Passwort
	 * Generiert ein Passwort für einen bestehenden User
	 *
	 * @version 4.1
	 * @since 1.0 method added
	 * @since 2.0 global strings added
	 * @since 3.0 17.10.2018 Fixed Bug #763: Passwort vergessen funktioniert nicht
	 * @since 4.0 21.10.2018 Code & DB-Query improvements
	 * @since 4.1 04.01.2019 Fixed handling $db->update() result, changed Error messages, added debugging-output on DEV
	 *
	 * @see usersystem::password_gen(), crypt_pw()
	 * @param string $email E-Mailadresse für deren User das PW geändert werden soll
	 * @global	object	$db	Globales Class-Object mit allen MySQL-Methoden
	 * @return string Error-Message
	 */
	function new_pass($email) {
		global $db;

		/** Validate passed $email */
		if (is_numeric($email) || is_array($email) || empty($email)) return t('invalid-email', 'user');
		if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> Passwort reset for e-mail triggered: %s', __METHOD__, __LINE__, $email));

		/** E-mailadresse validieren */
		if (check_email($email) === true)
		{
			/** überprüfe ob User mit dieser Email existiert */
			if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> E-Mail format valid: %s', __METHOD__, __LINE__, $email));
			$sql = 'SELECT id, username FROM user WHERE email="'.$email.'"';
			$result = $db->query($sql, __FILE__, __LINE__, __METHOD__);
			if($db->num($result))
			{
				if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> $db->num($result) done', __METHOD__, __LINE__));
				$rs = $db->fetch($result);

				/** 1. generiere neues passwort */
				$new_pass = $this->password_gen();
				if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> password_gen() done: %s', __METHOD__, __LINE__, $new_pass));

				/** 2. verschlüssle neues passwort */
				$crypted = crypt_pw($new_pass);
				if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> crypt_pw() done: %s', __METHOD__, __LINE__, $crypted));

				/** 3. trage aktion in errors ein */
				$this->logerror(3,$rs['id']);
				if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> logerror() done', __METHOD__, __LINE__));

				/** 4. update neues pw in user table */
				$result = $db->update($this->table_name, ['id', $rs['id']], ['userpw' => $crypted], __FILE__, __LINE__, __METHOD__);
				if ($result !== false)
				{
					/** 5. versende email mit neuem passwort */
					$new_pass_mail_status = mail($email, t('message-newpass-subject', 'user'), t('message-newpass', 'user', [ $rs['username'], $new_pass ]), "From: ".ZORG_EMAIL."\n");
					if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> Passwort reset mail() sent with status: %s', __METHOD__, __LINE__, ($new_pass_mail_status?'true':'false')));
					if ($new_pass_mail_status) return true;//$error = t('newpass-confirmation', 'user');
					else return false;
				} else {
					error_log(sprintf('[NOTICE] <%s:%d> Passwort could not be updated in DB', __METHOD__, __LINE__));
					return false;
				}
			} else {
				error_log(sprintf('[NOTICE] <%s:%d> No matching User found for Passwort reset', __METHOD__, __LINE__));
				return false;
			}
		} else {
			error_log(sprintf('[NOTICE] <%s:%d> %s', __METHOD__, __LINE__, t('invalid-email', 'user')));
			return false;
		}
	}

	/**
	 * Benutzer erstellen
	 *
	 * Erstellt einen Neuen Benutzer
	 *
	 * @version 3.0
	 * @since 1.0 method added
	 * @since 2.0 replaced messages with Translation-String solution t()
	 * @since 3.0 04.12.2018 removed IMAP-code, code & query optimizations
	 *
	 * @see crypt_pw(), t()
	 * @param string $username Benutzername
	 * @param string $pw Passwort
	 * @param string $pw2 Passwortwiederholung
	 * @param string $email E-Mail
	 * @global object $db Globales Class-Object mit allen MySQL-Methoden
	 * @return string error
	 */
	function create_newuser($username, $pw, $pw2, $email) {
		global $db;

		if($username)
		{
			$sql = 'SELECT id FROM '.$this->table_name.' WHERE '.$this->field_username.' = "'.$username.'"';
			$result = $db->query($sql, __FILE__, __LINE__, __METHOD__);

			/** überprüfe ob user bereits existiert */
			if(!$db->num($result))
			{
				/** E-mailadresse validieren */
				if(check_email($email))
				{
					/** überprüfe ob user mit gleicher email nicht bereits existiert */
					$sql = 'SELECT id FROM '.$this->table_name.' WHERE '.$this->field_email.' = "'.$email.'"';
					$result = $db->query($sql, __FILE__, __LINE__, __METHOD__);
					if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> %s', __METHOD__, __LINE__, $sql));

					if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> $db->num($result): %d', __METHOD__, __LINE__, $db->num($result)));
					if($db->num($result) === 0)
					{
						/** überprüfe passwort übereinstimmung */
						if($pw === $pw2)
						{
							/** erstelle regcode */
							$key = $this->regcode_gen($username);

							/** verschlüssle passwort */
							$crypted_pw = crypt_pw($pw);

							/** user eintragen */
							$sql = "INSERT into ".$this->table_name." 
								(".$this->field_regcode.", ".$this->field_regdate.",
								".$this->field_userpw.",".$this->field_username.",
								".$this->field_email.", ".$this->field_usertyp.") 
								VALUES ('".$key."',NOW(),'".$crypted_pw."','".$username."','".$email."', 1)";
							$db->query($sql, __FILE__, __LINE__, __METHOD__);

							/** email versenden */
							$sendNewaccountConfirmation = mail($email, t('message-newaccount-subject', 'user'), t('message-newaccount', 'user', [ $username, SITE_URL, $key ]), 'From: '.ZORG_EMAIL."\n");
							if ($sendNewaccountConfirmation !== true)
							{
								error_log(sprintf('[NOTICE] <%s:%d> Account confirmation e-mail could NOT be sent', __FILE__, __LINE__));
								$error = t('error-userprofile-update', 'user');
							} else {
								//$error = t('account-confirmation', 'user');
								return true;
							}
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
		if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> create_newuser() Error: %s', __METHOD__, __LINE__, $error));
		return $error;
	}

	/**
	 * Online Users
	 * Gibt Online Users als HTML aus
	 *
	 * @TODO HTML can be returned using new function usersystem::userpage_link()
	 *
	 * @see USER_TIMEOUT
	 * @see /js/zorg.js
	 * @param boolean $pic Userpic anzeigen, oder nur Usernamen - default: false
	 * @global object $db Globales Class-Object mit allen MySQL-Methoden
	 * @return string html
	 */
	function online_users($pic=FALSE)
	{
		global $db;

		$sql = 'SELECT id, username, clan_tag
				FROM user
				WHERE UNIX_TIMESTAMP(activity) > (UNIX_TIMESTAMP(NOW()) - '.USER_TIMEOUT.')
				ORDER by activity DESC';
		$result = $db->query($sql, __FILE__, __LINE__, __METHOD__);
		$i = 0;
		while($rs = $db->fetch($result))
		{
			if ($pic == FALSE)
			{
				//$html .= usersystem::link_userpage($rs[id], FALSE).', ';
				$html .= '<a href="/profil.php?user_id='.$rs['id'].'">'.$rs['clan_tag'].$rs['username'].'</a>';
				if(($i+1) < $db->num($result)) $html .= ', ';
			} else {
				$html .= '<table bgcolor="'.TABLEBACKGROUNDCOLOR.'" border="0"><tr><td><a href="/profil.php?user_id='.$rs['id'].'">'
						 .'<img border="0" src="'.USER_IMGPATH_PUBLIC.$rs['id'].'.jpg" title="'.$rs['clan_tag'].$rs['username'].'">'
						 .'</a></td></tr>'
						 .'<tr>'
						 .'<td align="center">'
						 .'<a href="/profil.php?user_id='.$rs['id'].'">'.$rs['clan_tag'].$rs['username'].'</a>'
						 .'</td></tr></table><br />';
			}
			$i++;
		}
		return $html;
	}

	/**
	 * User aktivieren
	 * Aktiviert einen Useraccount mittels Regcode
	 *
	 * @version 2.0
	 * @since 1.0 Method added
	 * @since 2.0 <inex> 07.12.2019 Fixed $regcode check and response for profil.php
	 *
	 * @see self::$error_message
	 * @param string $regcode User Registration-Code
	 * @global object $db Globales Class-Object mit allen MySQL-Methoden
	 * @return bool True/False whether if user could be activated or not
	 */
	function activate_user($regcode)
	{
		global $db;

		$sql = 'SELECT id, username, active FROM user WHERE regcode = "'.$regcode.'"';
		$result = $db->query($sql, __FILE__, __LINE__, __METHOD__);
		if($db->num($result))
		{
			if (DEVELOPMENT === true) error_log(sprintf('[DEBUG] <%s:%d> User regcode: VALID', __FUNCTION__, __LINE__));
			$rs = $db->fetch($result);

			/** User already activated */
			if ($rs[$this->field_user_active] == '1')
			{
				$this->error_message = t('account-is-active', 'user');
				return false;
			}

			/** Try activating User */
			else {
				$username = $rs[$this->field_username];
				$user_activated = $db->update($this->table_name, ['id', $rs['id']], [$this->field_user_active => 1], __FILE__, __LINE__, __METHOD__);
				/** FAILED */
				if ($user_activated === 0 || !$user_activated)
				{
					$this->error_message = t('invalid-regcode', 'user');
					return false;
				}
				/** SUCCESS */
				else {
					$this->error_message = t('account-activated', 'user');
					Activities::addActivity($rs['id'], 0, t('activity-newuser', 'user' ), 'u');
					return true;
				}
			}
		} else {
			if (DEVELOPMENT === true) error_log(sprintf('[DEBUG] <%s:%d> User regcode: INVALID', __FUNCTION__, __LINE__));
			$this->error_message = t('invalid-regcode', 'user');
			$this->logerror(2,0);
			return false;
		}
	}

	/**
	 * Error loggen
	 *
 	 * Speichert ein Fehler des Users in der DB ab.
	 *
	 * @TODO Refactor this functionality & solve this differently. Needs updateing of usersystem::login()
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

		$sql = 'INSERT into error (user_id, do, ip, date)
				VALUES ('.$user_id.', "'.$do_array[$do].'","'.$_SERVER['REMOTE_ADDR'].'", NOW())';
		$db->query($sql, __FILE__, __LINE__, __METHOD__);
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
	 * Überprüft ob der User eingeloggt ist
	 *
	 * @author IneX
	 * @version 2.0
	 * @since 1.0 method added
	 * @since 2.0 14.11.2018 method renamed from "islogged_in" => "is_loggedin"
	 *
	 * @return bool Returns true/false depening on if a successful execution was possible, or not 
	 */
	function is_loggedin()
	{
		if(isset($_SESSION['user_id']) && $_SESSION['user_id']) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Überprüfen ob User ausgesperrt ist
	 *
	 * @author IneX
	 * @version 1.0
	 * @since 1.0 14.11.2018 method added
	 *
	 * @see $_geaechtet
	 * @param integer $ausgesperrt_bis_timestamp Unix-Timestamp for specific date to check lockout against 
	 * @global array $_geaechtet Globales Array mit allen geächteten Usern
	 * @return bool Returns true/false if user is currently locked out, or not 
	 */
	function is_lockedout($ausgesperrt_bis_timestamp)
	{
		global $_geaechtet;

		if (!empty($ausgesperrt_bis_timestamp) && $ausgesperrt_bis_timestamp > 0)
		{
			if ($ausgesperrt_bis_timestamp > time())
			{
				$_geaechtet[] = $_SESSION['user_id'];
				return true;
			}
		} else if (!empty($_geaechtet[$_SESSION['user_id']])) {
				return true;
		} else {
			if ($this->ausgesperrt_bis > time())
			{
				$_geaechtet[] = $_SESSION['user_id'];
				return true;
			}
		}
		return false;
	}

	/**
	 * Passwort-Generator
	 *
	 * Erstellt ein zufälliges Passwort
	 *
	 * @author [z]biko
	 * @author IneX
	 * @version 2.0
	 * @since 1.0 method added
	 * @since 2.0 04.01.2019 updated mechanism and form of generated passwords, not using $username string anymore
	 *
	 * @param $length integer (Optional) specify length of random password to generate, Default: 12
	 * @return string Passwort
	 */
	function password_gen($length=12)
	{
		$charsToUse = 'abcdefghijklmnopqrstuwxyzABCDEFGHIJKLMNOPQRSTUWXYZ01234567890';
		$pass = array();
		$charsLength = strlen($charsToUse)-1;
		for ($i=0; $i<$length; $i++)
		{
			$n = rand(0, $charsLength);
			$pass[] = $charsToUse[$n];
		}
		$randpass = implode($pass); // convert the array to a string
		return $randpass;
	}

	/**
	 * Userpic prüfen
	 *
	 * Überprüft ob ein Bild zum User existiert
	 *
	 * @author [z]biko
	 * @author IneX
	 * @version 2.0
	 * @since 1.0 Method added
	 * @since 2.0 <inex> 11.07.2018 added check for locally cached Gravatar, replaced 'file_exists' with 'stream_resolve_include_path'
	 * @since 3.0 16.07.2018 Method now returns path to userpic (or queried Gravatar result) as string, instead of true.
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
		if (stream_resolve_include_path($user_imgpath_gravatar) !== false) // TODO use fileExists() method from util.inc.php?
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
	 * @since 1.0 Method added
	 * @since 2.0 <inex> Check & load cached Gravatar, optimized if-else
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
	 * @DEPRECATED
	 *
	 * @author IneX
	 * @date 26.12.2017
	 *
	 * @TODO remove this function 'getFormFieldUserlist()' & make sure to remove all references in corresponding files pointing to it
	 */
	function getFormFieldUserlist($name, $size, $users_selected=0, $tabindex=10) {
		global $db;

		/** Wenn User ganz neue Message schreibt */
		if (empty($users_selected) || $users_selected === 0) $users_selected = [];

		/** check and make an Array, if necessary */
		if (!is_array($users_selected)) // Fixes: PHP Warning: strpos() expects parameter 1 to be string, array given
		{
			if (strpos($users_selected, ',') !== false) $users_selected = explode(',', $users_selected);
		}
		/** Remove any duplicate User-IDs */
		$users_selected = array_unique($users_selected);

		$sql =
			'SELECT id, clan_tag, username FROM user'
			.' WHERE UNIX_TIMESTAMP(lastlogin) > (UNIX_TIMESTAMP(NOW())-'.(USER_OLD_AFTER*2).')'
			.' OR z_gremium = "1" OR (vereinsmitglied != "0" AND vereinsmitglied != "")'
			.(!empty($users_selected) ? ' OR id IN ('.implode(',', $users_selected).')' : null)
			.' ORDER BY clan_tag DESC, username ASC'
		;
		$result = $db->query($sql, __FILE__, __LINE__);

		$html = '<select multiple="multiple" name="'.$name.'" size="'.$size.'" tabindex="'.$tabindex.'">';
		$htmlSelectElements = [];
		while ($rs = mysql_fetch_array($result))
		{
			$selectCurrent = (in_array($rs['id'], $users_selected) || $rs['id'] == $users_selected[0] ? 'selected' : false);
			$elementHtml = sprintf('<option value="%d" %s>%s</option>', $rs['id'], $selectCurrent, $rs['clan_tag'].$rs['username']);
			if ($selectCurrent !== false) array_unshift($htmlSelectElements, $elementHtml);
			else array_push($htmlSelectElements, $elementHtml);
		}
		$html .= implode('', $htmlSelectElements);
		$html .= '</select>';

		return $html;
	}

	/**
	 * Convert ID to Username/Userpic
	 *
	 * Konvertiert eine ID zum entsprechenden Username (wahlweise inkl. Clantag oder ohne), oder dem HTML-Code zur Anzeige des Userpics
	 *
	 * @author IneX
	 * @version 5.0
	 * @since 1.0
	 * @since 2.0
	 * @since 3.0 Method now really only resolves an ID to a Username, redirects other features
	 * @since 4.0 changed output to new function usersystem::userprofile_link()
	 * @since 5.0 added better validation for $id & changed return to 'false' if $id doesn't exist
	 *
	 * @TODO 20.07.2018 Find out & fix issue with Query failing on id=$id instead of id="$id"...
	 *
	 * @see usersystem::userprofile_link()
	 * @global	object	$db	Globales Class-Object mit allen MySQL-Methoden
	 * @param integer $id User ID
	 * @param boolean $clantag Username mit Clantag true/false
	 * @param boolean $pic DEPRECATED Anstatt Username das Userpic HTML-Code ausgeben true/false
	 * @return string|boolean Username (mit/ohne Clantag), oder 'false' wenn $id ungültig ist
	 */
	function id2user($id, $clantag=FALSE, $pic=FALSE)
	{
		global $db, $_users;

		/** If given User-ID is not valid (not numeric), show a User Error */
		if (!empty($id) && !is_numeric($id)) {
			user_error(t('invalid-id', 'user'), E_USER_WARNING);
			return false;
		}
		$clantag = (empty($clantag) || $clantag === 'false' || $clantag === 0 ? FALSE : TRUE);

		if (!isset($_users[$id]) || !isset($_users[$id]['clan_tag']))
		{
			if ($clantag === TRUE)
			{
				$sql = 'SELECT clan_tag, username FROM user WHERE id="'.$id.'" LIMIT 0,1'; // ATTENTION: Query fails when $id is not quoted with "$id"!
			} else {
				$sql = 'SELECT username FROM user WHERE id="'.$id.'" LIMIT 0,1';
			}
			$rs = $db->fetch($db->query($sql, __FILE__, __LINE__, __METHOD__));
			if (!empty($rs) || $rs !== false || !empty($rs['username']))
			{
				/** User $id exists - returned a result */
				$_users[$id] = $rs;
			} else {
				/** User $id does NOT exist */
				return false;
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
		 *
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
	 * @version 3.0
	 * @since 1.0 <inex> 24.07.2014
	 * @since 2.0 <inex> 11.01.2017 Fixed Gravatar-URL to https using SITE_PROTOCOL
	 * @since 3.0 <inex> 16.07.2018 Removed possibility to return <img>-Tag
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
	 * @version 1.0
	 * @since 1.0 <inex> 12.07.2018 function added
	 *
	 * @param integer|string $userScope Scope for whom to get the Gravatar image for: a single User-ID integer, or 'all' string for all Useraccounts.
	 * @global object $db Globales Class-Object mit allen MySQL-Methoden
	 * @return bool Returns true/false depening on if a successful execution was possible, or not
	 */
	function cacheGravatarImages($userScope)
	{
		global $db;

		/** Validate passed $userScope variable */
		if (DEVELOPMENT === true) error_log(sprintf('[DEBUG] <%s:%d> $userScope: %s', __METHOD__, __LINE__, $userScope));
		if (empty($userScope)) return false;

		/** Get the Gravatar image for a User or a List of Users */
		switch ($userScope)
		{
			/** (integer)USER: If $userScope = User-ID: try to get the User's Gravatar-Image */
			case is_numeric($userScope) && $userScope > 0:
				if (DEVELOPMENT === true) error_log(sprintf('[DEBUG] <%s:%d> Checking for User-ID: %d', __METHOD__, __LINE__, $userScope));
				if (self::exportGravatarImages([$userScope])) return true;

			/** (array)LIST: If $userScope = User-ID list: try to get Gravatar-Image for all of them */
			case $userScope === 'all':
				if (DEVELOPMENT === true) error_log(sprintf('[DEBUG] <%s:%d> Checking for %s User-IDs', __METHOD__, __LINE__, $userScope));
				$sql = 'SELECT id FROM user WHERE email IS NOT NULL AND email <> "" AND active = 1';
				$userids_list = $db->query($sql, __FILE__, __LINE__, __METHOD__);
				while ($result = mysql_fetch_array($userids_list))
				{
					$userids[] = $result['id'];
				}
				if (self::exportGravatarImages($userids)) return true;
				else return false;

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
	 * @version 2.0
	 * @since 1.0 <inex> 11.07.2018 function added
	 * @since 2.0 <inex> 13.08.2018 added md5 file hash check to compare files before downloading
	 *
	 * @TODO wenn die self::id2useremail() Funktion gefixt ist (nicht nur eine response wenn E-Mail Notifications = true), dann Query ersetzen mit Methode
	 *
	 * @see SITE_PROTOCOL, USER_IMGPATH, USER_IMGSIZE_LARGE, USER_IMGSIZE_SMALL, USER_IMGEXTENSION
	 * @see cURLfetchUrl(), fileHash()
	 * @param array $userid Single or List of User ID(s) as Array
	 * @global object $db Globales Class-Object mit allen MySQL-Methoden
	 * @return bool Returns true/false depening on if a successful execution was possible, or not
	 */
	function exportGravatarImages(array $userids)
	{
		global $db;
		if (DEVELOPMENT) $start = microtime(true); // Start execution time measurement
		if ( empty($userids) || count($userids) <= 0 ) return false;

		$index = 0;
		foreach($userids as $userid)
		{
			/**
			 * Check for a valid user e-mail
			 * @TODO wenn die self::id2useremail() Funktion gefixt ist (nicht nur eine response wenn E-Mail Notifications = true), dann Query ersetzen mit Methode
			 */
			//$useremail = self::id2useremail($userid);
			$queryresult = $db->fetch($db->query('SELECT email FROM user WHERE id = '.$userid.' LIMIT 0,1', __FILE__, __LINE__, __METHOD__));
			$useremail = $queryresult['email'];

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

						/** Check - based on md5-Hash - if local & remote file are identical (if yes: don't queue for $gravatar_request) */
						if (!fileHash($user_imgpath_gravatar, false, $gravatar_request))
						{
							$curl_httpresources[$index++] = [ $gravatar_request, $user_imgpath_gravatar ];
						}
					}

				/** Handle exception */
				} catch (Exception $e) {
					error_log(sprintf('[ERROR] <%s:%d> %s', __METHOD__, __LINE__, $e->getMessage()));
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
			if (DEVELOPMENT) $end = microtime(true) - $start; // Stop execution time measurement
			if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> cURLfetchUrl(): SUCCESS (duration: %s)', __METHOD__, __LINE__, $end));
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

		$e = $db->query('SELECT id, from_mobile FROM user WHERE id='.$id.' LIMIT 1', __FILE__, __LINE__, __METHOD__);
		$d = $db->fetch($e);
		if ($d) return $d['from_mobile'];
		else return '';
	}

	/**
	 * @DEPRECATED
	 * ID zu Mail_Username
	 *
	 * Wandelt eine User ID in IMAP-Mail_Username um
	 *
	 * @param $id int User ID
	 * @return string username
	 */
	function id2mailuser($id)
	{
		global $db;

		$sql = 'SELECT mail_username FROM user WHERE id = '.$id;
		$rs = $db->fetch($db->query($sql, __FILE__, __LINE__, __METHOD__));
		return $rs['mail_username'];
	}

	/**
	 * ID zu User E-Mail
	 *
	 * Gibt aufgrund einer User ID dessen E-Mailadresse zurück.
	 *
	 * @author IneX
	 * @version 4.1
	 * @since 1.0 <inex> 17.03.2018 method added
	 * @since 2.0 added additional check for "email_notification=TRUE"
	 * @since 3.0 updated method return values, added query try-catch
	 * @since 4.0 removed check for "email_notification=TRUE" due to new Notifications() Class
	 * @since 4.1 <inex> 05.12.2019 removed unneccessary try-catch
	 *
	 * @see check_email()
	 * @param int $id User-ID
	 * @return string|bool EMail-Adresse, oder false
	 */
	function id2useremail($id)
	{
		global $db;

		$sql = 'SELECT email FROM user WHERE id = '.$id.' LIMIT 0,1';
		$rs = $db->fetch($db->query($sql, __FILE__, __LINE__, __METHOD__));

		if (!empty($rs['email']) && !is_numeric($rs['email']))
		{
			if (check_email($rs['email']) === true) return $rs['email'];
			else return false;
		} else {
			return false;
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
	 *
	 * @DEPRECATED
	 * @TODO wird diese Methode usersystem::userpagelink() noch benötigt irgendwo? Sonst: raus!
	 *
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
	 * @version 1.0
	 * @since 1.0 <inex> 05.07.2018 initial version (output from Smarty-Template)
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
		$show_pic = (empty($params['pic']) || $params['pic'] === 'false' || $params['pic'] === 0 ? FALSE : TRUE);
		$show_username = (empty($params['username']) || $params['username'] === 'false' || $params['username'] === 0 ? FALSE : TRUE);
		$show_clantag = (empty($params['clantag']) || $params['clantag'] === 'false' || $params['clantag'] === 0 ? FALSE : TRUE);
		$show_link = (empty($params['link']) || $params['link'] === 'false' || $params['link'] === 0 ? FALSE : TRUE);

		$smarty->assign('show_profilepic', ($show_pic ? 'true' : 'false'));
		if ($show_pic) $smarty->assign('profilepic_imgsrc', $this->userImage($userid));
		$smarty->assign('show_username', ($show_username ? 'true' : 'false'));
		if ($show_username) $smarty->assign('username', $this->id2user($userid, $show_clantag, false));
		if ($show_username) $smarty->assign('username_link', $this->id2user($userid, false, false));
		$smarty->assign('show_profile_link', ($show_link ? 'true' : 'false'));

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
		if($user_id != '')
		{
			$sql = 'SELECT count(*) as anzahl FROM quotes WHERE user_id = '.$user_id;
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
	 * @version 1.0
	 * @since 1.0 <inex> 27.01.2016 method added
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
	 * @version 1.0
	 * @since 1.0 <inex> 22.01.2017 method added
	 *
	 * @param integer $user_id User-ID
	 * @return integer The User's Telegram Chat-ID
	 */
	function userHasTelegram($user_id)
	{
		global $db;

		/** Validte $user_id - valid integer & not empty/null */
		if (empty($user_id) || $user_id === NULL || $user_id <= 0) return false;

		$query = $db->query('SELECT telegram_chat_id tci FROM user WHERE id='.$user_id.' LIMIT 1', __FILE__, __LINE__, __METHOD__);
		$result = $db->fetch($query);
		return ( $result ? $result['tci'] : false );
	}

	/**
	 * Password change
	 *
	 * Execute a password change for a User
	 *
	 * @author [z]biko, IneX
	 * @version 3.0
	 * @since 1.0 function added
	 * @since 2.0 03.10.2018 function improved
	 * @since 3.0 11.11.2018 function moved to usersystem()-Class
	 *
	 * @see crypt_pw()
	 * @param integer $user_id User-ID
	 * @param string $old_pass Previous User Password
	 * @param string $new_pass New User Password 
	 * @param string $new_pass2 New User Password repeated (to confirm $new_pass)
	 * @global object $db Globales Class-Object mit allen MySQL-Methoden
	 * @global object $user Globales Class-Object mit den User-Methoden & Variablen
	 * @return array Returns $error-Array with [0]=true/false & [1]=message(s)
	 */
	function exec_newpassword($user_id, $old_pass, $new_pass, $new_pass2)
	{
		global $db;

		$error[0] = FALSE;

		if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d>', __METHOD__, __LINE__));
		if(!empty($old_pass) && !empty($new_pass) && !empty($new_pass2))
		{
			/** Hash $old_pass */
			$crypted_old_pass = crypt_pw($old_pass);

			/** Check Hash of $old_pw against saved Hash */
			if($crypted_old_pass === $this->userpw)
			{
				/** Check $new_pass was entered twice & identical */
				if($new_pass === $new_pass2)
				{
					/** Hash $new_pass for storing in DB */
					$crypted_new_pass = crypt_pw($new_pass);
					$result = $db->update('user', ['id', $_SESSION['user_id']], ['userpw' => $crypted_new_pass], __FILE__, __LINE__, __METHOD__);
					if ($result === 0 || !$result) {
						$error[0] = TRUE;
						$error[1] = t('error-userpw-update', 'user');
					} else {
						$error[0] = FALSE;
						$error[1] = t('new-userpw-confirmation', 'user');
					}
				} else {
					$error[0] = TRUE;
					$error[1] = t('invalid-userpw-match', 'user');
				}
			} else {
				$error[0] = TRUE;
				$error[1] = t('invalid-userpw-old', 'user');
			}
		} else {
			$error[0] = TRUE;
			$error[1] = t('invalid-userpw-missing', 'user');
		}
		return $error;
	}

	/**
	 * Userprofil aktualisieren
	 *
	 * Execute a Profile info & settings update for a User
	 *
	 * @author [z]biko, IneX
	 * @version 3.0
	 * @since 1.0 function added
	 * @since 2.0 02.10.2018 function improved to handle $_POST data dynamically
	 * @since 3.0 11.11.2018 function moved to usersystem()-Class
	 *
	 * @see check_email(), $_geaechtet
	 * @param integer $user_id User-ID
	 * @param array $data_array Userprofile Infos in einem assoziativen Array, mit denen das Profil aktualisiert wird ($_POST aus dem Form)
	 * @global object $db Globales Class-Object mit allen MySQL-Methoden
	 * @global object $user Globales Class-Object mit den User-Methoden & Variablen
	 * @global array $_geaechtet Globales Array mit allen User-IDs welche einen Force-Logout haben
	 * @return array Returns $error-Array with [0]=true/false & [1]=message(s)
	 */
	function exec_changeprofile($user_id, $data_array)
	{
		global $db, $_geaechtet;

		/** Validate passed parameters */
		if (empty($user_id) || $user_id === NULL || $user_id <= 0) return false;
		if (!is_array($data_array) || count($data_array) <= 1) return false;
		if (isset($data_array['send'])) unset($data_array['send']); // clear 'send'
		if (isset($data_array['do'])) unset($data_array['do']); // clear 'do'
		$error[0] = FALSE;

		/** Check e-mail address & dass User nicht einen Force-Logout hat */
		if (check_email($data_array['email']) && !$_geaechtet[$user_id])
		{
			/** Process $data_array values */
			foreach ($data_array as $dataKey => $dataValue)
			{
				/** Check & set default parameters */
				if (!is_array($dataValue))
				{
					/**
					 * Fancy shit incoming:
					 * We're building a dynamic variable using ${string}
					 * refering to any $user->default_var from usersystem()
					 */
					$defaultValue = ${'$this->default_'.strtolower($dataKey)};
					if (empty($dataValue) && $dataValue !== $defaultValue)
					{
						/**
						 * if $data_array[param](value) is empty & not identical to dynamic $defaultValue
						 * then set $data_array[param](value) => value of $defaultValue
						 */
						$data_array[$dataKey] = (empty($defaultValue) || $defaultValue === null ? null : strval($defaultValue));
					}
				}

				/** Prepare SQL-Update "SET row=value"-Array */
				$sqlUpdateSetValuesArray[$dataKey] = (!is_array($dataValue) ? sanitize_userinput($dataValue) : $dataValue);
			}

			/**
			 * Process regular Form-Checkbox values
			 */
			$data_array_checkbox_count = count($data_array['checkbox']);
			if (is_array($data_array['checkbox']) && $data_array_checkbox_count > 0) {
				for ($i=0;$i<$data_array_checkbox_count;$i++)
				{
					if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> $data_array[checkbox]: <%d> %s => %s', __METHOD__, __LINE__, $data_array['checkbox'][$i], array_keys($data_array['checkbox'])[$i], array_values($data_array['checkbox'])[$i]));
					if (array_values($data_array['checkbox'])[$i] === true || array_values($data_array['checkbox'])[$i] === 'true' || array_values($data_array['checkbox'])[$i] === '1' || array_values($data_array['checkbox'])[$i] === 1) {
						if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> $sqlUpdateSetValuesArray adding: %s => 1', __METHOD__, __LINE__, array_keys($data_array['checkbox'])[$i]));
						$sqlUpdateSetValuesArray[array_keys($data_array['checkbox'])[$i]] = '1';
					} else {
						if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> $sqlUpdateSetValuesArray adding: %s => 1', __METHOD__, __LINE__, array_keys($data_array['checkbox'])[$i]));
						$sqlUpdateSetValuesArray[array_keys($data_array['checkbox'])[$i]] = '0';
					}
				}
			}
			if (isset($sqlUpdateSetValuesArray['checkbox'])) unset($sqlUpdateSetValuesArray['checkbox']);

			/**
			 * Process Form-Checkbox-Array values with JSON conversion
			 */
			if (is_array($data_array['notifications']) && count($data_array['notifications']) > 0) {
				$sqlUpdateSetValuesArray['notifications'] = sanitize_userinput(json_encode($data_array['notifications']));
			} elseif (!isset($data_array['notifications']) || empty($data_array['notifications'])) {
				$sqlUpdateSetValuesArray['notifications'] = NULL; // no change
			}
			if (is_array($data_array['forum_boards_unread']) && count($data_array['forum_boards_unread']) > 0) {
				$sqlUpdateSetValuesArray['forum_boards_unread'] = sanitize_userinput(json_encode($data_array['forum_boards_unread']));
			} elseif (!isset($data_array['forum_boards_unread']) || empty($data_array['forum_boards_unread'])) {
				$sqlUpdateSetValuesArray['forum_boards_unread'] = escape_text($this->default_forum_boards_unread); // no change
			}

			if (count($sqlUpdateSetValuesArray) > 0)
			{
				if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> $sqlUpdateSetValuesArray: %s', __METHOD__, __LINE__, print_r($sqlUpdateSetValuesArray,true)));
				$result = $db->update('user', ['id', $_SESSION['user_id']], $sqlUpdateSetValuesArray, __FILE__, __LINE__, __METHOD__);
				if ($result === 0 || !$result) {
					$error[0] = TRUE;
					$error[1] = t('error-userprofile-nochange', 'user');
				}
			}
		} else {
			$error[0] = TRUE;
			$error[1] = t('invalid-email', 'user');
		}

		return $error;
	}

	/**
	 * Userpic hochladen
	 *
	 * @TODO move this function to the usersystem()-Class
	 *
	 * @author [z]biko, IneX
	 * @version 4.0
	 * @since 1.0 function added
	 * @since 2.0 Userpic Archivierung eingebaut / IneX
	 * @since 3.0 03.10.2018 function fixed and modernized
	 * @since 4.0 11.11.2018 function moved to usersystem()-Class
	 *
	 * @see createPic()
	 * @param integer $user_id User-ID
	 * @param array|resource $new_pic_files_array $_FILES[] Array/-resource mit hochgeladenem Userpic & allen File-Infos
	 * @global object $db Globales Class-Object mit allen MySQL-Methoden
	 * @return array Returns $error-Array with [0]=true/false & [1]=message(s)
	 */
	function exec_uploadimage($user_id, $new_pic_files_array)
	{
		global $db;

		$error[0] = FALSE;

		/** Validate passed parameters & $_FILES-array */
		if (empty($user_id) || $user_id === NULL || $user_id <= 0) return false;
		if (!is_array($new_pic_files_array) || is_numeric($new_pic_files_array)) return false;
		if (!$new_pic_files_array['image']['name']) {
			if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> Bildupload Data Error: %s', __METHOD__, __LINE__, $new_pic_files_array['image']['name']));
			$error[0] = TRUE;
			$error[1] = t('error-userpic-name', 'user');
			return $error;
		}
		if($new_pic_files_array['image']['error'] != 0) {
			if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> Bildupload Error: %d', __METHOD__, __LINE__, $new_pic_files_array['image']['error']));
			$error[0] = TRUE;
			$error[1] = t('error-userpic-upload', 'user');
			return $error;
		}
		if ($new_pic_files_array['image']['type'] != 'image/jpeg' && $new_pic_files_array['image']['type'] != 'image/pjpeg') {
			 if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> kein JPEG Bild: %s', __METHOD__, __LINE__, $new_pic_files_array['image']['type']));
			 $error[0] = TRUE;
			 $error[1] = t('invalid-userpic-format', 'user');
			 return $error;
		}

		/** Zuerst altes Bild archivieren... */
		$currtimestamp = time();
		$oldfilename = $user_id.USER_IMGEXTENSION;
		$oldfile = USER_IMGPATH.$user_id.USER_IMGEXTENSION;
		$oldfile_tn = USER_IMGPATH.$user_id.'_tn'.USER_IMGEXTENSION;
		$archiv = USER_IMGPATH_ARCHIVE.$user_id.'_'.$currtimestamp.USER_IMGEXTENSION; // (mit timestamp versehen, damits keine pics Ÿberschreibt
		$archiv_tn = USER_IMGPATH_ARCHIVE.$user_id.'_'.$currtimestamp.'_tn'.USER_IMGEXTENSION; // (mit timestamp versehen, damits keine pics Ÿberschreibt

		/** ...aber nur wenn bereits ein Pic raufgeladen wurde (und nicht das Standartpic gesetzt ist) */
		if (file_exists($oldfile)) {
			if (!copy($oldfile, $archiv)) { // zuerst das grosse...
				$error[0] = TRUE;
				$error[1] = t('error-userpic-archive', 'user');
				error_log(sprintf('[ERROR] <%s:%d> %s. $oldfile: %s => $archiv: %s', __METHOD__, __LINE__, $error[1], $oldfile, $archiv));
				return $error;
			}
			if (!copy($oldfile_tn, $archiv_tn)) { // ...und dann noch das kleine
				$error[0] = TRUE;
				$error[1] = t('error-userpictn-archive', 'user');
				error_log(sprintf('[ERROR] <%s:%d> %s. $oldfile: %s => $archiv: %s', __METHOD__, __LINE__, $error[1], $oldfile_tn, $archiv_tn));
				return $error;
			}
			/** @TODO DEAKTIVIERT WEIL ZUVOR NOCH ALLE PHP-FILES ÜBERPRÜFT WERDEN MÜSSEN, OB DA NOCH WAS BEZÜGLICH USERPICS DRIN IST, WEGEN DER NEUEN NAMENGEBUNG! */
			/*$sql = "SELECT * FROM userpics
				WHERE user_id = $user_id AND image_name = '".$oldfilename."'";
			if ($db->query($sql, __FILE__, __LINE__)) {
				$sql = "UPDATE userpics
					SET image_replaced = $currtimestamp
					WHERE user_id = $user_id AND image_name = '".$oldfilename."'
					";
				$db->query($sql, __FILE__, __LINE__);
			} else {
				$sql = "INSERT INTO userpics
					(user_id, image_name, image_title, image_added, image_replaced)
					VALUES
					($user_id, $oldfilename, $oldfilename, NOW(), NOW())
					";
				$db->query($sql, __FILE__, __LINE__);
			}*/
		}

		/** ...danach das Neue raufladen */
		$tmpfile = APOD_TEMP_IMGPATH.$user_id.USER_IMGEXTENSION;
		if (!move_uploaded_file($new_pic_files_array['image']['tmp_name'], $tmpfile)) {
			$error[0] = TRUE;
			$error[1] = t('error-userpic-permissions', 'user');
			error_log(sprintf('[ERROR] <%s:%d> %s. tmp_name: %s => $tmpfile: %s', __METHOD__, __LINE__, $error[1], $new_pic_files_array['image']['tmp_name'], $tmpfile));
			return $error;
		} else {
			/** db-gschmäus machen */
			$sql = 'INSERT INTO userpics
						(user_id, image_name, image_title, image_added)
					VALUES
						('.$user_id.', "'.$new_pic_files_array['image']['name'].'", "'.$new_pic_files_array['image']['name'].'", NOW())';
			$db->query($sql, __FILE__, __LINE__, __METHOD__);
		}

		$userpicThumb = createPic($tmpfile, USER_IMGPATH.$user_id.'_tn'.USER_IMGEXTENSION, USER_IMGSIZE_SMALL, USER_IMGSIZE_SMALL, array(0,0,0));
		if (!$userpicThumb || $userpicThumb['error']) {
			$error[0] = TRUE;
			$error[1] = $userpicThumb['error'];
			error_log(sprintf('[ERROR] <%s:%d> %s. $tmpfile: %s => %s', __METHOD__, __LINE__, $userpicThumb['error'], $tmpfile, USER_IMGPATH.$user_id.'_tn'.USER_IMGEXTENSION));
			return $error;
		}

		$userpicLarge = createPic($tmpfile, USER_IMGPATH.$user_id.USER_IMGEXTENSION, USER_IMGSIZE_LARGE, USER_IMGSIZE_LARGE);
		if (!$userpicLarge || $userpicLarge['error']) {
			$error[0] = TRUE;
			$error[1] = $userpicLarge['error'];
			error_log(sprintf('[ERROR] <%s:%d> %s. $tmpfile: %s => %s', __METHOD__, __LINE__, $userpicLarge['error'], $tmpfile, USER_IMGPATH.$user_id.USER_IMGEXTENSION));
			return $error;
		}

		/** $tmpfile löschen */
		if (unlink($tmpfile) === false)
		{
			error_log(sprintf('[WARN] <%s:%d> unlink($tmpfile) FAILED: %s', __METHOD__, __LINE__, $tmpfile));
		}

		return $error;
	}

	/**
	 * User aussperren bis zu einem gewissen Zeitpunkt
	 *
	 * @author IneX
	 * @version 1.0
	 * @since 1.0 11.11.2018 method added, code adapted from /actions/profil.php
	 *
	 * @see $_geaechtet
	 * @see usersystem::logout()
	 * @see /actions/profil.php
	 * @param integer $user_id User-ID
	 * @param array $date_array Array mit Datum-Elementen bis wann User ausgesperrt werden soll ('year' => xxxx, 'month' => xxxx,...)
	 * @global object $db Globales Class-Object mit allen MySQL-Methoden
	 * @global array $_geaechtet Globales Array mit allen geächteten Usern
	 * @return bool Returns true/false depening on if a successful execution was possible, or not
	 */
	function exec_aussperren($user_id, $date_array)
	{
		global $db, $_geaechtet;

		/** Validate passed parameters */
		if (empty($user_id) || $user_id === NULL || $user_id <= 0) return false;
		if (empty($date_array) || !is_array($date_array) || is_numeric($date_array) || count($date_array) < 3) return false;

		/** Validate $date_array entries */
		if (!isset($date_array['year']) || $date_array['year'] <= date_format(date_create(time()), '%Y')) user_error(t('error-lockout-date', 'user', $date_array['year']));
		if (!isset($date_array['month']) || $date_array['month'] <= 0 || $date_array['month'] > 12) user_error(t('error-lockout-date', 'user', $date_array['month']));
		if (!isset($date_array['day']) || $date_array['day'] <= 0 || $date_array['day'] > 31) user_error(t('error-lockout-date', 'user', $date_array['day']));
		if (!isset($date_array['hour']) || $date_array['hour'] <= 0 || $date_array['hour'] > 23) $date_array['hour'] = 0;
		if (!isset($date_array['minute']) || $date_array['minute'] <= 0 || $date_array['minute'] > 60) $date_array['minute'] = 5;
		if (!isset($date_array['second']) || $date_array['second'] <= 0 || $date_array['second'] > 60) $date_array['second'] = 23;

		/** Format Lockout date-time. Format: Y-m-d H:i:s */
		$lockout_jahr = $date_array['year'];
		$lockout_monat = $date_array['month'];
		$lockout_tag = $date_array['day'];
		$lockout_stunde = $date_array['hour'];
		$lockout_minute = $date_array['minute'];
		$lockout_sekunde = $date_array['second'];
		$lockout_date = sprintf('%d-%d-%d %d:%d:%d', $lockout_jahr, $lockout_monat, $lockout_tag, $lockout_stunde, $lockout_minute, $lockout_sekunde);
		if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> $lockout_date: %s', __METHOD__, __LINE__, $lockout_date));

		/** User aussperren */
		$result = $db->update($this->table_name, ['id', $user_id], [$this->field_ausgesperrt_bis => $lockout_date], __FILE__, __LINE__, __METHOD__);
		if ($result === 0 || !$result)
		{
			if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> User ausgesperrt: %s', __METHOD__, __LINE__, ($result?'true':'false')));
			$_geaechtet[] = $user_id;
			if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> Liste ausgesperrter User: %s', __METHOD__, __LINE__, print_r($_geaechtet,true)));
			return true;
		} else {
			return false;
		}
	}
}

/** Static $_users Array to load & keep feteched Userdata while processing */
static $_users = array();

/** Ausgesperrte User werden geächtet! */
static $_geaechtet = array();

/** Instantiate a new usersystem Class */
//$user = new usersystem();

/**
 * LOGOUT
 * Fun fact: wenn der NACH dem Login-Check kommt, dann wird man wieder eingeloggt...
 * ...weil dann die Cookies & Session noch nicht gekillt wurden ;)
 */
if (isset($_POST['logout']))
{
	/** exec the User logout */
	if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> exec User logout', '$_POST[logout]', __LINE__));
	usersystem::logout();
} else {
	/** Instantiate a new usersystem Class */
	if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> Instantiate a new usersystem Class', '$_POST[logout]', __LINE__));
	$user = new usersystem();
}

/**
 * LOGIN mit Cookie (autologin)
 */
if (!isset($_POST['logout']) && isset($_COOKIE[ZORG_COOKIE_USERID]) && empty($_SESSION['user_id']))
{
	if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> exec User login (Cookie)', '$user->login()', __LINE__));
	$login_error = $user->login($_COOKIE[ZORG_COOKIE_USERID], null, true);
}

/**
 * LOGIN mit Login-Formular
 */
if (isset($_POST['do']) && $_POST['do'] === 'login')
{
	if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> exec User login (Form)', '$user->login()', __LINE__));
	if (!empty($_POST['username']) && !empty($_POST['password']))
	{
		$_POST['cookie'] ? $auto = TRUE : $auto = FALSE;
		$login_error = $user->login($_POST['username'], $_POST['password'], $auto);
	} else {
		$login_error = t('authentication-failed', 'user');
	}
}
