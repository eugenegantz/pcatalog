<?php
header("Content-type:text/html");
$core = new \Base\Core();
$rooturl = $core->get("rootURL");
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
		<style>
			body {
				background-color:#e6e6e6;
			}

			.sidebar {
				position:relative;
				width:256px;
				height:100%;
				font-size:12px;
				padding:0 20px;
				background-color:white;
				display: table;
				border-right: 1px solid #C8C8C8;
			}

			.loginForm {
				display:table-cell;
				vertical-align: middle;
			}

			input[type=submit] {
				width:100%;
				padding: 5px 0;
			}

			input[name=login], input[name=password]{
				width:calc(100% - 10px);
				padding:5px;
				border:1px solid #C8C8C8;
				border-radius: 0;
			}

			input[name=login]:hover, input[name=password]:hover{
				width:calc(100% - 10px);
				padding:5px;
				border:1px solid #555;
			}

			input[name=login]:focus, input[name=password]:focus {
				box-shadow: 0px 0px 15px #268ed2;
				border:1px solid #268ed2;
			}
		</style>
		<script>
			location.rooturl = "<?php echo $rooturl; ?>";
			$(document).ready(function(){
				var loginInput = document.querySelector("[name=login]");
				var passwordInput = document.querySelector("[name=password]");

				var onResizeFx = function(){
					var body = document.querySelector("body");
					body.style.height = window.innerHeight + "px";
				};

				window.addEventListener("resize",onResizeFx,null);

				onResizeFx();

				// -----------------------------------------

				passwordInput.addEventListener("keydown", function(e){
					if(e.keyCode == 13) loginfx();
				}, null);

				loginInput.addEventListener("keydown", function(e){
					if(e.keyCode == 13) loginfx();
				}, null);

				// -----------------------------------------

				var loginfx = function (){
					if ( !loginInput.value ) {
						alert("Поле логин не заполнено");
						return null;
					}
					if ( !passwordInput.value ) {
						alert("Поле пароль не заполнено");
						return null;
					}
					$.post(
						location.rooturl + "/auth/",
						{
							"login" : loginInput.value,
							"password" : passwordInput.value
						},
						function(res){
							var res = JSON.parse(res);
							if ( typeof res.err == "undefined" ){
								if (
									["boolean","integer"].indexOf(typeof(res.auth)) > -1
									&& res.auth == true
								){
									location.replace(location.rooturl + "/category/");
								} else {
									alert("Неверное введен E-mail или пароль");
								}
							} else if ( typeof res.err == "string" ){
								alert(res.err);
							} else if (
								typeof res.err == "object"
								&& res.err !== null
								&& typeof res.err.push == "function"
							){
								alert(res.err.join());
							} else {
								alert("Ошибка. См.консоль");
								console.log(res.err);
							}
						}
					);
				};

				$(".loginForm input[type=submit]").click(loginfx);
			});
		</script>
	</head>
	<body>
		<div class="sidebar">
			<div class="loginForm">
				<table class="w100percent">
					<tr>
						<td>E-mail</td>
						<td><input class="w100percent" name="login" type="text"></td>
					</tr>
					<tr>
						<td>Пароль</td>
						<td><input class="w100percent" name="password" type="password"></td>
					</tr>
				</table>
				<input type="submit" value="Войти">
			</div>
		</div>
	</body>
</html>