<?php
/**
 * A view to load Pines Tags.
 *
 * @package Pines
 * @subpackage com_ptags
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
?>
<link href="<?php echo $pines->config->rela_location; ?>components/com_ptags/includes/jquery.ptags.default.css" media="all" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="<?php echo $pines->config->rela_location; ?>components/com_ptags/includes/<?php echo $pines->config->debug_mode ? 'jquery.ptags.js' : 'jquery.ptags.min.js'; ?>"></script>