<?php
/**
 * tpl_print class.
 *
 * @package Pines
 * @subpackage tpl_print
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

/**
 * tpl_print main class.
 *
 * A simple template which only outputs the content position. Good for printing
 * a page.
 *
 * @package Pines
 * @subpackage tpl_print
 */
class tpl_print extends template {
	/**
	 * The template format.
	 * @var string $format
	 */
	var $format = 'xhtml-1.0-strict-desktop';
	/**
	 * The editor CSS location, relative to Pines' directory.
	 * @var string $editor_css
	 */
	var $editor_css = 'templates/tpl_print/css/editor.css';
}

?>