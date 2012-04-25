<?php
/**
 * Sign all packages.
 *
 * @package Components
 * @subpackage repository
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_repository/signpackage') )
	punt_user(null, pines_url('com_repository', 'signpackage'));

switch ($pines->com_repository->sign_packages((string) $_REQUEST['password'])) {
	case 0:
		pines_notice('Signed packages successfully.');
		break;
	case 1:
		pines_notice('There is no key to use to sign the packages. Please create a certificate.');
		break;
	case 2:
		$module = new module('com_repository', 'sign_password', 'content');
		return;
		break;
	default:
		pines_error('Error occurred while signing packages.');
		break;
}

pines_redirect(pines_url('com_repository', 'listpackages', array('all' => 'true')));

?>