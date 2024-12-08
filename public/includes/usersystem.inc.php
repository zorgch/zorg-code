<?php
/**
 * zorg Usersystem
 *
 * Enthält alle User Funktionen von zorg
 *
 * @since 1.0 `[z]cylander` File added
 * @package zorg\Usersystem
 */
/**
 * File includes
 * @include	config.inc.php 		Include required global site configurations
 * @include mysql.inc.php 		MySQL-DB Connection and Functions --> already included via config.inc.php
 * @include	activities.inc.php	Activities Functions and Stream
 */
require_once __DIR__.'/config.inc.php';
//require_once INCLUDES_DIR.'mysql.inc.php';
require_once INCLUDES_DIR.'activities.inc.php';

/**
 * Usersystem Klasse
 *
 * User Typen (verfügbar über $user->typ):
 * - 0 = USER_ALLE nicht eingeloggt ##################### Aber Weber: wenn typ = 2, gits $user jo gar nöd?! -> doch s'usersystem isch jo immer verfügbar
 * - 1 = USER_USER Normaler User ##################### 0 isch nöd so cool wil wenns nöd gsetzt isch chunt jo au 0
 * - 2 = USER_MEMBER [z]member und schöne
 * - 3 = USER_SPECIAL Admins & Coder
 *
 * @package zorg\Usersystem
 * @version 8.0
 * @since 1.0 `[z]cylander` class added
 * @since 2.0 `IneX` additional methods added
 * @since 3.0 `IneX` code optimizations and new methods
 * @since 4.0 `10.12.2018` `IneX` major refactorings & migrated methods from profil.php as part of the usersystem()-class
 * @since 5.0 `26.12.2018` `IneX` Bug #769: 'usertyp'-Spalte entspricht neu einer Usergruppe aus dem Table 'usergroups' (quasi als Foreign-Key)
 * @since 6.0 `10.04.2021` `IneX` Upgrade to new password_hash() & password_verify(), major rework of User Session & Cookies handling
 * @since 7.0 `14.05.2021` `IneX` Deactivated Session ID for all (incl. Guest) Users, only for Authenticated now. Proper z-Session Cookie lifetime handling.
 * @since 8.0 `23.12.2023` `IneX` Changed Class Properties definitions to modern PHP
 */
class usersystem
{
	/**
	 * Usersystem Configs
	 *
	 * @var bool $enable_cookies Auto-einloggen mit Cookie aktivieren
	 * @var bool $use_current_login Wird benötigt um nicht gesichteten content hervorzuheben
	 * @var bool $use_registration_code Wird benötigt um ein Account von einem User zweifelsfrei aufzuschalten
	 * @var bool $use_online_list Unterstützung einer "wer-ist-alles-online-liste"
	 * @var bool $use_user_picture Jeder User kann ein Bild von sich hochladen
	 * @var string $table_name DB-Table wo die User-Daten gespeichert sind, wird für die SQL-Queries benötigt
	 */
	public $enable_cookies = TRUE;
	public $use_current_login = TRUE;
	public $use_registration_code = TRUE;
	public $use_online_list = TRUE;
	public $use_user_picture = TRUE;
	public $table_name = 'user';

	/**
	 * User values
	 */
	public $id; // Stores the User's ID
    public $activities_allow; // Speichert ob Aktivitäten erlaubt sind (Boolean)
    public $activity; // Speichert den Timestamp der letzten Aktivität des Users
    public $addle; // Speichert ein Boolean ob user addle spielen will
	public $ausgesperrt_bis; // Speichert bis wann der Benutzer ausgesperrt ist (Timestamp)
    public $chess; // Speichert ob der Benutzer Schach spielt (Boolean)
    public $clantag; // Speichert den Clan Tag des Users
    public $currentlogin; // Speichert den aktuellen login als Timestamp
    public $email; // Speichert die Benutzer E-Mail Adresse
    public $firstname; // Speichert den Vornamen des Benutzers
    public $forum_boards; // Speichert die Forenboards des Benutzers (Array)
    public $forum_boards_unread; // Speichert die ungelesenen Forenboards des Benutzers (Array)
    public $from_mobile; // Speichert ob der Benutzer von einem Mobilen Gerät zugreift
    public $image; // Speichert den Link zum Bild des Benutzer
    public $irc; // Speichert die IRC-Information des Benutzers
    public $last_ip; // Speichert die letzte IP-Adresse des Benutzers
    public $lastlogin; // Speichert den letzen Login als Timestamp
    public $lastname; // Speichert den Nachnamen des Benutzers
    public $maxdepth; // Speichert die Forumanzeigeschwelle des Benutzers
    public $menulayout; // Speichert das präferierte Menu Layout das der User eingestellt hat
    public $mymenu; // Speichert das individuelle Menü des Benutzers
    public $notifications; // Speichert die Benachrichtigungen des Benutzers (Array)
    public $password; // Speichert das User Passwort
    public $show_comments; // Speichert ob der Benutzer die Comments sehen will (=1) oder nicht (=0)
    public $sql_tracker; // Speichert ob SQL-Tracking aktiviert ist (Boolean)
    public $telegram; // Speichert die Telegram-Information des Benutzers
    public $typ; // Speichert den Benutzer Typ / Rolle
    public $username; // Speichert den Benutzername (ohne Clan Tag)
	public $userpw; // Speichert das Passwort des Benutzers
    public $vereinsmitglied; // Speichert den Vereinsmitglied-Status des User
    public $z_gremium; // Speichert den Z-Gremium-Status des Benutzers
    public $zorger; // Speichert ob der User zooomclan.org (retro) oder zorg.ch (modern) sehen will

	/**
	 * Map User Fields to User DB Table Rows
	 */
	protected $field_userid = 'id';
	protected $field_activities_allow = 'activities_allow';
	protected $field_activity = 'activity';
	protected $field_addle = 'addle';
	protected $field_ausgesperrt_bis = 'ausgesperrt_bis';
	protected $field_bild = 'image';
	protected $field_chess = 'chess';
	protected $field_clantag = 'clan_tag';
	protected $field_currentlogin = 'currentlogin';
	protected $field_email = 'email';
	protected $field_from_mobile = 'from_mobile';
	protected $field_irc = 'irc_username';
	//protected $field_last_ip = 'last_ip'; // @DEPRECATED
	protected $sessionkey_last_ip = 'last_ip';
	protected $field_lastlogin = 'lastlogin';
	protected $field_maxdepth = 'forummaxthread';
	protected $field_menulayout = 'menulayout';
	protected $field_mymenu = 'mymenu';
	protected $field_notifications = 'notifications';
	protected $field_regcode = 'regcode';
	protected $field_regdate = 'regdate';
	protected $field_show_comments = 'show_comments';
	protected $field_sql_tracker = 'sql_tracker';
	protected $field_telegram = 'telegram_chat_id';
	protected $field_user_active = 'active';
	protected $field_username = 'username';
	protected $field_userpw = 'userpw';
	protected $field_usertyp = 'usertype';
	protected $field_zorger = 'zorger';
	protected $field_z_gremium = 'z_gremium';
	protected $field_vereinsmitglied = 'vereinsmitglied';
	protected $field_firstname = 'firstname';
	protected $field_lastname = 'lastname';

	/**
	 * Default Userprofile Settings
	 *
	 * @used-by usersystem::exec_changeprofile()
	 */
	public $default_clan_tag = null; // none
	public $default_activities_allow = '1'; // enabled
	public $default_telegram_chat_id = null; // none
	public $default_irc_username = null; // none
	public $default_addle = '1'; // enabled
	public $default_chess = '1'; // enabled
	public $default_forum_boards = '["b","e","f","o","r","t"]'; // Bugtracker, Events, Forum, Go, Tauschbörse, Templates
	public $default_forum_boards_unread = '["b","e","f","g","h","i","o","t"]'; // Bugtracker, Events, Forum, Hunting z, Gallery, Tauschbörse, Templates
	public $default_forummaxthread = 10; // depth: 10
	public $default_menulayout = ''; // none (String, because ENUM='')
	public $default_mymenu = null; // none
	public $default_notifications = '{"bugtracker":{"message":"true","email":"true"},"games":{"email":"true"},"mentions":{"email":"true"},"messagesystem":{"email":"true"},"subscriptions":{"message":"true"}}';
	public $default_show_comments = '1'; // enabled
	public $default_sql_tracker = '0'; // disabled
	public $default_usertype = 0; // nicht-eingeloggt
	public $default_zorger = '0'; // zorg-Layout
	public $default_vereinsmitglied = '0'; // kein Mitglied
	public $default_z_gremium = ''; // no
	public $default_firstname = null; // none
	public $default_lastname = null; // none

	/**
	 * Object Vars
	 *
	 * @var string (Optional) Error-Message, see: usersystem::activate_user()
	 */
	public $error_message;

