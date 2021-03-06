<?php
/**
 * MySQL-Authentifizierung laden
 *
 * Wenn lokal entwickelt wird, muss manuell eine Kopie
 * der DB-Info-Datei mit folgendem Namen angelegt werden:
 *	  mysql_login.inc.local.php
 *
 * @package zorg\Database
 */
/**
 * File includes
 * @include mysql_login.inc.local.php Include MySQL Database login information file
 * @include config.inc.php
 */
require_once dirname(__FILE__).'/config.inc.php';
require_once INCLUDES_DIR.( file_exists( INCLUDES_DIR.'mysql_login.inc.local.php') ? 'mysql_login.inc.local.php' : 'mysql_login.inc.php') ;

/**
 * MySQL Database Connection Class
 *
 * @package zorg\Database\MySQL
 */
class dbconn
{
	var $conn;
	var $noquerys = 0;
	var $noquerytracks = 0;
	var $nolog = 0;
	var $display_error = 1;
	var $query_track = array();

	/**
	 * dbconn constructor.
	 *
	 * @version 1.0
	 * @since 1.0 `03.11.2019` `kassiopaia` method added
	 *
	 * @param $database
	 * @throws Exception
	 */
	public function __construct($database) {
		try {
			$this->conn = mysqli_connect(MYSQL_HOST, MYSQL_DBUSER, MYSQL_DBPASS); // PHP7.x ready
			if(!$this->conn)
				header('Location: '.SITE_URL.'/error_static.html');
			//die("MySQL: can't connect to server");
			if(!@mysqli_select_db($this->conn, $database)) // PHP7.x ready
				die($this->msg());
			mysqli_set_charset($this->conn, 'utf8mb4'); // PHP7.x ready
		}
		catch (Exception $e) {
			throw $e;
		}
	}

	/**
	 * MySQL DB Verbindungsaufbau
	 *
	 * @version 3.0
	 * @since 3.0 `10.11.2017` `IneX` method code optimized
	 *
	 * @TODO kassiopaia: mysql_select_charset() & $this->conn() müssen noch => php7.x ready gemacht werden?
	 *
	 * @param string MYSQL_DBNAME
	 */
	function dbconn($database) {
		//$this->dbname = $dbname;
		//db: ersetzt durch pconnect: $this->conn = @mysql_connect($this->host,$this->dbuser,$this->dbpass);
		try {
			$this->conn = mysql_connect(MYSQL_HOST, MYSQL_DBUSER, MYSQL_DBPASS); // DEPRECATED - PHP5 only
			//$this->conn = @mysqli_connect( MYSQL_HOST, MYSQL_DBUSER, MYSQL_DBPASS, $database); // PHP7.x ready
			if(!$this->conn)
				header('Location: '.SITE_URL.'/error_static.html');
				//die("MySQL: can't connect to server");
			if(!@mysql_select_db($database, $this->conn)) // DEPRECATED - PHP5 only
				die($this->msg());
			mysql_set_charset('utf8mb4', $this->conn); // DEPRECATED - PHP5 only
			//mysqli_set_charset($this->conn, 'utf8mb4'); // PHP7.x ready
		}
		catch (Exception $e) {
			throw $e;
		}
	}

