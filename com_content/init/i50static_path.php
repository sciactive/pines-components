<?php
/**
 * Replace paths.
 *
 * @package Components
 * @subpackage content
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if ($pines->config->static_location === '' || !$pines->config->com_content->replace_static)
	return;

/**
 * Replace paths to the upload dir with static paths.
 *
 * @param array &$array Return value array.
 */
function com_content__replace_static(&$array) {
	global $pines;
	$array[0] = str_replace($pines->config->rela_location.$pines->config->upload_location, $pines->config->static_location.$pines->config->upload_location, $array[0]);
}

$pines->hook->add_callback('$pines->format_content', 10, 'com_content__replace_static');

?>