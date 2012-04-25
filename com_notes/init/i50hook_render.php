<?php
/**
 * Hook page render.
 *
 * @package Components
 * @subpackage notes
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

/**
 * Attach a note editor for any modules put in 'content' with an entity.
 */
function com_notes__attach_note_editors() {
	global $pines;
	foreach ((array) $pines->page->modules['content'] as $cur_module) {
		// It can't be a module from this component.
		if ($cur_module->component == 'com_notes')
			return;
		// It has to have one and only one entity.
		if (!isset($cur_module->entity) || !is_object($cur_module->entity))
			return;
		// And the user must be able to see threads.
		if (!gatekeeper('com_notes/seethreads'))
			return;
		// Now load the thread editor module.
		$module = new module('com_notes', 'thread/editor', $pines->config->com_notes->editor_position);
		$module->entity = $cur_module->entity;
	}
}

$pines->hook->add_callback('$pines->page->render', -10, 'com_notes__attach_note_editors');

?>