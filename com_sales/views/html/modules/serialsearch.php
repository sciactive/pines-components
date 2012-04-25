<?php
/**
 * Search for serialized stock.
 *
 * @package Components
 * @subpackage sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Angela Murrell <angela@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');
$this->title = 'Stock Serial Search';
$pines->icons->load();
?>
<div id="p_muid_form">
	<style type="text/css" scoped="scoped">
		#p_muid_form label, #p_muid_form label input {
			display: inline;
		}
		#p_muid_results .status {
			margin-top: 1em;
		}
		#p_muid_results .well {
			padding-bottom: 1px;
		}
		#p_muid_results .picon {
			display: block;
			height: 16px;
			width: 16px;
		}
	</style>
	<script type="text/javascript">
		pines(function(){
			// Links for editing:
			var stock_link = <?php echo json_encode(htmlspecialchars(pines_url('com_sales', 'stock/edit', array('id' => '__id__')))); ?>,
				product_link = <?php echo json_encode(htmlspecialchars(pines_url('com_sales', 'product/edit', array('id' => '__id__')))); ?>;
			$("[name=serial]", "#p_muid_controls").keypress(function(e){
				if (e.keyCode == 13)
					$("#p_muid_search").click();
			});
			$("#p_muid_search").click(function(){
				var stock_results = $("#p_muid_results > .stock_entries"),
					status_bar = $("#p_muid_results > .status"),
					query = $("[name=serial]", "#p_muid_controls").val();
				if (query.match(/^\s*$/)) {
					alert("Please enter a search query.");
					return;
				}
				stock_results.show().children("div").html("<i class=\"picon picon-throbber\"></i>");
				status_bar.html("Searching...");
				$.ajax({
					url: <?php echo json_encode(pines_url('com_sales', 'stock/searchstock')); ?>,
					type: "POST",
					dataType: "json",
					data: {'serial': query, 'stock_entries': 'true'},
					error: function(XMLHttpRequest, textStatus){
						stock_results.hide();
						status_bar.html("An error occured.");
						pines.error("An error occured:\n"+pines.safe(XMLHttpRequest.status)+": "+pines.safe(textStatus));
					},
					success: function(data){
						var stock_container = stock_results.children("div");
						stock_container.html("");
						if (data.stock_entries == null) {
							stock_results.hide();
							status_bar.html("Nothing found.");
							return;
						}
						var status = "Found "+data.stock_entries.length+" Stock Entr"+(data.stock_entries.length > 1 ? "ies" : "y");
						// Build the stock results:
						var stock_struct = [];
						$.each(data.stock_entries, function(){
							var cur_stock_link = '<a href="'+stock_link.replace('__id__', pines.safe(this.guid))+'" onclick="window.open(this.href);return false;">Entry '+pines.safe(this.serial)+'</a>',
							cur_product_link = '<a href="'+product_link.replace('__id__', pines.safe(this.product_guid))+'" onclick="window.open(this.href);return false;">'+pines.safe(this.product_name)+'</a>';

							var content = "<p><strong>Serial:</strong> "+pines.safe(this.serial)+"</p>"+
								"<p><strong>Location:</strong> "+(this.location_name ? pines.safe(this.location_name) : "Not in Inventory")+"</p>"+
								"<p><strong>Product:</strong> "+pines.safe(this.product_name)+"</p>"+
								"<p><strong>Product ID:</strong> "+pines.safe(this.product_guid)+"</p>"+
								"<p><strong>Product SKU:</strong> "+pines.safe(this.product_sku)+"</p>"+
								"<p><strong>Manufacturer:</strong> "+pines.safe(this.manufacturer)+"</p>"+
								"<p><strong>Manufacturer SKU:</strong> "+pines.safe(this.manufacturer_sku)+"</p>";
							stock_struct.push($("<div class=\"entry clearfix alert alert-success\">"+cur_stock_link+" | "+cur_product_link+"<br/>Last Transaction: "+pines.safe(this.last_transaction)+"</div>").popover({
								title: "Stock Entry "+pines.safe(this.serial),
								content: content
							}));
						});
						paginate_things(stock_container, stock_struct, 4, 0);
						status_bar.html(status);
					}
				});
			});

			var paginate_things = function(container, items, perpage, startpage){
				var cur_page = startpage,
					max_page = Math.ceil((items.length / perpage) - 1),
					get_cur_page = function(){
						return items.slice(cur_page * perpage, cur_page * perpage + perpage);
					},
					get_cur_controls = function(){
						var result = $("<ul class=\"pager\"><li class=\"previous\"><a href=\"javascript:void(0);\">&larr; Previous</a></li><li class=\"next\"><a href=\"javascript:void(0);\">Next &rarr;</a></li></ul>");
						if (cur_page >= max_page)
							result.find(".next").remove();
						if (cur_page <= 0)
							result.find(".previous").remove();
						result.on("click", ".previous", function(){
							cur_page--;
							if (cur_page < 0)
								cur_page = 0;
							do_current_page();
						}).on("click", ".next", function(){
							cur_page++;
							if (cur_page > max_page)
								cur_page = max_page;
							do_current_page();
						});
						return result;
					},
					do_current_page = function(){
						container.children(".pager").remove().end().children().detach();
						$.each(get_cur_page(), function(){
							container.append(this);
						});
						if (max_page != 0)
							container.append(get_cur_controls());
					};
				do_current_page();
			};
		});
	</script>
	<div id="p_muid_controls" class="clearfix">
		<div style="float: left; margin-right: 1em;">
			<input type="text" size="16" name="serial" />
			<button class="btn" type="button" id="p_muid_search" title="Search"><i class="icon-search"></i></button><br />
		</div>
	</div>
	<div id="p_muid_results">
		<div class="status"></div>
		<div class="stock_entries" style="display: none;">
			<h5>Stock Entries</h5>
			<div class="well"></div>
		</div>
	</div>
</div>