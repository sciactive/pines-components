<?php
/**
 * List modules.
 *
 * @package Pines
 * @subpackage com_modules
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_modules/listmodules') )
	punt_user(null, pines_url('com_modules', 'module/list'));

$pines->com_modules->list_modules();
?>