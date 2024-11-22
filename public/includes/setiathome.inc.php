<?php
/**
 * SETI@Home Stats for zooomclan
 *
 * @author [z]keep3r
 * @package zorg\SETI
 */

/**
 * File Includes
 * @include config.inc.php required
 * @include	mysql.inc.php Smarty, required
 */
require_once __DIR__.'/config.inc.php';
require_once INCLUDES_DIR.'mysql.inc.php';

class setiathome
{
	function seti_time($time_in) {
		$years = $time_in / 60 / 24 / 365;
		if($years < 1) {
			$hr = round($time_in / 60);
			$mins = ($time_in % 60);
			$time_out = $hr." Std. ".$mins." min";
		} else {
			$time_out = round($years,3). " Jahre";
		}
		return $time_out;
	}

	function update_group() {
		global $db;
		$group_stats = "http://setiathome2.ssl.berkeley.edu/fcgi-bin/fcgi?cmd=team_lookup_xml&name=zooomclan.org";
		$page = @file($group_stats);

		//check link
		do {
			if($page) {
				libxml_disable_entity_loader(true); // disable external entity loading (CVE-611)
				$xml = join('', $page);
				$parser = xml_parser_create();

				//parse xml in arrays
				xml_parse_into_struct($parser, $xml, $vals, $index);

				xml_parser_free($parser);

				$members = 0;
				for($i=0;$i<=count($vals)-1;$i++) {
					if($vals[$i]['tag']	== "MEMBER" && $vals[$i]['type'] == "open" && $vals[$i]['tag'] != "GROUPSTATS") {
						$members++;
					} else {
						$values[$members][$vals[$i]['tag']] = $vals[$i]['value'];
					}
				}

				//saves data in db
				for($i = 1;$i<=count($values)-1;$i++) {
					$month_array = array("Jan" => "01", "Feb" => "02", "Mar" => "03", "Apr" => "04", "May" => "05", "Jun" => "06", "Jul" => "07", "Aug" => "08", "Sep" => "09", "Oct" => 10, "Nov" => 11, "Dec" => 12);
					$month = $month_array[substr($values[$i]['DATELASTRESULT'],4,3)];
					$day = ltrim(rtrim(substr($values[$i]['DATELASTRESULT'],7,3)));
					$day = (strlen($day) == 1) ? "0".$day : $day ;
					$time = rtrim(substr($values[$i]['DATELASTRESULT'],11,9));
					$year = substr($values[$i]['DATELASTRESULT'],strlen($values[$i]['DATELASTRESULT'])-4);
					$date =  $year."-".$month."-".$day." ".$time;

					if(substr_count($values[$i]['TOTALCPU'],"years")) {
						$years = str_replace(" years","",$values[$i]['TOTALCPU']);
						$mins = $years * 365 * 24 * 60;
					} else {
						$hours = substr($values[$i]['TOTALCPU'],0,strpos($values[$i]['TOTALCPU']," hr"));
						$mins = substr($values[$i]['TOTALCPU'],strpos($values[$i]['TOTALCPU']," hr")+3,strpos($values[$i]['TOTALCPU']," min")-7) + ($hours * 60);
					}

					$sql = 'SELECT name FROM seti WHERE name=?';
					$result = $db->query($sql, __FILE__, __LINE__, __FUNCTION__, [$values[$i]['NAME']]);
					if($db->num($result)) {
						$sql = 'UPDATE seti set num_results=?, total_cpu=?, avg_cpu=?, date_last_result=? WHERE name=?';
						$params = [$values[$i]['NUMRESULTS'], $mins, $values[$i]['AVECPU'], $date, $values[$i]['NAME']];
					} else {
						$sql = 'INSERT INTO seti (name, num_results, total_cpu, avg_cpu, date_last_result) VALUES (?, ?, ?, ?, ?)';
						$params = [$values[$i]['NAME'], $values[$i]['NUMRESULTS'], $mins, $values[$i]['AVECPU'], $date];
					}
					$db->query($sql, __FILE__, __LINE__, __FUNCTION__, $params);
				}
			} else
			if(!$page) { sleep(1); }
			//wenn page nicht aufgerufen werden konnte...
		}  while (!$page);
	}

	function tagesabschluss()
	{
		global $db;
		self::update_group();
		$sql = 'REPLACE into seti_tage (datum, name, num_results, total_cpu, avg_cpu, date_last_result, account, user_id)
				SELECT now() as datum, name, num_results, total_cpu, avg_cpu, date_last_result, account, user_id FROM seti';
		$db->query($sql, __FILE__, __LINE__, __FUNCTION__);
	}

}
