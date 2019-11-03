<?php
/**
 * SETI Stats example
 * coded by [z]keep3r
 *
 * @author [z]keep3r
 * @package zorg\SETI
 */

/**
 * File includes
 */
require_once( __DIR__ .'/includes/main.inc.php');
require_once( __DIR__ .'/includes/setistats.inc.php');
require_once(__DIR__.'/models/core.model.php');

/**
 * Initialise MVC Model
 */
$model = new MVC\Seti();

if ($user->is_loggedin() && $user->typ >= USER_MEMBER)
{
/**
 * Initialise SETI Stats Class-Object
 */
$seti = new SetiStats();
$seti->setEmail('keep3r@seti.zooomclan.org');
$seti->Init();

print '<html>'
	 .'<head>'
	 .'<title>setistats</title>'
	 .'</head>'
	 .'<body>';
?>

UserEmail:              <?= $seti->email                               ?><br>
ServerUrl1:             <?= $seti->server1                             ?><br>
ServerUrl2:             <?= $seti->server2                             ?><br>
Username:               <?= $seti->viewStats('UserName')               ?><br>
Wokunits:               <?= $seti->viewStats('Workunits')              ?><br>
TotalCPUTime:           <?= $seti->viewStats('TotalCPUTime')           ?><br>
AverageCPUTime:         <?= $seti->viewStats('AverageCPUTime')         ?><br>
LastResult:             <?= $seti->viewStats('LastResult')             ?><br>
RegisteredOn:           <?= $seti->viewStats('RegisteredOn')           ?><br>
SetiUserFor:            <?= $seti->viewStats('SetiUserFor')            ?><br>
GroupName:              <?= $seti->viewStats('GroupName')              ?><br>
GroupURL:               <?= $seti->viewStats('GroupURL')               ?><br>
Rank:                   <?= $seti->viewStats('Rank')                   ?><br>
TotalUsers:             <?= $seti->viewStats('TotalUsers')             ?><br>
TotalUsersWithThisRank: <?= $seti->viewStats('TotalUsersWithThisRank') ?><br>
MoreWorkUnitsThan:      <?= $seti->viewStats('MoreWorkUnitsThan')      ?><br>
AverageResultsPerDay:   <?= $seti->viewStats('AverageResultsPerDay')   ?><br>
ResultsPerWeek:         <?= $seti->AverageResultsPerWeek()             ?><br>
ResultsPerMonth:        <?= $seti->AverageResultsPerMonth()            ?><br>
RegistrationClass:      <?= $seti->viewStats('RegistrationClass')      ?><br>
ResultInterval:         <?= $seti->ResultInt()					       ?><br>			  
<?php						   print $seti->ResultInterval['days'].' Days '
						   		.$seti->ResultInterval['hours'].' Hours '
						   		.$seti->ResultInterval['minutes'].' Minutes '
						   		.$seti->ResultInterval['seconds'].' Seconds'; ?>
</body>
</html>
<?php
}
/** Nicht eingeloggte User oder nicht Member */
else {
	$model->showOverview($smarty);
	$smarty->display('file:layout/head.tpl');
	echo 'Hier dürfen nur Member was machen. Tschau.';
	$smarty->display('file:layout/footer.tpl');
}