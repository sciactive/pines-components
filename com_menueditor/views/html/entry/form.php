<?php
/**
 * Provides a form for the user to edit an entry.
 *
 * @package Pines
 * @subpackage com_menueditor
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');
$this->title = (!isset($this->entity->guid)) ? 'Editing New Entry' : 'Editing ['.htmlspecialchars($this->entity->name).']';
$this->note = 'Provide entry details in this form.';
$pines->editor->load();
$pines->com_pgrid->load();
$pines->com_jstree->load();

// Testing that the path opens correctly.
$this->entity->location = 'main_menu/other/example/foobars';

// Make a JSON structure of the current entire menu.
$menus = array();
foreach ($this->captured_menu_arrays as $cur_entry) {
	// Transform URL arrays into actual URLs.
	if (isset($cur_entry['href']) && (array) $cur_entry['href'] === $cur_entry['href'])
		$cur_entry['href'] = call_user_func_array(array($pines->template, 'url'), $cur_entry['href']);
	$tmp_path = explode('/', $cur_entry['path']);
	$cur_menus =& $menus;
	foreach ($tmp_path as $cur_path) {
		if (!isset($cur_menus[$cur_path]))
			$cur_menus[$cur_path] = array();
		$cur_menus =& $cur_menus[$cur_path];
	}
	$cur_menus[0] = (object) array(
		'data' => $cur_path.' ['.$cur_entry['text'].']',
		'attr' => (object) array(
			'id' => 'p_muid_menu_'.$cur_entry['path']
		),
		'metadata' => (object) $cur_entry
	);
}

/**
 * Pack menu objects' children into the right structure.
 * @param type $menu_object 
 */
function com_menueditor__pack_children(&$menu_object) {
	if ($menu_object[0]->metadata->sort)
		ksort($menu_object);
	$new_item = $menu_object[0];
	unset($menu_object[0]);
	$children = array_values($menu_object);
	$menu_object = $new_item;
	if (!$children)
		return;
	$menu_object->children = $children;
	foreach ($menu_object->children as &$cur_child) {
		com_menueditor__pack_children($cur_child);
	}
	unset($cur_child);
}

// Format it for use in the jsTree.
foreach ($menus as &$cur_child) {
	if ($cur_child[0]->metadata->sort)
		ksort($cur_child);
	com_menueditor__pack_children($cur_child);
	$cur_child = array($cur_child);
}
unset($cur_child);
?>
<script type='text/javascript'>
	// <![CDATA[
	pines(function(){
		// Location Tree
		var location = $("#p_muid_location_saver");
		var location_tree = $("#p_muid_location");
		location_tree
		.bind("select_node.jstree", function(e, data){
			location.val(data.inst.get_selected().attr("id").replace("p_muid_", ""));
		})
		.bind("loaded.jstree", function(e, data){
			var path = data.inst.get_path("#"+data.inst.get_settings().ui.initially_select, true);
			if (!path.length) return;
			data.inst.open_node("#"+path.join(", #"), false, true);
		})
		.jstree({
			"plugins" : [ "themes", "json_data", "ui" ],
			"json_data" : {
				"data" : <?php echo json_encode($menus['main_menu']); ?>
			},
			"ui" : {
				"select_limit" : 1,
				"initially_select" : [<?php echo json_encode('p_muid_menu_'.$this->entity->location); ?>]
			}
		});
	});
	// ]]>
</script>
<form class="pf-form" method="post" id="p_muid_form" action="<?php echo htmlspecialchars(pines_url('com_menueditor', 'entry/save')); ?>">
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
		<span class="pf-label">Location</span>
		<select class="pf-field ui-widget-content ui-corner-all" name="top_menu">
			<option value="--new--">-- New Top Level Menu --</option>
			<?php foreach ($menus as $key => $cur_menu) { ?>
			<option value="<?php echo htmlspecialchars($key); ?>"><?php echo htmlspecialchars($cur_menu[0]->data); ?></option>
			<?php } ?>
		</select>
		<div class="pf-group">
			<div class="pf-field" id="p_muid_location"></div>
		</div>
		<input type="hidden" name="location" id="p_muid_location_saver" value="<?php echo htmlspecialchars($this->location); ?>" />
	</div>
	<div class="pf-element">
		<label><span class="pf-label">Name</span>
			<input class="pf-field ui-widget-content ui-corner-all" type="text" name="name" size="24" value="<?php echo htmlspecialchars($this->entity->name); ?>" /></label>
	</div>
	<div class="pf-element">
		<label><span class="pf-label">Enabled</span>
			<input class="pf-field" type="checkbox" name="enabled" value="ON"<?php echo $this->entity->enabled ? ' checked="checked"' : ''; ?> /></label>
	</div>
	<div class="pf-element pf-buttons">
		<?php if ( isset($this->entity->guid) ) { ?>
		<input type="hidden" name="id" value="<?php echo (int) $this->entity->guid; ?>" />
		<?php } ?>
		<input class="pf-button ui-state-default ui-priority-primary ui-corner-all" type="submit" value="Submit" />
		<input class="pf-button ui-state-default ui-priority-secondary ui-corner-all" type="button" onclick="pines.get('<?php echo htmlspecialchars(pines_url('com_menueditor', 'entry/list')); ?>');" value="Cancel" />
	</div>
</form>