<?php
/**
 * zorg Custom Smarty Functions, Variables, Modifiers & Blocks
 *
 * @package zorg\Smarty\Templates
 */

/**
 * File includes
 */
require_once __DIR__.'/config.inc.php';
require_once INCLUDES_DIR.'mysql.inc.php';
include_once INCLUDES_DIR.'usersystem.inc.php';
include_once INCLUDES_DIR.'addle.inc.php';
include_once INCLUDES_DIR.'apod.inc.php';
include_once INCLUDES_DIR.'bugtracker.inc.php';
include_once INCLUDES_DIR.'events.inc.php';
include_once INCLUDES_DIR.'forum.inc.php';
include_once INCLUDES_DIR.'gallery.inc.php';
include_once INCLUDES_DIR.'hz_game.inc.php';
include_once INCLUDES_DIR.'go_game.inc.php';
include_once INCLUDES_DIR.'quotes.inc.php';
include_once INCLUDES_DIR.'stockbroker.inc.php';
include_once INCLUDES_DIR.'poll.inc.php';
include_once INCLUDES_DIR.'stl.inc.php';
include_once INCLUDES_DIR.'error.inc.php';
include_once INCLUDES_DIR.'peter.inc.php';
include_once INCLUDES_DIR.'rezepte.inc.php';

/**
 * Define and include the MCV Controllers and initialise Layout related settings.
 */
require_once CONTROLLERS_DIR.'layout.controller.php';
zorgDebugger::log()->debug('New MVC\Controller\Layout()');
$zorgLayout = new MVC\Controller\Layout();

/**
 * Arrays for Smarty
 *
 * Arrays to be used in Smarty Templates
 */
function var_request ()
{
   return [ 'page' => $_SERVER['PHP_SELF']
           ,'params' => isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : ''
           ,'url' => $_SERVER['PHP_SELF'].(isset($_SERVER['QUERY_STRING']) ? '?'.$_SERVER['QUERY_STRING'] : '')
           ,'tpl' => isset($_GET['tpl']) ? $_GET['tpl'] : ''
           ,'_tpl' => 'tpl:'.(isset($_GET['tpl'])?$_GET['tpl']:'')
           ,'_word' => 'word:'.(isset($_GET['tpl'])?$_GET['tpl']:'')
		];
}


/**
 * Modifiers for Smarty
 *
 * PHP Functions acting as Smarty Modifiers in Smarty Template Functions
 */
	/**
	 * String, Integer, Date and Array Modifiers
	 */
	function smarty_datename($params) {
		return datename($params['date']);
	}
	function smarty_htmlentities($params) {
		return htmlentities($params['text']);
	}
	function smarty_concat($text1, $text2) {
		return $text1.$text2;
	};
	function smarty_ltrim($text, $chars='') {
		if($chars != '') {
			return ltrim($text, $chars);
		} else {
			return ltrim($text);
		}
	};
	function smarty_print_r($myarray) {
		return print_r($myarray, true);
	}
	function smarty_implode($myarray, $zeichen) {
		return implode($zeichen, $myarray);
	}
	function smarty_floor($zahl) {
		return floor($zahl);
	}
	function smarty_sizebytes ($size) {
	   $units = array('B', 'kB', 'MB', 'GB', 'TB');
	   $i = 0;
	   while ($size >= 1000 && $i<5) {
	      $i++;
	      $size /= 1024;
	   }
	   $size = round($size, 2);
	   return sprintf('<nobr>%g %s</nobr>', $size, $units[$i]);
	}
	function smarty_quantity ($count, $singular="", $plural="") {
		if ($count == 1) return "$count $singular";
		else return "$count $plural";
	}
	function smarty_number_quotes($number, $num_decimal_places='', $dec_seperator='', $thousands_seperator='') {
		if (!$thousands_seperator) $thousands_seperator = '\'';
		if ($thousands_seperator)
			return number_format($number, $num_decimal_places, $dec_seperator, $thousands_seperator);
		else
			return number_format($number, $num_decimal_places, $dec_seperator, $thousands_seperator);
	}
	function smarty_maxwordlength($text, $maxlength) {
		return maxwordlength($text, $maxlength);
	}


	/**
	 * URL Modifiers
	 */
	function smarty_change_url($url, $querystringchanges) {
		return changeURL($url, $querystringchanges);
	}
	function smarty_strip_anchor($url) {
		return substr($url, 0, strpos($url, "#"));
	}

	/**
	 * User Information
	 */
	function smarty_userismobile ($userid)
	{
		global $user;
		return $user->ismobile($userid);
	}
	function smarty_usergroup ($groupid) {
		$group = (filter_var($groupid, FILTER_VALIDATE_INT) !== false) ? $groupid : -1;
		switch ($group) {
			case 0: return "Alle";
			case 1: return "Normale User";
			case 2: return "Member &amp; Sch&ouml;ne";
			case 3: return "Nur Besitzer";
			default: return "unknown_usergroup";
		}
	}
	function smarty_name ($userid) {
    	global $user;
    	return $user->id2user($userid, false);
    }
    function smarty_username ($userid) { // converts id to username
    	global $user;
    	//Original: return $user->link_userpage($userid);
    	return $user->userprofile_link($userid, ['username' => TRUE, 'pic' => FALSE, 'link' => TRUE]);
    }
    function smarty_userpic ($userid, $displayName=TRUE) {
    	global $user;
    	//Original: return $user->link_userpage($userid, $displayName);
    	//return $user->userpic($userid, $displayName);
    	return $user->userprofile_link($userid, ['username' => $displayName, 'clantag' => TRUE, 'pic' => TRUE, 'link' => TRUE]);
    }
    /**
	 * Smarty Funktion checkimage
	 * Gibt an, ob Userpic als Gravatar existiert oder nur als zorg Userpic
	 * @return array Array mit Userpic-Typ (gravatar / zorg) und dazugehörigem Userpic-Pfad
	 */
    function smarty_check_userimage ($userid) {
	    global $user;
	    if (!empty($userid) && is_numeric($userid)) $userimagePath = $user->checkimage($userid);
	    if ($userimagePath !== false) return ['type' => (strpos($userimagePath, 'gravatar') !== false ? 'gravatar' : 'zorg'), 'path' => $userimagePath];
	    else return 'false';
    }
    function smarty_userpage ($userid, $pic=0) {
    	global $user;
    	//Original: return $user->link_userpage($userid, $pic);
    	return $user->userprofile_link($userid, ['username' => TRUE, 'pic' => $pic, 'link' => TRUE]);
    }
    function smarty_logerror($params, &$smarty) {
	    error_log(sprintf('[SMARTY] <%s:%d> %s', $params['tpl'], $params['line'], $params['string']));
    }

