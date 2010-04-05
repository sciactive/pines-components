<?php
/**
 * com_pinlock class.
 *
 * @package Pines
 * @subpackage com_pinlock
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

/**
 * com_pinlock main class.
 *
 * @package Pines
 * @subpackage com_pinlock
 */
class com_pinlock extends component {
	/**
	 * The component originally requested.
	 * @var string $component
	 */
	public $component;
	/**
	 * The action originally requested.
	 * @var string $action
	 */
	public $action;
	/**
	 * A unique session ID used to remember POST and GET data.
	 * @var string $sessionid
	 */
	public $sessionid;
}

?>