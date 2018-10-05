<?php
include_once(dirname(__FILE__)."/common/config.ini.php");
include_once(dirname(__FILE__)."/common/common.php");
include_once(dirname(__FILE__)."/common/Validate.php");
include_once(dirname(__FILE__)."/FX/FX.php");

// 初期化
$post = array();
$error = array();

// 確認ページから戻ったときの処理
if(!empty($_SESSION['info'])) {
	$post = $_SESSION['info'];
}



// 確認ボタンが押された際の処理
if(!empty($_POST)) {
	$post = $_POST;
	//2016.08.22　服部　郵便番号、電話番号を結合
	$post['tel'] = $post['tel_01'].'-'.$post['tel_02'].'-'.$post['tel_03'];
	$post['add_zip_code'] = $post['add_zip_code_01'].'-'.$post['add_zip_code_02'];
	if ( empty($post['grd_add_zip_code_01']) && empty($post['grd_add_zip_code_02']) ){ $post['grd_add_zip_code'] = ''; }else{ $post['grd_add_zip_code'] = $post['grd_add_zip_code_01'].'-'.$post['grd_add_zip_code_02']; }
	$post['old_sch_tel'] = $post['old_sch_tel_01'].'-'.$post['old_sch_tel_02'].'-'.$post['old_sch_tel_03'];

	// 必須項目確認処理
	if(empty($post['name_sei'])) $error['name_sei'] = '「氏名(姓)」を入力してください。';
	if(empty($post['name_mei'])) $error['name_mei'] = '「あfghjkl氏名(名)」を入力してください。';
	if(empty($post['name_sei_read'])) $error['name_sei_read'] = '「氏名ふりがな(せい)」を入力してください。';
	if(empty($post['name_mei_read'])) $error['name_mei_read'] = '「氏名ふりがな(めい)」を入力してください。';
	if(empty($post['date_of_birth'])) $error['date_of_birth'] = '「生年月日」を入力してください。';

	//2016.12.13　服部　氏名フリガナの全角カタカナチェックを追加
	//2018.09.17 izumi 全角ひらがなチェックに変更
	//▽▽▽
	if ( !isset($error['name_sei_read']) ){
		if ( preg_match("/^[ぁ-ん]+$/u", $post['name_sei_read']) ) {
		}else{
			$error['name_sei_read'] = '「氏名ふりがな(せい)」は全角ひらがなで入力してください。';
		}
	}
	if ( !isset($error['name_mei_read']) ){
		if ( preg_match("/^[ぁ-ん]+$/u", $post['name_mei_read']) ) {
		}else{
			$error['name_mei_read'] = '「氏名ふりがな(めい)」は全角ひらがなで入力してください。';
		}
	}
	//△△△

	//2016.08．22　服部　電話番号のエラーチェックを変更
	if( empty($post['tel_01']) || empty($post['tel_02']) || empty($post['tel_03']) ){
		$error['tel'] = '「電話番号」を入力してください。';
		$error['tel_01'] = 'no_disp';
		$error['tel_02'] = 'no_disp';
		$error['tel_03'] = 'no_disp';
	}
	if ( !isset($error['tel']) && (!preg_match("/^[0-9-]+$/", $post['tel_01'])||!preg_match("/^[0-9-]+$/", $post['tel_02'])||!preg_match("/^[0-9-]+$/", $post['tel_03'])) )  $error['tel'] = '「電話番号」を半角数字で入力した下さい。';
	if( empty($post['add_zip_code_01']) || empty($post['add_zip_code_02']) ){
		$error['add_zip_code'] = '「郵便番号」を入力してください。';
		$error['add_zip_code_01'] = 'no_disp';
		$error['add_zip_code_02'] = 'no_disp';
	}
	if ( !isset($error['add_zip_code']) && (!preg_match("/^[0-9-]+$/", $post['add_zip_code_01'])||!preg_match("/^[0-9-]+$/", $post['add_zip_code_02'])) )  $error['add_zip_code'] = '「郵便番号」を半角数字で入力した下さい。';

	if(empty($post['add_prefecture'])) $error['add_prefecture'] = '「都道府県」を入力してください。';
	if(empty($post['add_city'])) $error['add_city'] = '「市区町村」を入力してください。';
	if(empty($post['add_street'])) $error['add_street'] = '「町名番地等」を入力してください。';
	if ( empty($post['old__kp_Sch_data']) && empty($post['name_old_school_sub']) ) {
		$error['old_school'] = '「中学校」を選択してください。';
		$error['old_sch_add_prefecture'] = 'no_disp';
		$error['old_sch_add_city'] = 'no_disp';
		$error['old__kp_Sch_data'] = 'no_disp';
	}

	if( empty($post['old_sch_tel_01']) || empty($post['old_sch_tel_02']) || empty($post['old_sch_tel_03']) ){
		$error['old_sch_tel'] = '「中学校電話番号」を入力してください。';
		$error['old_sch_tel_01'] = 'no_disp';
		$error['old_sch_tel_02'] = 'no_disp';
		$error['old_sch_tel_03'] = 'no_disp';
	}
	if ( !isset($error['old_sch_tel']) && (!preg_match("/^[0-9-]+$/", $post['old_sch_tel_01'])||!preg_match("/^[0-9-]+$/", $post['old_sch_tel_02'])||!preg_match("/^[0-9-]+$/", $post['old_sch_tel_03'])) )  $error['old_sch_tel'] = '「中学校電話番号」を半角数字で入力して下さい。';

	if(empty($post['name_grd_sei'])) $error['name_grd_sei'] = '「保護者氏名(姓)」を入力してください。';
	if(empty($post['name_grd_mei'])) $error['name_grd_mei'] = '「保護者氏名(名)」を入力してください。';
	if(empty($post['name_grd_sei_read'])) $error['name_grd_sei_read'] = '「保護者氏名ふりがな(せい)」を入力してください。';
	if(empty($post['name_grd_mei_read'])) $error['name_grd_mei_read'] = '「保護者氏名ふりがな(めい)」を入力してください。';
	if(empty($post['family_relationship'])) $error['family_relationship'] = '「続柄」を入力してください。';
	if ( !empty($post['grd_add_zip_code']) && (!preg_match("/^[0-9-]+$/", $post['grd_add_zip_code_01'])||!preg_match("/^[0-9-]+$/", $post['grd_add_zip_code_02'])) )  $error['grd_add_zip_code'] = '「保護者郵便番号」を半角数字で入力して下さい。';
	//2016.12.13　服部　氏名フリガナの全角カタカナチェックを追加
	//2018.09.17 izumi 全角ひらがなに変更
	//▽▽▽
	if ( !isset($error['name_grd_sei_read']) ){
		if ( preg_match("/^[ぁ-ん]+$/u", $post['name_grd_sei_read']) ) {
		}else{
			$error['name_grd_sei_read'] = '「保護者氏名ふりがな(せい)」は全角ひらがなで入力してください。';
		}
	}
	if ( !isset($error['name_grd_mei_read']) ){
		if ( preg_match("/^[ぁ-ん]+$/u", $post['name_grd_mei_read']) ) {
		}else{
			$error['name_grd_mei_read'] = '「保護者氏名ふりがな(めい)」は全角ひらがなで入力してください。';
		}
	}
	//△△△


	// // 2017.05.05 平井 第一志望日程・第一志望追加・・・17.11.16 平井 仕様変更のため日程は必須項目としない。
	// if(empty($post['other_school_exam_date_01'])) $error['other_school_exam_date_01 '] = '「第一志望日程」を入力してください。';
	// if(empty($post['other_sch_prefecture_01']) || empty($post['other_sch_section_01']) || empty($post['MA_school_01'])) $error['other_sch_form'] =
	// '「第一志望」を入力してください。';

	//2016.08.23　服部　メールの受信確認をエラーチェックに追加
	if( $post['mail_chk'] !== $post['mail'] ){ $error['mail_test_error'] = '必ずメールの受信確認を行なってください。'; }

	if(empty($post['mail'])) $error['mail'] = '「メールアドレス」を入力してください。';
	if(empty($post['password'])) $error['password'] = '「パスワード」を入力してください。';
	if(!empty($post['password']) && !preg_match("/^[a-zA-Z0-9]{6,}+$/", $post['password'])) $error['password'] = '「パスワード」を半角英数字６文字以上で入力してください。';
	if(empty($post['password_cfm'])) $error['password_cfm'] = '「パスワード(確認)」を入力してください。';
	if(empty($post['payment_type'])) $error['payment_type'] = '「支払方法」を選択してください。';

	// 必須以外のエラーチェック
	if(empty($error)) {
		// メールアドレスの文字列チェック
		if($er = Validate::EMail($post['mail'], "「メールアドレス」")) {
			$error['mail'] = $er;
		} else {
			// メールアドレスの既存チェック --- 2017.05.23 平井 「Web_AP→z_Xm_Sy」ファイルへ変更
			$fx = new FX(FX_IP, FX_PORT, FX_VER);
			$fx->SetDBData('z_Xm_Sy','web_Xm_data_main');
			$fx->SetDBUserPass(FX_ID, FX_PASS);
			$fx->SetCharacterEncoding('utf8');
			$fx->SetDataParamsEncoding('utf8');

			// 変数の格納
			$mail = $post["mail"];
			$exam_year = JYear::GetJYear_exam();

			// 検索条件の指定 --- 2017.05.22 平井修正
			// 検索条件の指定
			$fx->AddDBParam('mail',"==\"$mail\"");
			$fx->AddDBParam('year',"==\"$exam_year\"");
			$er_res = $fx->FMFind();
			if(!empty($er_res['data'])) $error['mail'] = '既に登録されている「メールアドレス」です。';
		}

		// パスワードの一致チェック
		if($post['password'] != $post['password_cfm']) $error['password'] = '「パスワード」が一致しません。';
	}

	// 日付チェック(yy/mm/dd)
	// 生年月日
	if($er = Validate::Date($post['date_of_birth'], "「生年月日」", "/")) $error['date_of_birth'] = $er;

	// 併願校受験日程
	if(!empty($post['other_sch_exam_date_01']) && $er = Validate::Date($post['other_sch_exam_date_01'], "「併願校受験日程1」", "/")) $error['other_sch_exam_date_01'] = $er;
	if(!empty($post['other_sch_exam_date_02']) && $er = Validate::Date($post['other_sch_exam_date_02'], "「併願校受験日程2」", "/")) $error['other_sch_exam_date_02'] = $er;
	if(!empty($post['other_sch_exam_date_03']) && $er = Validate::Date($post['other_sch_exam_date_03'], "「併願校受験日程3」", "/")) $error['other_sch_exam_date_03'] = $er;
	if(!empty($post['other_sch_exam_date_04']) && $er = Validate::Date($post['other_sch_exam_date_04'], "「併願校受験日程4」", "/")) $error['other_sch_exam_date_04'] = $er;
	if(!empty($post['other_sch_exam_date_05']) && $er = Validate::Date($post['other_sch_exam_date_05'], "「併願校受験日程5」", "/")) $error['other_sch_exam_date_05'] = $er;

	// エラーがない場合確認ページへ
	if(empty($error)) {
		//出身校名を取得
		if( !empty($post['old__kp_Sch_data']) ){
			$old_school = new FX(FX_IP, FX_PORT, FX_VER);
			$old_school->SetDBData('z_XM_Sy','web_Sch_data');
			$old_school->SetDBUserPass(FX_ID, FX_PASS);
			$old_school->SetCharacterEncoding('utf8');
			$old_school->SetDataParamsEncoding('utf8');
			$old_school->AddDBParam('__kp_Sch_data', '=='.$post['old__kp_Sch_data'] );
			$shu_res = $old_school->FMFind();
			$key = key($shu_res['data']);
			$post['old_school'] = $shu_res['data'][$key]['sch_name'][0];
		}

		$_SESSION['info'] = $post;
		header("Location:cfm.php");
		exit();
	}
}