	/**
	 * Klassen Konstruktor
	 *
	 * @author [z]biko
	 * @author IneX
	 * @version 6.1
	 * @since 1.0 method added
	 * @since 2.0 `20.11.2018` `IneX` code & query optimizations, updated Cookie & Session info taken from config.inc.php
	 * @since 3.0 `27.11.2018` `IneX` refactored User-Object instantiation if $_SESSION[user_id] is missing but Session-Cookie is there
	 * @since 4.0 `10.12.2018` `IneX` adjusted reading the Autologin-Cookies (cannot be dependent on the Session-Cookie, doh!)
	 * @since 4.1 `02.11.2019` `IneX` fixed ENUM("0")-Values from User DB-Record wrongfully set=true instead of =false
	 * @since 5.0 `09.04.2021` `IneX` reworked Session and Cookie handling
	 * @since 6.0 `14.05.2021` `IneX` no longer creating a Session by default - only if user is authenticated!
	 * @since 6.1 `03.12.2021` `IneX` Deprecated to store & retrieve the `last_ip` (User IP address) on the Database
	 *
	 * @uses ZORG_SESSION_ID, ZORG_COOKIE_SESSION, ZORG_COOKIE_USERID, ZORG_COOKIE_USERPW, ZORG_SESSION_LIFETIME, ZORG_COOKIE_SECURE
	 * @uses usersystem::login(), usersystem::userImage(), timestamp()
	 * @return object usersystem()-Class object
	 */
	function __construct()
	{
		global $db;

		/** Grundsätzlich mal jeden zuerst als "Gast" anschauen */
		$this->typ = USER_ALLE;
		session_name(ZORG_SESSION_ID); // FIXME Cannot change session name when session is active

		/** DEACTIVATED: Generelle Session Settings & Session (re-)Starten (wenn noch nicht aktiv) */
		// if (session_status() === PHP_SESSION_NONE)
		// {
		// 	session_name(ZORG_SESSION_ID);
		// 	session_start();
		// }

		/**
		 * User Session (re-)starten
		 */
		if (session_status() === PHP_SESSION_NONE && isset($_COOKIE[ZORG_COOKIE_SESSION]))
		{
			/** Session init'en */
			session_start();
			zorgDebugger::log()->debug('Existing Session restarted');

			/** $_SESSION[user_id] not yet available -> if not on forced Login / Logout try to Autologin */
			if (!isset($_SESSION['user_id']) && !isset($_POST['username']) && !isset($_POST['logout']))
			{
				/** We got Cookies --> Autologin! */
				zorgDebugger::log()->debug('$_SESSION[user_id] missing & no login/logout...');
				if (!empty($_COOKIE[ZORG_COOKIE_USERID]) && !empty($_COOKIE[ZORG_COOKIE_USERPW]))
				{
					$cookie_username = filter_input(INPUT_COOKIE, ZORG_COOKIE_USERID, FILTER_UNSAFE_RAW);
					zorgDebugger::log()->debug('Autologin-Cookies existieren -> Login-Passthrough');
					$this->login($cookie_username); // Do NOT send $_COOKIE[ZORG_COOKIE_USERPW] here - because it only contains the PW-Hash!
				}
			}
		}

		/**
		 * User Session konfigurieren.
		 * Wenn bereits usersystem::login() erfolgreich triggered wurde,
		 * dann wurde nach session_start() die User-ID in die Session geschrieben
		 */
		if (session_status() === PHP_SESSION_ACTIVE &&
			isset($_SESSION['user_id']) && !empty($_SESSION['user_id']) && $_SESSION['user_id'] > 0)
		{
			/** Query User Infos in der DB */
			$session_userid = filter_var($_SESSION['user_id'], FILTER_VALIDATE_INT);
			zorgDebugger::log()->debug('Session re-started inkl. $_SESSION[user_id]');
			$sql = 'SELECT *,
						UNIX_TIMESTAMP('.$this->field_activity.') as '.$this->field_activity.',
						UNIX_TIMESTAMP('.$this->field_lastlogin.') as '.$this->field_lastlogin.',
						UNIX_TIMESTAMP('.$this->field_currentlogin.') as '.$this->field_currentlogin.'
					FROM '.$this->table_name.' WHERE id=?';
			$result = $db->query($sql, __FILE__, __LINE__, __METHOD__, [$session_userid]);
			$rs = $db->fetch($result);

			if (!empty($rs) && $rs !== false)
			{
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
				 * @var string $menulayout welches menu layout der user eingestellt hat.
				 * @var string $password User passwort
				 * @var string $show_comments Ob die Comments auf den smarty-pages angezeigt werden sollen (=1) oder nicht (=0)
				 * @var integer $typ Benutzer typ
				 * @var string $username Benutzername (ohne Clan Tag)
				 * @var string $zorger hat der user zooomclan.org (retro) gewählt? sonst zorg.ch (modern) anzeigen
				 * @var string $vereinsmitglied Vereinsmitglied-Status des user
				 */
				$this->id = intval($_SESSION['user_id']);
				$this->email = $rs[$this->field_email];
				$this->username = $rs[$this->field_username];
				$this->clantag = $rs[$this->field_clantag];
				$this->userpw = $rs[$this->field_userpw];
				$this->typ = (!empty($rs[$this->field_usertyp]) ? $rs[$this->field_usertyp] : USER_ALLE);
				$this->image = $this->userImage($this->id);
				$this->telegram = ($rs[$this->field_telegram] === '0' ? null : $rs[$this->field_telegram]);
				$this->irc = $rs[$this->field_irc];
				$this->activity = $rs[$this->field_activity];
				$this->lastlogin = $rs[$this->field_lastlogin];
				$this->currentlogin = $rs[$this->field_currentlogin];
				$this->ausgesperrt_bis = $rs[$this->field_ausgesperrt_bis];
				if ($this->ausgesperrt_bis > time()) $_geaechtet[] = $this->id;
				//$this->last_ip = $rs[$this->field_last_ip]; // @DEPRECATED
				$this->last_ip = (isset($_SESSION[$this->sessionkey_last_ip]) && !empty($_SESSION[$this->sessionkey_last_ip]) ? $_SESSION[$this->sessionkey_last_ip] : null);
				$this->activities_allow = ($rs[$this->field_activities_allow] === '0' ? false : true);
				$this->show_comments = ($rs[$this->field_show_comments] === '0' ? false : true);
				$this->notifications = json_decode( (!empty($rs[$this->field_notifications]) ? stripslashes($rs[$this->field_notifications]) : $this->default_notifications), true); // JSON-Decode to Array
				$this->sql_tracker = ($rs[$this->field_sql_tracker] === '0' ? false : true);
				$this->addle = ($rs[$this->field_addle] === '0' ? false : true);
				$this->chess = ($rs[$this->field_chess] === '0' ? false : true);
				$this->forum_boards = json_decode($rs['forum_boards'], true);//explode(',', $rs['forum_boards']);
				$this->forum_boards_unread = json_decode(stripslashes($rs['forum_boards_unread']), true);//explode(',', $rs['forum_boards_unread']);
				$this->maxdepth = ($rs[$this->field_maxdepth] ? $rs[$this->field_maxdepth] : $this->maxdepth = DEFAULT_MAXDEPTH);
				$this->menulayout = $rs[$this->field_menulayout];
				$this->mymenu = $rs[$this->field_mymenu];
				$this->zorger = ($rs[$this->field_zorger] === '0' ? false : true);
				$this->vereinsmitglied = $rs[$this->field_vereinsmitglied];
				$this->z_gremium = $rs[$this->field_z_gremium]; // ENUM('','z')

				/** Nur für Vereinsmitglieder */
				if (!empty($this->vereinsmitglied)) $this->firstname = $rs[$this->field_firstname];
				if (!empty($this->vereinsmitglied)) $this->lastname = $rs[$this->field_lastname];

				/**
				 * Mobile User Agent abfragen & speichern
				 * wenn nicht Mobile, dann in der DB '' (leer) und im $user Object 'false'
				 */
				$userMobileClientAgent = isMobileClient($_SERVER['HTTP_USER_AGENT']);
				$this->from_mobile = (!empty($userMobileClientAgent) ? reset($userMobileClientAgent) : false );

				zorgDebugger::log()->debug('$user->lastlogin: %s', [strval($this->lastlogin)]);
				zorgDebugger::log()->debug('$user->currentlogin: %s', [strval($this->currentlogin)]);
				zorgDebugger::log()->debug('$user->from_mobile: %s => %s', [$_SERVER['HTTP_USER_AGENT'], ($this->from_mobile ? $this->from_mobile : 'false')]);

				/**
				 * Update last user activity
				 * @TODO Activity nur updaten wenn vorherige & aktuelle Page-URL (z.B. Referrer vs. ...) nicht identisch sind?
				 */
				$db->update($this->table_name, ['id', $this->id],
				[
					 $this->field_activity => timestamp(true)
					,$this->field_from_mobile => (!$this->from_mobile ? null : (string)$this->from_mobile), // because 'ENUM'-fieldtype
				], __FILE__, __LINE__, __METHOD__);
			}
		}
	}

