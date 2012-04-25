<?php
/**
 * Hook the content formatter.
 *
 * @package Components\replace
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if (!$pines->config->com_replace->search_replace)
	return;

/**
 * Search and replace in content.
 *
 * @param array &$arguments Arguments.
 */
function com_replace__search_replace(&$arguments) {
	global $pines;
	$pines->com_replace->search_replace($arguments[0]);
}

$pines->hook->add_callback('$pines->format_content', -10, 'com_replace__search_replace');

?>