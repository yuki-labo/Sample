<?php
include_once(dirname(__FILE__)."/common/config.ini.php");
include_once(dirname(__FILE__)."/common/common.php");
include_once(dirname(__FILE__)."/common/Validate.php");
include_once(dirname(__FILE__)."/FX/FX.php");

$kp_xm_mst_clt = $_SESSION['__kp_Xm_mst_clt'][0];

//該当年度の対象区分出願者(_kf_xm_data_main)のリストを取得
// FX用初期処理
$sub = new FX(FX_IP, FX_PORT, FX_VER);
$sub->SetDBData('z_Xm_Sy','web_Xm_data_sub');
$sub->SetDBUserPass(FX_ID, FX_PASS);
$sub->SetCharacterEncoding('utf8');
$sub->SetDataParamsEncoding('utf8');

// 検索用変数
$year = JYear::GetJYear_exam();
$_key_sch_div = 5;

// 検索条件
$sub->AddDBParam('year', $year);
$sub->AddDBParam('_key_sch_div', $_key_sch_div);
$sub->AddDBParam('_kf_Xm_mst_clt', $kp_xm_mst_clt);
// $sub->AddDBParam('exam_code', $exam_code);
// $sub->AddDBParam('sub_code', $sub_code);

//検索実行
$res_sub = $sub->FMFind();
$key = array_keys($res_sub['data']);

for ($i=0; $i < count($res_sub['data']) ; $i++) {
	$kp_main[$i] = $res_sub['data'][$key[$i]]['_kf_Xm_data_main'][0];
	$xm_number[$i] = $res_sub['data'][$key[$i]]['xm_number_num'][0];
}


$mail = array();
for ($i=0; $i < count($kp_main) ; $i++) {
//該当年度の対象区分出願者のアドレスを取得
// FX用初期処理
$main = new FX(FX_IP, FX_PORT, FX_VER);
$main->SetDBData('z_Xm_Sy','web_Xm_data_main');
$main->SetDBUserPass(FX_ID, FX_PASS);
$main->SetCharacterEncoding('utf8');
$main->SetDataParamsEncoding('utf8');

// 検索条件
$main->AddDBParam('__kp_Xm_data_main', $kp_main[$i]);
$main->AddDBParam('_flg_send_mail',"=");

//検索実行
$res_main = $main->FMFind();

	if (!empty($res_main)) {
		$key = key($res_main['data']);
		$mail[$i] = $res_main['data'][$key]['mail'][0];
		$name[$i] = $res_main['data'][$key]['name'][0];

		//さらに_flg_send_mailを立てる処理をこのまま実行する。
		// レコードIDと修正ID取得
		$key = explode('.', $key);
		$recID = $key[0];
		$modID = $key[1];

		$main->AddDBParam('_flg_send_mail',1);
		// 修正特定ID
		$main->SetRecordID($recID);
		// 排他用ID
		$main->SetModID($modID);

		$edit = $main->FMEdit();
	}
}

$student = "";
//ここからメール送信
//生徒向け
for ($i=0; $i < count($mail); $i++) {
	if (empty($mail[$i])) {
		//メールが空なら何もしない
	} else {
		//送信先アドレス
		$to = $mail[$i];
		//教員向けメール用に生徒名をカンマ区切りで取得しておく。
		$student .= "受験番号：".$xm_number[$i]."  氏名：".$name[$i]."\n";

		$subject = SCH_NAME."Web出願 願書ダウンロード期間開始のお知らせ";

		//本文
		$message = SCH_NAME."入試広報課です。";
		$message .= "\n";
		$message .= "願書のダウンロードを行うことができる期間になりました。";
		$message .= "\n";
		$message .= "下記URL内「マイページ」→「確認/印刷」より願書をダウンロードし、";
		$message .= "\n";
		$message .= "「顔写真」を貼付の上、調査書と合わせて本校へ郵送してください。";
		$message .= "\n";
		$message .= "尚、入金の確認および願書/調査書の確認がとれた段階で、";
		$message .= "\n";
		$message .= "「確認/印刷」画面より受験表/調査書受領表がダウンロードできるようになります。";
		$message .= "\n";
		$message .= "*受験当日までに必ず確認及び印刷の上、顔写真を貼付て、ご持参ください。";
		$message .= "\n";
		$message .= "https://schoolmaster.jp/WebAp_dev/top.php";

		$message .= MAIL_FOOTER;

		//ヘッダー
		$header = "From: ".FROM_EMAIL;

		// カレントの言語を日本語に設定する
		mb_language("ja");
		// 内部文字エンコードを設定する
		mb_internal_encoding("UTF-8");
		//メールの送信処理
		//◆◆◆
		mb_send_mail( $to , $subject , $message , $header , '-f '.FROM_EMAIL );
		//◆◆◆
	}
}
//教員向け
	$student = trim($student);
