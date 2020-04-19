<?php
/**
 * MVC core controller
 *
 * @author IneX
 * @package zorg\MVC
 */
namespace MVC\Controller;

/**
 * Class representing the MVC Controller
 */
class Controller
{
	public function __construct(&$smarty) { }

	/**
	 * Dynamic loader to get the right .controller.php
	 * @FIXME <inex> didn't get this working...
	 */
	/*public function load()
	{
		require_once dirname(__FILE__) .'/../includes/util.inc.php';
		$controllerFile = CONTROLLERS_DIR . '/' . strtolower($this->controller) . '.controller.php';
		if (DEVELOPMENT === true) error_log(sprintf('[DEBUG] <%s:%d> $controllerFile for $controller "%s": %s', __METHOD__, __LINE__, $this->controller, $controllerFile));

		if (fileExists($controllerFile))
		{
			if (DEVELOPMENT === true) error_log(sprintf('[DEBUG] <%s:%d> require_once: %s', __METHOD__, __LINE__, $controllerFile));
			require_once $controllerFile;
			//$controllerClass = 'MVC\\'.$controller;
			//if (DEVELOPMENT === true) error_log(sprintf('[DEBUG] <%s:%d> Instantiating $controllerClass: %s', __METHOD__, __LINE__, $controllerClass));
			//${$controller} = new $controllerClass;
			//return new self($controller, get_called_class());
		}
		else {
			error_log(sprintf('[WARN] <%s:%d> Controller "%s" not found.', __METHOD__, __LINE__, $controllerFile));
		}
	}*/
}

/**
 * Include Controller Files
 * @include sitemap.controller.php Required
 */
require_once dirname(__FILE__).'/sitemap.controller.php';
