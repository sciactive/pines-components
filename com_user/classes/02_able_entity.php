<?php
/**
 * able_entity class.
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
 * Entities which support abilities, such as users and groups.
 *
 * @package Pines
 * @subpackage com_user
 */
class able_entity extends entity {
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

?>