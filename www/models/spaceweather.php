<?php
/**
 * Spaceweather MVC Model
 *
 * @author IneX
 * @package zorg\Spaceweather
 */
namespace MVC;

/**
 * Class representing the MVC Model
 */
class Spaceweather extends Model
{
	public function __construct()
	{
		/** Paget Title, zumindest als Prefix, ist für alle Seiten gleich */
		$this->page_title = 'Spacewetter';

		/** Paget URL Basis ist für alle Seiten gleich */
		$this->page_link = '/spaceweather.php';

		/** Meta description ist grundsätzlich für alle Seiten gleich */
		$this->meta_description = 'Asteroiden, Anzahl Sonnenflecken, Solarwind Dichte, Magnetfeldrichtungsstärke und viele weitere Stats zum aktuellen Spaceweather';

		/** Menus sind für alle Seiten gleich */
		$this->menus = [ 'zorg', 'mischt' ];
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
}
