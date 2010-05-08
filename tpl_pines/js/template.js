pines(function(){
	// Give hover effects to elements.
	var hover = function(elements){
		(typeof elements == "string" ? $(elements) : elements).live("mouseenter", function(){
			$(this).addClass("ui-state-hover");
		}).live("mouseleave", function(){
			$(this).removeClass("ui-state-hover");
		});
	};
	$.pnotify.defaults.pnotify_opacity = .9;
	// Maximize modules.
	hover($(".module .module_maximize").live("click", function(){
		$(this).closest(".module").toggleClass("module_maximized");
	}));
	// Shade modules.
	hover($(".module .module_minimize").live("click", function(){
		$(this).children("span.ui-icon").toggleClass("ui-icon-triangle-1-n").toggleClass("ui-icon-triangle-1-s")
		.end().parent().nextAll(".module_content").slideToggle("normal");
	}));
	// Menu hover.
	hover(".dropdown li a");

	// Get the loaded page ready. (Styling, etc.)
	// This needs to be called after Ajax page loads.
	var page_ready = function(){
		// Main menu corners.
		$("body > div#header > div.mainmenu").find(".dropdown > li:first-child > a").addClass("ui-corner-left").end()
		.find(".dropdown > li:last-child > a").addClass("ui-corner-right").end()
		.find(".dropdown ul > li:first-child > a").addClass("ui-corner-tr").end()
		.find(".dropdown ul > li:last-child > a").addClass("ui-corner-bottom");

		// Add disabled element styling.
		$(".ui-widget-content:input:disabled").addClass("ui-state-disabled");
		$(".ui-widget-content:input:not(:button, :submit, :reset), .ui-widget-content:file").addClass("ui-corner-all");

		// UI buttons.
		$(".module .ui-state-default:input:not(:not(:button, :submit, :reset))").button();
	};
	
	page_ready();

	// Experimental AJAX code.
	// Quit here if ajax isn't enabled.
	if (!pines.tpl_pines_ajax) return;

	var loader, j_window = $(window),
	pos_head = $("head"),
	main_menu = $("body > div#header > div.mainmenu > div.menuwrap"),
	pos_top = $("body > div#top"),
	page_title = $("body > div#header > h1.pagetitle"),
	pos_pre_content = $("body > div#pre_content"),
	col1 = $("body > div.colmask > div.colmid > div.colleft > div.col1wrap > div.col1"),
	pos_content_top_left = col1.children("div.content_top_left"),
	pos_content_top_right = col1.children("div.content_top_right"),
	pos_content = col1.children("div.content"),
	pos_content_bottom_left = col1.children("div.content_bottom_left"),
	pos_content_bottom_right = col1.children("div.content_bottom_right"),
	pos_post_content = $("body > div#post_content"),
	pos_left = $("body > div.colmask > div.colmid > div.colleft > div.col2"),
	pos_right = $("body > div.colmask > div.colmid > div.colleft > div.col3"),
	pos_footer = $("body > div#footer > div.modules"),
	pos_bottom = $("body > div#bottom");
	var load_page_ajax = function(url){
		$.ajax({
			"type": "GET",
			"url": url,
			"dataType": "json",
			beforeSend: function(xhr) {
				if (loader)
					loader.pnotify_display();
				else
					loader = $.pnotify({
						pnotify_text: "Loading...",
						pnotify_notice_icon: "icon picon_16x16_throbber",
						pnotify_width: "120px",
						pnotify_opacity: .6,
						pnotify_animate_speed: "fast",
						pnotify_nonblock: true,
						pnotify_hide: false,
						pnotify_history: false,
						pnotify_stack: {"dir1": "down","dir2": "right"}
					}).css("top", "-.6em");
				loader.css("left", (j_window.width() / 2) - (loader.width() / 2));
				xhr.setRequestHeader("Accept", "application/json");
			},
			complete: function(){
				loader.pnotify_remove();
			},
			error: function(xhr, textStatus){
				pines.error("An error occured while communicating with the server:\n\n"+xhr.status+": "+textStatus);
			},
			success: function(data){
				// Pause DOM ready script execution.
				pines.pause();
				$("body > div#header > div:not(.mainmenu)").remove();
				pos_head.append(data.pos_head);
				main_menu.html(data.main_menu);
				pos_top.html(data.pos_top);
				page_title.after(data.pos_header).after(data.pos_header_right);
				pos_pre_content.html(data.pos_pre_content);
				pos_content_top_left.html(data.pos_content_top_left);
				pos_content_top_right.html(data.pos_content_top_right);
				pos_content.html(data.pos_content);
				pos_content_bottom_left.html(data.pos_content_bottom_left);
				pos_content_bottom_right.html(data.pos_content_bottom_right);
				pos_post_content.html(data.pos_post_content);
				pos_left.html(data.pos_left);
				pos_right.html(data.pos_right);
				pos_footer.html(data.pos_footer);
				pos_bottom.html(data.pos_bottom);
				$.each(data.errors, function(){
					pines.error(this, "Error");
				});
				$.each(data.notices, function(){
					pines.notice(this, "Notice");
				});
				page_ready();
				// Now run DOM ready scripts.
				pines.play();
			}
		});
	};
	$("body").delegate("a", "click", function(e){
		var cur_link = $(this);
		if (cur_link.attr("href").indexOf("#") == 0)
			return true;
		load_page_ajax(cur_link.attr("href"));
		return false;
	});
	pines.get = function(url){
		load_page_ajax(url);
	};
	// TODO: Handle post through Ajax.
});