<?
 /***********************************************************************************
 *** 프로젝트 : CyPress
 *** 개발영역 : Preset Module
 *** 개 발 자 : 김성진
 *** 개발날짜 : 2016.09.08
 ***********************************************************************************/

 class CLS_Preset {

	 var $dbi;
	 var $sql;
	 var $rowsData;
	 var $idx;
	 var $curtime;
	 var $remoteip;


	 /***********************************************************************************
	 *** 초기화 ***
	 ***********************************************************************************/

	 function Init() {
		 $this->dbi			   = null;
		 $this->sql			   = null;
		 $this->rowsData	   = null;
		 $this->idx			   = null;
		 $this->curtime 	   = time();
		 $this->remoteip 	   = $_SERVER["REMOTE_ADDR"];
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
  *** 데이터 체크
  ***********************************************************************************/

	 function getPresetInfoDataValue($_DBS, $rData) {
		 $dbc = $this->DBCon($_DBS);

		 $this->Init();

		 $this->sql = "SELECT typset_format_seqno AS idx FROM "._TBL_TYPSET_FORMAT." ";
		 $this->sql .= "WHERE name = '".$rData['prn']."' LIMIT 1";
		 $this->rowsData = new CLS_DBQuery($this->sql, $dbc);

		 if (!trim($this->rowsData->error)) {
			 if ($this->rowsData->num_rows <= 0) {
				 $nRes = "INSERT";
			 } else {
				 $nRes = "UPDATE";
			 }
		 } else {
			 $nRes = "ERROR";
		 }

		 $dbc->DBClose();

		 return $nRes;
	 }


	 /***********************************************************************************
	  *** 데이터 삽입
	  ***********************************************************************************/

	 function setPresetInsertDataComplete($_DBS, $rData) {
		 $dbc = $this->DBCon($_DBS);

		 $this->Init();

		 $this->sql = "INSERT INTO "._TBL_TYPSET_FORMAT." (";
		 $this->sql .= "name, affil, subpaper, wid_size, vert_size, cate_sortcode, honggak_yn, freeset_name, freeset_cate, format_name, mat, worker_id, regi_date";
		 $this->sql .= ") VALUE (";
		 $this->sql .= "'".$rData['prn']."', '".$rData['ps']."', '".$rData['sp']."', '".$rData['psw']."', '".$rData['psh']."', '".$rData['cd']."', 'Y', ";
		 $this->sql .= "'".$rData['prn']."', '".$rData['prc']."', '".$rData['pt']."', '".$rData['pp']."', '".$rData['pw']."', '".$rData['pd']."'";
		 $this->sql .= ")";
		 $this->rowsData = new CLS_DBQuery($this->sql, $dbc);

		 if (!trim($this->rowsData->error)) {
			 $nRes = "SUCCESS";
		 } else {
			 $nRes = "ERROR";
		 }

		 $dbc->DBClose();

		 return $nRes;
	 }


	 /***********************************************************************************
	  *** 데이터 업데이트
	  ***********************************************************************************/

	 function setPresetUpdateDataComplete($_DBS, $rData) {
		 $dbc = $this->DBCon($_DBS);

		 $this->Init();

		 $this->sql = "UPDATE "._TBL_TYPSET_FORMAT." SET ";
		 $this->sql .= "name = '".$rData['prn']."' , affil = '".$rData['ps']."', wid_size = '".$rData['psw']."', vert_size = '".$rData['psH']."', ";
		 $this->sql .= "freeset_name = '".$rData['prn']."', freeset_cate = '".$rData['prc']."', format_name = '".$rData['pt']."', mat = '".$rData['pp']."', worker_id = '".$rData['pw']."' ";
		 $this->sql .= "WHERE name = '".$rData['prn']."' LIMIT 1";
		 $this->rowsData = new CLS_DBQuery($this->sql, $dbc);

		 if (!trim($this->rowsData->error)) {
			 $nRes = "SUCCESS";
		 } else {
			 $nRes = "ERROR";
		 }

		 $dbc->DBClose();

		 return $nRes;
	 }



	 /***********************************************************************************
	  *** 카테고리 코드 가져오기
	  ***********************************************************************************/

	 function getPresetCateCodeDataValue($_DBS, $sCode) {
		 $dbc = $this->DBCon($_DBS);

		 $this->Init();

		 $this->sql = "SELECT sortcode FROM "._TBL_CATE." ";
		 $this->sql .= "WHERE cate_name = '".$sCode."' LIMIT 1";
		 $this->rowsData = new CLS_DBQuery($this->sql, $dbc);

		 if (!trim($this->rowsData->error)) {
			 if ($this->rowsData->num_rows > 0) {
				 $nRes = $this->rowsData->data[0]['sortcode'];
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