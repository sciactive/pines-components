<?php
/**
 * Provide a wizard to create packages.
 *
 * @package Pines
 * @subpackage com_packager
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_packager/newpackage') )
	punt_user(null, pines_url('com_packager', 'package/wizard'));

$pines->com_packager->package_wizard();

?>