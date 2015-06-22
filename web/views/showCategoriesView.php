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
		<link rel="stylesheet" type="text/css" href="<?php echo $rooturl; ?>/web/static/css/categories.css" />
		<script src="<?php echo $rooturl; ?>/web/static/js/jquery-2.1.0.min.js"></script>
	</head>
	<body>
		<div class="titleRow"><div class="titleCol">Категории</div></div>
		<div class="mainCol">
			<div class="categoriesRow">
				<?php
				foreach($categories as $category){

					$thumbnail_url = (
					isset($category['attachments'][0]['thumbnail'])
						? $rooturl . $category['attachments'][0]['thumbnail']
						: '//:0'
					);

					echo '
					<div class="categoryItem">
						<table>
							<tr>
								<td class="img"><div style="background-image:url(' . $thumbnail_url . ')"></div></td>
								<td class="title">' . $category["name"] . '</td>
								<td class="hasProducts">' . $category["hasProducts"] . '</td>
							</tr>
						</table>
						<a href="' . $rooturl . '/category/' . $category["id"] . '/"></a>
					</div>
					';
				}
				?>
			</div>
		</div>
	</body>
</html>