/*
 * com_cache's JS
 * 
 * @package Components\cache
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Angela Murrell <amasiell.g@gmail.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */

pines(function(){
	var container = $('.p_cache_cachemanager');
	var item_modal = container.find('.item.modal');
	var directive_modal = container.find('.edit-directive.modal');
	var refresh_items = container.find('.refresh-item');
	var refresh_submit = container.find('.refresh-submit');
	var edit_directives = container.find('td.edit-directive');
	var edit_symbol = container.find('.edit-symbol');
	var edit_helper = container.find('.edit-helper');
	var other_helpers = container.find('.helper');
	var disable_btn = container.find('.disable-directive-btn');
	var disable_name = container.find('.disable-directive-name');
	var disable_btn_name = container.find('.disable-directive-button-name');
	var delete_btn = container.find('.delete-directive-btn');
	var directive_manage = directive_modal.find('[name=manage]');
	var directive_save_btn = directive_modal.find('.item-submit');
	var directive_save_info = directive_modal.find('.save-info');
	var directive_check_edit = directive_modal.find('.check-edit');
	var add_btn = container.find('.submit-new');
	var add_info = container.find('.add-info');
	var add_fields = container.find('.add');
	var show_add_modal_btn = container.find('.show-add-modal');
	var add_modal = container.find('.add-modal');
	var add_domain_field = container.find('.add[name=domain]');
	var edit_setting_btn = container.find('.edit-setting-btn');
	var cache_setting = container.find('.cache-setting');
	var domain_refresh_submits = container.find('button.refresh-domain');
	var example_div = container.find('.example');
	var exception_modal = container.find('.exception.modal');
	var exception_readmore_link = container.find('.exception-readmore-link');
	var exception_readmore = container.find('.exception-readmore');
	var submit_exceptions = container.find('.submit-exceptions');
	var details_modal = container.find('.details.modal');
	var view_details_btns = container.find('.view-domain');
	var global_exceptions_btn = container.find('.global-exceptions-btn');
	var global_exceptions_input = container.find('.global-exceptions-input');
	var global_exceptions_modal = container.find('.global-exceptions-modal.modal');
	var global_add_exceptions = global_exceptions_modal.find('.add-exception');
	var unique_users_modal = container.find('.unique-users-modal');
	var submit_users = container.find('.submit-users');
	var check_user_hash = container.find('[name=check_user_hash]');
	var lookup_btn = container.find('.lookup-btn');
	var hash_result = container.find('.hash-result');
	var apply_hash_buttons = container.find('.apply-btn');
	
	var urls_container = container.find('.cache-urls');
	var save_edit_url = urls_container.find('.save-edit-url').text();
	var cache_manager_url = urls_container.find('.cache-manager-url').text();
	var save_exception_url = urls_container.find('.save-exception-url').text();
	var domain_explore_url = urls_container.find('.domain-explore-url').text();
	var refresh_url = urls_container.find('.refresh-url').text();
	var save_url = urls_container.find('.save-url').text();
	var check_user_hash_url = urls_container.find('.check-user-hash-url').text();
	urls_container.remove();

	function save_directive() {
		var values = {};
		values.component = (directive_modal.find('[name=component]').val() == 'Home') ? '' : directive_modal.find('[name=component]').val();
		values.caction = directive_modal.find('[name=action]').val();
		values.cachequery = directive_modal.find('[name=cachequery]').val();
		values.cacheloggedin = directive_modal.find('[name=cacheloggedin]').val();
		values.cachetime = directive_modal.find('[name=cachetime]').val();
		values.domain = directive_modal.find('[name=domain]').val();
		values.maintain_exceptions = directive_modal.find('.exception-btn').attr('data-exceptions');
		values.maintain_unique_users = directive_modal.find('.unique-users-btn').attr('data-users');
		values.manage = directive_modal.find('[name=manage]').val();

		$.ajax({
			url: save_edit_url,
			type: "POST",
			dataType: "json",
			data: values,
			error: function(){
				directive_save_btn.html('<i class="icon-undo"></i>').removeClass('btn-success').addClass('btn-danger').addClass('edit-cancel');
				return;
			},
			success: function(data){
				if (!data) {
					directive_save_btn.html('<i class="icon-undo"></i>').removeClass('btn-success').addClass('btn-danger').addClass('edit-cancel');
				} else {
					// Change header location.
					location.href = cache_manager_url;
				}
			}
		});
	}

	function change_settings(field, btn) {
		var field_value = field.val();
		var field_name = field.attr('name');
		var values = {};
		if (field_name == 'cache_on')
			values.cache_on = field_value;
		else if (field_name == 'parent_directory')
			values.parent_directory = field_value;
		else if (field_name == 'delete_cacheoptions')
			values.delete_cacheoptions = field_value;
		$.ajax({
			url: save_url,
			type: "POST",
			dataType: "json",
			data: values,
			error: function(){
				btn.html('<i class="icon-undo"></i>').removeClass('btn-success').addClass('btn-danger').addClass('setting-cancel');
				return;
			},
			success: function(data){
				if (!data) {
					btn.html('<i class="icon-undo"></i>').removeClass('btn-success').addClass('btn-danger').addClass('add-cancel').addClass('setting-cancel');
				} else {
					// Change header location.
					//directive_save_btn.html('<i class="icon-ok"></i>');
					location.href = cache_manager_url;
				}
			}
		});
	}

	function add_directive(cur_add_fields, cur_add_btn, cur_add_info) {
		var values = {};
		values.component = (cur_add_fields.filter('[name=component]').val() == 'Home') ? '' : cur_add_fields.filter('[name=component]').val();
		values.caction = cur_add_fields.filter('[name=action]').val();
		values.cachequery = cur_add_fields.filter('[name=cachequery]').val();
		values.cacheloggedin = cur_add_fields.filter('[name=cacheloggedin]').val();
		values.cachetime = cur_add_fields.filter('[name=cachetime]').val();
		values.domain = cur_add_fields.filter('[name=domain]').val();

		$.ajax({
			url: save_url,
			type: "POST",
			dataType: "json",
			data: values,
			error: function(){
				cur_add_btn.html('<i class="icon-undo"></i>').removeClass('btn-success').addClass('btn-danger').addClass('add-cancel');
				return;
			},
			success: function(data){
				if (!data) {
					cur_add_info.fadeIn();
					cur_add_btn.html('<i class="icon-undo"></i>').removeClass('btn-success').addClass('btn-danger').addClass('add-cancel');
				} else {
					// Change header location.
					//directive_save_btn.html('<i class="icon-ok"></i>');
					location.href = cache_manager_url;
				}
			}
		});
	}

	function refresh_file() {
		var values = {};
		values.domain = item_modal.find('[name=refresh_domain]').val();
		values.file_name = item_modal.find('[name=file_name]').val();

		$.ajax({
			url: refresh_url,
			type: "POST",
			dataType: "json",
			data: values,
			error: function(){
				refresh_submit.html('<i class="icon-undo"></i>').removeClass('btn-success').addClass('btn-danger').addClass('refresh-cancel');
				return;
			},
			success: function(data){
				if (!data) {
					refresh_submit.html('<i class="icon-undo"></i>').removeClass('btn-success').addClass('btn-danger').addClass('refresh-cancel');
				} else {
					// Change header location.
					location.href = cache_manager_url;
				}
			}
		});
	}

	function refresh_domain(domain) {
		var values = {};
		values.domain = domain;

		$.ajax({
			url: refresh_url,
			type: "POST",
			dataType: "json",
			data: values,
			error: function(){
				refresh_submit.html('<i class="icon-undo"></i>').removeClass('btn-success').addClass('btn-danger').addClass('refresh-cancel');
				return;
			},
			success: function(data){
				if (!data) {
					refresh_submit.html('<i class="icon-undo"></i>').removeClass('btn-success').addClass('btn-danger').addClass('refresh-cancel');
				} else {
					// Change header location.
					location.href = cache_manager_url;
				}
			}
		});
	}

	function validate_add_fields(cur_fields, cur_add_btn){
		var count = 0;
		cur_fields.each(function(){
			var field = $(this);
			if (!(field.attr('name') == 'action') && field.val() == '') {
				count++;
			}
		});
		if (count != 0) {
			cur_add_btn.removeClass('btn-success').addClass('btn-info');
		} else {
			cur_add_btn.addClass('btn-success').removeClass('btn-info');
		}
	}

	function generate_exceptions(exceptions) {
		var remove_td = '<td style="width: 30px;vertical-align:middle;" class="text-center"><button class="btn-danger btn remove-exception"><i class="icon-remove"></i></button></td>';
		var isset_html = $('<div></div>');
		var value_html = $('<div></div>');
		$.each(exceptions, function(exception_type, cur_exceptions){
			if (exception_type == 'isset') {
				$.each(cur_exceptions, function(index, name){
					var cur_isset_html = $('<tr></tr>');
					cur_isset_html.append('<td class="name">'+name+'</td>');
					cur_isset_html.append(remove_td);
					isset_html.append(cur_isset_html);
				});
			} else if (exception_type == 'value') {
				$.each(cur_exceptions, function(name, values_array){
					$.each(values_array, function(index, cur_value){
						var cur_value_html = $('<tr></tr>');
						cur_value_html.append('<td class="name">'+name+'</td>');
						cur_value_html.append('<td class="value">'+cur_value+'</td>');
						cur_value_html.append(remove_td);
						value_html.append(cur_value_html);
					});
				});
			}
		});
		var gen_exceptions = {};
		gen_exceptions.isset = isset_html.html();
		gen_exceptions.value = value_html.html();
		return gen_exceptions;
	}

	function refactor_exceptions() {
		var isset_table = exception_modal.find('.table.isset');
		var isset_rows = isset_table.find('tbody tr');
		var value_table = exception_modal.find('.table.value');
		var value_rows = value_table.find('tbody tr');

		var exceptions = {};
		exceptions.isset = [];
		exceptions.value = {};

		isset_rows.each(function(){
			var name = $(this).find('td.name').text();
			exceptions.isset.push(name);
		});
		value_rows.each(function(){
			var name = $(this).find('td.name').text();
			var value = $(this).find('td.value').text();
			if (exceptions.value[name] == undefined) {
				exceptions.value[name] = [];
			}
			exceptions.value[name].push(value);
		});

		if (!isset_rows.length && !value_rows.length)
			var new_exceptions = '';
		else
			var new_exceptions = JSON.stringify(exceptions)
		exception_modal.find('[name="exceptions"]').val(new_exceptions).change();
	}


	function save_exceptions() {
		var values = {};
		values.component = exception_modal.find('[name=component]').val();
		values.caction = exception_modal.find('[name=caction]').val();
		values.domain = exception_modal.find('[name=domain]').val();
		values.exceptions = exception_modal.find('[name=exceptions]').val();

		$.ajax({
			url: save_url,
			type: "POST",
			dataType: "json",
			data: values,
			error: function(){
				submit_exceptions.html('<i class="icon-undo"></i>').removeClass('btn-success').addClass('btn-danger').addClass('exceptions-cancel');
				return;
			},
			success: function(data){
				if (!data) {
					submit_exceptions.html('<i class="icon-undo"></i>').removeClass('btn-success').addClass('btn-danger').addClass('exceptions-cancel');
				} else {
					// Change header location.
					location.href = cache_manager_url;
				}
			}
		});
	}
	
	function generate_users(users) {
		var remove_td = '<td style="width: 30px;vertical-align:middle;" class="text-center"><button class="btn-danger btn remove-user"><i class="icon-remove"></i></button></td>';
		var users_html = $('<div></div>');
		$.each(users, function(index, cur_user){
			var cur_user_html = $('<tr></tr>');
				cur_user_html.append('<td class="name">'+cur_user+'</td>');
				cur_user_html.append(remove_td);
				users_html.append(cur_user_html);
		});
		var gen_users = {};
		gen_users = users_html.html();
		return gen_users;
	}
	
	function refactor_unique_users() {
		var users_table = unique_users_modal.find('.table.users');
		var user_rows = users_table.find('tbody tr');
		
		var users = [];

		user_rows.each(function(){
			var name = $(this).find('td.name').text();
			users.push(name);
		});

		var new_users;
		if (!user_rows.length)
			new_users = '';
		else
			new_users = JSON.stringify(users)
		unique_users_modal.find('[name="unique_users"]').val(new_users).change();
	}
	
	function save_users() {
		var values = {};
		values.component = unique_users_modal.find('[name=component]').val();
		values.caction = unique_users_modal.find('[name=caction]').val();
		values.domain = unique_users_modal.find('[name=domain]').val();
		values.all_unique = unique_users_modal.find('[name=all_unique]').val();
		values.unique_users = unique_users_modal.find('[name=unique_users]').val();
		values.save_unique_users = true;

		$.ajax({
			url: save_url,
			type: "POST",
			dataType: "json",
			data: values,
			error: function(){
				submit_users.html('<i class="icon-undo"></i>').removeClass('btn-success').addClass('btn-danger').addClass('users-cancel');
				return;
			},
			success: function(data){
				if (!data) {
					submit_users.html('<i class="icon-undo"></i>').removeClass('btn-success').addClass('btn-danger').addClass('users-cancel');
				} else {
					// Change header location.
					location.href = cache_manager_url;
				}
			}
		});
	}
	
	function get_folder_files(path, files_container){
		$.ajax({
			url: domain_explore_url,
			type: "POST",
			dataType: "json",
			data: {'path' : path},
			beforeSend: function() {
				files_container.html('<div style="margin: 30px; text-align:center;"><i class="icon-spin icon-spinner icon-2x"></i><br/><br/>Loading</div>');
			},
			error: function(){
				files_container.html('<div class="text-error" style="margin: 30px; text-align:center;"><i class="icon-remove icon-2x"></i><br/><br/>Error</div>');
				return;
			},
			success: function(data){
				if (!data) {
					files_container.html('<div class="text-error" style="margin: 30px; text-align:center;"><i class="icon-remove icon-2x"></i><br/><br/>Error</div>');
				} else if (!data.length) {
					files_container.html('<div class="text-center">There are no files in this directory.</div>');
				} else {
					var files_table = $('<table class="table table-bordered table-hover"></table>');
					var files_thead = $('<thead><th>File Name</th><th>Last Cached</th></thead>');
					var files_tbody = $('<tbody></tbody>');
					files_table.append(files_thead);

					$.each(data, function(index, value){
						var cur_tr = $('<tr></tr>');
						cur_tr.append('<td>'+pines.safe(value.filename)+'</td>');
						cur_tr.append('<td><span class="mtime" title="'+pines.safe(value.timeago)+'">'+pines.safe(value.mtime)+'</span></td>');
						files_tbody.append(cur_tr);
					});

					files_table.append(files_thead).append(files_tbody);
					files_table.find('.mtime').timeago();
					files_container.hide().html('').append(files_table).fadeIn();
				}
			}
		});
	}

	function get_domain_folders(domain, folders_container, files_container){
		$.ajax({
			url: domain_explore_url,
			type: "POST",
			dataType: "json",
			data: {'domain' : domain},
			beforeSend: function() {
				folders_container.html('<div style="margin: 30px; text-align:center;"><i class="icon-spin icon-spinner icon-2x"></i><br/><br/>Loading</div>');
				files_container.html('<div class="text-center">Select a folder from which to view files.</div>');
			},
			error: function(){
				folders_container.html('<div class="text-error" style="margin: 30px; text-align:center;"><i class="icon-remove icon-2x"></i><br/><br/>Error</div>');
				return;
			},
			success: function(data){
				if (!data) {
					folders_container.html('<div class="text-error" style="margin: 30px; text-align:center;"><i class="icon-remove icon-2x"></i><br/><br/>Error</div>');
				} else {
					var new_tree = $('<div class="new-tree"></div>');
					new_tree.jstree({
						"plugins" : [ "themes", "json_data", "ui" ],
						"json_data" : data,
						"ui" : {
							"select_limit" : 1,
							"initially_select" : ['localhost']
						}
					}).bind("select_node.jstree", function(e, data){
						// Every time I select one, try to get files.
						var path = data.inst.get_selected().attr("data-path");
						get_folder_files(path, files_container);
						// Show the files below the jstree in overflow auto.
					});
					folders_container.hide().html('').append(new_tree).fadeIn();
				}
			}
		});
	}
	
	function recalculate_exceptions(){
		global_exceptions_modal.find('.error').hide();
		global_exceptions_modal.find('.success').hide();
		
		// Get the users
		var users = [];
		global_exceptions_modal.find('.exception-row td.user').each(function(){
			var cur_user = $(this);
			var text = cur_user.text();
			if (!cur_user.hasClass('dull') && text.length)
				users.push(text);
		});
		
		// Get the groups
		var groups = [];
		global_exceptions_modal.find('.exception-row td.group').each(function(){
			var cur_group = $(this);
			var text = cur_group.text();
			if (!cur_group.hasClass('dull') && text.length)
				groups.push(text);
		});
		
		var values = {};
		values.users = users;
		values.groups = groups; 
		
		$.ajax({
			url: save_exception_url,
			type: "POST",
			dataType: "json",
			data: values,
			beforeSend: function() {
				global_exceptions_modal.find('.success').hide();
				global_exceptions_modal.find('.saving').fadeIn();
			},
			error: function(){
				global_exceptions_modal.find('.success').hide();
				global_exceptions_modal.find('.saving').hide();
				global_exceptions_modal.find('.error').fadeIn();
				return;
			},
			success: function(data){
				if (!data) {
					global_exceptions_modal.find('.success').hide();
					global_exceptions_modal.find('.saving').hide();
					global_exceptions_modal.find('.error').fadeIn();
				} else {
					global_exceptions_modal.find('.saving').hide();
					global_exceptions_modal.find('.success').fadeIn();
				}
				
				setTimeout(function(){
					global_exceptions_modal.find('.success, .error, .saving').fadeOut();
				}, 3000);
			}
		});
	}
	
	function add_global_exception(input_type) {
		var input_selector = '[name='+input_type+']';
		var before_row = global_exceptions_modal.find('.exception-new');
		var last_row = global_exceptions_modal.find('.exception-row').last();
		var input_elem = global_exceptions_modal.find(input_selector);
		var user_or_group = input_elem.val();
		
		if (user_or_group == '')
			return;
		
		var td = (input_type == 'excep_user') ? last_row.find('td').first() : last_row.find('td').last();
		
		if (input_type == 'excep_user') {
			if (td.hasClass('dull')) {
				global_exceptions_modal.find('.dull.user').first().text(user_or_group).toggleClass('dull remove-exception');
			} else {
				before_row.before('<tr class="exception-row"><td colspan="2" class="user remove-exception" style="width: 50%;">'+user_or_group+'</td><td colspan="2" class="dull group" style="width: 50%;"></td></tr>');
			}
		} else {
			if (td.hasClass('dull')) {
				global_exceptions_modal.find('.dull.group').first().text(user_or_group).toggleClass('dull remove-exception');
			} else {
				before_row.before('<tr class="exception-row"><td colspan="2" class="dull user" style="width: 50%;"></td><td colspan="2" class="group remove-exception" style="width: 50%;">'+user_or_group+'</td></tr>');
			}
		}
		
		input_elem.val('').change();
	}
	
	function refresh_by_ability(ability_hash, element) {
		var values = {};
		values.domain = 'all';
		values.ability_hash = ability_hash;

		$.ajax({
			url: refresh_url,
			type: "POST",
			dataType: "json",
			data: values,
			error: function(){
				element.html('<i class="icon-undo"></i>').removeClass('btn-success').addClass('btn-danger');
				return;
			},
			success: function(data){
				if (data === false) {
					element.html('<i class="icon-undo"></i>').removeClass('btn-success').addClass('btn-danger');
				} else {
					element.closest('.hash-result').html('<span class="text-success"><i class="icon-ok"></i> Refreshed '+data+' file(s)!</span>');
				}
			}
		});
	}
	
	function lookup_ability_hash(username) {
		var values = {};
		values.username = username;

		$.ajax({
			url: check_user_hash_url,
			type: "POST",
			dataType: "json",
			data: values,
			beforeSend: function(){
				hash_result.html('<i class="icon-spin icon-spinner"></i> Searching the Database...');
				lookup_btn.html('<i class="icon-spin icon-spinner"></i>').removeClass('btn-success btn-info').addClass('btn-danger');
				container.find('.ability-badge').hide();
				container.find('.unique-badge').hide();
			},
			error: function(){
				hash_result.html('Did not find this user or it failed.');
				lookup_btn.html('<i class="icon-undo"></i>').removeClass('btn-success btn-info').addClass('btn-danger');
				return;
			},
			success: function(data){
				if (!data) {
					hash_result.html('Did not find this user or it failed.');
					lookup_btn.html('<i class="icon-undo"></i>').removeClass('btn-success btn-info').addClass('btn-danger');
				} else {
					// Change header location.
					lookup_btn.html('<i class="icon-undo"></i>').removeClass('btn-danger btn-info').addClass('btn-success');
					
					var ability_html = '<table class="table hash-result-table table-bordered"><tbody><tr><td class="text-center hash"><input type="text" class="hash-label full-field text-center" value="'+pines.safe(data.ability_hash)+'"></td><td class="text-center hash-control"><span class="hash-refresh"><i class="icon-refresh"></i></span></td></tr></tbody></table>';
					hash_result.filter('.ability').html(ability_html);
					
					var ability_class = (data.ability_count > 0) ? 'badge-success' : 'badge-info';
					container.find('.ability-badge').removeClass('badge-success badge-info').addClass(ability_class).text(data.ability_count).fadeIn();
					
					if (data.unique_hash != undefined) {
						var unique_html = '<table class="table hash-result-table table-bordered"><tbody><tr><td class="text-center hash"><input type="text" class="hash-label full-field text-center" value="'+pines.safe(data.unique_hash)+'"></td><td class="text-center hash-control"><span class="hash-refresh"><i class="icon-refresh"></i></span></td></tr></tbody></table>';
						hash_result.filter('.unique').html(unique_html);
						
						var unique_class = (data.unique_count > 0) ? 'badge-success' : 'badge-info';
						container.find('.unique-badge').removeClass('badge-success badge-info').addClass(unique_class).text(data.unique_count).fadeIn();
					} else {
						hash_result.filter('.unique').html('No unique hash.');
					}
					
					hash_result.find('.hash-label').click(function(){
						$(this).select();
					});
				}
			}
		});
		lookup_btn.addClass('clear');
	}
	
	lookup_btn.click(function(){
		if (lookup_btn.hasClass('clear')) {
			check_user_hash.val('');
			hash_result.html(hash_result.attr('data-orig'));
			lookup_btn.html('<i class="icon-search"></i>').removeClass('btn-danger btn-success clear').addClass('btn-info');
			check_user_hash.focus();
			container.find('.ability-badge').hide();
			container.find('.unique-badge').hide();
			return;
		}
		var username = check_user_hash.val();
		if (username == '')
			return;
		lookup_ability_hash(username);
	});
	
	check_user_hash.focusin(function(){
		if (lookup_btn.hasClass('clear')) {
			check_user_hash.val('');
			container.find('.ability-badge').hide();
			container.find('.unique-badge').hide();
			hash_result.html(hash_result.attr('data-orig'));
			lookup_btn.html('<i class="icon-search"></i>').removeClass('btn-danger btn-success clear').addClass('btn-info');
		}
	});
	
	check_user_hash.keypress(function(e){
		var cur_input = $(this);
		if (cur_input.val() != '' && e.which == 13) {
			lookup_btn.click();
		} else if (lookup_btn.hasClass('clear')) {
			container.find('.ability-badge').hide();
			container.find('.unique-badge').hide();
			hash_result.html(hash_result.attr('data-orig'));
			lookup_btn.html('<i class="icon-search"></i>').removeClass('btn-danger btn-success clear').addClass('btn-info');
		}
	});
	
	container.on('click', '.hash-refresh', function(){
		var element = $(this);
		var hash = element.closest('tr').find('.hash input').val();
		element.html('<i class="icon-spin icon-spinner"></i>');
		refresh_by_ability(hash, element);
	});
	
	var get_readmore_heights = function(selector){
		container.find(selector+'.readmore').each(function(){
			var cur_readmore = $(this);
			if (cur_readmore.attr('data-height') == undefined) {
				cur_readmore.css('visibility', 'hidden');
				cur_readmore.css('height', 'auto');
				var height = cur_readmore.outerHeight();
				cur_readmore.removeAttr('style');
				cur_readmore.attr('data-height', height);
			}
		});
	};
	get_readmore_heights('li.span4');
	
	container.on('click', '.readmore', function(){
		var element = $(this);
		var readless = element.find('.readless');
		element.removeClass('readmore');
		element.animate({'height': element.attr('data-height')}, 'fast');
		setTimeout(function(){
			readless.addClass('in');
		}, 350);
	});

	container.on('click', '.readless', function(){
		$(this).removeClass('in').closest('li, .alert').removeAttr('style').addClass('readmore');
	});

	refresh_items.click(function(){
		var name = $(this).attr('data-name');
		var file_name = $(this).attr('data-filename');
		item_modal.find('.item-name').text(name);
		item_modal.find('[name=file_name]').val(file_name);
		item_modal.modal();
	});

	item_modal.find('[name=refresh_domain]').change(function(){
		if ($(this).val() != '')
			refresh_submit.addClass('btn-success').removeClass('btn-info');
		else {
			refresh_submit.removeClass('btn-success btn-danger').addClass('btn-info');
		}
	});

	refresh_submit.click(function(){
		if (refresh_submit.hasClass('refresh-success')) {
			refresh_submit.removeClass('btn-danger').addClass('btn-info')
			.html('Refresh');
			item_modal.find('[name=refresh_domain]').val();
			return;
		}
		if (!refresh_submit.hasClass('btn-success'))
			return;
		refresh_submit.html('<i class="icon-spin icon-spinner"></i>');
		refresh_file();
	});

	domain_refresh_submits.click(function(){
		var cur_domain_refresh = $(this);
		var domain = cur_domain_refresh.attr('data-domain');
		if (cur_domain_refresh.hasClass('refresh-success')) {
			cur_domain_refresh.removeClass('btn-danger')
			.html('<i class="icon-refresh"></i>');
			return;
		}
		if (cur_domain_refresh.hasClass('btn-danger'))
			return;
		cur_domain_refresh.html('<i class="icon-spin icon-spinner"></i>');
		refresh_domain(domain);
	});

	edit_directives.hover(function(){
		var cell = $(this);
		var width = cell.width();
		var offset = cell.offset();
		var top = offset.top;
		var left = offset.left;
		edit_symbol.attr('style','position: absolute; top:'+(top+5)+'px; left:'+(left+width)+'px;');
		if (cell.hasClass('no-icon'))
			return;
		edit_symbol.show();
	}, function(){
		edit_symbol.hide();
	});

	edit_directives.click(function(){
		var cell = $(this);
		var tr = cell.closest('tr');
		var name = tr.find('[data-name]').attr('data-name');
		var tr_component = tr.find('td.component').text();
		var tr_action = tr.find('td.action').text();
		var tr_cachequery = tr.find('td.cachequery').text().replace(/\s/g, '');
		var tr_cacheloggedin = tr.find('td.cacheloggedin').text().replace(/\s/g, '');
		var tr_cachetime = tr.find('td.cachetime').text();
		var tr_domain = tr.find('td.domain').text();
//		var exception_btn = (tr.find('.exception-btn').length) ? tr.find('.exception-btn') : undefined;

		// Find exception/unique users btns and remove them.
		directive_modal.find('.exception-btn').remove();
		directive_modal.find('.unique-users-btn').remove();
		directive_modal.find('.item-name').text(name);
		directive_modal.find('[name=component]').val(tr_component);
		directive_modal.find('[name=action]').val(tr_action);
		// Set all options to false selected.
		directive_modal.find('option').prop('selected', false);
		directive_modal.find('[name=cachequery]').find("option:contains('"+tr_cachequery+"')").prop('selected', true);
		directive_modal.find('[name=cachequery]').attr('data-orig', tr_cachequery);

		var exception_span = tr.find('.exceptions-span');
		var exception_btn = tr.find('.exception-btn');
		var tr_exceptions = (exception_btn.length) ? exception_btn.attr("data-exceptions").replace(/"/g, '&quot;') : ((exception_span.length) ? exception_span.attr('data-exceptions').replace(/"/g, '&quot;') : '');
		var btn_class = (exception_btn.hasClass('btn-success')) ? 'btn-success' : '';
		var exceptions_button = '<button style="width:48%; float:right;padding:4px;" class="btn btn-mini exception-btn '+btn_class+'" data-name="'+name+'" data-component="'+tr_component+'" data-action="'+tr_action+'" data-domain="'+tr_domain+'" data-exceptions="'+tr_exceptions+'">Exceptions</button>';
		if (exception_btn.length) {
			directive_modal.find('[name=cachequery]').after(exceptions_button).css('width', '48%').removeClass('full-field');
		} else {
			directive_modal.find('[name=cachequery]').removeAttr('style').addClass('full-field').after(exceptions_button);
			directive_modal.find('.exception-btn').addClass('hide');
		}
		
		var users_span = tr.find('.unique-users-span');
		var users_btn = tr.find('.unique-users-btn');
		var tr_users = (users_btn.length) ? users_btn.attr("data-users").replace(/"/g, '&quot;') : ((users_span.length) ? users_span.attr('data-users').replace(/"/g, '&quot;') : '');
		var users_btn_class = (users_btn.hasClass('btn-success')) ? 'btn-success' : '';
		var users_button = '<button style="width:48%; float:right;padding:4px;" class="btn btn-mini unique-users-btn '+users_btn_class+'" data-name="'+name+'" data-component="'+tr_component+'" data-action="'+tr_action+'" data-domain="'+tr_domain+'" data-users="'+tr_users+'">Unique Users</button>';
		if (users_btn.length) {
			directive_modal.find('[name=cacheloggedin]').after(users_button).css('width', '48%').removeClass('full-field');
		} else {
			directive_modal.find('[name=cacheloggedin]').removeAttr('style').addClass('full-field').after(users_button);
			directive_modal.find('.unique-users-btn').addClass('hide');
		}
		
		directive_modal.find('[name=cacheloggedin]').find("option:contains('"+tr_cacheloggedin+"')").prop('selected', true);
		directive_modal.find('[name=cacheloggedin]').attr('data-orig', tr_cacheloggedin);
		directive_modal.find('[name=cachetime]').val(tr_cachetime).attr('data-orig', tr_cachetime);
		directive_modal.find('[name=domain]').val(tr_domain).attr('data-orig', tr_domain);

		// Make it say enable for the disable section:
		if (tr.find('span[data-name]').length && tr.find('span[data-name]').text() != ('No Files')) {
			disable_name.text('Enable');
			disable_btn_name.text('Enable');
			disable_btn.addClass('btn-success').removeClass('btn-warning');
		} else {
			disable_name.text('Disable');
			disable_btn_name.text('Only Disable');
			disable_btn.removeClass('btn-success').addClass('btn-warning');
		}
		directive_save_btn.addClass('btn-info').removeClass('btn-success');
		directive_save_info.hide();
		cell.addClass('cur-edit');
		directive_modal.modal();
	});

	container.find('[name=cachetime]').keyup(function(){
		this.value = this.value.replace(/\D/, '');
	});

	edit_helper.add(other_helpers).tooltip();

	disable_btn.click(function(){
		if (disable_btn.hasClass('disabled'))
			return;

		disable_btn.toggleClass('active');
		if (disable_btn.hasClass('active')) {
			// Dim out the delete button, alter the input
			delete_btn.addClass('disabled');
			if (disable_btn.hasClass('btn-success'))
				directive_manage.val('enable').change();
			else
				directive_manage.val('disable').change();
		} else {
			delete_btn.removeClass('disabled');
			directive_manage.val('').change();
		}
	});

	delete_btn.click(function(){
		if (delete_btn.hasClass('disabled'))
			return;

		delete_btn.toggleClass('active');
		if (delete_btn.hasClass('active')) {
			// Dim out the delete button, alter the input
			disable_btn.addClass('disabled');
			directive_manage.val('delete').change();
		} else {
			disable_btn.removeClass('disabled');
			directive_manage.val('').change();
		}
	});

	directive_manage.change(function(){
		if (directive_manage.val() != '') {
			directive_save_btn.removeClass('btn-info').addClass('btn-success');
			directive_save_info.fadeIn();
		} else if (!directive_manage.hasClass('altered')) {
			// If other fields altered it, we wouldnt want to undo the save info or btn-success
			directive_save_btn.addClass('btn-info').removeClass('btn-success');
			directive_save_info.hide();
		}
	});

	directive_check_edit.change(function(){
		var check = $(this);
		if (check.val() != check.attr('data-orig')) {
			directive_save_btn.removeClass('btn-info').addClass('btn-success');
			directive_save_info.fadeIn();
			directive_manage.addClass('altered');
		} else if (directive_manage.val() == '') {
			directive_save_btn.addClass('btn-info').removeClass('btn-success');
			directive_save_info.hide();
		}
	});

	directive_save_btn.click(function(){
		if (!directive_save_btn.hasClass('btn-success'))
			return;
		else {
			directive_save_btn.html('<i class="icon-spin icon-spinner"></i>');
			// Use ajax
			// Change window location to refresh this whole page.
			save_directive();
		}
	});

	add_fields.change(function(){
		var tr_parent = $(this).closest('tr.add-parent');
		var modal_form = (!tr_parent.length);
		var cur_add_fields = (modal_form) ? add_modal.find('.add') : tr_parent.find('.add');
		var cur_add_btn = (modal_form) ? add_modal.find('.submit-new') : tr_parent.find('.submit-new');

		add_info.hide();
		if (cur_add_fields.filter('[name=component]').val() == 'Home')
			cur_add_fields.filter('[name=action]').val('').addClass('disabled').attr('disabled', 'disabled');
		else
			cur_add_fields.filter('[name=action]').removeClass('disabled').removeAttr('disabled');

		validate_add_fields(cur_add_fields, cur_add_btn);
	});

	add_btn.click(function(){
		var cur_add_btn = $(this);
		var cur_add_parent = cur_add_btn.closest('.add-parent');
		var cur_add_fields = cur_add_parent.find('.add');
		var cur_add_info = (cur_add_parent.find('.add-info').length) ? cur_add_parent.find('.add-info') : container.find('.add-info-after-table');

		if (!cur_add_btn.hasClass('btn-success'))
			return;
		else {
			cur_add_btn.html('<i class="icon-spin icon-spinner"></i>');
			// Use ajax
			// Change window location to refresh this whole page.
			add_directive(cur_add_fields, cur_add_btn, cur_add_info);
		}
	});

	show_add_modal_btn.click(function(){
		// reset all fields.
		add_btn.removeClass('btn-success').addClass('.btn-info');
		add_info.hide();
		add_fields.removeClass('disabled').removeAttr('disabled').val('');
		add_modal.modal();
	});

	container.on('click', '.add-cancel', function(){
		var cur_btn = $(this);
		if (cur_btn.hasClass('submit-new')) {
			cur_btn.removeClass('add-cancel');
		}
		add_btn.removeClass('btn-success btn-danger').addClass('btn-info').html('<i class="icon-plus"></i> Add');
		add_info.hide();
		add_fields.removeClass('disabled').removeAttr('disabled').val('');
	});

	container.on('click', '.edit-cancel', function(){
		var cur_btn = $(this);
		if (cur_btn.hasClass('item-submit')) {
			cur_btn.removeClass('edit-cancel');
		}
		directive_save_btn.removeClass('btn-success btn-danger').addClass('btn-info').html('Save');
		directive_save_info.hide();
		disable_btn.add(delete_btn).removeAttr('disabled').removeClass('active disabled');
		directive_manage.val('');
		directive_modal.modal('hide');
	});

	add_domain_field.focusin(function(){
		var cur_domain_field = $(this);
		if (cur_domain_field.val() == '') {
			cur_domain_field.val('all').select();
		}
	}).focusout(function(){
		$(this).change();
	});

	edit_setting_btn.click(function(){
		var cur_btn = $(this);
		var setting_field = cur_btn.closest('tr').find('.cache-setting');
		if (cur_btn.hasClass('btn-info')) {
			if (setting_field.val() == setting_field.attr('data-orig')) {
				setting_field.attr('disabled', 'disabled');
				cur_btn.removeClass('btn-success btn-info');
			}
		} else if (cur_btn.hasClass('btn-success')) {
			// Save it
			// Check if import
			if (cur_btn.hasClass('import')) {
				var import_value = container.find('[name=import]').val();
				import_value = import_value.replace(/\..*$/, '');
				if (cache_manager_url.match(/\?/)) {
					var cache_url = cache_manager_url+'&import='+import_value;
				} else {
					var cache_url = cache_manager_url+'?import='+import_value;
				}
				location.href = cache_url;
				return;
			}
			cur_btn.html('<i class="icon-spin icon-spinner"></i>')
			change_settings(setting_field, cur_btn);
		} else if (cur_btn.hasClass('setting-cancel')) {
			// Revert to original setting: 
			setting_field.val(setting_field.attr('data-orig'));
			cur_btn.removeClass('btn-danger setting-cancel').addClass('btn-info')
			.html('<i class="icon-pencil"></i> Edit');
		} else {
			setting_field.removeAttr('disabled');
			cur_btn.addClass('btn-info');
		}
	});

	cache_setting.change(function(){
		var cur_setting = $(this);
		var cur_btn = cur_setting.closest('tr').find('.edit-setting-btn');
		if (cur_setting.val() != cur_setting.attr('data-orig')) {
			cur_btn.html('<i class="icon-save"></i> Save').addClass('btn-success').removeClass('btn-danger btn-info');
		} else {
			cur_btn.html('<i class="icon-pencil"></i> Edit').removeClass('btn-success').addClass('btn-info');
		}
	});

	container.find('.show-import').click(function(){
		container.find('.show-import-table').fadeIn();
	});

	container.find('.show-example').click(function(){
		if (example_div.is(':visible'))
			example_div.fadeOut();
		else {
			example_div.fadeIn();
			get_readmore_heights('.example-info');
		}
	});

	// DIRECTIVE EXCEPTIONS

	container.on('click', '.exception-btn', function(){
		var cur_btn = $(this);
		var name = cur_btn.attr('data-name');
		var component = cur_btn.attr('data-component');
		var action = cur_btn.attr('data-action');
		var domain = cur_btn.attr('data-domain');
		var get_exceptions = cur_btn.attr('data-exceptions');
		if (get_exceptions == '') {
			var isset_html = '';
			var value_html = '';
		} else {
			var clean_exceptions = get_exceptions.replace(/&quot;/, '"');
			var exceptions = JSON.parse(clean_exceptions);
			var isset_html = generate_exceptions(exceptions).isset;
			var value_html = generate_exceptions(exceptions).value;
		}
		exception_modal.find('.item-name').text(name);
		exception_modal.find('[name=component]').val(component);
		exception_modal.find('[name=caction]').val(action);
		exception_modal.find('[name=domain]').val(domain);
		exception_modal.find('[name=orig_exceptions]').val(get_exceptions);
		exception_modal.find('[name=exceptions]').val(get_exceptions);
		exception_modal.find('.table.isset tbody').html(isset_html);
		exception_modal.find('.table.value tbody').html(value_html);

		exception_modal.find('.remove-exception').click(function(){
			var tr = $(this).closest('tr');
			tr.remove();
			// Refactor the hidden input.
			refactor_exceptions();
		});

		exception_modal.modal();
	});

	exception_readmore_link.click(function(){
		exception_readmore.toggleClass('hide');
		refactor_exceptions();
	});

	exception_modal.find('.add-isset-btn').click(function(){
		var isset_table = exception_modal.find('.table.isset tbody');
		var tr = $(this).closest('tr');
		var name_input = tr.find('[name=add_isset]');
		var name = name_input.val();
		if (name == '')
			return;

		var validate = true;
		// Check if name and value combo exists?
		isset_table.find('td.name').each(function(){
			var cur_td = $(this);
			if (cur_td.text() == name) {
				pines.notice('That variable name is already an exception.', 'notice');
				validate = false;
				return;
			}
		});
		name_input.val('');
		if (!validate)
			return;
		var remove_td = '<td style="width: 30px;vertical-align:middle;" class="text-center"><button class="btn-danger btn remove-exception"><i class="icon-remove"></i></button></td>';
		var isset = $('<tr></tr>');

		isset.append('<td class="name">'+name+'</td>');
		isset.append(remove_td);

		isset_table.append(isset);
		exception_modal.find('.remove-exception').click(function(){
			var tr = $(this).closest('tr');
			tr.remove();
			// Refactor the hidden input.
			refactor_exceptions();
		});
		refactor_exceptions();
	});

	exception_modal.find('.add-value-btn').click(function(){
		var value_table = exception_modal.find('.table.value tbody');
		var tr = $(this).closest('tr');
		var name_input = tr.find('[name=add_value_name]');
		var name = name_input.val();
		var value_input = tr.find('[name=add_value]')
		var cur_value = value_input.val();
		if (name == '' || cur_value == '')
			return;

		var validate = true;
		// Check if name and value combo exists?
		value_table.find('td.name').each(function(){
			var cur_td = $(this);
			if (cur_td.text() == name) {
				var value_td = cur_td.closest('tr').find('td.value').text();
				if (value_td == cur_value) {
					pines.notice('That combination of name and value exists.', 'notice');
					validate = false;
					return;
				}
			}
		});
		name_input.add(value_input).val('');
		if (!validate)
			return;
		var remove_td = '<td style="width: 30px;vertical-align:middle;" class="text-center"><button class="btn-danger btn remove-exception"><i class="icon-remove"></i></button></td>';
		var value = $('<tr></tr>');

		value.append('<td class="name">'+name+'</td>');
		value.append('<td class="value">'+cur_value+'</td>');
		value.append(remove_td);

		value_table.append(value);
		exception_modal.find('.remove-exception').click(function(){
			var tr = $(this).closest('tr');
			tr.remove();
			// Refactor the hidden input.
			refactor_exceptions();
		});
		refactor_exceptions();
	});

	exception_modal.find('[name=exceptions]').change(function(){
		var cur_exceptions = $(this);
		var cur_exceptions_value = cur_exceptions.val();
		var orig_exceptions = exception_modal.find('[name=orig_exceptions]').val();

		if (orig_exceptions == cur_exceptions_value) {
			submit_exceptions.addClass('btn-info').removeClass('btn-success');
		} else {
			submit_exceptions.removeClass('btn-info').addClass('btn-success');
		}
	});

	submit_exceptions.click(function(){
		if (submit_exceptions.hasClass('btn-success')) {
			// Submit for saving
			submit_exceptions.html('<i class="icon-spin icon-spinner"></i>');
			save_exceptions();
		} else if (submit_exceptions.hasClass('exceptions-cancel')) {
			submit_exceptions.html('<i class="icon-plus"></i> Save').addClass('btn-info').removeClass('btn-success btn-danger');
		}
	});

	// The actual cancel button.
	container.find('.cancel-exceptions').click(function(){
		submit_exceptions.html('<i class="icon-plus"></i> Save').addClass('btn-info').removeClass('btn-success btn-danger');
	});
	
	// Details
	view_details_btns.click(function(){
		var domain = $(this).attr('data-domain');
		details_modal.find('.item-name').text(domain);
		var folders_container = details_modal.find('.jstree-container');
		var files_container = details_modal.find('.files-container');
		// Ajax Call to get json_data for jstree
		// Make a jstree in that ajax call.
		get_domain_folders(domain, folders_container, files_container);
		details_modal.modal();
	});
	
	var num_directives = container.find('.directives-table tbody tr').length;
	container.find('.num-directives').html(num_directives - 1); // the add one.
	
	// GLOBAL EXCEPTIONS
	
	global_exceptions_btn.click(function(){
		// Bring up the modal for editing global exceptions.
		global_exceptions_modal.modal();
	});
	
	global_add_exceptions.click(function(){
		var btn = $(this);
		var input_type = btn.attr('data-input');
		var input = global_exceptions_modal.find('[name='+input_type+']')
		if (input.val() == '')
			return;
		add_global_exception(input_type);
		recalculate_exceptions();
	});
	
	global_exceptions_input.change(function(){
		var cur_input = $(this);
		var cur_btn = global_exceptions_modal.find('[data-input='+cur_input.attr('name')+']');
		if (cur_input.val() != '') {
			cur_btn.addClass('btn-success');
		} else
			cur_btn.removeClass('btn-success');
	});
	
	global_exceptions_input.keypress(function(e){
		var cur_input = $(this);
		var cur_btn = global_exceptions_modal.find('[data-input='+cur_input.attr('name')+']');
		if (cur_input.val() != '' && e.which == 13) {
			cur_btn.click();
		}
	});
	
	global_exceptions_modal.on('click', '.remove-exception', function(){
		var td = $(this);
		var tr = td.closest('tr');
		var user = td.hasClass('user');
		var other_td = (user) ? tr.find('.group') : tr.find('.user');
		var tbody = td.closest('tbody');
		
		if (other_td.hasClass('dull')) {
			tr.remove();
		} else {
			td.remove(); // get rid of this so it won't count below.
			var user_tds = tbody.find('td.user').filter(':not(.dull)');
			var group_tds = tbody.find('td.group').filter(':not(.dull)');
			tbody.find('.exception-row').remove();
			var length = (user_tds.length < group_tds.length) ? group_tds.length : user_tds.length;
			for (var i = 0; i < length; i++) {
				var new_tr = $('<tr class="exception-row"></tr>');
				var user_td = user_tds.eq(i);
				var group_td = group_tds.eq(i);
				
				if (!user_td.length && !group_td.length)
					break;
				
				if (user_td.length)
					new_tr.append(user_td);
				else 
					new_tr.append('<td style="width: 50%;" class="user dull" colspan="2" data-here="sad"></td>');
				
				if (group_td.length)
					new_tr.append(group_td);
				else 
					new_tr.append('<td style="width: 50%;" class="group dull" colspan="2" data-here="mad"></td>');
				
				tbody.find('.exception-new').before(new_tr);
			}
		}
		recalculate_exceptions();
	});
	
	//	UNIQUE USERS
	
	container.on('click', '.unique-users-btn', function(){
		var cur_btn = $(this);
		var name = cur_btn.attr('data-name');
		var component = cur_btn.attr('data-component');
		var action = cur_btn.attr('data-action');
		var domain = cur_btn.attr('data-domain');
		var all_unique = !(cur_btn.attr('data-all') == 'false');
		var get_unique_users = cur_btn.attr('data-users');
		var users_html;
		if (get_unique_users == '') {
			users_html = '';
		} else {
			var clean_users = get_unique_users.replace(/&quot;/, '"');
			var users = JSON.parse(clean_users);
			users_html = generate_users(users);
		}
		
		// adjust toggles
		
		unique_users_modal.find('.btn-group .btn').removeClass('active');
		var apply_selector = (all_unique) ? '.apply-to-all' : '.apply-to-users';
		unique_users_modal.find(apply_selector).addClass('active');
		
		// Adjust if table looks disabled...
		if (all_unique) {
			unique_users_modal.find('.users').addClass('disabled');
			unique_users_modal.find('.users-form [name=add_user]').attr('disabled', 'disabled');
		} else {
			unique_users_modal.find('.users').removeClass('disabled');
			unique_users_modal.find('.users-form [name=add_user]').removeAttr('disabled');
		}
	
		unique_users_modal.find('.item-name').text(name);
		unique_users_modal.find('[name=component]').val(component);
		unique_users_modal.find('[name=caction]').val(action);
		unique_users_modal.find('[name=domain]').val(domain);
		unique_users_modal.find('[name=orig_unique_users]').val(get_unique_users);
		unique_users_modal.find('[name=unique_users]').val(get_unique_users);
		unique_users_modal.find('[name=all_unique]').val(all_unique);
		unique_users_modal.find('[name=orig_all_unique]').val(all_unique);
		unique_users_modal.find('.table.users tbody').html(users_html);

		unique_users_modal.find('.remove-user').click(function(){
			if (unique_users_modal.find('.users').hasClass('disabled'))
				return;
			var tr = $(this).closest('tr');
			tr.remove();
			// Refactor the hidden input.
			refactor_unique_users();
		});

		unique_users_modal.modal();
	});
	
	apply_hash_buttons.click(function(){
		var cur_btn = $(this);
		if (cur_btn.hasClass('active'))
			return;
		apply_hash_buttons.removeClass('active');
		cur_btn.addClass('active');
		var cur_val;
		// Switch the user table class
		if (cur_btn.hasClass('apply-to-all')) {
			cur_val = true;
			unique_users_modal.find('.users').addClass('disabled');
			unique_users_modal.find('.users-form [name=add_user]').attr('disabled', 'disabled');
		} else {
			cur_val = false;
			unique_users_modal.find('.users').removeClass('disabled');
			unique_users_modal.find('.users-form [name=add_user]').removeAttr('disabled');
		}
		// Switch input value...
		unique_users_modal.find('[name=all_unique]').val(cur_val);
		var orig_val = !(unique_users_modal.find('[name=orig_all_unique]').val() == 'false');
		
		// preserve btn-success if change is from users..?
		var cur_users_value = unique_users_modal.find('[name=unique_users]').val();
		var orig_users = unique_users_modal.find('[name=orig_unique_users]').val();
		
		if ((orig_val == cur_val) && (orig_users == cur_users_value)) {
			submit_users.addClass('btn-info').removeClass('btn-success');
		} else {
			// One of the above is "changed".. meaning we need to save changes.
			submit_users.removeClass('btn-info').addClass('btn-success');
		}
		
	});
	
	// The actual cancel button.
	container.find('.cancel-users').click(function(){
		submit_users.html('<i class="icon-plus"></i> Save').addClass('btn-info').removeClass('btn-success btn-danger');
	});
	
	submit_users.click(function(){
		if (submit_users.hasClass('btn-success')) {
			// Submit for saving
			submit_users.html('<i class="icon-spin icon-spinner"></i>');
			save_users();
		} else if (submit_users.hasClass('users-cancel')) {
			submit_users.html('<i class="icon-plus"></i> Save').addClass('btn-info').removeClass('btn-success btn-danger');
		}
	});
	
	unique_users_modal.find('[name=unique_users]').change(function(){
		var cur_users = $(this);
		var cur_users_value = cur_users.val();
		var orig_users = unique_users_modal.find('[name=orig_unique_users]').val();

		if (orig_users == cur_users_value) {
			submit_users.addClass('btn-info').removeClass('btn-success');
		} else {
			submit_users.removeClass('btn-info').addClass('btn-success');
		}
	});
	
	unique_users_modal.find('.add-user-btn').click(function(){
		var user_table = unique_users_modal.find('.table.users tbody');
		var tr = $(this).closest('tr');
		var name_input = tr.find('[name=add_user]');
		var name = name_input.val();
		if (name == '')
			return;

		var validate = true;
		// Check if name and value combo exists?
		user_table.find('td.name').each(function(){
			var cur_td = $(this);
			if (cur_td.text() == name) {
				pines.notice('That username is already listed.', 'notice');
				validate = false;
				return;
			}
		});
		name_input.val('');
		if (!validate)
			return;
		var remove_td = '<td style="width: 30px;vertical-align:middle;" class="text-center"><button class="btn-danger btn remove-user"><i class="icon-remove"></i></button></td>';
		var user = $('<tr></tr>');

		user.append('<td class="name">'+name+'</td>');
		user.append(remove_td);

		user_table.append(user);
		unique_users_modal.find('.remove-user').click(function(){
			var tr = $(this).closest('tr');
			tr.remove();
			// Refactor the hidden input.
			refactor_unique_users();
		});
		refactor_unique_users();
	});
});