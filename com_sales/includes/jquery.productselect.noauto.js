/*
 * jQuery Product Select Without Autocomplete (productselect_noauto) Plugin 1.0.0
 *
 * Copyright (c) 2014 Angela Murrell, Grey Vugrin, Mohammed Ahmed
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
        var results_array = [];
        var search_term = '';
        var found_result = '';
        var modal_markup = '<div style="display: block;" id="productmodal" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="productselect" aria-hidden="false"><div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button><h3 id="myModalLabel">Pick A Product:</h3></div><div id="#productselect-body" class="modal-body" style="min-height: 130px;"><div style="text-align: center; margin-top: 30px;" id="product_throbber"><i style="font-size: 30px; margin-bottom: 10px;" class="icon-spinner icon-spin icon-large"> </i><br />Loading...</div></div></div>';
        var error_message = '<div class="alert alert-info"><h4>No Result Found</h4><p>The person may not exist, or your term is misspelled.</p><a id="product_back_to_search" class="btn btn-info">Back to Search</a></div>';
        // Need to append modal_markup to html body
        $('body').append(modal_markup);
        var product_modal = $('#productmodal');
        var product_body = $('#productmodal .modal-body');
        var throbber = $('#productmodal #productthrobber');
        function searchHandler() {
            search_term = ps.val();
            $.ajax({
                url: pines.com_sales_autoproduct_url,
                dataType: "json",
                data: {"q": ps.val()},
                success: function(data) {
                    if (!data) {
                            results_array = [];
                            product_body.html(error_message);
                            $("#product_back_to_search").click(function(e) {
                                product_modal.modal('toggle');
                                ps.focus();
                            });
                            return;
                    }
                    results_array = $.map(data, function(item) {
                            return {
                                    "id": pines.safe(item.guid),
                                    "label": pines.safe(item.name)+"<small style=\"display: block; float: right;\">$"+pines.safe(item.unit_price)+"</small>",
                                    "value": item.sku,
                                    "desc": (item.receipt_description ? "<em>"+pines.safe(item.receipt_description)+"</em>" : "")
                            };
                    });
                    appendResults();
                }
            });
        }
        
        function appendResults() {
            var results_length = results_array.length;
            var result_text = '<ul class="unstyled">';
            for (var i = 0; i < results_length; i++) {
                result_text += "<li class='productselect_results' data-resultid='" + i.toString() + "'><a style='margin-bottom: 10px;' class='btn btn-block'><strong>" + results_array[i].label + "</strong><br />" + results_array[i].desc + "</a></li>";
            }
            result_text += "</ul>";
            product_body.html(result_text);
            
            $(".productselect_results").click(function(e){
               var index_result = $(this).attr('data-resultid');
               var customer = results_array[index_result].value;
               found_result = customer;
               ps.val(customer);
               product_modal.modal('toggle');
               ps.trigger('product_selected');
            });
        }
        
        function searchOrAppend() {
            console.log(ps.val());
            if (ps.val() == '') return;
            if (ps.val() != search_term && ps.val() != found_result) {
                product_body.html(throbber);
                searchHandler();
            }
            product_modal.modal('toggle');
            ps.trigger('product_modal_opened');
        }
        
        function makeSearch() {
            var h = (ps.outerHeight() - 14 ) / 2;
            var wrapper = '<span style="position: relative;"></span>';
            var btn = '<a style="text-decoration: none; cursor: pointer; z-index: 3; position: absolute; padding: '+ h +'px; right: 0px;"><i style="font-size: 14px;" class="icon-search"></i></a>';
            ps.wrap(wrapper).after(btn);
            ps.next('a').click(function() {
                searchOrAppend();
            });
        }
        
        makeSearch();
        
        ps.off('keydown');
        ps.unbind('keydown');
        ps.off('keypress');
        ps.unbind('keypress');
        ps.keypress(function(e) {
            console.log(e.keyCode);
            if (e.keyCode == 13) {
                searchOrAppend();
                e.preventDefault();
            }
        });
        
        ps.on('product_search', function(){
            searchOrAppend();
        });
        
        ps.on('remove_search_icon', function(){
           ps.next('a').remove(); 
        });
        
        ps.on('add_search_icon', function(){
           makeSearch();
        });
});
return all;
};
})(jQuery);