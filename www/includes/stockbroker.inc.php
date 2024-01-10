<?php
/**
 * Stockbroker Funktionen
 * @package zorg\Games\Stockbroker
 */
/**
 * File includes
 * @include config.inc.php required
 * @include mysql.inc.php required
 */
require_once __DIR__.'/config.inc.php';
require_once INCLUDES_DIR.'mysql.inc.php';

//set_time_limit(20);

/**
 * Stockbroker Class
 *
 * @package zorg
 * @subpackage Stockbroker
 * @version 1.0
 * @since 1.0 `[z]milamber` Class added
 */
class Stockbroker
{
	/**
	 * @param integer $user_id
	 * @param string $symbol
	 * @param integer $menge
	 * @param boolean $max
	 */
	function buyStock($user_id, $symbol, $menge, $max) {
		global $db;

		if($user_id < 1) {
			echo '$user_id ist ungültig';
			return false;
		}

		if(!isset($symbol)) {
			echo '$_POST[\'symbol\'] ist nicht gesetzt.';
			return false;
		}

		if($menge < 1 && !$max) {
			echo 'Du musst eine Menge grösser als 0 festlegen. ('.$menge.') oder max setzen.';
			return false;
		}

		$symbol = strtoupper($symbol); // müsste eigentlich nicht hier sein, aber um sicher zu gehen...

		// neuen Preis grabben
		self::updateKurs($symbol);

		// Kurs holen
		$kurs = self::getKurs($symbol);

		if(!is_numeric($kurs)) {
			echo 'Konnte keinen Kurs nicht finden für '.$_POST['symbol'];
			return false;
		}

		if($max) {
			$menge = floor(self::getBargeld($user_id)/$kurs);
		} else if(self::getBargeld($user_id) < ($menge * $kurs)) {
			echo 'Du hast gar nicht soviel Geld! ('.self::getBargeld($user_id).' < '.($menge * $kurs).')';
			return false;
		}

		// Handel vollziehen --------------------------------------------------------
		$sql = 'INSERT INTO
					stock_trades (tag, zeit, user_id, symbol, menge, action, kurs)
				VALUES
					(?, ?, ?, ?, ?,"buy", ?)';
		$db->query($sql, __FILE__, __LINE__, __METHOD__, [timestamp(true), timestamp(true), $user_id, $symbol, $menge, $kurs]);

		return true;
	}

	/**
	 * @param integer $user_id
	 * @param string $symbol
	 * @param string $comparison "<" or ">" or "="
	 * @param float $kurs
	 */
	function changeWarning($user_id, $symbol, $comparison, $kurs) {
		global $db;
		$sql = 'REPLACE INTO stock_warnings (user_id, symbol, comparison, kurs)
				VALUES (?, ?, ?, ?)';
		$db->query($sql, __FILE__, __LINE__, __METHOD__, [$user_id, $symbol, $comparison, $kurs]);
		return true;
	}

	function getBargeld($user_id) {
		global $db;
		$sql = 'SELECT (1000+SUM(if(action="buy", -(menge*kurs), +(menge*kurs)))) AS bargeld
				FROM stock_trades WHERE user_id=? GROUP BY user_id';
		$rs = $db->fetch($db->query($sql, __FILE__, __LINE__, __METHOD__, [$user_id]));
		return $rs['bargeld'];

	}

	function getKurs($symbol) {
		global $db;
		$sql = 'SELECT kurs FROM stock_quotes sq WHERE symbol=? ORDER BY tag DESC, zeit DESC LIMIT 1';
		$rs = $db->fetch($db->query($sql, __FILE__, __LINE__, __METHOD__, [$symbol]));
		return $rs['kurs'];
	}

	/*
	function getKurseNeuste() {
		global $db;
		$sql =
			"
			SELECT
				sq.symbol
				, sq.kurs
				, sq.zeit
				, si.company
			FROM
				stock_quotes sq
			LEFT JOIN stock_items si ON (si.symbol = sq.symbol)
			ORDER BY tag DESC, zeit DESC
			LIMIT 0,20
			"
		;
		$result = $db->query($sql, __FILE__, __LINE__, __METHOD__);

		while($rs = $db->fetch($result)) {
			$kurse[] = $rs;
		}

		return $kurse;
	}
	*/

