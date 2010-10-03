<?php
/**
 * Test the file uploader widget.
 *
 * @package Pines
 * @subpackage com_elfinderupload
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_elfinder/finder') && !gatekeeper('com_elfinder/finderself') )
	punt_user(null, pines_url('com_elfinderupload', 'test'));

$module = new module('com_elfinderupload', 'test', 'content');

?>