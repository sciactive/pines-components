<?php
/**
 * com_fortune class.
 *
 * @package Pines
 * @subpackage com_fortune
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

/**
 * com_fortune main class.
 *
 * @package Pines
 * @subpackage com_fortune
 */
class com_fortune extends component {
	public function print_fortune() {
		global $pines;
		$module = new module('com_fortune', 'fortune', $pines->config->com_fortune->position);
		$module->fortune = $this->get_fortune();
	}

	public function get_fortune() {
		global $pines;
		$databases = $pines->config->com_fortune->databases;
	}
}

?>