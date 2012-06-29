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
	 * Creates and attaches a module which lists renditions.
	 * @return module The module.
	 */
	public function list_renditions() {
		global $pines;

		$module = new module('com_mailer', 'rendition/list', 'content');

		$module->renditions = $pines->entity_manager->get_entities(
				array('class' => com_mailer_rendition),
				array('&',
					'tag' => array('com_mailer', 'rendition')
				)
			);

		if ( empty($module->renditions) )
			pines_notice('No renditions found.');

		return $module;
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
	 * @param array|string $mail The mail entry array, or a string representation ("com_example/save_foobar").
	 * @param array $macros An associative array of the macros available for the email. They have to be the same as in the mail definition. Remember to use htmlspecialchars!
	 * @param mixed $recipient A user, customer, employee, etc. that has user info and an email address.
	 * @param bool $send If this is set to false, the com_mailer_mail object is returned before being sent. Allows for adding attachments, etc.
	 * @return bool|com_mailer_mail True on success, false on failure. If $send is false, returns the mail instead.
	 * @todo Unsubscribe code.
	 */
	public function send_mail($mail, $macros = array(), $recipient = null, $send = true) {
		global $pines;
		if ((array) $mail !== $mail) {
			list($component, $defname) = explode('/', $mail, 2);
			$mail = array(
				'component' => $component,
				'mail' => $defname
			);
		}
		$def = $this->get_mail_def($mail);
		if (!$def)
			return false;

		// Format recipient.
		if ($recipient && is_string($recipient))
			$recipient = (object) array('email' => $recipient);

		// Find any renditions.
		$renditions = (array) $pines->entity_manager->get_entities(
				array('class' => com_mailer_rendition),
				array('&',
					'tag' => array('com_mailer', 'rendition'),
					'strict' => array(
						array('enabled', true),
						array('type', "{$mail['component']}/{$mail['mail']}")
					)
				)
			);
		$rendition = null;
		foreach ($renditions as $cur_rendition) {
			if ($cur_rendition->ready()) {
				$rendition = $cur_rendition;
				break;
			}
		}
		unset($renditions, $cur_rendition);

		// Get the email contents.
		$body = array();
		if ($rendition) {
			if (!$recipient) {
				if ($def['has_recipient'])
					return false;
				if ($rendition->to) {
					$user = (array) $pines->entity_manager->get_entities(
							array('class' => user),
							array('&',
								'tag' => array('com_user', 'user'),
								'strict' => array('email', $rendition->to)
							)
						);
					if ($user)
						$recipient = $user;
					else
						$recipient = (object) array('email' => $rendition->to);
				} else {
					if (!$pines->config->com_mailer->master_address)
						return false;
					$recipient = (object) array('email' => $pines->config->com_mailer->master_address);
				}
			}
			$body['subject'] = $rendition->subject;
			$body['content'] = $rendition->content;
		} else {
			if (!$recipient) {
				if ($def['has_recipient'] || !$pines->config->com_mailer->master_address)
					return false;
				$recipient = (object) array('email' => $pines->config->com_mailer->master_address);
			}
			$view = $def['view'];
			$view_callback = $def['view_callback'];
			if (!isset($view) && !isset($view_callback))
				return false;

			if (isset($view))
				$module = new module($component, $view);
			else {
				$module = call_user_func($view_callback, null, null, $options);
				if (!$module)
					return false;
			}

			$body['content'] = $module->render();
			$body['subject'] = $module->title;
		}

		// Get the template.
		$templates = (array) $pines->entity_manager->get_entities(
				array('class' => com_mailer_template),
				array('&',
					'tag' => array('com_mailer', 'template'),
					'strict' => array(
						array('enabled', true)
					)
				)
			);
		$template = null;
		foreach ($templates as $cur_template) {
			if ($cur_template->ready()) {
				$template = $cur_template;
				break;
			}
		}
		unset($templates, $cur_template);
		if (!$template)
			$template = com_mailer_template::factory();

		// Build the body of the email.
		$body['content'] = str_replace('#content#', $body['content'], str_replace('#content#', $template->content, $template->document));

		// Replace macros.
		foreach ($body as &$cur_field) {
			if (strpos($cur_field, '#subject#') !== false)
				$cur_field = str_replace('#subject#', htmlspecialchars($body['subject']), $cur_field);
			// Links
			if (strpos($cur_field, '#site_link#') !== false)
				$cur_field = str_replace('#site_link#', htmlspecialchars($pines->config->full_location), $cur_field);
			if (strpos($cur_field, '#unsubscribe_link#') !== false)
				$cur_field = str_replace('#unsubscribe_link#', htmlspecialchars(pines_url('com_mailer', 'unsubscribe', array('email' => $recipient->email), true)), $cur_field);
			// Recipient
			if (strpos($cur_field, '#to_username#') !== false)
				$cur_field = str_replace('#to_username#', htmlspecialchars($recipient->username), $cur_field);
			if (strpos($cur_field, '#to_name#') !== false)
				$cur_field = str_replace('#to_name#', htmlspecialchars($recipient->name), $cur_field);
			if (strpos($cur_field, '#to_first_name#') !== false)
				$cur_field = str_replace('#to_first_name#', htmlspecialchars($recipient->name_first), $cur_field);
			if (strpos($cur_field, '#to_last_name#') !== false)
				$cur_field = str_replace('#to_last_name#', htmlspecialchars($recipient->name_last), $cur_field);
			if (strpos($cur_field, '#to_email#') !== false)
				$cur_field = str_replace('#to_email#', htmlspecialchars($recipient->email), $cur_field);
			// Current User
			if (strpos($cur_field, '#username#') !== false)
				$cur_field = str_replace('#username#', htmlspecialchars($_SESSION['user']->username), $cur_field);
			if (strpos($cur_field, '#name#') !== false)
				$cur_field = str_replace('#name#', htmlspecialchars($_SESSION['user']->name), $cur_field);
			if (strpos($cur_field, '#first_name#') !== false)
				$cur_field = str_replace('#first_name#', htmlspecialchars($_SESSION['user']->name_first), $cur_field);
			if (strpos($cur_field, '#last_name#') !== false)
				$cur_field = str_replace('#last_name#', htmlspecialchars($_SESSION['user']->name_last), $cur_field);
			if (strpos($cur_field, '#email#') !== false)
				$cur_field = str_replace('#email#', htmlspecialchars($_SESSION['user']->email), $cur_field);
			// Date/Time
			if (strpos($cur_field, '#date_short#') !== false)
				$cur_field = str_replace('#date_short#', htmlspecialchars(format_date(time(), 'date_short')), $cur_field);
			if (strpos($cur_field, '#date_med#') !== false)
				$cur_field = str_replace('#date_med#', htmlspecialchars(format_date(time(), 'date_med')), $cur_field);
			if (strpos($cur_field, '#date_long#') !== false)
				$cur_field = str_replace('#date_long#', htmlspecialchars(format_date(time(), 'date_long')), $cur_field);
			if (strpos($cur_field, '#time_short#') !== false)
				$cur_field = str_replace('#time_short#', htmlspecialchars(format_date(time(), 'time_short')), $cur_field);
			if (strpos($cur_field, '#time_med#') !== false)
				$cur_field = str_replace('#time_med#', htmlspecialchars(format_date(time(), 'time_med')), $cur_field);
			if (strpos($cur_field, '#time_long#') !== false)
				$cur_field = str_replace('#time_long#', htmlspecialchars(format_date(time(), 'time_long')), $cur_field);
			// System
			if (strpos($cur_field, '#system_name#') !== false)
				$cur_field = str_replace('#system_name#', htmlspecialchars($pines->config->system_name), $cur_field);
			if (strpos($cur_field, '#page_title#') !== false)
				$cur_field = str_replace('#page_title#', htmlspecialchars($pines->config->page_title), $cur_field);
			// Definition Macros
			foreach ($def['macros'] as $cur_name => $cur_desc) {
				if (isset($macros[$cur_name]) && strpos($cur_field, "#$cur_name#") !== false)
					$cur_field = str_replace("#$cur_name#", $macros[$cur_name], $cur_field);
			}
		}
		unset($cur_field);

		// Build the mail object.
		$email = new com_mailer_mail($pines->config->com_mailer->from_address, isset($recipient->name) ? "\"".str_replace('"', '', $recipient->name)."\" <{$recipient->email}>" : $recipient->email, $body['subject'], $body['content']);
		if ($rendition) {
			if ($rendition->cc)
				$email->addHeader('CC', $rendition->cc);
			if ($rendition->bcc)
				$email->addHeader('BCC', $rendition->bcc);
		}

		// Now finish up.
		if ($send)
			return $email->send();
		return $email;
	}
}

?>