	/**
	 * User Login
	 *
	 * Erstellt eine Session (login)
	 *
	 * @author [z]cylander
	 * @author IneX
	 * @version 5.2
	 * @since 1.0 `cylander` method added
	 * @since 2.0 `12.11.2018` `IneX` code & query optimizations
	 * @since 3.0 `21.11.2018` `IneX` Fixed redirect bei Login auf jeweils aktuelle Seite, nicht immer Home
	 * @since 4.0 `10.12.2018` `IneX` Improved Cookie-Settings (secure and stuff)
	 * @since 4.1 `21.12.2018` `IneX` Fixed redirect auf ursprüngliche Seite bei Cookie-Login ohne Session
	 * @since 4.2 `04.04.2021` `IneX` Optimized Cookie and Session initialisation
	 * @since 4.3 `07.04.2021` `IneX` Updated to use modified usersystem::crypt_pw(), use better IP detection and Session handling
	 * @since 4.4 `13.04.2021` `IneX` Fixed currentlogin & lastlogin timestamps
	 * @since 5.0 `14.05.2021` `IneX` Added session_id() & session_start() Handling because now only creating a Session if user is authenticated!
	 * @since 5.1 `03.12.2021` `IneX` Replaced IP detection getRealIPaddress() with new IPinfo Class zorgUserIPinfos()
	 * @since 5.2 `28.05.2022` `IneX` Deprecated to store & retrieve User IP address on the Database
	 *
	 * @uses ZORG_SESSION_ID, ZORG_COOKIE_SESSION, ZORG_COOKIE_USERID, ZORG_COOKIE_USERPW, ZORG_SESSION_LIFETIME, ZORG_COOKIE_SECURE
	 * @uses usersystem::crypt_pw(), usersystem::invalidate_session()
	 * @uses timestamp()
	 *
	 * @param string $username Benutzername
	 * @param string $password Passwort-Hash
	 * @param boolean $user_wants_cookies Use Cookie 'true' oder 'false' - default: false
	 * @return void|string Location-Redirect (via header), oder Error-message als string
	 */
	function login($username, $password=null, $user_wants_cookies=false)
	{
		global $db;

		$error = null;

		/** erstellt sql string für User überprüfung */
		$sql = sprintf('SELECT id, %s FROM %s WHERE %s=? LIMIT 1', $this->field_userpw, $this->table_name, $this->field_username);
		$result = $db->query($sql, __FILE__, __LINE__, __METHOD__, [$username]);

		/**
		 * Record gefunden (= User existiert).
		 */
		if($db->num($result) === 1)
		{
			$rs = $db->fetch($result);
			zorgDebugger::log()->debug('username found in DB: %s', [$rs['id']]);

			/**
			 * Verifiziert ein übergebenes Passwort (on Login only)
			 */
			if (isset($password) && !empty($password) && !isset($_COOKIE[ZORG_COOKIE_USERPW]))
			{
				zorgDebugger::log()->debug('empty($password)');
				$hash_matches_pw = $this->verify_pw($password, $rs['userpw']);
				if (true === $hash_matches_pw)
				{
					zorgDebugger::log()->debug('Password matches Hash - OK');
					/** But wait: do we have a new Hash? Because usersystem::upgrade_old_pw() was invoked? */
					if (isset($this->userpw) && !empty($this->userpw))
					{
						zorgDebugger::log()->debug('Ou dang! Password-Fallback using Hash from $this->userpw');
						$crypted_pw = $this->userpw;
					} else {
						zorgDebugger::log()->debug('All good, regular Password match is OK');
						$crypted_pw = $rs['userpw'];
					}
				} else {
					zorgDebugger::log()->debug('MISMATCH Password and Hash!');
					$crypted_pw = null;
					http_response_code(401); // Set response code 401 (unauthorized)
					$error = t('authentication-failed', 'user');
				}
			}
			/**
			 * Wenn $_COOKIE[ZORG_COOKIE_USERPW] nehme diesen Hash um den Auth zu versuchen
			 */
			elseif (isset($_COOKIE[ZORG_COOKIE_USERPW]))
			{
				$crypted_pw = $_COOKIE[ZORG_COOKIE_USERPW];
			}

			/**
			 * User einloggen
			 */
			if (isset($crypted_pw) && !empty($crypted_pw))
			{
				/** Erstell SQL-Query auf Basis User+Passworthash-Kombi */
				$sql = sprintf('SELECT %8$s, %1$s, UNIX_TIMESTAMP(%2$s) %2$s, UNIX_TIMESTAMP(%3$s) %3$s, UNIX_TIMESTAMP(%4$s) %4$s FROM %5$s WHERE %6$s=? AND %7$s=? LIMIT 1',
								$this->field_user_active, // %1$s
								$this->field_ausgesperrt_bis, // %2$s
								$this->field_currentlogin, // %3$s
								$this->field_lastlogin, // %4$s
								$this->table_name, // %5$s
								$this->field_username, // %6$s
								$this->field_userpw, // %7$s
								$this->field_userid // %8$s
						);
				$result = $db->query($sql, __FILE__, __LINE__, __METHOD__, [$username, $crypted_pw]);

				/** Record gefunden = User+Password matchen */
				if($db->num($result) === 1)
				{
					$rs = $db->fetch($result);
					zorgDebugger::log()->debug('password matches=>%d', [$db->num($result)]);

					/** überprüfe ob user aktiviert wurde */
					if($rs[$this->field_user_active] !== null && $rs[$this->field_user_active] !== 0 && $rs[$this->field_user_active] !== '0' && $rs[$this->field_user_active] !== false)
					{
						/** überprüfe ob User nicht ausgesperrt ist */
						if($this->is_lockedout($rs[$this->field_ausgesperrt_bis]) === false)
						{
							/** Wenn "Autologin" (mit Cookie) aktiviert wurde vom User (und nicht global deaktiviert ist) */
							if($this->enable_cookies === true && $user_wants_cookies === true)
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
								 * Domain 'localhost' is invalid and the browser will refuse to set the cookie! instead for localhost you should use false.
								 * @link http://php.net/manual/de/function.setcookie.php
								 * @link http://php.net/manual/de/function.setcookie.php#73107
								 */
								zorgDebugger::log()->debug('Use cookies: %s | Session state: %d', [($user_wants_cookies ? 'true --> enabling & setting Cookies' : 'false'), session_status()]);
								if (session_status() === PHP_SESSION_NONE)
								{
									session_set_cookie_params([
										 'lifetime' => (isset($_ENV['COOKIE_EXPIRATION']) ? $_ENV['COOKIE_EXPIRATION'] : 60*60*24*7)
										,'path' => ZORG_COOKIE_PATH
										,'domain' => ZORG_COOKIE_DOMAIN
										,'secure' => ZORG_COOKIE_SECURE
										,'httponly' => COOKIE_HTTPONLY
										,'samesite' => ZORG_COOKIE_SAMESITE
									]);
									setcookie(ZORG_COOKIE_SESSION, session_id(), [
										 'expires' => ZORG_COOKIE_EXPIRATION
										,'path' => ZORG_COOKIE_PATH
										,'domain' => ZORG_COOKIE_DOMAIN
										,'secure' => ZORG_COOKIE_SECURE
										,'httponly' => COOKIE_HTTPONLY
										,'samesite' => ZORG_COOKIE_SAMESITE
									]);
									setcookie(ZORG_COOKIE_USERID, $username, [
										 'expires' => ZORG_COOKIE_EXPIRATION
										 ,'path' => ZORG_COOKIE_PATH
										 ,'domain' => ZORG_COOKIE_DOMAIN
										 ,'secure' => ZORG_COOKIE_SECURE
										 ,'httponly' => COOKIE_HTTPONLY
										 ,'samesite' => ZORG_COOKIE_SAMESITE
									 ]);
									setcookie(ZORG_COOKIE_USERPW, $crypted_pw, [
										 'expires' => ZORG_COOKIE_EXPIRATION
										 ,'path' => ZORG_COOKIE_PATH
										 ,'domain' => ZORG_COOKIE_DOMAIN
										 ,'secure' => ZORG_COOKIE_SECURE
										 ,'httponly' => COOKIE_HTTPONLY
										 ,'samesite' => ZORG_COOKIE_SAMESITE
									 ]);
								}
							}

							/** No Cookies wanted - Session/Login will expire on Browser close */
							else {
								if (session_status() === PHP_SESSION_NONE) session_set_cookie_params(['path' => ZORG_COOKIE_PATH, 'domain' => ZORG_COOKIE_DOMAIN, 'secure' => ZORG_COOKIE_SECURE, 'httponly' => true, 'samesite' => ZORG_COOKIE_SAMESITE]); // DISABLED: 'lifetime' => ZORG_SESSION_LIFETIME,
							}

							/** Fire up a new Session for the authenticated user */
							if (session_status() === PHP_SESSION_NONE) session_start();
							zorgDebugger::log()->debug('NEW Session ID created: %s', [session_id()]);

							/** Push User-ID to the Session Superglobal */
							$_SESSION['user_id'] = intval($rs['id']);

							/** Last Login & current Login updaten */
							zorgDebugger::log()->debug('Login update(user): %s=>%s | %s=>%s %s', [$this->field_lastlogin, timestamp(true, (int)$rs[$this->field_currentlogin]), $this->field_currentlogin, timestamp(true), print_r($rs,true)]);
							$db->update($this->table_name, ['id', $rs['id']], [
								$this->field_lastlogin => timestamp(true, (int)$rs[$this->field_currentlogin]),
								$this->field_currentlogin => timestamp(true),
								//$this->field_last_ip => getRealIPaddress(), // @DEPRECATED
							], __FILE__, __LINE__, __METHOD__);

							/**
							 * Page reload / Redirect after successful login
							 * ...because we just started a new Session
							 * ...to have __construct() assign all additional User values
							 * ...needed to work for whole page
							 */
							$redirect = filter_input(INPUT_POST, 'redirect', FILTER_DEFAULT, FILTER_REQUIRE_SCALAR) ?? null; // $_POST['redirect']
							$loginRedirectUrl = (!empty($redirect) ? base64url_decode($redirect) : getURL(true, false));
							zorgDebugger::log()->debug('redirect url => %s', [$loginRedirectUrl]);
							header('Location: '.$loginRedirectUrl);
							exit;

						} else {
							$error = t('lockout-message', 'user', date('d.m.Y HH:MM', $rs[$this->field_ausgesperrt_bis]));
						}
					} else {
						$error = t('account-inactive', 'user');
					}
				} else {
					http_response_code(401); // Set response code 401 (unauthorized)
					if (isset($_COOKIE[ZORG_COOKIE_USERPW]))
					{
						/** If User came with Cookie PW, request clearing them... */
						header('Clear-Site-Data: "cookies"'); // Request Client Browser to remove all Cookies
						$this->invalidate_session();
						$error = t('invalid-cookie', 'user');
						//user_error(t('invalid-cookie', 'user'), E_USER_WARNING); // Warnung loggen
					} else {
						$this->logerror(1,$rs['id']);
						$error = t('authentication-failed', 'user'); // nicht gegen aussen exponieren, dass es einen Useraccount gibt aber falsches PW
					}
					zorgDebugger::log()->debug('$db->num($result)=>ERROR: %s', [$error]);
				}
			} else {
				http_response_code(401); // Set response code 401 (unauthorized)
				$error = t('authentication-failed', 'user');
			}
		} else {
			http_response_code(401); // Set response code 401 (unauthorized)
			$error = t('authentication-failed', 'user'); // nicht gegen aussen exponieren, dass Useraccount NICHT existiert
		}
		zorgDebugger::log()->debug('Error: %s', [$error]);
		return $error;
	}

	/**
	 * User Logout
	 *
	 * Logt einen User aus!
	 *
	 * @author [z]cylander
	 * @author IneX
	 * @version 3.1
	 * @since 1.0 method added
	 * @since 2.0 fixed "If you put a date too far in the past, IE will bark and igores it, i.e. the value will not be removed"
	 * @since 3.0 `21.11.2018` Fixed redirect bei Logout auf jeweils aktuelle Seite, nicht immer Home
	 * @since 3.1 `11.04.2021` Added HTTP Header Output "Clear-Site-Data: cookies"
	 *
	 * @uses usersystem::invalidate_session()
	 * @return void
	 */
	static function logout()
	{
		/** Session destroy & Cookies killen */
		self::invalidate_session();

		/** Request Client Browser to remove all Cookies */
		header('Clear-Site-Data: "cookies"');

		/** Redirect user back to last page */
		$redirect = filter_input(INPUT_POST, 'redirect', FILTER_DEFAULT, FILTER_REQUIRE_SCALAR) ?? null; // $_POST['redirect']
		$redirectUrl = (!empty($redirect) ? base64url_decode($redirect) : $_SERVER['PHP_SELF']);
		zorgDebugger::log()->debug('redirect url => %s', [$redirectUrl]);
		header('Location: '.$redirectUrl);
		exit;
	}

	/**
	 * Session & Cookies invalidieren
	 *
	 * Important: Cookies must be deleted with the same parameters as they were set with.
	 * If the value argument is an empty string, or false, and all other arguments match a previous call to setcookie,
	 * then the cookie with the specified name will be deleted from the remote client.
	 *
	 * @link https://www.php.net/manual/en/function.setcookie.php
	 * @link https://stackoverflow.com/questions/686155/remove-a-cookie
	 *
	 * @author IneX
	 * @version 2.0
	 * @since 1.0 `28.11.2018` `IneX` method added
	 * @since 2.0 `08.04.2021` `IneX` Fixed proper Session & Cookie removal (order matters... who knew)
	 *
	 * @return void
	 */
	static function invalidate_session()
	{
		if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> Destroying Session for user %d', __METHOD__, __LINE__, (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'not logged in')));

		/** Session destroy */
		if (session_status() === PHP_SESSION_NONE) {
			/** Session muss vor dem Destroy initialisiert werden ! */
			session_name(ZORG_SESSION_ID);
			session_set_cookie_params(['lifetime' => null, 'path' => ZORG_COOKIE_PATH, 'domain' => ZORG_COOKIE_DOMAIN, 'secure' => ZORG_COOKIE_SECURE, 'httponly' => true, 'samesite' => ZORG_COOKIE_SAMESITE]);
			session_start();
		}
		session_regenerate_id(true); // Only works if a Session is active
		if (isset($_GET[ZORG_SESSION_ID])) unset($_GET[ZORG_SESSION_ID]); // Session-Parameter unsetten
		if (isset($_POST[ZORG_SESSION_ID])) unset($_POST[ZORG_SESSION_ID]); // Session-Parameter unsetten
		foreach (array_keys($_SESSION) as $k) unset($_SESSION[$k]); // PHP Session Superglobal leeren

		/**
		 * Cookies killen.
		 * Einmal unsetten & danach neu - aber abgelaufen - schreiben
		 */
		/** Previous z Cookie Version / Settings */
		if (isset($_COOKIE[ZORG_COOKIE_USERPW]) && strlen($_COOKIE[ZORG_COOKIE_USERPW]) === 13 && substr($_COOKIE[ZORG_COOKIE_USERPW], 0, 2) === 'CR')
		{
			setcookie(ZORG_COOKIE_SESSION, '', ['expires' => time()-3600, 'path' => ZORG_COOKIE_PATH, 'domain' => ZORG_COOKIE_DOMAIN, 'secure' => ZORG_COOKIE_SECURE, 'httponly' => true, 'samesite' => 'Strict']); // Session-Cookie invalidieren
			setcookie(ZORG_COOKIE_USERID, '', ['expires' => time()-3600, 'path' => ZORG_COOKIE_PATH, 'domain' => ZORG_COOKIE_DOMAIN, 'secure' => ZORG_COOKIE_SECURE, 'httponly' => true, 'samesite' => 'Strict']); // Login-Cookie invalidieren
			setcookie(ZORG_COOKIE_USERPW, '', ['expires' => time()-3600, 'path' => ZORG_COOKIE_PATH, 'domain' => ZORG_COOKIE_DOMAIN, 'secure' => ZORG_COOKIE_SECURE, 'httponly' => true, 'samesite' => 'Strict']); // Password-Cookie invalidieren
		}
		/** z Cookies unsetten */
		if (isset($_COOKIE[ZORG_COOKIE_SESSION])) unset($_COOKIE[ZORG_COOKIE_SESSION]); // zorg Session-Cookie unsetten
		if (isset($_COOKIE[ZORG_COOKIE_USERID])) unset($_COOKIE[ZORG_COOKIE_USERID]); // Login-Cookie unsetten
		if (isset($_COOKIE[ZORG_COOKIE_USERPW])) unset($_COOKIE[ZORG_COOKIE_USERPW]); // Password-Cookie unsetten
		if (isset($_SERVER['HTTP_COOKIE'])) unset($_SERVER['HTTP_COOKIE']);

		/** New z Cookies */
		setcookie(ZORG_COOKIE_SESSION, '', ['expires' => time()-3600, 'path' => ZORG_COOKIE_PATH, 'domain' => ZORG_COOKIE_DOMAIN, 'secure' => ZORG_COOKIE_SECURE, 'httponly' => true, 'samesite' => ZORG_COOKIE_SAMESITE]); // zorg Session-Cookie invalidieren
		setcookie(ZORG_COOKIE_USERID, '', ['expires' => time()-3600, 'path' => ZORG_COOKIE_PATH, 'domain' => ZORG_COOKIE_DOMAIN, 'secure' => ZORG_COOKIE_SECURE, 'httponly' => true, 'samesite' => ZORG_COOKIE_SAMESITE]); // Login-Cookie invalidieren
		setcookie(ZORG_COOKIE_USERPW, '', ['expires' => time()-3600, 'path' => ZORG_COOKIE_PATH, 'domain' => ZORG_COOKIE_DOMAIN, 'secure' => ZORG_COOKIE_SECURE, 'httponly' => true, 'samesite' => ZORG_COOKIE_SAMESITE]); // Password-Cookie invalidieren

		/** Finally destroy the PHP Session store */
		session_unset();
		session_destroy();
		session_write_close();
	}

	/**
	 * Speichert ob User zorg oder zooomclan Layout haben will
	 *
	 * @deprecated This seems no longer used (no function reference / dependency found) / IneX, 14.01.2024
	 *
	 * @param integer $user_id User-ID
	 * @param boolean $zorg Zorg-Layout
	 * @param boolean $zooomclan Zooomclan-Layout
	 */
	function set_page_style($user_id, $zorg=TRUE, $zooomclan=FALSE)
	{
		global $db, $zorg, $zooomclan;

		$params = [];
		$sql = sprintf('UPDATE %s SET %s=? WHERE id=?', $this->table_name, $this->field_zorger);
		$params[] = ($zooomclan ? '0' : '1');
		$params[] = $user_id;

		$db->query($sql, __FILE__, __LINE__, __METHOD__, $params);
	}

	/**
	 * Neues Passwort
	 * Generiert ein Passwort für einen bestehenden User
	 *
	 * @version 4.3
	 * @since 1.0 method added
	 * @since 2.0 global strings added
	 * @since 3.0 `17.10.2018` `IneX` Fixed Bug #763: Passwort vergessen funktioniert nicht
	 * @since 4.0 `21.10.2018` `IneX` Code & DB-Query improvements
	 * @since 4.1 `04.01.2019` `IneX` Fixed handling $db->update() result, changed Error messages, added debugging-output on DEV
	 * @since 4.2 `04.04.2021` `IneX` Adjusted string encoding for MIME header of To: & Subject: lines in e-mail
	 * @since 4.3 `07.04.2021` `IneX` Updated to use modified usersystem::crypt_pw()
	 *
	 * @uses usersystem::password_gen()
	 * @uses usersystem::crypt_pw()
	 * @uses SITE_HOSTNAME, SENDMAIL_EMAIL
	 * @param string $email E-Mailadresse für deren User das PW geändert werden soll
	 * @global object $db Globales Class-Object mit allen MySQL-Methoden
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
				$crypted = $this->crypt_pw($new_pass);
				if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> crypt_pw() done: %s', __METHOD__, __LINE__, $crypted));

				/** 3. trage aktion in errors ein */
				$this->logerror(3,$rs['id']);
				if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> logerror() done', __METHOD__, __LINE__));

				/** 4. update neues pw in user table */
				$result = $db->update($this->table_name, ['id', $rs['id']], ['userpw' => $crypted], __FILE__, __LINE__, __METHOD__);
				if ($result !== false)
				{
					/** 5. versende email mit neuem passwort */
					$mail_header = t('email-notification-header', 'messagesystem', [ SITE_HOSTNAME, SENDMAIL_EMAIL, phpversion() ]);
					$mail_subject = sprintf('=?UTF-8?Q?%s?=', quoted_printable_encode(remove_html(t('message-newpass-subject', 'user'), ENT_DISALLOWED, 'UTF-8')));
					$mail_body = t('message-newpass', 'user', [$rs['username'], $new_pass]);
					$new_pass_mail_status = mail($email, $mail_subject, $mail_body, $mail_header);
					if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> Passwort reset mail() sent with status: %s', __METHOD__, __LINE__, ($new_pass_mail_status?'true':'false')));
					if ($new_pass_mail_status) {
						return true;
					} else {
						error_log(sprintf('[WARN] <%s:%d> Passwort reset e-mail could not be sent to "%s"', __METHOD__, __LINE__, $email));
						return false;
					}
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
	 * @version 3.1
	 * @since 1.0 method added
	 * @since 2.0 `IneX` replaced messages with Translation-String solution t()
	 * @since 3.0 `04.12.2018` `IneX` removed IMAP-code, code & query optimizations
	 * @since 3.1 `03.01.2024` `IneX` code hardenings, excluded Password-Checks
	 *
	 * @uses usersystem::crypt_pw(), t()
	 * @uses SITE_URL, SENDMAIL_EMAIL
	 * @param string $username Benutzername
	 * @param string $crypted_pw Passwort Hash created using password_hash()
	 * @param string $email E-Mail
	 * @global object $db Globales Class-Object mit allen MySQL-Methoden
	 * @return bool|string True on success, or Error when unsuccessful
	 */
	function create_newuser($username, $crypted_pw, $email) {
		global $db;

		if(is_string($username))
		{
			$sql = sprintf('SELECT id FROM %s WHERE %s=?', $this->table_name, $this->field_username);
			$result = $db->query($sql, __FILE__, __LINE__, __METHOD__, [$username]);

			/** überprüfe ob user bereits existiert */
			if(!$db->num($result))
			{
				/** E-mailadresse validieren */
				if(check_email($email))
				{
					/** überprüfe ob user mit gleicher email nicht bereits existiert */
					$sql = 'SELECT id FROM '.$this->table_name.' WHERE '.$this->field_email.'=?';
					$result = $db->query($sql, __FILE__, __LINE__, __METHOD__, [$email]);

					if($db->num($result) === 0)
					{
						/** erstelle regcode */
						$key = $this->regcode_gen($username);

						/** user eintragen */
						$sql = 'INSERT into '.$this->table_name.'
									('.$this->field_regcode.', '.$this->field_regdate.', '.$this->field_userpw.','.$this->field_username.',
									'.$this->field_email.', '.$this->field_usertyp.')
								VALUES (?,?,?,?,?,1)';
						$db->query($sql, __FILE__, __LINE__, __METHOD__, [$key, timestamp(true), $crypted_pw, $username, $email]);

						/** email versenden */
						$sendNewaccountConfirmation = mail($email, t('message-newaccount-subject', 'user'), t('message-newaccount', 'user', [ $username, SITE_URL, $key ]), 'From: '.SENDMAIL_EMAIL."\n");
						if ($sendNewaccountConfirmation !== true)
						{
							error_log(sprintf('[NOTICE] <%s:%d> Account confirmation e-mail could NOT be sent', __FILE__, __LINE__));
							$error = t('error-userprofile-update', 'user');
						} else {
							//$error = t('account-confirmation', 'user');
							return true;
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
	 * Passwort encryption
	 *
	 * Verschlüsselt ein Passwort als Hash.
	 * Benutzt den Default Algorithmus (Blowfish).
	 * ACHTUNG: nicht PASSWORD_BCRYPT verwenden, weil dann der eingehende String auf 72 chars truncated wird!
	 *
	 * @version 2.0
	 * @since 1.0 `milamber` Method added
	 * @since 2.0 `07.04.2021` `IneX` Moved function from util.inc.php & changed to password_hash() conversion
	 *
	 * @link https://www.php.net/manual/en/function.password-hash.php
	 * @see usersystem::verify_pw()
	 *
	 * @param string $password Plaintext Passwort
	 * @return string|bool Hashed $password (or false)
	 */
	function crypt_pw($password)
	{
		if (DEVELOPMENT === true) error_log(sprintf('[DEBUG] <%s:%d> Calculation a Password Hash for you...', __METHOD__, __LINE__));
		return password_hash($password, PASSWORD_DEFAULT);
	}

	/**
	 * Passwort hash verification
	 *
	 * Prüft ein Passwort mit dem vorhandenen Password-Hash.
	 * Ist das Gegenstück zur password_hash()-Funktion.
	 *
	 * @version 1.0
	 * @since 1.0 `07.04.2021` `IneX` Method added
	 *
	 * @link https://www.php.net/manual/en/function.password-verify.php
	 * @uses usersystem::upgrade_pw()
	 *
	 * @param string $password Plaintext Passwort das verifiziert werden soll
	 * @param string $password_hash Plaintext Passwort das verifiziert werden soll
	 * @return bool True wenn $password zum Hash matchen, False wenn nicht
	 */
	function verify_pw($password, $password_hash)
	{
		$password_hash_check = $this->upgrade_old_pw($password, $password_hash);
		if (false === $password_hash_check)
		{
			/** PW upgrade required, but it failed */
			return false;
		} else {
			/** Regular Verification */
			return password_verify($password, (true === $password_hash_check ? $password_hash : $password_hash_check));
		}
	}

	/**
	 * Upgrade an old DES encrypted Password Hash
	 *
	 * Upgrade für alte crypt() DES basierten PW Hashes. Nur falls nötig.
	 * Diese Hashes haben eine fixe Länge von 13 Zeichen & starten immer mit "CR":
	 * - z.B.:`CR0DiniMueter`
	 *
	 * @version 1.0
	 * @since 1.0 `08.04.2021` `IneX` Method added
	 *
	 * @param string $password Plaintext Passwort das verifiziert werden soll
	 * @param string $password_hash Plaintext Passwort das verifiziert werden soll
	 * @return string|bool True wenn kein Upgrade nötig, neuer Hash-String wenn $password_hash updated wurde, False bei Fehler
	 */
	function upgrade_old_pw($password, $password_hash)
	{
		/**
		 * Check ob Hash ein alter crypt() DES basierter Hash ist & upgraded werden muss
		 */
		if (strlen($password_hash) === 13 && substr($password_hash, 0, 2) === 'CR')
		{
			/** $password_hash is old, thus we need to upgrade it NOW! */
			if (DEVELOPMENT === true) error_log(sprintf('[DEBUG] <%s:%d> Oh, oh - we have an old Password Hash!', __METHOD__, __LINE__));
			if ($password_hash === crypt($password, 'CRYPT_BLOWFISH'))
			{
				/** ...but only if it matches to old Hash */
				if (DEVELOPMENT === true) error_log(sprintf('[DEBUG] <%s:%d> Old Password Hash is VALID', __METHOD__, __LINE__));
				$temp_password_hash = $this->crypt_pw($password); // Temporary *Upgraded* Password Hash
				$this->userpw = $temp_password_hash; // Store temporary *new" Password Hash -> needed for exec_newpassword()
				if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> TEMPORARY $this->userpw = %s', __METHOD__, __LINE__, $this->userpw));

				/** Directly update the User's OLD hash with the NEW one */
				$pw_upgrade = $this->exec_newpassword($this->user2id($_POST['username']), $password, $password, $password); // The Password itself does not change, of course.
				if ($pw_upgrade[0] !== false)
				{
					/** exec_newpassword() failed, deshalb ABBRUCH */
					if (DEVELOPMENT === true) error_log(sprintf('[DEBUG] <%s:%d> Password *Upgrade* FAILED', __METHOD__, __LINE__));
					return false;
				} else {
					/* DONE - return upgraded Password Hash */
					if (DEVELOPMENT === true) error_log(sprintf('[DEBUG] <%s:%d> Password *Upgrade* SUCCESS', __METHOD__, __LINE__));
					return $password_hash;
				}
			} else {
				/** Otherwise: no need to proceed... */
				if (DEVELOPMENT === true) error_log(sprintf('[DEBUG] <%s:%d> Old Password Hash is WRONG', __METHOD__, __LINE__));
				return false;
			}
		} else {
			/** Not an old DES Hash... */
			if (DEVELOPMENT === true) error_log(sprintf('[DEBUG] <%s:%d> Password is not old / not DES --> Upgrade skipped', __METHOD__, __LINE__));
			return true;
		}
	}

	/**
	 * Online Users
	 *
	 * Gibt Online Users als HTML aus
	 *
	 * @version 2.3
	 * @since 1.0 Method added
	 * @since 2.0 `IneX` Code optimizations
	 * @since 2.1 `17.04.2020` `IneX` SQL Slow-Query optimization
	 * @since 2.2 `10.04.2021` `IneX` Code optimizations (store $db->num instead of calling it in loop)
	 * @since 2.3 `22.12.2021` `IneX` Code optimizations (implode instead of custom count)
	 *
	 * @TODO HTML can be returned using new function usersystem::userpage_link()
	 *
	 * @uses USER_TIMEOUT
	 * @param boolean $pic Userpic anzeigen, oder nur Usernamen - default: false
	 * @global object $db Globales Class-Object mit allen MySQL-Methoden
	 * @return string HTML-Code
	 */
	function online_users($pic=FALSE)
	{
		global $db;

		$html = '';
		$sql = 'SELECT id, username, clan_tag FROM user
				WHERE activity > (?-?) ORDER by activity DESC';
		$result = $db->query($sql, __FILE__, __LINE__, __METHOD__, [timestamp(true), USER_TIMEOUT]);
		$num_online = $db->num($result);
		if (!empty($num_online) && $num_online !== false)
		{
			while ($rs = $db->fetch($result))
			{
				$full_username = (!empty($rs['clan_tag']) ? $rs['clan_tag'] : '').$rs['username'];
				if ($pic == FALSE)
				{
					$userList[] = sprintf('<a href="/profil.php?user_id=%s">%s</a>', (string)$rs['id'], $full_username);
				} else {
					// @FIXME Change to <div> with flexbox, use Smarty Tpl Partial?
					$html .= sprintf('<table bgcolor="%1$s" border="0"><tr><td>
										<a href="/profil.php?user_id=%2$s">
											<img border="0" src="%3$s.jpg" title="%4$s">
										</a>
									</td></tr><tr><td align="center">
										<a href="/profil.php?user_id=%2$s">%4$s</a>
									</td></tr></table><br>',
							TABLEBACKGROUNDCOLOR, (string)$rs['id'], USER_IMGPATH_PUBLIC.$rs['id'], $full_username);
				}
			}
			if ($pic == FALSE) $html = implode(', ', $userList);
		}
		return $html;
	}

	/**
	 * User aktivieren
	 * Aktiviert einen Useraccount mittels Regcode
	 *
	 * @version 2.0
	 * @since 1.0 Method added
	 * @since 2.0 `07.12.2019` `IneX` Fixed $regcode check and response for profil.php
	 *
	 * @var string $error_message String to store any Error message for later output
	 * @param string $regcode User Registration-Code
	 * @global object $db Globales Class-Object mit allen MySQL-Methoden
	 * @return bool True/False whether if user could be activated or not
	 */
	function activate_user($regcode)
	{
		global $db;

		$sql = 'SELECT id, username, active FROM user WHERE regcode = "?"';
		$result = $db->query($sql, __FILE__, __LINE__, __METHOD__, [$regcode]);
		if($db->num($result))
		{
			if (DEVELOPMENT === true) error_log(sprintf('[DEBUG] <%s:%d> User regcode: VALID', __METHOD__, __LINE__));
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
			if (DEVELOPMENT === true) error_log(sprintf('[DEBUG] <%s:%d> User regcode: INVALID', __METHOD__, __LINE__));
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
	 * @TODO `ip`-field: must be extended to 45 length (ipv6) & should use value from \Utils\User\IP2Geolocation::getRealIPaddress()
	 *
	 * @return void
	 * @param int $do Aktion
	 * @param int $user_id User ID
	 */
	function logerror($do,$user_id) {
		global $db;
		$do_array = array(
			1 => t('authentication-failed', 'user'),
			2 => t('invalid-regcode', 'user'),
			3 => t('newpass-confirmation', 'user')
		);

		$sql = 'INSERT into error (user_id, do, ip, date) VALUES (?, ?, ?, ?)';
		$db->query($sql, __FILE__, __LINE__, __METHOD__,
				[$user_id, $do_array[$do], $_SESSION[$this->sessionkey_last_ip], timestamp(true)]);
	}

	/**
	 * Registrationscode generieren
	 *
	 * Erstellt einen Registrationscode für einen Benutzer
	 *
	 * @param string $username
	 * @return string hash
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
	 * @since 2.0 `14.11.2018` method renamed from "islogged_in" => "is_loggedin"
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
	 * @since 1.0 `14.11.2018` method added
	 *
	 * @param integer $ausgesperrt_bis_timestamp Unix-Timestamp for specific date to check lockout against
	 * @global array $_geaechtet Globales Array mit allen geächteten Usern
	 * @return bool Returns true/false if user is currently locked out, or not
	 */
	function is_lockedout($ausgesperrt_bis_timestamp)
	{
		global $_geaechtet;

		if (DEVELOPMENT === true) error_log(sprintf('[DEBUG] <%s:%d> Ausgesperrt => %s > %s ?', __METHOD__, __LINE__, $ausgesperrt_bis_timestamp, time()));
		if (!empty($ausgesperrt_bis_timestamp) && $ausgesperrt_bis_timestamp > 0)
		{
			if ($ausgesperrt_bis_timestamp > time())
			{
				if (DEVELOPMENT === true) error_log(sprintf('[DEBUG] <%s:%d> Ausgesperrt => TRUE', __METHOD__, __LINE__));
				$_geaechtet[] = $_SESSION['user_id'];
				return true;
			}
		} else if (isset($_SESSION['user_id']) && !empty($_geaechtet[$_SESSION['user_id']])) {
			if (DEVELOPMENT === true) error_log(sprintf('[DEBUG] <%s:%d> Ausgesperrt => TRUE ($_geachtet !)', __METHOD__, __LINE__));
			return true;
		} else {
			if (isset($this->ausgesperrt_bis) && $this->ausgesperrt_bis > time())
			{
				$_geaechtet[] = $_SESSION['user_id'];
				return true;
			}
		}
		if (DEVELOPMENT === true) error_log(sprintf('[DEBUG] <%s:%d> Ausgesperrt => FALSE', __METHOD__, __LINE__));
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
	 * @since 2.0 `04.01.2019` updated mechanism and form of generated passwords, not using $username string anymore
	 *
	 * @param integer $length (Optional) specify length of random password to generate, Default: 12
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
	 * @version 3.1
	 * @since 1.0 Method added
	 * @since 2.0 `11.07.2018` `IneX` added check for locally cached Gravatar, replaced 'file_exists' with 'stream_resolve_include_path'
	 * @since 3.0 `16.07.2018` `IneX` Method now returns path to userpic (or queried Gravatar result) as string, instead of true.
	 * @since 3.1 `18.04.2020` `IneX` replaced 'stream_resolve_include_path' with more performant 'is_file' (https://stackoverflow.com/a/19589043/5750030)
	 *
	 * @uses USER_IMGPATH
	 * @uses USER_IMGEXTENSION
	 * @param int $userid User ID
	 * @return string|bool Returns userimage path as string, or false if not found
	 */
	function checkimage($userid)
	{
		/** Image-Path to check */
		$user_imgpath_custom = USER_IMGPATH.$userid.USER_IMGEXTENSION;
		$user_imgpath_gravatar = USER_IMGPATH.$userid.'_gravatar'.USER_IMGEXTENSION;

		/** Check for cached Gravater */
		if (is_file($user_imgpath_gravatar) !== false) // TODO use fileExists() method from util.inc.php?
		{
			zorgDebugger::log()->debug('userImage GRAVATAR exists/cached: %s', [strval($user_imgpath_gravatar)]);
			return $user_imgpath_gravatar;

		/** Check for custom Userpic */
		} elseif (is_file($user_imgpath_custom) !== false) {
			zorgDebugger::log()->debug('userImage ZORG exists/cached: %s', [strval($user_imgpath_custom)]);
			return $user_imgpath_custom;

		/** Return false if no userpic cached */
		} else {
			zorgDebugger::log()->debug('userImage NOT CACHED: querying Gravatar');
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
	 * @since 2.0 `IneX` Check & load cached Gravatar, optimized if-else
	 *
	 * @uses USER_IMGPATH, USER_IMGPATH_PUBLIC, USER_IMGSIZE_SMALL, USER_IMGSIZE_LARGE
	 * @uses usersystem::checkimage(), usersystem::get_gravatar()
	 * @param int $userid User ID
	 * @param boolean $large Large image true/false
	 * @return string URL-Pfad zum Bild des Users
	 */
	function userImage($userid, $large=false)
	{
		/** Check if userpic-file exists, and return it */
		$user_imgpath = $this->checkimage($userid);
		if (!empty($user_imgpath))
		{
			/** Make internal USER_IMGPATH to external USER_IMGPATH_PUBLIC */
			$user_imgpath = str_replace(USER_IMGPATH, USER_IMGPATH_PUBLIC, $user_imgpath);

			/** Add Thumbnail shortcut, if $large is NOT set */
			if ($large !== true) $user_imgpath = str_replace(USER_IMGEXTENSION, '_tn' . USER_IMGEXTENSION, $user_imgpath);

			return $user_imgpath;

		/** If no userpic-file exists, query Gravatar with USER_IMGPATH_DEFAULT as fallback image */
		} else {
			zorgDebugger::log()->debug('userImage not cached for $userid: %s', [strval($userid)]);
			return $this->get_gravatar(
										 $this->id2useremail($userid)
										,($large ? USER_IMGSIZE_LARGE : USER_IMGSIZE_SMALL)
										,USER_IMGPATH_PUBLIC.USER_IMGPATH_DEFAULT
									);
		}
	}

	/**
	 * Retrieve list of Users for Notification-Messages in Comments or Personal Messages
	 *
	 * @deprecated
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

		$sql = 'SELECT id, clan_tag, username FROM user'
				.' WHERE UNIX_TIMESTAMP(lastlogin) > (UNIX_TIMESTAMP(NOW())-?)'
				.' OR z_gremium = "1" OR (vereinsmitglied != "0" AND vereinsmitglied != "")'
				.(!empty($users_selected) ? ' OR id IN (?)' : null)
				.' ORDER BY clan_tag DESC, username ASC'
		;
		$params = [ USER_OLD_AFTER*2 ];
		if (!empty($users_selected)) { $params[] = implode(',', $users_selected); }
		$result = $db->query($sql, __FILE__, __LINE__, __METHOD__, $params);

		$html = '<select multiple="multiple" name="'.$name.'" size="'.$size.'" tabindex="'.$tabindex.'">';
		$htmlSelectElements = [];
		while ($rs = $db->fetch($result))
		{
			$full_username = (!empty($rs['clan_tag']) ? $rs['clan_tag'] : '').$rs['username'];
			$selectCurrent = (in_array($rs['id'], $users_selected) ? 'selected' : false);
			$elementHtml = sprintf('<option value="%d" %s>%s</option>', $rs['id'], $selectCurrent, $full_username);
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
	 * @TODO 20.07.2018 Find out & fix issue with Query failing on id=$id instead of id="$id"...
	 *
	 * @author IneX
	 * @version 5.0
	 * @since 1.0
	 * @since 2.0
	 * @since 3.0 Method now really only resolves an ID to a Username, redirects other features
	 * @since 4.0 changed output to new function usersystem::userprofile_link()
	 * @since 5.0 added better validation for $id & changed return to 'false' if $id doesn't exist
	 *
	 * @uses usersystem::userprofile_link()
	 * @global object $db Globales Class-Object mit allen MySQL-Methoden
	 * @param integer $id User ID
	 * @param boolean $clantag Username mit Clantag true/false
	 * @param boolean $pic DEPRECATED Anstatt Username das Userpic HTML-Code ausgeben true/false
	 * @return string|boolean Username (mit/ohne Clantag), oder 'false' wenn $id ungültig ist
	 */
	function id2user($id, $clantag=FALSE, $pic=FALSE)
	{
		global $db, $_users;

		/** Validate passed parameters */
		$userid = (empty($id) || !is_numeric($id) ? false : (int)$id);
		$use_clantag = (bool)$clantag;
		//$show_pic = (bool)$pic;

		/** If given User-ID is not valid (not numeric), show a User Error */
		if (false === $userid) {
			user_error(t('invalid-id', 'user'), E_USER_WARNING);
			return false;
		}

		if (!isset($_users[$userid]) || ($use_clantag === true && !isset($_users[$userid]['clan_tag'])))
		{
			if ($use_clantag === true && !isset($_users[$userid]['clan_tag']))
			{
				$sql = 'SELECT username, clan_tag FROM user WHERE id=? LIMIT 1';
			} else {
				$sql = 'SELECT username FROM user WHERE id=? LIMIT 1';
			}
			$rs = $db->fetch($db->query($sql, __FILE__, __LINE__, __METHOD__, [$userid]));
			if (!empty($rs) || $rs !== false || !empty($rs['username']))
			{
				/** User $id exists - add record to global $_users-Array */
				$_users[$userid] = $rs;
			} else {
				/** User $id does NOT exist */
				user_error(t('invalid-id', 'user'), E_USER_WARNING);
				return false;
			}
		}

		/** Set string with Username */
		$username = (isset($_users[$userid]['username']) && !empty($_users[$userid]['username']) ? $_users[$userid]['username'] : 'yarak');

		/** If applicable, prefix Username with the Clantag */
		if ($use_clantag === true)
		{
			/** ...but only if the user really HAS a Clantag! */
			if (isset($_users[$userid]['clan_tag']) && !empty($_users[$userid]['clan_tag']))
			{
				$username = $_users[$userid]['clan_tag'].$username;
			}
		}
		return $username;
	}

	/**
	 * Get User ID based on Username
	 *
	 * Konvertiert einen Username zur dazugehörigen User ID
	 *
	 * @author IneX
	 * @version 2.0
	 * @since 1.0 initial function
	 * @since 2.0 optimized sql-query
	 * @since 2.1 `07.07.2021` code & return value optimization
	 *
	 * @global	object	$db	Globales Class-Object mit allen MySQL-Methoden
	 * @param string $username Username
	 * @return int User ID oder 0
	 */
	function user2id ($username)
	{
		global $db;
		$e = $db->query('SELECT id FROM user WHERE username=? LIMIT 1', __FILE__, __LINE__, __METHOD__, [$username]);
		$d = $db->fetch($e);
		return ($d !== false || !empty($d) ? $d['id'] : 0);
	}

	/**
	 * Userpic (klein) ausgeben
	 *
	 * @deprecated
	 *
	 * @author IneX
	 * @date 02.10.2009
	 * @version 2.0
	 * @since 1.0 initial function
	 * @since 2.0 changed output to new function usersystem::userprofile_link()
	 *
	 * @TODO there is no $clantag passed to this function?!
	 *
	 * @uses usersystem::userprofile_link()
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
		/** @deprecated
		global $db, $user;
		static $_users = array();

		$us = '';

		if ($displayName) {
			if (!isset($_users[$id])) {
				try {
					$sql = "SELECT clan_tag, username FROM user WHERE id=?";
					$result = $db->query($sql, __FILE__, __LINE__, __METHOD__, [$id]);
					while ($rs = $db->fetch($result)) {
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
		return $this->userprofile_link($id, ['pic' => TRUE, 'link' => TRUE, 'username' => FALSE, 'clantag' => FALSE]);
	}

	/**
	 * Gravatar Userpic
	 *
	 * Get either a Gravatar URL or complete image tag for a specified email address.
	 *
	 * @source http://gravatar.com/site/implement/images/php/
	 * @author IneX
	 * @version 3.0
	 * @since 1.0 `IneX` 24.07.2014
	 * @since 2.0 `11.01.2017` `IneX` Fixed Gravatar-URL to https using SITE_PROTOCOL
	 * @since 3.0 `16.07.2018` `IneX` Removed possibility to return `img`-Tag
	 *
	 * @uses SITE_PROTOCOL
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
		if(strpos($url_check[0],'200')===false) return htmlspecialchars($url_parse['path'], ENT_QUOTES, 'UTF-8'); // If $url response header is NOT 200, use local image
		return $url;
	}

	/**
	 * Fetch Gravatar images for Userlist
	 *
	 * @author IneX
	 * @version 1.0
	 * @since 1.0 `12.07.2018` `IneX` method added
	 * @since 1.1 `13.04.2021` `IneX` fixed switch-case conditions
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
		switch (true)
		{
			/** (integer)USER: If $userScope = User-ID: try to get the User's Gravatar-Image */
			case (is_numeric($userScope) && $userScope > 0):
				if (DEVELOPMENT === true) error_log(sprintf('[DEBUG] <%s:%d> Checking for User-ID: %d', __METHOD__, __LINE__, $userScope));
				if ($this->exportGravatarImages([$userScope])) return true;

			/** (array)LIST: If $userScope = User-ID list: try to get Gravatar-Image for all of them */
			case ($userScope === 'all'):
				if (DEVELOPMENT === true) error_log(sprintf('[DEBUG] <%s:%d> Checking for %s User-IDs', __METHOD__, __LINE__, $userScope));
				$sql = 'SELECT id FROM user WHERE email IS NOT NULL AND email<>? AND active=?';
				$userids_list = $db->query($sql, __FILE__, __LINE__, __METHOD__, ['', 1]);
					while ($result = $db->fetch($userids_list))
				{
					$userids[] = $result['id'];
				}
				if ($this->exportGravatarImages($userids)) return true;
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
	 * @since 1.0 `11.07.2018` `IneX` function added
	 * @since 2.0 `13.08.2018` `IneX` added md5 file hash check to compare files before downloading
	 *
	 * @TODO wenn die usersystem::id2useremail() Funktion gefixt ist (nicht nur eine response wenn E-Mail Notifications = true), dann Query ersetzen mit Methode
	 *
	 * @uses SITE_PROTOCOL
	 * @uses USER_IMGPATH
	 * @uses USER_IMGSIZE_LARGE
	 * @uses USER_IMGSIZE_SMALL
	 * @uses USER_IMGEXTENSION
	 * @uses cURLfetchUrl()
	 * @uses fileHash()
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
			 * @TODO wenn die usersystem::id2useremail() Funktion gefixt ist (nicht nur eine response wenn E-Mail Notifications = true), dann Query ersetzen mit Methode
			 */
			//$useremail = usersystem::id2useremail($userid);
			$queryresult = $db->fetch($db->query('SELECT email FROM user WHERE id = ? LIMIT 1', __FILE__, __LINE__, __METHOD__, [$userid]));
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
	 * @param int $id User ID
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
	 * ID zu Mail_Username
	 *
	 * Wandelt eine User ID in IMAP-Mail_Username um
	 *
	 * @deprecated
	 *
	 * @param int $id User ID
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
	 * @since 1.0 `17.03.2018` `IneX` method added
	 * @since 2.0 added additional check for "email_notification=TRUE"
	 * @since 3.0 updated method return values, added query try-catch
	 * @since 4.0 removed check for "email_notification=TRUE" due to new Notifications() Class
	 * @since 4.1 `05.12.2019` `IneX` removed unneccessary try-catch
	 *
	 * @uses check_email()
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
	 * @deprecated 2.0 Ersetzt mit usersystem::userprofile_link()
	 *
	 * @author [z]milamber
	 * @author IneX
	 * @version 2.0
	 * @since 1.0 initial version
	 * @since 2.0 changed output to new function usersystem::userprofile_link()
	 *
	 * @uses usersystem::userprofile_link()
	 * @param int $user_id User ID
	 * @param bool $pic Userpic mitausgeben
	 * @return string html
	 */
	function link_userpage($user_id, $pic=FALSE)
	{
		/** @deprecated */
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
		return $this->userprofile_link($user_id, ['pic' => $pic, 'link' => TRUE, 'username' => TRUE, 'clantag' => TRUE]);
	}

	/**
	 * Link zu einem Userprofil
	 *
	 * @deprecated Ersetzt mit usersystem::userprofile_link()
	 * @TODO wird diese Methode usersystem::userpagelink() noch benötigt irgendwo? Sonst: raus!
	 *
	 * @uses usersystem::userpage_link()
	 */
	function userpagelink($userid, $clantag, $username) {
		/** @deprecated
		$name = $clantag.$username;

		// Dreadwolfs spezieller Nick
		//if($userid == 307) $name = '<b style="background-color: green; color: white;">&otimes; '.$name.' &oplus;</b>';

		return '<a href="/user/'.$userid.'">'.$name.'</a>';
		*/

		/** Because method is DEPRECATED => Redirect to new usersystem::userprofile_link() */
		return $this->userprofile_link($userid, ['link' => TRUE, 'username' => TRUE, 'clantag' => TRUE]);
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
	 * @since 1.0 `05.07.2018` `IneX` initial version (output from Smarty-Template)
	 *
	 * @uses usersystem::userImage()
	 * @uses usersystem::id2user()
	 * @link https://github.com/zorgch/zorg-code/blob/master/www/templates/layout/partials/profile/userprofile_link.tpl Template for output used is userprofile_link.tpl
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
		if ($show_username) {
			/** URL-safe Username encoding including . and ~ (but not - and _) */
			$urlencoded_username = str_replace('.', '%2E',
										str_replace('~', '%7E',
											rawurlencode($this->id2user($userid, false, false))
									));
			$smarty->assign('username_link', $urlencoded_username);
		}
		$smarty->assign('show_profile_link', ($show_link ? 'true' : 'false'));

		return $smarty->fetch('file:layout/partials/profile/userprofile_link.tpl');
	}

	/**
	 * User specific /data/files/
	 * Check if User's /files/{$user_id}/ Directory exists, if not, create it
	 *
	 * @author IneX
	 * @version 1.0
	 * @since 1.0 `27.01.2016` `IneX` method added
	 */
	function get_and_create_user_files_dir($user_id)
	{
		$user_files_dir = FILES_DIR.$user_id.'/';//$files_dir.$user_id.'/';
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
	 * Check if User plays Games.
	 * Prüft ob der User-ID zum Beispiel Addle oder Schach spielt.
	 *
	 * @version 1.0
	 * @since 1.0 `04.01.2024` `IneX` Method added
	 *
	 * @param integer $user_id
	 * @param string $game_name Name of Game: «addle» or «chess». Default: addle
	 * @return boolean
	 */
	function userPlays($user_id, $game_name='addle')
	{
		global $db;

		/** Validte parameters */
		$allowedGames = ['addle', 'chess'];
		if (!is_string($game_name) || !in_array($game_name, $allowedGames)) return false;
		if (!is_numeric($user_id) || $user_id <= 0) return false;

		$query = $db->query('SELECT '.implode(',', $allowedGames).' FROM user WHERE id=? LIMIT 1', __FILE__, __LINE__, __METHOD__, [$user_id]);
		$result = $db->fetch($query);
		return ( boolval($result[$game_name]) ? true : false );
	}

	/**
	 * Get User Telegram Chat-ID
	 *
	 * Prüft ob der User-ID einen Telegram Messenger Chat-ID eingetragen hat
	 * -> wenn ja, wird die Telegram Chat-ID zurückgegeben
	 *
	 * @author IneX
	 * @version 1.0
	 * @since 1.0 `22.01.2017` `IneX` method added
	 *
	 * @param integer $user_id User-ID
	 * @return integer The User's Telegram Chat-ID
	 */
	function userHasTelegram($user_id)
	{
		global $db;

		/** Validte $user_id - valid integer & not empty/null */
		if (empty($user_id) || $user_id === NULL || $user_id <= 0) return false;

		$query = $db->query('SELECT telegram_chat_id tci FROM user WHERE id=? LIMIT 1', __FILE__, __LINE__, __METHOD__, [$user_id]);
		$result = $db->fetch($query);
		return ( $result ? $result['tci'] : false );
	}

	/**
	 * Password change
	 *
	 * Execute a password change for a User
	 *
	 * @author [z]biko
	 * @author IneX
	 * @version 4.0
	 * @since 1.0 function added
	 * @since 2.0 `03.10.2018` `IneX` function improved
	 * @since 3.0 `11.11.2018` `IneX` function moved to usersystem()-Class
	 * @since 4.0 `07.04.2021` `IneX` Updated to use modified usersystem::crypt_pw()
	 *
	 * @uses usersystem::crypt_pw()
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

		if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> exec_newpassword...', __METHOD__, __LINE__));
		if(!empty($old_pass) && !empty($new_pass) && !empty($new_pass2))
		{
			/** Check Hash of $old_pw against saved Hash */
			if ($this->verify_pw($old_pass, $this->userpw))
			{
				/** Check $new_pass was entered twice & identical */
				if($new_pass === $new_pass2)
				{
					/** Hash $new_pass for storing in DB */
					$crypted_new_pass = $this->crypt_pw($new_pass); // FINAL *Upgraded* Password Hash
					$this->userpw = $crypted_new_pass; // Store FINAL *new" Password Hash
					$result = $db->update('user', ['id', $user_id], ['userpw' => $crypted_new_pass], __FILE__, __LINE__, __METHOD__);
					if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> Final $this->userpw = %s', __METHOD__, __LINE__, $this->userpw));
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
	 * @author [z]biko
	 * @author IneX
	 * @version 3.0
	 * @since 1.0 function added
	 * @since 2.0 `02.10.2018` function improved to handle $_POST data dynamically
	 * @since 3.0 `11.11.2018` function moved to usersystem()-Class
	 *
	 * @uses check_email()
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

		if (check_email($data_array['email']) && !isset($_geaechtet[$user_id]))
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

					$defaultValue = isset(${'$this->default_'.strtolower($dataKey)})?${'$this->default_'.strtolower($dataKey)}:'';
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
				$sqlUpdateSetValuesArray[$dataKey] = (!is_array($dataValue) ? $dataValue : $dataValue);
			}

			/**
			 * Process regular Form-Checkbox values
			 */
			$data_array_checkbox_count = count($data_array['checkbox']);
			if (is_array($data_array['checkbox']) && $data_array_checkbox_count > 0) {
				for ($i=0;$i<$data_array_checkbox_count;$i++)
				{
					if (DEVELOPMENT === true && $data_array_checkbox_count > 0) error_log(sprintf('[DEBUG] <%s:%d> $data_array[checkbox]: <%d> %s => %s', __METHOD__, __LINE__, $data_array['checkbox'][$i], array_keys($data_array['checkbox'])[$i], array_values($data_array['checkbox'])[$i]));
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
				$sqlUpdateSetValuesArray['notifications'] = json_encode($data_array['notifications']);
			} elseif (!isset($data_array['notifications']) || empty($data_array['notifications'])) {
				$sqlUpdateSetValuesArray['notifications'] = NULL; // no change
			}
			if (is_array($data_array['forum_boards_unread']) && count($data_array['forum_boards_unread']) > 0) {
				$sqlUpdateSetValuesArray['forum_boards_unread'] = json_encode($data_array['forum_boards_unread']);
			} elseif (!isset($data_array['forum_boards_unread']) || empty($data_array['forum_boards_unread'])) {
				$sqlUpdateSetValuesArray['forum_boards_unread'] = $this->default_forum_boards_unread; // no change
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
	 * @author [z]biko
	 * @author IneX
	 * @version 4.0
	 * @since 1.0 function added
	 * @since 2.0 Userpic Archivierung eingebaut / IneX
	 * @since 3.0 `03.10.2018` function fixed and modernized
	 * @since 4.0 `11.11.2018` function moved to usersystem()-Class
	 *
	 * @uses createPic()
	 * @uses USER_IMGPATH, USER_IMGEXTENSION, USER_IMGPATH_ARCHIVE, UPLOAD_DIR
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
		$archiv = USER_IMGPATH_ARCHIVE.$user_id.'_'.$currtimestamp.USER_IMGEXTENSION; // (mit timestamp versehen, damits keine pics überschreibt
		$archiv_tn = USER_IMGPATH_ARCHIVE.$user_id.'_'.$currtimestamp.'_tn'.USER_IMGEXTENSION; // (mit timestamp versehen, damits keine pics überschreibt

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
		$tmpfile = UPLOAD_DIR.'userpics/'.$user_id.USER_IMGEXTENSION;
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
	 * @link https://github.com/zorgch/zorg-code/blob/master/www/actions/profil.php Logout-action is triggered through /actions/profil.php
	 *
	 * @author IneX
	 * @version 1.1
	 * @since 1.0 `11.11.2018` method added, code adapted from /actions/profil.php
	 * @since 1.1 `22.05.2021` SQL-insert DateTime is now created using `timestamp()`
	 *
	 * @uses usersystem::logout(), timestamp()
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
		// $lockout_jahr = $date_array['year'];
		// $lockout_monat = $date_array['month'];
		// $lockout_tag = $date_array['day'];
		// $lockout_stunde = $date_array['hour'];
		// $lockout_minute = $date_array['minute'];
		// $lockout_sekunde = $date_array['second'];
		// $lockout_date = sprintf('%d-%d-%d %d:%d:%d', $lockout_jahr, $lockout_monat, $lockout_tag, $lockout_stunde, $lockout_minute, $lockout_sekunde);
		$lockout_datetime = timestamp(true, [
											 'year' => $date_array['year']
											,'month' => $date_array['month']
											,'day' => $date_array['day']
											,'hour' => $date_array['hour']
											,'minute' => $date_array['minute']
											,'second' => $date_array['second']
										]);
		if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> $lockout_date: %s', __METHOD__, __LINE__, $lockout_datetime));

		/** User aussperren */
		$result = $db->update($this->table_name, ['id', $user_id], [$this->field_ausgesperrt_bis => $lockout_datetime], __FILE__, __LINE__, __METHOD__);
		if ($result !== false)
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

/** Static $_users Array to load & keep fetched Userdata while processing */
static $_users = array();

/** Ausgesperrte User werden geächtet! */
static $_geaechtet = array();

/**
 * LOGOUT machen.
 * Fun fact: wenn der NACH dem Login-Check kommt, dann wird man wieder eingeloggt...
 * ...weil dann die Cookies & Session noch nicht gekillt wurden ;)
 */
if (isset($_POST['logout']))
{
	/** exec the User logout */
	zorgDebugger::log()->debug('exec User logout');
	usersystem::logout();
} else {
	/** Instantiate a new usersystem Class */
	zorgDebugger::log()->debug('Instantiate new usersystem Class');
	$user = new usersystem();
}

/**
 * LOGIN mit Login-Formular
 */
if (isset($_POST['do']) && $_POST['do'] === 'login')
{
	zorgDebugger::log()->debug('exec User login (Form): %s', [print_r($_POST, true)]);
	if (!empty($_POST['username']) && !empty($_POST['password']))
	{
		$login_remember = filter_input(INPUT_POST, 'autologin', FILTER_DEFAULT, FILTER_REQUIRE_SCALAR) ?? false; // $_POST['autologin']
		$login_username = filter_input(INPUT_POST, 'username', FILTER_DEFAULT, FILTER_REQUIRE_SCALAR) ?? null; // $_POST['username']
		$login_password = (string)$_POST['password']; // No sanitization to prevent PW being modified vs. user input
		$auto = ($login_remember !== false && $login_remember === 'cookie' ? true : false); // User wants Autologin on/off?
		$login_error = $user->login($login_username, $login_password, $auto);
	} else {
		$login_error = t('authentication-empty', 'user');
	}
}
