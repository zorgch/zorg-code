<?php
/**
 * zorg Verein Mailer MVC Model
 *
 * @author IneX
 * @package zorg\Verein\Mailer
 */
namespace MVC;

/**
 * Class representing the MVC Model
 */
class VereinMailer extends Model
{
	public function __construct()
	{
		/** Page Title, zumindest als Prefix, ist für alle Seiten gleich */
		$this->page_title = 'zorg Verein - Mailer';

		/** URL Basis ist für alle Seiten gleich */
		$this->page_link = '/verein_mailer.php';

		/** Meta description ist grundsätzlich für alle Seiten gleich */
		$this->meta_description = 'Page um schöne Verein E-Mails an die Mitglieder zu verschicken';

		/** Image teaser ist für alle Seiten gleich */
		$this->page_image = SITE_URL.'/files/451/Zooomclan_GV_modern.JPG';

		/** Menus sind für alle Seiten gleich */
		$this->menus = [ 'zorg', 'verein' ];
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
}
