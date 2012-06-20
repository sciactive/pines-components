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
	 * A cache of the included mails.php files.
	 * @var array
	 * @access private
	 */
	private $mail_include_cache = array();

	/**
	 * Get a mail's definition array.
	 * @param array $mail The mail entry array.
	 * @return array The mail definition.
	 */
	public function get_mail_def($mail) {
		$component = clean_filename($mail['component']);
		$name = $mail['mail'];
		if (isset($this->mail_include_cache[$component])) {
			$include = $this->mail_include_cache[$component];
		} else {
			if (!file_exists("components/$component/mails.php"))
				return null;
			$include = include("components/$component/mails.php");
			$this->mail_include_cache[$component] = $include;
		}
		return $include[$name];
	}

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

	/**
	 * Get an array of all the mail types.
	 * 
	 * Goes through each component's mails.php file.
	 *
	 * @return array Mail types.
	 */
	public function mail_types() {
		global $pines;
		$return = array();
		foreach ($pines->components as $cur_component) {
			if (strpos($cur_component, 'tpl_') === 0)
				continue;
			if (!file_exists("components/$cur_component/mails.php"))
				continue;
			$mails = include("components/$cur_component/mails.php");
			if (!$mails || (array) $mails !== $mails)
				continue;
			if ($mails)
				$return[$cur_component] = $mails;
		}
		return $return;
	}

	/**
	 * Send a system registered email.
	 * @param array $mail The mail entry array.
	 * @param array $macros An associative array of the macros available for the email.
	 * @param mixed $recipient A user, customer, employee, etc. that has user info and an email address.
	 * @return bool True on success, false on failure.
	 */
	public function send_mail($mail, $macros, $recipient = null) {
		$def = $this->get_mail_def($mail);
		// TODO: Finish this.
	}
}

?>