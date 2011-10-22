<?php
/**
 * List packages.
 *
 * @package Pines
 * @subpackage com_repository
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_repository/listpackages') && !gatekeeper('com_repository/listallpackages') )
	punt_user(null, pines_url('com_repository', 'listpackages'));

if ($_REQUEST['all'] == 'true' && gatekeeper('com_repository/listallpackages')) {
	$pines->com_repository->list_packages();
} else {
	$pines->com_repository->list_packages($_SESSION['user']);
}

?>