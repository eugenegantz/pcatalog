<?php
header("Content-type: text/html");
$core = new \Base\Core();
$rooturl = $core->get("rootURL");
$rootdir = $core->get("rootDIR");
$auth = new Auth();
$auth->login();
?>
<!DOCTYPE html>
<html>
	<head>
		<meta content="text/html" charset="UTF-8">
		<meta name="description" content="">
		<meta name="keywords" content="">
		<title>Каталог</title>
		<link rel="stylesheet" type="text/css" href="<?php echo $rooturl; ?>/web/static/css/styles.css">
		<script src="<?php echo $rooturl; ?>/web/static/js/jquery-2.1.0.min.js"></script>
		<script src="<?php echo $rooturl; ?>/web/static/js/jquery-ui-1.11.3.min.js"></script>
		<script src="<?php echo $rooturl; ?>/web/static/js/utils.js"></script>
		<script>
			location.rooturl = "<?php echo $rooturl; ?>";
		</script>
	</head>
	<body>
	<?php
	$navmenu = Array();
	$navmenu[] = '<div class="navRow">';
	if ( $auth->isAuth ){
		$navmenu[] = '<a href="'. $rooturl .'/logout/" class="item">Выйти</a>';
		$navmenu[] = '<div class="item">Авторизирован: ' . $auth->email . '</div>';
		$navmenu[] = '<div class="item">';
		$navmenu[] = 	'<span>Управление</span>';
		$navmenu[] = 	'<div class="sub">';
		$navmenu[] = 		'<a class="subitem" href="'. $rooturl .'/cp/product/" >Товары</a>';
		$navmenu[] = 		'<a class="subitem" href="'. $rooturl .'/cp/category/" >Категории</a>';
		$navmenu[] = 	'</div>';
		$navmenu[] = '</div>';
	} else {
		$navmenu[] = '<a href="'. $rooturl .'/login/" class="item">Войти</a>';
	}
	$navmenu[] = '<a href="'. $rooturl .'/category/" class="item">Каталог</a></div>';

	echo implode("",$navmenu);

	if($data["_bodyTemplateFile"]){
		if ( file_exists($data["_bodyTemplateFile"]) ){
			require_once($data["_bodyTemplateFile"]);
		} else {
			echo "_bodyTemplateFile: file &quot;" . $data["_bodyTemplateFile"] . "&quot; does not exist";
		}
	}
	?>
	<div class="footer"></div>
	</body>
</html>