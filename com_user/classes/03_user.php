<?php
/**
 * user class.
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
 * Pines system users.
 *
 * @package Pines
 * @subpackage com_user
 */
class user extends able_entity {
    public function __construct() {
		$this->add_tag('com_user', 'user');
		$this->abilities = array();
		$this->groups = array();
        $this->inherit_abilities = true;
        $this->default_component = 'com_user';
    }

    /**
     * Add the user to a group.
     *
     * @param int $group_id The GUID of the group.
     * @return mixed True if the user is already in the group. The resulting array of group IDs if the user was not.
     */
    public function addgroup($group_id) {
        if ( !in_array($group_id, $this->__get('groups')) ) {
            return $this->__set('groups', array_merge(array($group_id), $this->__get('groups')));
        } else {
            return true;
        }
    }

    /**
     * Remove the user from a group.
     *
     * @param int $group_id The GUID of the group.
     * @return mixed True if the user wasn't in the group. The resulting array of group IDs if the user was.
     */
    public function delgroup($group_id) {
        if ( in_array($group_id, $this->__get('groups')) ) {
			return $this->__set('groups', array_values(array_diff($this->__get('groups'), array($group_id))));
        } else {
            return true;
        }
    }

    /**
     * Check whether the user is in a group.
     *
     * @param int $group_id The GUID of the group.
     * @return bool
     */
    public function ingroup($group_id) {
        return in_array($group_id, $this->__get('groups'));
    }

    /**
     * Change the user's password.
     *
     * This function first checks to see if the user already has a salt. If not,
     * one will be generated.
     *
     * @param string $password The new password.
     * @return string The resulting MD5 sum which is stored in the entity.
     */
	public function password($password) {
		if (!isset($this->salt)) $this->salt = md5(rand());
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

?>