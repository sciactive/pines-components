<?php
/**
 * Provides a form to add a repository certificate.
 *
 * @package Components
 * @subpackage plaza
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');
$this->title = 'Add a New Repository';
$this->note = 'Use this form to add a trusted repository.';
?>
<form class="pf-form" method="post" action="<?php echo htmlspecialchars(pines_url('com_plaza', 'repository/save')); ?>">
	<div class="pf-element pf-heading">
		<h3>Repository Details</h3>
	</div>
	<div class="pf-element">
		<label><span class="pf-label">Repository Filename</span>
			<span class="pf-note">The repository's filename will determine its priority. Repositories are loaded in order alphabetically.</span>
			<span class="pf-note">Ex: "repo_1"</span>
			<input class="pf-field" type="text" name="filename" size="24" /></label>
	</div>
	<div class="pf-element pf-heading">
		<h3>Repository Certificate</h3>
	</div>
	<div class="pf-element">
		<label><span class="pf-label">Certificate URL</span>
			<span class="pf-note">The public URL of the repository's certificate.</span>
			<input class="pf-field" type="text" name="cert_url" size="32" /></label>
	</div>
	<div class="pf-element">
		<strong>OR</strong>
	</div>
	<div class="pf-element pf-full-width">
		<label><span class="pf-label">Certificate Text</span>
			<span class="pf-note">The full text of the repository's certificate.</span>
			<span class="pf-group pf-full-width">
				<span class="pf-field" style="display: block;">
					<textarea style="width: 100%;" name="cert_text" rows="5" cols="35"></textarea>
				</span>
			</span></label>
	</div>
	<div class="pf-element pf-buttons">
		<input class="pf-button btn btn-primary" type="submit" value="Submit" />
	</div>
</form>