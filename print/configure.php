<?php
/**
 * Configuration for the Pines template.
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
 * Print template class.
 *
 * This template will only show the content modules, and has no border/graphics.
 *
 * @package Pines
 * @subpackage tpl_print
 */
class tpl_print extends template {
	/**
	 * The template format.
	 * @var string $format
	 */
	var $format = 'xhtml-1.0-strict-print';
	/**
	 * The editor CSS location.
	 * @var string $editor_css
	 */
	var $editor_css = '';

	function __construct() {
		global $pines;
		$this->editor_css = $pines->rela_location.'templates/print/css/editor.css';
	}
}

$pines->template = new tpl_print;

?>