	function getStocksOldest() {
		global $db;
		$sql = 'SELECT symbol FROM stock_items ORDER BY kurs_last_updated DESC LIMIT 3';
		$result = $db->query($sql, __FILE__, __LINE__, __METHOD__);

		while($rs = $db->fetch($result)) {
			$stocks[] = $rs['symbol'];
		}
		return $stocks;
	}

	function getStocksTraded() {
		global $db;
		$sql = 'SELECT symbol FROM stock_trades GROUP BY symbol';
		$result = $db->query($sql, __FILE__, __LINE__, __METHOD__);
		while($rs = $db->fetch($result)) {
			$stocks[] = $rs['symbol'];
		}
		return $stocks;
	}


	/**
	* Holt sich die neusten (nicht die heutigen) Kurse eines Wertpapiers.
	*
	* @author [z]biko
	* @version 2.0
	* @since 1.0 method added
	* @since 2.0 `25.11.2018` updated to use new $notifcation Class & some code and query optimizations
	*
	* @param String $symbol
	* @param float(6,3) kurs
	* @global object $db Globales Class-Object mit allen MySQL-Methoden
	* @global object $notification Globales Class-Object mit allen Notification-Methoden
	* @return void
	*/
	function issueStockWarnings($symbol, $kurs)
	{
		global $db, $notification;

		$sql = 'SELECT * FROM stock_warnings WHERE symbol=?';
		$result = $db->query($sql, __FILE__, __LINE__, __METHOD__, $symbol);

		while($rs = $db->fetch($result))
		{
			$warning = $kurs . $rs['comparison'] . $rs['kurs'];

			if ($warning)
			{
				/**
				 * @deprecated
				Messagesystem::sendMessage(
					59
					, $rs['user_id']
					, '[Stockbroker] Warning: '.$symbol
					,
						'<a href="/?tpl=173&symbol='.$symbol.'">Stock Information für '.$symbol.'</a>'
						.'<br />'
						.$symbol.' ist '.$rs['comparison'].' '.$rs['kurs'].' (aktueller Kurs: '.$kurs.')'
					, $rs['user_id']
				);*/
				/** Send $warning Notification */
				$notification_text = t('message-stock-warning', 'stockbroker', [ SITE_URL, $symbol, $rs['comparison'], $rs['kurs'], $kurs]);
				$notification_status = $notification->send($rs['user_id'], 'stockbroker', ['from_user_id'=>BARBARA_HARRIS, 'subject'=>t('message-subject', 'stockbroker'), 'text'=>$notification_text, 'message'=>$notification_text]);
				if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> Notification status "%s" for $symbol %s to user %d', __METHOD__, __LINE__, ($notification_status===true?'true':'false'), $symbol, $rs['user_id']));

				$sql = 'DELETE FROM stock_warnings WHERE user_id=? AND symbol=? AND comparison=?';
				$db->query($sql, __FILE__, __LINE__, __METHOD__, [$rs['user_id'], $symbol, $rs['comparison']]);
			}
		}
	}

	/**
	 * Holt sich die neusten (nicht die heutigen) Kurse eines Wertpapiers.
	 *
	 * @param string $symbol
	 * @return int
	 */
	function getSymbol($symbol) {
		global $db;
		$sql = 'SELECT * FROM stock_items si
					LEFT JOIN stock_quotes sq ON (sq.symbol = si.symbol AND sq.tag=(SELECT MAX(tag) FROM stock_quotes WHERE symbol = sq.symbol))
				WHERE si.symbol=?';
		return $db->fetch($db->query($sql, __FILE__, __LINE__, __METHOD__, [$symbol]));
	}

