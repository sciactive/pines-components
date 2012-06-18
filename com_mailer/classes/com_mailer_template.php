<?php
/**
 * com_mailer_template class.
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
 * A template.
 *
 * @package Components\mailer
 */
class com_mailer_template extends entity {
	/**
	 * Load a template.
	 * @param int $id The ID of the template to load, 0 for a new template.
	 */
	public function __construct($id = 0) {
		parent::__construct();
		$this->add_tag('com_mailer', 'template');
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
		$this->content = '<img style="float: right;" src="'.htmlspecialchars($pines->config->location.'media/logos/default_logo.png').'" alt="Logo" />
			<h2>#system_name#</h2>
			<div>#content#</div>
			<br />
			<p style="text-align: center;">You received this email because you have an account at <a href="#site_link#">#system_name#</a>.<br />If you no longer wish to receive emails from us, you can <a href="#unsubscribe_link#">unsubscribe</a>.</p>';
		$this->ac->other = 1;
	}

	/**
	 * Create a new instance.
	 * @return com_mailer_template The new instance.
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
				return 'template';
			case 'types':
				return 'templates';
			case 'url_edit':
				if (gatekeeper('com_mailer/edittemplate'))
					return pines_url('com_mailer', 'template/edit', array('id' => $this->guid));
				break;
			case 'url_list':
				if (gatekeeper('com_mailer/listtemplates'))
					return pines_url('com_mailer', 'template/list');
				break;
			case 'icon':
				return 'picon-internet-mail';
		}
		return null;
	}

	/**
	 * Delete the template.
	 * @return bool True on success, false on failure.
	 */
	public function delete() {
		if (!parent::delete())
			return false;
		pines_log("Deleted template $this->name.", 'notice');
		return true;
	}

	/**
	 * Save the template.
	 * @return bool True on success, false on failure.
	 */
	public function save() {
		if (!isset($this->name))
			return false;
		return parent::save();
	}

	/**
	 * Print a form to edit the template.
	 * @return module The form's module.
	 */
	public function print_form() {
		$module = new module('com_mailer', 'template/form', 'content');
		$module->entity = $this;

		return $module;
	}
}

?>