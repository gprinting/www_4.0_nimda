<?
 /***********************************************************************************
 *** 프로 젝트 : CyPress
 *** 개발 영역 : Common Lib
 *** 개  발  자 : 김성진
 *** 개발 날짜 : 2016.06.15
 ***********************************************************************************/


 class CLS_Common {


	/***********************************************************************************
	 *** tag 문자셋 변환
	 ***********************************************************************************/

	 function HtmlTagReplace($tag) {
		$tag = str_replace ('\'', '&#039;', $tag);
		$tag = str_replace ('"', '&quot;', $tag);
		$tag = str_replace ('<', '&lt;', $tag);
		$tag = str_replace ('>', '&gt;', $tag);

		return $tag;
	 }


	 /***********************************************************************************
	 *** html 문자셋 변환
	 ***********************************************************************************/

	 function TagHtmlReplace($tag) {
		$tag = str_replace ('&#039;', '\'', $tag);
		$tag = str_replace ('&quot;', '"', $tag);
		$tag = str_replace ('&lt;', '<', $tag);
		$tag = str_replace ('&gt;', '>', $tag);

		return $tag;
	 }



	 /***********************************************************************************
	 *** 문자열 자르기
	 ***********************************************************************************/

	 function CutString($str, $num) {
		$len = strlen($str);
		if ($len <= $num) return $str;

		$strtmp = "";
		$ishan1 = 0;
		$ishan2 = 0;
		$strlength = $len;

		for ($i = 0; $i < $len; $i++) {
			 if(preg_match("/[xA1-xFE][xA1-xFE]/", $str[$i])) $strlength++;
		}

		for ($i = 0; $i < $strlength; $i++) {
			if ($ishan1 == 1) $ishan2 = 1;

			if (ord($str[$i]) > 127 && $ishan1 == 0) {
				$ishan2 = 0;
				$ishan1 = 1;
			}

			if ($ishan2 == 1) $ishan1 = 0;

			if (($i + 1) == $num) {
				if($ishan2 != 1) break;

				$strtmp .= $str[$i]; break;
			}

			$strtmp .= $str[$i];
		}

		return trim($strtmp)."...";
	 }


	 /***********************************************************************************
	 *** 문자열 치환
	 ***********************************************************************************/

	 function ASlash($str) {
		return trim(addslashes($str));
	 }

	 function SSlash($str) {
		 return trim(stripslashes($str));
	 }

	 function STags($str) {
		 return trim(strip_tags($str));
	 }

	 function STagsAddSlash($str) {
		 return trim(strip_tags(addslashes($str)));
     }

	 function STagsStripSlash($str) {
		 return trim(strip_tags(stripslashes($str)));
	 }

	 function STagsStripSlashReplace($str) {
		 return eregi_replace("<br />", "", trim(nl2br(strip_tags(stripslashes($str)))));
	 }

	 function STagsStripSlashBr($str) {
		 return trim(nl2br(strip_tags(stripslashes($str))));
	 }

	 function StripSlashBr($str) {
		 return trim(nl2br(stripslashes($str)));
     }


	 /***********************************************************************************
	 *** 날짜 포멧
	 ***********************************************************************************/

	 function DateType($date, $type, $symbol = "") {
		 if (intVal($date) > 0) {
			 $date = date("YmdHis", intVal($date));

			 switch($type) {
				 case "date" : $year = substr($date, 0, 4);
							   $month = substr($date, 4, 2);
							   $day = substr($date, 6, 2);
							   $get_date = $year.$symbol.$month.$symbol.$day;
							   break;
				 case "time" : $year = substr($date, 0, 4);
							   $month = substr($date, 4, 2);
							   $day = substr($date, 6, 2);
							   $hour = substr($date, 8, 2);
							   $min = substr($date,	10, 2);
							   $sec = substr($date, 12, 2);
							   $get_date = $year.$symbol.$month.$symbol.$day." ".$hour.":".$min.":".$sec;
							   break;
				 default : $get_date = $date; break;
			 }
		 } else {
			 $get_date = "";
		 }

		return $get_date;
	}


	/***********************************************************************************
	*** 램덤 숫자
	***********************************************************************************/

	function Random($min, $max) {
		srand((double) microtime() * 1000000);
		$rand = rand($min, $max);

		return $rand;
	}


	/***********************************************************************************
	 *** 날짜 포멧 MS to Time   2011-02-22T07:40:00.000Z
	 ***********************************************************************************/

	 function MstDate2TimeDate($date) {
		 $date = explode("T", substr($date, 0, strlen($date) - 5));
		 $getTime = strtotime($date[0]." ".$date[1]);

		return $getTime;
	}


	/***********************************************************************************
	 *** 날짜 포멧 MS to Time   May 30 2007 03:34:48:763PM
	 ***********************************************************************************/

	 function MsDate2TimeDate($date) {
		 $date = substr($date, 0, strlen($date) - 6);
		 $getTime = strtotime($date);

		return $getTime;
	}


	/***********************************************************************************
	 *** 변수값 체크
	 ***********************************************************************************/

	 function isValCheck($value, $msg) {
		 if (!$value)  {
			 echo("$msg 이(가) 존재하지 않습니다.");
			 exit;
		 }
	}


	/******************************************************************
    ***  접근 방법 체크
    ******************************************************************/

	function hostCheck() {
		if (!preg_match("/".$_SERVER['HTTP_HOST']."/i", $_SERVER['HTTP_REFERER'])) {
			echo ("올바른 접근방법이 아닙니다.");
			exit;
		 }
	}


	/***********************************************************************************
	 *** 면수
	 ***********************************************************************************/

	 function getColorSideCode($cName) {
		if ($cName == "단면") {
			$side = 1;
		} else if($cName == "양면") {
			$side = 2;
		} else {
			$side = 3;
		}

		return $side;
	}


	/***********************************************************************************
	 *** 진행코드
	 ***********************************************************************************/

	 function getPocessGoCode($osCode) {
		if ($osCode == "2120") {
			$nRes = 0;
		} else if($osCode == "330" || $osCode == "340" || $osCode == "430" || $osCode == "530") {
			$nRes = 1;
		} else {
			$nRes = 0;
		}

		return $nRes;
	}


	 /***********************************************************************************
	  *** 면
	  ***********************************************************************************/

	 function getSideName($scData) {
		 if (strpos($scData, "단면") == 0 && strlen(strpos($scData, "단면")) > 0) {
			 $nRes = 1;
		 } else if(strpos($scData, "양면") == 0 && strlen(strpos($scData, "양면")) > 0 ) {
			 $nRes = 2;
		 } else {
			 $nRes = 3;
		 }

		 return $nRes;
	 }



	/***********************************************************************************
  *** 조판구분
  ***********************************************************************************/

	 function getPenDivsion($pCode) {
		 $nRes = substr($pCode, 0, 1);

		 return $nRes;
	}



	/***********************************************************************************
	 *** PDF 경로
	***********************************************************************************/

	 function getPDFPath($ordRes) {
		 $nRes = "/";
		 $nRes .= $ordRes['od_prdt_name'];
		 $nRes .= "/";
		 $nRes .= $ordRes['od_item_name'];
		 $nRes .= "/";
		 $nRes .= $ordRes['od_kind_name'];
		 $nRes .= "/";

		 return $nRes;
	 }


	 /***********************************************************************************
	  *** 미리보기 경로
	  ***********************************************************************************/

	 function getPreviewPath() {
		 $nRes = "/".date("Y")."/".date("m")."/".date("d")."/";

		 return $nRes;
	 }


	 function getDateToFormat($date) {
		 $nRes = preg_replace("/ /", "-", $date);
		 $nRes = preg_replace("/:/", "-", $nRes);

		 return $nRes;
	 }


	 function getAstricsFormat($ast) {
		 $nData = explode("*", $ast);
		 $nRes = $nData[0].$nData[1];

		 return $nRes;
	 }

 }
?>