///////////////
//学校区分の取得
//////////////
$year = date("Y");

//学校区分を検索
$yr_yr = new FX(FX_IP, FX_PORT, FX_VER);
$yr_yr->SetDBData('z_Mn_Sy','webmail_Yr_year');
$yr_yr->SetDBUserPass(FX_ID, FX_PASS);
$yr_yr->SetCharacterEncoding('utf8');
$yr_yr->SetDataParamsEncoding('utf8');
$yr_yr->AddDBParam('year', $year);
$yr_yr->AddSortParam('_key_sch_div','ascend',1);
$yr_res = $yr_yr->FMFind();

$yr_key = array_keys($yr_res['data']);

for ($i=0; $i < count($yr_res['data']) ; $i++) {
		$sch_div[$i] = $yr_res['data'][$yr_key[$i]]['_key_sch_div'][0];
		$sch_div_name[$i] = $yr_res['data'][$yr_key[$i]]['sch_div_name'][0];
}



//都道府県が選択済みの場合は市区町村リストを取得しておく
if ( !empty( $post['old_sch_add_prefecture'] ) ){
	//市区町村を検索
	$old_sch_add_city = new FX(FX_IP, FX_PORT, FX_VER);
	$old_sch_add_city->SetDBData('z_Xm_Sy','web_Sch_data');
	$old_sch_add_city->SetDBUserPass(FX_ID, FX_PASS);
	$old_sch_add_city->SetCharacterEncoding('utf8');
	$old_sch_add_city->SetDataParamsEncoding('utf8');

	$old_sch_add_city->AddDBParam('z_record_number', '==1');
	//市区町村リストをグローバルフィールドに格納するFMスクリプトを実行
	$param = $post['old_sch_add_prefecture'];
	$old_sch_add_city->AddDBParam('-script.prefind.param',$param );
	$old_sch_add_city->PerformFMScriptPrefind('WebAp_出身校_市区町村取得');
	$shu_city_res = $old_sch_add_city->FMFind();

	$key = key($shu_city_res['data']);
	$area_list = $shu_city_res['data'][$key]['_g_temp'][0];
	$old_sch_add_city = explode(',',$area_list);
}

