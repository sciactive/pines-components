<?php
/**
 * Show unsubscribing results.
 *
 * @package Components\mailer
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');
if ($this->success)
	$this->title = 'Unsubscribed Successfully';
else
	$this->title = 'Unsubscribe Failed';
if ($this->secret) { ?>
<div>
	The secret provided doesn't match the email sent. Please click on the
	unsubscribe link in the email if you wish to unsubscribe.
</div>
<?php } if ($this->error) { ?>
<div>
	There was an error processing your request. Please try again in a few
	minutes.
</div>
<?php } if ($this->not_set_up) { ?>
<div>
	The unsubscribed user database has not been set up on this system. Please
	notify an administrator about this error.
</div>
<?php } if ($this->success) { ?>
<div>
	You have been unsubscribed from our mailing list. If you'd like to receive
	emails from us again in the future, you can edit your account's
	communication preferences by logging in and choosing "My Account".
	<br /><br />
	Please note that you may still receive emails from us of an important
	nature, such as notifications of an account change.
</div>
<?php } ?>