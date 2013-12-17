<?php
/**
 * The action to generate a form for adding interactions to customers.
 *
 * @package Components\loan
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Angela Murrell <angela@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_customer/newinteraction') )
		punt_user(null, pines_url('com_loan', 'loan/list'));

$pines->com_loan->add_interaction_form($_REQUEST['ids']);
?>