/**
 * Blocks for Smarty
 *
 * PHP Function Output to be reused in Smarty Templates
 */
	/**
	 * String, Integer, Date und Array HTML-Ausgabe
	 */
	function smarty_substr ($params, $content, &$smarty, &$repeat) {
	   if (isset($params['to'])) {
	      return substr($content, $params['from'], $params['to']);
	   }else{
	      return substr($content, $params['from']);
	   }
	}
	function smarty_trim ($params, $content, &$smarty, &$repeat) {
		if ($content) {
			return trim($content);
		}
	}

	/**
	 * HTML Elemente
	 */
	/**
	 * gibt einen link aus
	 *
	 * @version 1.0
	 * @since 1.0 `[z]biko` function added
	 */
	function smarty_html_link ($params, $content, &$smarty, &$repeat) {
		if (!$content) $content = 'link';
		return '<a href="'.smarty_link($params).'">'.$content.'</a>';
	}
	/**
	 * gibt einen button als link aus
	 *
	 * @version 1.1
	 * @since 1.0 `[z]biko` function added
	 * @since 1.1 `29.09.2019` `IneX` added optional $params[class] setting
	 */
	function smarty_html_button ($params, $content, &$smarty, &$repeat) {
		return '<input type="button" value="'.$content.'" onClick="self.location.href=\''.smarty_link($params).'\'" '.($params['class'] ? 'class="'.$params['class'].'"' : '').'>';
	}
	/**
	 * HTML <form>-tag
	 * returns an opening-tag for a html-form. action is always 'smarty.php'
	 * if you set the parameter 'formid', a hidden input with this formid is added.
	 *
	 * @version 1.1
	 * @since 1.0 `[z]biko` function added
	 * @since 1.1 `29.09.2019` `IneX` added autocomplete=off as default
	 */
	function smarty_form ($params, $content, &$smarty, &$repeat)
	{
		$ret = null;

		if (!$_GET['tpl']) $_GET['tpl'] = '0';

		if ($params['url']) $url = $params['url'];
		elseif ($params['action']) $url = '/actions/'.$params['action'].'?'.url_params();
		else $url = "/?".url_params();

		if ($params['param']) $url .= '&'.$params['param'];

		$ret .= '<form method="post" action="'.$url.'" autocomplete="off" ';
		if ($params['upload']) $ret .= 'enctype="multipart/form-data"';
		$ret .= '>';
		if ($params['formid']) $ret .= '<input name="formid" type="hidden" value="'.$params['formid'].'">';
		$ret .= $content;
		$ret .= '</form>';

		return $ret;
    }

	function smarty_table ($params, $content, &$smarty, &$repeat) {
		global $table_color, $tr_count, $table_align, $table_valign;

		if (!$content) {
			$tr_count = 0;

			if ($params['align']) {
				$table_align = $params['align'];
				unset($params['align']);
			}else{
				$table_align = "";
			}

			if ($params['valign']) {
				$table_valign = $params['valign'];
				unset($params['valign']);
			}else{
				$table_valign = "";
			}

			if ($params['nocolor']) {
				$table_color = 0;
				unset($params['nocolor']);
			}else{
				$table_color = 1;
			}

		}else{

			$out = '<table ';
			foreach ($params as $key => $value) {
				if (!in_array($key, array("align", "valign", "nocolor")))
					$out .= $key.'="'.$value.'" ';
			}
			$out .= '>'.$content.'</table>';

			return $out;
		}
	}

	function smarty_tr ($params, $content, &$smarty, &$repeat) {
		global $tr_count, $tr_title, $tr_align, $tr_valign;

		if (!$content) {
			if ($params['title']) {
				$tr_title = 1;
				unset($params['title']);
			}else{
				$tr_count++;
				$tr_title = 0;
			}
			if ($params['align']) {
				$tr_align = $params['align'];
				unset($params['align']);
			}else{
				$tr_align = "";
			}
			if ($params['valign']) {
				$tr_valign = $params['valign'];
				unset($params['valign']);
			}else{
				$tr_valign = "";
			}
		}else{
			$out = '<tr ';
			foreach ($params as $key => $value) {
				if (!in_array($key, array("align", "valign", "title")))
					$out .= $key.'="'.$value.'" ';
			}
			$out .= '>'.$content.'</tr>';

			return $out;
		}
	}

	function smarty_td ($params, $content, &$smarty, &$repeat) {
		global $table_color, $table_align, $table_valign, $tr_count, $tr_title, $tr_align, $tr_valign, $user;

		if (!$content) {
		}else{
			$out = '<td ';
			if ($params['title'] || $tr_title) {
				$title = 1;
				unset($params['title']);
			}else{
				$title = 0;
			}

			if ($params['nobr']) {
				$nobr = 1;
				unset($params['nobr']);
			}else{
				$nobr = 0;
			}

			if ($tr_align && !$params['align']) {
				$out .= 'align="'.$tr_align.'" ';
			}
			if ($table_align && !$tr_align && !$params['align']) {
				$out .= "align='$table_align' ";
			}
			if ($tr_valign && !$params['valign']) {
				$out .= 'valign="'.$tr_valign.'" ';
			}
			if ($table_valign && !$tr_valign && !$params['valign']) {
				$out .= "valign='$table_valign' ";
			}

			foreach ($params as $key => $value) {
				$out .= $key.'="'.$value.'" ';
			}

			if($params['date'] > $user->lastlogin) {
				$out .= 'bgcolor="'.NEWCOMMENTCOLOR.'" ';
			} else {
				if (!$params['bgcolor'] && $table_color) {
					if (! ($tr_count % 2)) $out .= 'bgcolor="'.TABLEBACKGROUNDCOLOR.'" ';
				}
			}

			$out .= '>';
			if ($title) $out .= '<b>';
			if ($nobr) $out .= '<nobr>';
			$out .= $content;
			if ($nobr) $out .= '</nobr>';
			if ($title) $out .= '</b>';
			$out .= '</td>';

			return $out;
		}
	}

	function smarty_member ($params, $content, &$smarty, &$repeat) {
		global $user;

		if ($content) {
			if ($user->typ == USER_MEMBER) {

				if ($params['width']) $width = 'width="'.$params['width'].'"';
				else $width = '';

				if (!$params['noborder']) {
					return
						"<table class='border' cellspacing=0 cellpadding=3 $width>".
							"<tr><td align='left' bgcolor=".TABLEBACKGROUNDCOLOR."><b><font color='red'>Member only:</font></b></td></tr>".
							"<tr><td>$content</td></tr>".
						"</table>"
					;
				}else{
					return $content;
				}
			}else{
				return "";
			}
		}
	}

	/**
	 * Verein Mailer - Info Block
	 *
	 * @example {mail_infoblock topic="headline"}content{/mail_infoblock}
	 */
	function smarty_mailinfoblock ($params, $content, &$smarty, &$repeat) {
		//if (!$repeat) {  // closing tag
		$smarty->assign('infoblock', ['topic' => $params['topic'], 'text' => $content ]);
		return $smarty->fetch('file:email/verein/elements/block_info.tpl');
		//}
	}

	/**
	 * Verein Mailer - Call-to-Action Button Block
	 *
	 * @example {mail_button style="NULL|secondary" position="left|center|right" action="mail|link" href="url"}button-text{/mail_button}
	 */
	function smarty_mailctabutton ($params, $content, &$smarty, &$repeat) {
		if ($params['position'] == 'left') $ctaposition = 'float:left';
		if ($params['position'] == 'right') $ctaposition = 'float:right;';
		if ($params['position'] == 'center') $ctaposition = 'margin:0 auto';
		$smarty->assign('cta', [
								 'style' => $params['style']
								,'position' => $ctaposition
								,'action' => $params['action']
								,'href' => $params['href']
								,'text' => $content
								]);
		return $smarty->fetch('file:email/verein/elements/block_ctabutton.tpl');
	}

	/**
	 * Verein Mailer - Telegram Messenger Button Block
	 *
	 * @example {telegram_button}button-text{/telegram_button}
	 */
	function smarty_mailtelegrambutton ($params, $content, &$smarty, &$repeat) {
		$smarty->assign('telegrambtn', [
								 'text' => (!empty($content) ? $content : 'Telegram Chat beitreten' )
								,'href' => TELEGRAM_CHATLINK
								]);
		return $smarty->fetch('file:email/verein/elements/block_telegrambutton.tpl');
	}

	/**
	 * Verein Mailer - Swiss QR Bill Block
	 *
	 * @example {swissqrbillcode}Spende an zorg Verein{/swissqrbillcode}
	 * @example {swissqrbillcode size="s|m|l" user=23 betrag=23.00}zorg Verein Mitgliederbeitrag{/swissqrbillcode}
	 *
	 * @version 2.0
	 * @since 1.0 `01.12.2021` `IneX` Function added
	 * @since 2.0 `26.12.2024` `IneX` Code optimizations to increase stability & error handling
	 *
	 * @uses zorgSwissQRBill(), remove_html(), sanitize_userinput()
	 * @param array $params Contains defined function params like {swissqrbillcode size="m"...}
	 * @param string $content Contains the text between {swissqrbillcode}content{/swissqrbillcode}
	 * @return string
	 */
	function smarty_swissqrbillimage($params, $content, &$smarty, &$repeat)
	{
		global $user;

		/** Validate Params */
		$userid = (isset($params['user']) && (int)$params['user'] > 0 ? filter_var($params['user'], FILTER_VALIDATE_INT, ['flags' => FILTER_NULL_ON_FAILURE]) : null);
		$betrag = (isset($params['betrag']) && (float)$params['betrag'] > 0 ? filter_var($params['betrag'], FILTER_VALIDATE_FLOAT, ['flags' => FILTER_NULL_ON_FAILURE]) : null);
		$rechnungszweck = (isset($content) && !empty($content) ? remove_html(sanitize_userinput($content)) : ZORG_VEREIN_NAME);
		$sizeInput = (isset($params['size']) && !empty($params['size']) ? remove_html(sanitize_userinput($params['size'])) : 'm');
		switch ($sizeInput) {
			case 's':
				$imgStyle = 'width: 25%;';
				break;

			case 'm':
				$imgStyle = 'width: 50%;';
				break;

			case 'l':
				$imgStyle = 'width: 100%;';
				break;

			default:
				$imgStyle = 'width: 50%;';
		}

		/** Load the zorg Swiss QR Bill Class */
		if (require_once INCLUDES_DIR.'swissqrbill.inc.php')
		{
			$zorgQRCodeBill = new zorgSwissQRBill();
			$qrCodeImageString = $zorgQRCodeBill->generateQRCode($userid, $rechnungszweck, $betrag);
			if (false !== $qrCodeImageString && !empty($qrCodeImageString))
			{
				$imgContainerStyles = 'padding: 5mm;background-color: white;text-align: center;';
				$imgTitle = (null !== $rechnungszweck ? $rechnungszweck : null).
							(null !== $userid ? (null !== $rechnungszweck ? ' / ' : null).$user->id2user($userid) : null).
							(null !== $betrag ? (null !== $rechnungszweck || null !== $userid ? ': ' : null).ZORG_VEREIN_KONTO_CURRENCY.' '.$betrag : null);
				return sprintf('<div style="%s"><img %s style="%s" src="%s"></div>', $imgContainerStyles, (!empty($imgTitle) ? 'title="'.$imgTitle.'"' : null), $imgStyle, $qrCodeImageString);
			} else {
				/** QR-Bill Creation Error */
				error_log(sprintf('[WARN] <%s:%d> Invalid $zorgQRCodeBill->generateQRCode() using: %d | %s | %c', __METHOD__, __LINE__, $userid, $rechnungszweck, $betrag));
				return '<span>generateQRCode() Failed</span>';
			}
		} else {
			/** Require Class Error */
			error_log(sprintf('[ERROR] <%s:%d> Include failed: %s', __METHOD__, __LINE__, INCLUDES_DIR.'swissqrbill.inc.php'));
			return '<span>Include for generateQRCode() failed</span>';
		}
	}

	/**
	 * Smarty Menubar
	 *
	 * Usage: 	{menubar}
	 *				{menuitem tpl=x group="x"}text{/menuitem}
	 *				{menuitem...}
	 *			{/menubar}
	 *
	 * @author [z]biko
	 * @version 2.0
	 * @since 1.0 `[z]biko` migrated from smarty_menu.php (smarty_menu_old)
	 * @since 2.0 `02.07.2019` `IneX` moved inline HTML to block_menubar.tpl, output changed to Array
	 *
	 * @uses smarty_menuitem()
	 * @return object
	 */
	function smarty_menubar ($params, $content, &$smarty, &$repeat)
	{
		global $user;
		$vars = $smarty->get_template_vars();

		/** One iteration only */
		if (!$repeat)
		{
			$smarty->assign('menubar_content', [
								 'edit_link' => (tpl_permission($vars['tpl']['write_rights'], $vars['tpl']['owner']) ? edit_link_url($vars['tpl']['id']) : null )
								,'items' => preg_replace('/<\/a> *<a/', '</a><a', trim($content))
							]);
			return $smarty->fetch('file:layout/elements/block_menubar.tpl');
		}
	}
	/**
	 * Smarty Menubar Menu-Item Link
	 *
	 * Usage: {menuitem tpl=TplID-Link|url=URL-Link group="all|guest|user|member"}menuitem text{/menuitem}
	 *
	 * @author [z]biko
	 * @version 1.0
	 * @since 1.0 `[z]biko` migrated from smarty_menu.php (smarty_menu_old)
	 *
	 * @uses USER_MEMBER
	 * @uses smarty_menubar()
	 * @return string
	 */
	function smarty_menuitem ($params, $content, &$smarty, &$repeat) {
		global $user;

		/** One iteration only */
		if (!$repeat)
		{
			if (!isset($params['group'])) $params['group'] = "all";
			if(
				$params['group'] == "all"
				|| $params['group'] == "guest" && !$user->id
				|| $params['group'] == "user" && $user->id
				|| $params['group'] == "member" && $user->typ==USER_MEMBER
			) {
				if (!$content) $content = "???";
				return '<a href="'.smarty_link($params).'">'.trim($content).'</a>';
			}
		}
	}

	/**
	 * Smarty Templates
	 */
	function smarty_new_tpl_link ($params, $content, &$smarty, &$repeat) {
		global $smarty;

		$vars = $smarty->get_template_vars();

		return '<a href="/?tpleditor=1&tplupd=new&location='.base64url_encode($_SERVER['PHP_SELF'].'?'.url_params()).'">'.$content.'</a>';
	}
	function smarty_edit_link ($params, $content, &$smarty, &$repeat)
	{
		if (!$repeat) {  // closing tag
			if ($params['tpl']) {
				$tpl = $params['tpl'];
				$rights = 0;
				$owner = 0;
			}else{
				$vars = $smarty->get_template_vars();
				$tpl = $vars['tpl']['id'];
				$rights = $vars['tpl']['write_rights'];
				$owner = $vars['tpl']['owner'];
			}

			return edit_link($content, $tpl, $rights, $owner);
		}
	}
	function edit_link ($text='', $tpl=0, $rights=0, $owner=0) {
		global $db;

		if (!$text) $text = '[edit]';
		if (!$tpl) $tpl = $_GET['tpl'];

		if ($tpl && (!$rights || !$owner)) {
			$d = $db->fetch($db->query('SELECT * FROM templates WHERE id=?', __FILE__, __LINE__, __FUNCTION__, [$tpl]));
			$rights = $d['write_rights'];
			$owner = $d['owner'];
		}

		if ($tpl && tpl_permission($rights, $owner)) {
			return '<a href="'.edit_link_url($tpl).'">'.$text.'</a>';
		}else{
			return "";
		}
	}


