<?php
/**
 * ▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼
 * ■ 入力チェック用ライブラリ
 * 　　入力チェックとして使い回しが効きそうな処理をまとめたクラス。
 *
 * 　　調べたい値とその値の名称を渡すと、エラーが有った場合に
 * 　　整形されたメッセージが返ってくるという感じの静的メソッド群。
 *
 * 　　呼出元にてメソッドを組み合わせることで、
 * 　　複合的な入力チェックも簡単に作成できる。
 * 　　（逆に言えば組み合わせて使用することが前提。）
 *
 * 　　以下、諸注意と規則とか。
 *
 * ------------------------------------------------------------------------
 * １．Validateクラスが単独で使用できるように実装する。
 * 　⇒自作の他クラスを呼ばない。(include()を必要としないように実装する。)
 * 　⇒PHPのあまり一般的では無い様な拡張モジュールは使わない。
 * 　　（別途インストールが必要そうなモジュールとか。）
 * 　　　⇒レンタルサーバーで使えない！…とならないように注意。
 *
 * ------------------------------------------------------------------------
 * ２．メソッド規則
 * 　⇒静的メソッドで作成する。（『static』修飾子を付ける。）
 * 　　（インスタンス化せずに使用できるようにするため。）
 * 　⇒内部処理用メソッドは必ず『private』修飾子を付ける。
 * 　　（意図しない形で使用されないようにする。）
 * 　⇒１つの「項目名」に対し、１つのチェックとして実装する。
 * 　　複数項目や複合的なチェックで実装を行わない。
 * 　　（組み合わせで使用することが前提。）
 *
 * ------------------------------------------------------------------------
 * ３．メソッド命名規則
 * 　⇒行いたいチェック内容を１～３単語ぐらいで命名。
 * 　　基本的には『英名』。翻訳サイトで適当に単語を調べる。
 * 　　日本独自なチェック内容であればこの限りではない。（ローマ字で命名。）
 * 　⇒チェックする値の形式が限定されるのであれば、
 * 　　メソッド名で値の形式がわかるように命名する。
 * 　　値の形式が限定されないのであれば、
 * 　　値の形式を連想するような名前を付けない。
 *
 * 　　例. 『Date』は、日付で時刻を含まない。
 * 　　　　『DateTime』は、日時で日付と時刻を含む。
 * 　　　　『Time』は、時刻で日付を含まない。
 * 　　　　『Number』は、数字（数の文字）なので0～9の文字のみ。
 * 　　　　『Numeric』は、数値なので負数や小数といったものを含む。
 *
 * ------------------------------------------------------------------------
 * ４．メソッド引数の規則
 * 　⇒基本的には、『値（POST値）』と『名称（画面上での表示名称）』を指定
 * 　　できるようにする。
 * 　⇒『名称』にデフォルト値を使用しない。（『名称』は必ず指定させる。）
 * 　⇒引数の順序は『値』が先で『名称』が後。
 * 　　追加の引数が必ず必要な場合は、『名称』の前に追加する。
 * 　　追加の引数が必ずしも必要では無い場合は、『名称』の後に追加する。
 * 　　（デフォルト値が有る引数。）
 * 　⇒引数に参照渡しを使用しない。
 * 　　（入力チェックのための処理なので、入力チェックの正否以外を
 * 　　　返せるような余分な処理を盛り込まない。）
 *
 * ------------------------------------------------------------------------
 * ５．メソッドの戻り値の規則
 * 　⇒必ず文字列を返すこと。（配列を返したりしない。）
 * 　　『真』の場合には、空文字。
 * 　　『偽』の場合には、エラーメッセージ。
 *
 * ▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲
 */

/**
 * エラーチェック用クラス<br />
 * (基本的には&lt;form&gt;によってPOSTされた値のチェック用)
 */
class Validate{
	/** 正規表現でエスケープのいる文字 */
	const PTN_ESCAPE_REGEX = "/^[\/\.\\\"\'\|\[\{\}]$/u";

	/**
	 * 必須チェック（空チェック）
	 * @param string $value 検査したい値
	 * @param string $text エラー時の項目名
	 * @return string エラーが無ければ『空文字』。エラーが有ればエラーメッセージ。を返す。
	 */
	public static function Required($value, $text){
		$ptn = "/\S+/u";
		$ret = preg_match($ptn, $value);
		if (isset($value) && $ret !== FALSE && $ret > 0){
			return "";
		}
		return sprintf("『%s』を入力してください。", $text);
	}

