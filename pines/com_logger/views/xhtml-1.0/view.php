<?php
/**
 * Displays a log.
 *
 * @package Pines
 * @subpackage com_logger
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
$this->title = "Displaying Log File: {$pines->config->com_logger->path}";
?>
<div style="font-family: monospace; white-space: pre; width: 100%; overflow: auto;"><?php echo $this->log; ?></div>