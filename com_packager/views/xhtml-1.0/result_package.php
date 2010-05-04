<?php
/**
 * Provides a form for the user to edit a package.
 *
 * @package Pines
 * @subpackage com_packager
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
$this->title = "Packaging results for {$this->entity->name}.";
?>
<div class="pf-form" id="package_result">
	<?php if ($this->result) { ?>
	<script type="text/javascript">
		// <![CDATA[
		pines(function(){
			if ($.fn.button)
				$("#package_result a").button();
		});
		// ]]>
	</script>
	<div class="pf-heading">
		<h1>Congratulations</h1>
		<p>Packaging succeeded.</p>
	</div>
	<div class="pf-element">
		<span class="pf-label">Package Files</span>
		<span class="pf-note">You can download your package files now.</span>
		<span class="pf-field"><a href="<?php echo htmlentities("{$pines->config->rela_location}{$this->path}"); ?>"><?php echo htmlentities($this->filename); ?></a></span>
	</div>
	<?php } else { ?>
	<div class="pf-heading">
		<h1>Error</h1>
		<p>Packaging failed.</p>
	</div>
	<div class="pf-element">
		<span class="pf-label">Package Files</span>
		<div class="pf-group">
			<span class="pf-field">The filename which your package was attempted to be created with is "<?php echo htmlentities($this->path); ?>". If you don't have access to this path, try to gain access to it and attempt packaging again.</span>
		</div>
	</div>
	<div class="pf-element">
		<span class="pf-label">Package Again</span>
		<div class="pf-group">
			<span class="pf-field">You can attempt to <a href="<?php echo htmlentities(pines_url('com_packager', 'makepackage', array('id' => $this->entity->guid))); ?>">create the package again</a>.</span>
		</div>
	</div>
	<?php } ?>
</div>