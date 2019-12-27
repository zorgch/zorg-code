<?php
/**
 * Gallery Maker MVC Controller
 *
 * @author IneX
 * @package zorg\Gallery
 * @version 1.0
 * @since 1.0 <inex> 27.12.2019 File added
 */
namespace MVC\Controller;

/**
 * File includes
 * @include core.model.php Required
 */
require_once(__DIR__ .'/../models/core.model.php');
use MVC; // Fix namespace reference compatibility for MVC Model

/**
 * Class representing the MVC Controller
 */
class GalleryMaker extends Controller
{
	/**
	 * @var object $model Gallery Model
	 */
	var $model;

	/**
	 * @version 1.0
	 * @since 1.0 <inex> 27.12.2019 method added
	 */
	public function __construct()
	{
		/** Initialise MVC Model */
		$this->model = new MVC\Gallery();
	}
}
