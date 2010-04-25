<?php
/**
 * List widgets.
 *
 * @package Pines
 * @subpackage com_example
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_example/listwidgets') )
	punt_user('You don\'t have necessary permission.', pines_url('com_example', 'listwidgets'));

$pines->com_example->list_widgets();
?>