<?php
/**
 * List packages.
 *
 * @package Pines
 * @subpackage com_packager
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_packager/listpackages') )
	punt_user('You don\'t have necessary permission.', pines_url('com_packager', 'listpackages'));

$pines->com_packager->list_packages();
?>