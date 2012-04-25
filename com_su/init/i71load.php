<?php
/**
 * Load user switcher JS.
 *
 * @package Components
 * @subpackage su
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if (!gatekeeper('com_su/switch'))
	return;

$module = new module('com_su', 'load_js', 'head');
unset ($module);

?>