/**
 * PHP Functions for Smarty
 *
 * PHP Functions to be used in Smarty Templates
 */
/** Stockbroker */
	function stockbroker_assign_stocklist($params, &$smarty) {
		global $stockbroker;
		$smarty->assign("stocklist", $stockbroker->getStocklist($params['anzahl'], $params['page']));
		//{assign_stocklist anzahl=100 page=$smarty.get.page}
	}
	function stockbroker_assign_stock($params, &$smarty) {
		global $stockbroker;
		$smarty->assign("stock", $stockbroker->getSymbol($params['symbol']));
		//{assign_kurs symbol=$kurs.symbol}
	}
	function stockbroker_assign_searchedstocks($params, &$smarty) {
		global $stockbroker;
		$smarty->assign("searchedstocks", $stockbroker->searchstocks($params['search']));
	}
	function stockbroker_update_kurs($params, &$smarty) {
		global $stockbroker;
		$stockbroker->updateKurs($params['symbol']);
	}
	function stockbroker_getkursbought($params) {
		global $user, $stockbroker;
		return $stockbroker->getKursBought($user->id, $params['symbol']);
	}
	function stockbroker_getkurs($params) {
		global $stockbroker;
		return $stockbroker->getKurs($params['symbol']);
	}

/** Tauschbörse */
	function smarty_num_new_tauschangebote ($params, &$smarty) {
		global $db, $user;
		if (isset($user->lastlogin)) {
			$result = $db->query(
				'SELECT COUNT(*) AS num FROM tauschboerse WHERE UNIX_TIMESTAMP(datum)>?',
				__FILE__, __LINE__, __FUNCTION__, [$user->lastlogin]
			);
			$rs = $db->fetch($result);
			//$smarty->assign("artikel", $rs);
			$smarty->assign("num_new_tauschangebote", $rs['num']);
		}
	}
	function smarty_assign_artikel ($params, &$smarty) {
		global $db;
		$result = $db->query(
			'SELECT *, CONVERT(kommentar USING latin1) kommentar, UNIX_TIMESTAMP(datum) AS datum FROM tauschboerse WHERE id=?',
			__FILE__, __LINE__, __FUNCTION__, [$params['id']]
		);
		$rs = $db->fetch($result);
		$smarty->assign("artikel", $rs);
	}
	/**
	 * Smarty Block: Prüft ob ein Tauschbörse Artikelbild existiert.
	 * @example {check_artikel_image id=$angebot.id}<img src="...">{/check_artikel_image}
	 * @uses fileExists()
	 *
	 * @version 1.0
	 * @since 1.0 `17.01.2024` `IneX` Bug #607 : Wenns kein Bild hat, den img-Tag weglassen.
	 */
	function smarty_check_tauschartikel_image($params, $content, &$smarty, &$repeat)
	{
		$artikelimgurl = null;
		if (isset($params['id']) && is_numeric($params['id']))
		{
			$artikelid = strval($params['id']);
			$artikelimgpath = TAUSCHARTIKEL_IMGPATH.$artikelid.USER_IMGEXTENSION;
			if (fileExists($artikelimgpath) !== false) {
				$artikelimgurl = TAUSCHBOERSE_IMGPATH_PUBLIC.$artikelid.USER_IMGEXTENSION;
			}
		}
		return (!empty($artikelimgurl) ? $content : '&nbsp;');
	}

/** Usersystem */
	function var_online_users ()
	{
		global $db;

		$online_users = array();
		$sql = 'SELECT id FROM user WHERE UNIX_TIMESTAMP(activity) > (UNIX_TIMESTAMP(?) - ?) ORDER by activity DESC';
		$e = $db->query($sql, __FILE__, __LINE__, __FUNCTION__, [timestamp(true), USER_TIMEOUT]);

		while ($d = mysqli_fetch_row($e)) {
			array_push($online_users, $d[0]);
		}
		return $online_users;
	}
	function smarty_FormFieldUserlist($params) {
		global $user;
		return $user->getFormFieldUserlist($params['name'], $params['size']);
	}
	function smarty_onlineusers($params) {
		global $user;
		if (!isset($params['images'])) $params['images'] = false;
		return $user->online_users($params['images']);
	}

/** Addle */
function smarty_addle_highscore ($params) {
	// wrapper function for addle highscore
	if (!isset($params['anzahl'])) $params['anzahl'] = 5;
	return highscore_dwz($params['anzahl']);
}
/*function smarty_addle_highscore ($params) {
	if (!isset($params[anzahl])) $params[anzahl] = 5;
	return highscore_dwz($params[anzahl]);
}*/

/** Peter */
function smarty_peter ($params, &$smarty) {
	$smarty->assign('peter_zuege', peter::peter_zuege());
}

/** Shoot the Lamber */
function smarty_stl_games ($params, &$smarty) {
	$smarty->assign('stl_shots', stl::getOpenSTLLink());
	$smarty->assign('stl_open_games', stl::getOpenSTLGames());
}

/** Quotes */
function smarty_getrandomquote ($params) {
	return Quotes::getRandomQuote(true);
}
function smarty_getdailyquote ($params) {
	return Quotes::getDailyQuote();
}

/**
 * Polls
 *
 * @since 2.0 `19.02.2020` `IneX` Added param validation and Poll Class handling
 */
	function smarty_poll ($params)
	{
		global $polls;
		if (!isset($params['id']) || empty($params['id']) || !is_numeric($params['id']) || intval($params['id'])<=0)
		{
			return smarty_error(['msg' => t('invalid-poll_id', 'poll', [$params['id']])]);
		} else {
			//$poll = new Polls(); --> Instantiated in poll.inc.php
			return $polls->show(intval($params['id']));
		}
	}

/** APOD */
	function smarty_apod ($params, &$smarty) {
		$rs = get_apod_id();
		return formatGalleryThumb($rs);
	}

/** Events */
	function smarty_assign_yearevents($params, &$smarty) {
		$smarty->assign("yearevents", Events::getEvents($params['year']));
	}
	function smarty_assign_event ($params, &$smarty) {
		$smarty->assign("event", Events::getEvent($params['id']));
	}
	function smarty_assign_visitors ($params, &$smarty) {
		$smarty->assign("visitors", Events::getVisitors($params['event_id']));
	}
	function smarty_assign_event_hasjoined($params, &$smarty) {
		global $user;
		$smarty->assign("event_hasjoined", Events::hasJoined($user->id, $params['event_id']));
	}
	function smarty_event_hasjoined($params, &$smarty) {
		global $user;
		return Events::hasJoined($user->id, $params['event_id']);
	}

/** Rezepte */
	function smarty_assign_rezepte ($params, &$smarty) {
		$smarty->assign("rezepte", Rezepte::getRezepte($params['category']));
	}
	function smarty_assign_rezept ($params, &$smarty) {
		$smarty->assign("rezept", Rezepte::getRezept($params['id']));
	}
	function smarty_assign_rezept_voted ($params, &$smarty) {
		// nur für eingeloggte
		$smarty->assign("rezept_voted", Rezepte::hasVoted($params['user_id'], $params['rezept_id']));
	}
	function smarty_assign_rezept_score ($params, &$smarty) {
		$smarty->assign("rezept_score", Rezepte::getScore($params['rezept_id']));
	}

/** SQL Errors */
    function smarty_sql_errors($params) {
 		return  get_sql_errors($params['num'],$params['order'],$params['oby']);
	}

/**
 * URL Handling
 */
	/**
	 * URL to a template called by smarty.php
	 *
	 * if parameter button is set, the link is shown as a button.
	 */
	function smarty_link ($params)
	{
	 	global $smarty, $user;
	 	$vars = $smarty->get_template_vars();
		$ret = '';

	 	if (isset($params['url'])) {
	 		$ret = $params['url'];
	 	}elseif (isset($params['word'])) {
	 		$ret = '/page/'.$params['word'];
	 	}elseif (isset($params['tpl'])) {
	    	$ret = "/tpl/".$params['tpl'];
	 	}elseif (isset($params['comment'])) {
	 		$ret = Comment::getLinkComment($params['comment']);
	 	}elseif (isset($params['user'])) {
	 		if (is_numeric($params['user'])) $ret = '/user/'.$params['user'];
	 		else $ret = '/user/'.$user->user2id($params['user']);
	 	}elseif (isset($params['action'])) {
	 		$ret .= '/actions/'.$params['action'].'?'.url_params();
	 	}else{
	 		$ret = '/?tpl='.$vars['tpl']['root'];
	 	}

        if (isset($params['param'])) $ret .= str_replace('?&', '?', (strpos($ret, '?') !== false ? '&' : '?').$params['param']);

        if (isset($params['hash'])) $ret .= '#'.$params['hash'];
        return $ret;
    }
    function smarty_get_changed_url ($params) {
		return getChangedURL ($params['change']);
	}
	/**
	 * Smarty Funktion urlExists()
	 * Utilities Funktion urlExists() um eine URL/Pfad zu validieren.
	 * Usage: {url_exists url="[url]" assign="[smarty-variable]"}
	 */
	function smarty_url_exists ($params, &$smarty)
	{
		$checkUrl = (urlExists($params['url']) === true ? 'true' : 'false');
		if (isset($params['assign'])) $smarty->assign($params['assign'], $checkUrl);
		else return $checkUrl;
	}

/**
 * HTML
 */
    function smarty_space ($params) { // inserts &nbsp;
        return str_repeat('&nbsp;', $params['i']);
    }

/**
 * String, Integer, Date and Array Functions
 */
	function smarty_sizeof ($params) {
		/** Fix sizeof() to only be called when variable is an array, and therefore guarantee it's Countable */
		return (is_array($params['array']) ? sizeof($params['array']) : 0);
	}
	function smarty_rand ($params, &$smarty) {
		mt_srand();

		if (isset($params['min']) && isset($params['max'])) $z = mt_rand($params['min'], $params['max']);
		elseif (isset($params['min']) && !isset($params['max'])) $z = mt_rand($params['min']);
		elseif (!isset($params['min']) && isset($params['max'])) $z = mt_rand(0, $params['max']);
		elseif (isset($params['min']) && !isset($params['max'])) $z = mt_rand();

		if (isset($params['assign'])) $smarty->assign($params['assign'], $z);
		else return $z;
	}

