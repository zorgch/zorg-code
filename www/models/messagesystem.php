<?php
/**
 * Messagesystem MVC Model
 *
 * @author IneX
 * @package zorg\Messagesystem
 */
namespace MVC;

/**
 * Class representing the MVC Model
 */
class Messagesystem extends Model
{
	public function __construct()
	{
		/** Paget Title, zumindest als Prefix, ist für alle Seiten gleich */
		$this->page_title = 'zorg Messages';

		/** Paget URL Basis ist für alle Seiten gleich */
		$this->page_link = '/messagesystem.php';

		/** Meta description ist grundsätzlich für alle Seiten gleich */
		$this->meta_description = 'zorg Messages für den Austausch zwischen einzelnen oder mehreren Usern';

		/** Menus sind für alle Seiten gleich */
		$this->menus = [ 'zorg', 'gallery' ];
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
	public function showMessage(&$smarty, &$user, $message_id, $sender_userid, $message_subject)
	{
		$subject_text = text_width(remove_html($message_subject), 60, '…', true, true);
		if ($sender_userid != $user->id) $this->page_title = sprintf('«%s» von %s', $subject_text, $user->id2user($sender_userid));
		else $this->page_title = sprintf('Deine zorg Message #%d «%s»', $message_id, $subject_text);

		$this->page_link = $this->page_link . '?message_id='.$message_id;

		$this->assign_model_to_smarty($smarty);
	}

	/**
	 * @version 1.0
	 * @since 1.0 `29.08.2019` `IneX` method added
	 *
	 * @param object $smarty Smarty Class-Object
	 */
	public function showInvalidmessage(&$smarty, $message_id)
	{
		$this->page_title = 'Ungültige zorg Message';
		$this->page_link = null;
		$this->meta_description = null;

		$this->assign_model_to_smarty($smarty);
	}
}
