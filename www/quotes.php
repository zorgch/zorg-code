<?php
/**
 * Quotes
 * coded by [z]keep3r
 *
 * @author [z]keep3r
 * @package zorg\Quotes
 */

/**
 * File includes
 */
require_once dirname(__FILE__).'/includes/main.inc.php';
require_once INCLUDES_DIR.'quotes.inc.php';
require_once MODELS_DIR.'core.model.php';

/**
 * Initialise MVC Model
 */
$model = new MVC\Quotes();

/**
 * Validate GET-Parameters
 */
if (!empty($_GET['quote_id'])) $quote_id = (int)$_GET['quote_id'];
if (!empty($_GET['do'])) $action = (string)$_GET['do'];
$userid = (isset($_GET['user_id']) ? (int)$_GET['user_id'] : (isset($user->id) ? $user->id : null));

/** Form-Post Aktionen ausführen */
Quotes::execActions();

/** Aenderung an Quote speichern */
if(isset($_POST['do']) && $_POST['do'] == 'edit_now' && $user->is_loggedin())
{
	//FIXME Quote Editing not implented yet./keep3r
}

/** Quote hinzufuegen */
elseif (isset($_POST['do']) && $_POST['do'] === 'add_now' && $user->is_loggedin())
{
	$sql = 'INSERT INTO quotes(user_id, date, text)
			VALUES('.$user->id.',"'.date('YmdHis').'","'.sanitize_userinput($_POST['text']).'")';
	$db->query($sql,__FILE__, __LINE__);

	//echo ('Quote hinzugef&uuml;gt');
	$smarty->assign('error', ['type' => 'success', 'dismissable' => 'true', 'title' => 'Quote hinzugef&uuml;gt']);
	unset($_GET['do']);
	$action = null;

}

/** Quote loeschen */
elseif (isset($action) && $action === 'delete_now' && $user->is_loggedin()) {
	$sql = 'SELECT * FROM quotes WHERE id = '.sanitize_userinput($quote_id);
	$result = $db->query($sql, __FILE__, __LINE__);
	$rs = $db->fetch($result, __FILE__, __LINE__);

	if ($rs["user_id"] == $user->id)
	{
		if(Quotes::isDailyQuote($quote_id))
		{
			Quotes::newDailyQuote();
		}
		$sql = 'DELETE FROM quotes WHERE id = '.(int)$_GET[quote_id];
		$db->query($sql,__FILE__, __LINE__);
		//echo "Quote gel&ouml;scht";
		$smarty->assign('error', ['type' => 'info', 'dismissable' => 'true', 'title' => 'Quote gel&ouml;scht']);
		unset($_GET['do']);
		$action = null;
	} else {
		$smarty->assign('error', ['type' => 'warn', 'dismissable' => 'false', 'title' => 'scho recht, tschipthorre']);
	}
}

/** Quotes ausgeben, ev. von speziellem User */
if (!isset($action) || isset($action) && $action === 'my')
{
	$sql = 'SELECT count(*) as anzahl FROM quotes '.(isset($action) && $action === 'my' ? 'WHERE user_id = '.$userid : '');
	$rs = $db->fetch($db->query($sql, __FILE__, __LINE__));
	$total = $rs['anzahl'];

	$curr_site_num = (isset($_GET['site']) && is_numeric($_GET['site']) && $_GET['site'] > 0 ? (int)$_GET['site'] : 0);
	$cnt = 10; // wird hier noch auf usercount gesetzt

	if (isset($action) && $action === 'my')
	{
		$sql = sprintf('SELECT * FROM quotes WHERE user_id = %s ORDER BY date DESC LIMIT %d,%d', $userid, $curr_site_num, $cnt);
	} else {
		$sql = sprintf('SELECT * FROM quotes ORDER BY date DESC LIMIT %d,%d', $curr_site_num, $cnt);
	}
	$result = $db->query($sql, __FILE__, __LINE__);

	$model->showOverview($smarty, $user, $userid, $curr_site_num);
	$smarty->display('file:layout/head.tpl');

	echo '<h1>Quotes</h1>';

	while ($rs = $db->fetch($result, __FILE__, __LINE__))
	{
		echo Quotes::formatQuote($rs);
		echo '<br>';
	}

	/** Ausgabe der Navigationspfeile */
	echo '<div>';
	if (empty($curr_site_num))
	{
		$curr_site_num += 10;
		if($total % 10 == 0){
			$last = $total - 10;
		} else {
			$last = $total - ($total % 10);
		}
		echo '<a href="?site='.$curr_site_num.(isset($action) && $action === 'my' ? '&do=my' : null).'">Next page &gt;</a>'
			 .'<span style="padding-left: 25px;"><a href="?site='.$last.(isset($action) && $action === 'my' ? '&do=my' : null).'">Last page &gt;&gt;</a></span>';

	} elseif ($curr_site_num >= 10 && $curr_site_num+$cnt < $total ) {

		$curr_site_num -= 10;
		echo '<a href="?site=0">&lt;&lt; First</a>'
			 .'<span style="padding-left: 25px;"><a href="?site='.$curr_site_num.(isset($action) && $action === 'my' ? '&do=my' : null).'">&lt; Prev</a></span>';

		$site_next = $curr_site_num + $cnt + 10;
		echo ' '.$curr_site_num.' - '.$site_next.' ';

		$curr_site_num += 20;
		if($total % 10 == 0){
			$last = $total - 10;
		} else {
			$last = $total - ($total % 10);
		}

		echo '<a href="?site='.$curr_site_num.(isset($action) && $action === 'my' ? '&do=my' : null).'">Next &gt;</a>'
			 .'<span style="padding-left: 25px;"><a href="?site='.$last.(isset($action) && $action === 'my' ? '&do=my' : null).'">Last &gt;&gt;</a></span>';

	} elseif ($curr_site_num+$cnt >= $total) {
		$curr_site_num -= 10;

		echo '<a href="?site=0">&lt;&lt; First</a>'
			 .'<span style="padding-left: 25px;"><a href="?site='.$curr_site_num.(isset($action) && $action === 'my' ? '&do=my' : null).'">&lt; Prev</a></span>';
	}

	echo '</div>';
}

