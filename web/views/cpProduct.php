<?php
if ( isset($data["err"]) ){
	$err = $data["err"];
	if ( is_array($err) ) $err = implode("; ",$err);
	if ( is_string($err) ) echo json_encode(Array("err" => $err));
	return null;
}

$product = &$data["product"];
$categories = &$data["categories"];

$categoryJSON = Array();

$categoryName = Array();
$categoryID = $product->getValue("category");

foreach($categories as $tmp){
	if (  in_array($tmp["id"], $categoryID)  ){
		$categoryName[] = $tmp["name"];
	}

	$categoryJSON[] = Array(
		"id" => $tmp["id"],
		"name" => $tmp["name"]
	);
}

$dataJSON = Array(
	"actionType" => ( isset($data["actionType"]) ? $data["actionType"] : null ),
	"product" => Array(
		"id" => $product->getValue("id")
	),
	"categories" => $categoryJSON
);

$dataJSON = json_encode($dataJSON);
?>
<script src="<?php echo $rooturl; ?>/web/static/js/AttachmentsModel.js"></script>
<script src="<?php echo $rooturl; ?>/web/static/js/cpProduct.js"></script>
<script>
	data = <?php echo $dataJSON; ?>;
	$(document).ready(function(){

		// ----------------------------------------------------------------------

		var categories_list = [];

		for(var c=0; c<data.categories.length; c++){
			categories_list.push({
				"value" : data.categories[c].id,
				"label" : data.categories[c].name
			});
		}

		new _utils.mSelectDBox({
			"target" : "[name=category]",
			"list" : categories_list,
			"multiple" : true,
			"autoComplete" : true
		});

		// ----------------------------------------------------------------------

		$(document).ready(function(){

			pageModel.addAttachments(<?php echo json_encode($product->getValue("attachment")); ?>);

		});

		// ----------------------------------------------------------------------

	});
</script>

<div class="titleRow">
	<div class="titleCol">
		<a href="<?php echo $rooturl; ?>/cp/product/" style="display:block; float:left;" title="Вернуться">
			← Вернуться
		</a>
		<?php echo $product->getValue("name"); ?>
	</div>
</div>

<div class="mainCol" id="pageCpProduct">

	<div class="productRow">

		<div class="thumbnailsRow"></div>

		<div class="infoRow container gray">
			<table class="infoTable">
				<tr>
					<td class="descriptionCol">
						<div>Описание</div>
						<textarea name="description"><?php echo $product->getValue("description"); ?></textarea>
					</td>
					<td class="propsCol">
						<table>
							<tr>
								<td>Название:</td>
								<td><input type="text" name="name" value="<?php echo $product->getValue("name"); ?>"></td>
							</tr>
							<tr>
								<td>Статус:</td>
								<td>
									<select name="status">
										<?php
										$statuses_tmp = Array(
											"published" => "Опубликовано",
											"draft" => "Черновик"
										);
										$productStatus = strtolower($product->getValue("status"));
										foreach($statuses_tmp as $key => $value){
											echo '<option value="' . $key . '" '. ($productStatus == $key ? "selected" : "") .'>' . $value . '</option>';
										}
										?>
									</select>
								</td>
							</tr>
							<tr>
								<td>Цена:</td>
								<td><input type="number" name="price" value="<?php echo $product->getValue("price"); ?>"></td>
							</tr>
							<tr>
								<td>Категория:</td>
								<td>
									<input type="text" name="category" value="<?php echo implode("; ",$categoryName); ?>" msdb_value="<?php echo implode(";",$categoryID); ?>">
								</td>
							</tr>
							<tr>
								<td>Количество:</td>
								<td><input type="number" name="amount" value="<?php echo $product->getValue("amount"); ?>"></td>
							</tr>
							<tr>
								<td>Ед. измерения:</td>
								<td><input type="text" name="measure" value="<?php echo $product->getValue("measure"); ?>"></td>
							</tr>
						</table>
					</td>
				</tr>
			</table>
		</div> <!-- close.infoRow -->

	</div> <!-- close.categoryRow -->

	<div class="buttonsRow">
		<div id="deleteProductButton" class="nbutton red inline-block">
			<span>Удалить</span>
		</div>
		<div id="saveProductButton" class="nbutton blue inline-block">
			<span>Сохранить</span>
		</div>
	</div>

</div> <!--	close.mainCol-->