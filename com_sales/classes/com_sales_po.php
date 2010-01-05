<?php
/**
 * com_sales_po class.
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
 * A PO.
 *
 * @package Pines
 * @subpackage com_sales
 */
class com_sales_po extends entity {
	public function __construct() {
		parent::__construct();
		$this->add_tag('com_sales', 'po');
	}

    public function delete() {
        // Don't delete the PO if it has received items.
        if (!empty($this->received))
            return false;
		if (!parent::delete())
            return false;
        pines_log("Deleted PO $this->po_number.", 'notice');
        return true;
    }

    public function save() {
        if (!isset($this->po_number))
            return false;
		return parent::save();
    }
}

?>