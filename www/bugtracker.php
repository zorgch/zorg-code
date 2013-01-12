<?PHP

// Libraries
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/main.inc.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/includes/bugtracker.inc.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/includes/forum.inc.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/includes/layout.inc.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/includes/usersystem.inc.php');


// Aktionen ausführen
Bugtracker::execActions();

// Bugliste ausgeben
if($_GET['bug_id'] == '') {

	$show = array();
	parse_str($_SERVER['QUERY_STRING']);
	if(count($show) == 0) {

		if($user->id > 0) {
			header(
				'Location: '
				.$_SERVER['PHP_SELF']
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
				.$_SERVER['PHP_SELF']
				.'?show[]=open'
				.'&show[]=notdenied'
				.'&show[]=assigned'
				.'&show[]=unassigned'
			);
			exit;
		}
	}


	echo head(1, "Bugtracker");

	echo menu("zorg");
	echo menu("utilities");
	echo menu("admin");

	echo '<h1>Bugtracker</h1>';

	echo(
		'<table class="border" style="border-collapse: collapse;" width="100%">'
		.'<form action="'.$_SERVER['PHP_SELF'].'" method="get">'
		.'<tr>'
		.'<td valign="top"><b>Show:</b></td>'
		.'<td>'
		.'<input name="show[]" type="checkbox" value="open" '.(in_array('open', $show) ? 'checked' : '').'>open'
		.'<br />'
		.'<input name="show[]" type="checkbox" value="resolved" '.(in_array('resolved', $show) ? 'checked' : '').'>resolved'
		.'</td><td>'
		.'<input name="show[]" type="checkbox" value="denied" '.(in_array('denied', $show) ? 'checked' : '').'>denied'
		.'<br />'
		.'<input name="show[]" type="checkbox" value="notdenied" '.(in_array('notdenied', $show) ? 'checked' : '').'>not denied'
		.'</td>'

		.'<td>'
		.'<input name="show[]" type="checkbox" value="assigned" '.(in_array('assigned', $show) ? 'checked' : '').'>assigned'
	);

	if($user->id > 0) {
		echo(
			'<input name="show[]" type="checkbox" value="own" '.(in_array('own', $show) ? 'checked' : '').'>mine'
			.'<input name="show[]" type="checkbox" value="notown" '.(in_array('notown', $show) ? 'checked' : '').'>not mine'
		);
	}

	echo(
		'<br />'
		.'<input name="show[]" type="checkbox" value="unassigned" '.(in_array('unassigned', $show) ? 'checked' : '').'>unassigned'
		.'</td>'
	);

	/*echo(
		'<br />'
		.'<input name="show[]" type="checkbox" value="unassigned" '.(in_array('unassigned', $show) ? 'checked' : '').'>unassigned'
		.'<br />'
		.Bugtracker::getFormFieldFilterCategory()
		.'</td>'
	);*/

	if($user->id > 0) {
		echo(
			'<td>'
			.'<input name="show[]" type="checkbox" value="new" '.(in_array('new', $show) ? 'checked' : '').'>new'
			.'<br />'
			.'<input name="show[]" type="checkbox" value="old" '.(in_array('old', $show) ? 'checked' : '').'>old'
			.'</td>'
		);
	}

	echo(
		'<td align="center">'
		.'<input class="button" type="submit" value="refresh">'
		.'</td>'
		.'</tr>'
		.'</form>'
		.'</table>'
		.Bugtracker::getBugList($show, $_GET['order'])
		.'<br />'
		.Bugtracker::getFormNewBugHTML()
		.'<br />'
		.Bugtracker::getFormNewCategoryHTML()
	);

// Bug ausgeben
} else {
	echo head(1, "Bugtracker");
	echo menu("zorg");
	echo menu("admin");
	echo '<h1>Bugtracker</h1>';

	if($_GET['action'] == 'editlayout') {
		echo Bugtracker::getBugHTML($_GET['bug_id'], TRUE);
	} else {
		echo Bugtracker::getBugHTML($_GET['bug_id']);
		Forum::printCommentingSystem('b', $_GET['bug_id']);
	}

}

echo foot();

?>