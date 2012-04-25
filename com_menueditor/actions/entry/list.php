<?php
/**
 * List entries.
 *
 * @package Components
 * @subpackage menueditor
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_menueditor/listentries') )
	punt_user(null, pines_url('com_menueditor', 'entry/list'));

$pines->com_menueditor->list_entries();
?>