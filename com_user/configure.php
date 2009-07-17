<?php
/**
 * com_user's configuration.
 *
 * @package Dandelion
 * @subpackage com_user
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('D_RUN') or die('Direct access prohibited');

$config->com_user = new DynamicConfig;

// Allows users to have empty passwords.
$config->com_user->empty_pw = true;

/**
 * Entities which support abilities, such as users and groups.
 *
 * @package Dandelion
 * @subpackage com_user
 */
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
     * Grant an ability to a user.
     *
	 * Abilities should be named following this form!!
     *
	 *     com_componentname/abilityname
     *
	 * If it is a system ability (ie. not part of a component, substitute
	 * "com_componentname" with "system". The system ability "all" means the
     * user has every ability available.
     *
     * @param string $ability The ability.
	 */
    public function grant($ability) {
        if ( !in_array($ability, $this->__get('abilities')) ) {
            return $this->__set('abilities', array_merge(array($ability), $this->__get('abilities')));
        } else {
            return true;
        }
    }

    /**
     * Revoke an ability from a user.
     *
     * @param string $ability The ability.
     */
    public function revoke($ability) {
        if ( in_array($ability, $this->__get('abilities')) ) {
			return $this->__set('abilities', array_values(array_diff($this->__get('abilities'), array($ability))));
        } else {
            return true;
        }
    }
}

/**
 * Dandelion system users.
 *
 * @package Dandelion
 * @subpackage com_user
 */
class user extends able_entity {
    /**
     * Add the user to a group.
     *
     * @param string $group The GUID of the group.
     * @return mixed True if the user is already in the group. The resulting array of group IDs if the user was not.
     */
    public function addgroup($group) {
        if ( !in_array($group, $this->__get('groups')) ) {
            return $this->__set('groups', array_merge(array($group), $this->__get('groups')));
        } else {
            return true;
        }
    }

    /**
     * Remove the user from a group.
     *
     * @param string $group The GUID of the group.
     * @return mixed True if the user wasn't in the group. The resulting array of group IDs if the user was.
     */
    public function delgroup($group) {
        if ( in_array($group, $this->__get('groups')) ) {
			return $this->__set('groups', array_values(array_diff($this->__get('groups'), array($group))));
        } else {
            return true;
        }
    }


    /**
     * Check whether the user is in a group.
     *
     * @param string $group The GUID of the group.
     * @return bool
     */
    public function ingroup($group) {
        return in_array($group, $this->__get('groups'));
    }

    /**
     * Change the user's password.
     *
     * @param string $password The new password.
     * @return string The resulting MD5 sum which is stored in the entity.
     */
	public function password($password) {
		return $this->__set('password', md5($password.$this->__get('salt')));
	}

    /**
     * Check if the password given is the correct password for the user's
     * account.
     *
     * @param string $password The password in question.
     * @return bool
     */
    public function check_password($password) {
		return ($this->__get('password') == md5($password.$this->__get('salt')));
    }
}

/**
 * Dandelion system groups.
 *
 * @todo Function users() to list users of this group.
 * @package Dandelion
 * @subpackage com_user
 */
class group extends able_entity {
    
}

/**
 * A generic ability manager.
 *
 * @package Dandelion
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

$config->ability_manager = new abilities;

?>