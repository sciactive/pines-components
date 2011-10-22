<?php
/**
 * A view to load the customer selector.
 *
 * @package Pines
 * @subpackage com_customer
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');
?>
<script type="text/javascript">
	// <![CDATA[
	pines.loadjs("<?php echo htmlspecialchars($pines->config->location); ?>components/com_customer/includes/jquery.customerselect.js");
	pines.com_customer_autocustomer_url = "<?php echo addslashes(pines_url('com_customer', 'customer/search')); ?>";
	// ]]>
</script>