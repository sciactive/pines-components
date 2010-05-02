<?php
/**
 * com_sales_cashcount_deposit class.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

/**
 * A deposit off of skims from a cash count.
 *
 * @package com_sales
 */
class com_sales_cashcount_deposit extends entity {
	/**
	 * Load a deposit.
	 * @param int $id The ID of the deposit to load, 0 for a new deposit.
	 */
	public function __construct($id = 0) {
		parent::__construct();
		$this->add_tag('com_sales', 'cashcount_audit');
		// Defaults
		$this->status = 'pending';
		$this->count = array();
		if ($id > 0) {
			global $pines;
			$entity = $pines->entity_manager->get_entity(array('guid' => $id, 'tags' => $this->tags, 'class' => get_class($this)));
			if (!isset($entity))
				return;
			$this->guid = $entity->guid;
			$this->tags = $entity->tags;
			$this->put_data($entity->get_data());
		}
	}

	/**
	 * Create a new instance.
	 */
	public static function factory() {
		global $pines;
		$class = get_class();
		$args = func_get_args();
		$entity = new $class($args[0]);
		$pines->hook->hook_object($entity, $class.'->', false);
		return $entity;
	}

	/**
	 * Delete the cash count deposit.
	 * @return bool True on success, false on failure.
	 */
	public function delete() {
		if (!parent::delete())
			return false;
		pines_log("Deleted Deposit {$this->guid}.", 'notice');
		return true;
	}


	/**
	 * Print a form to deposit off of a cash count.
	 * @return module The form's module.
	 */
	public function print_form() {
		global $pines;
		$pines->com_pgrid->load();
		$module = new module('com_sales', 'form_cashcount_deposit', 'content');
		$module->entity = $this;
		return $module;
	}
}

?>