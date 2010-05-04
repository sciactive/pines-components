<?php
/**
 * com_mailer's information.
 *
 * @package Pines
 * @subpackage com_mailer
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

return array(
	'name' => 'Mailer',
	'author' => 'SciActive',
	'version' => '1.0.0',
	'license' => 'http://www.gnu.org/licenses/agpl-3.0.html',
	'website' => 'http://www.sciactive.com',
	'short_description' => 'Email interface',
	'description' => 'Provides a more object oriented interface for creating emails in Pines. Supports attachments.',
	'depend' => array(
		'pines' => '<2'
	),
);

?>