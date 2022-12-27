<?php
/**
 * Gallery MVC Model
 *
 * @author IneX
 * @package zorg\Gallery
 */
namespace MVC;

/**
 * Class representing the MVC Model
 */
class Gallery extends Model
{
	public function __construct()
	{
		/** Paget Title, zumindest als Prefix, ist für alle Seiten gleich */
		$this->page_title = 'zorg Galleries and Pics';

		/** Paget URL Basis ist für alle Seiten gleich */
		$this->page_link = '/gallery.php';

		/** Meta description ist grundsätzlich für alle Seiten gleich */
		$this->meta_description = 'zorg Galleries mit Bilder von Events und anderen Aktivitäten';

		/** Menus sind für alle Seiten gleich */
		$this->menus = [ 'zorg', 'gallery' ];
	}

	/**
	 * @version 1.0
	 * @since 1.0 `29.08.2019` `IneX` method added
	 *
	 * @param integer $album_id Eine gültige Album-ID
	 * @param integer $pic_id Einge gültige Pic-ID
	 */
	public function setAlbumId($album_id=null, $pic_id=null)
	{
		if(!empty($album_id)) $album_id = $album_id;
		if((!empty($pic_id) && $pic_id > 0) && empty($album_id)) $album_id = pic2album($pic_id);
		return $album_id;
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
	public function showAlbum(&$smarty, $album_id)
	{
		$this->page_title = 'Gallery Album #'.$album_id;
		$this->page_link = $this->page_link . '?show=albumThumbs&albID='.$album_id;

		$this->assign_model_to_smarty($smarty);
	}

	/**
	 * @version 1.0
	 * @since 1.0 `29.08.2019` `IneX` method added
	 *
	 * @param object $smarty Smarty Class-Object
	 */
	public function showPic(&$smarty, &$user, $pic_id, $album_id=null)
	{
		$picTitle = picHasTitle($pic_id);
		if (!empty($picTitle))
		{
			$this->page_title = sprintf('Gallery Album #%d: Pic «%s»', $album_id, text_width(remove_html($picTitle), 50, '…', true));
			$this->meta_description = text_width(remove_html($picTitle), 150, '…', true, true);
		} else {
			$this->page_title = 'Gallery Album #'.$album_id.': Pic #'.$pic_id;
		}
		$this->page_link = '?show=pic&picID='.$pic_id;
		if ($user->is_loggedin()) $this->page_image = SITE_URL.imgsrcPic($pic_id);
		if ($user->from_mobile != false) $this->scripts[] = JS_DIR.'hammer.min.js';

		$this->assign_model_to_smarty($smarty);
	}

	/**
	 * @version 1.0
	 * @since 1.0 `29.08.2019` `IneX` method added
	 *
	 * @param object $smarty Smarty Class-Object
	 */
	public function showAlbumedit(&$smarty, $album_id)
	{
		$this->page_title = 'Gallery-Album #'.$album_id.' bearbeiten';
		$this->page_link = $this->page_link . '?show=editAlbum&albID=' . $album_id;

		$this->assign_model_to_smarty($smarty);
	}

	/**
	 * @version 1.0
	 * @since 1.0 `15.12.2019` `IneX` method added
	 *
	 * @var object $smarty Smarty Class-Object
	 */
	public function showFacetagging($page_index=null)
	{
		global $smarty;

		$this->page_title = 'Fresse Tagging'.($page_index > 0 ? ' - Page #'.$page_index : '');
		$this->meta_description = 'Game zum Frässene vo Gallery Pics zu Users tagge';
		$this->page_link = '/facetag.php';

		$this->assign_model_to_smarty($smarty);
	}
}
