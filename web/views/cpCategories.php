<?php
if ( isset($data["err"]) ){
	$err = $data["err"];
	if ( is_array($err) ) $err = implode("; ",$err);
	if ( is_string($err) ) echo json_encode(Array("err" => $err));
	return null;
}
$categories = &$data["categories"];
?>
<script>
	$(document).ready(function(){

		// Сортировка
		$('.categoriesRow').sortable({
			"items" : ".categoryItem"
		});

		$("#saveCategoriesButton").click(function(){

			var catItems = $(".categoryItem");

			var categoriesOrder = [];

			for(var c=0; c<catItems.length; c++){
				var id = parseInt(catItems[c].getAttribute("data-id"));
				categoriesOrder.push(id);
			}

			$.ajax({
				"url" : location.rooturl + "/cp/category/",
				"type" : "post",
				"data" : {
					"categoriesOrder" : categoriesOrder
				},
				"complete" : function(res){

					var res_ = JSON.parse(res.responseText);
					var testCategoriesOrder = res_.categoriesOrder;
					if (testCategoriesOrder.join("") == categoriesOrder.join("")){
						alert("Сохранено");
						location.reload();
					}

				}
			});

		});

	});
</script>
<div class="titleRow"><div class="titleCol">Управление категориями</div></div>
<div class="mainCol" id="pageCpCategories">
	<div>Для изменения порядка категорий их нужно перетаскивать</div>
	<div class="categoriesRow">
		<?php
		foreach($categories as $category){

			$thumbnail_url = (
			isset($category['attachments'][0]['thumbnail'])
				? $rooturl . $category['attachments'][0]['thumbnail']
				: '//:0'
			);

			echo '
			<div class="categoryItem" data-id="' . $category["id"] . '">
				<table>
					<tr>
						<td class="img"><div style="background-image:url(' . $thumbnail_url . ')"></div></td>
						<td class="title">' . $category["name"] . '</td>
						<td class="hasProducts">' . $category["hasProducts"] . '</td>
					</tr>
				</table>
				<a class="button" " href="' . $rooturl . '/cp/category/' . $category["id"] . '/">Ред.</a>
			</div>
			';

		}
		?>
		<a href="<?php echo $rooturl; ?>/cp/category/new/" id="newCategoryItemButton" class="inline-block nbutton dashed hoverblue">
			<div class="table" style="height:100%; width:100%;"><span class="table-cell">Добавить категорию</span></div>
		</a>
	</div>

	<div class="buttonsRow">
		<div id="saveCategoriesButton" class="nbutton blue inline-block">
			<span>Сохранить</span>
		</div>
	</div>

</div>