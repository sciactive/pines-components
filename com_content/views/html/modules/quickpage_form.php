<?php
/**
 * Provides a form for the user to choose a page.
 *
 * @package Components\content
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');

$pines->com_pgrid->load();
$pines->com_ptags->load();

// This is all done so I can use the page form view. ;)
$this->entity = com_content_page::factory();
if (isset($this->title_position)) {
	$this->entity->title_position = ($this->title_position == 'null' ? null : $this->title_position);
	if (isset($this->entity->title_position) && !in_array($this->entity->title_position, array('prepend', 'append', 'replace')))
		$this->entity->title_position = null;
}
if (isset($this->show_front_page))
	$this->entity->show_front_page = ($this->show_front_page == 'null' ? null : ($this->show_front_page == 'true'));
if (isset($this->content_tags)) {
	$this->entity->content_tags = explode(',', $this->content_tags);
	foreach ($this->entity->content_tags as $key => $cur_tag) {
		if ($cur_tag == '')
			unset($this->entity->content_tags[$key]);
	}
}

// Page Head
if (isset($this->meta_tags) && gatekeeper('com_content/editmeta')) {
	$meta_tags = (array) json_decode($this->meta_tags);
	$this->entity->meta_tags = array();
	foreach ($meta_tags as $cur_meta_tag) {
		if (!isset($cur_meta_tag->values[0], $cur_meta_tag->values[1]))
			continue;
		$this->entity->meta_tags[] = array('name' => $cur_meta_tag->values[0], 'content' => $cur_meta_tag->values[1]);
	}
}
if ($pines->config->com_content->custom_head && gatekeeper('com_content/edithead')) {
	if (isset($this->enable_custom_head))
		$this->entity->enable_custom_head = ($this->enable_custom_head == 'ON');
	if (isset($this->custom_head))
		$this->entity->custom_head = $this->custom_head;
}

// Conditions
if (isset($this->conditions)) {
	$conditions = (array) json_decode($this->conditions);
	$this->entity->conditions = array();
	foreach ($conditions as $cur_condition) {
		if (!isset($cur_condition->values[0], $cur_condition->values[1]))
			continue;
		$this->entity->conditions[$cur_condition->values[0]] = $cur_condition->values[1];
	}
}

// Advanced
if (!empty($this->p_cdate))
	$this->entity->p_cdate = strtotime($this->p_cdate);
if (!empty($this->p_mdate))
	$this->entity->p_mdate = strtotime($this->p_mdate);
if (isset($this->publish_begin))
	$this->entity->publish_begin = strtotime($this->publish_begin);
if (!empty($this->publish_end))
	$this->entity->publish_end = strtotime($this->publish_end);
else
	$this->entity->publish_end = null;
if (isset($this->show_title_save))
	$this->entity->show_title = ($this->show_title_save == 'null' ? null : ($this->show_title_save == 'true'));
if (isset($this->show_author_info))
	$this->entity->show_author_info = ($this->show_author_info == 'null' ? null : ($this->show_author_info == 'true'));
if (isset($this->show_content_in_list))
	$this->entity->show_content_in_list = ($this->show_content_in_list == 'null' ? null : ($this->show_content_in_list == 'true'));
if (isset($this->show_intro))
	$this->entity->show_intro = ($this->show_intro == 'null' ? null : ($this->show_intro == 'true'));
if (isset($this->show_breadcrumbs))
	$this->entity->show_breadcrumbs = ($this->show_breadcrumbs == 'null' ? null : ($this->show_breadcrumbs == 'true'));
if (isset($this->variants)) {
	$this->entity->variants = array();
	foreach ($this->variants as $cur_variant_entry) {
		list ($cur_template, $cur_variant) = explode('::', $cur_variant_entry, 2);
		$this->entity->variants[$cur_template] = $cur_variant;
	}
}

$page_form = $this->entity->print_form();
$page_form->quickpage_options = true;
$page_form->category_guids = array();
foreach ($this->categories as $cur_guid) {
	if ($cur_guid)
		$page_form->category_guids[] = (int) $cur_guid;
}

?>
<div class="pf-form">
	<div class="pf-element pf-heading">
		<h3>Widget</h3>
	</div>
	<div class="pf-element pf-full-width">
		<label>
			<span class="pf-label">Title</span>
			<span class="pf-group pf-full-width">
				<span class="pf-field" style="display: block;">
					<input style="width: 100%;" type="text" name="widget_title" value="<?php echo htmlspecialchars($this->widget_title); ?>" />
				</span>
			</span>
		</label>
	</div>
	<div class="pf-element">
		<label><span class="pf-label">Full Editor for Intro</span>
			<input type="checkbox" class="pf-field" name="intro_full_editor" value="true"<?php echo $this->intro_full_editor == 'true' ? ' checked="checked"' : ''; ?> /></label>
	</div>
	<div class="pf-element">
		<label><span class="pf-label">Simple Editor for Content</span>
			<input type="checkbox" class="pf-field" name="content_simple_editor" value="true"<?php echo $this->content_simple_editor == 'true' ? ' checked="checked"' : ''; ?> /></label>
	</div>
	<div class="pf-element pf-heading">
		<h3>Page Defaults</h3>
	</div>
	<?php echo $page_form->render(); ?>
</div>