//市区町村が選択済みの場合は学校リストを取得しておく
if ( !empty( $post['old_sch_add_prefecture'] ) && !empty( $post['old_sch_add_city'] ) ){
	//市区町村を検索
	$old_school = new FX(FX_IP, FX_PORT, FX_VER);
	$old_school->SetDBData('z_Xm_Sy','web_Sch_data','All');
	$old_school->SetDBUserPass(FX_ID, FX_PASS);
	$old_school->SetCharacterEncoding('utf8');
	$old_school->SetDataParamsEncoding('utf8');

	// $old_school->AddDBParam('FLG_web_non_disp', '=');
	$_key_sch_div = 3;
	$old_school->AddDBParam('_key_mst_div', $_key_sch_div);
	$old_school->AddDBParam('sch_add_prefecture', '=='.$post['old_sch_add_prefecture']);
	$old_school->AddDBParam('sch_add_city', '=='.$post['old_sch_add_city']);

	$old_school->AddSortParam('sch_name_read','ascend',1);
	$old_school_res = $old_school->FMFind();

	foreach ($old_school_res['data'] as $key => $value) {
	$old_sch_name[] = $value['sch_name'][0];
	$__kp_Sch_data[] = $value['__kp_Sch_data'][0];
	}

}

////////////
//併願校リスト
////////////
$year_heigan = JYear::GetJYear_exam();
$_key_sch_div = 4;
//都道府県
$heigan_pref = new FX(FX_IP, FX_PORT, FX_VER);
$heigan_pref->SetDBData('z_Xm_Sy','web_Sch_data', 'All');
$heigan_pref->SetDBUserPass(FX_ID, FX_PASS);
$heigan_pref->SetCharacterEncoding('utf8');
$heigan_pref->SetDataParamsEncoding('utf8');

