<?php
/**
 * com_myentity's information.
 *
 * @package Pines
 * @subpackage com_myentity
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

return array(
	'name' => 'Entity Manager (MySQL)',
	'author' => 'SciActive',
	'version' => '1.0.0',
	'license' => 'http://www.gnu.org/licenses/agpl-3.0.html',
	'services' => array('entity_manager'),
	'short_description' => 'MySQL based entity manager',
	'description' => 'Provides an object relational mapper, which conforms to the Pines entity manager service standard and uses MySQL as its backend.',
);

?>