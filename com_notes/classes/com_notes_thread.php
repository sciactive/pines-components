<?php
/**
 * com_notes_thread class.
 *
 * @package Components\notes
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

/**
 * A thread.
 *
 * @package Components\notes
 */
class com_notes_thread extends entity {
	/**
	 * Load a thread.
	 * @param int $id The ID of the thread to load, 0 for a new thread.
	 */
	public function __construct($id = 0) {
		parent::__construct();
		$this->add_tag('com_notes', 'thread');
		if ($id > 0) {
			global $pines;
			$entity = $pines->entity_manager->get_entity(array('class' => get_class($this)), array('&', 'guid' => $id, 'tag' => $this->tags));
			if (isset($entity)) {
				$this->guid = $entity->guid;
				$this->tags = $entity->tags;
				$this->put_data($entity->get_data(), $entity->get_sdata());
				return;
			}
		}
		// Defaults.
		$this->ac = (object) array('user' => 3, 'group' => 2, 'other' => 2);
		$this->notes = array();
		$this->hidden = false;
	}

	/**
	 * Create a new instance.
	 * @return com_notes_thread The new instance.
	 */
	public static function factory() {
		global $pines;
		$class = get_class();
		$args = func_get_args();
		$entity = new $class($args[0]);
		$pines->hook->hook_object($entity, $class.'->', false);
		return $entity;
	}

	public function info($type) {
		switch ($type) {
			case 'name':
				return "Note Thread $this->guid";
			case 'type':
				return 'note thread';
			case 'types':
				return 'note threads';
			case 'url_view':
				if (gatekeeper('com_notes/seethreads') && isset($this->entities[0]->guid)) {
					$view = $this->entities[0]->info('url_view');
					if ($view)
						return $view;
					$edit = $this->entities[0]->info('url_edit');
					if ($edit)
						return $edit;
				}
				break;
			case 'url_edit':
				if (gatekeeper('com_notes/editthread'))
					return pines_url('com_notes', 'thread/edit', array('id' => $this->guid));
				break;
			case 'url_list':
				if (gatekeeper('com_notes/listthreads'))
					return pines_url('com_notes', 'thread/list');
				break;
			case 'icon':
				return 'picon-view-pim-notes';
		}
		return null;
	}

	/**
	 * Print a form to edit the thread.
	 * @return module The form's module.
	 */
	public function print_form() {
		$module = new module('com_notes', 'thread/form', 'content');
		$module->entity = $this;

		return $module;
	}
}

?>