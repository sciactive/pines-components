<?php
/**
 * com_sales_vendor class.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

/**
 * A vendor.
 *
 * @package Pines
 * @subpackage com_sales
 */
class com_sales_vendor extends entity {
	public function __construct() {
		parent::__construct();
		$this->add_tag('com_sales', 'vendor');
	}

	public function delete() {
		if (!parent::delete())
			return false;
		pines_log("Deleted vendor $this->name.", 'notice');
		return true;
	}

	public function save() {
		if (!isset($this->name))
			return false;
		return parent::save();
	}
}

?>