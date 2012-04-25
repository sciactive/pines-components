<?php
/**
 * com_esp_plan class.
 *
 * @package Components
 * @subpackage esp
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

/**
 * An extended service plan.
 *
 * @package Components
 * @subpackage esp
 */
class com_esp_plan extends entity {
	/**
	 * Load an ESP.
	 * @param int $id The ID of the esp to load, 0 for a new esp.
	 */
	public function __construct($id = 0) {
		parent::__construct();
		$this->add_tag('com_esp', 'esp');
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
		// Defaults
		$this->history = array();
		$this->status = 'pending';
		$this->disposed = 'pending';
	}

	/**
	 * Create a new instance.
	 * @return com_esp_plan The new instance.
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
	 * Delete the esp.
	 * @return bool True on success, false on failure.
	 */
	public function delete() {
		if (!parent::delete())
			return false;
		pines_log("Deleted ESP {$this->customer->name}.", 'notice');
		return true;
	}

	/**
	 * Print a form to dispose the esp.
	 * @return module The form's module.
	 */
	public function dispose_form() {
		$module = new module('com_esp', 'form_dispose', 'content');
		$module->entity = $this;
		return $module;
	}

	/**
	 * Print a histroy report for the esp.
	 * @return module The report module.
	 */
	public function history() {
		$module = new module('com_esp', 'view_history', 'content');
		$module->entity = $this;
		return $module;
	}

	/**
	 * Save the esp.
	 * @return bool True on success, false on failure.
	 */
	public function save() {
		if (!isset($this->customer->guid))
			return false;
		global $pines;
		if (!isset($this->id))
			$this->id = $pines->entity_manager->new_uid('com_esp_plan_id');
		return parent::save();
	}

	/**
	 * Swap an item on the ESP.
	 * @param string $new_serial The serial number of the new item.
	 * @return bool True on success, false on failure.
	 * TODO: Make sure that it handles the stock entity swapping correctly.
	 */
	public function swap($new_serial = null) {
		global $pines;

		// Return the old stock item to inventory.
		if ($this->item['serial'] == $old_serial && $cur_product['sku'] == $sku) {
			if ($this->item['entity']->serialized && empty($new_serial)) {
				pines_notice("This product requires a serial.");
				return false;
			}
			if (!is_array($this->item['stock_entities'])) {
				pines_notice('This item cannot be swapped, because it was not found.');
				return false;
			}
			// See if the new item is in stock.
			$selector = array('&',
				'tag' => array('com_sales', 'stock'),
				'data' => array(
					array('serial', $new_serial)
				),
				'ref' => array(
					array('product', $this->item['entity']),
					array('location', $this->group)
				)
			);
			$new_stock = $pines->entity_manager->get_entity(array('class' => com_sales_stock), $selector);
			if (isset($new_stock)) {
				// Remove the item from inventory.
				$new_product = $this->item;
				$new_product['serial'] = $new_serial;
				$new_product['stock_entities'] = array($new_stock);
			} else {
				pines_notice("Product with SKU [{$this->item['sku']}]".($this->item['entity']->serialized ? " and serial [$new_serial]" : " and quantity {$this->item['quantity']}")." is not in local stock.");
				return false;
			}
			$this->item = $new_product;
			if (!$this->save()) {
				pines_notice('Could not save the ESP after swapping.');
				return false;
			}
			return true;
		}
	}

	/**
	 * Print a form to swap items.
	 *
	 * Uses a page override to only print the form.
	 *
	 * @return module The form's module.
	 */
	public function swap_form() {
		global $pines;
		$pines->page->override = true;

		$module = new module('com_esp', 'form_swap', 'content');
		$module->entity = $this;

		$pines->page->override_doc($module->render());
		return $module;
	}
}

?>