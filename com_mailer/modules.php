<?php
/**
 * The definition of the modules for com_mailer.
 *
 * @package Components\mailer
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Angela Murrell <amasiell.g@gmail.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');

return array(
	'emailbutton' => array(
		'cname' => 'Email Button',
		'description' => 'Email Button for Grids.',
		'view' => 'modules/emailbutton',
		'type' => 'imodule module',
	),
);