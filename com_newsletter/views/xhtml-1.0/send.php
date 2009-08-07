<?php
/**
 * Displays the result of a mailing.
 *
 * @package Pines
 * @subpackage com_newsletter
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

?>
<h3>Subject: &quot;<?php echo $this->subject; ?>&quot;</h3>
<div style="background: white; border: 2px solid black; padding: 5px; clear: both; overflow: auto;">
    <?php echo $this->message; ?>
    <br style="clear: both;" />
</div>
<br />