/*
 * jQuery Employee Select Without Autocomplete (employeeselect_noauto) Plugin 1.0.0
 *
 * Copyright (c) 2014 Angela Murrell, Grey Vugrin, Mohammed Ahmed
 *
 * Licensed (along with all of Pines) under the GNU Affero GPL:
 *	  http://www.gnu.org/licenses/agpl.html
 */

(function($) {
$.fn.employeeselect = function(options){
// Iterate and transform each matched element.
var all = this;
all.each(function(){
	var es = $(this);
        var results_array = [];
        var search_term = '';
        var found_result = '';
        var modal_markup = '<div id="employeemodal" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="employeeselect" aria-hidden="false"><div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button><h3 id="myModalLabel">Pick An Employee:</h3></div><div id="#employeeselect-body" class="modal-body" style="min-height: 130px;"><div style="text-align: center; margin-top: 30px;" id="throbber"><i style="font-size: 30px; margin-bottom: 10px;" class="icon-spinner icon-spin icon-large"> </i><br />Loading...</div></div></div>';
        var error_message = '<div class="alert alert-info"><h4>No Result Found</h4><p>The person may not exist, or your term is misspelled.</p><a id="emp_back_to_search" class="btn btn-info">Back to Search</a></div>';
        // Need to append modal_markup to html body
        $('body').append(modal_markup);
        var emp_modal = $('#employeemodal');
        var emp_body = $('#employeemodal .modal-body');
        var throbber = $('#employeemodal #throbber');
        function searchHandler() {
            search_term = es.val();
            $.ajax({
                url: pines.com_hrm_autoemployee_url,
                dataType: "json",
                data: {"q": es.val()},
                success: function(data) {
                    if (!data) {
                            results_array = [];
                            emp_body.html(error_message);
                            $("#emp_back_to_search").click(function(e) {
                                emp_modal.modal('toggle');
                                es.focus();
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
            es.trigger('employee_results_opened')
            var results_length = results_array.length;
            var result_text = '<ul class="unstyled">';
            for (var i = 0; i < results_length; i++) {
                result_text += "<li class='employeeselect_results' data-resultid='" + i.toString() + "'><a style='margin-bottom: 10px;' class='btn btn-block'><strong>" + results_array[i].label + "</strong><br />" + results_array[i].desc + "</a></li>";
            }
            result_text += "</ul>";
            emp_body.html(result_text);
            
            $(".employeeselect_results").click(function(e){
               var index_result = $(this).attr('data-resultid');
               var employee = results_array[index_result].value;
               found_result = employee;
               es.val(employee);
               emp_modal.modal('toggle');
               es.trigger('employee_selected');
            });
        }
        
        function searchOrAppend() {
            if (es.val() == '') return;
            if (es.val() != search_term && es.val() != found_result) {
                emp_body.html(throbber);
                searchHandler();
            }
            emp_modal.modal('toggle');
            es.trigger('employee_modal_opened');
        }
        
        function makeSearch() {
            var h = es.outerHeight();
			if (!h) {
				setTimeout(function(){
					makeSearch();
				}, 200);
				return;
			}
			var wrapper = '<span class="noauto-wrapper" style="position: relative; font-size: '+h+'px; line-height: '+h+'px; "></span>';
            var btn = '<a class="searchbtn" style="top: 0; height: '+h+'px; text-decoration: none; cursor: pointer; z-index: 3; position: absolute; padding: 0 5px; right: 0px;"><i style="font-size: 14px;" class="icon-search"></i></a>';
            es.wrap(wrapper).after(btn);
            es.next('a').click(function() {
                searchOrAppend();
            });
        }
        
        makeSearch();
        
        es.keypress(function(e) {
            if (e.which == 13) {
                searchOrAppend();
                e.preventDefault();
            }
            
        });
        
        es.on('employee_search', function(){
            searchOrAppend();
        });
        
        es.on('remove_search_icon', function(){
           es.next('a').remove(); 
        });
        
        es.on('add_search_icon', function(){
           makeSearch();
        });
});
return all;
};
})(jQuery);