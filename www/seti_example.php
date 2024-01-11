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
require_once __DIR__.'/includes/config.inc.php';
require_once INCLUDES_DIR.'main.inc.php';
require_once INCLUDES_DIR.'setistats.inc.php';
require_once MODELS_DIR.'core.model.php';

/**
 * Initialise MVC Model
 */
$model = new MVC\Seti();
$model->showOverview($smarty);

if ($user->is_loggedin() && $user->typ >= USER_MEMBER)
{
	/**
	 * Initialise SETI Stats Class-Object
	 */
	$seti = new SetiStats($_ENV['SETI_EMAIL']);
	// $seti->setEmail();
	// $seti->Init(); REPLACED WITH __construct($user_email)

	print '<html>'
		 .'<head>'
		 .'<title>setistats</title>'
		 .'</head>'
		 .'<body>';
	?>

	UserEmail:              <?php echo $seti->email                               ?><br>
	ServerUrl1:             <?php echo $seti->server1                             ?><br>
	ServerUrl2:             <?php echo $seti->server2                             ?><br>
	Username:               <?php echo $seti->viewStats('UserName')               ?><br>
	Wokunits:               <?php echo $seti->viewStats('Workunits')              ?><br>
	TotalCPUTime:           <?php echo $seti->viewStats('TotalCPUTime')           ?><br>
	AverageCPUTime:         <?php echo $seti->viewStats('AverageCPUTime')         ?><br>
	LastResult:             <?php echo $seti->viewStats('LastResult')             ?><br>
	RegisteredOn:           <?php echo $seti->viewStats('RegisteredOn')           ?><br>
	SetiUserFor:            <?php echo $seti->viewStats('SetiUserFor')            ?><br>
	GroupName:              <?php echo $seti->viewStats('GroupName')              ?><br>
	GroupURL:               <?php echo $seti->viewStats('GroupURL')               ?><br>
	Rank:                   <?php echo $seti->viewStats('Rank')                   ?><br>
	TotalUsers:             <?php echo $seti->viewStats('TotalUsers')             ?><br>
	TotalUsersWithThisRank: <?php echo $seti->viewStats('TotalUsersWithThisRank') ?><br>
	MoreWorkUnitsThan:      <?php echo $seti->viewStats('MoreWorkUnitsThan')      ?><br>
	AverageResultsPerDay:   <?php echo $seti->viewStats('AverageResultsPerDay')   ?><br>
	ResultsPerWeek:         <?php echo $seti->AverageResultsPerWeek()             ?><br>
	ResultsPerMonth:        <?php echo $seti->AverageResultsPerMonth()            ?><br>
	RegistrationClass:      <?php echo $seti->viewStats('RegistrationClass')      ?><br>
	ResultInterval:         <?php echo $seti->ResultInt()					      ?><br>
	<?php						  echo $seti->ResultInterval['days'].' Days '
										.$seti->ResultInterval['hours'].' Hours '
										.$seti->ResultInterval['minutes'].' Minutes '
										.$seti->ResultInterval['seconds'].' Seconds'; ?>
	</body>
	</html>
	<?php
}
/** Nicht eingeloggte User oder nicht Member */
else {
	http_response_code(403); // Set response code 403 (access denied) and exit.
	$smarty->assign('error', ['type' => 'warn', 'dismissable' => 'false', 'title' => 'Access denied', 'message' => 'Hier dürfen nur Member was machen. Tschau.']);
	$smarty->display('file:layout/head.tpl');
	$smarty->display('file:layout/footer.tpl');
}
