<?php
/**
 * A view to load Pines Grid.
 *
 * @package Pines
 * @subpackage com_pgrid
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
?>
<link href="<?php echo $pines->config->rela_location; ?>components/com_pgrid/includes/jquery.pgrid.default.css" media="all" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="<?php echo $pines->config->rela_location; ?>components/com_pgrid/includes/<?php echo $pines->config->debug_mode ? 'jquery.pgrid.js' : 'jquery.pgrid.min.js'; ?>"></script>