	/**
	 * Führt ein SQL-Query aus
	 *
	 * @version 2.1
	 * @since 1.0 method added
	 * @since 2.0 `06.11.2018` `IneX` added mysql_affected_rows()-result for UPDATE-queries
	 * @since 2.1 `07.08.2019` `IneX` changed return mysql_insert_id() & mysql_affected_rows() to return row-id or true
	 *
	 * @param $sql string SQL
	 * @param $file string Filename
	 * @param $line int Linenumber
	 * @return object|integer Query-Result-Resource or Primary-Key of INSERT
	*/
	function query($sql, $file='', $line=0, $funktion='') {
		global $user;

		$this->noquerys++;

		if ($user && isset($user->sql_tracker)) {
			$this->noquerytracks++;
			$qfile = $file;
			$qline = $line;
			if (!$qfile) $qfile = '?';
			if (is_object($qfile)) $qfile = '?';  // weil irgend jemand auf die idee kam, ein object zu übergeben (tststs)
			if (!$qline) $qline = '?';
			if (!isset($this->query_track[$qfile])) $this->query_track[$qfile] = array();
			if(isset($this->query_track[$qfile]['line '.$qline])) {
				$this->query_track[$qfile]['line '.$qline]++;
			}
		}

		try {
			$result = mysqli_query($this->conn, $sql); // DEPRECATED - PHP5 only
			$sql_query_type = strtolower(substr($sql,0,6)); // first 6 chars of $sql = e.g. INSERT or UPDATE
			if ($sql_query_type == 'insert') {
				$sql_insert_id = mysqli_insert_id($this->conn);
				return (is_numeric($sql_insert_id) && $sql_insert_id !== 0 ? $sql_insert_id : ($sql_insert_id !== false ? true : false));
			} elseif ($sql_query_type == 'update') {
				$sql_affected_rows = mysqli_affected_rows($this->conn);
				return (is_numeric($sql_affected_rows) && $sql_affected_rows !== 0 ? $sql_affected_rows : ($sql_affected_rows !== false ? true : false));
			} elseif ($result === false && $this->display_error == 1) {
				/** Display MySQL-Error with context */
				die($this->msg($sql,$file,$line,$funktion));
			} else {
				return $result;
			}
		} catch (MySQLException $e) {
			user_error($e->getMessage(), E_USER_ERROR);
		}
	}

	/**
	 * Gibt die Errormeldungen formatiert zurück
	 *
	 * @return string html
	 * @param $sql string SQL
	 * @param $file string Filename
	 * @param $line int Linenumber
	 */
	function msg($sql='',$file='',$line='',$funktion='')
	{
		$num = mysqli_errno($this->conn); // PHP7.x ready
		$msg = mysqli_error($this->conn); // PHP7.x ready
		$ausg = "<table cellpadding='5' align='center' cellspacing='0' bgcolor='#FFFFFF' width='800' style='font-family: verdana; font-size:12px; color:black;'>
		<tr><td align='center' width='800' colspan='2'
		style='border-bottom-style:solid; border-bottom-color:#000000; border-bottom-width:1px;'>
		<b>MySQL Error: <b>".$num."</b><br>".$msg
		."</td></tr><td align='left' valign='top'><b>SQL-Query:</b> </td><td align='left' width='700'><xmp>"
		.$sql.
		"</xmp></td></tr><tr><td align='left' valign='top'><b>FILE:</b> </td><td align='left'>".$file."
		</td></tr><tr><td align='left' valign='top'><b>Line:</b> </td><td align='left'>".$line."
		</td></tr><tr><td align='left' valign='top'><b>Function:</b> </td><td align='left'>".$funktion."
		</td></tr></table>";
		if($this->nolog == 0) {
			$this->saveerror($msg,$sql,$file,$line,$funktion);
		}
		return $ausg;
	}

	/**
	 * Speichert SQL-Errors in der DB
	 *
	 * @version 1.1
	 * @since 1.0 method added
	 * @since 1.1 `04.12.2020` `IneX` Fixed PHP Notice undefined index
	 *
	 * @return void
	 * @param $msg string SQL-Error
	 * @param $sql string SQL-Query
	 * @param $file string Filename
	 * @param $line int Linenumber
	 */
	function saveerror($msg, $sql, $file='', $line=0, $funktion='') {
		$msg = addslashes($msg);
		$sql = addslashes($sql);
		$sql = sprintf('INSERT
							 into sql_error
							 (user_id, ip, page, query, msg, date, file, line, referer, status, function)
						VALUES
							 (%d, "%s", "%s","%s", "%s", NOW(), "%s", %d, "%s", 1, "%s")',
						(isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0),
						(isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : ''),
						(isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : ''),
						$sql,
						$msg,
						$file,
						$line,
						(isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '' ),
						$funktion
					);
		@mysqli_query($sql,$this->conn); // PHP7.x ready
	}

