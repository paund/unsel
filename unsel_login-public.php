<?php
session_start();

	//ログインデータ変数格納
	$login_nama = $_POST['login_nameormail'];
	$login_pass = $_POST['login_pass'];


	//DB接続	
	$dsn = 'mysql:dbname=tt_469_99sv_coco_com;host=localhost;charset=utf8mb4';
	$user = 'tt-469.99sv-coco';
	$password = 'Gi9KzBdy';
	$pdo = new PDO($dsn,$user,$password);



	//ログイン済みならマイページへ
	if(isset($_SESSION["username"])){

	     header("location:http://tt-469.99sv-coco.com/unsel_mypage.php");


	//ログイン機能
	}elseif(!empty($login_nama) && !empty($login_pass)){

		
		//メールアドレス・ユーザー名認証
		$sql = $pdo -> prepare ("SELECT * FROM user_info WHERE username = '$login_nama' or usermail = '$login_nama'");
		$sql -> execute();

		//入力データとDB内データ照合
		if($login_data = $sql->fetch(PDO::FETCH_BOTH)){

			$login_id = $login_data[1];
			$loginpassword = $login_data[2];

			if($loginpassword == $login_pass){
				
				//セッションに値をセット
 				$_SESSION["userid"] = $login_id;
 				$_SESSION["username"] = $login_name;

				header("location:http://tt-469.99sv-coco.com/unsel_mypage.php"."?userid=".$login_id);

			}else{
				$errors['login'] = "ログインに失敗しました";

				//エラーメッセージを表示
				foreach($errors as $error_content){
					echo "<div class='alert'>".$error_content."</div>";
				}

			}
		}
	}

?>

<!DOCTYPE html>
<head>
<meta charset = "UTF-8">


	<link rel="stylesheet" type="text/css" href="design.css">
	
	<title>ログイン｜あんせる</title>
</head>

<body>
<form action = "unsel_login.php" method = "post">

<h1>『あんせる』ログインフォーム</h1>

	<p>ユーザー名またはメールアドレス<br>	
	<input type="text" placeholder="登録したユーザー名もしくはメールアドレス" name="login_nameormail" size="50"></p>

	<p>パスワード<br>
	<input type="text" placeholder="パスワード" name="login_pass" size="10"></p>

	<button type="submit" value="">ログイン</button>
	<input type="button" onClick="location.href='http://tt-469.99sv-coco.com/unsel_register.php'" value="会員登録はこちら">

</form>

</body>

