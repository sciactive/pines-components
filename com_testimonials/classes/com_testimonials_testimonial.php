<?php
/**
 * com_testimonials_testimonial class.
 *
 * @package Components\testimonials
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Angela Murrell <angela@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

/**
 * A testimonial.
 *
 * @package Components\testimonials
 */
class com_testimonials_testimonial extends entity {
	/**
	 * Load a testimonial.
	 * @param int $id The ID of the testimonial to load, 0 for a new testimonial.
	 */
	public function __construct($id = 0) {
		parent::__construct();
		$this->add_tag('com_testimonials', 'testimonial');
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
		$this->enabled = true;
		$this->ac = (object) array('user' => 3, 'group' => 3, 'other' => 2);
		$this->attributes = array();
	}

	/**
	 * Create a new instance.
	 * @return com_testimonials_testimonial The new instance.
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
				return "Testimonial $this->id";
			case 'type':
				return 'testimonial';
			case 'types':
				return 'testimonials';
			case 'url_edit':
				if (gatekeeper('com_testimonials/edittestimonials'))
					return pines_url('com_testimonials', 'testimonial/edit', array('id' => $this->guid));
				break;
			case 'url_list':
				if (gatekeeper('com_testimonials/listtestimonials'))
					return pines_url('com_testimonials', 'testimonials/list');
				break;
			case 'icon':
				return 'picon-view-conversation-balloon';
		}
		return null;
	}

	/**
	 * Delete the testimonial.
	 * @return bool True on success, false on failure.
	 */
	public function delete() {
		if (!parent::delete())
			return false;
		pines_log("Deleted testimonial with ID $this->id.", 'notice');
		return true;
	}

	/**
	 * Save with an incremental ID.
	 * @return bool True on success, false on failure.
	 */
	public function save() {
		global $pines;
		if (!isset($this->id))
			$this->id = $pines->entity_manager->new_uid('com_testimonials_testimonial');
		return parent::save();
	}

	/**
	 * Print a form to edit the loan.
	 * @return module The form's module.
	 */
	public function print_form() {
		$module = new module('com_testimonials', 'testimonial/edit', 'content');
		$module->entity = $this;

		return $module;
	}

	/**
	 * Print a form to change the status on a testimonial.
	 *
	 * Uses a page override to only print the form.
	 *
	 * @return module The form's module.
	 */
	public function changestatus_form() {
		global $pines;
		$pines->page->override = true;

		$module = new module('com_testimonials', 'forms/changestatus', 'content');
		$module->entity = $this;

		$pines->page->override_doc($module->render());
		return $module;
	}
	
	/**
	 * Creates and attaches a module which views a testimonial.
	 * @return module The module.
	 */
	public function print_view() {
		$module = new module('com_testimonials', 'testimonial/view', 'content');
		$module->entity = $this;

		return $module;
	}
	
	
	/**
	 * Creates the Author information on a testimonial
	 * based on Anon Preferences and Location
	 * 
	 * @return string.
	 */
	public function create_author() {
		$first_name = $this->customer->name_first;
		// Customer's Address Info
		$city = $this->customer->city;
		$state = $this->customer->state;

		if (!empty($city) && !empty($state)) {
			if ($this->anon) {
				return false;
			} else {
				return $first_name.' in '.$city.', '.$state;
			}
		} else {
			if ($this->anon) {
				return false;
			} else {
				return $first_name;
			}
		}
		
	}
}

?>