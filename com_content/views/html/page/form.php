<?php
/**
 * Provides a form for the user to edit an page.
 *
 * @package Pines
 * @subpackage com_content
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');
$this->title = (!isset($this->entity->guid)) ? 'Editing New Page' : 'Editing ['.htmlspecialchars($this->entity->name).']';
$this->note = 'Provide page details in this form.';
$pines->editor->load();
$pines->com_pgrid->load();
$pines->com_ptags->load();
$pines->com_menueditor->load_editor();
?>
<form class="pf-form" method="post" id="p_muid_form" action="<?php echo htmlspecialchars(pines_url('com_content', 'page/save')); ?>">
	<script type="text/javascript">
		// <![CDATA[
		pines(function(){
			$("#p_muid_menu_entries").menueditor({
				disabled_fields: ['link'],
				defaults: {
					name: function(){
						return $("input[name=alias]", "#p_muid_form").val();
					},
					text: function(){
						return $("input[name=name]", "#p_muid_form").val();
					}
				}
			});

			// Conditions
			var conditions = $("#p_muid_form [name=conditions]");
			var conditions_table = $("#p_muid_form .conditions_table");
			var condition_dialog = $("#p_muid_form .condition_dialog");
			var cur_condition = null;

			conditions_table.pgrid({
				pgrid_paginate: false,
				pgrid_toolbar: true,
				pgrid_toolbar_contents : [
					{
						type: 'button',
						text: 'Add Condition',
						extra_class: 'picon picon-document-new',
						selection_optional: true,
						click: function(){
							cur_condition = null;
							condition_dialog.dialog('open');
						}
					},
					{
						type: 'button',
						text: 'Edit Condition',
						extra_class: 'picon picon-document-edit',
						double_click: true,
						click: function(e, rows){
							cur_condition = rows;
							condition_dialog.find("input[name=cur_condition_type]").val(pines.unsafe(rows.pgrid_get_value(1)));
							condition_dialog.find("input[name=cur_condition_value]").val(pines.unsafe(rows.pgrid_get_value(2)));
							condition_dialog.dialog('open');
						}
					},
					{
						type: 'button',
						text: 'Remove Condition',
						extra_class: 'picon picon-edit-delete',
						click: function(e, rows){
							rows.pgrid_delete();
							update_conditions();
						}
					}
				],
				pgrid_view_height: "300px"
			});

			// Condition Dialog
			condition_dialog.dialog({
				bgiframe: true,
				autoOpen: false,
				modal: true,
				width: 500,
				buttons: {
					"Done": function(){
						var cur_condition_type = condition_dialog.find("input[name=cur_condition_type]").val();
						var cur_condition_value = condition_dialog.find("input[name=cur_condition_value]").val();
						if (cur_condition_type == "") {
							alert("Please provide a type for this condition.");
							return;
						}
						if (cur_condition == null) {
							// Is this a duplicate type?
							var dupe = false;
							conditions_table.pgrid_get_all_rows().each(function(){
								if (dupe) return;
								if ($(this).pgrid_get_value(1) == cur_condition_type)
									dupe = true;
							});
							if (dupe) {
								pines.notice('There is already a condition of that type.');
								return;
							}
							var new_condition = [{
								key: null,
								values: [
									pines.safe(cur_condition_type),
									pines.safe(cur_condition_value)
								]
							}];
							conditions_table.pgrid_add(new_condition);
						} else {
							cur_condition.pgrid_set_value(1, pines.safe(cur_condition_type));
							cur_condition.pgrid_set_value(2, pines.safe(cur_condition_value));
						}
						$(this).dialog('close');
					}
				},
				close: function(){
					update_conditions();
				}
			});

			var update_conditions = function(){
				condition_dialog.find("input[name=cur_condition_type]").val("");
				condition_dialog.find("input[name=cur_condition_value]").val("");
				conditions.val(JSON.stringify(conditions_table.pgrid_get_all_rows().pgrid_export_rows()));
			};

			update_conditions();

			condition_dialog.find("input[name=cur_condition_type]").autocomplete({
				"source": <?php echo (string) json_encode((array) array_keys($pines->depend->checkers)); ?>
			});

			$("#p_muid_page_tabs").tabs();
		});
		// ]]>
	</script>
	<div id="p_muid_page_tabs" style="clear: both;">
		<ul>
			<li><a href="#p_muid_tab_general">General</a></li>
			<?php if (($pines->config->com_content->custom_head && gatekeeper('com_content/edithead')) || gatekeeper('com_content/editmeta')) { ?>
			<li><a href="#p_muid_tab_head">Page Head</a></li>
			<?php } ?>
			<li><a href="#p_muid_tab_menu">Menu</a></li>
			<li><a href="#p_muid_tab_categories">Categories</a></li>
			<li><a href="#p_muid_tab_conditions">Conditions</a></li>
			<li><a href="#p_muid_tab_advanced">Advanced</a></li>
		</ul>
		<div id="p_muid_tab_general">
			<div class="pf-element pf-full-width">
				<script type="text/javascript">
					// <![CDATA[
					pines(function(){
						var alias = $("#p_muid_form [name=alias]");
						$("#p_muid_form [name=name]").change(function(){
							if (alias.val() == "")
								alias.val($(this).val().replace(/[^\w\d\s-.]/g, '').replace(/\s/g, '-').toLowerCase());
						}).blur(function(){
							$(this).change();
						}).focus(function(){
							if (alias.val() == $(this).val().replace(/[^\w\d\s-.]/g, '').replace(/\s/g, '-').toLowerCase())
								alias.val("");
						});
					});
					// ]]>
				</script>
				<label>
					<span class="pf-label">Name</span>
					<span style="display: block;" class="pf-group pf-full-width">
						<input class="pf-field ui-widget-content ui-corner-all" style="width: 100%;" type="text" name="name" value="<?php echo htmlspecialchars($this->entity->name); ?>" />
					</span>
				</label>
			</div>
			<div class="pf-element pf-full-width">
				<label>
					<span class="pf-label">Alias</span>
					<span style="display: block;" class="pf-group pf-full-width">
						<input class="pf-field ui-widget-content ui-corner-all" style="width: 100%;" type="text" name="alias" value="<?php echo htmlspecialchars($this->entity->alias); ?>" onkeyup="this.value=this.value.replace(/[^\w\d-.]/g, '_');" />
					</span>
				</label>
			</div>
			<?php if (isset($this->entity->guid)) { ?>
			<div class="date_info" style="float: right; text-align: right;">
				<?php if (isset($this->entity->user)) { ?>
				<div>User: <span class="date"><?php echo htmlspecialchars("{$this->entity->user->name} [{$this->entity->user->username}]"); ?></span></div>
				<div>Group: <span class="date"><?php echo htmlspecialchars("{$this->entity->group->name} [{$this->entity->group->groupname}]"); ?></span></div>
				<?php } ?>
				<div>Created: <span class="date"><?php echo htmlspecialchars(format_date($this->entity->p_cdate, 'full_short')); ?></span></div>
				<div>Modified: <span class="date"><?php echo htmlspecialchars(format_date($this->entity->p_mdate, 'full_short')); ?></span></div>
			</div>
			<?php } ?>
			<div class="pf-element">
				<label><span class="pf-label">Enabled</span>
					<input class="pf-field" type="checkbox" name="enabled" value="ON"<?php echo $this->entity->enabled ? ' checked="checked"' : ''; ?> /></label>
			</div>
			<div class="pf-element">
				<label><span class="pf-label">Show on Front Page</span>
					<select class="pf-field ui-widget-content ui-corner-all" name="show_front_page">
						<option value="null">Use Default</option>
						<option value="true"<?php echo $this->entity->show_front_page === true ? ' selected="selected"' : ''; ?>>Yes</option>
						<option value="false"<?php echo $this->entity->show_front_page === false ? ' selected="selected"' : ''; ?>>No</option>
					</select></label>
			</div>
			<div class="pf-element pf-full-width">
				<span class="pf-label">Tags</span>
				<div class="pf-group">
					<input class="pf-field ui-widget-content ui-corner-all" type="text" name="content_tags" size="24" value="<?php echo htmlspecialchars(implode(',', $this->entity->content_tags)); ?>" />
					<script type="text/javascript">
						// <![CDATA[
						pines(function(){
							$("#p_muid_form [name=content_tags]").ptags();
						});
						// ]]>
					</script>
				</div>
			</div>
			<div class="pf-element pf-heading">
				<h1>Intro</h1>
			</div>
			<div class="pf-element pf-full-width">
				<textarea rows="3" cols="35" class="peditor" style="width: 100%;" name="intro"><?php echo htmlspecialchars($this->entity->intro); ?></textarea>
			</div>
			<div class="pf-element pf-heading">
				<h1>Content</h1>
			</div>
			<div class="pf-element pf-full-width">
				<textarea rows="8" cols="35" class="peditor" style="width: 100%; height: 500px;" name="content"><?php echo htmlspecialchars($this->entity->content); ?></textarea>
			</div>
			<br class="pf-clearing" />
		</div>
		<?php if (($pines->config->com_content->custom_head && gatekeeper('com_content/edithead')) || gatekeeper('com_content/editmeta')) { ?>
		<div id="p_muid_tab_head">
			<?php if (gatekeeper('com_content/editmeta')) { ?>
			<div class="pf-element pf-heading">
				<h1>Meta Tags</h1>
			</div>
			<script type="text/javascript">
				// <![CDATA[
				pines(function(){
					// Meta Tags
					var meta_tags = $("#p_muid_form [name=meta_tags]");
					var meta_tags_table = $("#p_muid_form .meta_tags_table");
					var meta_tag_dialog = $("#p_muid_form .meta_tag_dialog");
					var cur_meta_tag = null;

					meta_tags_table.pgrid({
						pgrid_paginate: false,
						pgrid_toolbar: true,
						pgrid_toolbar_contents : [
							{
								type: 'button',
								text: 'Add Meta Tag',
								extra_class: 'picon picon-document-new',
								selection_optional: true,
								click: function(){
									cur_meta_tag = null;
									meta_tag_dialog.dialog('open');
								}
							},
							{
								type: 'button',
								text: 'Edit Meta Tag',
								extra_class: 'picon picon-document-edit',
								double_click: true,
								click: function(e, rows){
									cur_meta_tag = rows;
									meta_tag_dialog.find("input[name=cur_meta_tag_name]").val(pines.unsafe(rows.pgrid_get_value(1)));
									meta_tag_dialog.find("input[name=cur_meta_tag_value]").val(pines.unsafe(rows.pgrid_get_value(2)));
									meta_tag_dialog.dialog('open');
								}
							},
							{
								type: 'button',
								text: 'Remove Meta Tag',
								extra_class: 'picon picon-edit-delete',
								click: function(e, rows){
									rows.pgrid_delete();
									update_meta_tags();
								}
							}
						],
						pgrid_view_height: "200px"
					});

					// Meta Tag Dialog
					meta_tag_dialog.dialog({
						bgiframe: true,
						autoOpen: false,
						modal: true,
						width: 500,
						buttons: {
							"Done": function(){
								var cur_meta_tag_name = meta_tag_dialog.find("input[name=cur_meta_tag_name]").val();
								var cur_meta_tag_value = meta_tag_dialog.find("input[name=cur_meta_tag_value]").val();
								if (cur_meta_tag_name == "") {
									alert("Please provide a name for this meta_tag.");
									return;
								}
								if (cur_meta_tag == null) {
									var new_meta_tag = [{
										key: null,
										values: [
											pines.safe(cur_meta_tag_name),
											pines.safe(cur_meta_tag_value)
										]
									}];
									meta_tags_table.pgrid_add(new_meta_tag);
								} else {
									cur_meta_tag.pgrid_set_value(1, pines.safe(cur_meta_tag_name));
									cur_meta_tag.pgrid_set_value(2, pines.safe(cur_meta_tag_value));
								}
								$(this).dialog('close');
							}
						},
						close: function(){
							update_meta_tags();
						}
					});

					var update_meta_tags = function(){
						meta_tag_dialog.find("input[name=cur_meta_tag_name]").val("");
						meta_tag_dialog.find("input[name=cur_meta_tag_value]").val("");
						meta_tags.val(JSON.stringify(meta_tags_table.pgrid_get_all_rows().pgrid_export_rows()));
					};

					update_meta_tags();

					meta_tag_dialog.find("input[name=cur_meta_tag_name]").autocomplete({
						"source": <?php echo (string) json_encode(array('description', 'author', 'keywords', 'robots', 'rating', 'distribution')); ?>
					});
				});
				// ]]>
			</script>
			<div class="pf-element pf-full-width">
				<table class="meta_tags_table">
					<thead>
						<tr>
							<th>Name</th>
							<th>Content</th>
						</tr>
					</thead>
					<tbody>
						<?php if (isset($this->entity->meta_tags)) foreach ($this->entity->meta_tags as $cur_value) { ?>
						<tr>
							<td><?php echo htmlspecialchars($cur_value['name']); ?></td>
							<td><?php echo htmlspecialchars($cur_value['content']); ?></td>
						</tr>
						<?php } ?>
					</tbody>
				</table>
				<input type="hidden" name="meta_tags" />
			</div>
			<div class="meta_tag_dialog" style="display: none;" title="Add a Meta Tag">
				<div class="pf-form">
					<div class="pf-element">
						<span class="pf-label">Common Meta Tags</span>
						<span class="pf-note">These tags are commonly used on pages.</span>
						<div class="pf-group">
							<div class="pf-field"><em><?php
							$name_links = array();
							foreach (array('description', 'keywords', 'robots', 'rating', 'distribution') as $cur_name) {
								$name_html = htmlspecialchars($cur_name);
								$name_js = htmlspecialchars(json_encode($cur_name));
								$name_links[] = "<a href=\"javascript:void(0);\" onclick=\"\$('#p_muid_cur_meta_tag_name').val($name_js);\">$name_html</a>";
							}
							echo implode(', ', $name_links);
							?></em></div>
						</div>
					</div>
					<div class="pf-element">
						<label><span class="pf-label">Name</span>
							<input class="pf-field ui-widget-content ui-corner-all" type="text" name="cur_meta_tag_name" id="p_muid_cur_meta_tag_name" size="24" /></label>
					</div>
					<div class="pf-element">
						<label><span class="pf-label">Value</span>
							<input class="pf-field ui-widget-content ui-corner-all" type="text" name="cur_meta_tag_value" size="24" /></label>
					</div>
				</div>
				<br style="clear: both; height: 1px;" />
			</div>
			<?php } if ($pines->config->com_content->custom_head && gatekeeper('com_content/edithead')) { ?>
			<div class="pf-element pf-heading">
				<h1>Custom Head Code</h1>
			</div>
			<div class="pf-element">
				The page head can contain extra CSS and JavaScript files, which
				can improve the page, but also can introduce security
				vulnerabilities. Please be very careful when putting custom code
				into the page head. Custom head code will be loaded when the
				page's full content is shown.
			</div>
			<div class="pf-element">
				<label><span class="pf-label">Enable Custom Head Code</span>
					<input class="pf-field" type="checkbox" name="enable_custom_head" value="ON"<?php echo $this->entity->enable_custom_head ? ' checked="checked"' : ''; ?> /></label>
			</div>
			<div class="pf-element pf-full-width">
				<label><span class="pf-label">Custom Head Code</span>
					<span class="pf-note"><a href="javascript:void(0);" onclick="$('#p_muid_custom_head_help').dialog({width: 800, height: 600, modal: false})">Read Me First</a></span>
					<span class="pf-field pf-full-width"><textarea class="ui-widget-content ui-corner-all" style="width: 100%;" rows="3" cols="35" name="custom_head"><?php echo htmlspecialchars($this->entity->custom_head); ?></textarea></span></label>
			</div>
			<div id="p_muid_custom_head_help" title="Custom Head Code" style="display: none;">
				<style type="text/css">
					/* <![CDATA[ */
					#p_muid_custom_head_help .code_block {
						background: #F6F6F6;
						border: 1px solid #DDDDDD;
						font-size: .9em;
					}
					#p_muid_custom_head_help .highlight {
						padding: 1em;
					}
					<?php /* pygmentize -S manni -f html | sed s/^/\\t\\t\\t\\t\\t\#p_muid_custom_head_help\ / */ ?>
					#p_muid_custom_head_help .hll { background-color: #ffffcc }
					#p_muid_custom_head_help .c { color: #0099FF; font-style: italic } /* Comment */
					#p_muid_custom_head_help .err { color: #AA0000; background-color: #FFAAAA } /* Error */
					#p_muid_custom_head_help .k { color: #006699; font-weight: bold } /* Keyword */
					#p_muid_custom_head_help .o { color: #555555 } /* Operator */
					#p_muid_custom_head_help .cm { color: #0099FF; font-style: italic } /* Comment.Multiline */
					#p_muid_custom_head_help .cp { color: #009999 } /* Comment.Preproc */
					#p_muid_custom_head_help .c1 { color: #0099FF; font-style: italic } /* Comment.Single */
					#p_muid_custom_head_help .cs { color: #0099FF; font-weight: bold; font-style: italic } /* Comment.Special */
					#p_muid_custom_head_help .gd { background-color: #FFCCCC; border: 1px solid #CC0000 } /* Generic.Deleted */
					#p_muid_custom_head_help .ge { font-style: italic } /* Generic.Emph */
					#p_muid_custom_head_help .gr { color: #FF0000 } /* Generic.Error */
					#p_muid_custom_head_help .gh { color: #003300; font-weight: bold } /* Generic.Heading */
					#p_muid_custom_head_help .gi { background-color: #CCFFCC; border: 1px solid #00CC00 } /* Generic.Inserted */
					#p_muid_custom_head_help .go { color: #AAAAAA } /* Generic.Output */
					#p_muid_custom_head_help .gp { color: #000099; font-weight: bold } /* Generic.Prompt */
					#p_muid_custom_head_help .gs { font-weight: bold } /* Generic.Strong */
					#p_muid_custom_head_help .gu { color: #003300; font-weight: bold } /* Generic.Subheading */
					#p_muid_custom_head_help .gt { color: #99CC66 } /* Generic.Traceback */
					#p_muid_custom_head_help .kc { color: #006699; font-weight: bold } /* Keyword.Constant */
					#p_muid_custom_head_help .kd { color: #006699; font-weight: bold } /* Keyword.Declaration */
					#p_muid_custom_head_help .kn { color: #006699; font-weight: bold } /* Keyword.Namespace */
					#p_muid_custom_head_help .kp { color: #006699 } /* Keyword.Pseudo */
					#p_muid_custom_head_help .kr { color: #006699; font-weight: bold } /* Keyword.Reserved */
					#p_muid_custom_head_help .kt { color: #007788; font-weight: bold } /* Keyword.Type */
					#p_muid_custom_head_help .m { color: #FF6600 } /* Literal.Number */
					#p_muid_custom_head_help .s { color: #CC3300 } /* Literal.String */
					#p_muid_custom_head_help .na { color: #330099 } /* Name.Attribute */
					#p_muid_custom_head_help .nb { color: #336666 } /* Name.Builtin */
					#p_muid_custom_head_help .nc { color: #00AA88; font-weight: bold } /* Name.Class */
					#p_muid_custom_head_help .no { color: #336600 } /* Name.Constant */
					#p_muid_custom_head_help .nd { color: #9999FF } /* Name.Decorator */
					#p_muid_custom_head_help .ni { color: #999999; font-weight: bold } /* Name.Entity */
					#p_muid_custom_head_help .ne { color: #CC0000; font-weight: bold } /* Name.Exception */
					#p_muid_custom_head_help .nf { color: #CC00FF } /* Name.Function */
					#p_muid_custom_head_help .nl { color: #9999FF } /* Name.Label */
					#p_muid_custom_head_help .nn { color: #00CCFF; font-weight: bold } /* Name.Namespace */
					#p_muid_custom_head_help .nt { color: #330099; font-weight: bold } /* Name.Tag */
					#p_muid_custom_head_help .nv { color: #003333 } /* Name.Variable */
					#p_muid_custom_head_help .ow { color: #000000; font-weight: bold } /* Operator.Word */
					#p_muid_custom_head_help .w { color: #bbbbbb } /* Text.Whitespace */
					#p_muid_custom_head_help .mf { color: #FF6600 } /* Literal.Number.Float */
					#p_muid_custom_head_help .mh { color: #FF6600 } /* Literal.Number.Hex */
					#p_muid_custom_head_help .mi { color: #FF6600 } /* Literal.Number.Integer */
					#p_muid_custom_head_help .mo { color: #FF6600 } /* Literal.Number.Oct */
					#p_muid_custom_head_help .sb { color: #CC3300 } /* Literal.String.Backtick */
					#p_muid_custom_head_help .sc { color: #CC3300 } /* Literal.String.Char */
					#p_muid_custom_head_help .sd { color: #CC3300; font-style: italic } /* Literal.String.Doc */
					#p_muid_custom_head_help .s2 { color: #CC3300 } /* Literal.String.Double */
					#p_muid_custom_head_help .se { color: #CC3300; font-weight: bold } /* Literal.String.Escape */
					#p_muid_custom_head_help .sh { color: #CC3300 } /* Literal.String.Heredoc */
					#p_muid_custom_head_help .si { color: #AA0000 } /* Literal.String.Interpol */
					#p_muid_custom_head_help .sx { color: #CC3300 } /* Literal.String.Other */
					#p_muid_custom_head_help .sr { color: #33AAAA } /* Literal.String.Regex */
					#p_muid_custom_head_help .s1 { color: #CC3300 } /* Literal.String.Single */
					#p_muid_custom_head_help .ss { color: #FFCC33 } /* Literal.String.Symbol */
					#p_muid_custom_head_help .bp { color: #336666 } /* Name.Builtin.Pseudo */
					#p_muid_custom_head_help .vc { color: #003333 } /* Name.Variable.Class */
					#p_muid_custom_head_help .vg { color: #003333 } /* Name.Variable.Global */
					#p_muid_custom_head_help .vi { color: #003333 } /* Name.Variable.Instance */
					#p_muid_custom_head_help .il { color: #FF6600 } /* Literal.Number.Integer.Long */
					/* ]]> */
				</style>
				<div class="pf-form">
					<div class="pf-element">
						When adding CSS and JavaScript files, you should use the
						Pines JavaScript object. This allows your code to load
						in Ajax enabled installations.
					</div>
					<fieldset class="pf-group">
						<legend>Adding CSS</legend>
						<div class="pf-element pf-full-width">
							Adding a CSS file on this server (relative to this Pines installation).
							<div class="code_block ui-corner-all">
							<?php
