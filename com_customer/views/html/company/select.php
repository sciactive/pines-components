<?php
/**
 * A view to load the company selector.
 *
 * @package Components\customer
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');
?>
<script type="text/javascript">
	pines.loadjs("<?php echo htmlspecialchars($pines->config->location); ?>components/com_customer/includes/jquery.companyselect.js");
	pines.com_customer_autocompany_url = <?php echo json_encode(pines_url('com_customer', 'company/search')); ?>;
</script>