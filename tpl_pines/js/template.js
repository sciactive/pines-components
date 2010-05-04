pines(function(){
	// Give hover effects to objects.
	var hover = function(elements){
		(typeof elements == "string" ? $(elements) : elements).live("mouseenter", function(){
			$(this).addClass("ui-state-hover");
		}).live("mouseleave", function(){
			$(this).removeClass("ui-state-hover");
		});
	};

	// Get the loaded page ready. (Styling, etc.)
	var page_ready = function(){
		$.pnotify.defaults.pnotify_opacity = .9;

		// Main menu corners.
		$(".mainmenu").find(".dropdown > li:first-child > a").addClass("ui-corner-left").end()
		.find(".dropdown > li:last-child > a").addClass("ui-corner-right").end()
		.find(".dropdown ul > li:first-child > a").addClass("ui-corner-tr").end()
		.find(".dropdown ul > li:last-child > a").addClass("ui-corner-bottom");

		// Maximize modules.
		hover($(".module .module_maximize").live("click", function(){
			$(this).closest(".module").toggleClass("module_maximized");
		}));

		// Shade modules.
		hover($(".module .module_minimize").live("click", function(){
			$(this).children("span.ui-icon").toggleClass("ui-icon-triangle-1-n").toggleClass("ui-icon-triangle-1-s")
			.end().parent().nextAll(".module_content").slideToggle("normal");
		}));

		// Add disabled element styling.
		$(".ui-widget-content:input:disabled").addClass("ui-state-disabled");
		$(".ui-widget-content:input:not(:button, :submit, :reset), .ui-widget-content:file").addClass("ui-corner-all");

		// Menu and UI buttons hover.
		hover(".dropdown li a");
		$(".module .ui-state-default:input:not(:not(:button, :submit, :reset))").button();
	};
	
	page_ready();

	/* Experimental AJAX code.
	var load_page_ajax = function(url){
		$.ajax({
			"type": "GET",
			"url": url,
			"dataType": "json",
			beforeSend: function(xhr) {
				xhr.setRequestHeader("Accept", "application/json");
			},
			error: function(xhr, textStatus){
				pines.error(xhr.status+": "+textStatus);
			},
			success: function(data, textStatus){
				$("body > div#header > div.mainmenu > div.menuwrap").html(data.main_menu);
				$("body > div#top").html(data.top);
				$("body > div#header > div:not(.mainmenu)").remove();
				$("body > div#header > h1.pagetitle").after(data.header).after(data.header_right);
				$("body > div#pre_content").html(data.pre_content);
				$("body > div.colmask > div.colmid > div.colleft > div.col1wrap > div.col1")
				.children("div.user1").html(data.user1).end()
				.children("div.user2").html(data.user2).end()
				.children("div.content").html(data.content).end()
				.children("div.user3").html(data.user3).end()
				.children("div.user4").html(data.user4);
				$("body > div#post_content").html(data.post_content);
				$("body > div.colmask > div.colmid > div.colleft > div.col2").html(data.left);
				$("body > div.colmask > div.colmid > div.colleft > div.col3").html(data.right);
				$("body > div#footer").children(":not(p.copyright)").remove().end().prepend(data.footer);
				$("body > div#bottom").html(data.bottom);
				$("head").append(data.head);
				$.each(data.errors, function(){
					pines.error(this, "Error");
				});
				$.each(data.notices, function(){
					pines.notice(this, "Notice");
				});
				page_ready();
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
	*/
});