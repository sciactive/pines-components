<?php
/**
 * able_object class.
 *
 * @package Components
 * @subpackage user
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

/**
 * Entities which support abilities, such as users and groups.
 *
 * @package Components
 * @subpackage user
 */
class able_object extends entity implements able_object_interface {
	public function grant($ability) {
		if ( !in_array($ability, $this->abilities) ) {
			return $this->abilities = array_merge(array($ability), $this->abilities);
		} else {
			return true;
		}
	}

	public function revoke($ability) {
		if ( in_array($ability, $this->abilities) ) {
			return $this->abilities = array_values(array_diff($this->abilities, array($ability)));
		} else {
			return true;
		}
	}
}

?>