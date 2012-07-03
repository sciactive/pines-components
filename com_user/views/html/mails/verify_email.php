<?php
/**
 * An email sent to verify a user's email address.
 *
 * @package Components\user
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');
$this->title = '#to_first_name#, Welcome to #system_name#. Please confirm your email.';
?>
Welcome #to_name#,<br />
<br />
Thank you for signing up at <a href="#site_link#" target="_blank">#site_link#</a>.
Please confirm your email by clicking on the following link to activate your
account:<br />
<br />
<a href="#verify_link#" target="_blank">#verify_link#</a><br />
<br />
Thank You,<br />
#system_name#