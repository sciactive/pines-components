/**
 * New Simple JS Option.
 *
 * Mobile Menu - New Adjusted Bootstrap Menu
 * 
 * Toggle in Configuration
 *
 * @package Templates\simple
 * @license http://opensource.org/licenses/MIT
 * @author Angela Murrell <amasiell.g@gmail.com>
 * @copyright Angela Murrell
 */
pines(function() {
	// Global Vars
	var max_width = 1000;
	var menu_btn = $('.btn.btn-navbar');
	var simple_height = 0;
	var nav_collapse = $('.navbar-inner .nav-collapse');
	var ul = nav_collapse.children('ul.nav');
	var cur_width = $(window).width();
	var backdrop_code = '<div class="modal-backdrop fade simple-menu-backdrop in"></div>';
	
	nav_collapse.addClass('hide');
	
	// Get Unique Number
	function get_unique(){
		return Math.floor(Math.random() * 100000);
	}
	
	// Make the Lis in each dropdown and the main lis too!
	function make_lis(lis){
		var empty_div = $('<div></div>');
		lis.each(function(){
			var cur_li = $(this);
			var href_text = cur_li.children('a').attr('href');
			var href = (href_text == undefined || href_text === 'javascript:void(0);') ? '' : 'href="'+cur_li.children('a').attr('href')+'"';
			var main_item_link_class = (href.length) ? 'link' : '';
			var dropdown_label = cur_li.children('a');
			var dropdown = cur_li.children('ul');
			
			var label = cur_li.children('a').text();
			var main_label = (!href.length) ? label : '<a class="menu-link" '+href+'>'+label+'</a>';
			
			var unique_value = 'drop-'+get_unique();
			var insert_dropdown = (!cur_li.hasClass('dropdown') && !cur_li.hasClass('dropdown-submenu')) ? '' : 'data-dropdown="'+unique_value+'"';
			var caret_dropdown = (!cur_li.hasClass('dropdown') && !cur_li.hasClass('dropdown-submenu')) ? '<div class="no-caret"></div>' : '<div class="menu-caret" '+insert_dropdown+'><i class="icon-caret-down"></i></div>';
//			var main_item_caret_class = (!caret_dropdown.length) ? 'no-caret' : '';
			
			dropdown_label.attr('data-label', unique_value);
			dropdown.attr('data-dropdown', unique_value);
			
			var new_item = $('<div class="menu-item '+main_item_link_class+'">'+caret_dropdown+main_label+'</div>');
			empty_div.append(new_item);
		});
		return empty_div;
	}
	
	// Determine the Height which may change with long dropdowns
	function set_height(simple_menu){
		simple_height = simple_menu.outerHeight() + 100;
		if ($(window).width() > max_width)
			simple_menu.hide();
		else {
			var doc_height = $(document).height();
			var height = (simple_height > doc_height) ? simple_height : doc_height;
			simple_menu.css('height', height+'px');
		}
	}
	
	// Function to Make the Menu
	function make_menu(){
		var simple_menu = $('<div class="simple-menu hide main-trans"></div>');
		var ul = menu_btn.closest('.navbar-inner').find('ul.nav');
		var main_lis = ul.children('li');
		
		var lis_div = make_lis(main_lis);
		simple_menu.append(lis_div.html());
		
		ul.closest('#nav').prepend(simple_menu);
		setTimeout(function(){
			set_height(simple_menu);
		}, 300);
	}
	
	// Get a dropdown box!
	function get_dropdown(caret){
		var dropdown_id = caret.attr('data-dropdown');
		var label = ul.find('[data-label='+dropdown_id+']').text();
		var dropdown = ul.find('[data-dropdown='+dropdown_id+']');
		
		var lis = dropdown.children('li');
		var lis_div = make_lis(lis);
		
		var dropdown_container = $('<div class="dropdown-container main-trans"></div>');
		dropdown_container.append('<div class="dropdown-box-title">'+label+'<div class="dropdown-box-close"><i class="icon-remove"></i></div></div>');
		dropdown_container.append('<div class="dropdown-box">'+lis_div.html()+'</div>');
		return dropdown_container;
	}
	
	// Initialize Menu
	make_menu();
	
	// Trigger On and Off the Menu
	menu_btn.click(function(e){
		// Leave at bigger resolutions
		if ($(window).width() > max_width)
			return;
		
		// Always Prevent Default
		e.preventDefault();
		e.stopPropagation();
		
		// For clicks after the first run.
		var possible_menu = $('.simple-menu');
		if (possible_menu.length && possible_menu.hasClass('menu-show')) {
			// Hide Menu.
			possible_menu.removeClass('menu-show');
			setTimeout(function(){
				$('body').css('overflow-x', 'auto');
			}, 550);
			return;
		} else if (possible_menu.length) {
			// Show Menu
			possible_menu.addClass('menu-show');
			setTimeout(function(){
				$('body').css('overflow-x', 'hidden');
			}, 550);
			return;
		}
	});
	
	// Manage resizing/changing the window width
	$(window).resize(function(){
		var simple_menu = $('.simple-menu');
		var new_width = $(this).width();
		
		if (cur_width == new_width)
			return;
		
		if (new_width < max_width) {
			simple_menu.css('height', $(document).height()+'px');
			simple_menu.removeClass('menu-show').show();
		} else {
			$('.simple-menu-backdrop').add('.dropdown-container').remove();
			simple_menu.removeClass('menu-show').hide();
		}
		cur_width = new_width;
	});
	
	// Click Carets
	$('.simple-menu').on('click', '.menu-caret', function(){
		var backdrop = $('.simple-menu-backdrop');
		var caret = $(this);
		var dropdown = get_dropdown(caret);
		var other_dropdowns = $('.dropdown-container');
		
		if (other_dropdowns.length) {
			// send it backward
			other_dropdowns.addClass('send-back');
		}
		
		var simple_menu = $('.simple-menu');
		
		if (!backdrop.hasClass('in')) {
			simple_menu.append(backdrop_code);
		}
		simple_menu.append(dropdown);
		
		$('html, body').animate({
			scrollTop: 0
		}, 300);
		
		setTimeout(function(){
			dropdown.addClass('dropdown-show');
			setTimeout(function(){
				set_height(simple_menu);
			}, 550);
		}, 330);
		
		
	});
	
	// Click Dropdown Close Buttons
	$('.simple-menu').on('click', '.dropdown-box-close', function(){
		var close = $(this);
		var cur_dropdown = close.closest('.dropdown-container');
		var backdrop = $('.simple-menu-backdrop');
		
//		backdrop.removeClass('in').hide();
		cur_dropdown.removeClass('dropdown-show');
		
		setTimeout(function(){
			cur_dropdown.remove();
			var prev_dropdown = $('.dropdown-container').last();
			prev_dropdown.removeClass('send-back');
			set_height($('.simple-menu'));
			if (!$('.dropdown-container').length) {
				backdrop.remove();
			}
		}, 550);
	});
});