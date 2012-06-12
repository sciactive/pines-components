<?php
/**
 *  Back end for Google Categories Auto-Completion.
 *
 * @package Components\sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Angela Murrell <angela@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_sales/editcategory') && !gatekeeper('com_sales/newcategory'))
	punt_user(null, pines_url('com_sales', 'category/list'));

header('Content-Type: application/json');
$pines->page->override = true;

$googlecategories = file_get_contents("components/com_sales/includes/googlecategories.txt");

$query = preg_quote($_REQUEST['term'], '/');

$results = array();

if ( $query != '' && preg_match_all('/^.*'.$query.'.*$/mi', $googlecategories, $matches))
	$results = array_slice($matches[0], 0, 10);

foreach ($results as &$cur_result){
	$display = preg_replace('/('.$query.')/i', '<strong>$1</strong>', $cur_result);
	$cur_result = array('value' => $cur_result, 'label' => $display);
}
unset($cur_result);

$pines->page->override_doc(json_encode($results));

?>