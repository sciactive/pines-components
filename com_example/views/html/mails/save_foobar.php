<?php
/**
 * An example mail that's sent when a foobar is saved.
 *
 * @package Components\example
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');
$this->title = '#to_name#, a foobar was saved!';
?>
<p>
	Hi #to_first_name#,<br />
	<br />
	I'm letting you know that #name# just saved a foobar:<br />
	<span style="margin-left: 2em;">#foobar_name#</span>
</p>