/**
 * Files
 */
	/**
	 * Read and print contents from a given textfile or from user files /files/[user-id]/
	 *
	 * Usage: {gettext id=[FILE-ID] linelength=[MAX-LINES]}
	 *    or: {gettext file=[PATH/WITHIN/FILES/FILE-NAME] linelength=[MAX-LINES]}
	 * @link http://zorg.local/thread/44614 Do ä Biispiel im Forum
	 *
	 * @author [z]cylander
	 * @version 1.1
	 * @since 1.0 `20.08.2004` `[z]cylander` function added
	 * @since 1.1 `15.01.2019` `IneX` added HTML escapes to file output
	 */
	function smarty_gettext ($params, &$smarty) {
		global $db;

		if ($params['file']) {
			$file = $params['file'];
			if (substr($file, -4) != '.txt') return '<font color="red"><b>[gettext: Can only read from txt-File]</b></font><br />';
			if (substr($file, 0, 1) == '/') $file = FILES_DIR.$file;
			if (!file_exists($file)) return '<font color="red"><b>[gettext: File "'.$file.'" not found]</b></font><br />';
		}elseif ($params['id']) {
			$e = $db->query('SELECT * FROM files WHERE id=?', __FILE__, __LINE__, __FUNCTION__, [$params['id']]);
			$d = $db->fetch($e);
			if ($d) {
				if (substr($d['name'], -4) != '.txt') return '<font color="red"><b>[gettext: Can only read from txt-File]</b></font><br />';
				$file = sprintf('%s%d/%s', FILES_DIR, $d['user'], $d['name']);
			}else{
				return '<font color="red"><b>[gettext: File mit id "'.$params['id'].'" in Filemanager nicht gefunden]</b></font><br />';
			}
		}else{
			return '<font color="red"><b>[gettext: Gib mittels dem Parameter "file" oder "id" eine Datei an]</b></font><br />';
		}
		if (DEVELOPMENT === true) error_log(sprintf('[DEBUG] <%s:%d> file path: %s', __FUNCTION__, __LINE__, $file));
		$out = '<div align="left"><pre>';

		/** Output only n lines (as passed) */
		if (isset($params['linelength']))
		{
			$len = $params['linelength'];
			if (DEVELOPMENT === true) error_log(sprintf('[DEBUG] <%s:%d> linelength: %d', __FUNCTION__, __LINE__, $len));
			if (!is_numeric($len) || $len < 1) {
				return '<font color="red"><b>[gettext: Parameter linelength has to be numeric and greater than 0]</b></font><br />';
			}
			$fcontent = file($file);
			foreach ($fcontent as $it) {
				while (strlen($it) > $len) {
					$out .= htmlspecialchars(substr($it, 0, $len)) . "\n   ";
					$it = htmlspecialchars(substr($it, $len));
				}
				$out .= htmlspecialchars($it);
			}
		}

		/** Output whole textfile at once */
		else{
			$out .= htmlspecialchars(file_get_contents($file));
		}
		if (DEVELOPMENT === true) error_log(sprintf('[DEBUG] <%s:%d> out: %s', __FUNCTION__, __LINE__, $out));
		$out .= '</pre></div>';

		return $out;
	}

/**
 * Commenting System und Forum Threads
 */
	function smarty_commentingsystem($params)
	{
		Forum::printCommentingSystem($params['board'], $params['thread_id']);
	}
	function smarty_comments ($params)
	{
		global $smarty, $user;

		$tplvars = $smarty->get_template_vars();
		if (!$params['board'] || !$params['thread_id'])
		{
 			$params['board'] = 't';
 			$params['thread_id'] = $tplvars['tpl']['id'];
 		}

		if (Thread::hasRights($params['board'], $params['thread_id'], $user->id))
		{
			/** Check if Template is not included in another Template */
			if ($tplvars['tpl']['id'] == $_GET['tpl'])
			{
				/** Check User preferences to show/hide Comments */
				if ($user->is_loggedin() && $user->show_comments)
				{
					echo '<table width="100%" cellspacing=0 cellpadding=0><tr><td width="100%" class="tiny border">'.
					 '<a class="threading switch collapse" href="/actions/show_tpl_comments.php?'.url_params().'&usershowcomments=0">'.
					 '<span style="padding-left:20px">Kommentare ausblenden</span></a>'.
					 '</td></tr><tr><td>';
					Forum::printCommentingSystem($params['board'], $params['thread_id']);
					echo '</td></tr></table>';

					return '';
				}
				/** User preferences = hide Comments */
				elseif ($user->is_loggedin()) {
					return '<table width="100%" cellspacing="0" cellpadding="0"><tr><td width="100%" class="tiny border">'.
							'<a class="threading switch expand" href="/actions/show_tpl_comments.php?'.url_params().'&usershowcomments=1">'.
							'<span style="padding-left: 20px;font-weight: 400;color: green;">Kommentare einblenden</span></a>'.
							'</td></tr><tr><td>';
				}
				/** For Guests, just show Comments (if permissions allow it) */
				else {
					echo '<h3>Comments</h3>';
					echo '<table width="100%" cellspacing=0 cellpadding=0><tr><td width="100%">';
					Forum::printCommentingSystem($params['board'], $params['thread_id']);
					echo '</td></tr></table>';
				}
			}
			/** On included Templates, hide Comments */
			else {
				return '<p><span style="padding-left: 20px;font-style: italic;font-weight: 400;color: green;">Kommentare werden beim include ausgeblendent. '
						.'<a href="?tpl='.$tplvars['tpl']['id'].'">Klick hier um sie zu sehen</a></span></p>';
			}
		}
		/** Access denied: don't show Comments */
		else {
			return '';
		}
	}
	function smarty_latest_threads ($params) {
	return Forum::getLatestThreads();
	}
	function smarty_latest_comments ($params) {
	return Forum::getLatestComments($params['anzahl'], $params['title'], $params['board']);
	}
	function smarty_3yearold_threads ($params) {
	return Forum::get3YearOldThreads();
	}
	function smarty_unread_comments ($params) {
	return Forum::getLatestUnreadComments($params['title'], $params['board']);
	}

	/**
	 * Smarty Function "forum_boards"
	 *
	 * Returns all Forum boards
	 * Usage: {forum_boards boards=$user->forum_boards_unread updateable=true/false}
	 *
	 * @author IneX
	 * @version 1.0
	 * @since 1.0 `30.09.2018` method added
	 * @uses Forum::getForumBoards()
	 */
	function smarty_get_forum_boards ($params) {
		return Forum::getForumBoards($params['boards'], $params['updateable']);
	}

	/**
	 * Smarty Information
	 */
    function smarty_error ($params) {
	      if ($params['msg']) {
	         return '<p><font color="red"><b>'.$params['msg'].'</b></font></p>';
	      }else{
	         return "";
	      }
    }
    function smarty_state ($params) {
	      if ($params['msg']) {
	         return '<p><font color="green"><b>'.$params['msg'].'</b></font></p>';
	      }else{
	         return "";
	      }
    }
	/**
	 * @uses edit_link_url()
	 */
 	function smarty_edit_link_url($params, &$smarty)
	 {
		if (!$params['tpl']) {
			$vars = $smarty->get_template_vars();
			$params['tpl'] = $vars['tpl']['id'];
		}
		return edit_link_url($params['tpl']);
	}
	/**
	 * Stich together Template Edit-URL
	 *
	 * @version 1.1
	 * @since 1.0 Method added
	 * @since 1.1 `22.10.2020` `IneX` added URL encoding of base64-part (because valid b64 chars like = can interfer with URL)
	 *
	 * @uses base64url_encode()
	 * @param int $tpl Template-ID for which to create Edit-URL
	 * @return string
	 */
	function edit_link_url($tpl)
	{
		return '/?tpleditor=1&tplupd='.$tpl.'&location='.base64url_encode($_SERVER['PHP_SELF'].'?'.url_params());
	}

	/**
	 * Letze Smarty Updates
	 *
	 * Gibt eine Tabelle mit Links zu den letzten upgedateten Smartys
	 *
	 * @return String
	 */
	function getLatestUpdates($params = array()) {
		global $db, $user;

		if (!$params['anzahl']) $params['anzahl'] = 5;

		$sql = 'SELECT *, UNIX_TIMESTAMP(last_update) as date FROM templates ORDER BY last_update DESC LIMIT ?';
		$result = $db->query($sql, __FILE__, __LINE__, __FUNCTION__, [$params['anzahl']]);

		$i=0;
		$html = '<table class="border" width="100%"><tr><td align="center" colspan="3"><b>letzte Änderungen</b></td></tr>';
		while($rs = $db->fetch($result)) {
	    	$i++;

			$color = ($i % 2 == 0) ? BACKGROUNDCOLOR : TABLEBACKGROUNDCOLOR;

			$html .=
			'<tr class="small"><td align="left" bgcolor="'.$color.'">'
			.'<a href="/?tpl='.$rs['id'].'">'.stripslashes($rs['title']).' ('.$rs['id'].')'.'</a>'
			.'</td><td align="left" bgcolor="'.$color.'" class="small">'
			.$user->link_userpage($rs['update_user'])
			.'</td><td align="left" bgcolor="'.$color.'" class="small"><nobr>'
			.datename($rs['date'])
			.'</nobr></td></tr>'
			;

		}
		$html .= '</table>';

		return $html;
	}

