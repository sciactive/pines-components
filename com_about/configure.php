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

$wddx_data = "<wddxPacket version='1.0'><header/><data><array length='2'><struct><var name='name'><string>description</string></var><var name='cname'><string>Description</string></var><var name='description'><string>Description of your installation.</string></var><var name='value'><string>This is the default installation of Pines.</string></var></struct><struct><var name='name'><string>describe_self</string></var><var name='cname'><string>Describe Pines</string></var><var name='description'><string>Whether to show Pines&#039; description underneath yours.</string></var><var name='value'><boolean value='true'/></var></struct></array></data></wddxPacket>";

?>