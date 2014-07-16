/*
 * jQuery Customer Select Without Autocomplete (customerselect_noauto) Plugin 1.0.0
 *
 * Copyright (c) 2014 Angela Murrell, Grey Vugrin, Mohammed Ahmed
 *
 * Licensed (along with all of Pines) under the GNU Affero GPL:
 *	  http://www.gnu.org/licenses/agpl.html
 */

(function($) {
$.fn.customerselect = function(options){
// Iterate and transform each matched element.
var all = this;
all.each(function(){
	var cs = $(this);
        var identifier = cs[0].id;
        var modal_id = 'customermodal';
        var back_to_search_id = 'back_to_search';
        if (identifier.length) {
            modal_id += '-' + identifier;
            back_to_search_id += '-' + identifier;
        }
        var results_array = [];
        var search_term = '';
        var found_result = '';
        var modal_markup = '<div id="' + modal_id + '" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="customerselect" aria-hidden="false"><div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button><h3 id="myModalLabel">Pick A Customer:</h3></div><div id="#customerselect-body" class="modal-body" style="min-height: 130px;"><div style="text-align: center; margin-top: 30px;" id="throbber"><i style="font-size: 30px; margin-bottom: 10px;" class="icon-spinner icon-spin icon-large"> </i><br />Loading...</div></div></div>';
        var error_message = '<div class="alert alert-info"><h4>No Result Found</h4><p>The person may not exist, or your term is misspelled.</p><a id="' + back_to_search_id + '" class="btn btn-info">Back to Search</a></div>';
        // Need to append modal_markup to html body
        $('body').append(modal_markup);
        var cust_modal = $("#" + modal_id);
        var cust_body = $('#'+ modal_id + ' .modal-body');
        var throbber = $('#' + modal_id + ' #throbber');
        function searchHandler() {
            search_term = cs.val();
            $.ajax({
                url: pines.com_customer_autocustomer_url,
                dataType: "json",
                data: {"q": cs.val()},
                success: function(data) {
                    if (!data) {
                            results_array = [];
                            cust_body.html(error_message);
                            $("#" + back_to_search_id).click(function(e) {
                                cust_modal.modal('toggle');
                                cs.focus();
                            });
                            return;
                    }
                    results_array = $.map(data, function(item) {
                            return {
                                    "id": pines.safe(item.guid),
                                    "label": pines.safe(item.name),
                                    "value": item.guid+": "+item.name,
                                    "desc": "<em>"+(item.email ? " "+pines.safe(item.email) : "")+
                                            (item.phone_cell ? " "+pines.safe(item.phone_cell) :
                                            (item.phone_home ? " "+pines.safe(item.phone_home) :
                                            (item.phone_work ? " "+pines.safe(item.phone_work) : "")))+"</em>"
                            };
                    });
                    appendResults();
                }
            });
        }
        
        function appendResults() {
            cs.trigger('customer_results_opened')
            var results_length = results_array.length;
            var result_text = '<ul class="unstyled">';
            for (var i = 0; i < results_length; i++) {
                result_text += "<li class='customerselect_results' data-resultid='" + i.toString() + "'><a style='margin-bottom: 10px;' class='btn btn-block'><strong>" + results_array[i].label + "</strong><br />" + results_array[i].desc + "</a></li>";
            }
            result_text += "</ul>";
            cust_body.html(result_text);
            
            $(".customerselect_results").click(function(e){
               var index_result = $(this).attr('data-resultid');
               var customer = results_array[index_result].value;
               found_result = customer;
               cs.val(customer);
               cust_modal.modal('toggle');
               cs.trigger('customer_selected');
            });
        }
        
        function searchOrAppend() {
            if (cs.val() == '') return;
            if (cs.val() != search_term && cs.val() != found_result) {
                cust_body.html(throbber);
                searchHandler();
            }
            cust_modal.modal('toggle');
            cs.trigger('customer_modal_opened');
        }
        
        function makeSearch() {
            var h = cs.outerHeight();
			if (!h) {
				setTimeout(function(){
					makeSearch();
				}, 200);
				return;
			}
			var wrapper = '<span class="noauto-wrapper" style="position: relative; font-size: '+h+'px; line-height: '+h+'px; "></span>';
            var btn = '<a class="searchbtn" style="top: 0; height: '+h+'px; text-decoration: none; cursor: pointer; z-index: 3; position: absolute; padding: 0 5px; right: 0px;"><i style="font-size: 14px;" class="icon-search"></i></a>';
            cs.wrap(wrapper).after(btn);
            cs.next('a').click(function() {
                searchOrAppend();
            });
        }
        
        makeSearch();
        
        cs.keypress(function(e) {
            if (e.which == 13) {
                searchOrAppend();
                e.preventDefault();
            }
            
        });
        
        cs.on('customer_search', function(){
            searchOrAppend();
        });
        
        cs.on('remove_search_icon', function(){
           cs.next('a').remove(); 
        });
        
        cs.on('add_search_icon', function(){
           makeSearch();
        });
});
return all;
};
})(jQuery);