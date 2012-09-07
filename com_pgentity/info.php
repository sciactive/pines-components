<?php
/**
 * com_pgentity's information.
 *
 * @package Components\pgentity
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

return array(
	'name' => 'PostgreSQL Entity Manager',
	'author' => 'SciActive',
	'version' => '1.1.0beta2',
	'license' => 'http://www.gnu.org/licenses/agpl-3.0.html',
	'website' => 'http://www.sciactive.com',
	'services' => array('entity_manager'),
	'short_description' => 'PostgreSQL based entity manager',
	'description' => 'Provides an object relational mapper, which conforms to the Pines entity manager service standard and uses PostgreSQL as its backend.',
	'depend' => array(
		'pines' => '<2',
		'component' => 'com_pgsql'
	),
);

?>