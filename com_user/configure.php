<?php
defined('D_RUN') or die('Direct access prohibited');

$config->com_user = new DynamicConfig;

// Allows users to have empty passwords.
$config->com_user->empty_pw = true;

class able_entity extends entity {
	// These aren't required.
    /*
    public function &__get($name) {
        return parent::__get($name);
    }

    public function __isset($name) {
        return parent::__isset($name);
    }

    public function __set($name, $value) {
        return parent::__set($name, $value);
    }

    public function __unset($name) {
        return parent::__unset($name);
    }
     */

	/**
	 * Abilities should be named following this form!!
	 *     com_componentname/abilityname
	 * If it is a system ability (ie. not part of a component, substitute
	 * "com_componentname" with "system". The system ability "all" means the
     * user has every ability available.
	 */
    public function grant($ability) {
        if ( !in_array($ability, $this->__get('abilities')) ) {
            return $this->__set('abilities', array_merge(array($ability), $this->__get('abilities')));
        } else {
            return true;
        }
    }

    public function revoke($ability) {
        if ( in_array($ability, $this->__get('abilities')) ) {
			return $this->__set('abilities', array_values(array_diff($this->__get('abilities'), array($ability))));
        } else {
            return true;
        }
    }
}

class user extends able_entity {

}

class group extends able_entity {

}

// Abilities
class abilities {
	public $abilities = array();

	/*
	 * Add a system managed ability. This function is used to let the system
	 * know that you will be using an ability in your component. If you don't
	 * let the system know, you will have to give your users abilities yourself.
	 *
	 * A good way to do this is have the following in your common.php
		if ( isset($config->ability_manager) ) {
			$config->ability_manager->add('com_whatever', 'firstability', 'title', 'description');
			$config->ability_manager->add('com_whatever', 'secondability', 'title', 'description');
		}
	 */
	function add($component, $ability, $title, $description) {
		$this->abilities[] = array('component' => $component, 'ability' => $ability, 'title' => $title, 'description' => $description);
	}

	function get_abilities($component) {
		$abilities_list = array();
		foreach ($this->abilities as $cur_ability) {
			if ( $cur_ability['component'] === $component )
				$abilities_list[] = $cur_ability;
		}
		return $abilities_list;
	}
}

$config->ability_manager = new abilities;

?>