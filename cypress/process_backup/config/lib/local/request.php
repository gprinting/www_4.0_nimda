<?
 /***********************************************************************************
  *** 프로 젝트 : CyPress
 *** 개발 영역 : Request
 *** 개  발  자 : 김성진
 *** 개발 날짜 : 2016.06.15
 ***********************************************************************************/

 class CLS_Request {

	var $post;
	var $get;

    function Init() {
        $this->post	= null;
        $this->get = null;
    }

	function CLS_Request() {
        $this->Init();
        $this->Parse();
    }

	function Parse() {
		while (list($key, $val) = each($_POST)) {
			   if (is_array($val)) {
				  while (list ($key2, $val2) = each($val)) {
					     $val[trim($key2)] = trim($val2);
				  }

				  $this->post[trim($key)] = $val;
			   } else {
				  $this->post[trim($key)] = trim($val);
			   }
		}

		while (list($key, $val) = each($_GET)) {
			   if (is_array($val)) {
				   while (list ($key2, $val2) = each($val)) {
					      $val[trim($key2)] = trim($val2);
				   }

				   $this->get[trim($key)] = $val;
			   } else {
				   $this->get[trim($key)] = trim($val);
			   }
		}
	}

	function GetValue ($key) {
		if (isset($this->post[$key]) === true) {
			return $this->post[$key];
		} else if (isset($this->get[$key]) === true) {
			return $this->get[$key];
		} else {
			return false;
		}
	}

}
?>