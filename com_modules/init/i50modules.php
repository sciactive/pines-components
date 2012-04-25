<?php
/**
 * Show configured modules.
 *
 * @package Components
 * @subpackage modules
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if (!$pines->config->com_modules->show_modules)
	return;

$modules = (array) $pines->entity_manager->get_entities(
		array('class' => com_modules_module),
		array('&',
			'tag' => array('com_modules', 'module'),
			'data' => array('enabled', true)
		)
	);
$pines->entity_manager->sort($modules, 'order');
foreach ($modules as $cur_module) {
	if ($cur_module->check_conditions())
		$cur_module->print_module();
}
unset($modules, $cur_module);

?>