	/**
	 * Fetcht ein SQL-Resultat in ein Array
	 *
	 * @TODO add 2nd param for MYSQLI_ASSOC feature? See e.g. /js/ajax/get-userpic.php
	 *
	 * @return array
	 * @param $result object SQL-Resultat
	 */
	function fetch($result) {
		global $sql; // notwendig??
		return @mysqli_fetch_array($result); // PHP7.x ready
	}

	/**
	 * gibt die letzte Autoincrement ID zurück.
	 * @return int
	 */
	function lastid() {
		return @mysqli_insert_id($this->conn); // PHP7.x ready
	}

	/**
	 * Gibt die Anzahl betroffener Datensätze zurück.
	 * @return int numrows
	 * @param $result object SQL-Resultat
	 */
	function num($result,$errorchk=TRUE) {
		return @mysqli_num_rows($result); // PHP7.x ready
	}

	/**
	 * Setzt den Zeiger auf einen Datensatz.
	 * @return object
	 * @param $result object SQL-Resultat
	 * @param $rownum int Rownumber
	 */
	function seek($result,$rownum) {
		return @mysqli_data_seek($result, $rownum); // PHP7.x ready
	}

	/**
	 * Gibt die Anzahl betroffener Felder zurück.
	 * @return int
	 * @param $result object SQL-Resultat
	 */
	function numfields($result) {
		return @mysqli_field_count($this->conn); // PHP7.x ready
	}

	/**
	 * Gibt sämtliche Tabellennamen einer DB als Array zurück.
	 * @return array
	 */
	function tables() {
		//$tables = @mysql_list_tables(MYSQL_DBNAME, $this->conn); // DEPRECATED - PHP5 only
		$tables = @mysqli_list_tables($this->conn, 'SHOW TABLES FROM ' . MYSQL_DBNAME); // PHP7.x ready
		$num = $this->num($tables);
		$tab = array();
		for($i=0;$i<$num;$i++) {
			@mysqli_data_seek($tables,$i); // PHP7.x ready
			$f = mysql_fetch_array($tables); // PHP7.x ready
			$tab[$i] = $f[0]; // PHP7.x ready
		}
		return $tab;
	}

	/**
	 * Fügt eine neue Row anhand eines assoziativen Arrays in eine DB-Table. Die Keys des Arrays entsprechen den Feldnamen
	 *
	 * @author [z]biko
	 * @version 2.5
	 * @since 1.0 method added
	 * @since 2.0 `26.05.2019` `IneX` improved code, additional parameter and logging
	 * @since 2.5 `27.09.2019` `IneX` added fix for "NOW()" instead of NOW()
	 *
	 * @param string $table Tabelle, in die eingefügt werden soll
	 * @param array $values Array mit Table-Feldern (als Key) und den Werten
	 * @param string $file (optinal) Datei des Aufrufes (optional, für Fehlermeldung)
	 * @param int $line (optinal) Zeile des Aufrufes (optional, für Fehlermeldung)
	 * @param string $funktion (optional) Funktion wo der Aufruf stattfand, für Fehlermeldung
	 * @return Primärschlüssel des neuen Eintrags
	 */
	function insert($table, $values, $file='', $line=0, $funktion=null)
	{
		if (!is_array($values))
		{
			error_log(sprintf('[ERROR] <%s:%d> db->insert() Wrong Parameter type: %s', __METHOD__, __LINE__, $values));
			user_error('Wrong Parameter type '.$values.' in db->insert()', E_USER_ERROR);
		}

		/** Prepare INSERT-Statement */
		$insertKeys = '(`'.implode('`,`', array_keys($values)).'`)';
		$insertValues = '("'.implode('","', $values).'")';
		$insertValues = str_replace('"NOW()"', 'NOW()', $insertValues); // Fix "NOW()" => NOW() without quotes
		$insertValues = str_replace('"NULL"', 'NULL', $insertValues); // Fix "NULL" => NULL without quotes
		if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> Clean $insertValues: %s', __METHOD__, __LINE__, $insertValues));
		$sql = sprintf('INSERT INTO `%s` %s VALUES %s', $table, $insertKeys, $insertValues);
		if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> $db->insert() query: %s', __METHOD__, __LINE__, $sql));
		return $this->query($sql, $file, $line, $funktion);
	}

