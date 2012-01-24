<?php
/**
 * List threads.
 *
 * @package Pines
 * @subpackage com_notes
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_notes/listthreads') )
	punt_user(null, pines_url('com_notes', 'thread/list'));

$pines->com_notes->list_threads();
?>