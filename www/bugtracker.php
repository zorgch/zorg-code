<?php
/**
 * Bugtracker
 *
 * Bug und Feature Reporting und Tracking Tool für zorg
 *
 * @package zorg\Bugtracker
 */

/**
 * File includes
 */
require_once( __DIR__ .'/includes/main.inc.php');
include_once( __DIR__ .'/includes/bugtracker.inc.php');
require_once( __DIR__ .'/models/core.model.php');

/**
 * Initialise MVC Model
 */
$model = new MVC\Bugtracker();

/**
 * Validate GET-Parameters
 */
if (!empty($_GET['bug_id'])) $bug_id = (int)$_GET['bug_id'];
if (!empty($_GET['show'])) $show = (array)$_GET['show'];

/** Aktionen ausführen */
Bugtracker::execActions();

/**
 * Bugtracker Übersichtsliste ausgeben
 */
if(empty($bug_id) || $bug_id <= 0)
{
	if (!empty($show)) $show = array();
	parse_str($_SERVER['QUERY_STRING']);
	if(count($show) == 0)
	{
		if($user->is_loggedin())
		{
			header(
				'Location: '
				.'?show[]=open'
				.'&show[]=notdenied'
				.'&show[]=assigned'
				.'&show[]=unassigned'
				.'&show[]=new'
				.'&show[]=old'
				.'&show[]=own'
				.'&show[]=notown'
			);
			exit;
		} else {
			header(
				'Location: '
				.'?show[]=open'
				.'&show[]=notdenied'
				.'&show[]=assigned'
				.'&show[]=unassigned'
			);
			exit;
		}
	}

	//$smarty->assign('tplroot', array('page_title' => 'Bugtracker', 'page_link' => $_SERVER['PHP_SELF']));
	//echo menu("zorg");
	//echo menu("utilities");
	//if ($user->typ == USER_MEMBER) echo menu("admin");
	$model->showOverview($smarty);
	$htmlOutput = null;
	$htmlOutput .= '<h1>Bugtracker</h1>';

	$htmlOutput .= t('buglist-headline', 'bugtracker')
		.'<form action="'.getURL(false,false).'" method="get">'
		.'<table class="border" style="border-collapse: collapse;" width="100%">'
		.'<thead>'
		.'<tr>'
		.'<th valign="top"><b>Show:</b></th>'
		.'<th>'
		.'<input name="show[]" type="checkbox" value="open" '.(in_array('open', $show) ? 'checked' : '').'>open'
		.'<br />'
		.'<input name="show[]" type="checkbox" value="resolved" '.(in_array('resolved', $show) ? 'checked' : '').'>resolved'
		.'</th><th>'
		.'<input name="show[]" type="checkbox" value="denied" '.(in_array('denied', $show) ? 'checked' : '').'>denied'
		.'<br />'
		.'<input name="show[]" type="checkbox" value="notdenied" '.(in_array('notdenied', $show) ? 'checked' : '').'>not denied'
		.'</th>'

		.'<th>'
		.'<input name="show[]" type="checkbox" value="assigned" '.(in_array('assigned', $show) ? 'checked' : '').'>assigned'
	;

	if($user->typ >= USER_USER) {
		$htmlOutput .= '<input name="show[]" type="checkbox" value="own" '.(in_array('own', $show) ? 'checked' : '').'>mine'
			.'<input name="show[]" type="checkbox" value="notown" '.(in_array('notown', $show) ? 'checked' : '').'>not mine';
	}

	$htmlOutput .= '<br />'
		.'<input name="show[]" type="checkbox" value="unassigned" '.(in_array('unassigned', $show) ? 'checked' : '').'>unassigned'
		.'</th>';

	/*$htmlOutput .=(
		'<br />'
		.'<input name="show[]" type="checkbox" value="unassigned" '.(in_array('unassigned', $show) ? 'checked' : '').'>unassigned'
		.'<br />'
		.Bugtracker::getFormFieldFilterCategory()
		.'</th>'
	);*/

	if($user->typ >= USER_USER) {
		$htmlOutput .= '<th>'
			.'<input name="show[]" type="checkbox" value="new" '.(in_array('new', $show) ? 'checked' : '').'>new'
			.'<br />'
			.'<input name="show[]" type="checkbox" value="old" '.(in_array('old', $show) ? 'checked' : '').'>old'
			.'</th>';
	}

	$htmlOutput .= '<td align="center">'
		.'<input class="button" type="submit" value="refresh">'
		.'</th>'
		.'</tr>'
		.'<thead>'
		.'</table>'
		.'</form>'
		.Bugtracker::getBugList($show, $_GET['order'])
	;

	if($user->typ >= USER_USER)
	{
		$sidebarHtml = Bugtracker::getFormNewBugHTML();
		if($user->typ >= USER_MEMBER) $sidebarHtml .= Bugtracker::getFormNewCategoryHTML();
		$smarty->assign('sidebarHtml', $sidebarHtml);
	}

	/** Layout */
	$smarty->display('file:layout/head.tpl');
	echo $htmlOutput;

/**
 * Bug ausgeben
 */
} else {
	//echo head(1, "Bugtracker");
	$bug_data = Bugtracker::getBugRS($bug_id);
	//$smarty->assign('tplroot', array('page_title' => sprintf('Bug #%d - %s', $_GET['bug_id'], $bugTitle['title']), 'page_link' => '/bug/'.$_GET['bug_id']));
	$model->showBug($smarty, $bug_id, $bug_data);
	//echo menu('zorg');
	//echo menu('utilities');
	//if ($user->typ == USER_MEMBER) echo menu("admin");

	$htmlOutput = null;
	$htmlOutput .= '<h1>Bugtracker</h1>';

	if($_GET['action'] == 'editlayout')
	{
		$htmlOutput .= Bugtracker::getBugHTML($_GET['bug_id'], TRUE);
	} else {
		$htmlOutput .= '<div itemscope itemtype="http://schema.org/QAPage">'; // schema.org
		$htmlOutput .= Bugtracker::getBugHTML($_GET['bug_id']);
		$htmlOutput .= '</div>'; // schema.org
	}

	/** Layout */
	$smarty->display('file:layout/head.tpl');
	echo $htmlOutput;
	if ($_GET['action'] !== 'editlayout') Forum::printCommentingSystem('b', $_GET['bug_id']);
}

$smarty->display('file:layout/footer.tpl');
