<?php
/**
 * List packages.
 *
 * @package Components\plaza
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_plaza/listpackages') )
	punt_user(null, pines_url('com_plaza', 'package/reload'));

if ($pines->com_plaza->reload_packages()) {
	pines_notice('Package list reloaded successfully.');
} else {
	pines_error('Error reloading package list. Not all indices could be fetched.');
}

pines_redirect(pines_url('com_plaza', 'package/repository'));
?>