<?php
/**
 * com_mailer class.
 *
 * @package Components\mailer
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

/**
 * com_mailer main class.
 *
 * @package Components\mailer
 */
class com_mailer extends component {
	/**
	 * Creates and attaches a module which lists templates.
	 * @return module The module.
	 */
	public function list_templates() {
		global $pines;

		$module = new module('com_mailer', 'template/list', 'content');

		$module->templates = $pines->entity_manager->get_entities(
				array('class' => com_mailer_template),
				array('&',
					'tag' => array('com_mailer', 'template')
				)
			);

		if ( empty($module->templates) )
			pines_notice('No templates found.');

		return $module;
	}
}

?>