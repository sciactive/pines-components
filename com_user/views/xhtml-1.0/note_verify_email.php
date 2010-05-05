<?php
/**
 * Displays a registration note to the user.
 *
 * @package Pines
 * @subpackage com_user
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
$this->title = 'New User Registration';
$this->note = 'The next step is to verify the email address you entered.';
?>
<div>
	An email has been sent to <?php echo $this->entity->email; ?> with a
	verification link. Please click the link to verify your email address and
	log in.
</div>