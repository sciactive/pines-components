<?php
/**
 * com_testimonials' defaults.
 *
 * @package Components\testimonials
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Angela Murrell <angela@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

return array(
	array(
		'name' => 'scroll_load',
		'cname' => 'Load Testimonials on window scroll.',
		'description' => 'Load the testimonials modules only on window scroll. Put scroll-load and manual-trigger classes on testimonial-box wrapper.',
		'value' => false,
		'peruser' => true,
	),
	array(
		'name' => 'signup_link',
		'cname' => 'Signup Link',
		'description' => 'Link to use by default on the share form for users not logged in on any testimonials or reviews.',
		'value' => pines_url(),
		'peruser' => true,
	),
	array(
		'name' => 'business_review_name',
		'cname' => 'Business Review Name',
		'description' => 'Use this name to by default use a business name for testimonials.',
		'value' => '',
		'peruser' => true,
	),
	array(
		'name' => 'review_text',
		'cname' => 'Review Text',
		'description' => 'Color of the review text in Hex Code, keeping the # sign. ie #ffffff',
		'value' => '',
		'peruser' => true,
	),
	array(
		'name' => 'average_background',
		'cname' => 'Review Heading Background',
		'description' => 'Color of the Review Heading background in Hex Code, keeping the # sign. ie #ffffff',
		'value' => '',
		'peruser' => true,
	),
	array(
		'name' => 'author_text',
		'cname' => 'Author / Average Text',
		'description' => 'Color of the author text and average text background in Hex Code, keeping the # sign. ie #ffffff',
		'value' => '',
		'peruser' => true,
	),
);

?>