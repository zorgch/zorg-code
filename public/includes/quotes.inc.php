<?php
/**
 * File includes
 */
require_once __DIR__.'/config.inc.php';
include_once INCLUDES_DIR.'usersystem.inc.php';

/**
 * Quotes Class
 *
 * In dieser Klasse befinden sich alle Funktionen zur Steuerung der Activities
 *
 * @version		2.0
 * @since		1.0 `[z]milamber` File and Class added
 * @since		2.0 `IneX` Extended Class Methods
 * @package		zorg\Quotes
 */
class Quotes
{
	static function execActions()
	{
		global $db, $user;

		/** Validate parameters */
		$doAction = filter_input(INPUT_POST, 'action', FILTER_SANITIZE_SPECIAL_CHARS) ?? null; // $_POST['action']
		$quote_id = filter_input(INPUT_POST, 'quote_id', FILTER_VALIDATE_INT) ?? null; // $_POST['quote_id']
		$votescore = filter_input(INPUT_POST, 'score', FILTER_VALIDATE_INT) ?? null; // $_POST['score']
		$redirectUrl = base64url_decode(filter_input(INPUT_POST, 'url', FILTER_SANITIZE_FULL_SPECIAL_CHARS)) ?? '/quotes.php'; // $_POST['url']
		if($doAction === 'benoten' && !empty($quote_id) && $quote_id>0 && !empty($votescore) && $votescore>0)
		{
			if (!self::hasVoted($user->id, $quote_id))
			{
				$sql = 'REPLACE INTO quotes_votes (quote_id, user_id, score) VALUES (?, ?, ?)';
				$db->query($sql, __FILE__, __LINE__, __METHOD__, [$quote_id, $user->id, $votescore]);
			}
			header('Location: '.$redirectUrl);
		}
	}

