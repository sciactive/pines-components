<?php
/**
 * Displays a note to the user about failed verification.
 *
 * @package Components\user
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');
$this->title = 'Verification Failed';
?>
<div>
	The secret code given is incorrect. If you changed your email address after
	signing up, you will need to use the link emailed to your new address.
</div>