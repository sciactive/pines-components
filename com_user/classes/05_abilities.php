<?php
/**
 * abilities class.
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
 * A generic ability manager.
 *
 * @package Pines
 * @subpackage com_user
 */
class abilities {
	public $abilities = array();

	/**
	 * Add a system managed ability.
     *
     * This function is used to let the system know that you will be using an
     * ability in your component. If you don't let the system know, you will
     * have to give your users abilities yourself.
	 *
	 * A good way to do this is have the following in your common.php
     * if ( isset($config->ability_manager) ) {
     * 	$config->ability_manager->add('com_whatever', 'firstability', 'title', 'description');
     * 	$config->ability_manager->add('com_whatever', 'secondability', 'title', 'description');
     * }
     *
     * @param string $component The component under which to place the ability.
     * @param string $ability The name of the ability to manage.
     * @param string $title A descriptive title to display to the user.
     * @param string $description A description of the ability to display to the user.
	 */
	function add($component, $ability, $title, $description) {
		$this->abilities[] = array('component' => $component, 'ability' => $ability, 'title' => $title, 'description' => $description);
	}

    /**
     * Get an array of the abilities that a specified component has reported
     * that it uses.
     *
     * @param string $component The component.
     * @return array The array of abilities.
     */
	function get_abilities($component) {
		$abilities_list = array();
		foreach ($this->abilities as $cur_ability) {
			if ( $cur_ability['component'] === $component )
				$abilities_list[] = $cur_ability;
		}
		return $abilities_list;
	}
}

?>