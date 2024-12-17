<?
 /***********************************************************************************
 *** 프로 젝트 : CyPress
 *** 개발 영역 : Crypt Lib
 *** 개  발  자 : 김성진
 *** 개발 날짜 : 2016.06.15
 ***********************************************************************************/


 class CLS_Crypt {

	 var $encoder_key;
     var $key_len;
	 var $rot_ptr_set;


	 /***********************************************************************************
	 *** 초기화
	 ***********************************************************************************/

	 function InitCrpyt() {
		 $this->encoder_key	= "1TAJ223ETA04AE6YL1FB2EK5AA230FC22J4B4M4QN7M4L6GWE";
		 $this->key_len		= strlen($this->encoder_key);
		 $this->rot_ptr_set	= 128;
	 }


	/***********************************************************************************
	*** 복호화
	***********************************************************************************/

	function Encrypt128($txt) {
		$this->InitCrpyt();
		$rot_ptr = $this->rot_ptr_set;

	    $tmp = "";
	    $txt = strrev($txt);
	    $txt_len = strlen($txt);

	    for ($i=0; $i<$txt_len; $i++) {
		     if ($rot_ptr >= $this->key_len) $rot_ptr = 0;

		     $tmp .= $txt[$i] ^ $this->encoder_key[$rot_ptr];
		     $v = ord($tmp[$i]);
		     $tmp[$i] = chr(((($v << 3) & 0xf8) | (($v >> 5) & 0x07)));
		     $rot_ptr++;
	    }

	    $tmp = base64_encode($tmp);
	    $tmp = strrev($tmp);

	    $tmp_string = "";
	    for ($i = 0; $i < strlen($tmp); $i++) {
		     $tmp_str[$i] = substr($tmp, $i, 1);
		     if ($tmp_str[$i] == "+") $tmp_str[$i] = "^";
		     $tmp_string .= $tmp_str[$i];
	    }

	    return $tmp_string;
	}


	/***********************************************************************************
	*** 복호화
	***********************************************************************************/

	function Decrypt128($txt) {
		$this->InitCrpyt();
		$rot_ptr = $this->rot_ptr_set;

		for ($i = 0; $i < strlen($txt); $i++) {
		     $txt_str[$i] = substr($txt, $i, 1);
		     if ($txt_str[$i] == "^") $txt_str[$i] = "+";
			 $txt_string .= $txt_str[$i];
		}

		$txt = $txt_string;

		$tmp = "";
		$txt = strrev($txt);
		$txt = base64_decode($txt);
		$txt_len = strlen($txt);

		for ($i=0; $i<$txt_len; $i++) {
		     if ($rot_ptr >= $this->key_len) $rot_ptr = 0;

		     $v = ord($txt[$i]);
		     $txt[$i] = chr(((($v >> 3) & 0x1f) | (($v << 5) & 0xe0)));
		     $tmp .= $txt[$i] ^ $this->encoder_key[$rot_ptr];
		     $rot_ptr++;
		}

		$tmp = strrev($tmp);
		return $tmp;
	}

 }
?>