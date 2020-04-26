<?php
/**
 * Peter Game MVC Model
 *
 * @author IneX
 * @package zorg\Games\Peter
 */
namespace MVC;

/**
 * Class representing the MVC Model
 */
class Peter extends Model
{
	public function __construct()
	{
		/** Paget Title, zumindest als Prefix, ist für alle Seiten gleich */
		$this->page_title = 'Peter Kartenspiel';

		/** Paget URL Basis ist für alle Seiten gleich */
		$this->page_link = '/peter.php';

		/** Meta description ist grundsätzlich für alle Seiten gleich */
		$this->meta_description = 'Das Ziel im von zorg erfundenem «Peter» Kartenspiel ist es die Jasskarten vor allen anderen Mitspielern wegzubringen. Dies erreicht man, indem man jeweils mit einer höheren Karte diejenige die als letztes gelegt wurde sticht.';

		/** Image teaser ist für alle Seiten gleich */
		$this->page_image = SITE_URL.IMAGES_DIR.'peter/99.gif';

		/** Menus sind für alle Seiten gleich */
		$this->menus = [ 'zorg', 'games', 'peter' ];
	}

	/**
	 * @version 1.0
	 * @since 1.0 `29.08.2019` `IneX` method added
	 *
	 * @param object $peter Ein Peter Game Class-Objekt
	 * @return void
	 */
	public function showKartenberg($peter)
	{
		header('Content-Type: Image/PNG');
		header('Cache-Control: no-cache, no-store, must-revalidate');
		header('Pragma: no-cache');
		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
		header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
	}

	/**
	 * @version 1.0
	 * @since 1.0 `29.08.2019` `IneX` method added
	 *
	 * @param integer $game_id Eine gültige Peter Game-ID
	 * @return resource
	 */
	public function getGamedata($game_id)
	{
		global $db;

		$sql = 'SELECT 
					*
				FROM peter_games pg
				LEFT JOIN user u
				ON pg.next_player = u.id
				WHERE pg.game_id = '.$game_id;
		$result = $db->query($sql, __FILE__, __LINE__, __FUNCTION__);
		return $db->fetch($result);
	}

	/**
	 * @version 1.0
	 * @since 1.0 `29.08.2019` `IneX` method added
	 *
	 * @param object $smarty Smarty Class-Object
	 */
	public function showOverview(&$smarty)
	{
		$this->assign_model_to_smarty($smarty);
	}

	/**
	 * @version 1.0
	 * @since 1.0 `29.08.2019` `IneX` method added
	 *
	 * @param object $smarty Smarty Class-Object
	 */
	public function showGame(&$smarty, $game_id)
	{
		$this->page_title = 'Peter Game #'.$game_id;
		$this->page_link = $this->page_link . '?game_id='.$game_id;

		$this->assign_model_to_smarty($smarty);
	}

	/**
	 * @version 1.0
	 * @since 1.0 `29.08.2019` `IneX` method added
	 *
	 * @param object $smarty Smarty Class-Object
	 */
	public function showHighscores(&$smarty)
	{
		$this->page_title = $this->page_title . ' - Highscores';

		$this->assign_model_to_smarty($smarty);
	}
}
