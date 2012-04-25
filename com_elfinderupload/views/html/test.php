<?php
/**
 * Tests the file uploader.
 *
 * @package Components
 * @subpackage elfinderupload
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');
$this->title = 'elFinder Uploader';
$pines->uploader->load();
?>
<form class="pf-form" method="post" action="<?php echo htmlspecialchars(pines_url('com_elfinderupload', 'result')); ?>">
	<div class="pf-element pf-heading">
		<h3>File Uploading Test</h3>
	</div>
	<div class="pf-element">
		<label>
			<span class="pf-label">File</span>
			<input class="pf-field puploader" type="text" name="file" />
		</label>
	</div>
	<div class="pf-element">
		<label>
			<span class="pf-label">Temp File</span>
			<span class="pf-note">A temp file uploader only lets you upload to a temporary folder.</span>
			<input class="pf-field puploader puploader-temp" type="text" name="tmpfile" />
		</label>
	</div>
	<div class="pf-element">
		<label>
			<span class="pf-label">Allow Folders</span>
			<input class="pf-field puploader puploader-folders" type="text" name="folder" />
		</label>
	</div>
	<div class="pf-element">
		<label>
			<span class="pf-label">Files</span>
			<input class="pf-field puploader puploader-multiple" type="text" name="files" />
		</label>
	</div>
	<div class="pf-element pf-buttons">
		<input class="pf-button btn" type="submit" value="Submit" />
	</div>
</form>