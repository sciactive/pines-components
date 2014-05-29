<?php
/**
 * Lets the user request the verification email again.
 *
 * @package Components\user
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');
$this->show_title = false;
$pines->com_user->load_check_verify();
?>
<div class="hide resend-verification-url"><?php echo pines_url('com_user', 'resend_verification'); ?></div>
<div class="hide check-verification-url"><?php echo pines_url('com_user', 'checkverify'); ?></div>
<div class="alert hide alert-info notice-email-verify"><div><span class="email-notice-title"><i class="icon-envelope"></i> <strong>Email Verification</strong></span> <span class="message">You haven't verified your email yet. Check your email, <a class="label label-info resend-verify" href="javascript:void(0);">Re-Send the verification email</a>, or</span> <span class="leave-for-errors"><a class="label label-info edit-email" href="<?php echo htmlspecialchars($pines->config->com_user->verify_edit_email_link); ?>">edit your email</a>.</span></div></div>