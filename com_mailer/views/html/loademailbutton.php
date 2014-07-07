<?php
/**
 * The view to load the external css/js for the email button
 *
 * @package Components\mailer
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Angela Murrell <amasiell.g@gmail.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');
?>
<link rel="stylesheet" type="text/css" href="<?php echo htmlspecialchars($pines->config->location); ?>components/com_mailer/includes/<?php echo ($pines->config->debug_mode) ? 'emailbutton' : 'emailbutton.min'; ?>.css">
<script type="text/javascript">
	pines.loadjs(<?php echo json_encode(htmlspecialchars($pines->config->location)); ?>+"components/com_mailer/includes/<?php echo ($pines->config->debug_mode) ? 'emailbutton' : 'emailbutton.min'; ?>.js");
</script>