	function searchstocks($searchstring) {
		global $db;
		$sql = "SELECT
					si.symbol
					,si.company
					,si.description
					,sq.tag
					,sq.zeit
					,sq.kurs
					,sq.proz_steigerung
					,sq.kurs_gestern
				FROM
					stock_items si
					LEFT JOIN stock_quotes sq ON (sq.symbol = si.symbol AND tag = (SELECT max(tag) from stock_quotes WHERE symbol = sq.symbol))
				WHERE
					si.symbol LIKE CONCAT('%', ?, '%')
					OR
					si.company LIKE CONCAT('%', ?, '%')";
		$result = $db->query($sql, __FILE__, __LINE__, __METHOD__, [$searchstring, $searchstring]);

		while($rs = $db->fetch($result)) {
			$stocks[] = $rs;
		}

		return $stocks;
	}

	function getTodaysWinners()
	{
		global $db;

		$sql = 'SELECT * FROM stock_quotes WHERE tag=? ORDER BY proz_steigerung DESC LIMIT 10';
		$result = $db->query($sql, __FILE__, __LINE__, __METHOD__, [timestamp(true)]);

		while($rs = $db->fetch($result)) {
			$kurse[] = $rs;
		}

		return $kurse;
	}

	function getTodaysLosers()
	{
		global $db;

		$sql = 'SELECT * FROM stock_quotes WHERE tag=? ORDER BY proz_steigerung ASC LIMIT 10';
		$result = $db->query($sql, __FILE__, __LINE__, __METHOD__, [timestamp(true)]);

		while($rs = $db->fetch($result)) {
			$kurse[] = $rs;
		}

		return $kurse;
	}

	function getStocksOwned($user_id)
	{
		global $db;
		$sql = 'SELECT symbol, SUM(if(action="buy", menge, -menge)) AS amount FROM stock_trades
				WHERE user_id=? GROUP BY user_id, symbol';
		$result = $db->query($sql, __FILE__, __LINE__, __METHOD__, [$user_id]);

		while($rs = $db->fetch($result)) {
			$ownedstocks[] = $rs;
		}

		return $ownedstocks;
	}

	/*
	function getCurrentProperty($user_id) {
		global $db;
		$sql =
			"
			SELECT
				*
			FROM
				stock_trades st
			WHERE
				user_id = '".$user_id."'
			"
		;

		$result = $db->query($sql, __FILE__, __LINE__, __METHOD__);

		$assets['Bargeld'] = 1000; // Anfangsvermögen

		while($rs = $db->fetch($result)) {
			if($rs['action'] == 'buy') {
				$assets['Bargeld'] -= $rs['menge'] * $rs['kurs'];
				$assets[$rs['symbol']] += $rs['menge'];
			} else if($rs['action'] == 'sell') {
				$assets['Bargeld'] += $rs['menge'] * $rs['kurs'];
				$assets[$rs['symbol']] -= $rs['menge'];
			}
		}

		return $assets;
	}*/

	function getKursBought($user_id, $symbol) {
		global $db;
		$sql = 'SELECT kurs FROM stock_trades WHERE action="buy" AND symbol=? AND user_id=? ORDER by tag DESC, zeit DESC LIMIT 1';
		$rs = $db->fetch($db->query($sql, __FILE__, __LINE__, __METHOD__, [$symbol, $user_id]));
		return $rs['kurs'];
	}

	function getMengeOwned($user_id, $symbol) {
		global $db;
		$sql = 'SELECT SUM(if(action="buy", menge, -menge)) AS amount FROM stock_trades WHERE symbol=? AND user_id=? GROUP BY user_id';
		$rs = $db->fetch($db->query($sql, __FILE__, __LINE__, __METHOD__, [$symbol, $user_id]));
		return $rs['amount'];
	}


	function getHighscore() {
		global $db;

		$sql = 'SELECT DISTINCT user_id,
					FLOOR(1000 + ((SUM(IF (ACTION = "sell", (menge * st.kurs), 0 )) - SUM(IF (ACTION = "buy", (menge * st.kurs), 0 )))
					+ (SUM(IF (ACTION = "buy", (menge * sq.kurs), 0 )) - SUM(IF (ACTION = "sell", (menge * sq.kurs), 0 ))))) AS betrag
				FROM stock_trades st LEFT JOIN stock_quotes sq ON (sq.tag = (SELECT MAX(tag) FROM stock_quotes WHERE symbol = st.symbol) AND sq.symbol = st.symbol)
				GROUP BY user_id ORDER BY betrag DESC';
		$result = $db->query($sql, __FILE__, __LINE__, __METHOD__);

		while($rs = $db->fetch($result)) {
			$highscore[] = $rs;
		}

		return $highscore;
	}

