<?php
/**
 * com_mailer_rendition class.
 *
 * @package Components\mailer
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

/**
 * A rendition.
 *
 * @package Components\mailer
 */
class com_mailer_rendition extends entity {
	/**
	 * Load a rendition.
	 * @param int $id The ID of the rendition to load, 0 for a new rendition.
	 */
	public function __construct($id = 0) {
		parent::__construct();
		$this->add_tag('com_mailer', 'rendition');
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
		global $pines;
		$this->enabled = true;
		$this->conditions = array();
		$this->ac->other = 1;
	}

	/**
	 * Create a new instance.
	 * @return com_mailer_rendition The new instance.
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
				return $this->name;
			case 'type':
				return 'rendition';
			case 'types':
				return 'renditions';
			case 'url_edit':
				if (gatekeeper('com_mailer/editrendition'))
					return pines_url('com_mailer', 'rendition/edit', array('id' => $this->guid));
				break;
			case 'url_list':
				if (gatekeeper('com_mailer/listrenditions'))
					return pines_url('com_mailer', 'rendition/list');
				break;
			case 'icon':
				return 'picon-internet-mail';
		}
		return null;
	}

	/**
	 * Delete the rendition.
	 * @return bool True on success, false on failure.
	 */
	public function delete() {
		if (!parent::delete())
			return false;
		pines_log("Deleted rendition $this->name.", 'notice');
		return true;
	}

	/**
	 * Save the rendition.
	 * @return bool True on success, false on failure.
	 */
	public function save() {
		if (!isset($this->name))
			return false;
		return parent::save();
	}

	/**
	 * Print a form to edit the rendition.
	 * @return module The form's module.
	 */
	public function print_form() {
		$module = new module('com_mailer', 'rendition/form', 'content');
		$module->entity = $this;

		return $module;
	}
}

?>