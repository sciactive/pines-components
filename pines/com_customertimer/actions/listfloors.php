<?php
/**
 * List floors.
 *
 * @package Pines
 * @subpackage com_customertimer
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_customertimer/listfloors') )
	punt_user('You don\'t have necessary permission.', pines_url('com_customertimer', 'listfloors'));

$pines->com_customertimer->list_floors();
?>