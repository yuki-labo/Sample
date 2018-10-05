<?php
// 変数が未定義の場合、空を返す
function funcDefinCheck($name, $param) {
	if(isset($name[$param])) {
		return GetEscapeInputVal($name[$param]);
	} else {
		return "";
	}
}

// html出力用にテキストをエスケープ
function GetEscapeInputVal($txt){
	return htmlentities($txt, ENT_QUOTES, mb_internal_encoding());
}

//引数によって現在の年度、入試年度
class JYear{
	//現在の年度を取得
	public static function GetJYear_current(){
        $month = date('n');
		if( $month > 3 ){
			return date('Y');
		}else{
			return date('Y') - 1;
		}
	}

	//入試年度(現在の年度+1)
	public static function GetJYear_exam(){
		$month = date('n');
		if( $month > 3 ){
			return date('Y') + 1;
		}else{
			return date('Y');
		}
	}
}

//手数料の設定
//define ('CHARGE', 0.023);
define ('SYSTEM_FEE', 0);
$common_charge = array( 'クレジットカード' => 648,'コンビニ/ペイジー' => 405 );
define ('CHARGE_TETSUZUKI', 972);

// FX関連の定数
//学校用サーバーIP
// define ('FX_IP', "124.35.85.98");
//246server
//define ('FX_IP', "220.110.80.182");
// SakuraServer
define ('FX_IP', "160.16.187.57");
// izumiServer
// define ('FX_IP', "160.16.181.26");

define ('FX_PORT', "80");
define ('FX_VER', "FMPro16");
define ('FX_ID', "smzadmin");
define ('FX_PASS', "zae2ucs");

// トップへ戻るためのURL
define ('TOP', "http://www.nittai-ebara.jp/");

// 問い合わせ先のメールアドレス
//define('QA_MAIL',"hattori@welldone.co.jp");

// パスワード再設定期限（時間で入力）
define('PASSWORD_RESET_LIMIT',1);

//ソルト
define('SALT','EB144305C18245C39EF5C3C3D3C694E7');
/*
 * 添付ファイル有メール送信機能
 * @param $param_to 宛先メールアドレス
 * @param $param_subject メールサブジェクト
 * @param $param_body メール本文
 * @param $param_from 送信者メールアドレス
 */
function sendMailCommon($param_to, $param_subject, $param_body, $param_from) {
	$org = mb_internal_encoding();	// 元のエンコーディングを保存
	mb_internal_encoding("ISO-2022-JP");// 変換したい文字列のエンコーディングをセット
	$param_subject = mb_encode_mimeheader(mb_convert_encoding($param_subject, "ISO-2022-JP", "SJIS"),"ISO-2022-JP","B","\n");
	mb_internal_encoding($org);// エンコーディングを戻す
	$param_body=mb_convert_encoding($param_body, 'ISO-2022-JP-MS');

	//◆◆◆
	//@mail($param_to, $param_subject, $param_body, "From: ".$param_from."\r\n");
	@mail($param_to, $param_subject, $param_body, "From: ".$param_from."\r\n" , '-f '.$param_from );
	//◆◆◆
}

//日付のMM/DD/YYYYをYYYY/MM/DDに変換
function DateUSToENG ($target_date){
	if(!empty($target_date)){
		$arr_temp = explode( '/', $target_date);
		$result = $arr_temp[2].'/'.$arr_temp[0].'/'.$arr_temp[1];
	}else{
		$result = "";
	}
	return $result;
}

//日付のYYYY/MM/DDをMM/DD/YYYYに変換
function DateENGToUS ($target_date){
	if(!empty($target_date)){
		$arr_temp = explode( '/', $target_date);
		$result = $arr_temp[1].'/'.$arr_temp[2].'/'.$arr_temp[0];
	}else{
		$result = "";
	}
	return $result;
}

?>
