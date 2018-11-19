<!DOCTYPE html>
<html lang="ja">

<head>

	<link rel="stylesheet" type="text/css" href="design.css">

	<meta charset = "UTF-8">

	<title>会員登録｜あんせる</title>

</head>

<body>

<form action = "unsel_register.php" method = "post">

<h1>『あんせる』会員登録フォーム</h1>

	<p>ユーザー名<br>	
	<input type="text" placeholder="ニックネーム" name="user_name" size="50"></p>

	<p>パスワード<br>
	<input type="text" placeholder="*****" name="user_password" size="10"></p>

	<p>メールアドレス<br>	
	<input type="text" placeholder="abc@xxx.co.jp" name="user_mail" size="50"></p>

	<input type="hidden" name="preid" value="<?php $random_number = rand(0,99999999); echo $random_number; ?>" size="50">

	<button class="normal_btn" type="submit" value="">登録</button>
	<input class="normal_btn"type="button" onClick="location.href='http://tt-469.99sv-coco.com/unsel_login.php'" value="ログインはこちら">

</form>


<?php


	//多重送信対策
	if($_SERVER['REQUEST_METHOD'] === 'POST') {


//【ログイン機能】

	//DB接続	
	$dsn = 'DB名';
	$user = 'ユーザー名';
	$password = 'パスワード';
	$pdo = new PDO($dsn,$user,$password);


	//送信データを変数に格納
	$preuser_name = $_POST['user_name'];
	$preuser_password = $_POST['user_password'];
	$preuser_mail = $_POST['user_mail'];
	$preid = $_POST['preid'];
	$reg_date = date("Y/m/d H:i:s");
	$reg_url = "http://ドメイン名.com/unsel_register.php"."?preid=".$preid;

	//エラー初期化
	$errors = array();


//【ログイン～登録エラー】
	
	if(!empty($preid)){

		//エラー初期化
		$errors = array();
		$message = array();

		//ユーザー名エラー
		if( empty($preuser_name) ){
			$errors['user_name'] =  "ユーザー名を入力してください";

		}elseif(mb_strlen($preuser_name)>15){
			$errors['user_name_count'] =  "ユーザー名は15文字以内で入力してください";

		}else{
			$sql = $pdo -> prepare ("SELECT * FROM user_info WHERE username = '$preuser_name'");
			$sql -> execute();

			$userinfo_name=$sql->fetch(PDO::FETCH_BOTH);

		
			if(""!=$userinfo_name["username"]){
				$errors['user_name_used'] =  "既にこのユーザー名は登録されています";

			}
		}


		//パスワードエラー
		if ( empty($preuser_password) ){
			$errors['user_password'] = "パスワードを入力してください";

		}elseif(!preg_match('/^[0-9a-zA-Z]{5,30}$/', $preuser_password)){
			$errors['user_password_count'] = "パスワードは半角英数字の5文字以上30文字以下で入力して下さい。";

		}

		//メールエラー
		if ( empty($preuser_mail) ){
			$errors['user_mail'] = "メールアドレスを入力してください";

		}else{

		$sql = $pdo -> prepare ("SELECT * FROM user_info WHERE usermail = '$preuser_mail'");
		$sql -> execute();

		$userinfo_mail=$sql->fetch(PDO::FETCH_BOTH);

			if("" != $userinfo_mail["usermail"]){
				$errors['user_mail_used'] =  "既にこのメールアドレスは登録されています";
			}
		}
	}



//【仮登録】
	//仮登録テーブル作成
	$sql="CREATE TABLE preuser_info"
		."("
			."preid INT(8) ,"
			."username char(15),"
			."userpassword char(30),"
			."usermail char(100),"
			."applydate DATETIME"
		.")"
		."DEFAULT CHARSET=utf8;";
	
	$preuser_info = $pdo->query($sql);

	//本登録テーブル作成

	$sql="CREATE TABLE user_info"
		."("
			."id INT AUTO_INCREMENT PRIMARY KEY,"
			."username char(15),"
			."userpassword char(30),"
			."usermail char(100)"
		.")"
		."DEFAULT CHARSET=utf8;";
	
	$user_info = $pdo->query($sql);

	//仮ユーザー情報の保存


	if( count($errors)===0 && !empty($preuser_password)){

		$sql = $pdo -> prepare("INSERT INTO preuser_info (preid,username,userpassword,usermail,applydate) VALUES (:preid,:username,:userpassword,:usermail,:applydate)");
		$sql -> bindParam(':preid', $preid, PDO::PARAM_STR);
		$sql -> bindParam(':username', $preuser_name, PDO::PARAM_STR);
		$sql -> bindParam(':userpassword', $preuser_password, PDO::PARAM_STR);
		$sql -> bindParam(':usermail', $preuser_mail, PDO::PARAM_STR);
		$sql -> bindParam(':applydate', $reg_date, PDO::PARAM_STR);
		$sql -> execute();

	}

	//仮ユーザー情報の自動削除

	//24時間の判定
	//登録日と判定日を比較
	$sql = "SELECT * FROM preuser_info";
	$preuser_data = $pdo -> query($sql);

	foreach($preuser_data as $compare_date){

		$pre_date = new DateTime($compare_date[4]);
		
		$now_date = new DateTime();
 
		$passtime = round(($now_date->format('U') - $pre_date->format('U')) / (60*60*24));


	//仮登録から1日以上の経過でデータ削除
		if($passtime > 1){
			
			$sql = "DELETE FROM preuser_info";
			$auto_del_preinfo = $pdo->query($sql);

		}
	}
 
	

	//本登録用のメールを送信
	mb_language("Japanese");
	mb_internal_encoding("UTF-8");

	//メール内容を変数に格納
	$mail_title = "会員登録メール認証";
	$mail_return = 'http://ドメイン名.com/unsel_register.php';

//EOM：長文をhtml形式で書き込む
$mail_contents = <<< EOM
『あんせる』をご利用いただき、ありがとうございます。
下記URLをクリックして会員登録を完了してください。
このURLは24時間を経過すると無効になります。ご注意ください。
$reg_url
EOM;

	//メールを送信する
	//mb_send_mail(送信先,タイトル,本文,追加ヘッダ＊省略可,追加コマンドラインパラメータ＊省略可)
	if(count($errors)===0 && !empty($preuser_name)){

		if(mb_send_mail($preuser_mail, $mail_title, $mail_contents, '-f'. $mail_return)){

			$message['mail_send'] = "メールが送信されました";

		}else{

			$errors['mail_send'] = "メール送信に失敗しました";
		}	
	}


	//DB閉じる
	$pdo = null;

//【メッセージを表示】

	//メッセージを表示
	if(count($message) > 0){
		foreach($message as $message_content){
			echo "<div class='message'>".$message_content."</div>";
		}
	}

  	//エラーメッセージを表示または
	//多重送信対策＝自分自身にリダイレクト

	if(count($errors) > 0 ){

		//エラーメッセージを表示
		foreach($errors as $error_content){
			echo "<div class='alert'>".$error_content."</div>";
		}
	}else{

	     header("location:http://ドメイン名.com/unsel_register.php");
	}
		


	//多重送信対策
	}else{

//【ログイン～本登録】


	//DB接続	
	$dsn = 'DB名';
	$user = 'ユーザー名';
	$password = 'パスワード';
	$pdo = new PDO($dsn,$user,$password);

	//●本登録テーブル作成

	$sql="CREATE TABLE user_info"
		."("
			."id INT AUTO_INCREMENT PRIMARY KEY,"
			."username char(15),"
			."userpassword char(30),"
			."usermail char(100)"
		.")"
		."DEFAULT CHARSET=utf8;";
	
	$user_info = $pdo->query($sql);

	//メールデータの受け取り
		
	if(!empty($_GET)){
		$message = array();
		$mailid = $_GET['preid'];

		//仮登録テーブルからデータを取り出し、変数変換
		$sql = $pdo -> prepare ("SELECT * FROM preuser_info WHERE preid=$mailid ");
		$sql -> execute();

		if($reguser_data = $sql->fetch(PDO::FETCH_BOTH)){

			$username = $reguser_data[1];
			$userpassword = $reguser_data[2];
			$usermail = $reguser_data[3];

			//本登録ユーザー情報の保存

			$sql = $pdo -> prepare("INSERT INTO user_info (username,userpassword,usermail) VALUES (:username,:userpassword,:usermail)");
			$sql -> bindParam(':username', $username, PDO::PARAM_STR);
			$sql -> bindParam(':userpassword', $userpassword, PDO::PARAM_STR);
			$sql -> bindParam(':usermail', $usermail, PDO::PARAM_STR);
			$sql -> execute();
			
			$message['user_info_register'] = "ユーザー登録が完了しました";

		}

	}elseif(count($errors) > 0){

		//エラーメッセージを表示
		foreach($errors as $error_content){
			echo "<p>".$error_content."</p>";
		}
	}

	//メッセージを表示
	if(count($message) > 0){
		foreach($message as $message_content){
			echo "<p>".$message_content."</p>";
		}
	}

	//DB閉じる
	$pdo = NULL;



	}//多重送信対策の閉じ括弧

?>

</body>
