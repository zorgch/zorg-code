<?php

class dbconn {
	var $host = "localhost";
	var $dbuser = "root";
	var $dbpass = "root";
	var $dbname;
	var $conn;
	
	var $noquerys = 0;
	var $noquerytracks = 0;
	var $nolog = 0;
	var $display_error = 1;
	var $query_track = array();
	
	/**
	* @return dbconn class
	* @param $dbname string
	* @desc Klassenkonstruktor
 	*/
	function dbconn($dbname="zooomclan") {
		$this->dbname = $dbname;
		//db: ersetzt durch pconnect: $this->conn = @mysql_connect($this->host,$this->dbuser,$this->dbpass);
		$this->conn = @mysql_connect($this->host,$this->dbuser,$this->dbpass);
		if(!$this->conn)
			die("<b>mySQL: Can't connect to server</b><br /><b></b>");
		if(!@mysql_select_db($this->dbname,$this->conn))
			die($this->msg());
	}

	/**
	* @return object resource or primarykey of insert
	* @param $sql string SQL
	* @param $file string Filename
	* @param $line int Linenumber
	* @desc F?hrt ein SQL-Query aus
 	*/
	function query ($sql, $file="", $line="", $funktion="") {
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
   	   $this->query_track[$qfile]["line $qline"]++;
	   }
	   
		$result = @mysql_query($sql, $this->conn);
      if (strtolower(substr($sql,0,7)) == "insert ") {
         return mysql_insert_id($this->conn);
	   }elseif (!$result && $this->display_error == 1) {
			die($this->msg($sql,$file,$line,$funktion));
		} else {
			return $result;
		}
	}

	/**
	* @return string html
	* @param $sql string SQL
	* @param $file string Filename
	* @param $line int Linenumber
	* @desc Gibt die Errormeldungen formatiert zur?ck
 	*/
	function msg($sql="",$file="",$line="",$funktion="") {
		$num = mysql_errno($this->conn);
		$msg = mysql_error($this->conn);
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
	* @return void
	* @param $msg string SQL-Error
	* @param $sql string SQL-Query
	* @param $file string Filename
	* @param $line int Linenumber
	* @desc Speichert SQL-Errors in der DB
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
		@mysql_query($sql,$this->conn);	
	}

	/**
	* @return array
	* @param $result object SQL-Resultat
	* @desc Fetcht ein SQL-Resultat in ein Array
 	*/
	function fetch($result) {
		global $sql;
		return @mysql_fetch_array($result);
	}

	/**
	* @return int
	* @desc gibt die letzte Autoincrement ID zur?ck
 	*/
	function lastid() {
		return @mysql_insert_id($this->conn);
	}
	
	/**
	* @return int numrows
	* @param $result object SQL-Resultat
	* @desc Gibt die Anzahl betroffener Datens?tze zur?ck
 	*/
	function num($result,$errorchk=TRUE) {
		return @mysql_num_rows($result);
	}
	
	/**
	* @return object
	* @param $result object SQL-Resultat
	* @param $rownum int Rownumber
	* @desc Setzt den Zeiger auf einen Datensatz
 	*/
	function seek($result,$rownum) {
		return @mysql_data_seek($result,$rownum);
	}

	/**
	* @return int 
	* @param $result object SQL-Resultat
	* @desc Gibt die Anzahl betroffener Felder zur?ck
 	*/
	function numfields($result) {
		return @mysql_num_fields($result);
	}
	
	/**
	* @return array
	* @desc Gibt s?mtliche Tabellennamen einer DB als Array zur?ck
 	*/
	function tables() {
		$tables = @mysql_list_tables($this->dbname,$this->conn);
		$num = $this->num($tables);
		$tab = array();
		for($i=0;$i<$num;$i++) {
			$tab[$i] = @mysql_tablename($tables,$i);
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
   * @param $table (String) Tabelle, in der ge?ndert werden soll
   * @param $id (Array) $id[0]: Name des Prim?rschl?sselfeldes / $id[1+] Rows, die ge?ndert werden sollen
   * @param $id (int) Row, die ge?ndert werden soll, nimmt Prim?rschl?sselfeld als 'id' an
   * @param $values (Array) Array mit Table-Feldern (als Key) und den Werten
   * @param $file (String) Datei des Aufrufes (optional, f?r Fehlermeldung)
   * @param $line (int) Zeile des Aufrufes (optional, f?r Fehlermeldung)
   * @desc ?ndert eine Row ein einer DB-Table, ?hnlich insert
   * @author biko
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

// Grad einen bauen damit er includet ist...
$db = new dbconn("zooomclan");

?>