	/**
	 * 複数項目の同時必須チェック（空チェック）<br>
	 * （渡されたデータのすべてが空でないかチェックする。）<br>
	 * （配列の場合には再帰的にチェックを行う。）
	 * @param ... 可変長引数。１～N個のチェックしたい変数を引数に指定し、N+1個目にエラー時の項目名を指定する。
	 * @return string エラーが無ければ『空文字』。エラーが有ればエラーメッセージ。を返す。
	 */
	public static function RequiredMulti(){
		$arg_num = func_num_args();
		if ($arg_num > 1){
			$ret = "";
			$args = func_get_args();
			$cnt = count($args) - 1;
			$text = $args[$cnt];
			for ($i=0; $i<$cnt; $i++){
				$ret = self::_requiredMulti($args[$i], $text);
				if (!empty($ret)){
					return $ret;
				}
			}
		}
		return "";
	}
	private static function _requiredMulti($value, $text){
		$ptn = "/\S+/u";
		$ret = "";
		if (isset($value) && is_array($value)){
			foreach ($value as $val){
				$ret = self::_requiredMulti($val, $text);
				if (!empty($ret)){
					break;
				}
			}
		} else {
			$tmp = preg_match($ptn, $value);
			if (isset($value) && $tmp !== FALSE && $tmp > 0){
				$ret = "";
			} else {
				$ret = sprintf("『%s』は全て必須項目です。", $text);
			}
		}
		return $ret;
	}

	/**
	 * 複数項目の同時入力チェック<br>
	 * （渡されたデータのすべてが無しか有りかをチェックする。）<br>
	 * （配列の場合には再帰的にチェックを行う。）
	 * @param ... 可変長引数。１～N個のチェックしたい変数を引数に指定し、N+1個目にエラー時の項目名を指定する。
	 * @return string エラーが無ければ『空文字』。エラーが有ればエラーメッセージ。を返す。
	 */
	public static function MultiInput(){
		$arg_num = func_num_args();
		$ret = "";
		if ($arg_num > 1){
			$ret = "";
			$args = func_get_args();
			$cnt = count($args) - 1;
			$text = $args[$cnt];
			$chk_true = 0;
			$chk_false = 0;
			for ($i=0; $i<$cnt; $i++){
				self::_multiInput($args[$i], $text, $chk_true, $chk_false);
			}
			if ($chk_true > 0 && $chk_false > 0){
				$ret = sprintf("『%s』を入力する場合には、全てに入力が必要です。", $text);
			}
		}
		return $ret;
	}
	private static function _multiInput($value, $text, &$chk_true, &$chk_false){
		$ptn = "/\S+/u";
		if (isset($value) && is_array($value)){
			foreach ($value as $val){
				self::_multiInput($val, $text, $chk_true, $chk_false);
			}
		} else {
			$tmp = preg_match($ptn, $value);
			if (isset($value) && $tmp !== FALSE && $tmp > 0){
				$chk_true++;
			} else {
				$chk_false++;
			}
		}
	}

	/**
	 * 最小文字数チェック
	 * @param string $value 検査したい値
	 * @param int $min 最小文字数
	 * @param string $text エラー時の項目名
	 * @return string エラーが無ければ『空文字』。エラーが有ればエラーメッセージ。を返す。
	 */
	public static function MinLength($value, $min, $text){
		if (is_null($value) || $value === "") {
			return "";
		}
		if (mb_strlen($value) < $min){
			return sprintf("『%s』は%d文字以上です。", $text, $max);
		}
		return "";
	}

	/**
	 * 最大文字数チェック
	 * @param string $value 検査したい値
	 * @param int $max 最大文字数
	 * @param string $text エラー時の項目名
	 * @return string エラーが無ければ『空文字』。エラーが有ればエラーメッセージ。を返す。
	 */
	public static function MaxLength($value, $max, $text){
		if (is_null($value) || $value === "") {
			return "";
		}
		if (mb_strlen($value) > $max){
			return sprintf("%sが%d文字を超えています。", $text, $max);
		}
		return "";
	}

	/**
	 * 文字数範囲チェック
	 * @param string $value 検査したい値
	 * @param int $min 最小文字数
	 * @param int $max 最大文字数
	 * @param string $text エラー時の項目名
	 * @return string エラーが無ければ『空文字』。エラーが有ればエラーメッセージ。を返す。
	 */
	public static function RangeLength($value, $min, $max, $text){
		if (is_null($value) || $value === "") {
			return "";
		}
		if ((mb_strlen($value) < $min) || (mb_strlen($value) > $max)){
			return sprintf("『%s』は%d文字から%d文字までの範囲です。", $text, $min, $max);
		}
		return "";
	}

