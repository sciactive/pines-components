pines(function(){
	var page_testimonials = $('.testimonial-box.manual-trigger.scroll-load');
	var num = page_testimonials.length;
	var scroll_handler = function(){
		page_testimonials.each(function(){
			var testimonial = $(this);
			var test_offset = testimonial.offset().top - 300;
			var scroll_point = $(window).scrollTop();
			var scroll_point_hit = (scroll_point + $(window).height() == $(document).height()) ? true : (test_offset < scroll_point);
			if (!testimonial.hasClass('loaded') && scroll_point_hit) {
				testimonial.addClass('loaded');
				window.create_testimonial_module(testimonial);
			}
		});
		if ($('.testimonial-box.scroll-load.loaded').length == num)
			$(window).unbind("scroll", scroll_handler);
	};
	$(window).scroll(scroll_handler);
});



