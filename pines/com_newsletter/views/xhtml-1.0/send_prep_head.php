<?php
/**
 * Includes for sending a newsletter.
 *
 * @package Pines
 * @subpackage com_newsletter
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
?>
<script src="<?php echo $pines->config->rela_location; ?>components/com_newsletter/js/jquery.checkboxtree.js" type="text/javascript"></script>
<script type="text/javascript">
	// <![CDATA[
	jQuery(document).ready(function(){
		jQuery(".unorderedlisttree").checkboxTree({
				collapsedarrow: "<?php echo $pines->config->rela_location; ?>components/com_newsletter/images/img-arrow-collapsed.gif",
				expandedarrow: "<?php echo $pines->config->rela_location; ?>components/com_newsletter/images/img-arrow-expanded.gif",
				blankarrow: "<?php echo $pines->config->rela_location; ?>components/com_newsletter/images/img-arrow-blank.gif",
				checkchildren: true
		});
	});
	// ]]>
</script>
<link href="<?php echo $pines->config->rela_location; ?>components/com_newsletter/css/checktree.css" media="all" rel="stylesheet" type="text/css" />