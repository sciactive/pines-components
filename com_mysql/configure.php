<?php
/**
 * com_mysql's WDDX configuration.
 *
 * @package Pines
 * @subpackage com_mysql
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

return array (
  0 => 
  array (
    'name' => 'host',
    'cname' => 'Host',
    'description' => 'The default MySQL host.',
    'value' => 'localhost',
  ),
  1 => 
  array (
    'name' => 'user',
    'cname' => 'User',
    'description' => 'The default MySQL user.',
    'value' => 'pinese',
  ),
  2 => 
  array (
    'name' => 'password',
    'cname' => 'Password',
    'description' => 'The default MySQL password.',
    'value' => 'password',
  ),
  3 => 
  array (
    'name' => 'database',
    'cname' => 'Database',
    'description' => 'The default MySQL database.',
    'value' => 'pines',
  ),
  4 => 
  array (
    'name' => 'prefix',
    'cname' => 'Table Prefix',
    'description' => 'The default MySQL table name prefix.',
    'value' => 'pin_',
  ),
);

?>