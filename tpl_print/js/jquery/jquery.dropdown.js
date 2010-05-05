pines(function(){
	$("ul.dropdown li").live("mouseenter", function(){
		$(this).addClass("hover");
		$('> .dir',this).addClass("open");
		$('ul:first',this).css({'visibility': 'visible', 'display': 'block'});
	}).live("mouseleave", function(){
		$(this).removeClass("hover");
		$('.open',this).removeClass("open");
		$('ul:first',this).css({'visibility': 'hidden', 'display': 'none'});
	});
});