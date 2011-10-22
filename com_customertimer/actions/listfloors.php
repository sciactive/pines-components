<?php
/**
 * List floors.
 *
 * @package Pines
 * @subpackage com_customertimer
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_customertimer/listfloors') )
	punt_user(null, pines_url('com_customertimer', 'listfloors'));

$pines->com_customertimer->list_floors();
?>