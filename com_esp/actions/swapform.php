<?php
/**
 * Provide a form for swapping inventory.
 *
 * @package Components
 * @subpackage esp
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_esp/editplan') )
	punt_user(null, pines_url('com_esp', 'swapform'));

$esp = com_esp_plan::factory((int) $_REQUEST['id']);
if (!isset($esp->guid)) {
	pines_error('Requested ESP id is not accessible.');
	pines_redirect(pines_url('com_esp', 'list'));
	return;
}

$esp->swap_form();

?>
