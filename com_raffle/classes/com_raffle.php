<?php
/**
 * com_raffle class.
 *
 * @package Components\raffle
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

/**
 * com_raffle main class.
 *
 * @package Components\raffle
 */
class com_raffle extends component {
	/**
	 * Creates and attaches a module which lists raffles.
	 * @return module The module.
	 */
	public function list_raffles() {
		global $pines;

		$module = new module('com_raffle', 'raffle/list', 'content');

		$module->raffles = $pines->entity_manager->get_entities(
				array('class' => com_raffle_raffle),
				array('&',
					'tag' => array('com_raffle', 'raffle')
				)
			);

		if ( empty($module->raffles) )
			pines_notice('There are no raffles.');

		return $module;
	}
}

?>