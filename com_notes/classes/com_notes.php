<?php
/**
 * com_notes class.
 *
 * @package Pines
 * @subpackage com_notes
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

/**
 * com_notes main class.
 *
 * @package Pines
 * @subpackage com_notes
 */
class com_notes extends component {
	/**
	 * Creates and attaches a module which lists threads.
	 * @return module The module.
	 */
	public function list_threads() {
		global $pines;

		$module = new module('com_notes', 'thread/list', 'content');

		$module->threads = $pines->entity_manager->get_entities(
				array('class' => com_notes_thread),
				array('&',
					'tag' => array('com_notes', 'thread')
				)
			);

		if ( empty($module->threads) )
			pines_notice('No threads found.');

		return $module;
	}
}

?>