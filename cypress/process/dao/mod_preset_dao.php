<?
 /***********************************************************************************
 *** 프로젝트 : CyPress
 *** 개발영역 : Preset Module
 *** 개 발 자 : 김성진
 *** 개발날짜 : 2016.09.08
 ***********************************************************************************/

 class CLS_Preset {

	 var $sql;
	 var $rowsData;
	 var $idx;
	 var $curtime;
	 var $remoteip;


	 /***********************************************************************************
	  *** 초기화 ***
	  ***********************************************************************************/

	 function Init() {
		 $this->sql			   = null;
		 $this->rowsData	   = null;
		 $this->idx			   = null;
		 $this->curtime 	   = time();
		 $this->remoteip 	   = $_SERVER["REMOTE_ADDR"];
	 }


	 /***********************************************************************************
      *** 데이터 체크
      ***********************************************************************************/

	 function getPresetInfoDataValue($conn, $rData) {
		 $this->Init();

		 $this->sql = "SELECT typset_format_seqno FROM "._TBL_TYPSET_FORMAT." WHERE preset_name = '".$rData['prn']."' LIMIT 1";
		 $rs = $conn->Execute($this->sql);

		 if ($rs && !$rs->EOF) {
			 $nRes = "UPDATE";
		 } else if ($rs->EOF) {
			 $nRes = "INSERT";
		 } else {
			 $nRes = "ERROR";
		 }

		 return $nRes;
	 }


	 /***********************************************************************************
	  *** 데이터 삽입
	  ***********************************************************************************/

	 function setPresetInsertDataComplete($conn, $rData) {
		 $this->Init();

         $honggak_yn = "Y";
         if (strrpos($rData['prn'], "돈땡")) {
             $honggak_yn = "N";      
         }

		 $this->sql = "INSERT INTO "._TBL_TYPSET_FORMAT." (";
		 $this->sql .= "affil, subpaper, wid_size, vert_size, cate_sortcode, honggak_yn, preset_name, preset_cate, format_name, paper, worker_id, regi_date";
		 $this->sql .= ") VALUE (";
		 $this->sql .= "'".$rData['ps']."', '".$rData['sp']."', '".$rData['psw']."', '".$rData['psh']."', '".$rData['cd']."', '".$honggak_yn."', ";
		 $this->sql .= "'".$rData['prn']."', '".$rData['prc']."', '".$rData['pt']."', '".$rData['pp']."', '".$rData['pw']."', '".$rData['pd']."'";
		 $this->sql .= ")";
		 $rs = $conn->Execute($this->sql);

		 if ($rs == true) {
			 $nRes = "SUCCESS";
		 } else {
			 $nRes = "ERROR";
		 }

		 return $nRes;
	 }


	 /***********************************************************************************
	  *** 데이터 업데이트
	  ***********************************************************************************/

	 function setPresetUpdateDataComplete($conn, $rData) {
		 $this->Init();

		 $this->sql = "UPDATE "._TBL_TYPSET_FORMAT." SET ";
		 $this->sql .= "affil = '".$rData['ps']."', wid_size = '".$rData['psw']."', ";
		 $this->sql .= "vert_size = '".$rData['psh']."', preset_name = '".$rData['prn']."', preset_cate = '".$rData['prc']."', ";
		 $this->sql .= "format_name = '".$rData['pt']."', paper = '".$rData['pp']."', worker_id = '".$rData['pw']."' ";
		 $this->sql .= "WHERE preset_name = '".$rData['prn']."' LIMIT 1";
		 $rs = $conn->Execute($this->sql);

		 if ($rs == true) {
			 $nRes = "SUCCESS";
		 } else {
			 $nRes = "ERROR";
		 }

		 return $nRes;
	 }



	 /***********************************************************************************
	  *** 카테고리 코드 가져오기
	  ***********************************************************************************/

	 function getPresetCateCodeDataValue($conn, $sCode) {
		 $this->Init();

		 $this->sql = "SELECT sortcode FROM "._TBL_CATE." WHERE cate_name = '".$sCode."' LIMIT 1";
		 $rs = $conn->Execute($this->sql);

		 if ($rs && !$rs->EOF) {
			 $nRes = $rs->fields['sortcode'];
		 } else if ($rs->EOF) {
			 $nRes = "FAILED";
		 } else {
			 $nRes = "ERROR";
		 }

		 return $nRes;
	 }

 }
?>
