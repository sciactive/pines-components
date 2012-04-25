<?php
/**
 * Provides a form for the user to edit a package.
 *
 * @package Components
 * @subpackage packager
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');
$this->title = 'Packaging Results';
?>
<div class="pf-form" id="p_muid_package_result">
	<div class="pf-element pf-heading">
		<h3>Successful Packages</h3>
	</div>
	<?php $successes = 0; foreach ($this->results as $cur_result) {
		if (!$cur_result['result'])
			continue;
		$successes++;
		?>
	<div class="pf-element">
		<span class="pf-label"><?php echo htmlspecialchars($cur_result['entity']->name); ?></span>
		<span class="pf-note">Now you can download the package.</span>
		<span class="pf-field"><a href="<?php echo htmlspecialchars("{$pines->config->location}{$cur_result['path']}"); ?>"><?php echo htmlspecialchars($cur_result['filename']); ?></a></span>
	</div>
	<?php } if (!$successes) { ?>
	<div class="pf-element">
		Uh oh. No packages were made successfully.
	</div>
	<?php } ?>
	<div class="pf-element pf-heading">
		<h3>Failed Packages</h3>
	</div>
	<?php $failures = 0; foreach ($this->results as $cur_result) {
		if ($cur_result['result'])
			continue;
		$failures++;
		?>
	<div class="pf-element">
		<span class="pf-label"><?php echo htmlspecialchars($cur_result['entity']->name); ?></span>
		<div class="pf-group">
			<div class="pf-field">The filename which your package was attempted to be created with is "<?php echo htmlspecialchars($cur_result['path']); ?>". If you don't have access to this path, try to gain access to it and attempt packaging again.</div>
			<div class="pf-field">You can attempt to <a href="<?php echo htmlspecialchars(pines_url('com_packager', 'package/make', array('id' => $cur_result['entity']->guid))); ?>">create the package again</a>.</div>
		</div>
	</div>
	<?php } if (!$failures) { ?>
	<div class="pf-element">
		Yay! No errors 
	</div>
	<?php } ?>
</div>