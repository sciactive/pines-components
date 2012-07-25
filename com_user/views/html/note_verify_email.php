<?php
/**
 * Displays a registration note to the user.
 *
 * @package Components\user
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');
$this->title = 'New User Registration';
$this->note = 'The next step is to verify the email address you entered.';
?>
<div>
	An email has been sent to <?php echo htmlspecialchars($this->entity->email); ?> with a
	verification link.
	<?php if ($pines->config->com_user->unconfirmed_access) { ?>
	You will have limited access until you verify your email address by clicking
	the link.
	<?php } else { ?>
	Please click the link to verify your email address and log in.
	<?php } ?>
</div>