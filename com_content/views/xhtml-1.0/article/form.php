<?php
/**
 * Provides a form for the user to edit an article.
 *
 * @package Pines
 * @subpackage com_content
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
$this->title = (!isset($this->entity->guid)) ? 'Editing New Article' : 'Editing ['.htmlentities($this->entity->name).']';
$this->note = 'Provide article details in this form.';
$pines->editor->load();
$pines->com_pgrid->load();
$pines->com_ptags->load();
?>
<form class="pf-form" method="post" id="p_muid_form" action="<?php echo htmlentities(pines_url('com_content', 'article/save')); ?>">
	<script type="text/javascript">
		// <![CDATA[
		pines(function(){
			$("#p_muid_article_tabs").tabs();
		});
		// ]]>
	</script>
	<div id="p_muid_article_tabs" style="clear: both;">
		<ul>
			<li><a href="#p_muid_tab_general">General</a></li>
			<li><a href="#p_muid_tab_categories">Categories</a></li>
			<li><a href="#p_muid_tab_advanced">Advanced</a></li>
		</ul>
		<div id="p_muid_tab_general">
			<?php if (isset($this->entity->guid)) { ?>
			<div class="date_info" style="float: right; text-align: right;">
				<?php if (isset($this->entity->user)) { ?>
				<div>User: <span class="date"><?php echo "{$this->entity->user->name} [{$this->entity->user->username}]"; ?></span></div>
				<div>Group: <span class="date"><?php echo "{$this->entity->group->name} [{$this->entity->group->groupname}]"; ?></span></div>
				<?php } ?>
				<div>Created: <span class="date"><?php echo format_date($this->entity->p_cdate, 'full_short'); ?></span></div>
				<div>Modified: <span class="date"><?php echo format_date($this->entity->p_mdate, 'full_short'); ?></span></div>
			</div>
			<?php } ?>
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
					<div class="pf-group pf-full-width">
						<input class="pf-field ui-widget-content" style="width: 100%;" type="text" name="name" value="<?php echo $this->entity->name; ?>" />
					</div>
				</label>
			</div>
			<div class="pf-element pf-full-width">
				<label>
					<span class="pf-label">Alias</span>
					<div class="pf-group pf-full-width">
						<input class="pf-field ui-widget-content" style="width: 100%;" type="text" name="alias" value="<?php echo $this->entity->alias; ?>" onkeyup="this.value=this.value.replace(/[^\w\d-.]/g, '_');" />
					</div>
				</label>
			</div>
			<div class="pf-element">
				<label><span class="pf-label">Enabled</span>
					<input class="pf-field ui-widget-content" type="checkbox" name="enabled" size="24" value="ON"<?php echo $this->entity->enabled ? ' checked="checked"' : ''; ?> /></label>
			</div>
			<div class="pf-element pf-full-width">
				<span class="pf-label">Tags</span>
				<div class="pf-group">
					<input class="pf-field ui-widget-content" type="text" name="content_tags" size="24" value="<?php echo implode(',', $this->entity->content_tags); ?>" />
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
				<textarea rows="3" cols="35" class="peditor" style="width: 100%;" name="intro"><?php echo $this->entity->intro; ?></textarea>
			</div>
			<div class="pf-element pf-heading">
				<h1>Content</h1>
			</div>
			<div class="pf-element pf-full-width">
				<textarea rows="8" cols="35" class="peditor" style="width: 100%; height: 500px;" name="content"><?php echo $this->entity->content; ?></textarea>
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
							<th>Articles</th>
						</tr>
					</thead>
					<tbody>
					<?php
					$category_guids = $this->entity->get_categories_guid();
					foreach($this->categories as $cur_category) { ?>
						<tr title="<?php echo $cur_category->guid; ?>" class="<?php echo $cur_category->children ? 'parent ' : ''; ?><?php echo isset($cur_category->parent) ? "child {$cur_category->parent->guid} " : ''; ?>">
							<td><?php echo isset($cur_category->parent) ? $cur_category->array_search($cur_category->parent->children) + 1 : '0' ; ?></td>
							<td><input type="checkbox" name="categories[]" value="<?php echo $cur_category->guid; ?>" <?php echo in_array($cur_category->guid, $category_guids) ? 'checked="checked" ' : ''; ?>/></td>
							<td><?php echo $cur_category->name; ?></td>
							<td><?php echo count($cur_category->articles); ?></td>
						</tr>
					<?php } ?>
					</tbody>
				</table>
			</div>
			<br class="pf-clearing" />
		</div>
		<div id="p_muid_tab_advanced">
			<div class="pf-element">
				<span class="pf-label">Nothing here yet...</span>
			</div>
			<br class="pf-clearing" />
		</div>
	</div>
	<div class="pf-element pf-buttons">
		<br />
		<?php if ( isset($this->entity->guid) ) { ?>
		<input type="hidden" name="id" value="<?php echo $this->entity->guid; ?>" />
		<?php } ?>
		<input class="pf-button ui-state-default ui-priority-primary ui-corner-all" type="submit" value="Submit" />
		<input class="pf-button ui-state-default ui-priority-secondary ui-corner-all" type="button" onclick="pines.get('<?php echo htmlentities(pines_url('com_content', 'article/list')); ?>');" value="Cancel" />
	</div>
</form>