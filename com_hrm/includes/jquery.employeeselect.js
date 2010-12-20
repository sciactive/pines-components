/*
 * jQuery Employee Select (employeeselect) Plugin 1.0.0
 *
 * Copyright (c) 2010 Hunter Perrin
 *
 * Licensed (along with all of Pines) under the GNU Affero GPL:
 *	  http://www.gnu.org/licenses/agpl.html
 */

(function($) {
$.fn.employeeselect = function(options){
// Iterate and transform each matched element.
var all = this;
all.each(function(){
	var cs = $(this);
	cs.employeeselect_version = "1.0.0";
	// Check for the employeeselect class. If it has it, we've already transformed this element.
	if (cs.hasClass("ui-employeeselect")) return true;
	// Add the employeeselect class.
	cs.addClass("ui-employeeselect");
	var opts = {
		minLength: 2,
		source: function(request, response) {
			$.ajax({
				url: pines.com_hrm_autoemployee_url,
				dataType: "json",
				data: {"q": request.term},
				success: function(data) {
					if (!data) {
						response([]);
						return;
					}
					response($.map(data, function(item) {
						return {
							"id": item.guid,
							"label": item.name,
							"value": item.guid+": "+item.name,
							"desc": "<em><pre>"+(item.email ? " "+item.email : "")+
								(item.location_name ? " "+item.location_name : "")+"</pre></em>"
						};
					}));
				}
			});
		}
	};
	if (typeof options != "undefined")
		$.extend(opts, options);
	cs.autocomplete(opts).data("autocomplete")._renderItem = function(ul, item){
		return $("<li></li>").data("item.autocomplete", item)
			.append("<a><strong>"+item.label+"</strong><br />"+item.desc+"</a>")
			.appendTo(ul);
	};
	// Save the employeeselect object in the DOM, so we can access it.
	this.pines_employeeselect = cs;
});
return all;
};
})(jQuery);