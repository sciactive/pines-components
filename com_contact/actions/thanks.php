<?php
/**
 * An action to view a thank you page.
 *
 * @package Components\contact
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Angela Murrell <angela@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

$module = new module('com_contact', 'thanks', 'content');
return $module;
?>