<?php
/**
 * Configuration for the Dandelion template.
 *
 * @package Dandelion
 * @subpackage tpl_dandelion
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('D_RUN') or die('Direct access prohibited');

/**
 * Dandelion template class.
 *
 * @package Dandelion
 * @subpackage tpl_dandelion
 */
class tpl_dandelion extends template {
    /**
     * The template format.
     * @var string $format
     */
	var $format = 'xhtml-1.0-strict-desktop';
}

$config->template = new tpl_dandelion;

?>