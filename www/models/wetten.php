<?php
/**
 * Wettbüro MVC Model
 *
 * @author IneX
 * @package zorg\Wetten
 */
namespace MVC;

/**
 * Class representing the MVC Model
 */
class Wetten extends Model
{
	public function __construct()
	{
		global $user;

		/** Paget Title, zumindest als Prefix, ist für alle Seiten gleich */
		$this->page_title = 'zorg Wettbüro';

		/** Paget URL Basis ist für alle Seiten gleich */
		$this->page_link = '/wetten.php';

		/** Meta description ist grundsätzlich für alle Seiten gleich */
		$this->meta_description = 'Keine Wette, Wetteinsätze und Versprechen mehr vergessen: dank der Transparenz und Fairness im zorg Wettbüro.';

		/** Menus sind für alle Seiten gleich */
		$this->menus = [ 'zorg' ];
		if ($user->is_loggedin()) array_push($this->menus, 'eingeloggte_user');
		array_push($this->menus, 'user');
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
	public function showWette(&$smarty, &$user, $wette_id, $wettstarter_userid=null, $wette_titel=null, $wette_text=null)
	{
		$this->page_title = (!empty($wette_titel) ? sprintf('Wette «%s» von %s', $wette_titel, $user->id2user($wettstarter_userid)) : sprintf('Wette #%d von %s', $wette_id, $user->id2user($wettstarter_userid)));
		$this->page_link = $this->page_link . '?id='.$wette_id;
		if (!empty($wette_text)) $this->meta_description = text_width(remove_html('Wir wetten… '.$wette_text), 155, '', true, true);

		$this->assign_model_to_smarty($smarty);
	}
}
