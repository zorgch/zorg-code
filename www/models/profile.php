<?php
/**
 * Userprofile MVC Model
 *
 * @author IneX
 * @package zorg\Usersystem
 */
namespace MVC;

/**
 * Class representing the MVC Model
 */
class Profile extends Model
{
	public function __construct()
	{
		/** Paget Title, zumindest als Prefix, ist für alle Seiten gleich */
		$this->page_title = 'Userlist';

		/** Paget URL Basis ist für alle Seiten gleich */
		$this->page_link = '/tpl/219';

		/** Meta description ist grundsätzlich für alle Seiten gleich */
		$this->meta_description = 'Profile der zorg User, Member und Mitglieder mit all ihren dunklen Geheimnissen';

		/** Menus sind für alle Seiten gleich */
		$this->menus = [ 'zorg', 'user' ];
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
	public function showProfileupdate(&$smarty)
	{
		$this->page_title = 'Mein zorg Profil aktualisieren';
		$this->page_link = '/profil.php?do=view';

		$this->assign_model_to_smarty($smarty);
	}

	/**
	 * @version 1.0
	 * @since 1.0 <inex> 29.08.2019 method added
	 *
	 * @param object $smarty Smarty Class-Object
	 */
	public function showOtherprofile(&$smarty, &$user, $user_id)
	{
		$this->page_title = 'zorg Profil Vorschau von '.$user->id2user($user_id, true);
		$this->page_link = '/profil.php?do=view&viewas='.$user_id;

		$this->assign_model_to_smarty($smarty);
	}

	/**
	 * @version 1.0
	 * @since 1.0 <inex> 29.08.2019 method added
	 *
	 * @param object $smarty Smarty Class-Object
	 */
	public function showUserprofile(&$smarty, &$user, $user_id)
	{
		$clantagUsername = $user->id2user($user_id, true);
		$this->page_title = $clantagUsername;
		$this->meta_description = 'zorg Userprofil von '.$clantagUsername;
		$this->page_link = '/user/'.rawurlencode($user->id2user($user_id));
		$this->page_image = SITE_URL.$user->userImage($user_id, 1);

		$this->assign_model_to_smarty($smarty);
	}

	/**
	 * @version 1.0
	 * @since 1.0 <inex> 29.08.2019 method added
	 *
	 * @param object $smarty Smarty Class-Object
	 */
	public function showUnknownuser(&$smarty, $user_id)
	{
		$this->page_title = (!empty($user_id) ? sprintf('User mit ID #%d existiert nicht', $user_id) : 'Unbekannte User ID');
		$this->meta_description = null;
		$this->page_link = null;

		$this->assign_model_to_smarty($smarty);
	}

	/**
	 * @version 1.0
	 * @since 1.0 <inex> 29.08.2019 method added
	 *
	 * @param object $smarty Smarty Class-Object
	 */
	public function showLogin(&$smarty)
	{
		$this->page_title = 'Neuen zorg Useraccount registrieren';
		$this->meta_description = 'Erstelle dir jetzt einen Account um auf alle Funktionen von zorg zugreifen und den Austausch mit anderen Usern starten zu können!';
		$this->page_link = '/profil.php?do=anmeldung';

		$this->assign_model_to_smarty($smarty);
	}

	/**
	 * @version 1.0
	 * @since 1.0 <inex> 29.08.2019 method added
	 *
	 * @param object $smarty Smarty Class-Object
	 */
	public function showActivation(&$smarty, $message = null)
	{
		$this->page_title = (empty($message) ? 'Account bestätigen' : $message);
		$this->page_link = '/profil.php?do=anmeldung';

		$this->assign_model_to_smarty($smarty);
	}
}
