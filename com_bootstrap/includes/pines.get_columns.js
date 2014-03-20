// Get the current number of columns in the CSS grid.
pines.com_bootstrap_get_columns = function(){
	var cur_grid = 0, cur_test;
	do {
		cur_grid++;
		cur_test = $("<div class=\"row\"><div class=\"span"+cur_grid+"\"><\/div><\/div>");
	} while (cur_grid <= 256 && cur_test.children().css("width") != "0px");
	cur_grid--;
	return cur_grid;
};