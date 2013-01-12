<?PHP
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/colors.inc.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/forum.inc.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/smarty.inc.php');

echo '<html><body text="#FFFFFF">';

echo(
	'<table height="100%" width="400">'
	.'<tr>'
	
	.'<td bgcolor="#'.NEWCOMMENTCOLOR.'">'
	.'NEWCOMMENTCOLOR ('.NEWCOMMENTCOLOR.')</td>'
	
	.'<td bgcolor="#'.OWNCOMMENTCOLOR.'">'
	.'OWNCOMMENTCOLOR ('.OWNCOMMENTCOLOR.')</td>'
	
	.'<td bgcolor="#'.TABLEBACKGROUNDCOLOR.'">'
	.'TABLEBACKGROUNDCOLOR ('.TABLEBACKGROUNDCOLOR.')</td>'
	
	.'</tr>'
);

for($i = 0; $i < 30; $i++) {
	echo(
		'<tr>'
		.'<td bgcolor="#'.Forum::colorfade($i, NEWCOMMENTCOLOR).'">'
		.Forum::colorfade($i, NEWCOMMENTCOLOR).'</td>'
		.'<td bgcolor="#'.Forum::colorfade($i, OWNCOMMENTCOLOR).'">'
		.Forum::colorfade($i, OWNCOMMENTCOLOR).'</td>'
		.'<td bgcolor="#'.Forum::colorfade($i, TABLEBACKGROUNDCOLOR).'">'
		.Forum::colorfade($i, TABLEBACKGROUNDCOLOR).'</td>'
		.'</tr>'
	);
}
echo '</table>';

echo '<br /><br />';
echo '<b>Colors im smarty: </b><br />';

$vars = $smarty->get_template_vars();

echo '<table><tr><td height=30 width=100 bgcolor="'.$vars['color']['newcomment'].'">'.$vars['color']['background'].'</td></tr></table>';

echo '</body></html>';
?>