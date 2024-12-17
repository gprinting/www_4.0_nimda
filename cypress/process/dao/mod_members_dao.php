<?
 /***********************************************************************************
 *** 프로젝트 : CyPress
 *** 개발영역 : Members Module
 *** 개 발 자 : 김성진
 *** 개발날짜 : 2016.06.15
 ***********************************************************************************/

 class CLS_Members {

	 var $sql;
	 var $rowsData;
	 var $idx;
	 var $usrid;
	 var $usrpw;
	 var $curtime;
	 var $remoteip;


	 /***********************************************************************************
	 *** 초기화 ***
	 ***********************************************************************************/

	 function Init() {
		 $this->sql		 = null;
		 $this->rowsData = null;
		 $this->idx		 = null;
		 $this->usrid    = null;
		 $this->usrpw    = null;
		 $this->curtime  = time();
		 $this->remoteip = $_SERVER["REMOTE_ADDR"];
	 }


	 /***********************************************************************************
	  *** 직원 로그인 정보 체크
	  ***********************************************************************************/

	 function getEmplLoginCheck($conn, $rData) {
		 $this->Init();
		 $this->usrid = $rData['usr_id'];
		 $this->usrpw = $rData['usr_pw'];

		 $this->sql = "SELECT name, passwd FROM "._TBL_EMPL." WHERE empl_id = '".$this->usrid."' LIMIT 1";
		 $rs = $conn->Execute($this->sql);

		 if ($rs && !$rs->EOF) {
			 $nRes['name'] = $rs->fields['name'];
			 $nRes['passwd'] = $rs->fields['passwd'];
		 } else if ($rs->EOF) {
			 $nRes = "FAILED";
		 } else {
			 $nRes = "ERROR";
		 }

		 return $nRes;
	 }



	 /***********************************************************************************
	  *** 패스워드 체크
	  ***********************************************************************************/

	 function getPassWDCheck($conn, $rData) {
		 $this->Init();
		 $this->usrpw = $rData['usr_pw'];

		 $this->sql = "select PASSWORD('".$this->usrpw."') AS passwd";
		 $rs = $conn->Execute($this->sql);

		 if ($rs && !$rs->EOF) {
			 $nRes = $rs->fields['passwd'];
		 } else if ($rs->EOF) {
			 $nRes = "FAILED";
		 } else {
			 $nRes = "ERROR";
		 }

		 return $nRes;
	 }

 }
?>