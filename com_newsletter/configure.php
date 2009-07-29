<?php
/**
 * com_newsletter's WDDX configuration.
 *
 * @package Pines
 * @subpackage com_newsletter
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

return "<wddxPacket version='1.0'><header/><data><array length='2'><struct><var name='name'><string>default_from</string></var><var name='cname'><string>Default From</string></var><var name='description'><string>The default &quot;from&quot; email.</string></var><var name='value'><string>Nowhere &lt;nowhere@example.com&gt;</string></var></struct><struct><var name='name'><string>default_reply_to</string></var><var name='cname'><string>Default Reply To</string></var><var name='description'><string>The &quot;reply-to&quot; email.</string></var><var name='value'><string>webmaster@example.com</string></var></struct></array></data></wddxPacket>";

?>