	/**
	 * Outputs an HTML of an individual Quote Record-Set.
	 * @param object $rs Fetched SQL-Query Result Record-Set
	 * @return string
	 */
	static function formatQuote($rs)
	{
		global $user;

		$site = filter_input(INPUT_GET, 'site', FILTER_VALIDATE_INT) ?? 0;
		$html = '<div class="quote">'
					.'<blockquote><i>'.nl2br(html_entity_decode($rs["text"], ENT_QUOTES, 'UTF-8')).'</i>'
					.' - '.$user->id2user($rs['user_id'], 0)
					.($user->is_loggedin() ? ($user->id === (int)$rs['user_id'] ? ' <a href="'.getChangedURL('do=delete&quote_id='.$rs['id'].'&site='.$site).'">[delete]</a>' : '') : '')
					.'</blockquote>';

		if ($user->is_loggedin() && !self::hasVoted($user->id, $rs['id']))
		{
			$html .= '<form name="quotevoteform'.$rs['id'].'" method="post" action="/quotes.php" class="voteform left">'
						.'<input name="action" type="hidden" value="benoten">'
						.'<input name="quote_id" type="hidden" value="'.$rs['id'].'">'
						.'<input name="url" type="hidden" value="'.base64url_encode($_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING']).'">'
						.'<span class="scoreinfo">Benoten:</span>'
						.'<label class="scorevalue">
							<input type="radio" name="score" onClick="document.quotevoteform'.$rs['id'].'.submit();this.setAttribute(\'disabled\', \'disabled\');" value="6"></label>'
						.'<label class="scorevalue">
							<input type="radio" name="score" onClick="document.quotevoteform'.$rs['id'].'.submit();this.setAttribute(\'disabled\', \'disabled\');" value="5"></label>'
						.'<label class="scorevalue">
							<input type="radio" name="score" onClick="document.quotevoteform'.$rs['id'].'.submit();this.setAttribute(\'disabled\', \'disabled\');" value="4"></label>'
						.'<label class="scorevalue">
							<input type="radio" name="score" onClick="document.quotevoteform'.$rs['id'].'.submit();this.setAttribute(\'disabled\', \'disabled\');" value="3"></label>'
						.'<label class="scorevalue">
							<input type="radio" name="score" onClick="document.quotevoteform'.$rs['id'].'.submit();this.setAttribute(\'disabled\', \'disabled\');" value="2"></label>'
						.'<label class="scorevalue">
							<input type="radio" name="score" onClick="document.quotevoteform'.$rs['id'].'.submit();this.setAttribute(\'disabled\', \'disabled\');" value="1"></label>'
					.'</form>';
		}
		else {
			$totalvotescore = round(self::getScore($rs['id']), 1);
			if ($totalvotescore > 0) $html .= '<small>(Note: '.$totalvotescore.')</small>';//+ Anzahl Votes: getNumVotes()
		}
		$html .= '</div>';

		return $html;
	}

	static function getScore($quote_id) {
		global $db;

		$sql = 'SELECT AVG(score) as score FROM quotes_votes WHERE quote_id=?';
		$rs = $db->fetch($db->query($sql, __FILE__, __LINE__, __METHOD__, [$quote_id]));
		$avgscore = floatval($rs['score']);

		return $avgscore;
	}

	/**
	 * Gibt einen random Quote zurück
	 *
	 * @version 2.0
	 * @since 1.0 `22.03.2004` `keep3r` function added. Original name: quote()
	 * @since 2.0 `24.12.2023` `Inex` moved function from util.inc.php & renamed
	 *
	 * @uses Quotes::formatQuote()
	 * @param boolean $htmlFormattedResult Whether to rich-format the Quote; or plain text on FALSE
	 */
	static function getRandomQuote($htmlFormattedResult=false)
	{
		global $db;

		$sql_cnt = 'SELECT COUNT(*) as anzahl FROM quotes';
		$rs = $db->fetch($db->query($sql_cnt, __FILE__, __LINE__, __METHOD__));
		$total = intval($rs['anzahl']);

		$rnd = random_int(1, $total);
		$sql_sel = 'SELECT * FROM quotes WHERE id=?';
		$rs = $db->query($sql_sel, __FILE__, __LINE__, __METHOD__, [$rnd]);

		$quote = ($htmlFormattedResult === true ? self::formatQuote($rs) : $rs['text']);
		return $quote;
	}

	/**
	 * Random User Quote (?).
	 * Gibt ein random Quote eines Users aus. Durch user_id wird es ein quote dieses users sein<br><br>
	 *
	 * @deprecated @[z]milamber: Wir brauchen das nicht?!
	 *
	 * @version 2.0
	 * @since 1.0 `[z]milamber` function added. Original name: usersystem::quote()
	 * @since 2.0 `25.12.2024` `Inex` moved function from usersystem.inc.php & refactored
	 *
	 * @param int $user_id User ID
	 * @return string Plain-Text Quote
	 */
	static function getUserQuote($user_id) {
		global $db;
		if(is_numeric($user_id) && $user_id > 0)
		{
			$sql_cnt = 'SELECT COUNT(*) as anzahl FROM quotes WHER user_id=?';
			$rs = $db->fetch($db->query($sql_cnt, __FILE__, __LINE__, __METHOD__));
			$total = intval($rs['anzahl']);

			$rnd = random_int(1, $total);
			$sql_sel = 'SELECT text FROM quotes WHERE id=?';
			$result = $db->query($sql_sel, __FILE__, __LINE__, __METHOD__, [$rnd]);

			$quote = $rs['text'];
			return $quote;
		}
	}

	/**
	 * Setzt einmal am Tag einen Quote in die DB daily_quote
	 *
	 * @deprecated Replaced by Quotes::newDailyQuote() ? To be confirmed...
	 *
	 * @version 1.1
	 * @since 1.0 `22.03.2004` `keep3r` function added
	 * @since 1.1 `24.12.2023` `IneX` moved function from util.inc.php
	 *
	 * @return bool
	 */
	static function set_daily_quote()
	{
		global $db;
		$date = date('Y-m-d');
		$sql = 'SELECT * FROM daily_quote WHERE date=?';
		$result = $db->query($sql, __FILE__, __LINE__, __METHOD__, [$date]);
		$rs = $db->fetch($result);

		if (!$rs)
		{
			$quote = self::getRandomQuote();
			$sql = 'INSERT INTO daily_quote (date, quote) VALUES (?, ?)';
			$db->query($sql, __FILE__, __LINE__, __METHOD__, [$date, $quote]);
			return 1;
		}
		return 0;
	}

	/**
	 * Outputs current Daily Quote in Rich-HTML.
	 * @version 1.0
	 * @since 1.0 `22.03.2004` `keep3r` function added
	 * @return string
	 */
	static function getDailyQuote() {
		global $db;

		$sql = 'SELECT quotes.*, TO_DAYS(p.date)-TO_DAYS(?) upd FROM periodic p, quotes WHERE p.name="daily_quote" AND p.id=quotes.id';
		$result = $db->query($sql, __FILE__, __LINE__, __METHOD__, [timestamp(true)]);

		$rs = $db->fetch($result);

		return self::formatQuote($rs);
	}

	static function getNumVotes($quote_id) {
		global $db;

		$sql = 'SELECT COUNT(*) as numvotes FROM quotes_votes WHERE quote_id=?';
		$result = $db->query($sql, __FILE__, __LINE__, __METHOD__, [$quote_id]);
		$rs = $db->fetch($result);
		$numvotes = (isset($rs['numvotes']) && !empty($rs['numvotes']) ? intval($rs['numvotes']) : 0);

		return $numvotes;
	}

	static function getScorebyUser($quote_id, $user_id) {
		global $db;

		$sql = 'SELECT score FROM quotes_votes WHERE quote_id=? AND user_id=?';
		$result = $db->query($sql, __FILE__, __LINE__, __METHOD__, [$quote_id, $user_id]);
		$rs = $db->fetch($result);
		$score = (isset($rs['score']) && !empty($rs['score']) ? intval($rs['score']) : 0);

		return $score;
	}

	static function hasVoted($user_id, $quote_id) {
		global $db;

		$sql = 'SELECT COUNT(*) as hasvoted FROM quotes_votes WHERE quote_id=? AND user_id=?';
		$rs = $db->fetch($db->query($sql, __FILE__, __LINE__, __METHOD__, [$quote_id, $user_id]));
		$hasvoted = $rs['hasvoted']; // Is either '1' or '0'

		return $hasvoted;
	}

	/**
	 * Check if Quote ID is Daily Quote
	 *
	 * @version 2.0
	 * @since 1.0 `[z]milamber` method added
	 * @since 2.0 `05.06.2023` `IneX` Removed date=NOW() comparison (not returning a result)
	 *
	 * @global object $db Globales Class-Object mit allen MySQL-Methoden
	 * @param int Quote ID to check
	 * @return bool True/False
	 */
	static function isDailyQuote($id) {
		global $db;

		$sql = 'SELECT id FROM periodic WHERE date=? AND name=?';
		$rs = $db->fetch($db->query($sql, __FILE__, __LINE__, __METHOD__, [timestamp(true), 'daily_quote']));

		return (int)$rs['id'] === (int)$id;
	}

	/**
	 * Quote of the Day
	 * Generates a new Daily Quote
	 *
	 * @version 3.0
	 * @since 1.0 `[z]milamber` Function added
	 * @since 2.0 `IneX` added Telegram Notification for new Daily Quote
	 * @since 2.1 `IneX` changed to new Telegram Send-Method
	 * @since 3.0 `05.06.2023` `IneX` optimized and reduced SQL-query, code refactored
	 *
	 * @global object $db Globales Class-Object mit allen MySQL-Methoden
	 * @global object $user Globales Class-Object mit den User-Methoden & Variablen
	 * @global object $telegram Globales Class-Object mit den Telegram-Methoden
	 */
	static function newDailyQuote() {
		global $db, $user, $telegram;

		try {
			/** Alle Quotes holen */
			$sql_sel = 'SELECT id, COALESCE((SELECT AVG(score) FROM quotes_votes WHERE quote_id=quotes.id GROUP BY quote_id), 0) score,
						text, user_id FROM quotes GROUP BY id ORDER BY RAND()';
			$quotes = $db->query($sql_sel, __FILE__, __LINE__, __METHOD__);

			/** Zufällige Quote ID auswählen */
			$count = $db->num($quotes); // Anzahl Quotes
			if ($count <= 0) throw new Exception('No Quotes found');
			$id = rand(0, $count-1); // Zufalls #
			$id = rand($id, $count-1); // die besten bevorzugen.

			/** Ausgewählten Quote fetchen */
			$select_quote = $db->seek($quotes, $id);
			$rs = $db->fetch($quotes);

			if (!$rs || count($rs) === 0) throw new Exception('Quote data not fetched');

			/** Quote in die daily tabelle tun */
			$sql = 'REPLACE INTO periodic (name, id, date) VALUES (?, ?, ?)';
			$db->query($sql, __FILE__, __LINE__, __METHOD__, ['daily_quote', $rs['id'], timestamp(true)]);

			/** Send new Daily Quote as Telegram Message */
			$telegram->send->message('group', sprintf('Daily [z]Quote: <b>%s</b><i> - %s</i>', html_entity_decode($rs['text']), $user->id2user($rs['user_id'], TRUE)), ['disable_notification' => 'true']);

			return true;
		}
		catch (Exception $e) {
			if (DEVELOPMENT === true) echo $e->getMessage();
			user_error($e->getMessage(), E_USER_ERROR);

			return false;
		}
	}
}