$heigan_pref->AddDBParam('z_record_number', '==1');

//検索前に実行するスクリプトの設定
$heigan_pref->PerformFMScriptPrefind('WebAp_併願校都道府県設定');
$param = $_key_sch_div;
$heigan_pref->AddDBParam('-script.prefind.param',$param );

$hei_pref_res = $heigan_pref->FMFind();

$key = key($hei_pref_res['data']);
$kubun_list = $hei_pref_res['data'][$key]['_g_temp'][0];
$heigan_prefecture = explode(',',$kubun_list);

//併願校区分リスト作成用関数
function getlist_heigan_kubun($pref,$self_value){
	$year = JYear::GetJYear_exam();
	//$year = date("Y");
	$_key_sch_div = 4;

	$fx = new FX(FX_IP, FX_PORT, FX_VER);
	$fx->SetDBData('z_Xm_Sy','web_Sch_data', 'All');
	$fx->SetDBUserPass(FX_ID, FX_PASS);
	$fx->SetCharacterEncoding('utf8');
	$fx->SetDataParamsEncoding('utf8');

	$fx->AddDBParam('z_record_number', '==1');

	//検索前に実行するスクリプトの設定
	$fx->PerformFMScriptPrefind('WebAp_併願校区分設定');
	$param = $_key_sch_div.'|'.$pref;
	$fx->AddDBParam('-script.prefind.param',$param );

	$fx_res = $fx->FMFind();
	$key = key($fx_res['data']);
	$kubun_list = $fx_res['data'][$key]['_g_temp'][0];
	$heigan_kubun = explode(',',$kubun_list);

	$max = count($heigan_kubun);
	for( $i = 0 ; $i < $max ; $i++ ) {
		$t_kubun = $heigan_kubun[$i];
		if($self_value == $t_kubun){
			echo '<option value="'.$t_kubun.'" selected="selected">'.$t_kubun.'</option>';
		}else{
			echo '<option value="'.$t_kubun.'">'.$t_kubun.'</option>';
		}
	}
}

