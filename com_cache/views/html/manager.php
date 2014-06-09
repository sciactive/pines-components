<?php

/**
 * com_cache's view for the manager.
 *
 * @package Components\cache
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Angela Murrell <amasiell.g@gmail.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

$pines->com_cache->load();
$pines->com_jstree->load();
$pines->com_timeago->load();

$this->title = 'Cache Manager';
$cache_on = $this->cacheoptions['cache_on'];
$parent_directory = $this->cacheoptions['parent_directory'];
$global_exceptions = $this->cacheoptions['global_exceptions'];
$c_users = count($global_exceptions['users']);
$c_groups = count($global_exceptions['groups']);
// This will give you the count for the loop:
$max_exceptions = ($c_users >= $c_groups) ? $c_users : $c_groups;
$max_type = ($c_users == $c_groups) ? 'both' : (($c_users > $c_groups) ? 'users': 'groups');
$cachelist = $this->cacheoptions['cachelist'];
ksort($cachelist); // Puts component directives in order.
$get_file_count = $this->get_file_count;
?>
<div class="p_cache_cachemanager">
	<h2 class="text-center">Instead of generating static PHP output, leverage PHP caching by serving HTML files instead.</h2>
	<hr style="margin-bottom: 5px;"/>
	<div class="row-fluid">
		<div class="span10 offset1">
			<br/>
			<h3 class="text-center" style="margin-top: 0; font-weight: normal;">
				With caching turned on, the system will create a hierarchy of cached files based on
			</h3>
			<br/>
			<ul style="list-style-type: none; margin: 0;" class="row-fluid">
				<li class="alert alert-info span4 readmore"><strong>Domain</strong>
					<br/>
					The system handles storing all your cached files per domain, so you never
					manage how your files are organized. You only manage directives.
					<br/><br/>
					<hr/>
					<span class="readless fade">
					This way, you can safely set one directive to apply to all domains. 
					However, if you need to apply a directive to a specific domain, you
					will need to write a unique directive for each domain and can no longer
					utilize the 'all'. 
					<br/><br/>
					<small><i class="icon-info-sign"></i> Compatibility not guaranteed if multiple installations of pines are ran per domain.
					 Although, to ensure that it works, specify different parent directories.</small>
					</span>
				</li>
				<li class="well span4 readmore"><strong>Query Hash</strong>
					<br/>
					All cached files will store with the same file name of component.action.html or home.html, 
					but for those that have a query string, a folder is created 
					by taking the hash of the query string, and the files are stored in there.
					<hr/>
					<span class="readless fade">
					This is only true if your directive says to cache the query. With that setting on, it will
					also cache the page if there is no query. This is useful for product pages, content pages etc.<br/><br/>
					<small><i class="icon-info-sign"></i> If caching the query is on for the directive, you can make exceptions
					to force skipping the cache if certain POST or GET data is set. This makes it possible to not cache
					individual products, pages, widgets, etc.</small>
					</span>
				</li>
				<li class="alert alert-success span4 readmore"><strong>Ability Hash</strong>
					<br/>
					For each unique set of abilities, a hash is made and the component.action.html or home.html files
					are stored within them, possibly within a query hash folder too.
					<br/><br/>
					<hr/>
					<span class="readless fade"> 
					This is a very important aspect of
					the way the caching works to ensure a proper experience based on abilities. The list users view should
					cache a grid with or without a delete user button depending on abilities. 
					
					<br/><br/>
					<small><i class="icon-info-sign"></i> It's important to group types
					of users and  their abilities together to eliminate creating too many ability folders. 
					ie. Customers, employees, admin etc.</small>
					</span>
				</li>
			</ul>
			<div class="text-center" style="margin-top: 0; font-weight: normal; padding: 10px; font-size: 20px;">
				<i class="icon-code-fork icon-2x"></i>
                <div style="margin: 5px;" class="text-center">
					View an <a class="show-example" href="javascript:void(0);">example and flowchart</a> of how files are cached.
				</div>
			</div>
			<div class="example hide">
				<h1 class="text-center dotted"><span>How Caching is Implemented</span></h1>
			</div>
			<div class="row-fluid example hide">
				<div class="span3">
					<p><strong>Caching is implemented by the following flow of files and includes:</strong></p>
					<hr/>
					<div class="alert example-info readmore">
						<h5>System Files</h5>
						<p><strong>Index.php</strong> is the access point to pines. Every action is ran through this point of entry.</p>
						<span class="readless fade">
							<p><strong>phpcache.php</strong> is immediately included to determine if the requested option/action combination should be cached.</p>
							<p><strong>load inits/run action</strong> is the standard process where pines does all of its initial thinking and then processes the action to get the appropriate output.</p>
						</span>
					</div>
					<div class="alert alert-info example-info readmore">
						<h5>Caching Mechanisms</h5>
						<p><strong>get cached file</strong> is responsible for maintaining the session time, notices, errors, and adjusting http/https links. This technically happens inside of the phpcache.php file.</p>
						<span class="readless fade">
							<p><strong>generate cache file</strong> happens around i90 render (the last phase of loading pines and where output buffering is on) and was set by phpcache.php to make a copy of the output for later use.</p>
						</span>
					</div>
					<div class="alert alert-success example-info readmore">
						<h5>Output</h5>
						<p><strong>echo output</strong> occurs from either obtaining the cached html file, or constructing it from the inits. 
						<span class="readless fade">
							Notice the arrows emphasizing that the output is echoed from normal inits OR right after generating the cache file.
						</span>
						</p>
					</div>
				</div>
				<div class="span9">
					<div class="text-center"><img src="/components/com_cache/includes/cache-flowchart.jpg" style=""></div>
				</div>
			</div>
			<div class="example hide">
				<h1 class="text-center dotted"><span>Cache Storage Architecture</span></h1>
			</div>
			<div class="row-fluid example hide">
				<div class="span3">
					<h3>Example: </h3>
					<p>If the directive were:</p>
					<table class="table table-bordered">
						<tbody>
							<tr>
								<td>Component</td>
								<td>Home</td>
							</tr>
							<tr>
								<td>Action</td>
								<td>N/A</td>
							</tr>
							<tr>
								<td>Cache Query</td>
								<td>On</td>
							</tr>
							<tr>
								<td>Cache Logged In</td>
								<td>On</td>
							</tr>
							<tr>
								<td>Domain</td>
								<td>Domain 1</td>
							</tr>
						</tbody>
					</table>
					<p><strong>And so, home.html is cached on domain 1 when:</strong></p>
					<ol class="example-list">
						<li>No one is logged in</li>
						<li>No one is logged in AND there is a unique query string.</li>
						<li>Someone with specific abilities is logged in. (Multiple ability folders could exist)</li>
						<li>Someone with specific abilities is logged in AND there is a unique query string.</li>
					</ol>
				</div>
				<div class="span9">
					<div class="text-center"><img src="/components/com_cache/includes/flowchart.jpg" style=""></div>
				</div>
			</div>
		</div>
	</div>
	<br/>
	<div class="row-fluid">
		<div class="span10 offset1">
			<?php if (!isset($this->cacheoptions)) { ?>
				<h1 class="text-center" style="margin-top: 0; font-weight: normal;">
					<strong>No cache options file exists.</strong>
					Do you want to 
					<a href="javascript:void(0);" class="show-import">import</a>
					or <a href="<?php echo pines_url('com_cache', 'manager', array('use_generic' => 'true')); ?>">use the default</a>?
				</h1>
				<div class="show-import-table hide">
					<hr/>
					<div class="text-center alert alert-info">
						<h3 style="font-weight:normal;">To import click <strong>edit</strong>, type in the <strong>path</strong>, and <strong>save</strong>.</h3>
					</div>
					<table class="table table-bordered">
						<tbody>
							<tr>
								<td><strong>Import Cache Options</strong></td>
								<td>
									<input class="full-field cache-setting" disabled="disabled" name="import" type="text" placeholder="Import Cache Options" data-orig=""/>
								</td>
								<td style="width: 100px;"><button class="btn btn-block edit-setting-btn import" type="button"><i class="icon-pencil"></i> Edit</button></td>
							</tr>
						</tbody>
					</table>
					<div class="text-center">
						Remember to import a file that is in the correct cacheoptions.php format. <br/>
						You can use the default cacheoptions file instead. Importing is ideal for migrating or copying cacheoptions from another installation.
					</div>
				</div>
			<?php } else { ?>
			<h3>Cache Settings</h3>
			<hr/>
			<table class="table table-bordered cache-settings">
				<tbody>
					<tr>
						<td colspan="2"><strong>Cache On</strong></td>
						<td>
							<select class="full-field cache-setting" name="cache_on" disabled="disabled" data-orig="<?php echo ($cache_on) ? 'On': 'Off'; ?>">
								<option value="On" <?php echo ($cache_on) ? 'selected="selected"': '' ?>>On</option>
								<option value="Off" <?php echo (!$cache_on) ? 'selected="selected"': '' ?>>Off</option>
							</select>	
						</td>
						<td style="width: 100px;"><button class="btn btn-block edit-setting-btn" type="button"><i class="icon-pencil"></i> Edit</button></td>
					</tr>
					<tr>
						<td><strong>Parent Directory</strong></td>
						<td class="text-center" style="vertical-align:middle;"><span class="edit-helper" data-title="Security Alert: Make sure this directory is a level up from your public_html folder so that these files cannot be accessed directly!"><i class="icon-warning-sign"></i></span></td>
						<td>
							<input class="full-field cache-setting" disabled="disabled" name="parent_directory" type="text" placeholder="Cache Directory" <?php echo (!empty($parent_directory)) ? 'value="'.$parent_directory.'" data-orig="'.$parent_directory.'"' : 'data-orig=""';?>/>
						</td>
						<td style="width: 100px;"><button class="btn btn-block edit-setting-btn" type="button"><i class="icon-pencil"></i> Edit</button></td>
					</tr>
					<tr>
						<td><strong>Import Cache Options</strong></td>
						<td class="text-center" style="vertical-align:middle;"><span class="edit-helper" data-title="Importing a file will override your existing cache options file. Be careful! Provide path and file name."><i class="icon-question-sign"></i></span></td>
						<td>
							<input class="full-field cache-setting" disabled="disabled" name="import" type="text" placeholder="Import Cache Options" data-orig=""/>
						</td>
						<td style="width: 100px;"><button class="btn btn-block edit-setting-btn import" type="button"><i class="icon-pencil"></i> Edit</button></td>
					</tr>
					<tr>
						<td><strong>Delete Cache Options</strong></td>
						<td class="text-center" style="vertical-align:middle;"><span class="edit-helper" data-title="You may just want to turn off caching, but this is a fast way to delete all cached files as well and the parent directory. You could restore the default or import options after doing this."><i class="icon-question-sign"></i></span></td>
						<td>
							<select name="delete_cacheoptions" data-orig="" class="cache-setting full-field" disabled="disabled">
								<option value=""></option>
								<option value="<?php echo htmlspecialchars($parent_directory); ?>">Yes, DELETE!</option>
							</select>
						</td>
						<td style="width: 100px;"><button class="btn btn-block edit-setting-btn" type="button"><i class="icon-pencil"></i> Edit</button></td>
					</tr>
					<tr>
						<td><strong>Manange Global Exceptions</strong></td>
						<td class="text-center" style="vertical-align:middle;"><span class="edit-helper" data-title="Use this for certain groups or users that should not use caching at all."><i class="icon-question-sign"></i></span></td>
						<td style="vertical-align:middle;">
							List of groups and/or users to exclude from caching all together.
						</td>
						<td style="width: 100px;"><button class="btn btn-block global-exceptions-btn" type="button"><i class="icon-pencil"></i> Edit</button></td>
					</tr>
					<tr>
						<td><strong>Pinlock Protected Actions</strong></td>
						<td class="text-center" style="vertical-align:middle;"><span class="edit-helper" data-title="Pinlocking will not be accessible on cached pages."><i class="icon-question-sign"></i></span></td>
						<td style="vertical-align:middle;" colspan="2">
							Remember not to cache pinlock protected actions. These are your pin-locked actions: <br/>
							<strong><?php echo implode(', ', $pines->config->com_pinlock->actions);?></strong>
						</td>
					</tr>
				</tbody>
			</table>
			<h3>Current Cache Directives</h3>
			<div class="pull-left"><small>There are <strong><span class="num-directives"><i class="icon-spinner icon-spin"></i></span> Directives</strong> in total.</small></div>
			<div class="pull-right" style="padding: 0 5px;"><strong><a href="<?php echo pines_url('com_cache', 'manager', array('getfilecount' => 'true')); ?>">Show file counts for each directive.</a></strong>
			<i class="edit-helper icon-question-sign" data-placement="left" data-title="Loading the file counts could take a while, depending on how many cached files there are, so getting the count per directive is now only performed when you click here."></i> 
			</div>
			<hr/>
			<table class="table table-bordered table-hover directives-table">
				<thead>
					<tr>
						<th>Component</th>
						<th>Action</th>
						<th class="hide-small" colspan="2">Cache Query</th>
						<th class="hide-small" colspan="2">Cache Logged In</th>
						<th class="hide-small">Cache Expire Time</th>
						<th class="hide-small">Domain</th>
						<th>Refresh Cache</th>
					</tr>
				</thead>
				<tbody>
					<?php 
					foreach($cachelist as $component => $cur_info) {
						$first_time_component = true;
						foreach ($cur_info as $action => $cur_domains) { 
							foreach ($cur_domains as $cur_domain => $cur_options) {
								$name = (($component.'/'.$action) == '/') ? 'home' : (($action == '') ? $component : ($component.'/'.$action));
								$file_name = ((($component.'.'.$action) == '.') ? 'home' : (($action == '') ? $component : ($component.'.'.$action))).'.html';
								
								if ($get_file_count) {
									$use_file_name = preg_replace('#/#', '.', $file_name);
									$domain_files = glob($parent_directory.'*/'.$use_file_name); // Domain -> File
									$query_or_ability_files = glob($parent_directory.'*/*/'.$use_file_name); // Domain -> Query OR Ability hashes -> File
									$query_and_ability_files = glob($parent_directory.'*/*/*/'.$use_file_name); // Domain -> Query/Ability hashes -> File
									$file_count = count(array_merge($domain_files, $query_or_ability_files, $query_and_ability_files));
								}
								if ($first_time_component) {
									$first_time_component = false;
									$use_tr_line = true;
								} else {
									$use_tr_line = false;
								}
						?>
						<tr class="<?php echo ($cur_options['disabled']) ? 'well' : ''; echo ($use_tr_line) ? ' tr-lined' : ''; ?>">
							<td class="edit-directive component"><?php echo ($name == 'home') ? 'Home' : htmlspecialchars($component); ?></td>
							<td class="edit-directive action"><?php echo htmlspecialchars($action); ?></td>
							<td class="edit-directive cachequery hide-small" <?php echo ($cur_options['cachequery']) ? '' : 'colspan="2"'; ?>>
								<?php echo ($cur_options['cachequery']) ? 'On' : 'Off';
								if (!$cur_options['cachequery'] && !empty($cur_options['exceptions'])) { ?>
									<span class="exceptions-span hide" data-exceptions="<?php echo htmlspecialchars(json_encode($cur_options['exceptions'])); ?>"></span>
								<?php } ?>
							</td>
							<?php if ($cur_options['cachequery']) { ?>
							<td class="text-center hide-small"><button class="btn btn-mini exception-btn <?php echo (!empty($cur_options['exceptions'])) ? (($cur_options['disabled']) ? 'active' : 'btn-success') : ''; ?>" data-name="<?php echo htmlspecialchars($name); ?>" data-component="<?php echo htmlspecialchars($component); ?>" data-action="<?php echo htmlspecialchars($action); ?>" data-domain="<?php echo htmlspecialchars($cur_domain);?>" data-exceptions="<?php echo (empty($cur_options['exceptions'])) ? '' : htmlspecialchars(json_encode($cur_options['exceptions'])); ?>" data-users="<?php echo (empty($cur_options['unique_users'])) ? '' : htmlspecialchars(json_encode($cur_options['unique_users'])); ?>">Exceptions</button></td>
							<?php } ?>
							<td class="edit-directive cacheloggedin hide-small" <?php echo ($cur_options['cacheloggedin']) ? '' : 'colspan="2"'; ?>>
								<?php echo ($cur_options['cacheloggedin']) ? 'On' : 'Off';
								if (!$cur_options['cacheloggedin'] && !empty($cur_options['unique_users'])) { ?>
									<span class="unique_users-span hide" data-users="<?php echo htmlspecialchars(json_encode($cur_options['unique_users'])); ?>" data-all="<?php echo ($cur_options['all_unique'] == true) ? 'true' : 'false'; ?>"></span>
								<?php } ?>
							</td>
							<?php if ($cur_options['cacheloggedin']) { ?>
							<td class="text-center hide-small"><button class="btn btn-mini unique-users-btn <?php echo (!empty($cur_options['unique_users']) || $cur_options['all_unique']) ? (($cur_options['disabled']) ? 'active' : 'btn-success') : ''; ?>" data-name="<?php echo htmlspecialchars($name); ?>" data-component="<?php echo htmlspecialchars($component); ?>" data-action="<?php echo htmlspecialchars($action); ?>" data-domain="<?php echo htmlspecialchars($cur_domain);?>" data-exceptions="<?php echo (empty($cur_options['exceptions'])) ? '' : htmlspecialchars(json_encode($cur_options['exceptions'])); ?>" data-users="<?php echo (empty($cur_options['unique_users'])) ? '' : htmlspecialchars(json_encode($cur_options['unique_users'])); ?>" data-all="<?php echo ($cur_options['all_unique'] == true) ? 'true' : 'false'; ?>">Unique Users</button></td>
							<?php } ?>
							<td class="edit-directive cachetime hide-small"><?php echo htmlspecialchars($cur_options['time']); ?></td>
							<td class="edit-directive domain hide-small"><?php echo htmlspecialchars($cur_domain); ?></td>
							<td class="text-center <?php echo ($cur_options['disabled']) ? 'edit-directive' : ''; ?>">
								<div style="position: relative;">
								<?php if ($cur_options['disabled']) { ?>
								<span class="pull-left" data-name="<?php echo htmlspecialchars($name); ?>">disabled</span>
									<?php if ($get_file_count) { ?>
									<span class="badge" style="position: absolute; right: 5px;"><?php echo $file_count; ?></span>
									<?php } ?>
								<?php } else if (count($this->domains) == 0) { ?>
								<span data-name="<?php echo htmlspecialchars($name); ?>">No Files</span>
								<?php } else { ?>
								<button type="button" class="refresh-item btn-mini btn" data-name="<?php echo htmlspecialchars($name); ?>" data-filename="<?php echo ($file_name); ?>"><i class="icon-refresh"></i></button>
								<?php if ($get_file_count) { ?>
								<span class="badge <?php echo ($file_count == 0) ? 'badge-info' : 'badge-success'; ?>" style="position: absolute; right: 5px;"><?php echo $file_count; ?></span>
								<?php } } ?>
								</div>
							</td>
						</tr>
					<?php }
						} 
					} ?>
						<tr class="hide-small add-parent">
							<td>
								<select name="component" class="add full-field">
									<option value="">Component</option>
									<option value="Home">Home</option>
									<?php 
									foreach ($pines->components as $cur_component ) { 
										if (!preg_match('/com_/', $cur_component))
											continue;?>
										<option value="<?php echo htmlspecialchars($cur_component);?>"><?php echo htmlspecialchars($cur_component);?></option>
									<?php } ?>
								</select>
							</td>
							<td><input class="add full-field" placeholder="Action" type="text" name="action"/></td>
							<td colspan="2">
								<select class="add full-field" name="cachequery">
									<option value=""></option>
									<option value="On">On</option>
									<option value="Off">Off</option>
								</select>	
							</td>
							<td colspan="2">
								<select class="add full-field" name="cacheloggedin">
									<option value=""></option>
									<option value="On">On</option>
									<option value="Off">Off</option>
								</select>	
							</td>
							<td><input class="add full-field" placeholder="Time (seconds)" type="text" name="cachetime"/></td>
							<td><input class="add full-field" placeholder="Domain" type="text" name="domain"/></td>
							<td class="text-center"><button class="btn btn-info submit-new" type="button"><i class="icon-plus"></i> Add</button></td>
						</tr>
						<tr class="show-small">
							<td colspan="7"><button class="show-add-modal btn btn-success" type="button" style="width: 100%; display: block; padding: 10px;"><i class="icon-plus"></i> New Directive</button></td>
						</tr>
				</tbody>
			</table>
			<div class="text-error add-info text-center hide add-info-after-table">You cannot create a component/action combo that already exists, nor can you add a specific domain to a component/action combo that is set to "all" domains. Also, you cannot cache the cache manager.</div>
			<div class="item modal hide fade in" data-backdrop="static" data-keyboard="false">
				<div class="modal-header">
					<h4 style="text-transform: uppercase; text-align: center;">Refresh Cache <span class="item-name" style="color: #339533;"></span></h4>
				</div>
				<div class="modal-body">
					<h3 class="text-center">Refresh on Domain</h3>
					<div class="control-group">
						<div class="controls" style="text-align:center;">
							<select name="refresh_domain">
								<?php if (count($this->domains) > 1) { ?>
								<option value=""></option>
								<option value="all">All Domains</option>
								<?php } if (!empty($this->domains)) { foreach ($this->domains as $cur_domain) { ?>
								<option value="<?php echo htmlspecialchars($cur_domain[0]); ?>"><?php echo htmlspecialchars($cur_domain[0]); ?></option>
								<?php } } ?>
							</select>
							<i class="edit-helper icon-question-sign" data-placement="left" data-title="Either perform refresh action on all domains (if applicable) or do one at a time."></i>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<input type="hidden" name="file_name" value=""/>
					<a class="btn" data-dismiss="modal" href="javascript:void(0);">Cancel</a>
					<a class="btn <?php echo ( count($this->domains) > 1) ? 'btn-info' : 'btn-success'; ?> refresh-submit" href="javascript:void(0);">Refresh</a>
				</div>
			</div>
			<div class="edit-directive modal hide fade in" data-backdrop="static" data-keyboard="false">
				<div class="modal-header">
					<h4 class="text-center" style="text-transform: uppercase;">Edit Directive <span class="item-name" style="color: #339533;"></span></h4>
					<div class="text-center" style="font-size: .7em;">Remember, you must delete directives with the domain set to 'all' in order to make a specific domain directive.</div>
				</div>
				<div class="modal-body">
					<table class="table table-bordered table-hover table-condensed" style="margin: auto;">
						<tbody>
							<tr>
								<td class="edit-name">Component</td>
								<td><input name="component" type="text" value="component" disabled="disabled" class="full-field"/></td>
								<td class="text-center" style="vertical-align:middle;"><i class="edit-helper icon-question-sign" data-placement="left" data-title="You must add a new directive and delete this one to 'Change' the component or action."></i></td>
							</tr>
							<tr>
								<td class="edit-name">Action</td>
								<td><input name="action" type="text" value="action" disabled="disabled" class="full-field"/></td>
								<td class="text-center" style="vertical-align:middle;"><i class="edit-helper icon-question-sign" data-placement="left" data-title="You must add a new directive and delete this one to 'Change' the component or action."></i></td>
							</tr>
							<tr>
								<td class="edit-name">Cache-Query</td>
								<td colspan="2">
									<select class="check-edit full-field" name="cachequery">
										<option value=""></option>
										<option value="On">On</option>
										<option value="Off">Off</option>
									</select>	
								</td>
							</tr>
							<tr>
								<td class="edit-name">Cache Logged-In</td>
								<td colspan="2">
									<select class="check-edit full-field" name="cacheloggedin">
										<option value=""></option>
										<option value="On">On</option>
										<option value="Off">Off</option>
									</select>	
								</td>
							</tr>
							<tr>
								<td class="edit-name">Cache Expire Time</td>
								<td colspan="2"><input class="check-edit full-field" name="cachetime" type="number" value="18000"/></td>
							</tr>
							<tr>
								<td class="edit-name">Apply to Domain(s)</td>
								<td><input class="check-edit full-field" name="domain" type="text" value="all"/></td>
								<td class="text-center" style="vertical-align:middle;"><i class="edit-helper icon-question-sign" data-placement="left" data-title="Directive will only apply to domains specified. Put all or one, or a comma separated list."></i></td>
							</tr>
							<tr>
								<td class="edit-name"><span class="disable-directive-name">Disable</span> Directive</td>
								<td>
									<button class="btn btn-warning disable-directive-btn btn-block"><i class="icon-ban-circle"></i> <span class="disable-directive-button-name">Only Disable</span> Directive</button>
								</td>
								<td class="text-center" style="vertical-align:middle;"><i class="edit-helper icon-question-sign" data-placement="left" data-title="You can safely add this directive again without having to re-cache all files. Removing directive will disable the use of caching for this directive."></i></td>
							</tr>
							<tr>
								<td class="edit-name">Delete Directive</td>
								<td>
									<button class="btn btn-danger delete-directive-btn btn-block"><i class="icon-remove"></i> Delete Directive + Files</button> 
								</td>
								<td class="text-center" style="vertical-align:middle;"><i class="edit-helper icon-question-sign" data-placement="left" data-title="You won't be able to undo this action. You can recreate the directive but all files will cache again."></i></td>
							</tr>
						</tbody>
					</table><br/>
					<div class="save-info text-center text-success hide">Press <strong>Save</strong> to Submit Changes. Changes will take affect immediately.</div>
				</div>
				<div class="modal-footer">
					<input type="hidden" name="manage" value=""/>
					<a class="btn edit-cancel" data-dismiss="modal" href="javascript:void(0);">Cancel</a>
					<a class="btn btn-info item-submit" href="javascript:void(0);">Save</a>
				</div>
			</div>
			<div class="add-modal add-parent modal hide fade in" data-backdrop="static" data-keyboard="false">
				<div class="modal-header">
					<h4 class="text-center" style="text-transform: uppercase;"><span class="item-name" style="color: #339533;">Add</span> Directive</h4>
				</div>
				<div class="modal-body">
					<table class="table table-bordered table-hover" style="margin: auto;">
						<tbody>
							<tr>
								<td colspan="2">
									<select name="component" class="add full-field">
										<option value="">Component</option>
										<option value="Home">Home</option>
										<?php 
										foreach ($pines->components as $cur_component ) { 
											if (!preg_match('/com_/', $cur_component))
												continue;?>
											<option value="<?php echo htmlspecialchars($cur_component);?>"><?php echo htmlspecialchars($cur_component);?></option>
										<?php } ?>
									</select>
								</td>
							</tr>
							<tr>
								<td colspan="2"><input class="add full-field" placeholder="Action" type="text" name="action"/></td>
							</tr>
							<tr>
								<td>Cache Query</td>
								<td>
									<select class="add full-field" name="cachequery">
										<option value=""></option>
										<option value="On">On</option>
										<option value="Off">Off</option>
									</select>
								</td>
							</tr>
							<tr>
								<td>Cache Logged-in</td>
								<td>
									<select class="add full-field" name="cacheloggedin">
										<option value=""></option>
										<option value="On">On</option>
										<option value="Off">Off</option>
									</select>
								</td>
							</tr>
							<tr>
								<td colspan="2"><input class="add full-field" placeholder="Time (seconds)" type="text" name="cachetime"/></td>
							</tr>
							<tr>
								<td colspan="2"><input class="add full-field" placeholder="Domain" type="text" name="domain"/></td>
							</tr>
						</tbody>
					</table>
					<div class="text-error add-info text-center hide">You cannot create a component/action combo that already exists, nor can you add a specific domain to a component/action combo that is set to "all" domains. Also, you cannot cache the cache manager.</div>
				</div>
				<div class="modal-footer">
					<a class="btn add-cancel" data-dismiss="modal" href="javascript:void(0);">Cancel</a>
					<a class="btn btn-info submit-new" href="javascript:void(0);"><i class="icon-plus"></i> Add</a>
				</div>
			</div>
			<div class="exception modal hide fade in" data-backdrop="static" data-keyboard="false">
				<div class="modal-header">
					<h4 class="text-center" style="text-transform: uppercase;">Query Exceptions For <span class="item-name" style="color: #339533;"></span></h4>
				</div>
				<div class="modal-body">
					<h3 class="text-center"><a href="javascript:void(0);" class="exception-readmore-link">Learn about exceptions</a> before using them.</h3>
					<p class="well hide exception-readmore" style="padding: 5px;"><small>Exceptions pertain only to cache directives with the cache query set to On. 
						These exceptions will check if a variable in post or get data is either set,
						or if the value of the variable is equal to the provided exception.
						<br/><br/>When these conditions are met, caching will <strong>be skipped</strong>. This
						is useful for when you do not want caching on some widgets on the dashboard, 
						or some product pages, or some content pages.</small>
					</p>
					<hr/>
					<h3>Isset</h3>
					<table class="table isset table-bordered table-hover" style="margin: auto;">
						<thead>
							<th>Variable Name</th>
							<th>Remove</th>
						</thead>
						<tbody></tbody>
					</table>
					<table  class="table isset-form table-bordered table-hover" style="margin: 10px 0;">
						<tbody>
							<tr>
								<td>
									<input type="text" name="add_isset" placeholder="variable name"/>
								</td>
								<td class="text-center" style="width: 55px; vertical-align:middle;"><button class="btn btn-info add-isset-btn"><i class="icon-plus"></i></button></td>
							</tr>
						</tbody>
					</table>
					<hr/>
					<h3>Value</h3>
					<table class="table value table-bordered table-hover" style="margin: auto;">
						<thead>
							<th>Variable Name</th>
							<th>Value</th>
							<th>Remove</th>
						</thead>
						<tbody></tbody>
					</table>
					<table  class="table value-form table-bordered table-hover" style="margin: 10px 0;">
						<tbody>
							<tr>
								<td>
									<input type="text" name="add_value_name" placeholder="variable name"/>
								</td>
								<td>
									<input type="text" name="add_value" placeholder="variable value"/>
								</td>
								<td class="text-center" style="width: 55px; vertical-align:middle;"><button class="btn btn-info add-value-btn"><i class="icon-plus"></i></button></td>
							</tr>
						</tbody>
					</table>
					<br/>
				</div>
				<div class="modal-footer">
					<input type="hidden" name="component" value=""/>
					<input type="hidden" name="caction" value=""/>
					<input type="hidden" name="domain" value=""/>
					<input type="hidden" name="exceptions" value=""/>
					<input type="hidden" name="orig_exceptions" value=""/>
					<a class="btn cancel-exceptions" data-dismiss="modal" href="javascript:void(0);">Cancel</a>
					<a class="btn btn-info submit-exceptions" href="javascript:void(0);"><i class="icon-plus"></i> Save</a>
				</div>
			</div>
			<div class="unique-users-modal modal hide fade in" data-backdrop="static" data-keyboard="false">
				<div class="modal-header">
					<h4 class="text-center" style="text-transform: uppercase;">Unique user cache for <span class="item-name" style="color: #339533;"></span></h4>
				</div>
				<div class="modal-body">
					<h3 class="text-center"><a href="javascript:void(0);" class="exception-readmore-link">Read about Unique User Cache</a> before using it.</h3>
					<p class="well hide exception-readmore" style="padding: 5px;"><small>Directives with logged in cache set to On will create folders based
						on ability hashes. Sometimes different users require unique caches, despite being in the same ability hash (same groups/abilities), <strong>so
						providing user names here will ensure that the user gets their own unique cache [hash] for this directive.</strong> <br/><br/>
						If you had many customers, you would not want too many unique caches, but if you have certain employees that need unique ability hashes
						without having to get them unique abilities, you can set that here.</small>
					</p>
					<hr/>
					<h3>Users</h3>
					<div class="btn-group text-center apply-to-container">
						<button class="btn apply-to-hash">Apply Unique Hash To</button>
						<button class="btn apply-btn apply-to-all btn-info">All Users</button>
						<button class="btn apply-btn apply-to-users btn-info">Users Below</button>
					</div>
					<table class="table users table-bordered table-hover" style="margin: auto;">
						<thead>
							<th>Username</th>
							<th>Remove</th>
						</thead>
						<tbody></tbody>
					</table>
					<table  class="table users-form table-bordered table-hover" style="margin: 10px 0;">
						<tbody>
							<tr>
								<td>
									<input type="text" name="add_user" placeholder="username"/>
								</td>
								<td class="text-center" style="width: 55px; vertical-align:middle;"><button class="btn btn-info add-user-btn"><i class="icon-plus"></i></button></td>
							</tr>
						</tbody>
					</table>
					<br/>
				</div>
				<div class="modal-footer">
					<input type="hidden" name="component" value=""/>
					<input type="hidden" name="caction" value=""/>
					<input type="hidden" name="domain" value=""/>
					<input type="hidden" name="all_unique" value=""/>
					<input type="hidden" name="orig_all_unique" value=""/>
					<input type="hidden" name="unique_users" value=""/>
					<input type="hidden" name="orig_unique_users" value=""/>
					<a class="btn cancel-users" data-dismiss="modal" href="javascript:void(0);">Cancel</a>
					<a class="btn btn-info submit-users" href="javascript:void(0);"><i class="icon-plus"></i> Save</a>
				</div>
			</div>
			<br/>
			<h3>Current Domains</h3>
			<hr/>
			<?php if (count($this->domains) > 0) { ?>
			<table class="table table-hover table-bordered files-table">
				<thead>
					<th>Domain</th>
					<th>File Count</th>
					<th>Details <span data-title="View file architecture for this domain." class="edit-helper pull-right" data-original-title=""><i class="icon-question-sign"></i></span></th>
					<th>Refresh <span data-title="Clicking this will refresh ALL cached files (delete them) for this domain." class="edit-helper pull-right" data-original-title=""><i class="icon-warning-sign"></i></span></th>
				</thead>
				<tbody>
					<?php foreach ($this->domains as $cur_domain) { ?>
					<tr>
						<td><?php echo htmlspecialchars($cur_domain[0]);?></td>
						<td><span class="badge <?php echo ($cur_domain[1] == 0) ? 'badge-info' : 'badge-success'; ?>"><?php echo htmlspecialchars($cur_domain[1]);?></span></td>
						<td style="width: 10%;"><button type="button" class="view-domain btn btn-block" data-domain="<?php echo htmlspecialchars($cur_domain[0]); ?>"><i class="icon-eye-open"></i></button></td>
						<td style="width: 10%;"><button type="button" class="refresh-domain btn btn-block" data-domain="<?php echo htmlspecialchars($cur_domain[0]); ?>"><i class="icon-refresh"></i></button></td>
					</tr>
					<?php } ?>
				</tbody>
			</table>
			<div class="details modal hide fade in" data-backdrop="static" data-keyboard="false">
				<div class="modal-header">
					<h4 class="text-center" style="text-transform: uppercase;">Explore Files on <span class="item-name" style="color: #339533;"></span></h4>
				</div>
				<div class="modal-body">
					<h2>View Files</h2>
					<div class="files-container">
						<div class="text-center">Select a folder from which to view files.</div>
					</div>
					<hr/>
					<h2>Browse Folders</h2>
					<div class="jstree-container">

					</div>
					<br/>
				</div>
				<div class="modal-footer">
					<a class="btn cancel-exceptions" data-dismiss="modal" href="javascript:void(0);">Done</a>
				</div>
			</div>
			
			<h3>Look Up User Hash</h3>
			<hr/>
			<table class="table table-bordered lookup-table">
				<thead>
					<th colspan="2">Username</th>
					<th>
						Ability Hash
						<span data-original-title="" class="edit-helper pull-right" data-title="These hashes will match many users, like customers, so be careful when refreshing the cache by ability."><i class="icon-warning-sign"></i></span>
						<span class="pull-right badge hide ability-badge" style="margin-right:4px;"></span>
					</th>
					<th>
						Unique Hash
						<span data-original-title="" class="edit-helper pull-right" data-title="Unique hash means refreshing will only affect 1 unique user."><i class="icon-info-sign"></i></span>
						<span class="pull-right badge hide unique-badge" style="margin-right:4px;"></span>
					</th>
				</thead>
				<tbody>
					<tr>
						<td><input type="text" name="check_user_hash" class="full-field" placeholder="Type Username"/></td>
						<td class="text-center" style="width: 40px;"><button class="btn btn-info lookup-btn"><i class="icon-search"></i></button></td>
						<td class="hash-result ability text-center" data-orig="Type a username to get hash." style="width: 35%;">Type a username to get hash.</td>
						<td class="hash-result unique text-center" data-orig="Unique hash will display if applicable." style="width: 35%;">Unique hash will display if applicable.</td>
					</tr>
				</tbody>
			</table>
			<?php } else { ?>
			<h3 class="text-center" style="margin-top: 0; font-weight: normal;">
				There are no cached files yet. Turn caching on, create directives, and watch the magic happen!
			</h3>
			<?php } ?>
			<div class="global-exceptions-modal modal hide fade in" data-backdrop="static" data-keyboard="false">
				<div class="modal-header">
					<h4 class="text-center" style="text-transform: uppercase;">Global Exceptions</h4>
				</div>
				<div class="modal-body">
					<p class="alert alert-info"><i class="icon-info-sign"></i> Use the group-name and not display name for groups!</p>
					<table class="table table-bordered table-hover" style="margin: auto;">
						<thead>
							<th style="width: 50%;" colspan="2">Users</th>
							<th style="width: 50%;" colspan="2">Groups</th>
						</thead>
						<tbody>
							<?php if ($c_users != 0 || $c_groups != 0) { 
							for ($i = 0; $i < $max_exceptions; $i++) { 
								$cur_user = isset($global_exceptions['users'][$i]) ? $global_exceptions['users'][$i] : null;
								$cur_group = isset($global_exceptions['groups'][$i]) ? $global_exceptions['groups'][$i] : null; 
								?>
								<tr class="exception-row">
									<?php if (($max_type == 'both') || ($cur_group && $cur_user)) { ?>
									<td colspan="2" class="remove-exception user" style="width: 50%;"><?php echo htmlspecialchars($global_exceptions['users'][$i]); ?></td>
									<td colspan="2" class="remove-exception group" style="width: 50%;"><?php echo htmlspecialchars($global_exceptions['groups'][$i]); ?></td>
									<?php } else { ?>
										<td colspan="2" class="user <?php echo ($cur_user ? 'remove-exception' : 'dull'); ?>" style="width: 50%;"><?php echo ($cur_user ? $cur_user : ''); ?></td>
										<td colspan="2" class="group <?php echo ($cur_group ? 'remove-exception' : 'dull'); ?>" style="width: 50%;"><?php echo ($cur_group ? $cur_group : ''); ?></td>
									<?php } ?>
								</tr>
							<?php } } ?>
								<tr class="exception-new">
									<td class="dull"><input name="excep_user" type="text" class="global-exceptions-input" placeholder="Username"/></td>
									<td class="dull"><button class="btn add-exception" type="button" data-input="excep_user">Add</button></td>
									<td class="dull"><input name="excep_group" type="text" class="global-exceptions-input" placeholder="Group name"/></td>
									<td class="dull"><button class="btn add-exception" type="button" data-input="excep_group">Add</button></td>
								</tr>
						</tbody>
					</table>
				</div>
				<div class="modal-footer">
					<div style="padding: 4px; font-weight:bold; " class="pull-left text-success success hide"><i class="icon-ok"></i> Saved!</div>
					<div style="padding: 4px; font-weight:bold; " class="pull-left text-success saving hide"><i class="icon-spin icon-spinner"></i> Saving ...</div>
					<div style="padding: 4px; font-weight:bold; " class="pull-left text-error error hide"><i class="icon-remove"></i> Error</div>
					<a class="btn" data-dismiss="modal" href="javascript:void(0);">Done</a>
				</div>
			</div>
			<?php } ?>
		</div>
	</div>
	<div class="edit-symbol hide"><i class="icon-pencil"></i></div>
	<div class="cache-urls hide">
		<div class="save-edit-url"><?php echo (pines_url('com_cache', 'save', array('edit_directive' => 'true'))); ?></div>
		<div class="cache-manager-url"><?php echo (pines_url('com_cache', 'manager')); ?></div>
		<div class="save-exception-url"><?php echo (pines_url('com_cache', 'save', array('save_global_exceptions' => 'true'))); ?></div>
		<div class="domain-explore-url"><?php echo (pines_url('com_cache', 'domain_explore')); ?></div>
		<div class="save-url"><?php echo (pines_url('com_cache', 'save')); ?></div>
		<div class="check-user-hash-url"><?php echo (pines_url('com_cache', 'lookup')); ?></div>
		<div class="refresh-url"><?php echo (pines_url('com_cache', 'refresh')); ?></div>
	</div>
</div>