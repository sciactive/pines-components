<?php
/**
 * A view to load Google Drive Library
 *
 * @package Components\googledrive
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Mohammed Ahmed <mohammedsadikahmed@gmail.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');
?>
<script type="text/javascript">
    var CLIENT_ID = '<?php echo $pines->config->com_googledrive->client_id;?>';
    var SCOPES = '<?php echo (!empty($pines->config->com_googledrive->scopes_export)) ? $pines->config->com_googledrive->scopes_export : 'https://www.googleapis.com/auth/drive';?>';
</script>
<script type="text/javascript" src="<?php echo $pines->config->full_location; ?>components/com_googledrive/includes/export_csv.js"></script>
<script type="text/javascript" src="https://apis.google.com/js/client.js"></script>

