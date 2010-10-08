<?php
/**
 * Shows certificate details.
 *
 * @package Pines
 * @subpackage com_repository
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
$this->title = 'Repository Certificate';
$this->note = 'All new packages are signed against this certificate.';
?>
<div class="pf-form">
	<div class="pf-element pf-heading">
		<p>The certificate text below is what you can supply to your users to allow them access to your repository. Check the details below and make sure that "CN" under "subject" is the correct address of your repository.</p>
	</div>
	<div class="pf-element pf-full-width">
		<span class="pf-label">Certificate</span>
		<span class="pf-note">Full repository certificate text.</span>
		<span class="pf-field pf-full-width"><textarea class="ui-widget-content ui-corner-all" readonly="readonly" style="width: 100%;" rows="8" cols="35"><?php echo $this->cert; ?></textarea></span>
	</div>
	<div class="pf-element pf-full-width">
		<span class="pf-label">Certificate Details</span>
		<span class="pf-field pf-full-width"><textarea class="ui-widget-content ui-corner-all" readonly="readonly" style="width: 100%;" rows="16" cols="35"><?php echo print_r($this->data, true); ?></textarea></span>
	</div>
</div>