<?php
/**
 * com_sales_employee_data class.
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
 * Data for an employee.
 *
 * @package Pines
 * @subpackage com_sales
 * @todo Clean up this extra database info after a user is deleted.
 */
class com_sales_employee_data extends entity {
	public function __construct() {
		parent::__construct();
		$this->add_tag('com_sales', 'employee_data');
	}
}

?>