	/**
	 * 範囲チェック($from <= $value && $value <= $to)
	 * @param string $value 検査したい値
	 * @param mixed $from bool or string 下限。不要な場合は FALSE を指定。
	 * @param mixed $to bool or string 上限。不要な場合は FALSE を指定。
	 * @param string $text エラー時の項目名
	 * @return string
	 */
	public static function Range($value, $from, $to, $text){
		if (is_null($value) || $value === "" || ($from === FALSE && $to === FALSE)) {
			return "";
		}
		if ($from !== FALSE && $to !== FALSE){
			if ($value < $from || $value > $to){
				return sprintf("『%s』は%dから%dまでの範囲です。", $text, $from, $to);
			}
		} elseif ($from !== FALSE && $to === FALSE){
			if ($value < $from){
				return sprintf("『%s』は%d以上です。", $text, $from);
			}
		} elseif ($from === FALSE && $to !== FALSE){
			if ($value > $to){
				return sprintf("『%s』は%d以下です。", $text, $from);
			}
		}
		return "";
	}

	/**
	 * 文字数(バイト数)チェック SQL Server用
	 * @param string $value 検査したい値
	 * @param int $size 制限文字数
	 * @param string $text エラー時の項目名
	 * @return string エラーが無ければ『空文字』。エラーが有ればエラーメッセージ。を返す。
	 */
	public static function MaxByte($value, $size, $text) {
		$str = mb_convert_encoding($value, "SJIS", "auto");
		$byte = strlen(bin2hex($str)) / 2;
		if($byte > ($size*2)) {
			return sprintf("%sが%d文字を超えています。", $text, $size);
		}
		return "";
	}

	/**
	 * 正数(数字)チェック
	 * @param string $value 検査したい値
	 * @param string $text エラー時の項目名
	 * @return string エラーが無ければ『空文字』。エラーが有ればエラーメッセージ。を返す。
	 */
	public static function Number($value, $text){
		if (is_null($value) || $value === "") {
			return "";
		}
		$ptn = "/^[0-9]*$/u";
		$ret = preg_match($ptn, $value);
		if ($ret !== FALSE && $ret > 0){
			return "";
		}
		return sprintf("%sは半角数字で入力してください。", $text);
	}

	/**
	 * 数字+記号チェック
	 * @param string $value 検査したい値
	 * @param string $text エラー時の項目名
	 * @param string $symbol 追加したい記号（正規表現）
	 * @return string エラーが無ければ『空文字』。エラーが有ればエラーメッセージ。を返す。
	 */
	public static function NumberSymbol($value, $text, $symbol="-_\."){
		if (is_null($value) || $value === "") {
			return "";
		}
		$ptn = "/^[0-9{$symbol}]*$/iu";
		$ret = preg_match($ptn, $value);
		if ($ret !== FALSE && $ret > 0){
			return "";
		} else {
			$tmp = "";
			$symbol = str_replace("\\", "", $symbol);
			foreach(str_split($symbol) as $value){
				$tmp.= "『{$value}』";
			}
			return sprintf("『%s』は数字と%sのみ入力ができます。", $text, $tmp);
		}
	}

	/**
	 * 数値(整数/小数)チェック
	 * @param string $value 検査したい値
	 * @param string $text エラー時の項目名
	 * @param int $decimal 最大少数桁の指定（デフォは無し。）
	 * @param int $digit 最大桁の指定（デフォは10桁まで。）
	 * @return string エラーが無ければ『空文字』。エラーが有ればエラーメッセージ。を返す。
	 */
	public static function Numeric($value, $text, $decimal=0, $digit=10){
		if (is_null($value) || $value === "") {
			return "";
		}
		$decimal = (is_int($decimal) && $decimal >= 0 ? $decimal : 0);
		$digit = (is_int($digit) && $digit > 0 ? $digit : 10);
		$flg_dot = mb_strpos($value, '.');
		// 小数不可
		if ($flg_dot !== FALSE && $decimal === 0){
			return sprintf("『%s』は整数のみで、小数は入力ができません。", $text);
		}
		// 小数可
		else {
			// 入力有り
			if ($value !== ""){
				// 小数有り
				if ($flg_dot !== FALSE){
					if (preg_match(sprintf("/^[-+]?[0-9]{1,%d}\.[0-9]{1,%d}$/u", $digit, $decimal), $value)){
						return "";
					} else {
						return sprintf("『%s』は数値のみで、整数は%d桁まで小数は%d桁まで入力ができます。", $text, $digit, $decimal);
					}
				}
				// 小数無し
				else {
					if(preg_match(sprintf("/^[-+]?[0-9]{1,%d}$/u", $digit), $value)){
						return "";
					} else {
						return sprintf("『%s』は数値のみで、%d桁まで入力ができます。", $text, $digit);
					}
				}
			}
			// 入力無し
			else {
				return "";
			}
		}
	}