	function getYesterdaysMosttraded() {
		global $db;

		$sql = 'SELECT symbol, sum(menge*kurs) AS menge FROM stock_trades WHERE tag = DATE_SUB(now(), INTERVAL 1 DAY) GROUP BY symbol ORDER by menge desc';

		$result = $db->query($sql, __FILE__, __LINE__, __METHOD__);

		while($rs = $db->fetch($result)) {
			if($rs['menge'] > 0) $stocks[] = $rs;
		}

		return $stocks;
	}

	function getWarnings($user_id) {
		global $db;

		$sql = 'SELECT * FROM stock_warnings WHERE user_id=? ORDER by symbol ASC';
		$result = $db->query($sql, __FILE__, __LINE__, __METHOD__, [$user_id]);
		while($rs = $db->fetch($result)) {
			$warnings[] = $rs;
		}
		return $warnings;
	}


	function getStocklist($anzahl, $page) {
		global $db;

		$sql = 'SELECT si.symbol, si.company, sq.kurs, sq.tag, sq.zeit FROM stock_items si
					LEFT JOIN stock_quotes sq ON (sq.symbol = si.symbol AND tag = (SELECT MAX(tag) FROM stock_quotes WHERE symbol = sq.symbol))
				ORDER BY si.symbol ASC LIMIT ?,?';
		$result = $db->query($sql, __FILE__, __LINE__, __METHOD__, [$page*$anzahl, $anzahl]);

		while($rs = $db->fetch($result)) {
			$stocklist[] = $rs;
		}

		return $stocklist;
	}

	function getTrades($user_id) {
		global $db;

		$sql = 'SELECT * FROM stock_trades st WHERE user_id=? ORDER BY tag, zeit ASC';
		$result = $db->query($sql, __FILE__, __LINE__, __METHOD__, [$user_id]);

		while($rs = $db->fetch($result)) {
			$trades[] = $rs;
		}

		return $trades;
	}

	function sellStock($user_id, $symbol, $menge, $max) {
		global $db;

		if(!isset($symbol)) {
			echo '$symbol ist nicht gesetzt.';
			return false;
		}

		if($menge < 1 && !$max) {
			echo 'Du musst eine Menge grösser als 0 festlegen. ('.$menge.') oder max setzen.';
			return false;
		}

		$symbol = strtoupper($symbol); // müsste eigentlich nicht hier sein, aber um sicher zu gehen...

		// neuen Preis grabben
		self::updateKurs($symbol);
		$kurs = self::getKurs($symbol);

		if(!is_numeric($kurs)) {
			echo 'Konnte keinen Kurs finden für '.$symbol;
			return false;
		}

		if($max) {
			$menge = self::getMengeOwned($user_id, $symbol);
		} else if($menge > self::getMengeOwned($user_id, $symbol)) {
			echo 'Du kannst gar nicht soviel verkaufen!';
			return false;
		}

		// Handel vollziehen --------------------------------------------------------
		$sql = 'INSERT INTO stock_trades (tag, zeit, user_id, symbol, menge, action, kurs) VALUES (? ,?, ?, ?, ?, "sell", ?)';
		$db->query($sql, __FILE__, __LINE__, __METHOD__, [timestamp(true), timestamp(true), $user_id, $symbol, $menge, $kurs]);

		return true;
	}

