<?php
/**
 * An email sent to recover a user's account.
 *
 * @package Components\user
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');
$this->title = '#to_first_name#, Account Recovery for #to_username# at #system_name#.';
?>
Hi #to_name#,<br />
<br />
We've received a request at <a href="#site_link#" target="_blank">#site_link#</a>
to help you recover your account. In case you forgot your username, it's
"#to_username#". You can also reset your password by clicking on the following
link:<br />
<br />
<a href="#recover_link#" target="_blank">#recover_link#</a><br />
<br />
As a safety measure, this link will only work for the next #minutes# minutes. If
you didn't make this request, you can ignore this email.<br />
<br />
Thank You,<br />
#system_name#