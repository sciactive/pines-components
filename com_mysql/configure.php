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

$wddx_data = "<wddxPacket version='1.0'><header/><data><array length='5'><struct><var name='name'><string>host</string></var><var name='cname'><string>Host</string></var><var name='description'><string>The default MySQL host.</string></var><var name='value'><string>localhost</string></var></struct><struct><var name='name'><string>user</string></var><var name='cname'><string>User</string></var><var name='description'><string>The default MySQL user.</string></var><var name='value'><string>pines</string></var></struct><struct><var name='name'><string>password</string></var><var name='cname'><string>Password</string></var><var name='description'><string>The default MySQL password.</string></var><var name='value'><string>password</string></var></struct><struct><var name='name'><string>database</string></var><var name='cname'><string>Database</string></var><var name='description'><string>The default MySQL database.</string></var><var name='value'><string>pines</string></var></struct><struct><var name='name'><string>prefix</string></var><var name='cname'><string>Table Prefix</string></var><var name='description'><string>The default MySQL table name prefix.</string></var><var name='value'><string>pin_</string></var></struct></array></data></wddxPacket>";
?>