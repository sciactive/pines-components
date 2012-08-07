<?php
/**
 * com_sales_shipment class.
 *
 * @package Components\sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

/**
 * A shipment.
 *
 * @package Components\sales
 */
class com_sales_shipment extends entity {
	/**
	 * Load a shipment.
	 * @param int $id The ID of the shipment to load, 0 for a new shipment.
	 */
	public function __construct($id = 0) {
		parent::__construct();
		$this->add_tag('com_sales', 'shipment');
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
		$this->shipped = false;
		$this->delivered = false;
		$this->products = array();
	}

	/**
	 * Create a new instance.
	 * @return com_sales_shipment The new instance.
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
				return "Shipment $this->id";
			case 'type':
				return 'shipment';
			case 'types':
				return 'shipments';
			case 'url_edit':
				if (gatekeeper('com_sales/editshipment'))
					return pines_url('com_sales', 'shipment/edit', array('id' => $this->guid));
				break;
			case 'url_list':
				if (gatekeeper('com_sales/listshipments'))
					return pines_url('com_sales', 'shipment/list');
				break;
			case 'icon':
				return 'picon-mail-send';
		}
		return null;
	}

	/**
	 * Delete the shipment.
	 * @return bool True on success, false on failure.
	 */
	public function delete() {
		if (!parent::delete())
			return false;
		pines_log("Deleted shipment $this->id.", 'notice');
		return true;
	}

	/**
	 * Save the shipment.
	 * @return bool True on success, false on failure.
	 */
	public function save() {
		global $pines;
		if (!isset($this->id))
			$this->id = $pines->entity_manager->new_uid('com_sales_shipment');
		return parent::save();
	}

	/**
	 * Email a notification to the customer.
	 * 
	 * @return bool True on success, false on failure. 
	 */
	public function email() {
		global $pines;
		if (empty($this->ref->customer->email))
			return false;
		$module = new module('com_sales', 'shipment/packing_list_email');
		$module->entity = $this;

		$tracking_links = array();
		foreach ($this->tracking_numbers as $cur_number) {
			$url = htmlspecialchars($this->shipper->tracking_url($cur_number));
			$tracking_links[] = '<a href="'.$url.'" target="_blank">'.$url.'</a>';
		}
		$address = '<strong>'.htmlspecialchars($this->shipping_address->name).'</strong><br />';
		if ($this->shipping_address->address_type == 'us') {
			if (!empty($this->shipping_address->address_1)) {
				$address .= htmlspecialchars($this->shipping_address->address_1.' '.$this->shipping_address->address_2).'<br />';
				$address .= htmlspecialchars($this->shipping_address->city).', '.htmlspecialchars($this->shipping_address->state).' '.htmlspecialchars($this->shipping_address->zip);
			}
		} else
			$address .= str_replace("\n", '<br />', htmlspecialchars($this->shipping_address->address_international));

		$macros = array(
			'packing_list' => $module->render(),
			'sale_id' => htmlspecialchars($this->ref->id),
			'sale_total' => htmlspecialchars($this->ref->total),
			'shipper' => htmlspecialchars($this->shipper->name),
			'tracking_link' => implode('<br />', $tracking_links),
			'eta' => htmlspecialchars(format_date($this->eta, 'date_long')),
			'address' => $address,
		);
		return $pines->com_mailer->send_mail($this->tracking_numbers ? 'com_sales/sale_shipped_tracking' : 'com_sales/sale_shipped', $macros, $this->ref->customer);
	}

