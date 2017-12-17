<?PHP
include_once($_SERVER['DOCUMENT_ROOT'].'/includes/colors.inc.php');
function write_css() {	
	global $sun;	
	$css = 
		// Main -------------------------------------------------------------------
		'
		html, body { height: 100%; }
		 
		body {
		  background-color: '.BODYBACKGROUNDCOLOR.';
		  color: '.FONTCOLOR.';
			font-family: Verdana, Sans-Serif;
			font-size: 13px;

			margin: 2px;
		}

		table {
			background-color: '.TABLEBACKGROUNDCOLOR.'
			font-family: Verdana, Sans-Serif;
			font-size: 12px;
		}
		table.header {
			background-color: '.HEADERBACKGROUNDCOLOR.';
			margin:0px;
		}
	
		table.shadedcells td {
			background-color: '.TABLEBACKGROUNDCOLOR.';
		}
		
		a {
		  color: '.LINKCOLOR.';
		  text-decoration: none;
		}

		a:hover {
		  text-decoration: underline;
		}
		
		.border {
	    border-color: '.BORDERCOLOR.';
			border-style: solid;
			border-width: 1px;
		}

		.bottom_border {
			border-bottom-style: solid;
			border-bottom-width: 1px;
			border-bottom-color: '.BORDERCOLOR.';
		}
	
		.title {	
			height: 20px;
			font-weight: bold;
		}
		
		blockquote {
		  font: 13px/22px normal;
		  margin-top: 10px;
		  margin-bottom: 10px;
		  margin-left: 50px;
		  padding-left: 15px;
		  border-left: 3px solid #CBBA79;
		}
		
		code { word-wrap: break-word; }
		
		pre {
		  background-color: #23241f;
		  color: #f8f8f2;
		  overflow: visible;
		  white-space: pre-wrap;
		  margin-bottom: 5px;
		  margin-top: 5px;
		  padding: 5px 10px;
		  border-radius: 3px;
		}
		'

		// Menus -------------------------------------------------------------------------
		
		// New menu
		.'
		div.menu {
			background-color: '.MENUCOLOR1.'; /* EDF2F2 */
			
			border-bottom-style: solid;
			border-bottom-color: '.BORDERCOLOR.'; /* ddd */
			border-bottom-width: 1px;	
			
			border-top-style: solid;
			border-top-color: #FFF;
			border-top-width: 1px;
		
			letter-spacing: 1px;
			
			/* Innenabstand */ 
			padding-bottom: 1px;
			padding-top: 1px;
		}
		
		table.dreid {
			background-color: '.TABLEBGCOLOR.'; /* EDF2F2 */
			
			border-bottom-style: solid;
			border-bottom-color: '.BORDERCOLOR.'; /* ddd */
			border-bottom-width: 1px;	
		
			border-left-style: solid;
			border-left-color: #FFF;
			border-left-width: 1px;
		
			border-right-style: solid;
			border-right-color: '.BORDERCOLOR.'; /* ddd */
			border-right-width: 1px;	
			
			border-top-style: solid;
			border-top-color: #FFF;
			border-top-width: 1px;
		}
		
		div.menu a {	
			background-color: '.MENUCOLOR1.'; /* EDF2F2 */
		
			border-bottom:	1px solid '.BORDERCOLOR.'; /* DDD */
			border-left:		1px solid #FFF;
			border-right:		1px solid '.BORDERCOLOR.'; /* DDD */
		
			color: '.LINKCOLOR.';		
		
			padding-bottom: 1px;
			padding-left: 15px;
			padding-right: 15px;
			padding-top: 1px;
			
			text-decoration: none;
		}
		
		div.menu a:hover {
			background: '.MENUCOLOR2.';
			text-decoration: none;
		}
		
		div.menu a.left {
			background-color: '.MENUCOLOR1.'; /* EDF2F2 */
			border-left-style: none;
			padding-left: 0px;
			padding-right: 1px;
		}
		
		div.menu a.left:hover {
		}
		
		div.menu a.right {
			background-color: '.MENUCOLOR1.'; /* EDF2F2 */
			border-right-style: none;
			padding-left: 1px;
			padding-right: 0px;
		}
		
		div.menu a.right:hover {
		}		
		'		

		// Forum ------------------------------------------------------------------
		.'
		table.forum {
			border-collapse:collapse;
			/* table-layout:fixed; */
			width:100%;
		}
		
		td.forum {
			margin: 0px;
			padding: 0px;
		}
		
		img.forum {
			heigth: 16px;
			width: 16px;
		}
		
		td.end {
			background-image: url("/images/forum/end.gif");
			background-repeat: no-repeat;
		  height: 16px;
			margin: 0px;
			padding: 0px;
		  width: 16px;
		  vertical-align: top;
		}
		
		td.space {
		  background-image: url("/images/forum/space.gif");
		  height: 16px;
			margin: 0px;
			padding: 0px;
		  width: 16px;
		  vertical-align: top;
		}
		
		td.vertline {
		  background-image: url("/images/forum/vertline.gif");
		  height: 16px;
			margin: 0px;
			padding: 0px;
		  width: 16px;
		  vertical-align: top;
		}
		'

		
		// Input ------------------------------------------------------------------
		.'		
		input:focus {
			border-style: inset;
			border-color: #'.BACKGROUNDCOLOR.';
		}
		
		select { 
			border-style: groove;
			border-width: 2px;
			border-color: '.BORDERCOLOR.';		
			font-family: Verdana;
			color: '.FONTCOLOR.';
			background-color: #'.BACKGROUNDCOLOR.';
			font-size: 10px;
		}
		
		option {
			font-family: verdana;
			font-size: 10px;
			color: '.FONTCOLOR.';
			background-color: #'.BACKGROUNDCOLOR.';
		
		}

		.text, textarea {
			border-style: solid;
			border-width: 1px;
			border-color: '.BORDERCOLOR.';
			font-size: 10px;
			font-family: verdana;
			color: '.FONTCOLOR.';
			background-color: '.IBG.';
		}

		.text:focus, textarea:focus {
			border-style: inset;
			border-color: #'.BACKGROUNDCOLOR.';
		}
		
		input[type=checkbox], input[type=radio] {
			border-style: inset;
			border-width: 2px;
			border-color: #'.BACKGROUNDCOLOR.';
			margin: 2px;
			background-color: '.MENUCOLOR1.';
		}
		
		.button {
			border-style: outset;
			border-width: 1px;
			border-color: '.BORDERCOLOR.';
			font-size: 11px;
			font-family: verdana;
			font-weight: bold;
			color: '.FONTCOLOR.';
			background-color: '.HEADERBACKGROUNDCOLOR.';
		}

		.button:hover{
			border-style: inset;
		}

		.small{
			font-size: 9px;
		}

		.titlebar{
			font-size: 20px;
			font-stretch: expanded;
			letter-spacing: 5px;
		}'
  
  
    // Addle ------------------------------------------------------------------
    .'
    td.addletd {
       border-style:solid;
       border-color: '. TABLEBORDERC .';
       border-width:1px;
       font-size: 22px;
		   text-align: center;
    }'
	;
	$style_name = array("up" => "day.css", "down" => "night.css");
	$fp = fopen($_SERVER['DOCUMENT_ROOT']."/includes/".$style_name[$sun],'w');
	fwrite($fp,$css);	
	fclose($fp);
}

if(
	(
		// Falls man was an den Farben gemacht hat
		@filemtime($_SERVER['DOCUMENT_ROOT'].'/includes/colors.inc.php') >
		@filemtime($_SERVER['DOCUMENT_ROOT'].'/includes/day.css')
	)
	||
	(
		// oder an diesem File, .css Datei neu machen.
		@filemtime($_SERVER['DOCUMENT_ROOT'].'/includes/css.inc.php') >
		@filemtime($_SERVER['DOCUMENT_ROOT'].'/includes/day.css')
	)
	||
	(
		// Falls man was an den Farben gemacht hat
		@filemtime($_SERVER['DOCUMENT_ROOT'].'/includes/colors.inc.php') >
		@filemtime($_SERVER['DOCUMENT_ROOT'].'/includes/night.css')
	)
	||
	(
		// oder an diesem File, .css Datei neu machen.
		@filemtime($_SERVER['DOCUMENT_ROOT'].'/includes/css.inc.php') >
		@filemtime($_SERVER['DOCUMENT_ROOT'].'/includes/night.css')
	)
	)
	{

	write_css();

}

?>
