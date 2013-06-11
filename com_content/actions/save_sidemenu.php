<?php
/**
 * Save side menu items
 *
 * @package Components\content
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Grey Vugrin <greyvugrin@gmail.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

$pines->page->override = true;
header('Content-Type: application/json');

$guid_order = $_REQUEST['guid_order'];
$tag = $_REQUEST['tag'];
$order_array = explode(',', $guid_order);

$result = $pines->com_content->save_sidemenu($order_array, $tag);


$pines->page->override_doc(json_encode($result));
?>