	/**
	 * 数値(正数)チェック（!is_numeric() , $value &lt; 0）
	 * @param string $value 検査したい値
	 * @param string $text エラー時の項目名
	 * @param bool $flg_zero 「0」を許可する場合。TRUE。
	 * @return string エラーが無ければ『空文字』。エラーが有ればエラーメッセージ。を返す。
	 */
	public static function NumericPositive($value, $text, $flg_zero=TRUE){
		if (is_null($value) || $value === "") {
			return "";
		}
		if (!is_numeric($value)) {
			return sprintf("『%s』は数値ではありません。", $text);
		}
		if ($flg_zero){
			if ($value < 0) {
				return sprintf("『%s』は正数ではありません。", $text);
			}
		} else {
			if ($value <= 0) {
				return sprintf("『%s』は正数ではありません。", $text);
			}
		}
		return "";
	}

	/**
	 * 数値(負数)チェック（!is_numeric() , $value &gt; 0）
	 * @param string $value 検査したい値
	 * @param string $text エラー時の項目名
	 * @param bool $flg_zero 「0」を許可する場合。TRUE。
	 * @return string エラーが無ければ『空文字』。エラーが有ればエラーメッセージ。を返す。
	 */
	public static function NumericNegative($value, $text, $flg_zero=TRUE){
		if (is_null($value) || $value === "") {
			return "";
		}
		if (!is_numeric($value)) {
			return sprintf("『%s』は数値ではありません。", $text);
		}
		if ($flg_zero){
			if ($value > 0) {
				return sprintf("『%s』は負数ではありません。", $text);
			}
		} else {
			if ($value >= 0) {
				return sprintf("『%s』は負数ではありません。", $text);
			}
		}
		return "";
	}

	/**
	 * 英数チェック
	 * @param string $value 検査したい値
	 * @param string $text エラー時の項目名
	 * @return string エラーが無ければ『空文字』。エラーが有ればエラーメッセージ。を返す。
	 */
	public static function AlphaNumber($value, $text){
		if (is_null($value) || $value === "") {
			return "";
		}
		$ptn = "/^[a-z0-9]*$/iu";
		$ret = preg_match($ptn, $value);
		if ($ret !== FALSE && $ret > 0){
			return "";
		} else {
			return sprintf("『%s』は半角英数字で入力してください。", $text);
		}
	}

	/**
	 * 英数+記号チェック
	 * @param string $value 検査したい値
	 * @param string $text エラー時の項目名
	 * @param string $symbol 追加したい記号（正規表現）
	 * @return string エラーが無ければ『空文字』。エラーが有ればエラーメッセージ。を返す。
	 */
	public static function AlphaNumberSymbol($value, $text, $symbol="-_\."){
		if (is_null($value) || $value === "") {
			return "";
		}
		$ptn = "/^[a-z0-9{$symbol}]*$/iu";
		$ret = preg_match($ptn, $value);
		if ($ret !== FALSE && $ret > 0){
			return "";
		} else {
			$tmp = "";
			$symbol = str_replace("\\", "", $symbol);
			foreach(str_split($symbol) as $value){
				$tmp.= "『{$value}』";
			}
			return sprintf("%sは半角英数字と%sで入力してください。", $text, $tmp);
		}
	}

	/**
	 * 日付のチェック(Y-m-d)
	 * @param string $value 検査したい値
	 * @param string $text エラー時の項目名
	 * @param string $separate 日付の区切り文字（デフォは「-」）
	 * @return string エラーが無ければ『空文字』。エラーが有ればエラーメッセージ。を返す。
	 */
	public static function Date($value, $text, $separate="-"){
		if (is_null($value) || $value === "") {
			return "";
		}
		$tmp_sep = $separate;
		if (preg_match(self::PTN_ESCAPE_REGEX, $separate)){
			$tmp_sep = "\\".$separate;
		}
		$ptn = "/^[0-9]{4}{$tmp_sep}[0-9]{2}{$tmp_sep}[0-9]{2}$/iu";
		$ret = preg_match($ptn, $value);
		if ($ret !== FALSE && $ret > 0){
			$exp = explode($separate, $value);
			if (checkdate((int)$exp[1], (int)$exp[2], (int)$exp[0])){
				return "";
			} else {
				return sprintf('%sの日付は正しくありません。', $text);
			}
		} else {
			return sprintf('%1$sは正しくありません。(例.2014%2$s01%2$s01)', $text, $separate);
		}
	}

