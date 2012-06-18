<?php
/**
 * List templates.
 *
 * @package Components\mailer
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_mailer/listtemplates') )
	punt_user(null, pines_url('com_mailer', 'template/list'));

$pines->com_mailer->list_templates();
?>