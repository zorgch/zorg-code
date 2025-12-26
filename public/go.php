<?php
/**
 * GO (Game)
 *
 * Hier kommt die Beschreibung zu dieser
 * Datei hinein. Auch über mehrere Dateien
 * wenn man will.
 * GO benutzt folgende Tabellen in der DB:
 *		xy, xy_dwz, ...
 *
 * @author [z]bert
 * @author [z]domi
 * @package zorg\Games\Go
 */

/**
 * File includes
 * @include main.inc.php
 * @include core.model.php
 */
include_once dirname(__FILE__).'/includes/main.inc.php';
require_once MODELS_DIR.'core.model.php';

/**
 * Initialise MVC Model
 */
$model = new MVC\Go();

/**
 * GO Klasse
 *
 * Dies ist die Klasse zum GO Spiel.
 *
 * @author [z]bert
 * @author [z]domi
 * @date nn.nn.nnnn
 * @version 1.0
 * @package zorg
 * @subpackage GO
 */
class Go
{
	/**
	 * Feldgrösse Standartwert
	 * @var integer Variable für die Feldgrösse, Default = 40
	 */
	var $feld_groesse = 40;

	/**
	 * GO Spielfeld (Goban?) der Grösse \$size anzeigen
	 *
	 * @author [z]bert
	 * @author [z]domi
	 * @date nn.nn.nnnn
	 * @version 1.0
	 * @since 1.0
	 *
	 * @param integer $size Grösse des GO-Spielfeldes
	 */
	function __construct($size)
	{
		$this->size = $size;
		$this->img_size = $this->feld_groesse * $this->size;
		$this->img = imagecreatetruecolor($this->img_size,$this->img_size);
		$this->bg = imagecolorallocate($this->img,255,255,255);
		imagefill($this->img,0,0,$this->bg);
		$this->line = imagecolorallocate($this->img,0,0,0);
		for($i = 0;$i<=$this->size;$i++) {
			imageline($this->img,0,($i*$this->feld_groesse),$this->img_size,($i*$this->feld_groesse),$this->line);
			imageline($this->img,($i*$this->feld_groesse),0,($i*$this->feld_groesse),$this->img_size,$this->line);
		}
		imagerectangle($this->img,0,0,$this->img_size-1,$this->img_size-1,$this->line);
		$this->partei[0] = imagecolorallocate($this->img,23,23,23);
		$this->partei[1] = imagecolorallocate($this->img,200,200,200);
	}	

	/**
	 * ...
	 *
	 * @author [z]bert
	 * @author [z]domi
	 * @date nn.nn.nnnn
	 * @version 1.0
	 * @since 1.0
	 *
	 * @param integer $x ...
	 * @param integer $y ...
	 * @param integer $partei ...
	 */
	function stone($x,$y,$partei)
	{
		imagefilledellipse($this->img,$x,$y,23,23,$this->partei[$partei]);
	}

	/**
	 * ...
	 *
	 * @author [z]bert
	 * @author [z]domi
	 * @date nn.nn.nnnn
	 * @version 1.0
	 * @since 1.0
	 */
	function display()
	{
		imagepng($this->img);	
	}
}

header('Content-Type: Image/PNG');

$go = new Go(13);
$go->stone(40,40,0);
$go->stone(40,120,1);
$go->display();
