<?php
class TWMOEDict {
	private $cookiepath = __DIR__.'/tmp/cookie.txt';
	private $o = "";
	private $ccd = "";
	private $sec = "";
	private $ok = false;
	private $status = [];

	function __construct() {
		include(__DIR__.'/config.php');
		require(__DIR__.'/curl.php');
		if (isset($C["TWMOEDict"]["cookiepath"])) {
			$cookiepath = $C["TWMOEDict"]["cookiepath"];
		}
		$res = cURL("http://dict.revised.moe.edu.tw/cbdic/search.htm", false, $this->cookiepath);
		if ($res === false) {
			$this->status = ["error"=>1];
			return;
		}
		preg_match("/<a href=\"\/cgi-bin\/cbdic\/gsweb\.cgi\/\?&o=(.*?)&\" title/", $res, $m);
		$this->o = $m[1];
		$res = cURL("http://dict.revised.moe.edu.tw/cgi-bin/cbdic/gsweb.cgi/?&o={$this->o}&", false, $this->cookiepath);
		if ($res === false) {
			$this->status = ["error"=>2];
			return;
		}
		if (preg_match("/<a href=\"\/cgi-bin\/cbdic\/gsweb\.cgi\?ccd=(.*?)&o=(.*?)&sec=(.*?)&index=.*?\" title/", $res, $m)) {
			$this->ccd = $m[1];
			$this->o = $m[2];
			$this->sec = $m[3];
		} else {
			$this->status = ["error"=>3];
			return;
		}
		$this->ok = true;
		$this->status = ["ok"=>null];
	}
	function status() {
		return $this->status;
	}
	function cookiepath() {
		return $this->cookiepath;
	}
	function search($word, $onlylist = false) {
		if ($this->ok === false) {
			$this->status = ["error"=>5];
			return $this->status;
		}
		$post = array(
			"o" => $this->o,
			"ccd" => $this->ccd,
			"sec" => $this->sec,
			"selectmode" => "mode1",
			"qs0" => $word,
			"psize" => "100"
		);
		$res = cURL("http://dict.revised.moe.edu.tw/cgi-bin/cbdic/gsweb.cgi", $post, $this->cookiepath);
		if ($res === false) {
			$this->status = ["error"=>4];
			return $this->status;
		}
		$mulit = preg_match("/正文資料<font class=numfont>(\d+)<\/font>則/", $res, $m);
		if ($mulit) {
			$cnt = $m[1];
			if ($cnt == 0) {
				return ["ok"=>0];
			} else {
				preg_match_all("/<td class=maintd.>[^<]*<a href=\"(.+?)\" class=\"slink\">(.+?)<\/a>/", $res, $m);
				$response = [];
				foreach ($m[1] as $key => $url) {
					$word = strip_tags($m[2][$key]);
					if (!$onlylist) {
						$res = cURL("http://dict.revised.moe.edu.tw/".$url, false, $this->cookiepath);
						if ($res === false) {
							$response []= ["error"=>null];
						} else {
							$response[$word] = ["ok"=>$this->ParseResult($res)];
						}
					} else {
						$response[$word] = ["ok"=>null];
					}
				}
				return ["ok"=>$cnt, "result"=>$response];
			}
		} else {
			$response[$word] = $this->ParseResult($res);
			return ["ok"=>1, "result"=>$response];
		}
	}
	function ParseResult($res) {
		$res = preg_replace("/<img src=\"\/cbdic\/images\/words\/fe52\.gif\".*?>/", "(一) ", $res);
		$res = preg_replace("/<img src=\"\/cbdic\/images\/words\/fe53\.gif\".*?>/", "(二) ", $res);
		$res = preg_replace("/<img src=\"\/cbdic\/images\/words\/fe54\.gif\".*?>/", "(三) ", $res);
		$res = preg_replace("/<img src=\"\/cbdic\/images\/words\/fe55\.gif\".*?>/", "(四) ", $res);
		$res = preg_replace("/<img src=\"\/cbdic\/images\/words\/fe56\.gif\".*?>/", "(五) ", $res);
		$res = preg_replace("/<img src=\"\/cbdic\/images\/words\/fe57\.gif\".*?>/", "(六) ", $res);
		$res = preg_replace("/<img src=\"\/cbdic\/images\/words\/fe58\.gif\".*?>/", "(七) ", $res);
		$res = preg_replace("/<img src=\"\/cbdic\/images\/words\/fe59_?\.jpg\".*?>/", "(1) ", $res);
		$res = preg_replace("/<img src=\"\/cbdic\/images\/words\/fe5a_?\.jpg\".*?>/", "(2) ", $res);
		$res = preg_replace("/<img src=\"\/cbdic\/images\/words\/fe5b_?\.jpg\".*?>/", "(3) ", $res);
		$res = preg_replace("/<img src=\"\/cbdic\/images\/words\/fe5c_?\.jpg\".*?>/", "(4) ", $res);
		$res = preg_replace("/<img src=\"\/cbdic\/images\/words\/fe5d_?\.jpg\".*?>/", "(5) ", $res);
		$res = preg_replace("/<img src=\"\/cbdic\/images\/words\/fe5e_?\.jpg\".*?>/", "(6) ", $res);
		$res = preg_replace("/<img src=\"\/cbdic\/images\/words\/fe5f_?\.jpg\".*?>/", "(7) ", $res);
		$res = preg_replace("/<img src=\"\/cbdic\/images\/words\/fe60_?\.jpg\".*?>/", "(8) ", $res);
		$res = preg_replace("/<img src=\"\/cbdic\/images\/words\/fe61_?\.jpg\".*?>/", "(9) ", $res);
		$res = preg_replace("/<img src=\"\/cbdic\/images\/words\/fe62_?\.jpg\".*?>/", "(10) ", $res);
		$res = preg_replace("/<img src=\"\/cbdic\/images\/words\/fe63_?\.jpg\".*?>/", "(11) ", $res);
		$res = preg_replace("/<img src=\"\/cbdic\/images\/words\/fe64_?\.jpg\".*?>/", "(12) ", $res);
		$res = preg_replace("/<img src=\"\/cbdic\/images\/words\/fe65_?\.jpg\".*?>/", "(13) ", $res);
		$res = preg_replace("/<img src=\"\/cbdic\/images\/words\/fe66_?\.jpg\".*?>/", "(14) ", $res);
		$res = preg_replace("/<img src=\"\/cbdic\/images\/words\/fe67_?\.jpg\".*?>/", "(15) ", $res);
		$response = [];
		if (preg_match("/字詞.*?<\/b><\/th><td class=\"std2\">(.*?)<\/td>/", $res, $m)) {
			$m[1] = strip_tags($m[1]);
			if (preg_match("/【(.+?)】/", $m[1], $m2)) {
				$response["word"] = html_entity_decode($m2[1]);
			}
		}
		if (preg_match("/注音.*?<\/b><\/th><td class=\"std2\">(.*?)<\/td>/", $res, $m)) {
			$response["bopomofo"] = strip_tags($m[1]);
		}
		if (preg_match("/漢語拼音.*?<\/b><\/th><td class=\"std2\">(.*?)<\/td>/", $res, $m)) {
			$response["pinyin"] = strip_tags($m[1]);
		}
		if (preg_match("/相似詞.*?<\/b><\/th><td class=\"std2\">(.*?)<\/td>/", $res, $m)) {
			$response["synonyms"] = strip_tags($m[1]);
		}
		if (preg_match("/相反詞.*?<\/b><\/th><td class=\"std2\">(.*?)<\/td>/", $res, $m)) {
			$response["antonym"] = strip_tags($m[1]);
		}
		if (preg_match("/釋義.*?<\/b><\/th><td class=\"std2\">(.*?)\n/", $res, $m)) {
			$m[1] = str_replace("\r", "", $m[1]);
			$m[1] = str_replace("</p>", "</p>\n", $m[1]);
			$m[1] = str_replace("</li>", "</li>\n", $m[1]);
			$m[1] = strip_tags($m[1]);
			$m[1] = str_replace("　", " ", $m[1]);
			$m[1] = preg_replace("/\s\s+$/", " ", $m[1]);
			$m[1] = preg_replace("/^ $/m", "", $m[1]);
			$m[1] = preg_replace("/\n+$/", "", $m[1]);
			$response["meaning"] = $m[1];
		}
		if (preg_match("/本頁網址︰<\/span><input type=\"text\" value=\"(.+?)\" size/", $res, $m)) {
			$response["url"] = $m[1];
		}
		return $response;
	}
}
