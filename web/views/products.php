<?php
if ( isset($data["err"]) ){
	$err = $data["err"];
	if ( is_array($err) ) $err = implode("; ",$err);
	if ( is_string($err) ) echo json_encode(Array("err" => $err));
	return;
}
$utils = new Utils();
$products = &$data["products"];
$category = &$data["category"];
?>
<script>

	$(document).ready(function(){

		$("#productPerpageSelect").change(function(){

			var query = _utils.URLQueryParse();
			var page = 1;

			_utils.URLQuerySet({
				"vars" : {
					"perpage" : this.value,
					"page" : page
				}
			})
		});

		// ---------------------------------------------------------

		$(".paginationList li").click(function(){
			var query = _utils.URLQueryParse();
			var qpage = ( typeof query.page == "undefined" ? 1 : parseInt(query.page) );

			var page = this.getAttribute("page");
			if ( !page ){
				return null;
			} else if ( ["next","prev"].indexOf(page) > -1 ) {

				if ( page == "next" ){
					_utils.URLQuerySet({"vars":{"page":qpage + 1}});
				} else {
					_utils.URLQuerySet({"vars":{"page":qpage - 1}});
				}
			} else {
				_utils.URLQuerySet({"vars":{"page":page}});
			}
		});

		$(".paginationList input").change(function(){
			_utils.URLQuerySet({"vars":{"page":this.value}});
		});

		// ---------------------------------------------------------

		(function(){

			var query = _utils.URLQueryParse();

			for(var prop in query){
				if (prop == "perpage") {
					$("#productPerpageSelect").val(query[prop]);
				}
			}

		})()

	});

</script>
<div class="titleRow">
	<div class="titleCol">
		<a href="<?php echo $rooturl; ?>/category/" style="float:left; display:inline-block;">←Вернуться</a>
		Товары в <?php echo $category->getValue("name"); ?>
	</div>
</div>

<div class="mainCol" id="pageProducts">

	<div class="optionsRow container gray">
		<div class="optionCol inline-block">
			<label>
				На странице:
				<select id="productPerpageSelect">
					<option value="24">24</option>
					<option value="48">48</option>
					<option value="96">96</option>
				</select>
			</label>
		</div>
	</div>

	<div class="productsRow">
		<?php
		foreach($products as $product){

			$thumbnail_url = (
				isset($product['attachments'][0]['thumbnail'])
				? $rooturl . $product['attachments'][0]['thumbnail']
				: '//:0'
			);

			echo '
			<div class="productItem">
				<div class="title">' . $product["name"] . '</div>
				<div class="img" style="background-image:url('. $thumbnail_url .')"></div>
				<!--<div class="description">' . $product["description"] . '</div>-->
				<a href="' . $rooturl . '/product/' . $product["id"] . '/"></a>
			</div>
			';
		}
		?>
	</div>

	<div class="paginationRow">
		<ul class="paginationList">
			<?php

			$page = ( isset($_GET["page"]) ? intval($_GET["page"]) : 1 );

			if ( $page < 1 ) $page = 1;

			$perpage = ( isset($_GET["perpage"]) ? intval($_GET["perpage"]) : 24 );

			$fr = ( count($products) ? $products[0]["foundRows"] : 0);

			echo $utils->paginationTemplate($page, $perpage, $fr);

			?>
		</ul>
	</div>

</div> <!-- close.class.mainCol -->