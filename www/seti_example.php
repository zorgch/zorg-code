<?
	require_once($_SERVER['DOCUMENT_ROOT'].'/includes/main.inc.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/includes/setistats.inc.php');
$seti = New SetiStats();
$seti->setEmail('keep3r@seti.zooomclan.org');
$seti->Init();


print "<html>"
	 ."<head>"
	 ."<title>setistats</title>"
	 ."</head>"
	 ."<body>";
?>

UserEmail:              <? print($seti->email);                               ?><br>
ServerUrl1:             <? print($seti->server1);                             ?><br>
ServerUrl2:             <? print($seti->server2);                             ?><br>
Username:               <? print($seti->viewStats('UserName'));               ?><br>
Wokunits:               <? print($seti->viewStats('Workunits'));              ?><br>
TotalCPUTime:           <? print($seti->viewStats('TotalCPUTime'));           ?><br>
AverageCPUTime:         <? print($seti->viewStats('AverageCPUTime'));         ?><br>
LastResult:             <? print($seti->viewStats('LastResult'));             ?><br>
RegisteredOn:           <? print($seti->viewStats('RegisteredOn'));           ?><br>
SetiUserFor:            <? print($seti->viewStats('SetiUserFor'));            ?><br>
GroupName:              <? print($seti->viewStats('GroupName'));              ?><br>
GroupURL:               <? print($seti->viewStats('GroupURL'));               ?><br>
Rank:                   <? print($seti->viewStats('Rank'));                   ?><br>
TotalUsers:             <? print($seti->viewStats('TotalUsers'));             ?><br>
TotalUsersWithThisRank: <? print($seti->viewStats('TotalUsersWithThisRank')); ?><br>
MoreWorkUnitsThan:      <? print($seti->viewStats('MoreWorkUnitsThan'));      ?><br>
AverageResultsPerDay:   <? print($seti->viewStats('AverageResultsPerDay'));   ?><br>
ResultsPerWeek:         <? print($seti->AverageResultsPerWeek());             ?><br>
ResultsPerMonth:        <? print($seti->AverageResultsPerMonth());            ?><br>
RegistrationClass:      <? print($seti->viewStats('RegistrationClass'));      ?><br>
ResultInterval:         <? $seti->ResultInt();								  
						   print $seti->ResultInterval["days"]." Days "
						   		.$seti->ResultInterval["hours"]." Hours "
						   		.$seti->ResultInterval["minutes"]." Minutes "
						   		.$seti->ResultInterval["seconds"]." Seconds"; ?>
</body>
</html>