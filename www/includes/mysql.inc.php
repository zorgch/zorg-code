<?php
/**
 * Include MySQL Database login information file
 *
 * Wenn lokal entwickelt wird, muss manuell eine Kopie der DB-Info-Datei mit folgendem Namen angelegt werden:
 * "mysql_login.inc.local.php"
 */
require_once( (file_exists($_SERVER['DOCUMENT_ROOT'].'/includes/mysql_login.inc.local.php') ? 'mysql_login.inc.local.php' : 'mysql_login.inc.php') );

/**
 * MySQL Database Connection Class
 *
 * @package Zorg
 * @subpackage MySQL
 */
class dbconn {

	var $conn;
	var $noquerys = 0;
	var $noquerytracks = 0;
	var $nolog = 0;
	var $display_error = 1;
	var $query_track = array();

	/**
	* Verbindungsaufbau
	*
	* @author IneX
	* @date 10.11.2017
	* @version 3.0
	*
	* @param MYSQL_DBNAME string
 	*/
	function dbconn($database) {
		//$this->dbname = $dbname;
		//db: ersetzt durch pconnect: $this->conn = @mysql_connect($this->host,$this->dbuser,$this->dbpass);
		try {
			$this->conn = @mysql_connect(MYSQL_HOST, MYSQL_DBUSER, MYSQL_DBPASS); // DEPRECATED - PHP5 only
			//$this->conn = @mysqli_connect( MYSQL_HOST, MYSQL_DBUSER, MYSQL_DBPASS, $database); // PHP7.x ready
			if(!$this->conn)
				header("Location: ".SITE_URL."/error_static.html");
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
	* @return object resource or primarykey of insert
	* @param $sql string SQL
	* @param $file string Filename
	* @param $line int Linenumber
 	*/
	function query ($sql, $file="", $line=0, $funktion="") {
	   global $user;

	   $this->noquerys++;

	   if ($user && $user->sql_tracker) {
         $this->noquerytracks++;
	      $qfile = $file;
   	   $qline = $line;
   	   if (!$qfile) $qfile = "?";
   	   if (is_object($qfile)) $qfile = "?";  // weil irgend jemand auf die idee kam, ein object zu ?bergeben (tststs)
   	   if (!$qline) $qline = "?";
   	   if (!isset($this->query_track[$qfile])) $this->query_track[$qfile] = array();
   	   $this->query_track[$qfile]['line '.$qline]++;
	   }

		$result = @mysql_query($sql, $this->conn); // DEPRECATED - PHP5 only
		//$result = @mysqli_query($this->conn, $sql); // PHP7.x ready
      if (strtolower(substr($sql,0,7)) == "insert ") {
         return mysql_insert_id($this->conn);
	   }elseif (!$result && $this->display_error == 1) {
			die($this->msg($sql,$file,$line,$funktion));
		} else {
			return $result;
		}
	}

	/**
	* Gibt die Errormeldungen formatiert zur?ck
	*
	* @return string html
	* @param $sql string SQL
	* @param $file string Filename
	* @param $line int Linenumber
 	*/
	function msg($sql="",$file="",$line="",$funktion="") {
		$num = mysql_errno($this->conn); // DEPRECATED - PHP5 only
		$msg = mysql_error($this->conn); // DEPRECATED - PHP5 only
		//$num = mysqli_errno($this->conn); // PHP7.x ready
		//$msg = mysqli_errno($this->conn); // PHP7.x ready
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
	* @return void
	* @param $msg string SQL-Error
	* @param $sql string SQL-Query
	* @param $file string Filename
	* @param $line int Linenumber
 	*/
	function saveerror($msg,$sql,$file="",$line="",$funktion="") {
		$msg = addslashes($msg);
		$sql = addslashes($sql);
		$sql = "
		INSERT
			into sql_error
			(user_id, ip, page, query, msg, date, file, line, referer, status, function)
			VALUES
			('".$_SESSION['user_id']."','".$_SERVER['REMOTE_ADDR']."',
			'".$_SERVER['REQUEST_URI']."','$sql', '$msg', now(),
			'$file', '$line', '".$_SERVER['HTTP_REFERER']."',1, '$funktion')";
		@mysql_query($sql,$this->conn); // DEPRECATED - PHP5 only
		//@mysqli_query($sql,$this->conn); // PHP7.x ready
	}

	/**
	* @return array
	* @param $result object SQL-Resultat
	* @desc Fetcht ein SQL-Resultat in ein Array
 	*/
	function fetch($result) {
		global $sql;
		return @mysql_fetch_array($result); // DEPRECATED - PHP5 only
		//return @mysqli_fetch_array($result); // PHP7.x ready
	}

	/**
	* @return int
	* @desc gibt die letzte Autoincrement ID zur?ck
 	*/
	function lastid() {
		return @mysql_insert_id($this->conn); // DEPRECATED - PHP5 only
		//return @mysqli_insert_id($this->conn); // PHP7.x ready
	}

	/**
	* @return int numrows
	* @param $result object SQL-Resultat
	* @desc Gibt die Anzahl betroffener Datens?tze zur?ck
 	*/
	function num($result,$errorchk=TRUE) {
		return @mysql_num_rows($result); // DEPRECATED - PHP5 only
		//return @mysqli_num_rows($result); // PHP7.x ready
	}

	/**
	* @return object
	* @param $result object SQL-Resultat
	* @param $rownum int Rownumber
	* @desc Setzt den Zeiger auf einen Datensatz
 	*/
	function seek($result,$rownum) {
		return @mysql_data_seek($result,$rownum); // DEPRECATED - PHP5 only
		//return @mysqli_data_seek($result, $rownum); // PHP7.x ready
	}

	/**
	* @return int
	* @param $result object SQL-Resultat
	* @desc Gibt die Anzahl betroffener Felder zur?ck
 	*/
	function numfields($result) {
		return @mysql_num_fields($result); // DEPRECATED - PHP5 only
		//return @mysqli_field_count($this->conn); // PHP7.x ready
	}

	/**
	* @return array
	* @desc Gibt s?mtliche Tabellennamen einer DB als Array zur?ck
 	*/
	function tables() {
		$tables = @mysql_list_tables(MYSQL_DBNAME, $this->conn); // DEPRECATED - PHP5 only
		//$tables = @mysqli_list_tables($this->conn, 'SHOW TABLES FROM ' . MYSQL_DBNAME); // PHP7.x ready
		$num = $this->num($tables);
		$tab = array();
		for($i=0;$i<$num;$i++) {
			$tab[$i] = @mysql_tablename($tables,$i); // DEPRECATED - PHP5 only
			//@mysqli_data_seek($tables,$i); // PHP7.x ready
			//$f = mysql_fetch_array($tables); // PHP7.x ready
			//$tab[$i] = $f[0]; // PHP7.x ready
		}
		return $tab;
	}

   /**
   * @return Prim?rschl?ssel des neuen Eintrags
   * @param $table (String) Tabelle, in die eingef?gt werden soll
   * @param $values (Array) Array mit Table-Feldern (als Key) und den Werten
   * @param $file (String) Datei des Aufrufes (optional, f?r Fehlermeldung)
   * @param $line (int) Zeile des Aufrufes (optional, f?r Fehlermeldung)
   * @desc F?gt eine neue Row anhand eines assoziativen Arrays in eine DB-Table. Die Keys des Arrays entsprechen den Feldnamen
   * @author biko
   */
   function insert($table, $values, $file="", $line="") {
      if (!is_array($values)) {
      	user_error('Wrong Parameter type '.$values.' in db->insert()', E_USER_ERROR);
      }

      $sql =
      	"INSERT INTO ".$table." ("
      	.implode(",", array_keys($values)).") VALUES ('"
      	.implode("','", $values)."')"
      ;
      $id = $this->query($sql, $file, $line);
      return $id;
   }

   /**
   * Ändert eine Row ein einer DB-Table, ähnlich insert
   *
   * @author biko
   * @param $table (String) Tabelle, in der ge?ndert werden soll
   * @param $id (Array) $id[0]: Name des Prim?rschl?sselfeldes / $id[1+] Rows, die ge?ndert werden sollen
   * @param $id (int) Row, die ge?ndert werden soll, nimmt Prim?rschl?sselfeld als 'id' an
   * @param $values (Array) Array mit Table-Feldern (als Key) und den Werten
   * @param $file (String) Datei des Aufrufes (optional, f?r Fehlermeldung)
   * @param $line (int) Zeile des Aufrufes (optional, f?r Fehlermeldung)
   */
   function update($table, $id, $values, $file="", $line="") {
      if (!is_array($values)) {
         echo "Wrong Parameter type $values in db->insert()";
         exit;
      }
      if (!is_array($id)) {
         $tmp = $id;
         $id = array("id", $tmp);
      }

      $sql = "UPDATE ".$table." SET ";
      foreach ($values as $key => $val) {
         $sql .= $key."='".$val."', ";
      }
      $sql = substr($sql, 0, -2);
      $sql .= " WHERE ";
      for ($i=1; $i<sizeof($id); $i++) {
         $sql .= $id[0]."='".$id[$i]."' OR ";
      }
      $sql = substr($sql, 0, -4);
      $this->query($sql, $file, $line);
   }
}

// Grad eine Verbindung bauen, damit sie includet ist...
$db = new dbconn(MYSQL_DBNAME);
