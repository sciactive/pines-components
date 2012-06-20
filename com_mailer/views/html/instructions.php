<?php
/**
 * Email setup instructions.
 *
 * @package Components\mailer
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');
$this->title = 'Email Instructions';
$this->note = 'Some help for setting up this awesome email system.';
?>
<div>
	Welcome to the Mailer. This component handles the emails sent out of the
	system. It can be a little tricky to set up, so read through these
	instructions carefully.
</div>
<div class="page-header">
	<h3>How It Works</h3>
</div>
<div>
	To use the Mailer to its full potential, you must first know how it works.
	This system constructs emails using three parts:
	<ul>
		<li>The template.</li>
		<li>The mail definition.</li>
		<li>An optional custom redefinition called .</li>
	</ul>
</div>