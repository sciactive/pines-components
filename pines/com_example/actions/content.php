<?php
/**
 * Show content positions.
 *
 * @package Pines
 * @subpackage com_example
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper() )
	punt_user('You don\'t have necessary permission.', pines_url('com_example', 'content', null, false));

$pines->com_example->print_content();
?>