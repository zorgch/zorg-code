<?php
/**
 * Dreamjournal
 * coded by [z]keep3r
 *
 * @author [z]keep3r
 * @package zorg\Dreamjournal
 */

/**
 * File includes
 */
require_once dirname(__FILE__).'/includes/main.inc.php';
require_once MODELS_DIR.'core.model.php';

/**
 * Initialise MVC Model
 */
$model = new MVC\Dreamjournal();
$model->showOverview($smarty);

function dream_add_form()
{
    return(
    '<form action="'.$_SERVER['PHP_SELF'].'" method="post" enctype="multipart/form-data">'
    .'<input type="hidden" name="do" value="add_dream">'

    ."<table width=\"$mainwidth\"><tr><td align=\"left\" class=\"title\">"
    ."<h2>Add Dream</h2>"
    ."</td></tr></table>"
    ."<br/>"
    ."<table cellpadding=\"1\" cellspacing=\"1\" width=\"500\" class=\"border\" align=\"center\">"
    ."<tr><td align=\"left\" style=\"font-weight: 600;\">"

    ."Titel:"
    ."</td><td align=\"left\" style=\"color:#".FONTCOLOR."; background-color:#".BACKGROUNDCOLOR.";border-bottom-style: solid; border-bottom-color: #".BORDERCOLOR."; border-bottom-width: 1px; border-left-style: solid; border-left-color: #".BORDERCOLOR."; border-left-width: 1px;\">"
      ."<input class='text' size='80' type=\"text\" name=\"titel\">"
    ."</td></tr><tr><td align=\"left\" style=\"font-weight: 600;\">"

    ."Text:"
    ."</td><td align=\"left\" style=\"color:#".FONTCOLOR."; background-color:#".BACKGROUNDCOLOR.";border-bottom-style: solid; border-bottom-color: #".BORDERCOLOR."; border-bottom-width: 1px; border-left-style: solid; border-left-color: #".BORDERCOLOR."; border-left-width: 1px;\">"
    ."<textarea class='text' type=\"text\" name=\"text\" cols=\"80\" rows=\"10\">"
    ."</textarea>"
    ."</td></tr><tr><td align=\"left\" style=\"font-weight: 600;\">"


    ."</td></tr></table>"
    ."<input type='submit' class='button' name='send' value='speichern'>"
    ."</form>");
 }

/** Only for logged in users */
if ($user->is_loggedin())
{
	$smarty->display('file:layout/head.tpl');
	echo dream_add_form();
}
else {
	http_response_code(403); // Set response code 403 (access denied) and exit.
	$smarty->assign('error', ['type' => 'warn', 'dismissable' => 'false', 'title' => 'Access denied', 'message' => 'You may still be dreaming and therefore not ready for this yet. Or you need to log in.']);
	$smarty->display('file:layout/head.tpl');
}

$smarty->display('file:layout/footer.tpl');