	/**
	 * Ändert eine Row ein einer DB-Table, ähnlich insert
	 *
	 * @author [z]biko
	 * @version 3.0
	 * @since 1.0 method added
	 * @since 1.1 `10.11.2017` added 3rd optional parameter $funktion for better logging
	 * @since 2.0 `20.08.2018` added return as mysql_affected_rows()
	 * @since 3.0 `05.11.2018` fixed iteration for $id (WHERE x=y) building, depending if array or integer is provided
	 *
	 * @FIXME nicht PHP7.x-kompatibel
	 * @FIXME array($id) soll nicht key,value-Pairs parsen, sondern direkt der Vergleich (z.B. "id>2"), aktuell kann nur auf 1 name & mehrere exakte values geprüft werden: "a=b OR a=c"
	 * @TODO change all usages of $db->update to pass associative array elements, like 'name'=>'Barbara Harris'.
	 *
	 * @param string $table Name der Tabelle, in der geändert werden soll
	 * @param array|int $id Array: $id[0]: Name des Primärschlüsselfeldes + $id[1+] Rows, die geändert werden sollen | bei Integer: Row, die geändert werden soll, nimmt Primärschlüsselfeld als 'id' an
	 * @param array $values Array mit Table-Feldern (als Key) und den Werten (als Values), z.B. 'name'=>'value' oder 'name'=>23
	 * @param string $file (optional) Datei des Aufrufes, für Fehlermeldung
	 * @param int $line (optional) Zeile des Aufrufes, für Fehlermeldung
	 * @param string $funktion (optional) Funktion wo der Aufruf stattfand, für Fehlermeldung
	 * @return integer|boolean Anzahl der geänderten Table-Rows des Update Queries - oder FALSE bei Fehler
	*/
	function update($table, $id, $values, $file='', $line='', $funktion='')
	{
		if (empty($values) || !is_array($values)) {
			error_log(sprintf('Wrong Parameter type "values" in db->update(): %s', print_r($values,true)));
			return false;
		}

		/** Build 'UPDATE a SET b=c, d=e, ...' */
		$sql = 'UPDATE '.$table.' SET ';
		foreach ($values as $key => $val) {
			if ((empty($val) || $val === null || strtolower($val) === 'null') && $val !== 0 && $val !== '0' && $val !== '') $sql .= $key.'=NULL'; // handle NULL
			elseif (strtolower($val) === 'now()') $sql .= $key.'=NOW()'; // handle NOW()
			elseif (is_numeric($val) && strlen((string)$val) === 10) $sql .= $key.'='.$val; // handle Timestamps
			else $sql .= $key.'="'.$val.'"';
			end($values); // @link https://stackoverflow.com/a/8780881/5750030 Add Separator if not last Array-Iteration
			if ($key !== key($values)) $sql .= ', ';
		}

		/** Build 'WHERE n=o OR y=x' */
		$sql .= ' WHERE ';
		if (!is_array($id))
		{
			$sql .= 'id='.$id;
		} else {
			/** Convert array('id',1,'name','Barbara Harris,...) => associative Array key=>value */
			for ($i=0;$i<count($id);$i++)
			{
				//$conditions = array_map(function($kva){return [$kva[0] => $kva[1]];}, $id);
				$conditions[$id[$i]] = $id[$i+1]; // map $id[0] => $id[1], $id[2] => $id[3],... to $conditions-Array
				$i++;
			}
			//if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> $db->update() $conditions[ %s ]', __METHOD__, __LINE__, print_r($conditions,true)));
			foreach ($conditions as $field => $value) {
				$sql .= $field.'='.(is_numeric($value) ? $value : '"'.$value.'"');
				end($conditions); // @link https://stackoverflow.com/a/8780881/5750030
				if ($field !== key($conditions)) $sql .= ' OR ';  // Add Separator if not last Array-Iteration
			}
		}
		if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> $db->update() $sql: %s', __METHOD__, __LINE__, $sql));
		return $this->query($sql, $file, $line, $funktion);
		//return mysql_affected_rows();
	}
}

/** Grad eine Verbindung bauen, damit sie includet ist... */
$db = new dbconn(MYSQL_DBNAME);
