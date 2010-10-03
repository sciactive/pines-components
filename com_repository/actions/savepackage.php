<?php
/**
 * Save a package to the repository.
 *
 * @package Pines
 * @subpackage com_repository
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_repository/newpackage') )
	punt_user(null, pines_url('com_repository', 'listpackages'));

pines_notice('Saved package ['.$package->name.']');
pines_error('Error saving package. Do you have permission?');

redirect(pines_url('com_repository', 'listpackages'));

?>