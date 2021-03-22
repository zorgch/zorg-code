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
require_once dirname(__FILE__).'/includes/main.inc.php';
include_once INCLUDES_DIR.'bugtracker.inc.php';
require_once MODELS_DIR.'core.model.php';

/**
 * Initialise MVC Model
 */
$model = new MVC\Bugtracker();

/**
 * Validate GET-Parameters
 */
$bug_id = (isset($_GET['bug_id']) ? (int)$_GET['bug_id'] : null);
$show = (isset($_GET['show']) && !empty($_GET['show']) ? (array)$_GET['show'] : []);
$order = isset($_GET['order'])?$_GET['order']:'';

/** Aktionen ausführen */
Bugtracker::execActions();

/**
 * Bugtracker Übersichtsliste ausgeben
 */
if(empty($bug_id) || $bug_id <= 0)
{
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

	$model->showOverview($smarty);
	$htmlOutput = null;
	$htmlOutput .= t('buglist-headline', 'bugtracker');
	$htmlOutput .= '<form action="'.getURL(false,false).'" method="get">'
		.'<table class="border" style="border-collapse: collapse;" width="100%">'
		.'<thead>'
		.'<tr>'
		.'<th>'
		.'<input style="white-space: nowrap;" name="show[]" type="checkbox" value="open" '.(in_array('open', $show) ? 'checked' : '').'>open'
		.'<br />'
		.'<input style="white-space: nowrap;margin-left: 10px;" name="show[]" type="checkbox" value="resolved" '.(in_array('resolved', $show) ? 'checked' : '').'>resolved'
		.'</th><th>'
		.'<input style="white-space: nowrap;" name="show[]" type="checkbox" value="denied" '.(in_array('denied', $show) ? 'checked' : '').'>denied'
		.'<br />'
		.'<input style="white-space: nowrap;margin-left: 10px;" name="show[]" type="checkbox" value="notdenied" '.(in_array('notdenied', $show) ? 'checked' : '').'>not denied'
		.'</th>'

		.'<th>'
		.'<input style="white-space: nowrap;" name="show[]" type="checkbox" value="assigned" '.(in_array('assigned', $show) ? 'checked' : '').'>assigned'
	;

	if($user->typ >= USER_USER) {
		$htmlOutput .= '<input style="white-space: nowrap;margin-left: 10px;" name="show[]" type="checkbox" value="own" '.(in_array('own', $show) ? 'checked' : '').'>mine'
			.'<input style="white-space: nowrap;margin-left: 10px;" name="show[]" type="checkbox" value="notown" '.(in_array('notown', $show) ? 'checked' : '').'>not mine';
	}

	$htmlOutput .= '<br />'
		.'<input style="white-space: nowrap;" name="show[]" type="checkbox" value="unassigned" '.(in_array('unassigned', $show) ? 'checked' : '').'>unassigned'
		.'</th>';

	/* @TODO [Bug #406] Filter by Category (IneX)
	$htmlOutput .=(
		'<br />'
		.'<input name="show[]" type="checkbox" value="unassigned" '.(in_array('unassigned', $show) ? 'checked' : '').'>unassigned'
		.'<br />'
		.Bugtracker::getFormFieldFilterCategory()
		.'</th>'
	);*/

	if($user->typ >= USER_USER) {
		$htmlOutput .= '<th>'
			.'<input style="white-space: nowrap;" name="show[]" type="checkbox" value="new" '.(in_array('new', $show) ? 'checked' : '').'>new'
			.'<br />'
			.'<input style="white-space: nowrap;" name="show[]" type="checkbox" value="old" '.(in_array('old', $show) ? 'checked' : '').'>old'
			.'</th>';
	}

	$htmlOutput .= '<td align="center">'
		.'<input class="button" type="submit" value="filter">'
		.'</th>'
		.'</tr>'
		.'<thead>'
		.'</table>'
		.'</form>'
		.Bugtracker::getBugList($show, $order)
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
	$bug_data = Bugtracker::getBugRS($bug_id);

	$htmlOutput = null;
	if(isset($_GET['action']) && $_GET['action'] == 'editlayout')
	{
		$model->showEdit($smarty, $bug_id);
		$htmlOutput .= '<h1>Bug bearbeiten</h1>';
		$htmlOutput .= Bugtracker::getBugHTML($_GET['bug_id'], TRUE);
	} else {
		$model->showBug($smarty, $bug_id, $bug_data);
		$htmlOutput .= '<h1>'.$bug_data['title'].'</h1>';
		$htmlOutput .= '<div itemscope itemtype="http://schema.org/QAPage">'; // schema.org
		$htmlOutput .= Bugtracker::getBugHTML($_GET['bug_id']);
		$htmlOutput .= '</div>'; // schema.org
	}

	/** Layout */
	$smarty->display('file:layout/head.tpl');
	echo $htmlOutput;
	if (!isset($_GET['action']) || $_GET['action'] !== 'editlayout') Forum::printCommentingSystem('b', $_GET['bug_id']);
}

$smarty->display('file:layout/footer.tpl');
