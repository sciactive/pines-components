<?php
/**
 * com_mailer_template class.
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
 * A template.
 *
 * @package Components\mailer
 */
class com_mailer_template extends entity {
	/**
	 * Load a template.
	 * @param int $id The ID of the template to load, 0 for a new template.
	 */
	public function __construct($id = 0) {
		parent::__construct();
		$this->add_tag('com_mailer', 'template');
		if ($id > 0) {
			global $pines;
			$entity = $pines->entity_manager->get_entity(array('class' => get_class($this)), array('&', 'guid' => $id, 'tag' => $this->tags));
			if (isset($entity)) {
				$this->guid = $entity->guid;
				$this->tags = $entity->tags;
				$this->put_data($entity->get_data(), $entity->get_sdata());
				return;
			}
		}
		// Defaults.
		global $pines;
		$this->enabled = true;
		$this->conditions = array();
		$this->replacements = array();
		$full_location = htmlspecialchars($pines->config->full_location);
		$this->content = <<<EOF
<div style="font-family: 'Helvetica Neue',Helvetica,Arial,sans-serif; color: #3A3A3A;">
	<table width="100%" cellpadding="0" cellspacing="0" bgcolor="#ffffff" align="center" border="0">
		<tr><td valign="top" style="color:#000; font-size:20px; font-weight:bold; text-align: left; line-height:17px; background-repeat:repeat-x; background-position:top left; background-color: #5baa45;" background="{$full_location}templates/tpl_pinescms/images/pines-navbar-bg.png">
			<div align="left" style="background: url({$full_location}templates/tpl_pinescms/images/pines-navbar-bg.png) repeat-x top left;">
				<table class="table" width="600" cellpadding="0" cellspacing="0" align="center" border="0"><tr><td valign="top" style="text-align: left;">
					<div align="left" style="padding-top: 7px; padding-bottom: 9px"><a href="#site_link#"><img src="{$full_location}templates/tpl_pinescms/images/default_nav_logo.png" alt="#system_name#" style="display:block; color:#000; font-size:20px; font-weight:bold; line-height:17px; height:17px; text-align: left;" height="17" title="#system_name#" border="0"/></a></div>
				</td></tr></table>
			</div>
		</td></tr>
	</table>
	<br />
	<table class="table" width="600" cellpadding="0" cellspacing="0" bgcolor="#ffffff" align="center" border="0">
		<tr><td valign="top" style="color:#3A3A3A">#content#</td></tr>
	</table>
	<br />
	<br />
	<table class="table" width="600" cellpadding="8" cellspacing="0" bgcolor="#D8D8D8" align="center" border="0">
		<tr><td valign="top" background="{$full_location}templates/tpl_pinescms/images/footer-bg.png" style="color:#3A3A3A; font-size:14px; background-color: #D8D8D8; text-align:center; line-height:20px">You received this email because you have an account at <a href="#site_link#">#system_name#</a>.<br />If you no longer wish to receive emails from us, you can <a href="#unsubscribe_link#">unsubscribe</a>.</td></tr>
	</table>
</div>
EOF;
		$this->document = <<<'EOF'
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title>#subject#</title>
	<style type="text/css">
		.ExternalClass {width:100%;}
		.ExternalClass, .ExternalClass p, .ExternalClass span, .ExternalClass font, .ExternalClass td, .ExternalClass div {
			line-height: 100%;}
		body {-webkit-text-size-adjust:none; -ms-text-size-adjust:none;}
		body {margin:0; padding:0;}
		table td {border-collapse:collapse;}
		h1, h2, h3, h4, h5, h6 {color: black; line-height: 100%;}
		a, a:link {color:#2A5DB0; text-decoration: underline;}
		@media only screen and (max-device-width: 480px) {
			body[style] .table {width:320px;}
		}
	</style>
</head>
<body style="font-family: 'Helvetica Neue',Helvetica,Arial,sans-serif; color: #3A3A3A;">
#content#
</body>
</html>
EOF;
		$this->ac->other = 1;
	}

	/**
	 * Create a new instance.
	 * @return com_mailer_template The new instance.
	 */
	public static function factory() {
		global $pines;
		$class = get_class();
		$args = func_get_args();
		$entity = new $class($args[0]);
		$pines->hook->hook_object($entity, $class.'->', false);
		return $entity;
	}

	public function info($type) {
		switch ($type) {
			case 'name':
				return $this->name;
			case 'type':
				return 'template';
			case 'types':
				return 'templates';
			case 'url_edit':
				if (gatekeeper('com_mailer/edittemplate'))
					return pines_url('com_mailer', 'template/edit', array('id' => $this->guid));
				break;
			case 'url_list':
				if (gatekeeper('com_mailer/listtemplates'))
					return pines_url('com_mailer', 'template/list');
				break;
			case 'icon':
				return 'picon-internet-mail';
		}
		return null;
	}

	/**
	 * Delete the template.
	 * @return bool True on success, false on failure.
	 */
	public function delete() {
		if (!parent::delete())
			return false;
		pines_log("Deleted template $this->name.", 'notice');
		return true;
	}

	/**
	 * Save the template.
	 * @return bool True on success, false on failure.
	 */
	public function save() {
		if (!isset($this->name))
			return false;
		return parent::save();
	}

	/**
	 * Print a form to edit the template.
	 * @return module The form's module.
	 */
	public function print_form() {
		$module = new module('com_mailer', 'template/form', 'content');
		$module->entity = $this;

		return $module;
	}

	/**
	 * Determine if this template is ready to use.
	 *
	 * This function will check the conditions of the template.
	 *
	 * @return bool True if the template is ready, false otherwise.
	 */
	public function ready() {
		if (!$this->enabled)
			return false;
		if (!$this->conditions)
			return true;
		global $pines;
		// Check that all conditions are met.
		foreach ($this->conditions as $cur_type => $cur_value) {
			if (!$pines->depend->check($cur_type, $cur_value))
				return false;
		}
		return true;
	}
}

?>