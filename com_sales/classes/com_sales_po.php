<?php
/**
 * com_sales_po class.
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
 * A PO.
 *
 * @package Components\sales
 */
class com_sales_po extends entity {
	/**
	 * Load a PO.
	 * @param int $id The ID of the PO to load, 0 for a new PO.
	 */
	public function __construct($id = 0) {
		parent::__construct();
		$this->add_tag('com_sales', 'po');
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
		$this->products = array();
		$this->finished = false;
		$this->destination = $_SESSION['user']->group;
		$this->ac->other = 2;
	}

	/**
	 * Create a new instance.
	 * @return com_sales_po The new instance.
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
				return "PO $this->po_number";
			case 'type':
				return 'PO';
			case 'types':
				return 'POs';
			case 'url_edit':
				if (gatekeeper('com_sales/editpo'))
					return pines_url('com_sales', 'po/edit', array('id' => $this->guid));
				break;
			case 'url_list':
				if (gatekeeper('com_sales/listpos'))
					return pines_url('com_sales', 'po/list');
				break;
			case 'icon':
				return 'picon-resource-calendar-child';
		}
		return null;
	}

	/**
	 * Return the entity helper module.
	 * @return module Entity helper module.
	 */
	public function helper() {
		return new module('com_sales', 'po/helper');
	}

	/**
	 * Delete the PO.
	 * @return bool True on success, false on failure.
	 */
	public function delete() {
		// Don't delete the PO if it has received items.
		if (!empty($this->received))
			return false;
		if (!parent::delete())
			return false;
		pines_log("Deleted PO $this->po_number.", 'notice');
		return true;
	}

	/**
	 * Save the PO.
	 * @return bool True on success, false on failure.
	 */
	public function save() {
		if (!isset($this->po_number) || !$this->products)
			return false;
		if (!$this->finished) {
			$this->pending = array();
			$this->pending_products = array();
			foreach ($this->products as &$cur_product) {
				$cur_product['received'] = 0;
				// Count how many of this product has been received.
				foreach ((array) $this->received as $cur_received_stock_entity) {
					if (isset($cur_received_stock_entity) && $cur_product['entity']->is($cur_received_stock_entity->product))
						$cur_product['received']++;
				}
				// If we've received all of them, move on.
				if ($cur_product['received'] >= $cur_product['quantity'])
					continue;
				$cur_pending = $cur_product;
				$cur_pending['quantity'] -= $cur_product['received'];
				$this->pending[] = $cur_pending;
				$this->pending_products[] = $cur_product['entity'];
			}
			unset($cur_product);
			if (empty($this->pending_products))
				$this->finished = true;
		}
		return parent::save();
	}

	/**
	 * Email a notification to the destination.
	 * 
	 * @return bool True on success, false on failure. 
	 */
	public function email() {
		global $pines;
		if (empty($this->destination->email))
			return false;
		$module = new module('com_sales', 'po/products_email');
		$module->entity = $this;

		$tracking_links = array();
		if (isset($this->shipper->guid) && $this->shipper->can_track()) {
			foreach ($this->tracking_numbers as $cur_number) {
				$url = htmlspecialchars($this->shipper->tracking_url($cur_number));
				$tracking_links[] = '<a href="'.$url.'" target="_blank">'.$url.'</a>';
			}
		} else {
			foreach ($this->tracking_numbers as $cur_number)
				$tracking_links[] = htmlspecialchars($cur_number);
		}
		$address = '';
		if ($this->destination->address_type == 'us') {
			if (!empty($this->destination->address_1)) {
				$address .= htmlspecialchars($this->destination->address_1.' '.$this->destination->address_2).'<br />';
				$address .= htmlspecialchars($this->destination->city).', '.htmlspecialchars($this->destination->state).' '.htmlspecialchars($this->destination->zip);
			}
		} else
			$address .= str_replace("\n", '<br />', htmlspecialchars($this->destination->address_international));

		$macros = array(
			'products' => $module->render(),
			'po_number' => htmlspecialchars($this->po_number),
			'ref_number' => htmlspecialchars($this->reference_number),
			'vendor' => htmlspecialchars($this->vendor->name),
			'destination' => htmlspecialchars($this->destination->name),
			'shipper' => htmlspecialchars($this->shipper->name),
			'tracking_link' => implode('<br />', $tracking_links),
			'eta' => htmlspecialchars($this->eta ? format_date($this->eta, 'date_long') : ''),
			'address' => $address,
			'comments' => htmlspecialchars($this->comments),
		);
		return $pines->com_mailer->send_mail('com_sales/po_committed', $macros, $this->destination);
	}

	/**
	 * Print a form to edit the PO.
	 * @return module The form's module.
	 */
	public function print_form() {
		global $pines;
		$module = new module('com_sales', 'po/form', 'content');
		$module->entity = $this;
		$module->locations = (array) $pines->user_manager->get_groups();
		$module->shippers = (array) $pines->entity_manager->get_entities(array('class' => com_sales_shipper), array('&', 'tag' => array('com_sales', 'shipper')));
		$module->vendors = (array) $pines->entity_manager->get_entities(array('class' => com_sales_vendor), array('&', 'tag' => array('com_sales', 'vendor')));

		return $module;
	}
}

?>