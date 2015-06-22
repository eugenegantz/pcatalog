<?php
header("Content-type: text/html");

$core = new \Base\Core();
$rooturl = $core->get("rootURL");

if ( isset($data["err"]) ){

	$err = $data["err"];

	if ( is_array($err) ){
		$err = implode("; ",$err);
	}

	if ( is_string($err) ){
		echo json_encode(Array("err" => $err));
	}

	return null;

}

$categories = &$data["categories"];

?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
	<link rel="stylesheet" type="text/css" href="<?php echo $rooturl; ?>/web/static/css/cpCategories.css" />
	<script src="<?php echo $rooturl; ?>/web/static/js/jquery-2.1.0.min.js"></script>
	<script src="<?php echo $rooturl; ?>/web/static/js/jquery-ui-1.11.3.min.js"></script>
	<script>

		location.rooturl = "<?php echo $rooturl; ?>";

		$(document).ready(function(){

			// Сортировка
			$('.categoriesRow').sortable({
				"items" : ".categoryItem"
			});

			$(".saveCategoriesButton").click(function(){

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
</head>
<body>
<div class="titleRow"><div class="titleCol">Управление категориями</div></div>
<div class="mainCol">
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
		<a href="<?php echo $rooturl; ?>/cp/category/new/" class="newCategoryItemButton">
			<div><span>Добавить категорию<span></div>
		</a>
	</div>

	<div class="buttonsRow">
		<div class="saveCategoriesButton">
			<span>Сохранить</span>
		</div>
	</div>

</div>
</body>
</html>