if(!empty($student)){
	//送信先アドレス
	$to = FROM_EMAIL;

	$subject = "メール送信履歴";

	//本文
	$message = "以下の生徒に対して、願書がダウンロード可能になった旨を送信しました。";
	$message .= "\n";
	$message .= $student;
	$message .= "\n";

	//ヘッダー
	$header = "From: ".ADMIN_EMAIL;

	// カレントの言語を日本語に設定する
	mb_language("ja");
	// 内部文字エンコードを設定する
	mb_internal_encoding("UTF-8");
	//メールの送信処理
	//◆◆◆
	mb_send_mail( $to , $subject , $message , $header , '-f '.ADMIN_EMAIL );
	//◆◆◆
} else {
	//送信先アドレス
	$to = FROM_EMAIL;

	$subject = "対象者がいません。";

	//本文
	$message = "以下の原因でメール送信されませんでした。";
	$message .= "\n";
	$message .= "1.該当の入試出願区分に対象の生徒がいない。";
	$message .= "\n";
	$message .= "2.既に一度メールを送信した生徒。";
	$message .= "\n";
	$message .= "ご確認ください。";
	$message .= "\n";

	//ヘッダー
	$header = "From: ".ADMIN_EMAIL;

	// カレントの言語を日本語に設定する
	mb_language("ja");
	// 内部文字エンコードを設定する
	mb_internal_encoding("UTF-8");
	//メールの送信処理
	//◆◆◆
	mb_send_mail( $to , $subject , $message , $header , '-f '.ADMIN_EMAIL );


}

$_SESSION = array(); // セッションの削除


?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ja" lang="ja">

<head>
<meta name="viewport" content="width=device-width,initial-scale=1" />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="Content-Style-Type" content="text/css" />
<title>送信完了</title>

<script src="js/jquery-1.11.3.min.js" type="text/javascript"></script>
<script src="js/jquery.corner.js" type="text/javascript"></script>

<link rel="stylesheet" href="css/reset.css" media="all" type="text/css" />
<link rel="stylesheet" href="css/base.css" media="all" type="text/css" />
<link rel="stylesheet" href="css/end.css" media="all" type="text/css" />

<script>
$(function(){
	$(".pankuzu").corner("14px");
});
</script>
</head>

<body>
<!-- header ▼ -->
<?php include ('common/header.php'); ?>
<!-- header ▲ -->

<!-- title ▼ -->
<div id="title">
	<p id="sub_title">Complete</p>
	<h2>送信完了</h2>
</div>
<!-- title ▲ -->

<!-- main_contents ▼ -->
<div id="main_contents">
	<div id="contents_inner">
	<!-- 2016.07.20　服部　パン屑リストを「1.受験日程選択」「2.受験者情報入力」「3.決済前確認」「4.完了」に変更 -->
		<div id="transition" class="pc">
			<ul class="cf">
				<li class="pankuzu">1. メール対象区分選択</li>
				<li class="arrow">→</li>
				<li class="pankuzu">2. 完了</li>
			</ul>
		</div>

		<div id="transition" class="sp">
			<ul class="cf">
				<li class="pankuzu">1.</li>
				<li class="arrow">&gt;</li>
				<li class="act pankuzu">2. 完了</li>
			</ul>
		</div>

		<p class="comment">対象者にメールを送信しました。</p>

		<p class="to_top"><a href="send_mail_from_teacher.php">選択画面へ戻る</a></p>
	</div>
</div>
<!-- main_contents ▲ -->

<!-- footer ▼ -->
<?php include ('common/footer.php'); ?>
<!-- footer ▲ -->
</body>
</html>
