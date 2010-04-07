<?php
/**
 * com_user_abilities class.
 *
 * @package Pines
 * @subpackage com_user
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

/**
 * A generic ability manager.
 *
 * com_user uses this ability manager to build a list of ablities for the user
 * to select from when editing a user or group.
 *
 * @package Pines
 * @subpackage com_user
 */
class com_user_abilities extends p_base implements ability_manager_interface {
	/**
	 * Array of defined abilities.
	 * @var array $abilities
	 */
	public $abilities = array();

	public function add($component, $ability, $title, $description) {
		$this->abilities[] = array('component' => $component, 'ability' => $ability, 'title' => $title, 'description' => $description);
	}

	public function get_abilities($component) {
		$abilities_list = array();
		foreach ($this->abilities as $cur_ability) {
			if ( $cur_ability['component'] === $component )
				$abilities_list[] = $cur_ability;
		}
		return $abilities_list;
	}
}

?>