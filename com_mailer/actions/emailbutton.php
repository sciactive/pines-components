<?php
/**
 * Generate the Email Button
 *
 * @package Components\mailer
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Angela Murrell <amasiell.g@gmail.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_mailer/sendtemplateemail') )
	punt_user(null, pines_url());

$pines->page->override = true;
$module = new module('com_mailer', 'emailbutton', 'content');
$module->email_templates = $pines->com_mailer->get_email_templates();
$module->email_suffix = '@'.$pines->config->com_mailer->email_templates_domain;
$module->email_prefix = $pines->config->com_mailer->email_templates_prefix_group ? preg_replace('#@.*$#', '', $_SESSION['user']->group->email) : (!empty($pines->config->com_mailer->email_templates_prefix_default) ? $pines->config->com_mailer->email_templates_prefix_default : preg_replace('#@.*$#', '', $_SESSION['user']->email));
$module->edit_email = (gatekeeper('com_mailer/editsendtemplateemail')) ? true : false;
$pines->page->override_doc($module->render());

?>