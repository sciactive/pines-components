<?php
/**
 * Configuration for the XROOM template.
 *
 * @package XROOM
 * @subpackage tpl_xroom
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('D_RUN') or die('Direct access prohibited');

/**
 * XROOM template class.
 *
 * @package XROOM
 * @subpackage tpl_xroom
 */
class tpl_xroom extends template {
    /**
     * The template format.
     * @var string $format
     */
	var $format = 'xhtml-1.0-strict-desktop';
}

$config->template = new tpl_xroom;

?>