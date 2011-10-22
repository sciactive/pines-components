<?php
/**
 * Clear the package and index caches.
 *
 * @package Pines
 * @subpackage com_plaza
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_plaza/editpackages') )
	punt_user(null, pines_url('com_plaza', 'clearcache'));

if ($pines->com_plaza->clear_cache()) {
	pines_notice('Package and index cache cleared successfully.');
} else {
	pines_error('Error clearing cache.');
}

pines_action('com_plaza', 'reload');
?>