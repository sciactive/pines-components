<?php
/**
 * Search users, returning JSON.
 *
 * @package Components\user
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Angela Murrell <angela@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_customer/listusers') )
	punt_user(null, pines_url('com_user', 'search', $_REQUEST));

$pines->page->override = true;
header('Content-Type: application/json');

$query = trim($_REQUEST['q']);

// Build the main selector, including location and timespan.
$selector = array('&', 'tag' => array('com_user', 'user'));

if (empty($query)) {
	$users = array();
} else {
	$num_query = preg_replace('/\D/', '', $query);
	$r_query = '/'.str_replace(' ', '.*', preg_quote($query)).'/i';
	$r_num_query = '/'.preg_quote($num_query).'/';
	$selector2 = array('|',
		'match' => array(
			array('name', $r_query),
			array('username', $r_query),
			array('email', $r_query)
		)
	);
	if ($num_query != '') {
		$selector2['match'][] = array('phone_home', $r_num_query);
		$selector2['match'][] = array('phone_work', $r_num_query);
		$selector2['match'][] = array('phone_cell', $r_num_query);
		$selector2['match'][] = array('fax', $r_num_query);
	}
	$args = array(
			array('class' => user, 'limit' => $pines->config->com_user->user_search_limit),
			$selector,
			$selector2
		);
	$users = (array) call_user_func_array(array($pines->entity_manager, 'get_entities'), $args);
}

foreach ($users as $key => &$cur_user) {
	$json_struct = (object) array(
		'guid'			=> (int) $cur_user->guid,
		'name'			=> (string) $cur_user->name,
		'email'			=> (string) $cur_user->email,
		'phone_home'	=> format_phone($cur_user->phone_home),
		'phone_work'	=> format_phone($cur_user->phone_work),
		'phone_cell'	=> format_phone($cur_user->phone_cell)
	);
	$cur_user = $json_struct;
}
unset($cur_user);

if (!$users)
	$users = null;

$pines->page->override_doc(json_encode($users));

?>