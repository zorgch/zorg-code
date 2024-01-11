<?php

/**
 * @deprecated Requires migration to use BOINC Stats...
 */
class SetiStats {
      public $exp = [
		"UserName"                => "URL)\n</td>\n<td>\n(.*)\n</td>\n</tr>\n<tr>\n<td>\nResults",
		"Workunits"               => "<TR>\n<TD>\nResults Received\n</TD>\n<TD>\n(.*)</TD>\n</TR>\n<TR>\n<TD>\nTotal CPU Time",
		"TotalCPUTime"            => "<TR>\n<TD>\nTotal CPU Time\n</TD>\n<TD>\n(.*)\n</TD>\n</TR>\n<TR>\n<TD>\nAverage CPU Time per work unit",
		"AverageCPUTime"          => "<TR>\n<TD>\nAverage CPU Time per work unit\n</TD>\n<TD>\n(.*)\n</td>\n</tr>\n<tr>\n<td>\nAverage results received per day",
		"LastResult"              => "<TR><TD>Last result returned:</TD><TD>(.*)</TD>\n<TR><TD>Registered on:</TD>",
		"RegisteredOn"            => "<TR><TD>Registered on:</TD><TD>(.*)</TD>\n<TR><TD>&nbsp;</TD><TD><a href=",
		"SetiUserFor"             => "<TR><TD>SETI@home user for:</TD><TD>(.*)</TD></TR>\n<TR BGCOLOR=\"#303080\"><TD COLSPAN=2><b>Your group info",
		"GroupName"               => ".html\"><b>(.*)</b></a></td>\n</tr>\n<tr>\n<td",
		"GroupURL"                => "<tr>\n<td>\nYou belong to the group named:\n</td>\n<td>\n<a href=\"(.*)\"><b>",
		"TotalUsers"              => "<tr><td>\nYour rank out of <b>(.*)</b> total users is:\n</td><td>",
		"Rank"                    => "</td><td>\n<b>(.*)<sup>",
		"TotalUsersWithThisRank"  => "The number of users who have this rank:\n</td><td>\n<b>(.*)</b>\n</td></tr>\n<tr><td>\nYou have completed more work units than",
		"MoreWorkUnitsThan"       => "work units than\n</td><td>\n<b>(.*)</b> of our",
		"AverageResultsPerDay"    => "Average results received per day\n</td>\n<td>\n(.*)\n</td>\n</tr>\n<TR><TD>Last",
		"RegistrationClass"       => "</TD>\n<TR><TD>&nbsp;</TD><TD><a href=(.*)>View Registration Class</a></TD>\n<TR><TD>SETI@home user for"
	];
	public $errors = [
		"SiteDown"   => "Seti@Home Server unreachable",
		"ParseError" => "Parse Error",
		"Error"      => "Error"
	];
	public $what;
	private $email;
	private $server1;
	private $server2;
	private $server3;
	private $raw;
	private $Groupname;
	private $Groupurl;
	private $Groupstuff;
	private $null;
    private $tmp_month;
    private $tmp_null;
    private $tmp_day;
    private $tmp_time;
    private $tmp_year;
    private $tmp_hour;
    private $tmp_min;
    private $tmp_sec;
	private $RegDate;
    private $ActDate;
    private $DiffDate;
    private $ResInt;
    private $days;
    private $hours;
    private $minutes;
    private $seconds;
    private $ResultInterval;


	function __construct($user_email)
	{
		self::setEmail($user_email);
		self::setServer();
		$this->raw = @implode("", @file($this->server1));

		if (!$this->raw){
			$this->raw = @implode("", @file($this->server2));
		}
	}

	function setEmail($user_email)
	{
		$this->email = $user_email;
		return true;
	}

	function setServer()
	{
		$this->server1 = "https://www.boincstats.com/stats/search/?search=". $this->email;
		$this->server2 = "https://stats.free-dc.org/stats.php?page=search&proj=&team=&name=". $this->email. "&exact=Y&cross=N";
		$this->server3 = "http:///setiathome2.ssl.berkeley.edu/fcgi-bin/fcgi?email=". $this->email. "&cmd=user_stats_new";
		return true;
	}

	function viewStats($what)
	{

		if (!$this->raw) {
			$this->$what = $this->errors["SiteDown"];
		}
		else
		{
			if (@preg_match('/' . $this->exp[$what] . '/i', $this->raw, $output))
			{
				$this->$what = $output[1];
			}
				else
			{
				$this->$what = $this->errors["ParseError"];
			}
		}
		if (!$this->$what)
		{
			$this->$what = $this->errors["Error"];
		}
		return $this->$what;

	}

	function Group()
	{
		if (@preg_match("/You do not currently belong to a group/i", $this->raw))
		{
			$this->Groupname = "none";
			$this->Groupurl  = "";
		}
		else
		{
			$this->Groupname = $this->viewStats('GroupName');
			$this->Groupurl  = $this->viewStats('GroupURL');
		}

		$this->Groupstuff = array (
			"name"     => $this->Groupname,
			"url"      => $this->Groupurl
		);

		return $this->Groupstuff;
	}

	function ResultInt()
	{
		list($this->null, $this->tmp_month, $this->tmp_null, $this->tmp_day, $this->tmp_time, $this->tmp_year, $this->null) = explode(' ', $this->viewStats('RegisteredOn'));
		list($this->tmp_hour, $this->tmp_min, $this->tmp_sec) = explode(':', $this->tmp_time);

		switch ($this->tmp_month)
		{
			case 'Jan'; $this->tmp_month = 1;  break;
			case 'Feb'; $this->tmp_month = 2;  break;
			case 'Mar'; $this->tmp_month = 3;  break;
			case 'Apr'; $this->tmp_month = 4;  break;
			case 'May'; $this->tmp_month = 5;  break;
			case 'Jun'; $this->tmp_month = 6;  break;
			case 'Jul'; $this->tmp_month = 7;  break;
			case 'Aug'; $this->tmp_month = 8;  break;
			case 'Sep'; $this->tmp_month = 9;  break;
			case 'Oct'; $this->tmp_month = 10; break;
			case 'Nov'; $this->tmp_month = 11; break;
			case 'Dec'; $this->tmp_month = 12; break;
		}

		$this->RegDate  = mktime($this->tmp_hour, $this->tmp_min, $this->tmp_sec, $this->tmp_month, $this->tmp_day, $this->tmp_year);
		$this->ActDate  = time();
		$this->DiffDate = ($this->ActDate-$this->RegDate);
		$this->ResInt   = ($this->DiffDate/$this->viewStats('Workunits'));

		$this->days     = floor($this->ResInt / 24 / 60 / 60 );

		$this->ResInt   = $this->ResInt - ($this->days*24*60*60);
		$this->hours    = floor($this->ResInt / 60 / 60);

		$this->ResInt   = ($this->ResInt - ($this->hours*60*60));
		$this->minutes  = floor($this->ResInt / 60);

		$this->ResInt   = $this->ResInt - ($this->minutes*60);
		$this->seconds  = floor($this->ResInt);

		$this->ResultInterval = array (
		"days"     => $this->days,
		"hours"    => $this->hours,
		"minutes"  => $this->minutes,
		"seconds"  => $this->seconds
		);
		return $this->ResultInterval;
	}

	function AverageResultsPerWeek()
	{
		return $this->viewStats('AverageResultsPerDay')*7;
	}

	function AverageResultsPerMonth()
	{
		return $this->viewStats('AverageResultsPerDay')*30;
	}

}
