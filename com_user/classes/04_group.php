<?php
/**
 * group class.
 *
 * @package Pines
 * @subpackage com_user
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

/**
 * Pines system groups.
 *
 * @package Pines
 * @subpackage com_user
 */
class group extends able_entity {
    public function __construct() {
	$this->add_tag('com_user', 'group');
	$this->abilities = array();
    }
}

?>