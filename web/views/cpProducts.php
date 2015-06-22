<?php
if ( isset($data["err"]) ){
	$err = $data["err"];
	if ( is_array($err) ) $err = implode("; ",$err);
	if ( is_string($err) ) echo json_encode(Array("err" => $err));
	return null;
}
$utils = new Utils();
$products = &$data["products"];
?>
<script>
	$(document).ready(function(){

		$("#selectAllProductsCheckbox").click(function(){
			var tmp = $(".productRowCheckbox");
			for(var c=0; c<tmp.length; c++){
				tmp[c].checked = this.checked;
			}
		});

		// ---------------------------------------------------------

		$(".deleteProductsButton").click(function(){
			var checked = $(".productsTable .productRowCheckbox:checked");
			var id = [];
			for(var c=0; c<checked.length; c++){
				id.push(checked[c].getAttribute("data-id"));
			}

			if (!id.length) return null;

			$.ajax({
				"url" : location.rooturl + "/cp/product/",
				"type" : "DELETE",
				"data" : {
					"id" : id
				},
				"complete" : function(res){
					var res = JSON.parse(res.responseText);
					if ( typeof res.err != "undefined" && res.err !== null  ) {
						if ( typeof res.err == "object" && typeof res.err.push == "function" ){
							alert("Ошибка: " + res.err.join("; "));
						} else if ( typeof res.err == "string" ) {
							alert("Ошибка: " + res.err);
						} else {
							alert("Ошибка. см. консоль");
							console.log(res.err);
						}
					} else {
						alert("Удалено");
						location.reload();
					}
				},
				"error" : function(){
					alert("Ошибка на стороне сервера.");
				}
			});
		});

		// ---------------------------------------------------------

		$("#productStatusSelect").change(function(){
			_utils.URLQuerySet({
				"vars" : {
					"status" : this.value
				}
			})
		});

		// ---------------------------------------------------------

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
				if (prop == "status") {
					$("#productStatusSelect").val(query[prop]);
				}
				if (prop == "perpage") {
					$("#productPerpageSelect").val(query[prop]);
				}
			}

		})()

	});
</script>

<div class="titleRow">
	<div class="titleCol">Товары для редактирования</div>
</div>

<div class="mainCol" id="pageCpProducts">

	<div class="optionsRow container gray">

		<div class="optionCol">
			<label>
				Статус товара:
				<select id="productStatusSelect">
					<option value="">Показывать все</option>
					<option value="draft">Черновик</option>
					<option value="published">Опубликован</option>
				</select>
			</label>
		</div>

		<div class="optionCol">
			<label>
				На странице:
				<select id="productPerpageSelect">
					<option value="24">24</option>
					<option value="48">48</option>
					<option value="96">96</option>
					<option value="2">2</option>
				</select>
			</label>
		</div>

	</div>

	<table class="productsTable tableStyle_01 w100percent">
		<thead>
			<tr>
				<th><input type="checkbox" id="selectAllProductsCheckbox" ></th>
				<th>ID</th>
				<th>Название</th>
				<th>Цена</th>
				<th>Кол-во</th>
				<th>Категория</th>
				<th>Дата заведения</th>
				<th>Дата обновления</th>
				<th>Статус</th>
				<th style="width:34px;"></th>
			</tr>
		</thead>
		<tbody>
			<?php
			foreach($products as $product){
				echo '
				<tr>
					<td><input class="productRowCheckbox" type="checkbox" data-id="' . $product["id"] . '"></td>
					<td>' . $product["id"] . '</td>
					<td>' . $product["name"] . '</td>
					<td>' . $product["price"] . '</td>
					<td>' . $product["amount"] . ' ' . $product["measure"] . '</td>
					<td>' . implode("<br />",$product["category"]) . '</td>
					<td>' . $product["regdate"] . '</td>
					<td>' . $product["editdate"] . '</td>
					<td>' . $product["status"] . '</td>
					<td>
						<a href="./' . $product["id"] . '" class="menu">Ред.</a>
					</td>
				</tr>
				';
			}
			?>
			<tr>
				<td colspan="10" class="addNewProductButton" style="background-color:white;"><a class="block nbutton dashed hoverblue" href="./new/">Добавить</a></td>
			</tr>
		</tbody>
	</table>

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

	<div class="buttonsRow">
		<div class="deleteProductsButton nbutton red inline-block">
			<span>Удалить выбранные</span>
		</div>
	</div>

</div> <!--	close.mainCol-->