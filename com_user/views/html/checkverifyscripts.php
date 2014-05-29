<?php
/**
 * A view to load the Check Email Verification CSS and JS
 * 
 * @package Components\cache
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Angela Murrell <amasiell.g@gmail.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');
?>
<link href="<?php echo htmlspecialchars($pines->config->location); ?>components/com_user/includes/<?php echo $pines->config->debug_mode ? 'checkverify.css' : 'checkverify.min.css'; ?>" media="all" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="<?php echo htmlspecialchars($pines->config->location); ?>components/com_user/includes/<?php echo $pines->config->debug_mode ? 'checkverify.js' : 'checkverify.min.js'; ?>"></script>