	/**
	 * @param string $symbol
	 */
	function updateKurs($symbol)
	{
		global $db;

		if(empty($symbol) || !is_string($symbol)) return false;
		else $symbol = strtoupper($symbol);

		//link machen
		$source = 'https://finance.yahoo.com/quote/'.$symbol;
		$html = join('',file($source));
		//unnützi war löschä
		$html = strip_tags(str_replace("  "," ",$html),"<b> <i>");

		//kurs ermittlä
		$pattern = "(Last\sTrade:<b>(\d+\.\d+)<\/b>)";
		preg_match_all($pattern,$html,$out);

		//checkä öbs klapt hät
		if(isset($out[1][0])) {
			$kurs = trim($out[1][0]);

			if($kurs > 0) {

				$sql_select = 'SELECT * FROM stock_quotes WHERE symbol=? AND tag=DATE_SUB(?, INTERVAL 1 DAY)';
				$rs = $db->fetch($db->query($sql_select, __FILE__, __LINE__, __METHOD__, [$symbol, timestamp(true)]));

				$sql_replace = 'REPLACE INTO stock_quotes (symbol, kurs, zeit, tag, kurs_gestern, proz_steigerung)
						VALUES ( ?, ?, ?, ?, ?, IF(?>0, (?-?)/?*100, 0) )';
				$db->query($sql_replace, __FILE__, __LINE__, __METHOD__, [$symbol, $kurs, timestamp(true), timestamp(true), $rs['kurs'], $rs['kurs'], $kurs, $rs['kurs'], $rs['kurs'], $rs['kurs']]);

				$sql_update = 'UPDATE stock_items SET kurs_last_updated=? WHERE symbol=?';
				$db->query($sql_update, __FILE__, __LINE__, __METHOD__, [timestamp(true), $symbol]);

				self::issueStockWarnings($symbol, $kurs);

				return true;
			} else {
				/*
				Messagesystem::sendMessage(
					59
					, 3
					, '[Stockbroker] Error: '.$symbol
					, 'Konnte Kurs nicht grabben für '.$symbol
					, 3
				);
				*/
				return false;
			}
		//wenn symbol bi yahoo unbekannt isch...z.b (SWX,SMI war)
		} else {
			/*
			Messagesystem::sendMessage(
				59
				, 3
				, '[Stockbroker] Error: '.$symbol
				, 'Konnte '.$symbol.' nicht grabben - gelöscht.'
				, 3
			);
			*/
			$db->query('DELETE FROM stock_quotes WHERE symbol=?', __FILE__, __LINE__, __METHOD__, [$symbol]);
			return false;
		}
	}

	function update_orders($symbol) {

		$source = 'https://finance.yahoo.com/quote/';

		$html = join('',file($source.$symbol));
		$html = strip_tags($html,"<table> <tr> <td> <th>");
		$html = str_replace("  ","",$html);
		$html = str_replace("\n","",$html);
		$html = substr($html,strpos($html,"Bid Orders"));
		$html = substr($html,0,strpos($html,"Add to Portfolio"));
		$array = explode("</td>",$html);

		$c = 0;
		$d = 0;

		//echo count($array);
		$i = 0;
		for($i = 4;$i<count($array);$i++) {
			$array[$i] = strip_tags($array[$i]);
			//echo $i." = ".$array[$i]."<br />";
			$c++;
			$c = (!$array[$i] ? 0 : $c);
			$c = ($array[$i] == "Institution" ? 0 : $c);
			$new_array[$d][$c] = $array[$i];
			if(($c%3)==0) {
				$c = 0;
				//echo "<hr>";
				$d++;
			}

		}
		$bez = array("","price","volume","institution");
		$key_new = 0;
		$w = FALSE;
		foreach ($new_array as $key => $ar) {
			$typ = ($w ? "Ask" : "Bid");
			//echo $typ ." ".$key." = ".$ar." - ".count($ar)."<br />";
			if(is_numeric($ar[1])) {
				foreach($ar as $kk => $vv) {
					$orders[$typ][$key_new][$bez[$kk]] = $vv;

					//echo $key." ".$kk." = ".$vv."<br />";
				}
				$key_new++;
			} else {
				$w = TRUE;
				$key_new = 0;

			}
			//echo "<hr><br />";

		}
		return $orders;

	}
}

/** Initiate Stockbroker class */
$stockbroker = new Stockbroker();
