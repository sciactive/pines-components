<?php
/**
 * com_pinlock's information.
 *
 * @package Components\pinlock
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

return array(
	'name' => 'PIN Locker',
	'author' => 'SciActive',
	'version' => '1.0.1',
	'license' => 'http://www.gnu.org/licenses/agpl-3.0.html',
	'website' => 'http://www.sciactive.com',
	'short_description' => 'PIN based security',
	'description' => 'Provides a PIN based security measure to both prevent unauthorized use of accounts and securely allow users to switch accounts quickly.',
	'depend' => array(
		'pines' => '<3',
		'service' => 'user_manager',
		'component' => 'com_pform'
	),
);

?>