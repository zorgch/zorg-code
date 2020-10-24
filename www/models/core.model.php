<?php
/**
 * MVC core model
 *
 * @author IneX
 * @package zorg\MVC\Model
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
	 *
	 * @FIXME didn't get this working... (IneX)
	 */
	/*public function load()
	{
		require_once dirname(__FILE__).'/../includes/util.inc.php';
		$modelFile = MODELS_DIR. '/' . strtolower($this->model) . '.php';
		if (DEVELOPMENT === true) error_log(sprintf('[DEBUG] <%s:%d> $modelFile for $model "%s": %s', __METHOD__, __LINE__, $this->model, $modelFile));

		if (fileExists($modelFile))
		{
			if (DEVELOPMENT === true) error_log(sprintf('[DEBUG] <%s:%d> require_once: %s', __METHOD__, __LINE__, $modelFile));
			require_once $modelFile;
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
	 * @since 1.0 `29.08.2019` `IneX` method added
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
 * @include sitemap.model.php Required
 * @include spaceweather.php Required
 * @include stl.php Required
 * @include verein_mailer.php Required
 * @include wetten.php Required
 */
require_once dirname(__FILE__).'/addle.php';
require_once dirname(__FILE__).'/anficker.php';
require_once dirname(__FILE__).'/books.php';
require_once dirname(__FILE__).'/bugtracker.php';
require_once dirname(__FILE__).'/dreamjournal.php';
require_once dirname(__FILE__).'/forum.php';
require_once dirname(__FILE__).'/gallery.php';
require_once dirname(__FILE__).'/go.php';
require_once dirname(__FILE__).'/fretsonzorg.php';
require_once dirname(__FILE__).'/join.php';
require_once dirname(__FILE__).'/messagesystem.php';
require_once dirname(__FILE__).'/peter.php';
require_once dirname(__FILE__).'/pimp.php';
require_once dirname(__FILE__).'/profile.php';
require_once dirname(__FILE__).'/quotes.php';
require_once dirname(__FILE__).'/seti.php';
require_once dirname(__FILE__).'/sitemap.model.php';
require_once dirname(__FILE__).'/spaceweather.php';
require_once dirname(__FILE__).'/stl.php';
//require_once dirname(__FILE__).'/verein_mailer.php';
require_once dirname(__FILE__).'/wetten.php';
