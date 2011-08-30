<?php
/**
 * Provides a file manager.
 *
 * @package Pines
 * @subpackage com_elfinder
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
$this->title = 'File Manager';
$pines->com_elfinder->load();
?>
<script type="text/javascript">
	// <![CDATA[
	pines(function(){
		<?php if (!$this->ckeditor) { ?>
		$(".com_elfinder_finder").elfinder({
			url: "<?php echo addslashes(pines_url('com_elfinder', 'connector')); ?>",
			docked: false,
			height: <?php echo addslashes($pines->config->com_elfinder->default_height); ?>
		});
		<?php } else { ?>
		var funcNum = window.location.search.replace(/^.*CKEditorFuncNum=(\d+).*$/, "$1");
		var langCode = window.location.search.replace(/^.*langCode=([a-z]{2}).*$/, "$1");

		$(".com_elfinder_finder").elfinder({
			url: "<?php echo addslashes(pines_url('com_elfinder', 'connector')); ?>",
			docked: false,
			height: <?php echo addslashes($pines->config->com_elfinder->default_height); ?>,
			lang: langCode,
			editorCallback: function(url) {
				window.opener.CKEDITOR.tools.callFunction(funcNum, url);
				window.close();
			}
		});
		<?php } ?>
	});
	// ]]>
</script>
<div class="com_elfinder_finder"></div>