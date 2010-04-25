<?php
/**
 * Display "about" information for the current installation.
 *
 * @package Pines
 * @subpackage com_about
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_about/show') )
	punt_user('You don\'t have necessary permission.', pines_url('com_about'));

$mod1 = new module('com_about', 'about1', 'content');
if ( $pines->config->com_about->describe_self )
	$mod2 = new module('com_about', 'about2', 'content');

?>