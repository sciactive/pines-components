<?php
/**
 * An email sent when a user registers.
 *
 * @package Components\user
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');
$this->title = '#to_first_name#, New user [#user_username#] registered on #system_name#.';
?>
#to_name#,<br />
<br />
A new user has registered on <a href="#site_link#" target="_blank">#site_link#</a>.
Here are the details of their account:<br />
<br />
<table cellpadding="0" cellspacing="2" border="0">
	<tr>
		<td valign="top" style="padding-right: 1em;">Username:</td>
		<td>#user_username#</td>
	</tr>
	<tr>
		<td valign="top" style="padding-right: 1em;">Name:</td>
		<td>#user_name#</td>
	</tr>
	<tr>
		<td valign="top" style="padding-right: 1em;">Email:</td>
		<td><a href="mailto:#user_email#">#user_email#</a></td>
	</tr>
	<tr>
		<td valign="top" style="padding-right: 1em;">Phone:</td>
		<td>#user_phone#</td>
	</tr>
	<tr>
		<td valign="top" style="padding-right: 1em;">Fax:</td>
		<td>#user_fax#</td>
	</tr>
	<tr>
		<td valign="top" style="padding-right: 1em;">Timezone:</td>
		<td>#user_timezone#</td>
	</tr>
	<tr>
		<td valign="top" style="padding-right: 1em;">Address:</td>
		<td>#user_address#</td>
	</tr>
</table>
<br />
Regards,<br />
#system_name#