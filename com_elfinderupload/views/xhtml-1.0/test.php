<?php
/**
 * Tests the file uploader.
 *
 * @package Pines
 * @subpackage com_elfinderupload
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
$this->title = 'elFinder Uploader';
$pines->uploader->load();
?>
<form class="pf-form" method="post" action="<?php echo htmlentities(pines_url('com_elfinderupload', 'result')); ?>">
	<div class="pf-heading">
		<h1>File Uploading Test</h1>
	</div>
	<div class="pf-element">
		<label>
			<span class="pf-label">File</span>
			<input class="pf-field ui-widget-content puploader" type="text" name="file" />
		</label>
	</div>
	<div class="pf-element pf-buttons">
		<input class="pf-button ui-state-default ui-corner-all" type="submit" value="Submit" />
	</div>
</form>