	/**
	 * Load the remaining products from a sale into this shipment.
	 * @param com_sales_sale $sale The sale.
	 * @return bool True on success, false on failure.
	 */
	public function load_sale($sale) {
		if (isset($this->ref))
			return false;

		if ($sale->status != 'invoiced' && $sale->status != 'paid') {
			pines_notice('Requested sale has not been invoiced.');
			return false;
		}

		$this->ref = $sale;
		$this->group = $sale->group;

		foreach ($sale->products as $key => $cur_product) {
			if (!in_array($cur_product['delivery'], array('shipped', 'warehouse')))
				continue;
			// Calculate included stock entries.
			$stock_entries = $cur_product['stock_entities'];
			$shipped_stock_entries = (array) $cur_product['shipped_entities'];
			foreach ((array) $cur_product['returned_stock_entities'] as $cur_stock_entity) {
				$i = $cur_stock_entity->array_search($stock_entries);
				if (isset($i))
					unset($stock_entries[$i]);
				// If it's still in there, it was entered on the sale twice (fulfilled after returned once), so don't remove it from shipped.
				if (!$cur_stock_entity->in_array($stock_entries)) {
					$i = $cur_stock_entity->array_search($shipped_stock_entries);
					if (isset($i))
						unset($shipped_stock_entries[$i]);
				}
			}
			// Is the product already shipped?
			if (count($shipped_stock_entries) >= count($stock_entries))
				continue;
			$product_entry = array(
				'ref' => $sale,
				'key' => $key,
				'entity' => $cur_product['entity'],
				'stock_entities' => array()
			);
			foreach ($stock_entries as $cur_stock) {
				if ($cur_stock->in_array($shipped_stock_entries))
					continue;
				$product_entry['stock_entities'][] = $cur_stock;
			}
			if (!$product_entry['stock_entities'])
				continue;
			$this->products[] = $product_entry;
		}

		if (!$this->products) {
			unset($this->ref);
			return false;
		}

		$this->shipping_address = $sale->shipping_address;
		return true;
	}

	/**
	 * Print a form to edit the shipment.
	 * @return module The form's module.
	 */
	public function print_form() {
		global $pines;
		//$module = new module('com_sales', 'shipment/form', 'content');
		$module = new module('com_sales', 'shipment/ship', 'content');
		$module->shippers = (array) $pines->entity_manager->get_entities(array('class' => com_sales_shipper), array('&', 'tag' => array('com_sales', 'shipper')));
		$module->entity = $this;

		return $module;
	}

	/**
	 * Print a packing list for the shipment.
	 * @return module The module.
	 */
	public function print_packing_list() {
		$module = new module('com_sales', 'shipment/packing_list', 'content');
		$module->entity = $this;
		$module = new module('com_sales', 'shipment/actions', 'right');
		$module->entity = $this;

		return $module;
	}

	/**
	 * Remove stock from inventory.
	 * @return bool True on success, false on failure.
	 */
	public function remove_stock() {
		// Keep track of the whole process.
		$no_errors = true;
		// Remove the stock from inventory.
		if ($this->ref->has_tag('sale')) {
			// Go through each product on the packing list, marking its stock as shipped.
			foreach ($this->products as $cur_product) {
				$key = $cur_product['key'];
				if (!isset($this->ref->products[$key]) || !in_array($this->ref->products[$key]['delivery'], array('shipped', 'warehouse')) || !is_array($this->ref->products[$key]['stock_entities'])) {
					$no_errors = false;
					continue;
				}
				if (!is_array($this->ref->products[$key]['shipped_entities']))
					$this->ref->products[$key]['shipped_entities'] = array();
				foreach ($cur_product['stock_entities'] as $cur_stock) {
					$stock_key = $cur_stock->array_search($this->ref->products[$key]['stock_entities']);
					if ($stock_key === false || !isset($this->ref->products[$key]['stock_entities'][$stock_key])) {
						$no_errors = false;
						continue;
					}
					// If the stock is already shipped, skip it.
					if ($this->ref->products[$key]['stock_entities'][$stock_key]->in_array($this->ref->products[$key]['shipped_entities']))
						continue;
					// Remove inventory and save stock entity.
					if ($this->ref->products[$key]['stock_entities'][$stock_key]->remove('sale_shipped', $this) && $this->ref->products[$key]['stock_entities'][$stock_key]->save())
						$this->ref->products[$key]['shipped_entities'][] = $this->ref->products[$key]['stock_entities'][$stock_key];
					else
						$no_errors = false;
				}
			}
		}
		$this->shipped = true;
		return $no_errors && $this->ref->save();
	}
}

?>
