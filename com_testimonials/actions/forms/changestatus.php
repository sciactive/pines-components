<?php
/**
 * Change status on a testimonial.
 *
 * @package Components\testimonials
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Angela Murrell <angela@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_testimonials/changestatus') )
		punt_user(null, pines_url('com_testimonials', 'forms/changestatus'));

if ( isset($_REQUEST['id']) ) {
	$testimonial = com_testimonials_testimonial::factory((int) $_REQUEST['id']);
	if (!isset($testimonial->guid)) {
		pines_error('Requested testimonial id is not accessible.');
		return;
	}
}

$testimonial->changestatus_form();

?>