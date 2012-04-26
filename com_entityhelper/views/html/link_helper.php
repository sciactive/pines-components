<?php
/**
 * A view to load the entity helper.
 *
 * @package Components\entityhelper
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');
?>
<script type="text/javascript">
pines.entity_helper_url = <?php echo json_encode(pines_url('com_entityhelper', 'helper')); ?>;
pines(function(){
$("body").on("click", "a[data-entity]", function(){
var e = this;
pines.loadjs("<?php echo htmlspecialchars($pines->config->location); ?>components/com_entityhelper/includes/entityhelper.js");
pines(function(){pines.entity_helper(e);});
});
});
</script>