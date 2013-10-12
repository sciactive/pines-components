<?php
/**
 * Get testimonials.
 *
 * @package Components\testimonials
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Angela Murrell <angela@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

// The permissions on this are weak, because we want testimonials to be public

$pines->page->override = true;
header('Content-Type: application/json');

// If not set, they will just be null
$review_entity_class = !empty($_REQUEST['review_entity']) ? $_REQUEST['review_entity'] : null;
$review_entity_guid = !empty($_REQUEST['review_entity_id']) ? $_REQUEST['review_entity_id'] : null;
$review_option_name = !empty($_REQUEST['review_name']) ? $_REQUEST['review_name'] : null;
$review_option_additional_tags = (!empty($_REQUEST['review_tags'])) ? explode(',', preg_replace('/ /', '_', $_REQUEST['review_tags'])) : array('approved', 'share');
$review_data_type = isset($_REQUEST['review_data_type']) ? $_REQUEST['review_data_type'] : 'individual';
$review_ratings_off = ($_REQUEST['review_ratings_off'] == 'true') ? true : false;

$review_option_reverse = ($_REQUEST['review_reverse'] == 'false') ? false : true;
$review_option_offset = isset($_REQUEST['review_offset']) ? (int) $_REQUEST['review_offset'] : 0;
$review_option_limit = isset($_REQUEST['review_limit']) ? (int) $_REQUEST['review_limit'] : 20;

if ($_REQUEST['review_option_type'] == 'review') {
	$review_option_additional_tags[] = 'review'; // add this tag so we search by this.
}
$result = $pines->com_testimonials->get_testimonials($review_data_type, $review_option_reverse, $review_option_limit, $review_option_offset, $review_option_additional_tags, $review_entity_guid, $review_entity_class, $review_option_name, $review_ratings_off);

$pines->page->override_doc(json_encode($result));

?>