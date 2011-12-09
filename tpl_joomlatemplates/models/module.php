<?php
/**
 * Template for a module.
 *
 * @package Pines
 * @subpackage tpl_joomlatemplates
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

// We need to use the Joomla! templates chrome functions to layout this module.
// First we need the current options.
$options = $pines->template->cur_module_options;

// Now let's find the chrome function to use.
$suffix = 'none';
if (!empty($options['style']) && function_exists("modChrome_{$options['style']}"))
	$suffix = $options['style'];

// Now we need to make fake objects to pass to the function.
$module = new jmodule();
$module->position = empty($options['name']) ? $this->position : $options['name'];
$module->showtitle = $this->show_title;
$module->style = $options['style'];
switch ($pines->config->tpl_joomlatemplates->module_notes) {
	case 'title':
		$module->title = $this->title;
		if (!empty($this->note))
			$module->title .= "<br />\n<small class=\"module_note\">$this->note</small>";
		$module->content = $this->content;
		break;
	case 'content':
		$module->title = $this->title;
		$module->content = $this->content;
		if (!empty($this->note))
			$module->content = "<div class=\"module_note\"><small>$this->note</small></div><br />\n$module->content";
		break;
	case 'ignore':
		$module->title = $this->title;
		$module->content = $this->content;
		break;
}

$params = new jmodule_params();
$params->classes = htmlspecialchars($this->classes);

// Fix breadcrumbs to look like Joomla!'s breadcrumbs layout.
if ($this->position == 'breadcrumbs')
	$module->content = "<span class=\"breadcrumbs pathway\">$module->content</span>";

// Now call the Joomla! chrome function. This prints the output.
call_user_func_array("modChrome_{$suffix}", array($module, &$params, &$options));

?>