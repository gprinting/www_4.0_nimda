<?
 /***********************************************************************************
 *** 프로젝트 : CyPress
 *** 개발영역 : 분할/병함 Module
 *** 개 발 자 : 김성진
 *** 개발날짜 : 2016.09.27
 ***********************************************************************************/

 class CLS_Div {

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
  *** 주문번호 체크
  ***********************************************************************************/

	 function getDivOrderDetailCountFileInfoDataValue($_DBS, $rData) {
		 $dbc = $this->DBCon($_DBS);

		 $this->Init();

		 $this->sql = "SELECT order_detail_count_file_seqno AS idx FROM "._TBL_ORDER_DETAIL_COUNT_FILE." ";
		 $this->sql .= "WHERE order_detail_file_num = '".$rData['on']."' LIMIT 1";
		 $this->rowsData = new CLS_DBQuery($this->sql, $dbc);

		 if (!trim($this->rowsData->error)) {
			 if ($this->rowsData->num_rows <= 0) {
				 $nRes = "FAILED";
			 } else {
				 $nRes = $this->rowsData->data[0]['idx'];
			 }
		 } else {
			 $nRes = "ERROR";
		 }

		 $dbc->DBClose();

		 return $nRes;
	 }


	 /***********************************************************************************
	  *** 수량 주문 상세 낱장 데이터 가져오기
	  ***********************************************************************************/

	 function getDivAmtOrderDetailSheetInfoDataValue($_DBS, $nIdx) {
		 $dbc = $this->DBCon($_DBS);

		 $this->Init();

		 $this->sql = "SELECT state, sheet_typset_seqno AS idx FROM "._TBL_AMT_ORDER_DETAIL_SHEET." ";
		 $this->sql .= "WHERE order_detail_count_file_seqno = '".$nIdx."' LIMIT 1";
		 $this->rowsData = new CLS_DBQuery($this->sql, $dbc);

		 if (!trim($this->rowsData->error)) {
			 if ($this->rowsData->num_rows <= 0) {
				 $nRes = "FAILED";
			 } else {
				 $nRes['state'] = $this->rowsData->data[0]['state'];
				 $nRes['idx'] = $this->rowsData->data[0]['idx'];
			 }
		 } else {
			 $nRes = "ERROR";
		 }

		 $dbc->DBClose();

		 return $nRes;
	 }


	 /***********************************************************************************
	  *** 기존 데이터 삭제
	  ***********************************************************************************/

	 function setDivAmtOrderDetailSheetDataDeleteComplete($_DBS, $ordRes) {
		 $dbc = $this->DBCon($_DBS);

		 $this->Init();

		 $this->sql = "DELETE FROM "._TBL_AMT_ORDER_DETAIL_SHEET." ";
		 $this->sql .= "WHERE order_detail_count_file_seqno = '".$ordRes."'";
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
	  *** 분할 데이터 삽입완료
	  ***********************************************************************************/

	 function setDivAmtOrderDetailSheetDataInsertComplete($_DBS, $rData, $amtRes, $nIdx) {
		 $dbc = $this->DBCon($_DBS);

		 $this->Init();
		 $qtData = explode(":", $rData['qt']);
		 $qtLen = count($qtData);
		 $sucCount = 0;
		 $errCount = 0;

		 for ($i = 0; $i < $qtLen; $i++) {
			  $this->sql = "INSERT INTO "._TBL_AMT_ORDER_DETAIL_SHEET." (";
			  $this->sql .= "amt, state, sheet_typset_seqno, order_detail_count_file_seqno";
			  $this->sql .= ") VALUE (";
			  $this->sql .= "'".$qtData[$i]."', '".$amtRes['state']."', ";

			  if (strlen(trim($amtRes['idx'])) <= 0) {
				  $this->sql .= "NULL, ";
			  } else {
				  $this->sql .= "'".$amtRes['idx']."', ";
			  }

			  $this->sql .= "'".$nIdx."'";

			  $this->sql .= ")";
			  $this->rowsData = new CLS_DBQuery($this->sql, $dbc);

			  if (!trim($this->rowsData->error)) {
				  $sucCount++;
			  } else {
				  $errCount++;
			  }
		 }

		 $dbc->DBClose();

		 if ($sucCount == $qtLen) {
			 $nRes = "SUCCESS";
		 } else {
			 $nRes = "ERROR";
		 }

		 return $nRes;
	 }


	 /***********************************************************************************
	  *** 수량 주문 상세 낱장 병합 데이터 가져오기
	  ***********************************************************************************/

	 function getDivAmtOrderDetailSheetMergeDataValue($_DBS, $nIdx) {
		 $dbc = $this->DBCon($_DBS);

		 $this->Init();

		 $this->sql = "SELECT SUM(amt) AS amt, state, sheet_typset_seqno AS idx FROM "._TBL_AMT_ORDER_DETAIL_SHEET." ";
		 $this->sql .= "WHERE order_detail_count_file_seqno = '".$nIdx."' GROUP BY order_detail_count_file_seqno";
		 $this->rowsData = new CLS_DBQuery($this->sql, $dbc);

		 if (!trim($this->rowsData->error)) {
			 if ($this->rowsData->num_rows <= 0) {
				 $nRes = "FAILED";
			 } else {
				 $nRes['amt'] = $this->rowsData->data[0]['amt'];
				 $nRes['state'] = $this->rowsData->data[0]['state'];
				 $nRes['idx'] = $this->rowsData->data[0]['idx'];
			 }
		 } else {
			 $nRes = "ERROR";
		 }

		 $dbc->DBClose();

		 return $nRes;
	 }


	 /***********************************************************************************
	  *** 병합 데이터 삽입완료
	  ***********************************************************************************/

	 function setDivAmtOrderDetailSheetMergeDataInsertComplete($_DBS, $amtRes, $nIdx) {
		 $dbc = $this->DBCon($_DBS);

		 $this->Init();

		 $this->sql = "INSERT INTO "._TBL_AMT_ORDER_DETAIL_SHEET." (";
		 $this->sql .= "amt, state, sheet_typset_seqno, order_detail_count_file_seqno";
		 $this->sql .= ") VALUE (";
		 $this->sql .= "'".$amtRes['amt']."', '".$amtRes['state']."', ";

		 if (strlen(trim($amtRes['idx'])) <= 0) {
			 $this->sql .= "NULL, ";
		 } else {
			 $this->sql .= "'".$amtRes['idx']."', ";
		 }

		 $this->sql .= "'".$nIdx."'";

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

 }
?>