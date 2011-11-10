<?php
/**
 * Provides a form to upload a package.
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
$this->title = 'Upload a Package';

// Find the max upload size.
$upload_max = trim(ini_get('upload_max_filesize'));
$post_max = ini_get('post_max_size');

$last = strtolower(substr($upload_max, -1));
$val1 = intval($upload_max);
switch($last) {
	// The 'G' modifier is available since PHP 5.1.0
	case 'g':
		$val1 *= 1024;
	case 'm':
		$val1 *= 1024;
	case 'k':
		$val1 *= 1024;
}

$last = strtolower(substr($post_max, -1));
$val2 = intval($post_max);
switch($last) {
	// The 'G' modifier is available since PHP 5.1.0
	case 'g':
		$val2 *= 1024;
	case 'm':
		$val2 *= 1024;
	case 'k':
		$val2 *= 1024;
}

$max = ($val1 < $val2) ? $upload_max : $post_max;

?>
<form enctype="multipart/form-data" class="pf-form" method="post" action="<?php echo htmlspecialchars(pines_url('com_repository', 'savepackage')); ?>">
	<div class="pf-element pf-heading">
		<p>Select the Slim archive to add to the repository.</p>
	</div>
	<div class="pf-element">
		<label><span class="pf-label">Upload Package File</span>
			<span class="pf-note">Max Size: <?php echo htmlspecialchars($max); ?></span>
			<input class="pf-field ui-widget-content ui-corner-all" type="file" name="package" /></label>
	</div>
	<div class="pf-element pf-buttons">
		<input class="pf-button ui-state-default ui-priority-primary ui-corner-all" type="submit" value="Submit" />
	</div>
</form>