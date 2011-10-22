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
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');
$this->title = 'Repository Certificate';
$this->note = 'All new packages are signed against this certificate.';
?>
<div class="pf-form">
	<script type="text/javascript">
		// <![CDATA[
		pines(function(){
			$.ajax({
				type: "GET",
				url: "<?php echo addslashes("{$pines->config->location}{$pines->config->com_repository->repository_path}private/cert.key"); ?>",
				complete: function(){
					$("#p_muid_key_loading").hide();
				},
				error: function(xhr){
					switch (xhr.status) {
						case 403:
						case 404:
							$("#p_muid_key_good").show();
							break;
						default:
							$("#p_muid_key_unsure").show();
							break;
					}
				},
				success: function(){
					$("#p_muid_key_unsafe").show();
				}
			});
		});
		// ]]>
	</script>
	<div class="pf-element pf-heading">
		<h1>Private Key Check</h1>
	</div>
	<div id="p_muid_key_loading">
		Checking that your private key is not readable to everyone...
	</div>
	<div id="p_muid_key_good" style="display: none;">
		Your private key has been verified as not readable. Remember to keep
		your key secure. If anyone gets ahold of it, they can use it to sign
		packages and pretend to be your repository.
	</div>
	<div id="p_muid_key_unsure" style="display: none;">
		Your private key could not be verified as not readable. Please manually
		check the address at
		<?php echo htmlspecialchars("{$pines->config->location}{$pines->config->com_repository->repository_path}private/cert.key"); ?>.
		If it is readable, you should block access to it by setting
		"AllowOverride" to "All" in your Apache configuration. (Or something
		similar if you're not using Apache.) After the key is verified to not be
		readable, you should regenerate your certificate in case it was
		compromised in transit during the check.
	</div>
	<div id="p_muid_key_unsafe" style="display: none;">
		<div style="float: left; height: 16px; width: 16px; margin: .5em;" class="ui-icon ui-icon-alert">&nbsp;</div>
		Your private key is readable at the address,
		<?php echo htmlspecialchars("{$pines->config->location}{$pines->config->com_repository->repository_path}private/cert.key"); ?>.
		This is a serious security risk. This private key is used to sign
		packages and should never be accessible to anyone but yourself. You
		should block access to it by setting "AllowOverride" to "All" in your
		Apache configuration. (Or something similar if you're not using Apache.)
		After the key is verified to not be readable, you should regenerate your
		certificate in case it was compromised in transit during the check.
	</div>
	<?php if ($pines->config->com_repository->public_cert) { ?>
	<div class="pf-element pf-heading">
		<h1>Public URL</h1>
	</div>
	<div class="pf-element pf-full-width">
		Using this URL, a user can easily add your repository as a trusted
		software source. If your certificate is self signed, however, the user
		will need to add your certificate as a trusted authority.
		<div class="pf-group">
			<a class="pf-field" href="<?php echo htmlspecialchars(pines_url('com_repository', 'publiccert', array(), true)); ?>" onclick="window.open(this.href); return false;"><?php echo htmlspecialchars(pines_url('com_repository', 'publiccert', array(), true)); ?></a>
		</div>
	</div>
	<?php } ?>
	<div class="pf-element pf-heading">
		<h1>Certificate</h1>
	</div>
	<div class="pf-element">
		The certificate text below is what you can supply to your users to allow them access to your repository. Check the details below and make sure that "CN" under "subject" is the correct address of your repository.
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