/* pygmentize -l html -f html -o /dev/stdout pygments.html
<script type="text/javascript">
	// <![CDATA[
	pines.loadcss(pines.rela_location+"path/to/css/file.css");
	// ]]>
</script>
*/
							?>
							<div class="highlight"><pre><span class="nt">&lt;script </span><span class="na">type=</span><span class="s">&quot;text/javascript&quot;</span><span class="nt">&gt;</span>
	<span class="c1">// &lt;![CDATA[</span>
	<span class="nx">pines</span><span class="p">.</span><span class="nx">loadcss</span><span class="p">(</span><span class="nx">pines</span><span class="p">.</span><span class="nx">rela_location</span><span class="o">+</span><span class="s2">&quot;path/to/css/file.css&quot;</span><span class="p">);</span>
	<span class="c1">// ]]&gt;</span>
<span class="nt">&lt;/script&gt;</span>
</pre></div>
							</div>
						</div>
						<div class="pf-element pf-full-width">
							Adding a CSS file on a different server.
							<div class="code_block ui-corner-all">
							<?php
/* pygmentize -l html -f html -o /dev/stdout pygments.html
<script type="text/javascript">
	// <![CDATA[
	pines.loadcss("http://example.com/path/to/css/file.css");
	// ]]>
</script>
*/
							?>
							<div class="highlight"><pre><span class="nt">&lt;script </span><span class="na">type=</span><span class="s">&quot;text/javascript&quot;</span><span class="nt">&gt;</span>
	<span class="c1">// &lt;![CDATA[</span>
	<span class="nx">pines</span><span class="p">.</span><span class="nx">loadcss</span><span class="p">(</span><span class="s2">&quot;http://example.com/path/to/css/file.css&quot;</span><span class="p">);</span>
	<span class="c1">// ]]&gt;</span>
<span class="nt">&lt;/script&gt;</span>
</pre></div>
							</div>
						</div>
					</fieldset>
					<br />
					<fieldset class="pf-group">
						<legend>Adding JavaScript</legend>
						<div class="pf-element pf-full-width">
							Adding a JavaScript file on this server (relative to this Pines installation).
							<div class="code_block ui-corner-all">
							<?php
/* pygmentize -l html -f html -o /dev/stdout pygments.html
<script type="text/javascript">
	// <![CDATA[
	pines.loadjs(pines.rela_location+"path/to/js/file.js");
	// ]]>
</script>
*/
							?>
							<div class="highlight"><pre><span class="nt">&lt;script </span><span class="na">type=</span><span class="s">&quot;text/javascript&quot;</span><span class="nt">&gt;</span>
	<span class="c1">// &lt;![CDATA[</span>
	<span class="nx">pines</span><span class="p">.</span><span class="nx">loadjs</span><span class="p">(</span><span class="nx">pines</span><span class="p">.</span><span class="nx">rela_location</span><span class="o">+</span><span class="s2">&quot;path/to/js/file.js&quot;</span><span class="p">);</span>
	<span class="c1">// ]]&gt;</span>
<span class="nt">&lt;/script&gt;</span>
</pre></div>
							</div>
						</div>
						<div class="pf-element pf-full-width">
							Adding a JavaScript file on a different server.
							<div class="code_block ui-corner-all">
							<?php
/* pygmentize -l html -f html -o /dev/stdout pygments.html
<script type="text/javascript">
	// <![CDATA[
	pines.loadjs("http://example.com/path/to/js/file.js");
	// ]]>
</script>
*/
							?>
							<div class="highlight"><pre><span class="nt">&lt;script </span><span class="na">type=</span><span class="s">&quot;text/javascript&quot;</span><span class="nt">&gt;</span>
	<span class="c1">// &lt;![CDATA[</span>
	<span class="nx">pines</span><span class="p">.</span><span class="nx">loadjs</span><span class="p">(</span><span class="s2">&quot;http://example.com/path/to/js/file.js&quot;</span><span class="p">);</span>
	<span class="c1">// ]]&gt;</span>
<span class="nt">&lt;/script&gt;</span>
</pre></div>
							</div>
						</div>
						<div class="pf-element pf-full-width">
							Adding JavaScript to be loaded in turn with files.
							<div class="code_block ui-corner-all">
							<?php
/* pygmentize -l html -f html -o /dev/stdout pygments.html
<script type="text/javascript">
	// <![CDATA[
	pines.load(function(){
		// This code will run before all files have
		// been loaded and before the page is ready.
		... code ...
	});
	// ]]>
</script>
*/
							?>
							<div class="highlight"><pre><span class="nt">&lt;script </span><span class="na">type=</span><span class="s">&quot;text/javascript&quot;</span><span class="nt">&gt;</span>
	<span class="c1">// &lt;![CDATA[</span>
	<span class="nx">pines</span><span class="p">.</span><span class="nx">load</span><span class="p">(</span><span class="kd">function</span><span class="p">(){</span>
		<span class="c1">// This code will run before all files have</span>
		<span class="c1">// been loaded and before the page is ready.</span>
		<span class="p">...</span> <span class="nx">code</span> <span class="p">...</span>
	<span class="p">});</span>
	<span class="c1">// ]]&gt;</span>
<span class="nt">&lt;/script&gt;</span>
</pre></div>
							</div>
						</div>
						<div class="pf-element pf-full-width">
							Adding JavaScript to be loaded when the page (DOM) is ready.
							<div class="code_block ui-corner-all">
							<?php
/* pygmentize -l html -f html -o /dev/stdout pygments.html
<script type="text/javascript">
	// <![CDATA[
	pines(function(){
		// This code will run after all files have 
		// been loaded and after the page is ready.
		... code ...
	});
	// ]]>
</script>
*/
							?>
							<div class="highlight"><pre><span class="nt">&lt;script </span><span class="na">type=</span><span class="s">&quot;text/javascript&quot;</span><span class="nt">&gt;</span>
	<span class="c1">// &lt;![CDATA[</span>
	<span class="nx">pines</span><span class="p">(</span><span class="kd">function</span><span class="p">(){</span>
		<span class="c1">// This code will run after all files have </span>
		<span class="c1">// been loaded and after the page is ready.</span>
		<span class="p">...</span> <span class="nx">code</span> <span class="p">...</span>
	<span class="p">});</span>
	<span class="c1">// ]]&gt;</span>
<span class="nt">&lt;/script&gt;</span>
</pre></div>
							</div>
						</div>
					</fieldset>
				</div>
				<br />
			</div>
			<?php } ?>
			<br class="pf-clearing" />
		</div>
		<?php } ?>
		<div id="p_muid_tab_menu">
			<div class="pf-element pf-full-width">
				<span class="pf-label">Menu Entries</span>
				<span class="pf-note">It isn't necessary to add the same conditions on menu entries. They will only appear if the Page Conditions are met.</span>
				<div class="pf-group">
					<input class="pf-field ui-widget-content ui-corner-all" type="text" name="com_menueditor_entries" id="p_muid_menu_entries" size="24" value="<?php echo htmlspecialchars(json_encode($this->entity->com_menueditor_entries)); ?>" />
				</div>
			</div>
			<br class="pf-clearing" />
		</div>
		<div id="p_muid_tab_categories">
			<div class="pf-element pf-full-width">
				<script type="text/javascript">
					// <![CDATA[
					pines(function(){
						// Category Grid
						$("#p_muid_category_grid").pgrid({
							pgrid_toolbar: true,
							pgrid_toolbar_contents: [
								{type: 'button', text: 'Expand', title: 'Expand All', extra_class: 'picon picon-arrow-down', selection_optional: true, return_all_rows: true, click: function(e, rows){
									rows.pgrid_expand_rows();
								}},
								{type: 'button', text: 'Collapse', title: 'Collapse All', extra_class: 'picon picon-arrow-right', selection_optional: true, return_all_rows: true, click: function(e, rows){
									rows.pgrid_collapse_rows();
								}},
								{type: 'separator'},
								{type: 'button', text: 'All', title: 'Check All', extra_class: 'picon picon-checkbox', selection_optional: true, return_all_rows: true, click: function(e, rows){
									$("input", rows).attr("checked", "true");
								}},
								{type: 'button', text: 'None', title: 'Check None', extra_class: 'picon picon-dialog-cancel', selection_optional: true, return_all_rows: true, click: function(e, rows){
									$("input", rows).removeAttr("checked");
								}}
							],
							pgrid_hidden_cols: [1],
							pgrid_sort_col: 1,
							pgrid_sort_ord: "asc",
							pgrid_child_prefix: "ch_",
							pgrid_paginate: false,
							pgrid_view_height: "300px"
						});
					});
					// ]]>
				</script>
				<table id="p_muid_category_grid">
					<thead>
						<tr>
							<th>Order</th>
							<th>In</th>
							<th>Name</th>
							<th>Pages</th>
						</tr>
					</thead>
					<tbody>
					<?php
					$category_guids = $this->entity->get_categories_guid();
					foreach($this->categories as $cur_category) { ?>
						<tr title="<?php echo (int) $cur_category->guid; ?>" class="<?php echo $cur_category->children ? 'parent ' : ''; ?><?php echo isset($cur_category->parent) ? htmlspecialchars("child ch_{$cur_category->parent->guid} ") : ''; ?>">
							<td><?php echo isset($cur_category->parent) ? $cur_category->array_search($cur_category->parent->children) + 1 : '0' ; ?></td>
							<td><input type="checkbox" name="categories[]" value="<?php echo (int) $cur_category->guid; ?>" <?php echo in_array($cur_category->guid, $category_guids) ? 'checked="checked" ' : ''; ?>/></td>
							<td><?php echo htmlspecialchars($cur_category->name); ?></td>
							<td><?php echo count($cur_category->pages); ?></td>
						</tr>
					<?php } ?>
					</tbody>
				</table>
			</div>
			<br class="pf-clearing" />
		</div>
		<div id="p_muid_tab_conditions">
			<div class="pf-element pf-heading">
				<h1>Page Conditions</h1>
				<p>Users will only see this page if these conditions are met.</p>
			</div>
			<div class="pf-element pf-full-width">
				<table class="conditions_table">
					<thead>
						<tr>
							<th>Type</th>
							<th>Value</th>
						</tr>
					</thead>
					<tbody>
						<?php if (isset($this->entity->conditions)) foreach ($this->entity->conditions as $cur_key => $cur_value) { ?>
						<tr>
							<td><?php echo htmlspecialchars($cur_key); ?></td>
							<td><?php echo htmlspecialchars($cur_value); ?></td>
						</tr>
						<?php } ?>
					</tbody>
				</table>
				<input type="hidden" name="conditions" />
			</div>
			<div class="condition_dialog" style="display: none;" title="Add a Condition">
				<div class="pf-form">
					<div class="pf-element">
						<span class="pf-label">Detected Types</span>
						<span class="pf-note">These types were detected on this system.</span>
						<div class="pf-group">
							<div class="pf-field"><em><?php
							$checker_links = array();
							foreach (array_keys($pines->depend->checkers) as $cur_checker) {
								$checker_html = htmlspecialchars($cur_checker);
								$checker_js = htmlspecialchars(json_encode($cur_checker));
								$checker_links[] = "<a href=\"javascript:void(0);\" onclick=\"\$('#p_muid_cur_condition_type').val($checker_js);\">$checker_html</a>";
							}
							echo implode(', ', $checker_links);
							?></em></div>
						</div>
					</div>
					<div class="pf-element">
						<label><span class="pf-label">Type</span>
							<input class="pf-field ui-widget-content ui-corner-all" type="text" name="cur_condition_type" id="p_muid_cur_condition_type" size="24" /></label>
					</div>
					<div class="pf-element">
						<label><span class="pf-label">Value</span>
							<input class="pf-field ui-widget-content ui-corner-all" type="text" name="cur_condition_value" size="24" /></label>
					</div>
				</div>
				<br style="clear: both; height: 1px;" />
			</div>
			<br class="pf-clearing" />
		</div>
		<div id="p_muid_tab_advanced">
			<div class="pf-element pf-heading">
				<h1>Dates</h1>
				<p>Dates can be entered in almost any standard English phrase. (Next Monday, July 1st, Tomorrow 4pm, etc.)</p>
			</div>
			<div class="pf-element">
				<label><span class="pf-label">Override Created Date</span>
					<input class="pf-field ui-widget-content ui-corner-all" type="text" name="p_cdate" value="<?php echo $this->entity->p_cdate ? htmlspecialchars(format_date($this->entity->p_cdate, 'full_med')) : ''; ?>" /></label>
			</div>
			<div class="pf-element">
				<label><span class="pf-label">Override Modified Date</span>
					<input class="pf-field ui-widget-content ui-corner-all" type="text" name="p_mdate" value="<?php echo $this->entity->p_mdate ? htmlspecialchars(format_date($this->entity->p_mdate, 'full_med')) : ''; ?>" /></label>
			</div>
			<div class="pf-element">
				<label><span class="pf-label">Begin Publish Date</span>
					<input class="pf-field ui-widget-content ui-corner-all" type="text" name="publish_begin" value="<?php echo $this->entity->publish_begin ? htmlspecialchars(format_date($this->entity->publish_begin, 'full_med')) : htmlspecialchars(format_date(time(), 'full_med')); ?>" /></label>
			</div>
			<div class="pf-element">
				<label><span class="pf-label">End Publish Date</span>
					<input class="pf-field ui-widget-content ui-corner-all" type="text" name="publish_end" value="<?php echo $this->entity->publish_end ? htmlspecialchars(format_date($this->entity->publish_end, 'full_med')) : ''; ?>" /></label>
			</div>
			<div class="pf-element pf-heading">
				<h1>Options</h1>
			</div>
			<div class="pf-element">
				<label><span class="pf-label">Show Title</span>
					<select class="pf-field ui-widget-content ui-corner-all" name="show_title">
						<option value="null">Use Default</option>
						<option value="true"<?php echo $this->entity->show_title === true ? ' selected="selected"' : ''; ?>>Yes</option>
						<option value="false"<?php echo $this->entity->show_title === false ? ' selected="selected"' : ''; ?>>No</option>
					</select></label>
			</div>
			<div class="pf-element">
				<label><span class="pf-label">Show Author Info</span>
					<select class="pf-field ui-widget-content ui-corner-all" name="show_author_info">
						<option value="null">Use Default</option>
						<option value="true"<?php echo $this->entity->show_author_info === true ? ' selected="selected"' : ''; ?>>Yes</option>
						<option value="false"<?php echo $this->entity->show_author_info === false ? ' selected="selected"' : ''; ?>>No</option>
					</select></label>
			</div>
			<div class="pf-element">
				<label><span class="pf-label">Show Full Content in List</span>
					<span class="pf-note">Show both the intro and the content when this page is shown in a page list.</span>
					<select class="pf-field ui-widget-content ui-corner-all" name="show_content_in_list">
						<option value="null">Use Default</option>
						<option value="true"<?php echo $this->entity->show_content_in_list === true ? ' selected="selected"' : ''; ?>>Yes</option>
						<option value="false"<?php echo $this->entity->show_content_in_list === false ? ' selected="selected"' : ''; ?>>No</option>
					</select></label>
			</div>
			<div class="pf-element">
				<label><span class="pf-label">Show Intro on Page</span>
					<select class="pf-field ui-widget-content ui-corner-all" name="show_intro">
						<option value="null">Use Default</option>
						<option value="true"<?php echo $this->entity->show_intro === true ? ' selected="selected"' : ''; ?>>Yes</option>
						<option value="false"<?php echo $this->entity->show_intro === false ? ' selected="selected"' : ''; ?>>No</option>
					</select></label>
			</div>
			<div class="pf-element">
				<label><span class="pf-label">Show Breadcrumbs</span>
					<select class="pf-field ui-widget-content ui-corner-all" name="show_breadcrumbs">
						<option value="null">Use Default</option>
						<option value="true"<?php echo $this->entity->show_breadcrumbs === true ? ' selected="selected"' : ''; ?>>Yes</option>
						<option value="false"<?php echo $this->entity->show_breadcrumbs === false ? ' selected="selected"' : ''; ?>>No</option>
					</select></label>
			</div>
			<div class="pf-element pf-heading">
				<h1>Page Variants</h1>
			</div>
			<script type="text/javascript">
				// <![CDATA[
				pines(function(){
					$("#p_muid_variant_template").change(function(){
						var cur_template = $(this).val();
						$("option", "#p_muid_variant_variant").hide().filter("."+cur_template).show();
					}).change();
					$("#p_muid_variant_button").click(function(){
						var cur_template = $("#p_muid_variant_template").val();
						if ($("."+cur_template, "#p_muid_variants").length) {
							alert("There is already a variant set for this template. You must remove it before setting a new variant.");
							return;
						}
						var cur_template_name = $("option:selected", "#p_muid_variant_template").text();
						var cur_variant = $("#p_muid_variant_variant").val();
						var new_html = '<div class="pf-element pf-full-width '+pines.safe(cur_template)+'">\
							<button class="pf-field ui-state-default ui-corner-all remove" style="float: right;" type="button">Remove</button>\
							<span class="pf-label">'+pines.safe(cur_template_name)+'</span>\
							<span class="pf-field">'+pines.safe(cur_variant)+'</span>\
							<input type="hidden" name="variants[]" value="'+pines.safe(cur_template)+'::'+pines.safe(cur_variant)+'" />\
						</div>';
						$("#p_muid_variants").append(new_html).find(":button").button();
					});
					$("#p_muid_variants").delegate(".remove", "click", function(){
						$(this).closest(".pf-element").remove();
					}).find(":button").button();
				});
				// ]]>
			</script>
			<div class="pf-element">
				<span class="pf-label">Add a Variant</span>
				<?php
				$variants = array();
				foreach ($pines->components as $cur_template) {
					if (strpos($cur_template, 'tpl_') !== 0)
						continue;
					$cur_template = clean_filename($cur_template);
					// Is there even a variant option?
					if (!isset($pines->config->$cur_template->variant))
						continue;
					// Find the defaults file.
					if (!file_exists("templates/$cur_template/defaults.php"))
						continue;
					/**
					 * Get the template defaults to list all the variants.
					 */
					$template_options = (array) include("templates/$cur_template/defaults.php");
					foreach ($template_options as $cur_option) {
						if ($cur_option['name'] != 'variant')
							continue;
						$variants[$cur_template] = $cur_option['options'];
						break;
					}
				}
				if (empty($variants)) {
				?>
				<span class="pf-field">None of the enabled templates have any page variants.</span>
				<?php } else { ?>
				<br />
				<select class="pf-field ui-widget-content ui-corner-all" id="p_muid_variant_template">
					<?php foreach ($variants as $cur_template => $cur_variants) { ?>
					<option value="<?php echo htmlspecialchars($cur_template); ?>"<?php echo $cur_template == $pines->current_template ? ' selected="selected"' : ''; ?>><?php echo htmlspecialchars("{$pines->info->$cur_template->name} ($cur_template)"); ?></option>
					<?php } ?>
				</select>
				<select class="pf-field ui-widget-content ui-corner-all" id="p_muid_variant_variant">
					<?php foreach ($variants as $cur_template => $cur_variants) {
						foreach ($cur_variants as $cur_description => $cur_variant) { ?>
					<option class="<?php echo htmlspecialchars($cur_template); ?>" value="<?php echo htmlspecialchars($cur_variant); ?>"><?php echo htmlspecialchars($cur_description); ?></option>
					<?php } } ?>
				</select>
				<button class="pf-field ui-state-default ui-corner-all" type="button" id="p_muid_variant_button">Add</button>
				<?php } ?>
			</div>
			<div id="p_muid_variants">
				<?php foreach ((array) $this->entity->variants as $cur_template => $cur_variant) { ?>
				<div class="pf-element pf-full-width <?php echo htmlspecialchars($cur_template); ?>">
					<button class="pf-field ui-state-default ui-corner-all remove" style="float: right;" type="button">Remove</button>
					<span class="pf-label"><?php echo htmlspecialchars("{$pines->info->$cur_template->name} ($cur_template)"); ?></span>
					<span class="pf-field"><?php echo htmlspecialchars($cur_variant); ?></span>
					<input type="hidden" name="variants[]" value="<?php echo htmlspecialchars("{$cur_template}::{$cur_variant}"); ?>" />
				</div>
				<?php } ?>
			</div>
			<br class="pf-clearing" />
		</div>
	</div>
	<div class="pf-element pf-buttons">
		<br />
		<?php if ( isset($this->entity->guid) ) { ?>
		<input type="hidden" name="id" value="<?php echo (int) $this->entity->guid; ?>" />
		<?php } ?>
		<input class="pf-button ui-state-default ui-priority-primary ui-corner-all" type="submit" value="Submit" />
		<input class="pf-button ui-state-default ui-priority-secondary ui-corner-all" type="button" onclick="pines.get('<?php echo htmlspecialchars(pines_url('com_content', 'page/list')); ?>');" value="Cancel" />
	</div>
</form>