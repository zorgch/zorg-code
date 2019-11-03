<?php
/**
 * SETI@Home MVC Model
 *
 * @author IneX
 * @package zorg\SETI
 */
namespace MVC;

/**
 * Class representing the MVC Model
 */
class Seti extends Model
{
	public function __construct()
	{
		/** Paget Title, zumindest als Prefix, ist für alle Seiten gleich */
		$this->page_title = 'SETI@Home zooomclan Group Stats';

		/** Paget URL Basis ist für alle Seiten gleich */
		$this->page_link = '/seti.php';

		/** Meta description ist grundsätzlich für alle Seiten gleich */
		$this->meta_description = 'SETI@Home detailed stats for zooomclan.org group';

		/** Page image ist grundsätzlich für alle Seiten gleich */
		$this->page_image = SITE_URL.'/img/logos/korrekt.png';

		/** Menus sind für alle Seiten gleich */
		$this->menus = [ 'zorg', 'mischt', 'seti' ];
	}

	/**
	 * @version 1.0
	 * @since 1.0 <inex> 29.08.2019 method added
	 *
	 * @param object $smarty Smarty Class-Object
	 */
	public function showOverview(&$smarty)
	{
		$this->assign_model_to_smarty($smarty);
	}

	/**
	 * @version 1.0
	 * @since 1.0 <inex> 29.08.2019 method added
	 *
	 * @param object $smarty Smarty Class-Object
	 */
	public function showAdminpage(&$smarty)
	{
		$this->page_title = 'zorg SETI@Home Admin';
		$this->page_link = '/seti_xml.php';

		$this->assign_model_to_smarty($smarty);
	}
}
