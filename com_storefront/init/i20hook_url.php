<?php
/**
 * Hook URL creation.
 *
 * @package Components\storefront
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if (!$pines->config->com_storefront->storefront_location)
	return;

/**
 * Check for a storefront URL.
 *
 * @param array &$array An array of arguments. (The options array and the selectors.)
 * @param mixed $name Unused.
 * @param mixed &$object Unused.
 * @param mixed &$function Unused.
 * @param array &$data The callback data array.
 */
function com_storefront__url(&$array, $name, &$object, &$function, &$data) {
	global $pines;
	if (!$pines->config->com_storefront->storefront_location)
		return;
	if ($array[0] == 'com_storefront') {
		if (!$array[1])
			$array[1] = '';
		if (!$array[2])
			$array[2] = array();
		$array[3] = true;
		$data['com_storefront__location'] = $pines->config->full_location;
		$pines->config->full_location = $pines->config->com_storefront->storefront_location;
	}
}

/**
 * Put the original location back into config.
 *
 * @param array &$array Unused.
 * @param mixed $name Unused.
 * @param mixed &$object Unused.
 * @param mixed &$function Unused.
 * @param array &$data The callback data array.
 */
function com_storefront__url_after(&$array, $name, &$object, &$function, &$data) {
	global $pines;
	if ($data['com_storefront__location'])
		$pines->config->full_location = $data['com_storefront__location'];
}

$pines->hook->add_callback('$pines->template->url', -10, 'com_storefront__url');
$pines->hook->add_callback('$pines->template->url', 10, 'com_storefront__url_after');

?>