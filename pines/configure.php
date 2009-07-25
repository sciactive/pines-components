<?php
/**
 * Configuration for the Pines template.
 *
 * @package Pines
 * @subpackage tpl_pines
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

/**
 * Pines template class.
 *
 * @package Pines
 * @subpackage tpl_pines
 */
class tpl_pines extends template {
    /**
     * The template format.
     * @var string $format
     */
	var $format = 'xhtml-1.0-strict-desktop';
}

$config->template = new tpl_pines;

?>