<?php
/**
 * Displays the result of a mailing.
 *
 * @package Pines
 * @subpackage com_newsletter
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');
$this->title = 'Sending Mail';
?>
<h3>Status: <?php echo ($this->success ? 'Successfully Sent' : 'Failed to Send'); ?></h3>
<h3>Name: "<?php echo htmlspecialchars($this->name); ?>"</h3>
<h3>Subject: "<?php echo htmlspecialchars($this->subject); ?>"</h3>
<div style="background: white; border: 2px solid black; padding: 5px; clear: both; overflow: auto;">
	<?php echo $this->message; // TODO: Secure this. ?>
	<br style="clear: both;" />
</div>
<br />