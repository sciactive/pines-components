<?php
/**
 * Load the entity helper.
 *
 * @package Components\entityhelper
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

$module = new module('com_entityhelper', 'link_helper', 'head');
unset ($module);
$pines->icons->load();

?>