/**
 * Menu
 */
 	/**
	 * Display a navigation menu using {menu name=menubar}
	 *
	 * @version 2.0
	 * @since 1.0 `[z]biko` function added
	 * @since 2.0 `30.09.2019` `IneX` adjusted with new responsive HTML Layout structure
	 */
	function smarty_menu ($params, &$smarty)
	{
		global $db, $user;

		$vars = $smarty->get_template_vars();

		// if (isset($vars['tpl_parent']) && $vars['tpl_parent']['id'] == $vars['tpl_root']['id'])
		// {
			if (isset($params['tpl'])) {
				$e = $db->query('SELECT * FROM templates WHERE id=?', __FILE__, __LINE__, __FUNCTION__, [$params['tpl']]);
				$d = $db->fetch($e);
				if (tpl_permission($d['read_rights'], $d['owner']))
				{
					return $smarty->fetch('tpl:'.$params['tpl']);
				}
			} else {
				$e = $db->query('SELECT m.* FROM menus m, templates t WHERE name=? AND t.id = m.tpl_id',
								 __FILE__, __LINE__, __FUNCTION__, [$params['name']]);
				$d = $db->fetch($e);
				if ($d && tpl_permission($d['read_rights'], $d['owner']))
				{
					return $smarty->fetch('tpl:'.$d['tpl_id']);
				//}elseif ($d) {
					//return '</nav>';
				} else {
					return '<font color="red"><b>[Menu "'.$params['name'].'" not found]</b></font><br />';
				}
			}
		// }
	}

	/**
	 * Add or remove a Menu-Entry from the database table `menus`
	 *
	 * @author [z]biko
	 * @version 1.0
	 * @since 1.0 `[z]biko` function added
	 * @since 2.0 `16.09.2019` `IneX` function updated for zorg v4 to prevent updating new 'id' table row
	 *
	 * @param string $name Name of a Menubar (e.g. 'zorg'), containing a {menubar}{menuitem...}{/menubar}-structure
	 */
	function smarty_menuname_exec ($name)
	{
		global $db, $smarty;

		$vars = $smarty->get_template_vars();
		$tpl_id = $vars['tpl']['id'];

		$namePlain = htmlentities($name, ENT_QUOTES); // remove any html from the menuname
		$nameArray = explode(' ', $namePlain); // convert menuname into Array

		/*for ($i=0; $i<sizeof($name); $i++) {
			if ($it) {
				$menu = $db->fetch($db->query('SELECT * FROM menus WHERE name="'.$name[$i].'"', __FILE__, __LINE__, __METHOD__));
				if ($menu && $menu['tpl_id'] != $tpl) return 'Menuname "'.$name[$i].'" existiert schon und wurde nicht registriert.<br />';
				unset($name[$i]);
			}
		}*/
		foreach ($nameArray as $it)
		{
			zorgDebugger::log()->debug('«%s» on tpl_id %s', [$it, strval($tpl_id)]);
			if (!empty($it)) {
				/** Check if menu with same name already exists... */
				$menuExists = $db->fetch($db->query('SELECT * FROM menus WHERE name=?', __FILE__, __LINE__, __FUNCTION__, [$it]));
				//if (DEVELOPMENT === true) error_log(sprintf('[DEBUG] <%s:%d> $menuExists Query: %s', __FUNCTION__, __LINE__, print_r($menuExists,true)));
				if ($menuExists !== false && $menuExists['tpl_id'] === $tpl_id)
				{
					zorgDebugger::log()->debug('$menuExists: TRUE (tpl_id: %d)', [strval($tpl_id)]);
					//return sprintf('Menuname "%s" existiert schon mit der id#%d und wurde deshalb nicht gespeichert!<br>Bitte anderen Namen verwenden.', $it, $tpl_id);
				}

				/** Menu mit $name gibt es noch nicht, deshlab erstellen wir es neu */
				else {
					zorgDebugger::log()->debug('$menuExists: FALSE (adding new)');
					$db->query('INSERT INTO menus (tpl_id, name) VALUES (?, ?)', __FILE__, __LINE__, __FUNCTION__, [$tpl_id, $it]);
					//$smarty->assign('error', ['type' => 'success', 'dismissable' => 'true', 'title' => sprintf('Neues Menu "%s" erfolgreich gespeichert', $it), 'message' => 'Du kannst es jetzt im Template-Editor einer Page auswählen.']);
				}
			}
		}

		return '';
	}

	/**
	 * Smarty Array "$smarty_menus"
	 *
	 * Returns all Smarty Menus (Smarty-Menutemplates) as an Array
	 * Usage: {$smarty_menus}
	 *
	 * @author IneX
	 * @version 1.0
	 * @since 1.0 `30.09.2018` `IneX` function added
	 */
	function smarty_get_menus()
	{
		global $db;

		$sql = 'SELECT name, tpl_id as id FROM menus ORDER BY name';
		$result = $db->query($sql, __FILE__, __LINE__, __METHOD__);
		if (!empty($result) && $result !== false)
		{
			while ($menuTemplate = $db->fetch($result)) $menus[] = $menuTemplate;
			return $menus;
			//$smarty->assign('smarty_menus', $menus);
		} else {
			return false;
		}
	}


/**
 * Gallery
 */
	function smarty_assign_users_on_pic ($params, &$smarty) {
		$smarty->assign("users_on_pic", getUsersOnPic($params['picID']));
	}
	function smarty_get_randomalbumpic($params) {
		return getAlbumLinkRandomThumb($params['album_id'], $params['show_title'], $params['image_quality']);
	}
		/**
		 * Smarty Function "top_pics"
		 *
		 * Returns a specific amount of best rated images from a given gallery
		 * Usage: {top_pics album=41 limit=1}
		 *
		 * @author IneX
		 * @version 1.0
		 * @since 1.0 `23.06.2007` `IneX` function added as part of Bug #609
		 *
		 * @uses getTopPics()
		 * @param array $params All passed Smarty-Function parameters, allowed: album, limit, options
		 * @return string HTML displaying top rated n amount of Gallery-Pics
		 */
		function smarty_top_pics ($params)
		{
			/** Validate and assign passed $params */
		 	$album_id = (empty($params['album']) ? null : $params['album']);
		 	$limit = (empty($params['limit']) ? 5 : $params['limit']);
		 	$options = (empty($params['options']) ? false : true);

	 		return getTopPics($album_id, $limit, $options);
		}
		/**
		 * Smarty Function "user_pics"
		 *
		 * Returns a specific amount of Gallery Pictures on which a given User has been tagged
		 * Usage: {user_pics user=41 limit=1}
		 *
		 * @author IneX
		 * @version 1.0
		 * @since 1.0 `18.10.2013` `IneX` Function added
		 */
		function smarty_user_pics($params)
		{
			$userid = ($params['user'] == '' ? 0 : $params['user']);
		 	$limit = ($params['limit'] == '' ? 0 : $params['limit']);
		 	//$options = ($params['options'] == '' ? '' : $params['options']);

	 		//return getUserPics($userid, $limit, $options);
	 		return getUserPics($userid, $limit);
		}


/**
 * Chat
 */
	/*function smarty_chat() { Inaktiv?! IneX, 2.5.09
  	return Chat::getInterfaceHTML();
	}*/
	function smarty_assign_chatmessages($params, &$smarty) {
		global $db;

		$anzahl = ($params['anzahl'] == '' ? 10 : $params['anzahl']);
		$page = ($params['page'] == '' ? 0 : $params['page']);

		$sql = "SELECT COUNT(*) AS anz from chat";
		$result = $db->fetch($db->query($sql, __FILE__, __LINE__, __FUNCTION__));
		$num = $result['anz'];//$db->num($result);

		$sql = 'SELECT chat.text, UNIX_TIMESTAMP(date) AS date, user.username AS username, user.clan_tag AS clantag, chat.user_id, chat.from_mobile
				FROM chat LEFT JOIN user ON (chat.user_id=user.id) ORDER BY date ASC LIMIT ?,?';
		//echo $sql;
		$result = $db->query($sql, __FILE__, __LINE__, __FUNCTION__, [(($num-$anzahl)-($page*$anzahl)), $anzahl]);

		while ($rs = $db->fetch($result)) {
		  $chatmessages[] = $rs;
		}
		$smarty->assign("chatmessages", $chatmessages);
	}


/**
 * Compiler Functions
 *
 * ACHTUNG: compiler-funktionen müssen php-code zurückgeben!
 */
function smarty_menuname ($name, &$smarty) {
	return 'echo smarty_menuname_exec("'.$name.'");';
}


/**
 * Smarty Plugins
 *
 * Third party plugin functions to extend Smarty with new functionality
 */
	/*
	 * assign_adv
	 * -------------------------------------------------------------
	 * Type:     function
	 * Name:     assign_adv --> geändert zu "assign_array" damit es verständlicher ist, 6.8.07/IneX
	 * File:     function.assign_adv.php
	 * Version:  0.11
	 * Purpose:  assigns smarty variables including arrays and range arrays
	 * Author:   Bill Wheaton <billwheaton atsign mindspring fullstop com>
	 * Synopsis:
	 *      {assign_adv var="myvar" value="array('x','y',array('a'=>'abc'))"}
	 *      or
	 *      {assign_adv var="myvar" value="range(1,2)"}
	 *      or
	 *      {assign_adv var="myvar" value="myvalue"}
	 *
	 * Description: assign_adv is a direct and backward compatable replacement
	 *  of assign.  It adds extra features, hence the '_adv' extention.
	 *  The extra features are:
	 *      value - can now contain a string formatted as a valid PHP array code or range code.
	 *          the code is checked to see if it matches array(...) or range(...), and if so
	 *          evaluates an array or range code from the contents of them (...).
	 *
	 * Examples:
	 *  assign an array of hashes of javascript events (useful for html_field_group):
	 *      {assign_adv
	 *              var='events'
	 *              value="array(
	 *                      array(
	 *                          'onfocus'=>'alert(\'Dia guit\');',
	 *                          'onchange'=>'alert(\'Slainte\');'
	 *                          ),
	 *                      array(
	 *                          'onfocus'=>'alert(\'God be with you\');',
	 *                          'onchange'=>'alert(\'Cheers\');'
	 *                          )
	 *                      )" }
	 * or assign a range of days to select for calendaring & scheduling
	 *      {assign_adv var='repeatdays' value="range(1,30)" }
	 *
	 * Justification: Some might say "shoot, why not just write all your code in templates".  Well,
	 *      I'm not really.  assign already assigns scalars, so allowing arrays and hashes seems
	 *      logical.  I'm willing to draw the line there.
	 *
	 * Downside: Its slower to use assign_adv, so while you can use it as a replacement for
	 *      assign, unless you need to assign an array, use assign instead.  assign_adv uses
	 *      a PHP eval statement to facilitate it which can eat some time.
	 *
	 * See Also: function.assign.php
	 *
	 * ChangeLog: beta 0.10 first release (Bill Wheaton)
	 *            beta 0.11 changed regular expression and flow control (Soeren Weber)
	 *
	 * COPYRIGHT:
	 *     Copyright (c) 2003 Bill Wheaton
	 *     This software is released under the GNU Lesser General Public License.
	 *     Please read the following disclaimer
	 *
	 *      THIS SOFTWARE IS PROVIDED ''AS IS'' AND ANY EXPRESSED OR IMPLIED
	 *      WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES
	 *      OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
	 *      DISCLAIMED.  IN NO EVENT SHALL THE AUTHOR OR CONTRIBUTORS BE
	 *      LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY,
	 *      OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT
	 *      OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS;
	 *      OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF
	 *      LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING
	 *      NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
	 *      SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
	 *
	 *     See the GNU Lesser General Public License for more details.
	 *
	 * You should have received a copy of the GNU Lesser General Public
	 * License along with this library; if not, write to the Free Software
	 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
	 *
	 * -------------------------------------------------------------
	 */
	function smarty_function_assign_array($params, &$smarty)
	{
		extract($params);

		if (empty($var)) {
			trigger_error("assign_array: missing 'var' parameter");
			return;
		}

		if (!in_array('value', array_keys($params))) {
			trigger_error("assign_array: missing 'value' parameter");
			return;
		}

		if (!in_array('array', array_keys($params)) XOR !in_array('range', array_keys($params))) {
			trigger_error("assign_array: missing 'value=array()' or 'value=range()'");
			return;
		}

		if (preg_match('/^\s*array\s*\(\s*(.*)\s*\)\s*$/s',$value,$match)){
			eval('$value=array('.str_replace("\n", "", $match[1]).');');
		}
		else if (preg_match('/^\s*range\s*\(\s*(.*)\s*\)\s*$/s',$value,$match)){
			eval('$value=range('.str_replace("\n", "", $match[1]).');');
		}

		$smarty->assign($var, $value);
	}

	/**
	 * Smarty |rendertime modifier function
	 *
	 * Type:	Modifier
	 * Name:	timer --> geändert zu "rendertime" damit es verständlicher ist, 07.01.2017/IneX
	 * Date:	Sat Nov 01, 2003
	 * Author:	boots
	 *
	 * Assuming you do little or nothing after display()
	 * and that you do minimal work before starting the timer,
	 * this technique should be close to actual processing time
	 * close enough that it is splitting hairs, IMHO
	 * Usage:
	 * - in index.php
	 *    util::timer('begin', 'Page'); // starts a timer block with the message 'Page'
	 *    // other code
	 *    echo 'history: '.util::timer('list'); // returns a history of timed block results
	 * - in template.tpl
	 *    {"begin"|timer:"template block"}
	 *    template stuff...
	 *    {"end"|timer:true}
	 *    {"stop"|timer:true|nl2br}
	 * Call : $smarty->register_modifier('rendertime', 'rendertime');
	 *
	 * @link https://www.smarty.net/forums/viewtopic.php?p=5750&sid=ede53d9870b3a4cd1fd6a4cfd8cfae1b#5750 Smarty '|timer' modifier function
	 */
	function smarty_modifier_rendertime($mode='begin')
	{
		global $_timer_blocks, $_timer_history;
		switch ($mode) {
			case 'begin':
				$_timer_blocks[] =array(microtime());
				break;

			case 'end':
				$last = array_pop($_timer_blocks);
				$_start = $last[0];
				list($a_micro, $a_int) = explode(' ', $_start);
				list($b_micro, $b_int) = explode(' ', microtime());
				$elapsed = ($b_int - $a_int) + ($b_micro - $a_micro);
				$_timer_history[] = [ $elapsed ];
				return $elapsed;

			case 'list':
				$o = '';
				foreach ($_timer_history as $mark) {
					$o .= $mark[2] . " \n";
				}
				return $o;

			case 'stop':
				$result = '';
				while(!empty($_timer_blocks)) {
					$result .= smarty_modifier_rendertime('end');
				}
				return $result;
			}
	}


