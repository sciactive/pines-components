<?php
/**
 * A view to load Pines Form.
 *
 * @package Components\pform
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');
?>
<script type="text/javascript">
pines.loadcss("<?php echo htmlspecialchars($pines->config->location); ?>components/com_pform/includes/pform.css");
<?php if ($pines->depend->check('component', 'com_bootstrap')) { ?>
pines.loadcss("<?php echo htmlspecialchars($pines->config->location); ?>components/com_pform/includes/pform-bootstrap.css");
<?php } ?></script>
<!--[if lt IE 8]>
<script type="text/javascript">
pines.loadcss("<?php echo htmlspecialchars($pines->config->location); ?>components/com_pform/includes/pform-ie-lt-8.css");</script>
<![endif]-->
<!--[if lt IE 6]>
<script type="text/javascript">
pines.loadcss("<?php echo htmlspecialchars($pines->config->location); ?>components/com_pform/includes/pform-ie-lt-6.css");</script>
<![endif]-->
<style type="text/css">
.pf-form .pf-element .pf-label, .pf-form .pf-element .pf-note {
width: 180px;
}
.pf-form .pf-element .pf-group {
margin-left: 180px;
}
.pf-form .pf-element .pf-field.pf-full-width {
margin-left: 185px;
}
.pf-form .pf-buttons {
padding-left: 165px;
}
</style>