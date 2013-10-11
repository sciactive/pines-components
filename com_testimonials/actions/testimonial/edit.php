<?php
/**
 * Provide a form to edit a testimonial.
 *
 * @package Components\testimonials
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Angela Murrell <angela@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if (!empty($_REQUEST['id'])) {
	if ( !gatekeeper('com_testimonials/edittestimonials') )
		punt_user(null, pines_url('com_testimonials', 'testimonial/edit', array('id' => $_REQUEST['id'])));
} else {
	if ( !gatekeeper('com_testimonials/newtestimonials') )
		punt_user(null, pines_url('com_testimonials', 'testimonial/edit'));
}

$entity = com_testimonials_testimonial::factory((int) $_REQUEST['id']);
$entity->print_form();

?>