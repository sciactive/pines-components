<?php
/**
 * Provide a file manager.
 *
 * @package Pines
 * @subpackage com_elfinder
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_elfinder/finder') )
	punt_user('You don\'t have necessary permission.', pines_url('com_elfinder', 'finder'));

$module = new module('com_elfinder', 'finder', 'content');

?>