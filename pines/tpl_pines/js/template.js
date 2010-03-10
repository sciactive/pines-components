$(function($){
	$.pnotify.defaults.pnotify_opacity = .9;
	// Turn notices into Pines Notify notices.
	$("div.col1 > div.notice.ui-state-error").find("p.entry span.text").each(function(){
		$.pnotify({
			pnotify_title: "Error",
			pnotify_text: $(this).html(),
			pnotify_type: "error",
			pnotify_hide: false
		});
	}).end().remove();
	$("div.col1 > div.notice.ui-state-highlight").find("p.entry span.text").each(function(){
		$.pnotify({
			pnotify_title: "Notice",
			pnotify_text: $(this).html()
		});
	}).end().remove();
	
	// Just in case Pines Notify isn't working.
	$(".notice .close, .error .close").css("cursor", "pointer").click(function() {
		$(this).parent().fadeOut("slow");
	});
	// Menu mouseover effects.
	$(".mainmenu li").hover(function(){
		$(this).addClass("ui-state-hover");
	}, function(){
		$(this).removeClass("ui-state-hover");
	});

	// Minimize the right modules.
	$(".module .module_right_minimize").hover(function(){
		$(this).addClass("ui-state-hover");
	}, function(){
		$(this).removeClass("ui-state-hover");
	}).toggle(function(){
		$(this).children("span.ui-icon").removeClass("ui-icon-triangle-1-n").addClass("ui-icon-triangle-1-s")
		.end().parent().nextAll(".module_content").slideUp("normal");
	}, function(){
		$(this).children("span.ui-icon").removeClass("ui-icon-triangle-1-s").addClass("ui-icon-triangle-1-n")
		.end().parent().nextAll(".module_content").slideDown("normal");
	});

	// Style UI buttons on hover.
	$(".ui-state-default:input:enabled:not(:text, textarea)").live('mouseover', function(){
		$(this).addClass("ui-state-hover");
	}).live('mouseout', function(){
		$(this).removeClass("ui-state-hover");
	});

	$(".ui-widget-content:input:disabled").addClass("ui-state-disabled");

	$(".ui-widget-content:text, .ui-widget-content:password").addClass("ui-corner-right");

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