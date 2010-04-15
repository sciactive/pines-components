$(function(){
	$.pnotify.defaults.pnotify_opacity = .9;
	var hover = function(elements){
		(typeof elements == "string" ? $(elements) : elements).live("mouseenter", function(){
			$(this).addClass("ui-state-hover");
		}).live("mouseleave", function(){
			$(this).removeClass("ui-state-hover");
		});
	};

	// Turn notices into Pines Notify notices.
	$("#top div.notices").find("div.ui-state-error span.text").each(function(){
		$.pnotify({pnotify_title: "Error", pnotify_text: $(this).html(), pnotify_type: "error", pnotify_hide: false});
	}).end().find("div.ui-state-highlight span.text").each(function(){
		$.pnotify({pnotify_title: "Notice", pnotify_text: $(this).html()});
	}).end().remove();

	// Main menu corners.
	$(".mainmenu").find(".dropdown > li:first-child").addClass("ui-corner-left").end()
	.find(".dropdown > li:last-child").addClass("ui-corner-right").end()
	.find(".dropdown li ul li:first-child").addClass("ui-corner-tr").end()
	.find(".dropdown li ul li:last-child").addClass("ui-corner-bottom");

	// Maximize modules.
	hover($(".module .module_maximize").live("click", function(){
		$(this).closest(".module").toggleClass("module_maximized");
	}));

	// Shade modules.
	hover($(".module .module_minimize").live("click", function(){
		$(this).children("span.ui-icon").toggleClass("ui-icon-triangle-1-n").toggleClass("ui-icon-triangle-1-s")
		.end().parent().nextAll(".module_content").slideToggle("normal");
	}));

	$(".ui-widget-content:input:disabled").addClass("ui-state-disabled");
	$(".ui-widget-content:text, .ui-widget-content:password").addClass("ui-corner-right");

	// Menu and UI buttons hover.
	hover(".dropdown li");
	$(".ui-state-default:button, .ui-state-default:submit, .ui-state-default:reset").button();


	/* Experimental AJAX code.
	var load_page_ajax = function(url){
		$.ajax({
			"type": "GET",
			"url": url,
			"dataType": "json",
			beforeSend: function(xhr) {
				xhr.setRequestHeader("Accept", "application/json");
			},
			complete: function(xhr, textStatus){
				alert(xhr.status+": "+textStatus);
			},
			success: function(data, textStatus){
				$("body > div#header > div.mainmenu > div.menuwrap").html(data.main_menu);
				$("head").append(data.head);
				$("body > div#top").html(data.top);
				$("body > div#header > div:not(.pagetitle, .mainmenu)").remove();
				$("body > div#header > div.pagetitle").after(data.header).after(data.header_right);
				$("body > div.colmask > div.colmid > div.colleft > div.col1wrap > div.col1")
				.children("div.user1").html(data.user1).end()
				.children("div.user2").html(data.user2).end()
				.children("div.content").html(data.content).end()
				.children("div.user3").html(data.user3).end()
				.children("div.user4").html(data.user4);
				$("body > div.colmask > div.colmid > div.colleft > div.col2").html(data.left);
				$("body > div.colmask > div.colmid > div.colleft > div.col3").html(data.right);
				$("body > div#footer").children(":not(p.copyright)").remove().end().prepend(data.footer);
				$("body > div#bottom").html(data.bottom);
				$.each(data.errors, function(){
					$.pnotify({
						pnotify_title: "Error",
						pnotify_text: this,
						pnotify_type: "error",
						pnotify_hide: false
					});
				});
				$.each(data.errors, function(){
					$.pnotify({
						pnotify_title: "Notice",
						pnotify_text: this
					});
				});
			}
		});
	};
	$("body").delegate("a", "click", function(e){
		var cur_link = $(this);
		load_page_ajax(cur_link.attr("href"));
		return false;
	});
	pines.get = function(url){
		load_page_ajax(url);
	};
	*/
});