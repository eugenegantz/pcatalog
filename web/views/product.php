<?php
if ( isset($data["err"]) ){
	$err = $data["err"];
	if ( is_array($err) ) $err = implode("; ",$err);
	if ( is_string($err) ) echo json_encode(Array("err" => $err));
	return;
}
$product = &$data["product"];
?>
<div class="titleRow">
	<div class="titleCol">
		<a href="<?php echo $rooturl . "/category/" . $product->getValue("category")[0]; ?>/" style="float:left; display:inline-block;">←Вернуться</a>
		<?php echo $product->getValue("name"); ?>
	</div>
</div>
<div class="mainCol" id="pageProduct">
	<div class="productRow">
		<div class="thumbnailsRow">
			<?php
			$productAttachments = $product->getValue("attachment");
			foreach($productAttachments as $attachment){
				if (!$attachment) continue;
				echo '
				<a href="' . $rooturl . $attachment['path'] . '" target="_blank" class="nthumbnail inline-block">
					<div style="background-image:url(' . $rooturl . $attachment['thumbnail'] . ');" class="img"></div>
				</a>
				';
			}
			?>
		</div>
		<div class="infoRow container gray">
			<table class="infoTable">
				<tr>
					<td class="descriptionCol">
						<div ыенду="">Описание:</div>
						<?php echo $product->getValue("description"); ?>
					</td>
					<td class="propsCol">
						<table>
							<tr>
								<td>Цена:</td>
								<td><?php echo $product->getValue("price"); ?></td>
							</tr>
							<tr>
								<td>Осталось на складе:</td>
								<td><?php echo $product->getValue("amount") . ' ' . $product->getValue("measure"); ?></td>
							</tr>
							<tr>
								<td>Обновлено:</td>
								<td><?php echo $product->getValue("editdate"); ?></td>
							</tr>
						</table>
					</td>
				</tr>
			</table>
		</div>
	</div>
</div>