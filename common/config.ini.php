<?php
ob_start();
session_name("wellnet");
session_start();
setlocale(LC_ALL, 'ja_JP.UTF-8');
mb_internal_encoding("UTF-8");
header('Content-Type: text/html; charset=utf8');

ini_set("display_errors", "Off");	// 本番設定はOff
ini_set("error_reporting", E_ALL & ~E_NOTICE);

//Web出願受付メールアドレス
// //本番用メールアドレス
// define("ADMIN_EMAIL", "");
//2018.08.13　izumi　修正
define("ADMIN_EMAIL", "hirai@welldone.co.jp");
define("ADMIN_TEL", "03-5318-5660");
//2018.08.13　izumi　修正
define("FROM_EMAIL", "hirai@welldone.co.jp");

//学校名
define("SCH_NAME", "日本体育大学荏原高等学校");
//学校電話番号
define("SCH_TEL", "03-3759-3291");


// 決済システム障害対応用 (0 = 障害なし 1 = 障害中 2 = 障害後)
define("IRREGULAR", 0);

define("MAIL_FOOTER", "\r\n"."\r\n".'※このメールはシステムにより自動送信されています。'."\r\n".'本メールの返信でのお問い合わせは受け付けておりません。'."\r\n"."\r\n".'・電話でのお問い合わせ:'.ADMIN_TEL."\r\n".'・メールでのお問い合わせ:'.ADMIN_EMAIL);
//デバッグ用
function fnpr($data, $type=false){
	echo "<pre>";
	if($type){
		var_dump($data);
	}else{
		var_export($data);
	}
	echo "</pre>";
}



?>
