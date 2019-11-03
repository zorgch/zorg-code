<?php
/**
 * MVC core model
 *
 * @author IneX
 * @package zorg
 */
namespace MVC;

/**
 * Class representing the MVC Model
 */
class Model
{
	/**
	 * @var string $page_title
	 * @var string $page_link
	 * @var string $meta_description
	 * @var string $page_image
	 * @var array $menus
	 * @var integer $sidebar
	 */
	//public $model;
	public $page_title;
	public $page_link;
	public $meta_description;
	public $page_image;
	public $menus;
	public $sidebar;

	public function __construct($smarty)
	{
		$this->page_title = null;
		$this->page_link = null;
		$this->meta_description = null;
		$this->page_image = null;
		$this->menus = null;
		$this->sidebar = null;
		//$this->load();
	}

	/**
	 * Dynamic loader to get the right .model.php
	 * @FIXME <inex> didn't get this working...
	 */
	/*public function load()
	{
		require_once( __DIR__ .'/../includes/util.inc.php');
		$modelFile = __DIR__ . '/' . strtolower($this->model) . '.php';
		if (DEVELOPMENT === true) error_log(sprintf('[DEBUG] <%s:%d> $modelFile for $model "%s": %s', __METHOD__, __LINE__, $this->model, $modelFile));

		if (fileExists($modelFile))
		{
			if (DEVELOPMENT === true) error_log(sprintf('[DEBUG] <%s:%d> require_once: %s', __METHOD__, __LINE__, $modelFile));
			require_once($modelFile);
			//$modelClass = 'MVC\\'.$model;
			//if (DEVELOPMENT === true) error_log(sprintf('[DEBUG] <%s:%d> Instantiating $modelClass: %s', __METHOD__, __LINE__, $modelClass));
			//${$model} = new $modelClass;
			//return new self($model, get_called_class());
		}
		else {
			error_log(sprintf('[WARN] <%s:%d> Model "%s" not found.', __METHOD__, __LINE__, $modelFile));
		}
	}*/

	/**
	 * Assign Model to $smarty Object
	 *
	 * @version 1.0
	 * @since 1.0 <inex> 29.08.2019 method added
	 *
	 * @param object $smarty Smarty Class-Object
	 */
	public function assign_model_to_smarty(&$smarty)
	{
		/** SEO friendly content */
		$seo_allowed_chars_pattern = '([^\w\s\p{L}.,;:!?\-\/\(\)\[\]…«»#@])';
		$this->page_title = mb_ereg_replace($seo_allowed_chars_pattern, '', $this->page_title);
		$this->meta_description = mb_ereg_replace($seo_allowed_chars_pattern, '', $this->meta_description);

		/** Assign Model to Smarty variables */
		$smarty->assign('tplroot', [
									 'page_title' => $this->page_title
									,'page_link' => $this->page_link
									,'page_image' => $this->page_image
									,'meta_description' => $this->meta_description
									,'menus' => $this->menus
									,'sidebar' => $this->sidebar
								 ]);
	}
}

/**
 * Include Model Files
 * @include addle.php Required
 * @include anficker.php Required
 * @include books.php Required
 * @include bugtracker.php Required
 * @include dreamjournal.php Required
 * @include forum.php Required
 * @include gallery.php Required
 * @include go.php Required
 * @include fretsonzorg.php Required
 * @include join.php Required
 * @include messagesystem.php Required
 * @include peter.php Required
 * @include pimp.php Required
 * @include profile.php Required
 * @include quotes.php Required
 * @include seti.php Required
 * @include spaceweather.php Required
 * @include stl.php Required
 * @include verein_mailer.php Required
 * @include wetten.php Required
 */
require_once( __DIR__ . '/addle.php');
require_once( __DIR__ . '/anficker.php');
require_once( __DIR__ . '/books.php');
require_once( __DIR__ . '/bugtracker.php');
require_once( __DIR__ . '/dreamjournal.php');
require_once( __DIR__ . '/forum.php');
require_once( __DIR__ . '/gallery.php');
require_once( __DIR__ . '/go.php');
require_once( __DIR__ . '/fretsonzorg.php');
require_once( __DIR__ . '/join.php');
require_once( __DIR__ . '/messagesystem.php');
require_once( __DIR__ . '/peter.php');
require_once( __DIR__ . '/pimp.php');
require_once( __DIR__ . '/profile.php');
require_once( __DIR__ . '/quotes.php');
require_once( __DIR__ . '/seti.php');
require_once( __DIR__ . '/spaceweather.php');
require_once( __DIR__ . '/stl.php');
//require_once( __DIR__ . '/verein_mailer.php');
require_once( __DIR__ . '/wetten.php');
