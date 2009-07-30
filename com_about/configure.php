<?php
/**
 * com_about's WDDX configuration.
 *
 * @package Pines
 * @subpackage com_about
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

return array (
  0 => 
  array (
    'name' => 'description',
    'cname' => 'Description',
    'description' => 'Description of your installation.',
    'value' => 'This is the default installation of Pines.',
  ),
  1 => 
  array (
    'name' => 'describe_self',
    'cname' => 'Describe Pines',
    'description' => 'Whether to show Pines\' description underneath yours.Look!semicolons;;;;;',
    'value' => true,
  ),
);

?>