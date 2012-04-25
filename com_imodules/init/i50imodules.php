<?php
/**
 * Hook the content formatter.
 *
 * @package Components
 * @subpackage imodules
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if (!$pines->config->com_imodules->parse_imodules)
	return;

/**
 * Parse imodules in content.
 *
 * @param array &$arguments Arguments.
 */
function com_imodules__parse_imodules(&$arguments) {
	global $pines;
	$pines->com_imodules->parse_imodules($arguments[0]);
}

$pines->hook->add_callback('$pines->format_content', -10, 'com_imodules__parse_imodules');

?>