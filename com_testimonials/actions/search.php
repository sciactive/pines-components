<?php
/**
 * Search for testimonials.
 *
 * @package Components\testimonials
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Angela Murrell <angela@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_testimonials/search') )
	punt_user(null, pines_url('com_testimonials', 'testimonial/list', $_REQUEST));

$pines->page->override = true;
header('Content-Type: application/json');

$query = trim($_REQUEST['q']);
$type = trim($_REQUEST['type']);
$status = trim($_REQUEST['status']);

// Build the main selector, including location and timespan.
if ($type == 'review')
	$tags = array('com_testimonials', 'testimonial', $status, $type);
else
	$tags = array('com_testimonials', 'testimonial', $status);
$selector = array('&', 'tag' => $tags);
$selector2 = array('!&', 'tag' => 'review');

if (empty($query)) {
	$testimonials = array();
} elseif ($query == '*') {
	if (!gatekeeper('com_testimonials/listalltestimonials'))
		$testimonials = array();
	else {
		if ($type != 'review') {
			$args = array(
				array('class' => com_testimonials_testimonial),
				$selector,
				$selector2
			);
		} else {
			$args = array(
				array('class' => com_testimonials_testimonial),
				$selector
			);
		}
		$testimonials = (array) call_user_func_array(array($pines->entity_manager, 'get_entities'), $args);
	}
} else {
	$r_query = '/'.str_replace(' ', '.*', preg_quote($query)).'/i';
	$a_query = preg_match('/,/', $query); // has a comma in it
	if ($a_query) {
		$tag_query = explode(',', $query);
		foreach ($tag_query as $key => $cur_tag) {
			if (preg_match('/com_/', $cur_tag))
				$tag_query[$key] = 'review_'.$cur_tag;
		}
	} else {
		$tag_query = $query;
		if (preg_match('/com_/', $tag_query))
			$tag_query = 'review_'.$tag_query;
	}
	$selector3 = array('|',
		'match' => array(
			array('name', $r_query),
			array('username', $r_query),
			array('email', $r_query)
		),
		'tag' => $tag_query
	);
		
	$args = array(
		array('class' => com_testimonials_testimonial),
		$selector,
		$selector3
	);
	$testimonials = (array) call_user_func_array(array($pines->entity_manager, 'get_entities'), $args);
}

foreach ($testimonials as $key => &$cur_testimonial) {
	
	if (!isset($cur_testimonial->status))
		$status = "Pending";
	elseif ($cur_testimonial->status)
		$status = "Approved";
	elseif (!$cur_testimonial->status)
		$status = "Denied";
	
	$json_struct = (object) array(
		'guid'			=> $cur_testimonial->guid,
		'id'			=> $cur_testimonial->id,
		'customer_guid'	=> (string) $cur_testimonial->customer->guid,
		'customer_name'	=> (string) $cur_testimonial->customer->name,
		'user_name'	=> (string) $cur_testimonial->user->name,
		'user_guid'	=> (string) $cur_testimonial->user->guid,
		'location'		=> (string) $cur_testimonial->group->name,
		'email'			=> (string) $cur_testimonial->customer->email,
		'city'			=> (string) $cur_testimonial->customer->city,
		'state'			=> (string) $cur_testimonial->customer->state,
		'creation_date'	=> format_date($cur_testimonial->p_cdate, "date_short"),
		'status'		=> $status,
		'rating'		=> (!empty($cur_testimonial->rating)) ? $cur_testimonial->rating : '',
		'share_allowed'	=> ($cur_testimonial->share) ? 'Yes' : 'No',
		'share_anon'	=> ($cur_testimonial->anon) ? 'Yes' : 'No',
		'original'		=> substr($cur_testimonial->feedback, 0, 40),
		'quotes'		=> (!empty($cur_testimonial->quotefeedback)) ? htmlspecialchars(substr($cur_testimonial->quotefeedback, 0, 40)).'...' : ''
	);
	$cur_testimonial = $json_struct;
}
unset($cur_testimonial);

if (!$testimonials)
	$testimonials = null;

$pines->page->override_doc(json_encode($testimonials));

?>