	/**
	 * 日時のチェック(Y-m-d H:i または Y-m-d H:i:s)
	 * @param string $value 検査したい値
	 * @param string $text エラー時の項目名
	 * @param string $separate 日付の区切り文字（デフォは「-」）
	 * @param bool $flg_second 秒のチェック有無
	 * @return string エラーが無ければ『空文字』。エラーが有ればエラーメッセージ。を返す。
	 */
	public static function DateTime($value, $text, $separate="-", $flg_second=FALSE){
		if (is_null($value) || $value === "") {
			return "";
		}
		$tmp_sep = $separate;
		if (preg_match(self::PTN_ESCAPE_REGEX, $separate)){
			$tmp_sep = "\\".$separate;
		}
		$ptn = "/^[0-9]{4}{$tmp_sep}[0-9]{2}{$tmp_sep}[0-9]{2} [0-9]{2}:[0-9]{2}".($flg_second ? ":[0-9]{2}" : "")."$/iu";
		$ret = preg_match($ptn, $value);
		if ($ret !== FALSE && $ret > 0){
			$format = "Y{$separate}m{$separate}d H:i".($flg_second ? ":s" : "");
			$d = DateTime::createFromFormat($format, $value);
			if ($d && $d->format($format) == $value){
				return "";
			} else {
				return sprintf('%sには正しい日時を入力してください。', $text);
			}
		} else {
			return sprintf('%sには正しい日時を入力してください。', $text, $separate, ($flg_second ? ":59" : ""));
		}
	}

	/**
	 * 時刻のチェック(H:i または H:i:s)
	 * @param string $value 検査したい値
	 * @param string $text エラー時の項目名
	 * @param bool $flg_second 秒のチェック有無
	 * @return string エラーが無ければ『空文字』。エラーが有ればエラーメッセージ。を返す。
	 */
	public static function Time($value, $text, $flg_second=FALSE){
		$separate = ":";
		if (is_null($value) || $value === "") {
			return "";
		}
		$tmp_sep = $separate;
		if (preg_match(self::PTN_ESCAPE_REGEX, $separate)){
			$tmp_sep = "\\".$separate;
		}
		$ptn = "/^[0-9]{2}:[0-9]{2}".($flg_second ? ":[0-9]{2}" : "")."$/iu";
		$ret = preg_match($ptn, $value);
		if ($ret !== FALSE && $ret > 0){
			$format = "H:i".($flg_second ? ":s" : "");
			$d = DateTime::createFromFormat($format, $value);
			if ($d && $d->format($format) == $value){
				return "";
			} else {
				return sprintf('『%s』の時刻は正しくありません。', $text);
			}
		} else {
			return sprintf('『%1$s』は正しくありません。(例.23:59%3$s)', $text, $separate, ($flg_second ? ":59" : ""));
		}
	}

	/**
	 * 大小関係チェック（from <= to）<br>
	 * （『日付』・『日時』・『時刻』・『数値』・『文字列(ASCIIコードで判定？)』の大小比較）
	 * @param string $from From値（小）
	 * @param string $to To値（大）
	 * @param string $text エラー時の項目名
	 * @return string エラーが無ければ『空文字』。エラーが有ればエラーメッセージ。を返す。
	 */
	public static function SmallLarge($from, $to, $text){

		// FromとToが両方入力されていない場合は処理を抜ける
		if (is_null($from) || ($from === "") || is_null($to) || ($to === "")) {
			return;
		}

		// 大小をチェックする
		if($from <= $to){
			return "";
		} else {
			return sprintf('『%s』の大小関係に誤りがあります。', $text);
		}
	}

