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

	return;

}

$product = &$data["product"];
?>
<!DOCTYPE html>
<html>
	<head>
		<script>
			var rooturl = '<?php echo $rooturl; ?>';
		</script>
		<meta charset="UTF-8">
		<link rel="stylesheet" type="text/css" href="<?php echo $rooturl; ?>/web/static/css/product.css">
		<script src="<?php echo $rooturl; ?>/web/static/js/jquery-2.1.0.min.js"></script>
		<script src="<?php echo $rooturl; ?>/web/static/js/product.js"></script>
	</head>
	<body>
	<div class="titleRow"><div class="titleCol"><?php echo $product->getValue("name"); ?></div></div>
	<div class="mainCol">
		<div class="productRow">
			<div class="thumbnailsRow">
				<?php
				$productAttachments = $product->getValue("attachment");
				foreach($productAttachments as $attachment){
					if (!$attachment) continue;
					echo '
					<a href="' . $rooturl . $attachment['path'] . '" target="_blank" class="thumbnail">
						<div style="background-image:url(' . $rooturl . $attachment['thumbnail'] . ');" class="img"></div>
					</a>
					';
				}
				?>
			</div>
			<div class="infoRow">
				<table class="infoTable">
					<tr>
						<td class="descriptionCol"><?php echo $product->getValue("description"); ?></td>
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
									<td>Актуально на момент:</td>
									<td><?php echo $product->getValue("editdate"); ?></td>
								</tr>
							</table>
						</td>
					</tr>
				</table>
			</div>
		</div>
	</div>
	</body>
</html>