/**
 * Smarty Template Function Handler
 *
 * This Class contains various functions/function-mappings between
 * PHP and the Smarty Template engine. Further, it takes care
 * of properly registering/assigning the functions or methods
 * to Smarty as Template Functions, so they can be used within
 * Smarty templates using somethling like {custom_function}
 *
 * @ToDo This stuff here doesn't work in a Class Context yet...
 * @ToDo Probably needs to use $this-> context for everything - Functions, Constants, etc.?
 *
 * @package zorg\Smarty
 * @author IneX
 * @version 1.0
 * @since 1.0 `03.01.2016` `IneX` Class added
 */
//class SmartyZorgFunctions
//{

	/**
	 * PHP Functions, Arrays, Modifiers and Blocks for Smarty
	 *
     * These are all the PHP Functions and Arrays we want to register in Smarty
     * var Array format for Functions: '[PHP Function Name]' => '[Optional*: Smarty TPL Function Name]'
     * var Array format for Arrays: [Variable-Name] => array ([Werte], [Kategorie], [Beschreibung], [Members only: true/false])
     * *if Smarty TPL Function Name is empty/false, the PHP Function Name will be used!
     *
     * @author IneX
     * @version 1.0
     * @since 1.0 `03.01.2016` `IneX` Method added
     * @var array
     */
    $zorg_php_vars = array( //Format: [Variable-Name] => array ([Werte] | [Kategorie] | [Beschreibung] | [Members only true/false])
								 'color' => array(SMARTY_COLORS, 'Layout', 'Array mit allen Standardfarben (wechselt zwischen Tag und Nacht)', false)
								,'event_newest' => array(Events::getEventNewest(), 'Events', 'Zeigt neusten Event an', false)
								,'nextevents' => array(Events::getNext(), 'Events', 'Zeigt nächsten kommenden Event an', false)
								,'eventyears' => array(Events::getYears(), 'Events', 'Zeigt alle Jahre an, in denen Events erfasst sind', false)
								,'rezept_newest' => array(Rezepte::getRezeptNewest(), 'Rezepte', 'Zeigt neustes Rezept an', false)
								,'categories' => array(Rezepte::getCategories(), 'Rezepte', 'Zeigt Liste von Rezept-Kategorien an', false)
								,'num_errors' => array($num_errors, 'System', 'Zeigt Anzahl geloggter SQL-Errors an', false)
								,'sun' => array($zorgLayout->sun, 'Layout', 'Zeigt an ob Sonne "up" oder "down" ist', false)
								,'sunset' => array($zorgLayout->sunset, 'Layout', 'Zeit des nächsten SonnenUNTERgangs', false)
								,'sunrise' => array($zorgLayout->sunrise, 'Layout', 'Zeit des nächsten SonnenAUFgangs', false)
								,'country' => array($zorgLayout->country, 'Layout', 'ISO-Code des ermittelten Landes des aktuellen Besuchers', false)
								,'country_image' => array($zorgLayout->country_flagicon, 'Layout', 'Bildpfad zur Länderflagge des ermittelten Landes', false)
								,'request' => array(var_request(), 'URL Handling', 'associative array:  page = requested page / params = url parameter / url = page+params', false)
								,'url' => array(getURL(), 'URL Handling', 'Gesamte aktuell aufgerufene URL (inkl. Query-Parameter)', false)
								,'self' => array($_SERVER['PHP_SELF'], 'URL Handling', 'Self = Aktuelle Seiten-URL', false)
								,'user' => array($user, 'Usersystem', 'Array mit allen User-Informationen des aktuellen Besuchers', false) // TODO change to $smarty->register_object()
								,'usertyp' => array(array('alle'=>USER_ALLE, 'user'=>USER_USER, 'member'=>USER_MEMBER, 'special'=>USER_SPECIAL), 'Usersystem', 'Array mit allen vorhandenen Usertypen: alle, user, member und special', false)
								,'user_mobile' => array((isset($user->from_mobile)?$user->from_mobile:''), 'Usersystem', 'Zeigt an ob aktueller Besucher mittels Mobiledevice die Seite aufgerufen hat', false)
								,'user_ip' => array((isset($user->last_ip)?$user->last_ip:''), 'Usersystem', 'IP-Adresse des aktuellen Besuchers', false)
								,'comments_default_maxdepth' => array(DEFAULT_MAXDEPTH, 'Layout', 'Standart angezeigte Tiefe an Kommentaren z.B. im Forum', false)
								,'online_users' => array(var_online_users(), 'Usersystem', 'Array mit allen zur Zeit eingeloggten Usern', false)
								,'num_new_events' => array(Events::getNumNewEvents(), 'Events', 'Zeigt Anzahl neu erstellter Events an', true)
								,'login_error' => array((isset($login_error)?$login_error:null), 'Usersystem', 'Ist leer oder enthält Fehlermeldung eines versuchten aber fehlgeschlagenen Logins eines Benutzers', false)
								,'code_info' => array(getGitCodeVersion(), 'Code Info', 'Holt die aktuellen Code Infos (Version, last commit, etc.) aus dem Git HEAD', false)
								,'smarty_menus' => array(smarty_get_menus(), 'Smarty', 'Array mit allen verfügbaren Smarty-Menutemplates', true)
  						 );

	/**
	 * PHP Functions as Modifiers for Smarty Functions
     *
	 * @var array
	 */
    $zorg_php_modifiers = array( //Format: [PHP-Funktion] => array ([Smarty-Modifier] | [Kategorie] | [Beschreibung] | [Members only true/false])
								 'datename' => array('datename', 'Datum und Zeit', '{$timestamp|datename} konviertiert einen timestamp in ein anständiges datum/zeit Format', false)
								,'stripslashes' => array('stripslashes', 'Variablen', 'Modifier für die Funktion stripslashes() wie in PHP', false)
								,'strstr' => array('strstr', 'Variablen', 'Modifier für die Funktion strstr() wie in PHP', false)
								,'stristr' => array('stristr', 'Variablen', 'Modifier für die Funktion stristr() wie in PHP (Gross-/Kleinschreibung ignorieren)', false)
								,'smarty_sizebytes' => array('sizebytes', 'Variablen', 'stellt z.B: ein "kB" dahinter und konvertiert die zahl.', false)
								,'smarty_quantity' => array('quantity', 'Variablen', '{$anz|quantity:Zug:Züge}', false)
								,'smarty_number_quotes' => array('number_quotes', 'Variablen', 'Registriert für Smarty den Modifier number_quotes() aus PHP', false)
								,'htmlentities' => array('htmlentities', 'Variablen', 'Registriert für Smarty den Modifier htmlentities() aus PHP', false)
								,'base64url_decode' => ['base64decodeurl', 'URL Handling', 'Dekodiert die spezielle URL-safe base64 Strings für {$url} Variablen; Beispiel: {$url|base64encodeurl}', false]
								,'base64_decode' => array('base64decode', 'Variablen', 'Registriert für Smarty den Modifier base64_decode() aus PHP', false)
								,'base64url_encode' => array('base64encodeurl', 'URL Handling', 'Spezielle URL-safe base64_decode() für {$url} Variablen; Beispiel: {$url|base64encodeurl}', false)
								,'base64_encode' => array('base64encode', 'Variablen', 'Registriert für Smarty den Modifier base64_encode() aus PHP', false)
								,'smarty_concat' => array('concat', 'Variablen', 'Registriert für Smarty den Modifier concat() aus PHP', false)
								,'smarty_ltrim' => array('ltrim', 'Variablen', 'Registriert für Smarty den Modifiert ltrim() aus PHP', false)
								,'smarty_maxwordlength' => array('maxwordlength', 'Variablen', 'Registriert für Smarty den Modifier maxwordlength() aus PHP, 1.param = word length', false)
								,'smarty_name' => array('name', 'Usersystem', 'usersystem', false)
								,'smarty_username' => array('username', 'Usersystem', '{$userid|username} konvertiert userid zu username', false)
								,'smarty_userpic' => array('userpic', 'Usersystem', '{$userid|userpic:0} zeigt Userpic für eine User-ID, 1.param = Username anzeigen ja/nein', false)
								,'smarty_usergroup' => array('usergroup', 'Usersystem', '{$id|usergroup} für tpl schreib / lese rechte', false)
								,'smarty_userpage' => array('userpage', 'Usersystem', '{$userid|userpage:0} , 1.param = username (0) or userpic (1)', false)
								,'smarty_check_userimage' => array('check_userimage', 'Usersystem', '{$userid|@check_userimage} - ersetzt $userid mit Array["typ","pfad"],', false)
								,'smarty_userismobile' => array('ismobile', 'Usersystem', '{$userid|ismobile} ermittelt ob letzter Login eines Users per Mobile war', false)
								,'smarty_strip_anchor' => array('strip_anchor', 'URL Handling', 'link', false)
								,'smarty_change_url' => array('change_url', 'URL Handling', 'newquerystring', false)
								,'smarty_print_r' => array('print_r', 'Variablen', 'ACHTUNG {$myarray|@print_r} verwenden!', false)
								,'smarty_implode' => array('implode', 'Variablen', 'String glue', false)
								,'smarty_floor' => array('floor', 'Mathematische Funktionen', 'util', false)
								,'print_array' => array('print_array', 'Variablen', '{print_array arr=$hans} gibt die Elemente eines Smarty {$array} aus', false)
								,'rendertime' => array('smarty_modifier_rendertime', 'System', 'Smarty Template Rendering-Time', false, true)
								);

	/**
	 * PHP Function Output as HTML-Blocks for Smarty Templates
	 *
	 * @var array
	 */
	$zorg_php_blocks 	= array( //Format: [PHP-Funktion] => array ([Block] | [Kategorie] | [Beschreibung] | [Members only true/false])
								 'smarty_zorg' => array('zorg', 'Layout', '{zorg title="Titel"}...{/zorg}	displays the zorg layout (including header, menu and footer)', false)
								,'smarty_html_link' => array('link', 'HTML', '{link tpl=x param="urlparams"}text{/a}	default tpl = das aktuelle', false)
								,'smarty_new_tpl_link' => array('new_link', 'Smarty Template', 'shows a link to the editor with new tpl.', false)
								,'smarty_html_button' => array('button', 'HTML', '{button tpl=x param="urlparams"}button-text{/button}', false)
								,'smarty_form' => array('form', 'HTML', '{form param="urlparams" formid=23 upload=1}..{/form}', false)
								,'smarty_table' => array('table', 'HTML', 'layout, table', false)
								,'smarty_tr' => array('tr', 'HTML', 'layout, table > tr', false)
								,'smarty_td' => array('td', 'HTML', 'layout, table > tr > td', false)
								,'smarty_menubar' => array('menubar', 'Layout', 'menu', false)
								,'smarty_menuitem' => array('menuitem', 'Layout', 'menu', false)
								,'smarty_edit_link' => array('edit_link', 'Smarty Template', '{edit_link tpl=x}  link zum tpl-editor, default ist aktuelles tpl', false)
								,'smarty_substr' => array('substr', 'Variablen', '{substr from=2 to=-1}text{/substr}  // gleich wie php-fnc substr(text, from, to)', false)
								,'smarty_trim' => array('trim', 'Variablen', 'text modification', false)
								,'smarty_member' => array('member', 'Layout', '{member}..{/member}   {member noborder=1}..{/member}', false)
								,'smarty_mailinfoblock' => array('mail_infoblock', 'Verein Mailer - Info Block', '{mail_infoblock topic="headline"}...{/mail_infoblock}', false)
								,'smarty_mailctabutton' => array('mail_button', 'Verein Mailer - Call-to-Action-Button', '{mail_button style="NULL|secondary" position="left|center|right" action="mail|link" href="url"}button-text{/mail_button}', false)
								,'smarty_mailtelegrambutton' => array('telegram_button', 'Verein Mailer - Telegram Messenger Button', '{telegram_button}button-text{/telegram}', false)
								,'smarty_swissqrbillimage' => ['swissqrbillcode', 'zorg Swiss QR Bill - QR-Code', '{swissqrbillcode size="s|m|l" user=23 betrag=23.00}zorg Verein Mitgliederbeitrag{/swissqrbillcode}', true]
								,'smarty_check_tauschartikel_image' => [ 'check_artikel_image', 'Tauschbörse', 'Renders content only if Tauschbörse Artikel-Image exists. Usage: {check_artikel_image id=$angebot.id}<img src="...">{/check_artikel_image}', false ]

								);

	/**
	 * PHP Functions as Template Functions for Smarty
     * Format:
     *    unique_name = array( {array([PHP-Klasse], }[PHP-Funktion]{)}, [Tpl-Funktion], [Kategorie], [Beschreibung], [Members only true/false], [Compiler Function true/false])
	 * @var array
	 */
    $zorg_php_functions = array(
  								 'menuname' => [ 'smarty_menuname', 'menuname', 'Layout', 'Compiler Funktion: echo() eines Menus basierend auf dessen Menuname (retourniert PHP)', false, true ] // Compiler Funktion
								,'addle_highscore' => [ 'smarty_addle_highscore', 'addle_highscore', 'Addle', 'Addle', false, false ]
								,'apod' => [ 'smarty_apod', 'apod', 'APOD', 'Astronomy Picture of the Day (APOD)', false, false ]
								,'assign_chatmessages' => [ 'smarty_assign_chatmessages', 'assign_chatmessages', 'Chat', 'Chat', false, false ]
								,'assign_yearevents' => [ 'smarty_assign_yearevents', 'assign_yearevents', 'Events', 'events', false, false ]
								,'assign_event' => [ 'smarty_assign_event', 'assign_event', 'Events', 'events', false, false ]
								,'assign_visitors' => [ 'smarty_assign_visitors', 'assign_visitors', 'Events', 'events', false, false ]
								,'assign_rezepte' => [ 'smarty_assign_rezepte', 'assign_rezepte', 'Rezepte', 'rezepte', false, false ]
								,'assign_rezept' => [ 'smarty_assign_rezept', 'assign_rezept', 'Rezepte', 'rezepte', false, false ]
								,'assign_rezept_score' => [ 'smarty_assign_rezept_score', 'assign_rezept_score', 'Rezepte', 'rezepte', false, false ]
								,'url' => [ 'smarty_link', 'url', 'URL Handling', '&lt;a href={link id=x word="x" param="urlparams"}&gt;  default tpl ist das akutelle', false, false ]
								,'space' => [ 'smarty_space', 'spc', 'HTML', '{space i=5}', false, false ]
								,'error' => [ 'smarty_error', 'error', 'System', '{error msg="Fehler!"}', false, false ]
								,'state' => [ 'smarty_state', 'state', 'System', '{state msg="Update erfolgreich"}', false, false ]
								,'gettext' => [ 'smarty_gettext', 'gettext', 'File Manager', 'files / filemanager', false, false ]
								,'comments' => [ 'smarty_comments', 'comments', 'Commenting', '{comments}  fügt comments zu diesem tpl an.', false, false ]
								,'latest_comments' => [ 'smarty_latest_comments', 'latest_comments', 'Commenting', '{latest_comments anzahl=10 board=t title="Tabellen-Titel"}  // letzte comments aus board (optional)', false, false ]
								,'latest_threads' => [ 'smarty_latest_threads', 'latest_threads', 'Forum', '{latest_threads}', false, false ]
								,'unread_comments' => [ 'smarty_unread_comments', 'unread_comments', 'Forum', '{unread_comments board=t title="Tabellen-Titel"}', false, false ]
								,'getForumBoards' => [ 'smarty_get_forum_boards', 'forum_boards', 'Forum', '{forum_boards boards=$user->forum_boards_unread updatable=true/false}', false, false ]
								,'3yearold_threads' => [ 'smarty_3yearold_threads', '3yearold_threads', 'Forum', '{3yearold_threads}', false, false ]
								,'commentingsystem' => [ 'smarty_commentingsystem', 'commentingsystem', 'Commenting', 'forum, comments', false, false ]
								,'random_pic' => [ 'getRandomThumb', 'random_pic', 'Gallery', '{random_pic}  displays a random thumb out of the gallery', false, false ]
								,'daily_pic' => [ 'getDailyThumb', 'daily_pic', 'Gallery', '{daily_pic}   displays the pic of the day', false, false ]
								,'random_albumpic' => [ 'smarty_get_randomalbumpic', 'random_albumpic', 'Gallery', 'Gets a random Pic of a specific Gallery Album.<br>Parameters supported: <code>abum_id=[1-9]</code>, <code>show_title=[true/false*]</code>, <code>image_quality=[normal*|high]</code> (* Defaults)', false, false ]
								,'top_pics' => [ 'smarty_top_pics', 'top_pics', 'Gallery', 'gallery', false, false ]
								,'user_pics' => [ 'smarty_user_pics', 'user_pics', 'Gallery', 'gallery', false, false ]
								,'assign_users_on_pic' => [ 'smarty_assign_users_on_pic', 'assign_users_on_pic', 'Gallery', 'gallery', false, false ]
								,'menu' => [ 'smarty_menu', 'menu', 'Layout', 'menu', false, false ]
								,'random_quote' => [ 'smarty_getrandomquote', 'random_quote', 'Quotes', '{random_quote} display a random quote', false, false ]
								,'daily_quote' => [ 'smarty_getdailyquote', 'daily_quote', 'Quotes', '{daily_quote} display a daily quote', false, false ]
								,'poll' => [ 'smarty_poll', 'poll', 'Polls', '{poll id=23}', false, false ]
								,'shoot the lamber' => [ 'smarty_stl_games', 'get_stl_games', 'STL', '{get_stl_games} Shoot the lamber: lädt Link zu offene Spielzügen & Link zu offenen STL-Spielen für den User', false, false ]
								//,'stl_shots' => [ 'smarty_stl_shots', 'assign_stl_shots', 'STL', '{stl_shots} Shoot the lamber: offene Spielzüge für den User', false, false ]
								//,'stl_open_games' => [ 'smarty_stl_open_games', 'assign_stl_open_games', 'STL', '{stl_open_games} Shoot the lamber: offene Games zum joinen für den User', false, false ]
								,'latest_updates' => [ 'getLatestUpdates', 'latest_updates', 'Smarty Templates', '{latest_updates}  table mit den letzten smarty-updates', false, false ]
								,'edit_url' => [ 'smarty_edit_link_url', 'edit_url', 'Smarty Templates', '{edit_url tpl=x}  tpl ist optional. default: aktuelles tpl.', false, false ]
								,'spaceweather' => [ 'spaceweather_ticker', 'spaceweather', 'Space', 'spaceweather', false, false ]
								,'peter_zuege' => [ 'smarty_peter', 'get_peter_zuege', 'Peter', '{get_peter_zuege} Lädt ein Array das die Anzahl offener Peter Züge eines Users, und die game_id eines Peter Spiels mit offenem Zug, enthält ', false, false ]
								,'sql_errors' => [ 'smarty_sql_errors', 'sql_errors', 'System', 'sql errors', false, false ]
								,'assign_stocklist' => [ 'stockbroker_assign_stocklist', 'assign_stocklist', 'Stockbroker', 'Stockbroker', false, false ]
								,'stockbroker_assign_stock' => [ 'stockbroker_assign_stock', 'assign_stock', 'Stockbroker', 'Stockbroker', false, false ]
								,'assign_searchedstocks' => [ 'stockbroker_assign_searchedstocks', 'assign_searchedstocks', 'Stockbroker', 'Stockbroker', false, false ]
								,'stockbroker_update_kurs' => [ 'stockbroker_update_kurs', 'update_kurs', 'Stockbroker', 'Stockbroker', false, false ]
								,'getkursbought' => [ 'stockbroker_getkursbought', 'getkursbought', 'Stockbroker', 'Stockbroker', false, false ]
								,'getkurs' => [ 'stockbroker_getkurs', 'getkurs', 'Kategorie', 'Stockbroker', false, false ]
								,'num_new_tauschangebote' => [ 'smarty_num_new_tauschangebote', 'num_new_tauschangebote', 'Tauschbörse', 'Tauschbörse', false, false ]
								,'assign_artikel' => [ 'smarty_assign_artikel', 'assign_artikel', 'Tauschbörse', 'Tauschbörse', false, false ]
								,'url_params' => [ 'url_params', 'url_params', 'URL Handling', 'system', false, false ]
								,'sizeof' => [ 'smarty_sizeof', 'sizeof', 'Smarty Templates', 'system', false, false ]
								,'get_changed_url' => [ 'smarty_get_changed_url', 'get_changed_url', 'URL Handling', 'system', false, false ]
								,'htmlentities' => [ 'smarty_htmlentities', 'htmlentities', 'Variablen', 'Registriert für Smarty die Funktion htmlentities() aus PHP', false, false ]
								,'base64encode' => [ 'base64_encode', 'base64encode', 'Variablen', 'Registriert für Smarty die Funktion base64_encode() aus PHP', false, false ]
								,'onlineusers' => [ 'smarty_onlineusers', 'onlineusers', 'Usersystem', 'usersystem', false, false ]
								,'loginform' => [ 'loginform', 'loginform', 'Usersystem', 'usersystem', false, false ]
								,'formfielduserlist' => [ 'smarty_FormFieldUserlist', 'formfielduserlist', 'Usersystem', 'usersystem', false, false ]
								,'datename' => [ 'smarty_datename', 'datename', 'Datum und Zeit', 'stellt ein Datum leserlich dar', false, false ]
								,'rand' => [ 'smarty_rand', 'rand', 'Variablen', '{rand min=2 max=10 assign=var}', false, false ]
								,'assign_array' => [ 'smarty_function_assign_array', 'assign_array', 'Variablen', 'erlaubt es, mit Smarty Arrays zu erzeugen', false, false ]
								,'assign_event_hasjoined' => [ 'smarty_assign_event_hasjoined', 'assign_event_hasjoined', 'Events', 'events', true, false ]
								,'event_hasjoined' => [ 'smarty_event_hasjoined', 'event_hasjoined', 'Events', 'events', true, false ]
								,'assign_rezept_voted' => [ 'smarty_assign_rezept_voted', 'assign_rezept_voted', 'Rezepte', 'rezepte', true, false ]
								,'logerror' => [ 'smarty_logerror', 'logerror', 'PHP Error Log', 'errorhandling', false, false ]
								,'url_exists' => [ 'smarty_url_exists', 'url_exists', 'URL Handling', 'Utilities Funktion urlExists() um eine URL/Pfad zu validieren. Usage: {url_exists url="[url]" assign="[smarty-variable]"}', false, false ]
							);


	/**
	 * Function to register PHP Variables to Smarty
	 *
	 * Maps custom Zorg PHP Variables and Arrays to a Smarty Variable
	 * e.g. Array "$colors['background']" will be available in Smarty as {$colors.background}
	 * Usage: $smarty->assign([array], [value])
	 *
	 * @author IneX
	 * @version 1.0
	 * @since 1.0 function added
	 *
	 * @var object $smarty Smarty Class
	 * @global object $user Globales Class-Object mit den User-Methoden & Variablen
	 */
	function register_php_arrays($php_vars_array)
	{
		// Globals
		global $smarty, $user;
		$smarty_vars_documentation = array();

		foreach ($php_vars_array as $smarty_var_key => $smarty_var_data)
		{ // Format: 'color' => array($color, 'Layout', 'Array mit allen Standardfarben (wechselt zwischen Tag und Nacht)', false)
			if ($smarty_var_data[3] && $user != null) $smarty->assign($smarty_var_key, $smarty_var_data[0]);
			if (!$smarty_var_data[3]) $smarty->assign($smarty_var_key, $smarty_var_data[0]);
			$smarty_vars_documentation['{$'.$smarty_var_key.'}'] = array('category' => $smarty_var_data[1], 'description' => $smarty_var_data[2], 'members_only' => $smarty_var_data[3]);
		}

		//natcasesort($smarty_vars_documentation);
		$smarty->assign('smartyvars_doc', $smarty_vars_documentation); // {smartyvars_doc} Lists all available custom Smarty Vars
	}


	/**
	 * Function to register the PHP Functions as Modifiers for Smarty Functions
	 *
	 * Maps custom Zorg PHP Functions to an equal Smarty Template Function
	 * e.g. function "getLatestUpdates" will be available in Smarty as {latest_updates}
	 * Usage: $smarty->register_modifier([template modifier], [php function])
	 *
	 * @author IneX
	 * @version 1.0
	 * @since 1.0 function added
	 *
	 * @var object $smarty Smarty Class
	 * @global object $user Globales Class-Object mit den User-Methoden & Variablen
	 */
	function register_php_modifiers($php_modifiers_array)
	{
		// Globals
		global $smarty, $user;
		$documentation = array();
		//natcasesort($php_modifiers_array); // Sort the Array from A-Z

		foreach ($php_modifiers_array as $function_name => $data)
		{ // Format: 'datename' => array('datename', 'Datum und Zeit', '{$timestamp|datename} konviertiert einen timestamp in ein anständiges datum/zeit Format', false)
			$smarty_name = (!empty($data[0]) ? $data[0] : $function_name);
			if ($data[3] && $user != null) $smarty->register_modifier($smarty_name, $function_name);
			elseif (!$data[3]) $smarty->register_modifier($smarty_name, $function_name);
			$documentation['{$var|'.$smarty_name.'}'] = array('category' => $data[1], 'description' => $data[2], 'members_only' => $data[3]);
		}

		//natcasesort($smarty_modifiers_documentation); // Sort the Array from A-Z
		$smarty->assign('smartymodifiers_doc', $documentation); // {smartymodifiers} Lists all available Smarty Modifiers
	}


	/**
	 * Function to register PHP Function Outputs as HTML-Blocks for Smarty Templates
	 *
	 * Maps custom Zorg PHP Functions to an equal Smarty Template Function
	 * e.g. function "getLatestUpdates" will be available in Smarty as {latest_updates}
	 * Usage: $smarty->register_block([template block], [php function])
	 *
	 * @author IneX
	 * @version 1.0
	 * @since 1.0 function added
	 *
	 * @var object $smarty Smarty Class
	 * @global object $user Globales Class-Object mit den User-Methoden & Variablen
	 */
	function register_php_blocks($php_blocks_array)
	{
		// Globals
		global $smarty, $user;
		$documentation = array();

		foreach ($php_blocks_array as $function_name => $data)
		{ // Format: 'smarty_zorg' => array('zorg', 'Layout', '{zorg title="Titel"}...{/zorg}	displays the zorg layout (including header, menu and footer)', false)
			$smarty_name = (!empty($data[0]) ? $data[0] : $function_name);
			if ($data[3] && $user != null) $smarty->register_block($smarty_name, $function_name);
			elseif (!$data[3]) $smarty->register_block($smarty_name, $function_name);
			$documentation['{'.$smarty_name.'}'] = array('category' => $data[1], 'description' => $data[2], 'members_only' => $data[3]);
		}

		//natcasesort($documentation); // Sort the Array from A-Z
		$smarty->assign('smartyblocks_doc', $documentation); // {smartyblocks_doc} Lists all available Smarty HTML-Blocks
	}


	/**
	 * Function to register the PHP Functions to Smarty Functions
	 *
	 * Maps custom Zorg PHP Functions to an equal Smarty Template Function
	 * e.g. function "getLatestUpdates" will be available in Smarty as {latest_updates}
	 *   Usage 1: $smarty->register_function([template function], [php function])
	 *   Usage 2: $smarty->register_function([template function], array([php class], [php function]))
	 *   Usage 3: $smarty->register_compiler_function([template function], [php function], [cacheable true/false])
	 * @link https://www.smarty.net/docsv2/en/api.register.function.tpl
	 *
	 * @author IneX
	 * @version 2.0
	 * @since 1.0 function added
	 * @since 2.0 added support for registering array($class, $method)
	 *
	 * @var object $smarty Smarty Class
	 * @global object $user Globales Class-Object mit den User-Methoden & Variablen
	 */
	function register_php_functions($php_functions_array)
	{
		// Globals
		global $smarty, $user;
		$documentation = array();

		foreach ($php_functions_array as $array_name => $data)
		{ // Format: array_name => array( [0]{array([PHP-Klasse], }[PHP-Funktion]{)}, [1][Tpl-Funktion], [2][Kategorie], [3][Beschreibung], [4][Members only true/false], [5][Compiler Function true/false])
			$smarty_name = (!empty($data[1]) ? $data[1] : $array_name);
			if ($data[4] && $user != null && !$data[5]) $smarty->register_function($smarty_name, $data[0]); // Register "Members only"-Functions
			elseif (!$data[4] && !$data[5]) $smarty->register_function($smarty_name, $data[0]);  // Register Functions for all Users
			elseif ($data[5]) $smarty->register_compiler_function($smarty_name, $data[0], false); // Register Compiler Functions
			$documentation['{'.$smarty_name.'}'] = array('category' => $data[2], 'description' => $data[3], 'members_only' => $data[4], 'compiler_function' => $data[5]);
		}

		//natcasesort($documentation); // Sort the Array from A-Z
		$smarty->assign('smartyfunctions_doc', $documentation); // {smartyblocks_doc} Lists all available Smarty HTML-Blocks
	}


//} Closing Class "SmartyZorgFunctions"

//$ZorgSmarty = new SmartyZorgFunctions;
//$ZorgSmarty->register_php_arrays();
register_php_arrays($zorg_php_vars);
register_php_blocks($zorg_php_blocks);
register_php_modifiers($zorg_php_modifiers);
register_php_functions($zorg_php_functions);
