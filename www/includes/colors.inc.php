<?php
/**
 * The Colors of zorg
 * @package zorg\Layout
 */
/**
 * File Includes
 * @include	Sunrise Class
 */
//require_once dirname(__FILE__).'/sunrise.inc.php'; // DEACTIVATED BECAUSE NO LONGER NEEDED INCLUSION

/**
 * Define colors for day layout
 */
if($zorgLayout->layouttype == 'day'){
	/** Background colors */
	define('BACKGROUNDCOLOR', '#F2F2F2'); /* EEEEEE */
	define('TABLEBACKGROUNDCOLOR', '#DDDDDD');
	define('BORDERCOLOR', '#CCCCCC');
	define('HEADERBACKGROUNDCOLOR', '#FFFFFF');

	/** Forum */
	define('NEWCOMMENTCOLOR', '#8D9FE5');
	define('OWNCOMMENTCOLOR', '#9FE58D');
	define('FAVCOMMENTCOLOR', '#E58D9F');
	define('IGNORECOMMENTCOLOR', '#E55842');

	/** Text colors */
	define('FONTCOLOR', '#000000');
	define('LINKCOLOR', '#344586');

	/** Menu Colors */
	define('MENUCOLOR1', '#BDCFF5');
	define('MENUCOLOR2', '#9DAFD5');

	/** Form Inputs */
	define('IFC','#000000');
	define('IBG','#FFFFFF');

	/** hunting z  */
	define('HZ_BG_COLOR', '#C8C8C8');

	/** Table Colors */
	define('TABLEBGCOLOR', '#E5E5E5');

} else {
/**
 * Define colors for night layout
 */
	/** Background colors */
	define('BACKGROUNDCOLOR', '#141414');
	define('TABLEBACKGROUNDCOLOR', '#242424');
	define('BORDERCOLOR', '#CBBA79');
	define('HEADERBACKGROUNDCOLOR','#000000');

	/** Forum */
	define('NEWCOMMENTCOLOR', '#72601A');
	define('OWNCOMMENTCOLOR', '#601A72');
	define('FAVCOMMENTCOLOR', '#A2601A');
	define('IGNORECOMMENTCOLOR', '#C91B12');

	/** Text colors */
	define('FONTCOLOR', '#FFFFFF');
	define('LINKCOLOR', '#CBBA79');

	/** Menu Colors */
	define('MENUCOLOR1', '#42300A');
	define('MENUCOLOR2', '#62502A');

	/** Form Inputs */
	define('IFC','#FFFFFF');
	define('IBG','#000000');

	/** hunting z  */
	define('HZ_BG_COLOR', '#C8C8C8');

	/** Table Colors */
	define('TABLEBGCOLOR', '#141414');
}

/**
* Define generic colors & sizes (for day & night layout)
* @const BODYBACKGROUNDCOLOR HTML <body>-Background Color
* @const HIGHLITECOLOR Color for Texxt Highlighting
* @const TABLEBORDERC Table Border Color
* @const FORUMWIDTH Generic size width for tables
*/
define('BODYBACKGROUNDCOLOR', '#000000');
define('HIGHLITECOLOR', '#FF0000');
define('TABLEBORDERC', BORDERCOLOR);
define('FORUMWIDTH', '100%');
