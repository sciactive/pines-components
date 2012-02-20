/*
 * jQuery Menu Editor (menueditor) Plugin 1.0.0
 *
 * Copyright (c) 2010 Hunter Perrin
 *
 * Licensed (along with all of Pines) under the GNU Affero GPL:
 *	  http://www.gnu.org/licenses/agpl.html
 */

(function($) {
$.fn.menueditor = function(options){
// Iterate and transform each matched element.
var all = this;
all.each(function(){
	var input = $(this), container = $("<div class=\"ui-widget ui-widget-content ui-corner-all ui-menu-editor\"></div>");
	input.menueditor_version = "1.0.0";
	// Check for the menueditor class. If it has it, we've already transformed this element.
	if (input.hasClass("ui-menueditor")) return true;
	// Add the menueditor class.
	input.addClass("ui-menueditor");
	input.wrap(container).hide();
	// Reset container to actual DOM element.
	container = input.parent();
	// Build interface.
	var toolbar = $("<div class=\"ui-widget-header ui-corner-all ui-menu-editor-toolbar\">\n\
<button class=\"ui-menueditor-add btn btn-success\" type=\"button\">New Entry</button>\n\
<button class=\"ui-menueditor-clear btn btn-danger\" type=\"button\">Clear All Entries</button>\n\
</div>").appendTo(container)
	.delegate(".ui-menueditor-add", "click", function(){
		edit_entry();
	}).delegate(".ui-menueditor-clear", "click", function(){
		if (confirm("Are you sure you want to delete all menu entries?")) {
			entries.children(".ui-menu-editor-entry").remove();
			update_entries();
		}
	});
	var entries = $("<div class=\"ui-widget-content ui-corner-all ui-menu-editor-entries\"><div class=\"ui-menu-editor-noentries\">There are no menu entries yet.</div></div>")
	.appendTo(container)
	.sortable({
		placeholder: "ui-state-highlight ui-menu-editor-placeholder",
		update: update_entries
	});
	var edit_entry = function(cur_entry){
		var menu_dialog = $("<div title=\"Editing Menu Entry\"><div style=\"text-align: center;\"><span style=\"padding-left: 18px; line-height: 16px; background-repeat: no-repeat;\" class=\"picon-throbber\">Loading...</span></div></div>");
		if (!cur_entry)
			menu_dialog.attr("title", "New Menu Entry");
		menu_dialog.dialog({
			modal: true,
			height: 500,
			width: 800
		});
		var data = {};
		if (options.disabled_fields && options.disabled_fields.length)
			data.disabled_fields = options.disabled_fields.join(",");
		if (options.defaults && !$.isEmptyObject(options.defaults)) {
			var defaults = $.extend({}, options.defaults);
			$.each(defaults, function(key, val){
				if ($.isFunction(val))
					defaults[key] = val();
			});
			data.defaults = JSON.stringify(defaults);
		}
		if (cur_entry)
			data.values = cur_entry.children(".ui-menu-editor-entry-values").html();
		$.ajax({
			url: pines.com_menueditor_dialog_url,
			method: "GET",
			dataType: "html",
			data: data,
			error: function(XMLHttpRequest, textStatus){
				menu_dialog.dialog("close").remove();
				pines.error("An error occured while trying to load the menu editor form:\n"+pines.safe(XMLHttpRequest.status)+": "+pines.safe(textStatus));
			},
			success: function(data){
				pines.pause();
				menu_dialog.html(data).dialog("option", "buttons", {
					"Done": function(){
						var values = {};
						$.each(menu_dialog.find("form").serializeArray(), function(i, val){
							values[val.name] = val.value;
						});
						values.enabled = (values.enabled == "ON");
						values.conditions = JSON.parse(values.conditions);
						if (values.top_menu == "--new--") {
							delete values.top_menu;
							delete values.location;
						} else
							delete values.position;
						add_entry(values, cur_entry);
						menu_dialog.dialog("close").remove();
					}
				});
				pines.play();
			}
		});
	};
	var add_entry = function(values, cur_entry){
		var entry;
		if (cur_entry)
			entry = cur_entry.empty();
		else
			entry = $("<div class=\"ui-menu-editor-entry ui-widget-content ui-corner-all ui-helper-clearfix\"></div>").appendTo(entries);
		if (!values.enabled)
			entry.addClass("ui-priority-secondary");
		else
			entry.removeClass("ui-priority-secondary");
		entry.append($("<button type=\"button\" class=\"btn btn-danger\">Delete</button>").click(function(){
			$(this).closest(".ui-menu-editor-entry").remove();
			update_entries();
		}))
		.append($("<button type=\"button\" class=\"btn\">Edit</button>").click(function(){
			edit_entry($(this).closest(".ui-menu-editor-entry"));
		}))
		.append("<div class=\"ui-menu-editor-entry-name\">"+pines.safe(values.text)+" ["+pines.safe(values.name)+"]</div>")
		.append("<div class=\"ui-menu-editor-entry-path\">"+(values.location ? pines.safe(values.location+"/") : "")+pines.safe(values.name)+"</div>")
		.append("<div class=\"ui-menu-editor-entry-values\" style=\"display: none;\">"+pines.safe(JSON.stringify(values))+"</div>");
		if (update_entries) // This prevents calling while adding the original entries.
			update_entries();
	};
	// Add all the original entries.
	var cur_entries = input.val();
	if (cur_entries && cur_entries != "")
		cur_entries = JSON.parse(cur_entries);
	if (cur_entries && cur_entries.length)
		$.each(cur_entries, function(i, val){
			add_entry(val);
		});

	var update_entries = function(){
		var cur_entries = entries.children(".ui-menu-editor-entry");
		if (cur_entries.length)
			entries.children(".ui-menu-editor-noentries").hide();
		else
			entries.children(".ui-menu-editor-noentries").show();
		var input_value = [];
		cur_entries.each(function(){
			input_value.push(JSON.parse($(this).children(".ui-menu-editor-entry-values").html()));
		});
		input.val(JSON.stringify(input_value));
	};
	update_entries();
	// Save the menueditor object in the DOM, so we can access it.
	this.pines_menueditor = input;
});
return all;
};
})(jQuery);