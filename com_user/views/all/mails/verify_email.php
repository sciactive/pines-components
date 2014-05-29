<?php
/**
 * An email sent to verify a user's email address.
 *
 * @package Components\user
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');
$this->title = '#to_first_name#, Welcome to #system_name#. Please confirm your email.';
$site_link = (!empty($pines->config->com_user->verify_email_domain)) ? htmlspecialchars($pines->config->com_user->verify_email_domain) : '#site_link#';
// Add the Redirect URL if that option is being used.
$redirect = (!empty($pines->config->com_user->verify_email_domain) && $pines->config->com_user->verify_redirect_domain) ? '?url='.$site_link : '';
pines_log('the site link is: '.$site_link, 'notice');
?>
Welcome #to_name#,<br />
<br />
Thank you for signing up at <a href="<?php echo $site_link; ?>" target="_blank"><?php echo $site_link; ?></a>.
Please confirm your email by clicking on the following link to activate your
account:<br />
<br />
<a href="#verify_link#<?php echo $redirect; ?>" target="_blank">#verify_link#</a><br />
<?php if (!empty($pines->config->com_user->verify_email_contact_url)) { ?>
<br />
<br />
For any questions or help with our online services, please <a href="<?php echo htmlspecialchars($pines->config->com_user->verify_email_contact_url); ?>" target="_blank">contact us</a>.
<?php } ?>
<br /><br />
Regards,<br />
#system_name#