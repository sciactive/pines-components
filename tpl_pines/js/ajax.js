// Experimental AJAX code.
pines(function(){
	var current_hash, loader, j_window = $(window),
	pos_head = $("head"),
	main_menu = $("#main_menu"),
	pos_top = $("#top"),
	page_title = $("#page_title"),
	pos_pre_content = $("#pre_content"),
	pos_content_top_left = $("#content_top_left"),
	pos_content_top_right = $("#content_top_right"),
	pos_content = $("#content"),
	pos_content_bottom_left = $("#content_bottom_left"),
	pos_content_bottom_right = $("#content_bottom_right"),
	pos_post_content = $("#post_content"),
	pos_left = $("#left"),
	pos_right = $("#right"),
	pos_footer = $("#footer > div.modules"),
	pos_bottom = $("#bottom");
	var load_page_ajax = function(url, type, data){
		if (typeof data == "undefined") {
			data = {tpl_pines_ajax: 1};
		} else if (typeof data == "string") {
			if (data != "")
				data += "&"
			data += "tpl_pines_ajax=1";
		} else {
			data.tpl_pines_ajax = 1;
		}
		$.ajax({
			"type": type,
			"url": url,
			"dataType": "json",
			"data": data,
			beforeSend: function(xhr) {
				// TODO: Detect redirects.
				if (!loader)
					loader = $.pnotify({
						pnotify_text: "Loading...",
						pnotify_notice_icon: "picon picon-throbber",
						pnotify_width: "120px",
						pnotify_opacity: .6,
						pnotify_animate_speed: 20,
						pnotify_nonblock: true,
						pnotify_hide: false,
						pnotify_history: false,
						pnotify_stack: {"dir1": "down","dir2": "right"}
					}).css("top", "-.6em");
				loader.css("left", (j_window.width() / 2) - (loader.width() / 2));
				loader.pnotify_display();
				xhr.setRequestHeader("Accept", "application/json");
			},
			complete: function(){
				loader.pnotify_remove();
			},
			error: function(xhr, textStatus){
				pines.error("An error occured while communicating with the server:\n\n"+xhr.status+": "+textStatus);
			},
			success: function(data){
				if (window.location != url)
					window.location.hash = current_hash = "#!"+url.slice(pines.rela_location.length);
				else
					current_hash = "";
				// Pause DOM ready script execution.
				pines.pause();
				$("#header > div:not(.menuwrap)").remove();
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
				pos_left.html(data.pos_left+"&nbsp;");
				pos_right.html(data.pos_right+"&nbsp;");
				pos_footer.html(data.pos_footer);
				pos_bottom.html(data.pos_bottom);
				$.each(data.errors, function(){
					pines.error(this, "Error");
				});
				$.each(data.notices, function(){
					pines.notice(this, "Notice");
				});
				pines.tpl_pines_page_ready();
				// Now run DOM ready scripts.
				pines.play();
			}
		});
	};
	$("body").delegate("a", "click", function(){
		var cur_elem = $(this);
		var target = cur_elem.attr("href");
		if (target.indexOf(pines.rela_location) != 0)
			return true;
		load_page_ajax(target, "GET");
		return false;
	});
	$("body").delegate("form", "submit", function(){
		// TODO: Check for file elements.
		var cur_elem = $(this);
		var target = cur_elem.attr("action");
		if (target.indexOf(pines.rela_location) != 0)
			return true;
		var data = cur_elem.serialize();
		load_page_ajax(target, "POST", data);
		return false;
	});
	pines.get = function(url, params){
		if (params) {
			params.tpl_pines_ajax = 1;
		} else
			params = {tpl_pines_ajax: 1};
		url += (url.indexOf("?") == -1) ? "?" : "&";
		var parray = [];
		for (var i in params) {
			if (params.hasOwnProperty(i)) {
				if (encodeURIComponent)
					parray.push(encodeURIComponent(i)+"="+encodeURIComponent(params[i]));
				else
					parray.push(escape(i)+"="+escape(params[i]));
			}
		}
		url += parray.join("&");
		if (url.indexOf(pines.rela_location) != 0) {
			window.location = url;
			return;
		}
		load_page_ajax(url, "GET");
	};
	// TODO: Handle pines.post through Ajax.

	// Load any page found on the hash.
	if (typeof window.location.hash == "string" && window.location.hash != "" && window.location.hash.indexOf("!") == 1)
		load_page_ajax(pines.rela_location + window.location.hash.slice(2), "GET");

	// When the hash changes (like the back button) load the new page.
	var hashchange = function(){
		// Check that the hash hasn't been loaded.
		if (window.location.hash == current_hash)
			return;
		// Load the new hash.
		if (typeof window.location.hash == "string" && window.location.hash != "") {
			if (window.location.hash.indexOf("!") == 1)
				load_page_ajax(pines.rela_location + window.location.hash.slice(2), "GET");
		} else
			load_page_ajax(window.location, "GET");
	};
	if ("onhashchange" in window) {
		window.onhashchange = hashchange;
	} else {
		window.setInterval(hashchange, 100);
	}
});