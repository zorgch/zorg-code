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
 * @include config.inc.php REQUIRED for $_ENV vars to be available
 * @include mysql_login.inc.local.php Include MySQL Database login information file
 */
require_once __DIR__.'/config.inc.php';

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
	var $display_error = (DEVELOPMENT === true ? 1 : 0);
	var $query_track = [];

	/**
	 * MySQL DB Verbindungsaufbau
	 *
	 * @version 5.0
	 * @since 3.0 `10.11.2017` `IneX` method code optimized
	 * @since 4.0 `03.11.2019` `kassiopaia` method renamed from dbconn() (PHP 7.x compatibility)
	 * @since 5.0 `28.12.2022` `IneX` method relies now on $_ENV vars from .env file (mysql_login.inc.php is no longer needed)
	 *
	 * @uses $_ENV
	 * @throws Exception
	 */
	public function __construct() {
		try {
			$this->conn = mysqli_connect($_ENV['MYSQL_HOST'], $_ENV['MYSQL_USER'], $_ENV['MYSQL_PASSWORD']);

			/** Enable mysqli Exception handling */
			mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

			/** MySQL: can't connect to server */
			if (!$this->conn)
			{
				header('Location: /error_static.html?cause=dbconn');
				exit;
			}

			/** MySQL: can't find or load database */
			if (!@mysqli_select_db($this->conn, $_ENV['MYSQL_DATABASE']))
			{
				die($this->msg());
			}

			mysqli_set_charset($this->conn, 'utf8mb4');
		}
		catch (Exception $e) {
			throw $e;
			exit;
		}
	}

	/**
	 * Führt ein SQL-Query aus
	 *
	 * @version 3.0
	 * @since 1.0 method added
	 * @since 2.0 `06.11.2018` `IneX` added mysql_affected_rows()-result for UPDATE-queries
	 * @since 2.1 `07.08.2019` `IneX` changed return mysql_insert_id() & mysql_affected_rows() to return row-id or true
	 * @since 3.0 `02.06.2023` `IneX` added support for mysqli prepared statements, helps mitigating SQL Injection risks (CWE-89)
	 *
	 * @param $sql string SQL-Query. Als Prepared Statement wird auch $params benötigt!
	 * @param $file string (Optional) Dateiname in welcher SQL-Query abgesetzt wurde
	 * @param $line int (Optional) Linenumber in der Datei welche SQL-Query abgesetzt hat
	 * @param $funktion string (Optional) Name der Funktion aus welcher SQL-Query abgesetzt wurde
	 * @param $params array (Optional) Parameter für Prepared SQL Statement
	 * @global object $user Globales Class-Object mit den User-Methoden & Variablen
	 * @return object|integer Query-Result-Resource or Primary-Key of INSERT
	*/
	function query($sql, $file='', $line=0, $funktion='', $params=[]) {
		global $user;

		/** Anzahl SQL-Queries (hoch-)zählen */
		$this->noquerys++;

		/** Query Infos in den SQL Query Tracker speichern */
		if (is_object($user) && isset($user->sql_tracker)) {
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
			$stmt = mysqli_prepare($this->conn, $sql);
			if ($stmt === false) throw new mysqli_sql_exception(mysqli_error($this->conn));

			/** Prepared Statement that needs binded $params */
			if (mysqli_stmt_param_count($stmt) > 0)
			{
				if (!empty($params))
				{
					$paramTypes = '';
					$bindParams = [$stmt, &$paramTypes];
					$paramCount = count($params);
					for ($i=0; $i<$paramCount; $i++)
					{
						switch (true)
						{
							case is_bool($params[$i]):
								$params[$i] = ($params[$i] ? 1 : 0); // 0 for FALSE, 1 for TRUE
								$paramTypes .= 'i';
								break;
							case is_int($params[$i]):
								$paramTypes .= 'i';
								break;
							case is_float($params[$i]):
								$paramTypes .= 'd';
								break;
							case is_null($params[$i]) || $params[$i] === '':
								$paramTypes .= 's';
								break;
							case is_string($params[$i]):
								$paramTypes .= 's';
								break;
							case is_resource($params[$i]):
								$paramTypes .= 'b'; // 'b' for BLOB type
								break;
							default:
								/** Handle unsupported or unknown types */
								$params[$i] = (string)$params[$i];
								$paramTypes .= 's';
								break;
						}
						$bindParams[] = &$params[$i];
					}
					/** The $bindParams Array contains the final $paramTypes, so mysqli_stmt_bind_param can bind the parameters */
					call_user_func_array('mysqli_stmt_bind_param', $bindParams);
				}
				/** Prepared Statement but missing $params */
				else {
					throw new mysqli_sql_exception(sprintf('Missing Parameters for Prepared Statement: %s<br>%s', ($needs_bindParams ? 'true' : 'false'), $sql));
				}
			}

			/** Execute SQL Prepared Statement */
			mysqli_stmt_execute($stmt);
			$result = mysqli_stmt_get_result($stmt);

			/** If the query failed or no rows were returned, display MySQL-Error with some context */
			if ($result === false && mysqli_num_rows($result) === 0 && mysqli_affected_rows($this->conn) === 0)
			{
				if ($this->display_error === 1) {
					throw new mysqli_sql_exception($this->msg($sql, $file, $line, $funktion));
				} else {
					$this->msg($sql, $file, $line, $funktion);
					throw new mysqli_sql_exception('SQL query error in '.$file.' at line '.$line);
				}
			} else {
				/** Retrieve and return a more valuable information, depending on the SQL-query type */
				switch (strtolower(substr($sql, 0, 6)))
				{
					case 'insert':
						/** mysqli_insert_id() returns a string representation of the last inserted ID, or 0/false on failure */
						$sql_insert_id = (int)mysqli_insert_id($this->conn);
						return $sql_insert_id;
					case 'select':
						return $result;
					default:
						/** mysqli_affected_rows() returns the number of affected rows as an integer, or -1 on failure */
						$sql_affected_rows = (int)mysqli_affected_rows($this->conn);
						return ($sql_affected_rows !== -1 ? $sql_affected_rows : false);
				}
			}
		} catch (mysqli_sql_exception $e) {
			if (DEVELOPMENT === true) var_dump([$file, $funktion, $line, $sql, $params]);
			die($e->getMessage());
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
		$num = mysqli_errno($this->conn);
		$msg = mysqli_error($this->conn);
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
		@mysqli_query($this->conn, $sql);
	}

	/**
	 * Fetcht ein SQL-Resultat in ein Array
	 *
	 * @version 2.0
	 * @since 1.0 method added
	 * @since 2.0 `02.06.2023` `IneX` Use MYSQLI_ASSOC with mysqli_fetch_array() to fetch result as an associative array
	 *
	 * @param $result array|null|false SQL-Resultat
	 * @return array|false Array containing fetched data or false on failure
	 */
	function fetch($result) {
		global $sql; // notwendig??
		if ($result !== false) {
			return @mysqli_fetch_array($result, MYSQLI_ASSOC);
		}
		return false;
	}

	/**
	 * gibt die letzte Autoincrement ID zurück.
	 * @return int|string Returns an Integer (can also be '0') or number as a String if greater than max. int value
	 */
	function lastid() {
		return @mysqli_insert_id($this->conn);
	}

	/**
	 * Gibt die Anzahl betroffener Datensätze zurück.
	 * @return int numrows
	 * @param $result int|string Returns an Integer of the number of fetched rows. Returns 0 if unbuffered. String if rows greater than PHP_INT_MAX.
	 */
	function num($result,$errorchk=TRUE) {
		return @mysqli_num_rows($result);
	}

	/**
	 * Setzt den Zeiger auf einen Datensatz.
	 * @return object
	 * @param $result object SQL-Resultat
	 * @param $rownum int Rownumber
	 */
	function seek($result,$rownum) {
		return @mysqli_data_seek($result, $rownum);
	}

	/**
	 * Gibt die Anzahl betroffener Felder zurück.
	 * @return int
	 * @param $result object SQL-Resultat
	 */
	function numfields($result) {
		return @mysqli_field_count($this->conn);
	}

	/**
	 * Gibt sämtliche Tabellennamen einer DB als Array zurück.
	 * @return array
	 */
	function tables() {
		$tables = @mysqli_list_tables($this->conn, 'SHOW TABLES FROM ' . $_ENV['MYSQL_DATABASE']);
		$num = $this->num($tables);
		$tab = array();
		for($i=0;$i<$num;$i++) {
			@mysqli_data_seek($tables,$i);
			$f = mysql_fetch_array($tables);
			$tab[$i] = $f[0];
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
			//user_error('Wrong Parameter type '.$values.' in db->insert()', E_USER_ERROR);
			die('Wrong Parameter type '.$values.' in db->insert()');
		}

		/** Prepare INSERT-Statement */
		$insertKeys = '(`'.implode('`,`', array_keys($values)).'`)';
		$insertValues = '("'.implode('","', $values).'")';
		$insertValues = str_replace('"NOW()"', 'NOW()', $insertValues); // Fix "NOW()" => NOW() without quotes
		$insertValues = str_replace('"NULL"', 'NULL', $insertValues); // Fix "NULL" => NULL without quotes
		if (DEVELOPMENT === true) error_log(sprintf('[DEBUG] <%s:%d> Clean $insertValues: %s', __METHOD__, __LINE__, $insertValues));
		$sql = sprintf('INSERT INTO `%s` %s VALUES %s', $table, $insertKeys, $insertValues);
		if (DEVELOPMENT === true) error_log(sprintf('[DEBUG] <%s:%d> $db->insert() query: %s', __METHOD__, __LINE__, $sql));
		return $this->query($sql, $file, $line, $funktion);
	}

	/**
	 * Ändert eine Row ein einer DB-Table, ähnlich insert
	 *
	 * @author [z]biko
	 * @version 3.1
	 * @since 1.0 method added
	 * @since 1.1 `10.11.2017` `IneX` added 3rd optional parameter $funktion for better logging
	 * @since 2.0 `20.08.2018` `IneX` added return as mysql_affected_rows()
	 * @since 3.0 `05.11.2018` `IneX` fixed iteration for $id (WHERE x=y) building, depending if array or integer is provided
	 * @since 3.0 `02.06.2023` `IneX` added compatibility with mysqli prepared statements
	 *
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
			if ((empty($val) || $val === null || strtolower($val) === 'null') && $val !== 0 && $val !== '0' && $val !== '') {
				$sql .= $key.'=?';//'=NULL'; // handle NULL
				$params[] = null;
			}
			elseif (strtolower($val) === 'now()') {
				$sql .= $key.'=NOW()'; // handle NOW()
				//$params[] = 'NOW()'; --> string 'NOW()' breaks DateTime column inserts!
			}
			elseif (is_numeric($val) && strlen((string)$val) === 10) {
				$sql .= $key.'=?';//'='.$val; // handle Timestamps
				$params[] = $val;
			}
			else {
				$sql .= $key.'=?';//'="'.$val.'"';
				$params[] = $val;
			}
			end($values); // Add Separator if not last Array-Iteration (https://stackoverflow.com/a/8780881/5750030)
			if ($key !== key($values)) $sql .= ', ';
		}

		/** Build 'WHERE n=o OR y=x' */
		$sql .= ' WHERE ';
		if (!is_array($id))
		{
			$sql .= 'id=?';//.$id;
			$params[] = $id;
		} else {
			/** Convert array('id',1,'name','Barbara Harris,...) => associative Array key=>value */
			for ($i=0;$i<count($id);$i++)
			{
				//$conditions = array_map(function($kva){return [$kva[0] => $kva[1]];}, $id);
				$conditions[$id[$i]] = $id[$i+1]; // map $id[0] => $id[1], $id[2] => $id[3],... to $conditions-Array
				$i++;
			}
			//if (DEVELOPMENT === true) error_log(sprintf('[DEBUG] <%s:%d> $db->update() $conditions[ %s ]', __METHOD__, __LINE__, print_r($conditions,true)));
			foreach ($conditions as $field => $value) {
				$sql .= $field.'=?';//.(is_numeric($value) ? $value : '"'.$value.'"');
				$params[] = $value;
				end($conditions); // @link https://stackoverflow.com/a/8780881/5750030
				if ($field !== key($conditions)) $sql .= ' OR ';  // Add Separator if not last Array-Iteration
			}
		}
		if (DEVELOPMENT === true) error_log(sprintf('[DEBUG] <%s:%d> $db->update() $sql: %s', __METHOD__, __LINE__, $sql));
		return $this->query($sql, $file, $line, $funktion, $params);
		//return mysql_affected_rows();
	}
}

/** Grad eine Verbindung bauen, damit sie includet ist... */
$db = new dbconn();
