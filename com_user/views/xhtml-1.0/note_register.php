<?php
/**
 * Displays a registration note to the user.
 *
 * @package com_user
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
$this->title = 'Registered User';
?>
<div class="pf-form">
	<div class="pf-element">
		<span class="pf-label">Email Verification</span>
		<span class="pf-note">
			An email has been sent to you with a link. Please click the link to
			verify your email address and login.
		</span>
	</div>
</div>