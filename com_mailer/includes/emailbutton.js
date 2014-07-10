$(window).load(function(){
	// Retrieve the Email button html
	var main_container = $('#p_mailer_send_email');
	var get_email_url = main_container.attr('data-url');
	var get_email_button_content = function(){
		$.ajax({
			url: get_email_url,
			type: "POST",
			dataType: "html",
			success: function(data){
				if (data == "")
					return;
				main_container.html(data);
				email_button_js();
			}
		});		
	};
	var email_button_js = function(){
		var main_container = $('#p_mailer_send_email');
		var btn_container = main_container.find('.mailer-email-button');
		var pgrid = $('.ui-pgrid-toolbar-container').closest('div.ui-pgrid');
		var pgrid_table = pgrid.find('.ui-pgrid-table');
		var entity_class = '';
		var email_modal = main_container.find('.mailer-email-modal-container').find('.modal');
		var template_select = main_container.find('.mailer-template-select');
		var template_description = main_container.find('.mailer-template-description');
		var custom_message = main_container.find('.mailer-custom-message');
		var send_email_url = email_modal.attr('data-url');
		var send_email_btn = email_modal.find('.send-email-btn');
		var before_send = email_modal.find('.before-send');
		var sending_sent = email_modal.find('.sending-sent');
		var send_status = email_modal.find('.send-status');
		var email_edit = email_modal.find('.email-prefix-edit');
		var email_label = email_modal.find('.email-prefix-label');
		var email_input = email_modal.find('.email-prefix');
		var sender_input = email_modal.find('[name=sender]');
		var email_suffix = email_modal.find('.email-suffix').text();
		var email_container = email_modal.find('.email-container');
		var cancel_btn = email_modal.find('.cancel-btn');
		var done_btn = email_modal.find('.done-btn');

		var results_container = email_modal.find('.results-container');
		var progress = email_modal.find('.progress-container .progress');
		var partial_sent = email_modal.find('.partial-sent');
		var full_sent = email_modal.find('.full-sent');
		var full_failed = email_modal.find('.full-failed');
		var partial_sent_num = email_modal.find('.partial-sent-num');
		var partial_skipped_num = email_modal.find('.partial-skipped-num');
		var partial_failed_num = email_modal.find('.partial-failed-num');
		var partial_result_items = email_modal.find('.partial-result-item');
		var select_rows_limit = parseInt(send_email_btn.attr('data-send'));

		function get_entity_class(){
			var title = pgrid_table.find('tr.ui-state-default').first().attr('title');
			var entity = $('[data-entity='+title+']').attr('data-entity-context');
			return entity;
		}

		// Do this if there are rows loaded in the grid
		if (pgrid_table.find('td').length) {
			entity_class = get_entity_class();
		}

		// Pgrid Object
		pgrid.bind('rows_added.pgrid', function(){
			if (entity_class != '') 
				return;
			entity_class = get_entity_class();
		});

		window.mailer_send_email_modal = function(){
			var selected_rows = pgrid_table.pgrid_get_selected_rows();
			var num_rows = selected_rows.length;
			if (num_rows < 1) {
				alert('Please make a selection before performing this operation.');
				return;
			}
			if (num_rows > select_rows_limit && select_rows_limit > 0) {
				alert('You may only select '+select_rows_limit+' rows per transaction.');
				return;
			}
			var guids = [];
			selected_rows.each(function(){
				var cur_guid = $(this).attr('title');
				guids.push(parseInt(cur_guid));
			});
			guids = (JSON.stringify(guids));

			before_send.show();

			// Populate Modal
			email_modal.find('.num-rows').text(num_rows);
			email_modal.find('[name=guids]').val(guids);
			email_modal.find('[name=entity_class]').val(entity_class);

			// Reset fields
			template_select.val('').change();
			if (num_rows < 2) {
				custom_message.show();
				email_modal.find('[name=custom_message]').val('');
			} else {
				custom_message.hide();
				email_modal.find('[name=custom_message]').val('');
			}
			results_container.hide();
			partial_sent.add(full_failed).add(full_sent).hide();
			sending_sent.text('Sending');
			send_email_btn.html('<i class="icon-envelope"></i> Send Email');
			send_status.html('');
			cancel_btn.show();
			done_btn.removeClass('btn-success').addClass('btn-info');

			// Hide templates that do not work for this...
			email_modal.find('option.'+entity_class).hide();

			// Trigger Modal
			email_modal.modal();
		};

		// For each add item ...
		btn_container.find('.add-item').each(function(){
			pgrid_table.pgrid_add_toolbar_item($(this));
		});

		template_select.change(function(){
			var cur_value = template_select.val();
			var description = template_select
			.find('option[value='+cur_value+']')
			.attr('data-description');
			template_description.text(description);

			if (cur_value != '')
				send_email_btn.addClass('btn-success').removeClass('btn-info');
			else 
				send_email_btn.addClass('btn-info').removeClass('btn-success');
		}).change();


		function make_full(percent, num, class_name) {
			var div = $('<div></div>');
			var message = '<div style="width:'+percent+'%" class="bar '+class_name+'"></div>';
			for (var c = 0; c < num; c++) {
				div.append(message);
			}
			return div.html();
		}

		function make_partial(cur_num, num_rows, badge_class, bar_class) {
			var cur_percent = (cur_num / num_rows) * 100;
			var span_absolute = (cur_percent < 8) ? 'absolute-badge' : '';
			var span = '<span class="badge '+badge_class+' '+span_absolute+'">'+cur_num+'</span>';
			var div = $('<div style="width:'+cur_percent+'%; position:relative;" class="bar '+bar_class+'"></div>');
			div.html(span);
			return div;
		}



		function mailer_send_mail(){
			var values = {};
			values.email_template = email_modal.find('[name=template]').val();
			values.guids = email_modal.find('[name=guids]').val();
			values.entity_class = email_modal.find('[name=entity_class]').val();
			values.sender = email_modal.find('[name=sender]').val();
			values.custom_message = email_modal.find('[name=custom_message]').val();

			$.ajax({
				url: send_email_url,
				type: "POST",
				dataType: "json",
				data: values,
				beforeSend: function(){
					results_container.hide();
					partial_sent.add(full_failed).add(full_sent).hide();
					progress.html('');
					send_email_btn.html('<i class="icon-spin icon-spinner"></i> Sending');
				},
				error: function(){
					// You wont see the button anymore
					return;
				},
				success: function(data){
					cancel_btn.hide();
					done_btn.removeClass('btn-info').addClass('btn-success');
					var num_rows = JSON.parse(values.guids).length;
					var percent = 100 / num_rows;
					if (!data) {
						progress.append(make_full(percent, num_rows, 'bar-danger'));
						full_failed.show();
					} else if (data.failed > 0 || data.skipped > 0) {
						// Partial
						partial_result_items.hide();
						var div;
						if (data.sent > 0) {
							div = make_partial(data.sent, num_rows, 'badge-success', 'bar-success');
							progress.append(div);
							sending_sent.text('Sent');
							partial_result_items.filter('.text-success').show();
						}
						if (data.skipped > 0) {
							div = make_partial(data.skipped, num_rows, 'badge-warning', 'bar-warning');
							progress.append(div);
							partial_result_items.filter('.text-warning').show();
						}
						if (data.failed > 0) {
							div = make_partial(data.failed, num_rows, 'badge-important', 'bar-danger');
							progress.append(div);
							partial_result_items.filter('.text-error').show();
						}
						partial_sent_num.text(data.sent);
						partial_skipped_num.text(data.skipped);
						partial_failed_num.text(data.failed);

						partial_sent.show();
					} else {
						// All Sent
						progress.append(make_full(percent, num_rows, 'bar-success'));
						full_sent.show();
						sending_sent.text('Sent');
					}

					// Remember you won't see the button anymore
					before_send.hide();
					// Tell how many were not sent
					results_container.fadeIn();
				}
			});
		}

		send_email_btn.click(function(){
			if (!send_email_btn.hasClass('btn-success')) {
				send_status.html('<i class="icon-info-sign"></i> Please choose a template!')
				return;
			}
			// Maybe do some validation...
			// Call the function...
			mailer_send_mail();
		});

		email_edit.add(email_container).click(function(){
			if (!email_container.find('i').length)
				return;
			if (email_label.is(':visible')) {
				email_label.hide();
				email_input.fadeIn().select();
			}
		});

		email_input.focusout(function(){
			email_input.hide();
			var value = email_input.val();
			sender_input.val(value+email_suffix);
			email_label.text(value).fadeIn();
		});
	};
	
	
	get_email_button_content();
});