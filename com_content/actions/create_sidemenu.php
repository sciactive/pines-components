<?php
/**
 * Create side menu items
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

$tag = $_REQUEST['tag'];
json_decode($tag);

$result = $pines->com_content->create_sidemenu($tag);

$pines->page->override_doc(json_encode($result));
?>