// Quote hinzufügen
elseif (isset($action) && $action === 'add' && $user->is_loggedin()) {
	$model->showAddnew($smarty);
	$smarty->display('file:layout/head.tpl');
	if ($smarty->get_template_vars('error') != null) $smarty->display('file:layout/elements/block_error.tpl');
	//if ($smarty->getTemplateVars('foo') != null) $smarty->display('file:layout/elements/block_error.tpl'); // Smarty 3.x

	echo '<h1>Add Quote</h1>';
	echo '<form action="'.getURL(false,false).'" method="post" enctype="multipart/form-data">'
			.'<input type="hidden" name="do" value="add_now">'
			.'<style>@media (max-width: 767px){fieldset#quote{flex-direction: column;}}</style>'
			.'<fieldset id="quote" style="display: flex;white-space: wrap;align-items: stretch;justify-content: flex-start;"><label style="flex: 1;">Text<br>'
		 		.'<textarea type="text" name="text" id="text" class="text" style="width: 90%; height: 50px;" onkeypress="updateQuotePreview(this.value)"></textarea></label>'
		 		.'<blockquote id="preview" style="flex: 1; display: inline-block;"></blockquote>'
		 	.'</fieldset>'
			.'<input type="submit" name="send" value="speichern" class="button">'
			.'<script>function updateQuotePreview(content){document.getElementById("preview").innerHTML = content;}</script>'
		.'</form>';
}

// Quote wirklich loeschen?
elseif (isset($action) && $action === 'delete' && $user->is_loggedin()) {
	$sql = 'SELECT * FROM quotes where id = '.sanitize_userinput($quote_id);
	$result = $db->query($sql, __FILE__, __LINE__);
	$rs = $db->fetch($result, __FILE__, __LINE__);

	$model->showDelete($smarty, $quote_id);
	$smarty->display('file:layout/head.tpl');

	/*echo ("Willst du den Quote wirklich l&ouml;schen?<br>"
		 ."<a href=$PHP_SELF?do=delete_now&quote_id=$rs[id]>ja</a>"
		 ." / "
		 ."<a href=$PHP_SELF?site=$_GET[site]>nein</a>");
	*/
	$confirmSubject = 'Willst du den Quote wirklich l&ouml;schen?';
	$confirmMessage = '<a href="?do=delete_now&quote_id='.$rs['id'].'"">ja</a> / <a href="?site='.$_GET['site'].'">nein</a>';
	$smarty->assign('error', ['type' => 'warn', 'dismissable' => 'false', 'title' => $confirmSubject, 'message' => $confirmMessage]);
	$smarty->display('file:layout/elements/block_error.tpl');
} else {
	$model->showOverview($smarty, $user);
	$smarty->display('file:layout/head.tpl');
	$smarty->assign('error', ['type' => 'warn', 'dismissable' => 'false', 'title' => t('invalid-permissions-write', 'tpl')]);
	$smarty->display('file:layout/elements/block_error.tpl');
}

$smarty->display('file:layout/footer.tpl');
