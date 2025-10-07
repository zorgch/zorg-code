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
require_once __DIR__.'/includes/config.inc.php';
include_once INCLUDES_DIR.'bugtracker.inc.php';
require_once MODELS_DIR.'core.model.php';

/**
 * Initialise MVC Model
 */
$model = new MVC\Bugtracker();

/**
 * Validate GET-Parameters
 */
$doAction = filter_input(INPUT_GET, 'action', FILTER_DEFAULT, FILTER_REQUIRE_SCALAR) ?? null;
$bug_id = (isset($getBugId) ? $getBugId : (filter_input(INPUT_GET, 'bug_id', FILTER_VALIDATE_INT) ?? null));
$order = filter_input(INPUT_GET, 'order', FILTER_DEFAULT, FILTER_REQUIRE_SCALAR) ?? null;
// Filter handling
$show = [];
$allowedFilters = ['open','resolved','denied','notdenied','assigned','unassigned','new','old','own','notown'];
$show = filter_input(INPUT_GET, 'show', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY) ?? [];
$showCategory = isset($show['category']) && ctype_digit($show['category']) ? (int)$show['category'] : null;
$show = array_values(array_intersect($show, $allowedFilters)); // Nicht erlaubte ?show[]=xyz Werte entfernen/ignorieren
if (!empty($showCategory) && $showCategory > 0) $show['category'] = $showCategory;

/** Aktionen ausführen */
Bugtracker::execActions();

/**
 * Bugtracker Übersichtsliste ausgeben
 */
if(empty($bug_id) || $bug_id <= 0)
{
	if(empty($show))
	{
		$showDefaults = (isset($_ENV['BUGTRACKER_FILTER_DEFAULT'])
			? $_ENV['BUGTRACKER_FILTER_DEFAULT']
			: 'show[]=open'
		);

		if($user->is_loggedin()) $showDefaults .= '&show[]=own'
												 .'&show[]=notown';
		header('Location: ?'.$showDefaults);
		exit;
	}

	$model->showOverview($smarty);
	$htmlOutput = null;
	$htmlOutput .= t('buglist-headline', 'bugtracker');
	$htmlOutput .= '<form action="'.getURL(false,false).'" method="get">'
		.'<table class="border" style="border-collapse: collapse;" width="100%">'
		.'<thead>'
		.'<tr>'
		.'<th>'
		.'<label style="white-space: nowrap;"><input name="show[]" type="checkbox" value="open" '.(in_array('open', $show) ? 'checked' : '').'>open</label>'
		.'<br>'
		.'<label style="white-space: nowrap;"><input style="margin-left: 10px;" name="show[]" type="checkbox" value="resolved" '.(in_array('resolved', $show) ? 'checked' : '').'>resolved</label>'
		.'</th><th>'
		.'<label style="white-space: nowrap;"><input name="show[]" type="checkbox" value="denied" '.(in_array('denied', $show) ? 'checked' : '').'>denied</label>'
		.'<br>'
		.'<label style="white-space: nowrap;"><input style="margin-left: 10px;" name="show[]" type="checkbox" value="notdenied" '.(in_array('notdenied', $show) ? 'checked' : '').'>not denied</label>'
		.'</th><th>'
		.'<label style="white-space: nowrap;"><input name="show[]" type="checkbox" value="assigned" '.(in_array('assigned', $show) ? 'checked' : '').'>assigned</label>'
		.'<br>'
		.'<label style="white-space: nowrap;"><input name="show[]" type="checkbox" value="unassigned" '.(in_array('unassigned', $show) ? 'checked' : '').'>unassigned</label>'
		.'</th>'
	;

	if($user->typ >= USER_USER)
	{
		$htmlOutput .= '</tr><tr>'
					   .'<th colspan="2">'
					   .'<label style="white-space: nowrap;"><input name="show[]" type="checkbox" value="own" '.(in_array('own', $show) ? 'checked' : '').'>mine</label>'
					   .'<label style="white-space: nowrap;margin-left: 1rem;"><input name="show[]" type="checkbox" value="notown" '.(in_array('notown', $show) ? 'checked' : '').'>not mine</label>'
					   .'</th><th>'
					   .'<label style="white-space: nowrap;"><input name="show[]" type="checkbox" value="new" '.(in_array('new', $show) ? 'checked' : '').'>new</label>'
					   .'<label style="white-space: nowrap;margin-left: 1rem;"><input name="show[]" type="checkbox" value="old" '.(in_array('old', $show) ? 'checked' : '').'>old</label>'
					   .'</th>'
		;
	}

	$htmlOutput .= '</tr>';

	$htmlOutput .= '<tr>'
		.'<th colspan="2">'
		.'<label for="show[category]" class="hide-mobile">Category</label>'
		.Bugtracker::getFormFieldFilterCategory($showCategory)
		.'</th><th align="right">'
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
	if($doAction === 'editlayout' && $bug_id > 0)
	{
		$model->showEdit($smarty, $bug_id);
		$htmlOutput .= '<h1>Bug bearbeiten</h1>';
		$htmlOutput .= Bugtracker::getBugHTML($bug_id, TRUE);
	} else {
		$model->showBug($smarty, $bug_id, $bug_data);
		$htmlOutput .= '<h1>'.$bug_data['title'].'</h1>';
		$htmlOutput .= '<div itemscope itemtype="http://schema.org/QAPage">'; // schema.org
		$htmlOutput .= Bugtracker::getBugHTML($bug_id);
		$htmlOutput .= '</div>'; // schema.org
	}

	/** Layout */
	$smarty->display('file:layout/head.tpl');
	echo $htmlOutput;
	if (empty($doAction) || $doAction !== 'editlayout') Forum::printCommentingSystem('b', $bug_id);
}

$smarty->display('file:layout/footer.tpl');
