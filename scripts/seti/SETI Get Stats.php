<?php
if (! strcmp ($QUERY_STRING, 'source'))
{
	highlight_file ($SCRIPT_FILENAME);
	exit (0);
}
require_once ('aTeam.inc');

$aName    = array();
$aCredits = array();
$NumErrors = 0;

for ($i=0; $i<sizeof($aTeam); $i++)
{
	$fp = fopen ('https://setiathome.berkeley.edu/team_lookup.php?team_id='.$aTeam[$i], 'rt');
	
	$GotIt=false;
	if ($fp)
	{
	  while ($buf = fgets ($fp))
	  {
	    if ($Start=strpos ($buf, "<name>"))
	    {
	      $aName[] = substr ($buf, $Start+6, -8);
	    }
	    if ($Start=strpos ($buf, "<total_credit>"))
	    {
	      $buf = substr ($buf, $Start+14, -16);
	      $aCredits[] = $buf;
	      $GotIt=true;
	    }
	  }
	  fclose ($fp);
	}
	if (! $GotIt)
	{
	  $aName[] = "http error";
	  $aCredits[] = 0;
	  $NumErrors ++;
	}
}

if ($NumErrors > 4)
{
	die ("Too many HTTP errors");
}

$fp = fopen ('race.csv', 'at');
for ($i=0; $i<sizeof($aTeam); $i++)
{
	fwrite ($fp, sprintf ("%d;", $aCredits[$i]));
}
fwrite ($fp, date("j;G\n"));
fclose ($fp);

$fp = fopen ('teams.csv', 'wt');
for ($i=0; $i<sizeof($aTeam); $i++)
{
	fwrite ($fp, sprintf ("%s\n", $aName[$i]));
}
fwrite ($fp, sprintf ("DayOfMonth\nHourOfDay\n"));
fclose ($fp);