//併願校名（キー）リスト作成用関数
function getlist_heigan_key($pref,$kubun,$self_value){
	$year = JYear::GetJYear_exam();
	$_key_sch_div = 4;

	$fx = new FX(FX_IP, FX_PORT, FX_VER);
	$fx->SetDBData('z_Xm_Sy','web_Sch_data', 'All');
	$fx->SetDBUserPass(FX_ID, FX_PASS);
	$fx->SetCharacterEncoding('utf8');
	$fx->SetDataParamsEncoding('utf8');

	$fx->AddDBParam('_key_mst_div', $_key_sch_div);
	$fx->AddDBParam('sch_add_prefecture', '=='.$pref);
	$fx->AddDBParam('sch_installation_personnel', '=='.$kubun);
	$fx->AddSortParam('sch_name_read','ascend',1);

	$fx_res = $fx->FMFind();

	foreach ($fx_res['data'] as $key => $value) {
		$t_key = $value['__kp_Sch_data'][0];
		$t_name = $value['sch_name'][0];
		if($self_value == $t_key){
			echo '<option value="'.$t_key.'" selected="selected">'.$t_name.'</option>';
		}else{
			echo '<option value="'.$t_key.'">'.$t_name.'</option>';
		}
	}
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ja" lang="ja">

<head>
<meta name="viewport" content="width=device-width,initial-scale=1" />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="Content-Style-Type" content="text/css" />
<title>受験者情報入力</title>

<script src="js/jquery-1.11.3.min.js" type="text/javascript"></script>
<script src="js/jquery-ui-1.10.4.custom.min.js" type="text/javascript"></script>
<script src="js/jquery.corner.js" type="text/javascript"></script>
<script src="js/jquery.ui.datepicker-ja.min.js" type="text/javascript"></script>
<script type="text/javascript" src="https://ajaxzip3.github.io/ajaxzip3.js" charset="utf-8"></script>
<link rel="stylesheet" href="css/jquery-ui-1.10.4.custom.css" media="all" type="text/css" />
<link rel="stylesheet" href="css/reset.css" media="all" type="text/css" />
<link rel="stylesheet" href="css/base.css" media="all" type="text/css" />
<link rel="stylesheet" href="css/form.css" media="all" type="text/css" />

<script>
////////////////
//併願校の入力補助
////////////////
function set_other_school(target){
	$(function(){
		//初期設定
		//データ取得部分を関数化しておく
		function GetData(p_params){
			return $.ajax({
				type: "POST",
				url: "get_ajax.php",
				data: p_params
			})
		}

		//選択したリストを取得
		if (target.indexOf('prefecture') > 0) {
			var select_val = 'pref';
		}else{
			var select_val = 'sch_installation_personnel';
		}
		//併願校1～5を取得(01～05)
		var num = target.slice(-2);
		var id_pref = 'other_sch_prefecture_'+num;
		var val_pref = $('#'+id_pref).val();
		var id_kubun = 'other_sch_section_'+num
		var val_kubun = $('#'+id_kubun).val();
		var id_heigan_key = '__kp_Sch_data_'+num;

		//都道府県の値
		var val_prefecture = $('#other_sch_prefecture').val();
		//市区町村の値
		var val_city = $('#other_sch_city').val();

		////////////
		//リストの取得
		///////////
		if ( select_val == 'pref' ) {
			//区分の初期化
			$('select#'+id_kubun+' option').remove();
			//学校名の初期化
			$('select#'+id_heigan_key+' option').remove();
			var op = '<option value="" selected>選択</option>';
			$('#'+id_heigan_key).html(op);

			if( val_pref ){
				$_key_sch_div = 4;
				//併願校区分の取得
				var param_01 = '<?php echo $_key_sch_div; ?>';
				var param_02 = val_pref;
				var params = "category=sch_installation_personnel&param_01="+param_01+"&"+"param_02="+param_02;
				GetData(params).done(function(result) {
					var res = JSON.parse(result||"null");
					var vl_list = res.sch_installation_personnel;
					//optionの作成
					var max = Object.keys(vl_list).length;
					var op = '<option value="" selected>選択</option>';
					for( var i = 0 ; i < max ; i++ ){
						op += '<option value="'+vl_list[i]+'">'+vl_list[i]+'</option>';
					}
					$('#'+id_kubun).html(op);
				})
			}else{
				var op = '<option value="" selected>選択</option>';
				$('#'+id_kubun).html(op);
			}
		}

		//学校名の取得
		if ( select_val == 'sch_installation_personnel' ) {
			$('select#'+id_heigan_key+' option').remove();
			if( val_kubun ){
				var param_01 = '<?php echo $_key_sch_div; ?>';
				var param_02 = val_pref;
				var param_03 = val_kubun;
				var params = "category=other_school&param_01="+param_01+"&"+"param_02="+param_02+"&"+"param_03="+param_03;
				GetData(params).done(function(result) {
					var res = JSON.parse(result||"null");
					var name_list = res.sch_name;
					var key_list = res.__kp_Sch_data;
					//optionの作成
					var max = Object.keys(key_list).length;
					var op = '<option value="" selected>選択</option>';
					for( var i = 0 ; i < max ; i++ ){
						op += '<option value="'+key_list[i]+'">'+name_list[i]+'</option>';
					}
					$('#'+id_heigan_key).html(op);
				})
			}else{
				var op = '<option value="" selected>選択</option>';
				$('#'+id_heigan_key).html(op);
			}
		}
	});
}

////////////////
//出身校の入力補助
////////////////
function set_old_school(target_val,target_id){
	$(function(){

		//初期設定
		//データ取得部分を関数化しておく
		function GetData(p_param,p_category){
			return $.ajax({
				type: "POST",
				url: "get_ajax.php",
				data: "category="+p_category+"&param_01="+p_param
			})
		}

		//都道府県の値
		var val_prefecture = $('#old_sch_add_prefecture').val();
		//市区町村の値
		var val_city = $('#old_sch_add_city').val();

		////////////
		//リストの取得
		///////////
		//市区町村の取得
		if ( target_id == 'old_sch_add_prefecture' ) {
			//市区町村の初期化
			$('select#old_sch_add_city option').remove();
			//学校名の初期化
			$('select#__kp_Sch_data option').remove();
			var op = '<option value="" selected>選択</option>';
			$('#old__kp_Sch_data').html(op);

			if( val_prefecture ){
				//市区町村内の学校リストを取得
				var param = target_val;
				var category = 'old_sch_add_city';
				GetData(param,category).done(function(result) {
					var res = JSON.parse(result||"null");
					var vl_list = res.city;
					//optionの作成
					var max = Object.keys(vl_list).length;
					var op = '<option value="" selected>選択</option>';
					for( var i = 0 ; i < max ; i++ ){
						op += '<option value="'+vl_list[i]+'">'+vl_list[i]+'</option>';
					}
					$('#old_sch_add_city').html(op);
				})
			}else{
				var op = '<option value="" selected>選択</option>';
				$('#old_sch_add_city').html(op);
			}
		}

		//学校名の取得
		if ( target_id == 'old_sch_add_city' ) {
			$('select#old__kp_Sch_data option').remove();
			if( val_city ){
				//市区町村内の学校リストを取得
				var param = target_val;
				var category = 'old_school';
				GetData(param,category).done(function(result) {
					var res = JSON.parse(result||"null");
					var name_list = res.old_school_name;
					var key_list = res.__kp_Sch_data;
					//optionの作成
					var max = Object.keys(key_list).length;
					var op = '<option value="" selected>選択</option>';
					for( var i = 0 ; i < max ; i++ ){
						op += '<option value="'+key_list[i]+'">'+name_list[i]+'</option>';
					}
					$('#old__kp_Sch_data').html(op);
				})
			}else{
				var op = '<option value="" selected>選択</option>';
				$('#old__kp_Sch_data').html(op);
			}
		}
	});
}

	//$post['mail_chk']に値がある場合は#mail_chkに値をセットしておく
$(function(){
	var mail_chk = '<?php echo $post['mail_chk']; ?>';
	if( mail_chk ){
		$('#mail_chk').val(mail_chk);
	}
});

////////////////
//メール受信テスト
////////////////
function mail_test(){
	$(function(){
		//メールの送信部分を関数化しておく
		function mail_send(param){
			//テスト送信
			return $.ajax({
				type: "POST",
				url: "mail_test.php",
				data: "mail="+param
			})
		}

		//メッセージ表示部分を関数化しておく
		function show_dialog(p_title,p_message,p_width){
			//メッセージボックスの生成
			var el = $('<div class="mail_test_dialog"></div>').dialog({autoOpen:false});
			el.dialog("option", {
				title: p_title,
				width: p_width,
				buttons: {
					"OK": function() { $(this).dialog("close"); }
				}
			});
			el.html(p_message);
			el.dialog("open");
		}

		//ウィンドウ幅によって文字サイズを変更
		var screen_width = $(window).width();
		if( screen_width < 479 ){
			$('.mail_test_dialog').css('font-size','0.6em');
			var dialog_width = 'auto';
		}else{
			var dialog_width = '400px';
		}
		//var id_mail = document.getElementById('mail');
		var val_mail = $("#mail").val();
		var title;
		var message;
		//「@」が含まれるかの判定用
		var chk_at = val_mail.indexOf('@');
		//メールアドレスが空欄の場合はエラーメッセージを表示する
		//空欄の場合
		if ( !val_mail ) {
			title = "エラー";
			message = "メールアドレスを入力してください。";
			show_dialog(title,message,dialog_width);

		//「@」が含まれていない場合
		}else if ( chk_at == -1 ){
			title = "エラー";
			message = "メールアドレスに「@」が含まれていません。";
			show_dialog(title,message,dialog_width);

		//メール送信
		}else{
			//メールの送信
			mail_send(val_mail).done(function(result) {
				if( result > 0 ){
					title = "エラー";
					message = "既に登録されているメールアドレスです。";
				}else{
					title = "確認";
					message = "受信確認用のメールを送信しました。<br>必ずメールが届くことを確認してください。";
					//メールの受信確認チェックを設定
					$('#mail_chk').val(val_mail);
				}
				show_dialog(title,message,dialog_width);
			});
		}
	});
}

function funcErrorBack(id) {
	$('#'+id).css('background-color', '#FCD8D9');
}

$(function(){
	$(".pankuzu").corner("14px");
	<?php if(!empty($error)): ?>
	<?php foreach($error as $key=>$val): ?>
	funcErrorBack("<?php echo $key; ?>");
	<?php endforeach; ?>
	<?php endif; ?>
});

$(function() {
	$.datepicker.setDefaults($.datepicker.regional["ja"]);
	$(".date").datepicker({
		dateFormat: 'yy/mm/dd',
		changeMonth: true,
		changeYear: true,
		yearRange: 'c-20:c+1'
	});
});

//2016.08.05　服部　生年月日用のdatepickerを作成
$(function() {
	//var now = new Date();
	//var current_year = now.getFullYear();
	var current_year = '<?php echo JYear::GetJYear_exam(); ?>';
	var default_year = Number(current_year) - 16;
	var default_date = String(default_year)+"/04/01";
	$.datepicker.setDefaults($.datepicker.regional["ja"]);
	$(".date_of_birth").datepicker({
		dateFormat: 'yy/mm/dd',
		changeMonth: true,
		changeYear: true,
		defaultDate: default_date,
		yearRange: 'c-20:c'
	});
});

//2017.04.24　平井　東洋女子FLAG作成


$(function(){

	$('input[name="checkbox_first"]').change(function(){

		// checkbox_first()でチェックの状態を取得
		var chk_first = $('#checkbox_first').prop('checked');

		// 変数設定(都道府県 / 区分 / 学校名)
		var id_date = 'other_sch_exam_date_01';
		var id_pref = 'other_sch_prefecture_01';
		var id_kubun = 'other_sch_section_01';
		var id_heigan_key = 'MA_school_01';

		if(chk_first){



			//第1志望日程の初期化 / 設定
			$(function(){

					//初期設定
					//データ取得部分を関数化しておく
					function GetData_02(p_param,p_category){
						return $.ajax({
							type: "POST",
							url: "get_ajax.php",
							data: "category="+p_category+"&param_01="+p_param
						})
					}

					// 日付の設定
					var param_01 = '<?php echo $_SESSION['REF_master_juken_number'][0]; ?>';
					var category = 'date_wish';
					GetData_02(param_01,category).done(function(result) {
						var res = JSON.parse(result||"null");
						var t_date = res.date;
						$('#'+id_date).val("");
						// var op_date = '<input type="text" value='+t_date+'/>';
						$('#'+id_date).val(t_date);
					})


					// 日付変更 入力制御
					var target_id_date = document.getElementById(id_date);
					target_id_date.readOnly = true;
					$('#'+id_date).datepicker("destroy");

		});

			//都道府県の初期化 / 設定
			$('select#'+id_pref+' option').remove();
			var op01 = '<option value="東京" selected>選択</option>';
				$('#'+id_pref).html(op01);

			//区分の初期化 / 設定
			$('select#'+id_kubun+' option').remove();
			var op02 = '<option value="私立" selected>選択</option>';
				$('#'+id_kubun).html(op02);

			//学校名の初期化 / 設定
			$('select#'+id_heigan_key+' option').remove();

			var op03 = '<option value="1278" selected>選択</option>';
				$('#'+id_heigan_key).html(op03);

		}else{

			// 第1志望の初期化 / datepicker設定
			var target_id_date = document.getElementById(id_date);
			$('#'+id_date).val("");
			target_id_date.readOnly = false;
			$.datepicker.setDefaults($.datepicker.regional["ja"]);
			$(".date").datepicker({
				dateFormat: 'yy/mm/dd',
				changeMonth: true,
				changeYear: true,
				yearRange: 'c-20:c+1'
			});


			//都道府県の初期化 / 設定
			$('select#'+id_pref+' option').remove();

			// 都道府県の配列から受け取るためにjson形式で加工
			<?php $json_pref =json_encode($heigan_prefecture)?>

			// 都道府県の配列から受け取る
			var pref_list = JSON.parse('<?php echo $json_pref ; ?>');
			// pref_list.trim();
			// var pref_list = json_pref.split(" ");
			var max = Object.keys(pref_list).length;
			var op01 = '<option value="" selected>選択</option>';
					for (var i = 0; i < max ; i++) {
				 	op01 += '<option value="'+pref_list[i]+'">'+pref_list[i]+'</option>'
					}
				$('#'+id_pref).html(op01);

			//区分の初期化 / 設定
			$('select#'+id_kubun+' option').remove();
				var op02 = '<option value="" selected>選択</option>';
				$('#'+id_kubun).html(op02);

			//学校名の初期化 / 設定
			$('select#'+id_heigan_key+' option').remove();
     		var op03 = '<option value="" selected>選択</option>';
				$('#'+id_heigan_key).html(op03);

		}
	});
});

//2017.04.24　平井　東洋女子FLAG作成


</script>
</head>

<body>
<!-- header ▼ -->
<?php include ('common/header.php'); ?>
<!-- header ▲ -->



<!-- <?php //echo $_SESSION['__kp_Xm_mst_clt'][0];?> -->



<!-- title ▼ -->
<div id="title">
	<p id="sub_title">Selection of mail category</p>
	<h2>メール対象区分選択</h2>
</div>
<!-- title ▲ -->

<!-- main_contents ▼ -->
<div id="main_contents">
	<div id="contents_inner">
	<!-- 2016.07.20　服部　パン屑リストを「1.受験日程選択」「2.受験者情報入力」「3.決済前確認」「4.完了」に変更 -->
		<div id="transition" class="pc">
			<ul class="cf">
				<li class="act pankuzu">1. メール対象区分選択</li>
				<li class="arrow">→</li>
				<li class="pankuzu">2. 送信内容設定</li>
				<li class="arrow">→</li>
				<li class="pankuzu">3.完了</li>
			</ul>
		</div>

		<div id="transition" class="sp">
			<ul class="cf">
				<li class="act pankuzu">1.メール対象区分選択</li>
				<li class="arrow">&gt;</li>
				<li class="pankuzu">2. </li>
				<li class="arrow">&gt;</li>
				<li class="pankuzu">3.完了</li>
			</ul>
		</div>

		<div id="error">
		<?php
			if(!empty($error)) {
				echo "<ul>";
				foreach($error as $er) {
					if( $er !== 'no_disp' ){
						echo "<li>・".$er."</li>";
					}
				}
				echo "</ul>";
			}
		?>
		</div>

		<p class="comment">メール送信対象を選択してください。</p>
		<p class="info"><span>*</span>は必須項目です。</p>
		<!--<p class="info"><span>*</span>は必須項目です。</p>-->
		<form action="<?php echo $_SERVER['SCRIPT_NAME']; ?>" method="post">
			<div id="form">
				<dl class="cf">
					<dt>学校区分<span>*</span></dt>
					<dd>
						<select name="sch_div">
							<option class = "sch_div_list" name = "sch_div_list" value ="" selected= "selected">選択</option>
							<?php for ($i=0; $i < count($sch_div_name) ; $i++) { ?>
								<option class = "sch_div_list" name = "sch_div_list" value ="<?php echo $sch_div[$i];?>" /><?php echo $sch_div_name[$i];?></option>
							<?php }?>
						</select>
					</dd>
				</dl>

				<dl class="cf">
				<dt>学年</dt>
					<dd>
						<select name="key_age" >
							<option value="">選択して下さい</option>
							<?php
							for ($i=1; $i < 7 ; $i++) { ?>
							<option class = "sch_div_list" name = "sch_div_list" value ="<?php echo $i. "年";?>" /><?php echo $i. "年";?></option>
							<?php }?>
						</select>
					</dd>
				</dl>

				<dl class="cf">
					<dt>学科</dt>
					<dd><input type="radio" name="gender" value="男" checked="checked"/>男性<input type="radio" name="gender" value="女"/>女性
					</dd>
				</dl>
				<dl class="cf">
					<dt>コース</dt>
					<!--2016.08.05　服部　生年月日のみ別のdatepickerを割り当てるため、classを変更-->
					<dd><input type="text" id="date_of_birth" class="date_of_birth" name="date_of_birth" value="<?php echo funcDefinCheck($post, 'date_of_birth'); ?>" /></dd>
				</dl>
				<!--2016.08.23　服部　電話番号の配置を変更-->
				<dl class="cf">
					<dt>クラス</dt>
					<!-- 2016.08.22　服部　電話番号を3枠に変更-->
					<dd><input type="text" class="tel" id="tel_01" name="tel_01" value="<?php echo funcDefinCheck($post, 'tel_01'); ?>" /><span class="hyphen">-</span><input type="text" class="tel" id="tel_02" name="tel_02" value="<?php echo funcDefinCheck($post, 'tel_02'); ?>" /><span class="hyphen">-</span><input type="text" class="tel" id="tel_03" name="tel_03" value="<?php echo funcDefinCheck($post, 'tel_03'); ?>" />
					<a class="caution">携帯または固定電話</a>
					</dd>
				</dl>
				<dl class="cf">
					<dt>出席番号</dt>
					<dd><input type="text" class="add_zip_code" id="add_zip_code_01" name="add_zip_code_01" value="<?php echo funcDefinCheck($post, 'add_zip_code_01'); ?>" /><span class="hyphen">-</span><input type="text" class="add_zip_code" id="add_zip_code_02" name="add_zip_code_02" value="<?php echo funcDefinCheck($post, 'add_zip_code_02'); ?>" onkeyup="AjaxZip3.zip2addr('add_zip_code_01','add_zip_code_02','add_prefecture','add_city','add_street');"/></dd>
				</dl>
			</div>
			<div class="buttons cf">
				<!--2016.08.22　服部　戻り先を変更-->
				<input type="button" class="back_btn lf" value="戻る"  onclick="location.href='exam.php'" />
				<!--<input type="button" class="back_btn lf" value="戻る"  onclick="location.href='exam_cfm.php'" />-->
				<input type="submit" class="cfm_btn rg" value="確認" />
				<!--2016.08.05　服部　メールの受信確認の有無-->
				<input type="hidden" id="mail_chk" name="mail_chk" value="" />
			</div>
		</form>
	</div>
</div>
<!-- main_contents ▲ -->
<!-- footer ▼ -->
<?php include ('common/footer.php'); ?>
<!-- footer ▲ -->

</body>
</html>
