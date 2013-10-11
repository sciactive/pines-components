<?php
/**
 * Update a testimonial.
 *
 * @package Components\testimonials
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Angela Murrell <angela@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if ( isset($_REQUEST['id']) ) {
	if ( !gatekeeper('com_testimonials/changestatus') )
		punt_user(null, pines_url('com_testimonials', 'testimonial/list'));
	$testimonial = com_testimonials_testimonial::factory((int) $_REQUEST['id']);
	if (!isset($testimonial->guid)) {
		pines_error('Requested testimonial id is not accessible.');
		return;
	}
} else {
	pines_error('Requested testimonial id is not accessible.');
	return;
}

// Get user input variables.
$testimonial->quotefeedback = $_REQUEST['quotefeedback'];
$testimonial->status = $_REQUEST['status'];


// Check user input for empty values.
if (!isset($testimonial->status)) {
	$testimonial->print_form();
	pines_notice('Please specify a status.');
	return;
}

// Make it a testimonial
if ($testimonial->share) {
	if (!empty($testimonial->quotefeedback)) {
		$testimonial->testimonial = $testimonial->quotefeedback;
	} else {
		$testimonial->testimonial = $testimonial->feedback;
	}
}

// Add / Remove appropriate SHARING tag
if ($testimonial->share) {
	$testimonial->add_tag('share');
} else {
	$testimonial->remove_tag('share');
}

// Add / Remove appropriate status tags
// I remove share tags if the testimonial is not approved
switch ($testimonial->status) {
	case 'pending':
		if (!$testimonial->has_tag('pending')) {
			$testimonial->add_tag('pending');
			$testimonial->remove_tag('share', 'denied', 'approved');
		}
		break;
	case 'approved':
		if (!$testimonial->has_tag('approved')) {
			$testimonial->add_tag('approved');
			$testimonial->remove_tag('denied', 'pending');
		}
		break;
	case 'denied':
		if (!$testimonial->has_tag('denied')) {
			$testimonial->add_tag('denied');
			$testimonial->remove_tag('share', 'pending', 'approved');
		}
		break;
}

if ($testimonial->save())
	pines_notice('Updated testimonial '.$testimonial->id.' for customer '.$testimonial->customer->name.'.');
else
	pines_error('Error updating testimonial. Do you have permission?');
pines_redirect(pines_url('com_testimonials', 'testimonial/list'));



?>