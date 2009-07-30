<?php
/**
 * com_user's WDDX configuration.
 *
 * @package Pines
 * @subpackage com_user
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

return array (
  0 => 
  array (
    'name' => 'empty_pw',
    'cname' => 'Empty Passwords',
    'description' => 'Allow users to have empty passwords.',
    'value' => false,
  ),
  1 => 
  array (
    'name' => 'create_admin',
    'cname' => 'Create Admin',
    'description' => 'Allow the creation of an admin user.',
    'value' => true,
  ),
  2 => 
  array (
    'name' => 'create_admin_secret',
    'cname' => 'Create Admin Secret',
    'description' => 'The secret necessary to create an admin user.',
    'value' => '874jdiv8',
  ),
);

?>