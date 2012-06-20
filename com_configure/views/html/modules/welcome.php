<?php
/**
 * Welcome to Pines widget.
 *
 * @package Components\configure
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');
$this->title = 'Welcome to '.htmlspecialchars($pines->info->name);
?>
<div class="page-header">
	<h1>Welcome to <?php echo htmlspecialchars($pines->info->name); ?> <small>version <?php echo htmlspecialchars($pines->info->version); ?></small></h1>
</div>
<p>Congratulations on successfully installing <?php echo htmlspecialchars($pines->info->name); ?>
	on your system. <a href="#p_muid_migrating" data-toggle="modal">Are you migrating from another installation?</a></p>
<div class="modal hide" id="p_muid_migrating">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal">×</button>
		<h3>Migrating an Installation</h3>
	</div>
	<div class="modal-body">
		<p>Migrating a Pines installation involves just a few quick steps:</p>
		<div id="p_muid_migrate_steps" class="accordion">
			<div class="accordion-group">
				<a class="accordion-heading ui-helper-clearfix" href="javascript:void(0);" data-parent="#p_muid_migrate_steps" data-toggle="collapse" data-target=":focus + .collapse" tabindex="0">
					<big class="accordion-toggle">
						Reinstalling Components and Templates
					</big>
				</a>
				<div class="accordion-body collapse">
					<div class="accordion-inner clearfix">
						You need to reinstall all the components and templates
						you had installed on the previous installation. You can
						do that in the <a href="<?php echo htmlspecialchars(pines_url('com_plaza', 'package/repository')); ?>">Pines Plaza</a>.
					</div>
				</div>
			</div>
			<div class="accordion-group">
				<a class="accordion-heading ui-helper-clearfix" href="javascript:void(0);" data-parent="#p_muid_migrate_steps" data-toggle="collapse" data-target=":focus + .collapse" tabindex="0">
					<big class="accordion-toggle">
						Copying Configuration Files
					</big>
				</a>
				<div class="accordion-body collapse">
					<div class="accordion-inner clearfix">
						Next you need to copy all your configuration files to
						the new installation. If you are on a Unix or Linux
						system, you can do that while in the installation
						directory with this command:
						<pre>find -L . ./components/ ./templates/ -maxdepth 2 -name "config.php" | xargs tar -czf configfiles.tar.gz</pre>
						Then move the <code>configfiles.tar.gz</code> file to
						this new installation's directory and run this command:
						<pre>tar -xzhf configfiles.tar.gz && rm configfiles.tar.gz</pre>
					</div>
				</div>
			</div>
			<div class="accordion-group">
				<a class="accordion-heading ui-helper-clearfix" href="javascript:void(0);" data-parent="#p_muid_migrate_steps" data-toggle="collapse" data-target=":focus + .collapse" tabindex="0">
					<big class="accordion-toggle">
						Copying Media Files
					</big>
				</a>
				<div class="accordion-body collapse">
					<div class="accordion-inner clearfix">
						Since media files are all stored in the <code>media</code>
						folder (unless you changed the "Upload Location"
						option), you can simply copy the contents of that folder
						from the old installation.
					</div>
				</div>
			</div>
			<div class="accordion-group">
				<a class="accordion-heading ui-helper-clearfix" href="javascript:void(0);" data-parent="#p_muid_migrate_steps" data-toggle="collapse" data-target=":focus + .collapse" tabindex="0">
					<big class="accordion-toggle">
						Migrating Entity Data
					</big>
				</a>
				<div class="accordion-body collapse">
					<div class="accordion-inner clearfix">
						Data in Pines is stored in objects called entities,
						which can be migrated using the Entity Tools component,
						com_entitytools. If you don't already have it installed
						on the old installation, install it. Now go to System ->
						Entity Tools -> Export to export your entities. Then on
						this system, go to <a href="<?php echo htmlspecialchars(pines_url('com_entitytools', 'import')); ?>">Import</a>
						and select the file that the export returned.
					</div>
				</div>
			</div>
			<div class="accordion-group">
				<a class="accordion-heading ui-helper-clearfix" href="javascript:void(0);" data-parent="#p_muid_migrate_steps" data-toggle="collapse" data-target=":focus + .collapse" tabindex="0">
					<big class="accordion-toggle">
						Anything Else?
					</big>
				</a>
				<div class="accordion-body collapse">
					<div class="accordion-inner clearfix">
						That covers all the standard files and data that
						components use, however there may be other data that
						needs to be migrated. Check the old database for any
						other tables that may need to be migrated. Check the old
						installation folder for other files that may need to be
						copied.
						<br /><br />
						After you're sure there's no more data to copy, you're
						done!
						<div class="picon-32 picon-face-smile" style="background-position: center top; background-repeat: no-repeat; height: 32px; margin-top: 1em;">&nbsp;</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="modal-footer">
		<a href="javascript:void(0);" class="btn btn-primary" data-dismiss="modal">Close</a>
		<a href="http://pinesframework.org/content/page/a-support/" target="_blank" class="btn">Ask for Help</a>
	</div>
</div>
<div>
	To help you get started with Pines, here are some important areas in the
	<a href="<?php echo htmlspecialchars(pines_url('com_configure', 'list')); ?>">configuration</a>:
	<h4 style="text-align: center;">Settings and Preferences</h4>
	<dl style="margin-top: 0;">
		<dt><a href="<?php echo htmlspecialchars(pines_url('com_configure', 'edit', array('component' => 'system'))); ?>">Pines Config</a></dt>
		<dd>Main system config includes things like names, default templates and component, timezone, etc.</dd>

		<dt><a href="<?php echo htmlspecialchars(pines_url('com_configure', 'edit', array('component' => 'com_user'))); ?>">User Manager</a></dt>
		<dd>The user manager provides user and group abilities. You can tune it to work just how you'd like here.</dd>

		<dt><a href="<?php echo htmlspecialchars(pines_url('com_configure', 'edit', array('component' => 'com_content'))); ?>">CMS</a></dt>
		<dd>The CMS, or Content Management System is what builds the pages in your website. You can set defaults and options here.</dd>

		<dt><a href="<?php echo htmlspecialchars(pines_url('com_configure', 'edit', array('component' => 'com_timeoutnotice'))); ?>">Timeout Notice</a></dt>
		<dd>The timeout notice will log users out after they've been idle for a while. You can set up its features here.</dd>

		<dt><a href="<?php echo htmlspecialchars(pines_url('com_configure', 'edit', array('component' => 'com_logger'))); ?>">Logger</a></dt>
		<dd>The logger keeps a log of important things that happen on your website. You can set up how and where it logs information here.</dd>
	</dl>
	<h4 style="text-align: center;">Appearance</h4>
	<dl style="margin-top: 0;">
		<dt><a href="<?php echo htmlspecialchars(pines_url('com_configure', 'edit', array('component' => 'system'))); ?>">Pines Config</a></dt>
		<dd>The main system config lets you choose default templates, which change the whole site's appearance.</dd>

		<dt><a href="<?php echo htmlspecialchars(pines_url('com_configure', 'edit', array('component' => 'com_bootstrap'))); ?>">Bootstrap</a> and <a href="<?php echo htmlspecialchars(pines_url('com_configure', 'edit', array('component' => 'com_jquery'))); ?>">jQuery</a></dt>
		<dd>Bootstrap provides theming for most of the form inputs, buttons, and other various elements. jQuery UI provides theming for many of the widgets, like the data grids, and the tpl_pines template. Try different combinations of Bootstrap and jQuery UI themes.</dd>

		<dt><a href="<?php echo htmlspecialchars(pines_url('com_configure', 'edit', array('component' => 'tpl_pinescms'))); ?>">Pines CMS Template</a> and <a href="<?php echo htmlspecialchars(pines_url('com_configure', 'edit', array('component' => 'tpl_pines'))); ?>">Pines Template</a></dt>
		<dd>These are the default templates for Pines. You can configure a lot of options for each of them, including changing their layout.</dd>
	</dl>
</div>