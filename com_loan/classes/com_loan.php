<?php
/**
 * com_loan class.
 *
 * @package Components
 * @subpackage loan
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Angela Murrell <angela@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

/**
 * com_loan main class.
 *
 * @package Components
 * @subpackage loan
 */
class com_loan extends component {
	/**
	 * Creates and attaches a module which lists loans.
	 * @return module The module.
	 */
	public function list_loans() {
		global $pines;

		$module = new module('com_loan', 'loan/list', 'content');

		$module->loans = $pines->entity_manager->get_entities(
				array('class' => com_loan_loan),
				array('&',
					'tag' => array('com_loan', 'loan')
				)
			);

		if ( empty($module->loans) )
			pines_notice('No loans found.');

		return $module;
	}

}

?>