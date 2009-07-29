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

$wddx_data = "<wddxPacket version='1.0'><header/><data><array length='3'><struct><var name='name'><string>empty_pw</string></var><var name='cname'><string>Empty Passwords</string></var><var name='description'><string>Allow users to have empty passwords.</string></var><var name='value'><boolean value='false'/></var></struct><struct><var name='name'><string>create_admin</string></var><var name='cname'><string>Create Admin</string></var><var name='description'><string>Allow the creation of an admin user.</string></var><var name='value'><boolean value='true'/></var></struct><struct><var name='name'><string>create_admin_secret</string></var><var name='cname'><string>Create Admin Secret</string></var><var name='description'><string>The secret necessary to create an admin user.</string></var><var name='value'><string>874jdiv8</string></var></struct></array></data></wddxPacket>";

?>