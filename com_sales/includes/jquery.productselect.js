/*
 * jQuery Product Select (productselect) Plugin 1.0.0
 *
 * Copyright (c) 2010 Zak Huber
 *
 * Licensed (along with all of Pines) under the GNU Affero GPL:
 *	  http://www.gnu.org/licenses/agpl.html
 */

(function($) {
$.fn.productselect = function(options){
// Iterate and transform each matched element.
var all = this;
all.each(function(){
	var ps = $(this);
	ps.productselect_version = "1.0.0";
	// Check for the productselect class. If it has it, we've already transformed this element.
	if (ps.hasClass("ui-productselect")) return true;
	// Add the productselect class.
	ps.addClass("ui-productselect");
	var opts = {
		minLength: 2,
		source: function(request, response) {
			$.ajax({
				url: pines.com_sales_autoproduct_url,
				dataType: "json",
				data: {"q": request.term},
				success: function(data) {
					if (!data) {
						response([]);
						return;
					}
					response($.map(data, function(item) {
						return {
							"id": pines.safe(item.guid),
							"label": pines.safe(item.name)+"<small style=\"display: block; float: right;\">$"+pines.safe(item.unit_price)+"</small>",
							"value": item.sku,
							"desc": 
								(item.receipt_description ? "<em>"+pines.safe(item.receipt_description)+"</em>" : "")
						};
					}));
				}
			});
		}
	};
	if (typeof options != "undefined")
		$.extend(opts, options);
	ps.autocomplete(opts).data("autocomplete")._renderItem = function(ul, item){
		return $("<li></li>").data("item.autocomplete", item)
			.append("<a><strong>"+item.label+"</strong><br />"+item.desc+"</a>")
			.appendTo(ul);
	};
	// Save the productselect object in the DOM, so we can access it.
	this.pines_productselect = ps;
});
return all;
};
})(jQuery);