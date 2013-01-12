<?php
//coded by [z]keep3r

require_once($_SERVER['DOCUMENT_ROOT'].'/includes/main.inc.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/layout.inc.php');
//include_once($_SERVER['DOCUMENT_ROOT'].'/includes/quotes.inc.php');
//require_once($_SERVER['DOCUMENT_ROOT'].'/includes/usersystem.inc.php');


// Form-Aktionen ausf?hren	
//Quotes::execActions();

echo head(40, "dreamjournal");
echo menu('main');
echo menu('user');


  function dream_add_form() {
    return(
    "<form action='$_SERVER[PHP_SELF]' method='post' enctype='multipart/form-data'>"
    .'<input type="hidden" name="do" value="add_dream">'

    ."<table width=\"$mainwidth\"><tr><td align=\"left\" class=\"title\">"
    ."Add Dream"
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
    

/*
    ."Preis:"
    ."</td><td align=\"left\" style=\"color:#".FONTCOLOR."; background-color:#".BACKGROUNDCOLOR.";border-bottom-style: solid; border-bottom-color: #".BORDERCOLOR."; border-bottom-width: 1px; border-left-style: solid; border-left-color: #".BORDERCOLOR."; border-left-width: 1px;\">"
    ."<input class='text' size='80' type=\"text\" name=\"preis\">"
    ."</td></tr><tr><td align=\"left\" style=\"font-weight: 600;\">"
*/



    ."</td></tr></table>"
    ."<input type='submit' class='button' name='send' value='speichern'>"
    ."</form>");

  }
  echo dream_add_form();
  
echo foot(52);
?>
