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
To use the Mailer to its full potential, you must first know how it works.
This system constructs emails using three parts:
<ul>
	<li>The template.</li>
	<li>The mail definition.</li>
	<li>An optional custom redefinition called a rendition.</li>
</ul>
<p>
	The <strong>template</strong> is used for each email that is sent. It
	provides the basic layout of the email, and some common features like an
	unsubscribe link. Any number of templates can be defined, but only the
	first one that is enabled and whose conditions are met will be used. If
	you don't define your own template, a default one will be used.
</p>
<p>
	The <strong>mail definition</strong> is provided by the component
	responsible for initiating the email. It provides the main content of
	the email. So, for example, the mail definition of an email sent when a
	new user registers would pertain to the user and provide their
	information in the body of the email. Whereas, the definition of an
	email sent when a user makes an online purchase would provide
	information about the purchase and likely a receipt.
</p>
<p>
	A <strong>rendition</strong>, when defined, is used in place of the mail
	definition to construct the body of the email. It can be created, by
	you, to customize the email's content.
</p>
<div class="page-header">
	<h3>How To Customize</h3>
</div>
<p>
	The two ways you can customize the emails are by creating templates and
	renditions. When you create a template, you can design the overall look and
	design of all emails. When you create a rendition, you can customize a
	single type of email (like a new user registration email).
</p>
<h4>Macros</h4>
<p>
	The content in an email almost always includes variables, such as the
	recipient's name, which are handled by macros. A macro is just the name of a
	variable surrounded by hash symbols (e.g. #to_name#). This text is replaced
	by the correct value before the email is sent.
</p>
<p>
	There are universal macros, which can be used in any template, definition,
	or rendition. Also, there are macros specific to a mail definition. For
	example, the definition of an email sent when a user changes an appointment
	with a customer may have macros called #old_date# and #new_date#. When you
	create a rendition to customize the email, you can use these macros.
</p>
<div class="well">
	<p>
		<span class="label label-warning">Heads Up</span>
		When you format a macro, edit the macro string as a whole. If you want
		to bold it, change the whole string, including the hash symbols.
	</p>
	<div class="row-fluid">
		<div class="span6">
			<pre style="font-size: 1.2em; font-weight: normal;"><span class="label label-success">Right</span> <strong>#to_name#</strong>, <em>#old_date#</em></pre>
		</div>
		<div class="span6">
			<pre style="font-size: 1.2em; font-weight: normal;"><span class="label label-important">Wrong</span> #<strong>to_name</strong>#, #old_<em>date#</em></pre>
		</div>
	</div>
</div>