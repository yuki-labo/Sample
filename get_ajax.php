<?php
include_once(dirname(__FILE__)."/common/config.ini.php");
include_once(dirname(__FILE__)."/common/common.php");
include_once(dirname(__FILE__)."/common/Validate.php");
include_once(dirname(__FILE__)."/FX/FX.php");

//パラメータで処理を分岐する
$category = $_POST['category'];

//出身校の市区町村を取得
if ( $category == 'old_sch_add_city' ) {
	//市区町村を検索
	$shusshin_city = new FX(FX_IP, FX_PORT, FX_VER);
	$shusshin_city->SetDBData('z_XM_Sy','web_Sch_data');
	$shusshin_city->SetDBUserPass(FX_ID, FX_PASS);
	$shusshin_city->SetCharacterEncoding('utf8');
	$shusshin_city->SetDataParamsEncoding('utf8');

	$shusshin_city->AddDBParam('z_record_number', '==1');
	//市区町村リストをグローバルフィールドに格納するFMスクリプトを実行
	$param = $_POST['param_01'];
	$shusshin_city->AddDBParam('-script.prefind.param',$param );
	$shusshin_city->PerformFMScriptPrefind('WebAp_出身校_市区町村取得');
	$shu_city_res = $shusshin_city->FMFind();

	$key = key($shu_city_res['data']);
	$area_list = $shu_city_res['data'][$key]['_g_temp'][0];
	$temp['city'] = explode(',',$area_list);

	$result = json_encode($temp);
	echo $result;
	exit();
}

//出身校の取得
if ( $category == 'old_school' ) {
	//市区町村を変数に格納
	$city = $_POST[param_01];
	//市区町村を検索
	$shusshin = new FX(FX_IP, FX_PORT, FX_VER);
	$shusshin->SetDBData('z_XM_Sy','web_Sch_data', 'All');
	$shusshin->SetDBUserPass(FX_ID, FX_PASS);
	$shusshin->SetCharacterEncoding('utf8');
	$shusshin->SetDataParamsEncoding('utf8');

	// $shusshin->AddDBParam('FLG_web_non_disp', '=');
	$shusshin->AddDBParam('_key_mst_div', 3);
	$shusshin->AddDBParam('sch_add_city', '=='.$city);
	$shusshin->AddSortParam('sch_name_read','ascend',1);

	$shu_res = $shusshin->FMFind();

	foreach ($shu_res['data'] as $key => $value) {
		$temp['old_school_name'][] = $value['sch_name'][0];
		$temp['__kp_Sch_data'][] = $value['__kp_Sch_data'][0];
	}
	$result = json_encode($temp);
	echo $result;
	exit();
}

//併願校区分の取得
if ( $category == 'sch_installation_personnel' ) {
	//引数を変数に格納
	$FLG_chuko = $_POST[param_01];
	$pref = $_POST[param_02];
	//市区町村を検索
	$fx = new FX(FX_IP, FX_PORT, FX_VER);
	$fx->SetDBData('z_XM_Sy','web_Sch_data', 'All');
	$fx->SetDBUserPass(FX_ID, FX_PASS);
	$fx->SetCharacterEncoding('utf8');
	$fx->SetDataParamsEncoding('utf8');

	$fx->AddDBParam('z_record_number', '==1');

	//検索前に実行するスクリプトの設定
	$fx->PerformFMScriptPrefind('WebAp_併願校区分設定');
	$param = $FLG_chuko.'|'.$pref;
	$fx->AddDBParam('-script.prefind.param',$param );

	$fx_res = $fx->FMFind();
	$key = key($fx_res['data']);
	$kubun_list = $fx_res['data'][$key]['_g_temp'][0];
	$temp['sch_installation_personnel'] = explode(',',$kubun_list);

	$result = json_encode($temp);
	echo $result;
	exit();
}

//併願校の取得
if ( $category == 'other_school' ) {
	//引数を変数に格納
	$FLG_chuko = $_POST[param_01];
	$pref = $_POST[param_02];
	$kubun = $_POST[param_03];
	//市区町村を検索
	$fx = new FX(FX_IP, FX_PORT, FX_VER);
	$fx->SetDBData('z_XM_Sy','web_Sch_data', 'All');
	$fx->SetDBUserPass(FX_ID, FX_PASS);
	$fx->SetCharacterEncoding('utf8');
	$fx->SetDataParamsEncoding('utf8');

	$fx->AddDBParam('_key_mst_div', '=='.$FLG_chuko);
	$fx->AddDBParam('sch_add_prefecture', '=='.$pref);
	$fx->AddDBParam('sch_installation_personnel', '=='.$kubun);
	$fx->AddSortParam('sch_name_read','ascend',1);

	$fx_res = $fx->FMFind();

	foreach ($fx_res['data'] as $key => $value) {
		$temp['sch_name'][] = $value['sch_name'][0];
		$temp['__kp_Sch_data'][] = $value['__kp_Sch_data'][0];
	}
	$result = json_encode($temp);
	echo $result;
	exit();
}

// 2017.05.05 平井 東洋女子FLAGをつけた場合に日付取得
if ( $category == 'date_wish' ) {
	//REF_master_juken_number格納
	$m_jnum = $_POST[param_01];
	// $m_jnum = "535";
	//選択した受験日程検索
	$fx = new FX(FX_IP, FX_PORT, FX_VER);
	$fx->SetDBData('hsv3_nyushiSY','web_master_juken_number');
	$fx->SetDBUserPass(FX_ID, FX_PASS);
	$fx->SetCharacterEncoding('utf8');
	$fx->SetDataParamsEncoding('utf8');

	$fx->AddDBParam('REF_master_juken_number', '=='.$m_jnum);

	$fx_res = $fx->FMFind();

	$key = key($fx_res['data']);
	$date = $fx_res['data'][$key]['date_exam'][0];
	$temp['date'] = date('Y/m/d' , strtotime($date));
	$result = json_encode($temp);

	echo $result;
	exit();
}

?>
