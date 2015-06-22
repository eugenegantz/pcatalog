<?php
if ( isset($data["err"]) ){
	$err = $data["err"];
	if ( is_array($err) ) $err = implode("; ",$err);
	if ( is_string($err) ) echo json_encode(Array("err" => $err));
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
<script>
	data = <?php echo json_encode($datajson); ?>
</script>
<script src="<?php echo $rooturl; ?>/web/static/js/AttachmentsModel.js"></script>
<script src="<?php echo $rooturl; ?>/web/static/js/cpCategory.js"></script>
<script>
	$(document).ready(function(){

		pageModel.addAttachments(<?php echo json_encode($categoryAttachments = $category->getValue("attachment")); ?>);

	});
</script>
<div class="titleRow">
	<div class="titleCol">
		<a href="<?php echo $rooturl; ?>/cp/category/" style="display:block; float:left;" title="Вернуться">
			← Вернуться
		</a>
		<?php echo $category->getValue("name"); ?>
	</div>
</div>
<div class="mainCol" id="pageCpCategory">

	<div class="categoryRow">
		<div class="thumbnailsRow"></div>
		<div class="infoRow container gray">
			<table class="infoTable">
				<tr>
					<td>Название:</td>
					<td><input type="text" name="categoryName" value="<?php echo $category->getValue("name"); ?>"></td>
				</tr>
			</table>
		</div> <!-- close.infoRow -->
	</div> <!-- close.categoryRow -->

	<div class="buttonsRow">
	<?php if ( $data["actionType"] == "update" ){ ?>
		<div id="deleteCategoryButton" class="nbutton red inline-block">
			<span>Удалить</span>
		</div>
	<?php } ?>
		<div id="saveCategoryButton" class="nbutton blue inline-block">
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
		<option value="delete">Удалить вместе товарами</option>
		<?php echo $selecthtml; ?>
	</select>
	<input type="submit" value="OK" >
	<div class="closeButton" title="Закрыть">[x]</div>
</div>
<?php } ?>