	/**
	 * EMailのチェック
	 * @param string $value 検査したい値
	 * @param string $text エラー時の項目名
	 * @return string エラーが無ければ『空文字』。エラーが有ればエラーメッセージ。を返す。
	 */
	public static function EMail($value, $text){
		if (is_null($value) || $value === "") {
			return "";
		}
		$tmp = explode("@", $value);
		if (count($tmp) < 2){
			return sprintf("%sは正しくありません。＠マーク等を確認してください。", $text);
		}
		// 旧DoCoMo対応（記号の連続OK）
		$local = $tmp[0];
		$ptn = "/^[a-z0-9\._+-]+$/iu";
		$ret = preg_match($ptn, $local);
		if (!$ret){
			return sprintf("『%s』は正しくありません。", $text);
		}
		// ---
		// ＠以降
		$domain = $tmp[1];
		// 連続した記号を許可しない。
		$ptn = "/^[_+-\.]{2,}$/u";
		$ret = preg_match($ptn, $domain);
		if ($ret !== FALSE && $ret > 0){
			return sprintf("『%s』は正しくありません。", $text);
		}
		// 開始は英数、終了は英字。
		$ret = preg_match("/^[a-z0-9].+[a-z]$/iu", $domain);
		if (!$ret){
			return sprintf("『%s』は正しくありません。", $text);
		}
		// 「.」で区切られた文字のチェック。（トップレベルドメイン以外）
		$tmp = explode(".", $domain);
		for ($i=0; $i<(count($tmp) - 1); $i++){
			$ret = preg_match("/^[a-z0-9_+-]+$/iu", $tmp[$i]);
			if (!$ret){
				return sprintf("『%s』は正しくありません。", $text);
			}
		}
		// トップレベルドメインは英字2～6文字。
		$ret = preg_match("/[a-z]{2,6}/iu", $tmp[count($tmp) - 1]);
		if (!$ret){
			return sprintf("『%s』は正しくありません。", $text);
		}
		return "";
	}

	/**
	 * フリガナのチェック
	 * @param string $value 検査したい値
	 * @param string $text エラー時の項目名
	 * @return string エラーが無ければ『空文字』。エラーが有ればエラーメッセージ。を返す。
	 */
	public static function Kana($value, $text){
		if (is_null($value) || $value === "") {
			return "";
		}

		$ptn = "/(\s|　)/";
		$str = preg_replace($ptn,"",$value);

		$ptn = "/^[ァ-ヶー]+$/u";
		$ret = preg_match($ptn, $str);
		if ($ret !== FALSE && $ret > 0){
			return "";
		} else {
			return sprintf("『%s』は全角カタカナのみ入力ができます。", $text);
		}
	}

	/**
	 * URLのチェック
	 * @param string $value 検査したい値
	 * @param string $text エラー時の項目名
	 * @return string エラーが無ければ『空文字』。エラーが有ればエラーメッセージ。を返す。
	 */
	public static function URL($value, $text){
		if (is_null($value) || $value === "") {
			return "";
		}
		$ptn = "/^(https?)(:\/\/[-_.!~*\'()a-zA-Z0-9;\/?:\@&=+\$,%#]+)$/";
		$ret = preg_match($ptn, $value);
		if ($ret !== FALSE && $ret > 0){
			return "";
		} else {
			return sprintf("『%s』は正しくありません。文字を確認してください。", $text);
		}
	}

	/**
	 * 画像ファイルチェック（GIF、JPEG、PNG）<br>
	 * （ファイルパスは正しい(ファイルが存在する)ことが前提。）
	 * @param array $img_path ファイルパス
	 * @param string $text エラー時の項目名
	 * @return string エラーが無ければ『空文字』。エラーが有ればエラーメッセージ。を返す。
	 */
	public static function Img($img_path, $text)
	{
		$ret = "";
		if (file_exists($img_path)){
			$type = exif_imagetype($img_path);
			switch ($type){
				case IMAGETYPE_GIF:
				case IMAGETYPE_JPEG:
				case IMAGETYPE_PNG:
					$ret = "";
					break;
				default:
					$ret = sprintf("『%s』は画像ファイルではありません。", $text);
			}
		}
		return $ret;
	}

	/**
	 * IPアドレスチェック(IPV4)
	 * @param string $value 検査したい値
	 * @param string $text エラー時の項目名
	 * @return string エラーが無ければ『空文字』。エラーが有ればエラーメッセージ。を返す。
	 */
	public static function IPAddressIPV4($value, $text){
		if (is_null($value) || $value === "") {
			return "";
		}
		$ret = filter_var($value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4);
		if ($ret !== FALSE){
			return "";
		} else {
			return sprintf("『%s』の形式に誤りがあります。", $text);
		}
	}

}