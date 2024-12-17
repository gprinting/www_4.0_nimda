<?
 /***********************************************************************************
 *** 프로젝트 : CyPress
 *** 개발영역 : Members Module
 *** 개 발 자 : 김성진
 *** 개발날짜 : 2016.06.15
 ***********************************************************************************/

 class CLS_Members {

	 var $dbi;
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
		 $this->dbi		     = null;
		 $this->sql		     = null;
		 $this->rowsData = null;
		 $this->idx		     = null;
		 $this->usrid       = null;
		 $this->usrpw     = null;
		 $this->curtime    = time();
		 $this->remoteip  = $_SERVER["REMOTE_ADDR"];
	 }


	 /***********************************************************************************
	 *** DB 연결 ***
	 ***********************************************************************************/

	 function DBCon($_DBS) {
		 $this->Init();

		 $this->dbi = new CLS_DBSet($_DBS);
		 $CDbcon = new CLS_DBConect($this->dbi->host, $this->dbi->user, $this->dbi->passwd, $this->dbi->name, $this->dbi->alias, $this->dbi->charset, $this->dbi->collate);

		 return $CDbcon;
	 }


	 /***********************************************************************************
	 *** 회원 로그인 정보 체크
	 ***********************************************************************************/

	 function getMembersLoginCheck($_DBS, $rData) {
		$dbc = $this->DBCon($_DBS);

		$this->Init();
		$this->usrid = $rData['usr_id'];
		$this->usrpw = $rData['usr_pw'];

		$this->sql = "SELECT member_name, passwd FROM "._TBL_MEMBERS." ";
		$this->sql .= "WHERE member_id = '".$this->usrid."' LIMIT 1";
		$this->rowsData = new CLS_DBQuery($this->sql, $dbc);

		if (!trim($this->rowsData->error)) {
			if ($this->rowsData->num_rows > 0) {
				$nRes['name'] = $this->rowsData->data[0]['member_name'];
				$nRes['passwd'] = $this->rowsData->data[0]['passwd'];
			} else {
				$nRes = "FAILED";
			}
		} else {
			$nRes = "ERROR";
		}

		$dbc->DBClose();

		return $nRes;
	 }


	 /***********************************************************************************
	  *** 직원 로그인 정보 체크
	  ***********************************************************************************/

	 function getEmplLoginCheck($_DBS, $rData) {
		 $dbc = $this->DBCon($_DBS);

		 $this->Init();
		 $this->usrid = $rData['usr_id'];
		 $this->usrpw = $rData['usr_pw'];

		 $this->sql = "SELECT name, passwd FROM "._TBL_EMPL." ";
		 $this->sql .= "WHERE empl_id = '".$this->usrid."' LIMIT 1";
		 $this->rowsData = new CLS_DBQuery($this->sql, $dbc);

		 if (!trim($this->rowsData->error)) {
			 if ($this->rowsData->num_rows > 0) {
				 $nRes['name'] = $this->rowsData->data[0]['name'];
				 $nRes['passwd'] = $this->rowsData->data[0]['passwd'];
			 } else {
				 $nRes = "FAILED";
			 }
		 } else {
			 $nRes = "ERROR";
		 }

		 $dbc->DBClose();

		 return $nRes;
	 }



	 /***********************************************************************************
	  *** 패스워드 체크
	  ***********************************************************************************/

	 function getPassWDCheck($_DBS, $rData) {
		 $dbc = $this->DBCon($_DBS);

		 $this->Init();
		 $this->usrpw = $rData['usr_pw'];

		 $this->sql = "select PASSWORD('".$this->usrpw."') AS passwd";
		 $this->rowsData = new CLS_DBQuery($this->sql, $dbc);

		 if (!trim($this->rowsData->error)) {
			 if ($this->rowsData->num_rows > 0) {
				 $nRes = $this->rowsData->data[0]['passwd'];
			 } else {
				 $nRes = "FAILED";
			 }
		 } else {
			 $nRes = "ERROR";
		 }

		 $dbc->DBClose();

		 return $nRes;
	 }

 }
?>