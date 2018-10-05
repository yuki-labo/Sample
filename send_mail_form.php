<?php
include_once(dirname(__FILE__)."/common/config.ini.php");
include_once(dirname(__FILE__)."/common/common.php");
include_once(dirname(__FILE__)."/common/Validate.php");
include_once(dirname(__FILE__)."/FX/FX.php");

// 初期化
$post = array();
$error = array();

// post送信時の処理
if(!empty($_POST)) {
	$post = $_POST;
	$emp_flg = true;
	$nums = 0; // 出願数
	for($i=0; $i < $post['max']; $i++) {
		if(!empty($post['exam'.$i])) {
			$_SESSION['__kp_Xm_mst_clt'][$nums] = $post['exam'.$i];
			$nums++;
			$emp_flg = false;
		}
	}

	// 選択確認
	if($emp_flg) $error['emp'] = '区分を選択してください。';

	// エラーがない場合に選択数をセッションに格納してページ遷移
	if(empty($error)){
		$_SESSION['nums'] = $nums;
		//2016.07.20　服部　決済前確認へ進むよう変更
		header("Location:send_mail_from_teacher_end.php");
		exit();
	}
}



// FX用初期処理
$fx = new FX(FX_IP, FX_PORT, FX_VER);
$fx->SetDBData('z_XM_Sy','web_mst_clt');
$fx->SetDBUserPass(FX_ID, FX_PASS);
$fx->SetCharacterEncoding('utf8');
$fx->SetDataParamsEncoding('utf8');

// 検索用変数
$year = JYear::GetJYear_exam();
$_key_sch_div = 5;
$date = date('m/d/Y H:i:s');

// 検索条件
$fx->AddDBParam('year', $year);
$fx->AddDBParam('_key_sch_div', $_key_sch_div);
//必ず試験日が入っているように変更　20180904 izumi
$fx->AddDBParam('date_start', '<='.$date);
$fx->AddDBParam('date_end', '>='.$date);
$fx->AddDBParam('date_exam', '*');
// ソート順を指定
$fx->AddSortParam('date_exam','ascend',1);
$fx->AddSortParam('clt_No','ascend',2);

//検索実行
$result = $fx->FMFind();
$key = key($result['data']);

// 表示用リストの生成
$lists = array();
$i = 0;
$j = 0;
$start = true;
$con_flg = false;
foreach($result['data'] as $val) {
	// 登録済の受験日程(xxxx年xx月xx日:終日等)の読み飛ばし
	if(!empty($reserved)) {
		foreach($reserved as $res_val) {
			if($res_val['date_exam'] == $val['date_exam'][0]) {
					$con_flg = true;
			}
		}
	}

	if($con_flg) {
		$con_flg = false;
		continue;
	}

	if($start) {
		$date_exam = $val['date_exam'][0];
		//$time_exam_code = $val['time_exam_code'][0];
		$start = false;
	}

	if($date_exam == $val['date_exam'][0]) {
		$lists[$i][$j] = $val;
		$j++;
	} else {
		$date_exam = $val['date_exam'][0];
		//$time_exam_code = $val['time_exam_code'][0];
		$i++;
		$j = 0;
		$lists[$i][$j] = $val;
		$j++;
	}
}


?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ja" lang="ja">

<head>
<meta name="viewport" content="width=device-width,initial-scale=1" />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="Content-Style-Type" content="text/css" />
<title>メール対象区分選テストですよ</title>

<script src="js/jquery-1.11.3.min.js" type="text/javascript"></script>
<script src="js/jquery.corner.js" type="text/javascript"></script>

<link rel="stylesheet" href="css/reset.css" media="all" type="text/css" />
<link rel="stylesheet" href="css/base.css" media="all" type="text/css" />
<link rel="stylesheet" href="css/exam.css" media="all" type="text/css" />

<script>
$(function(){
	$(".pankuzu").corner("14px");
})
</script>
<script>
	function submitcheck() {
		var check = confirm('本当に送信してよろしいですか？');
		return check;
	}
</script>
</head>

<body>
<!-- header ▼ -->
<?php include ('common/header_teacher_view.php'); ?>
<!-- header ▲ -->

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
				<li class="act pankuzu">2. 送信内容設定</li>
				<li class="arrow">→</li>
				<li class="pankuzu">3. 完了</li>
		</div>

		<div id="transition" class="sp">
			<ul class="cf">
				<li class="pankuzu">1. メール対象区分選択</li>
				<li class="arrow">&gt;</li>
				<li class="act pankuzu">2. 送信内容設定</li>
				<li class="arrow">&gt;</li>
				<li class="pankuzu">3. 完了</li>
			</ul>
		</div>

		<div id="error">
		<?php
			if(!empty($error)) {
				echo "<ul>";
				foreach($error as $er) {
					echo "<li>・".$er."</li>";
				}
				echo "</ul>";
			}
		?>
		</div>

		<p class="comment">メール送信の対象区分を選択してください。</p>

		<form action="<?php echo $_SERVER['SCRIPT_NAME']; ?>" method="post" onsubmit="return submitcheck()">
			<?php
				$i = 0;
				foreach($lists as $list) {
					$j = 0;
					echo '<dl class="cf">';
					//2016.08.22　服部　入試時間を表示しないよう修正
					echo '<dt class="lf">'.$list[0]['date_exam_disp'][0].'</dt>';
				 	foreach($list as $val) {
						//2016.08.22　服部　但し書きを追加　1つだけ選択できるように変更
				 		if($j == 0) {
							echo '<dd class="rg"><input type="radio" name="exam0" value="'.$val['__kp_Xm_mst_clt'][0].'" id="radios'.$i.$j.'" />&nbsp;<label for="radios'.$i.$j.'">'.$val['web_disp_div'][0].'</label></dd>';
				 		} else {
							echo '<dd class="rg next_cel"><input type="radio" name="exam0" value="'.$val['__kp_Xm_mst_clt'][0].'" id="radios'.$i.$j.'" />&nbsp;<label for="radios'.$i.$j.'">'.$val['web_disp_div'][0].'</label></dd>';
				 		}
						$j++;
					}
					echo '</dl>';
				 	$i++;
				}
			?>
			<br>
			<p class="comment" style="color:red; font-size:24px;">※送信を押下すると、対象者に対してメールが送信されます。</p>
			<input type="hidden" name="max" value="<?php echo $i; ?>" />
			<div class="buttons cf">
				<input type="reset" id="clear_btn" class="lf" value="クリア" />
				<input type="submit" id="mail_btn" class="rg" value="送信" />
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
