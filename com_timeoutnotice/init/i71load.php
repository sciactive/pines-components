<?php
/**
 * Load timeout notice JS.
 *
 * @package Pines
 * @subpackage com_timeoutnotice
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if ( gatekeeper() ) {
	$module = new module('com_timeoutnotice', 'load_js', 'head');
	unset ($module);
}

?>