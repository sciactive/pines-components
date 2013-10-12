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
		'name' => 'business_review_name',
		'cname' => 'Business Review Name',
		'description' => 'Use this name to by default use a business name for testimonials.',
		'value' => '',
		'peruser' => true,
	),
	array(
		'name' => 'review_background',
		'cname' => 'Review Background',
		'description' => 'Color of the Review background in Hex Code, keeping the # sign. ie #ffffff',
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
	array(
		'name' => 'feedback_background',
		'cname' => 'Feedback Background',
		'description' => 'Color of the Feedback background in Hex Code, keeping the # sign. ie #ffffff',
		'value' => '',
		'peruser' => true,
	),
	array(
		'name' => 'feedback_background_opened',
		'cname' => 'Feedback Background Opened',
		'description' => 'Color of the Feedback background when expanded in Hex Code, keeping the # sign. ie #ffffff',
		'value' => '',
		'peruser' => true,
	),
	array(
		'name' => 'feedback_color',
		'cname' => 'Feedback Font Color',
		'description' => 'Color of the Feedback Font in Hex Code, keeping the # sign. ie #ffffff',
		'value' => '',
		'peruser' => true,
	),
	array(
		'name' => 'feedback_color_opened',
		'cname' => 'Feedback Font Color Opened',
		'description' => 'Color of the Feedback Font when expanded in Hex Code, keeping the # sign. ie #ffffff',
		'value' => '',
		'peruser' => true,
	),
	array(
		'name' => 'feedback_hr_top',
		'cname' => 'Feedback Line Border Top',
		'description' => 'Color of Top Border of the Feedback Horizontal Rule when expanded; in Hex Code, keeping the # sign. ie #ffffff',
		'value' => '',
		'peruser' => true,
	),
	array(
		'name' => 'feedback_hr_bottom',
		'cname' => 'Feedback Line Border Bottom',
		'description' => 'Color of Bottom Border of the Feedback Horizontal Rule when expanded; in Hex Code, keeping the # sign. ie #ffffff',
		'value' => '',
		'peruser' => true,
	),
	array(
		'name' => 'scroll_up_background',
		'cname' => 'List Scroll Up Background',
		'description' => 'Color of Scroll Up Bar of the List View, in Hex Code, keeping the # sign. ie #ffffff',
		'value' => '',
		'peruser' => true,
	),
	array(
		'name' => 'scroll_up_text',
		'cname' => 'List Scroll Up Text',
		'description' => 'Color of Scroll Up Bar Text of the List View, in Hex Code, keeping the # sign. ie #ffffff',
		'value' => '',
		'peruser' => true,
	),
	array(
		'name' => 'list_item_border',
		'cname' => 'List Item Border',
		'description' => 'Color of the top bottom border line on items in list view: in Hex Code, keeping the # sign. ie #ffffff',
		'value' => '',
		'peruser' => true,
	),
        array(
		'name' => 'misc_css',
		'cname' => 'All Misc CSS Rules',
		'description' => 'Write any misc CSS rules here to override any of the above settings.',
		'value' => '',
		'peruser' => true,
	),
);

?>