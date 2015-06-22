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

$category = &$data["category"];

$datajson = Array(
	"categories" => &$data["categories"],
	"category" => Array(
		"id" => $category->getValue("id"),
		"name" => $category->getValue("name")
	),
	"actionType" => (isset($data["actionType"]) ? $data["actionType"] : null)
);

?>
<!DOCTYPE html>
<html>

	<head>
		<script>
			location.rooturl = '<?php echo $rooturl; ?>';
			data = <?php echo json_encode($datajson); ?>
		</script>
		<meta charset="UTF-8">
		<link href="<?php echo $rooturl; ?>/web/static/css/cpCategory.css" rel="stylesheet" type="text/css">
		<script src="<?php echo $rooturl; ?>/web/static/js/jquery-2.1.0.min.js"></script>
		<script src="<?php echo $rooturl; ?>/web/static/js/utils.js"></script>
		<script src="<?php echo $rooturl; ?>/web/static/js/AttachmentsModel.js"></script>
		<script src="<?php echo $rooturl; ?>/web/static/js/cpCategorySingle.js"></script>
		<script>

			$(document).ready(function(){

				pageModel.addAttachments(<?php echo json_encode($categoryAttachments = $category->getValue("attachment")); ?>);

			});

		</script>
	</head>

	<body>
		<div class="titleRow">
			<div class="titleCol">
					<a href="<?php echo $rooturl; ?>/cp/category/" style="display:block; float:left;" title="Вернуться">
						← Вернуться
					</a>
				<?php echo $category->getValue("name"); ?>
			</div>
		</div>
		<div class="mainCol">

			<div class="categoryRow">

				<div class="thumbnailsRow"></div>

				<div class="infoRow">
					<table class="infoTable">
						<tr>
							<td></td>
							<td>
								<table>
									<tr>
										<td>Название:</td>
										<td><input type="text" name="categoryName" value="<?php echo $category->getValue("name"); ?>"></td>
									</tr>
								</table>
							</td>
						</tr>
					</table>
				</div> <!-- close.infoRow -->

			</div> <!-- close.categoryRow -->


			<div class="buttonsRow">
			<?php if ( $data["actionType"] == "update" ){ ?>
				<div class="deleteCategoryButton">
					<span>Удалить</span>
				</div>
			<?php } ?>
				<div class="saveCategoryButton">
					<span>Сохранить</span>
				</div>
			</div>

		</div> <!--	close.mainCol-->

		<?php
		if ( $data["actionType"] == "update" ){
		$selecthtml = '';
		$thisCatID = $data["category"]->getValue("id");
		foreach($data["categories"] as $tmp){
			if ( $thisCatID == $tmp["id"] ) continue;
			$selecthtml .= '<option value="' . $tmp["id"] . '">Назначить им категорию: ' . $tmp["name"] . '</option>';
		}
		?>
		<div id="categoryDeleteDialogWindow" class="none">
			<div style="text-align:center;">Категория содержит товары</div>
			<select>
				<option>Удалить вместе товарами</option>
				<?php echo $selecthtml; ?>
			</select>
			<input type="submit" value="OK" >
			<div class="closeButton" title="Закрыть">[x]</div>
		</div>
		<?php } ?>

	</body>

</html>