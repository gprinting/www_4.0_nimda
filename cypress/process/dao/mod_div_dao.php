<?
 /***********************************************************************************
 *** 프로젝트 : CyPress
 *** 개발영역 : 분할/병함 Module
 *** 개 발 자 : 김성진
 *** 개발날짜 : 2016.09.27
 ***********************************************************************************/

 class CLS_Div {

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
  *** 주문번호 체크
  ***********************************************************************************/

	 function getDivOrderDetailCountFileInfoDataValue($conn, $rData) {
		 $this->Init();

		 $this->sql = "SELECT order_detail_count_file_seqno  FROM "._TBL_ORDER_DETAIL_COUNT_FILE." ";
		 $this->sql .= "WHERE barcode_num = '".$rData['on']."' LIMIT 1";
		 $rs = $conn->Execute($this->sql);

		 if ($rs && !$rs->EOF) {
			 $nRes = $rs->fields['order_detail_count_file_seqno'];
		 } else if ($rs->EOF) {
			 $nRes = "FAILED";
		 } else {
			 $nRes = "ERROR";
		 }

		 return $nRes;
	 }


	 /***********************************************************************************
	  *** 수량 주문 상세 낱장 데이터 가져오기
	  ***********************************************************************************/

	 function getDivAmtOrderDetailSheetInfoDataValue($conn, $nIdx) {
		 $this->Init();

		 $this->sql = "SELECT order_num, state, sheet_typset_seqno FROM "._TBL_AMT_ORDER_DETAIL_SHEET." ";
		 $this->sql .= "WHERE order_detail_count_file_seqno = '".$nIdx."' LIMIT 1";
		 $rs = $conn->Execute($this->sql);

		 CLS_File::FileWrite(_WPATH, "\n" . date("H:i:s")." query : " . $this->sql . "\n", "a+");

		 if ($rs && !$rs->EOF) {
			 $nRes['order_num'] = $rs->fields['order_num'];
			 $nRes['state'] = $rs->fields['state'];
			 $nRes['idx'] = $rs->fields['sheet_typset_seqno'];
		 } else if ($rs->EOF) {
			 $nRes = "FAILED";
		 } else {
			 $nRes = "ERROR";
		 }

		 return $nRes;
	 }


	 /***********************************************************************************
	  *** 기존 데이터 삭제
	  ***********************************************************************************/

	 function setDivAmtOrderDetailSheetDataDeleteComplete($conn, $ordRes) {
		 $this->Init();

		 $this->sql = "DELETE FROM "._TBL_AMT_ORDER_DETAIL_SHEET." ";
		 $this->sql .= "WHERE order_detail_count_file_seqno = '".$ordRes."'";
		 $rs = $conn->Execute($this->sql);
		 CLS_File::FileWrite(_WPATH, "\n" . date("H:i:s")." query : " . $this->sql . "\n", "a+");
		 if ($rs == true) {
			 $nRes = "SUCCESS";
		 } else {
			 $nRes = "ERROR";
		 }

		 return $nRes;
	 }


	 /***********************************************************************************
	  *** 분할 데이터 삽입완료
	  ***********************************************************************************/

	 function setDivAmtOrderDetailSheetDataInsertComplete($conn, $rData, $amtRes, $nIdx) {
		 $this->Init();
		 $qtData = explode(":", $rData['qt']);
		 $qtLen = count($qtData);
		 $sucCount = 0;
		 $errCount = 0;

		 for ($i = 0; $i < $qtLen; $i++) {
			  $this->sql = "INSERT INTO "._TBL_AMT_ORDER_DETAIL_SHEET." (";
			  $this->sql .= "amt, state, sheet_typset_seqno, order_detail_count_file_seqno, order_num";
			  $this->sql .= ") VALUE (";
			  $this->sql .= "'".$qtData[$i]."', '".$amtRes['state']."', ";
			 $this->sql .= "NULL, ";

			  $this->sql .= "'".$nIdx."','" . $amtRes['order_num'] . "'";

			  $this->sql .= ")";
			  $rs = $conn->Execute($this->sql);
			 CLS_File::FileWrite(_WPATH, "\n" . date("H:i:s")." query : " . $this->sql . "\n", "a+");
			  if ($rs == true) {
				  $sucCount++;
			  } else {
				  $errCount++;
			  }
		 }

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

	 function getDivAmtOrderDetailSheetMergeDataValue($conn, $nIdx) {
		 $this->Init();

		 $this->sql = "SELECT SUM(amt) AS amt, state, sheet_typset_seqno, order_num FROM "._TBL_AMT_ORDER_DETAIL_SHEET." ";
		 $this->sql .= "WHERE order_detail_count_file_seqno = '".$nIdx."' GROUP BY order_detail_count_file_seqno";
		 $rs = $conn->Execute($this->sql);

		 if ($rs && !$rs->EOF) {
			 $nRes['order_num'] = $rs->fields['order_num'];
			 $nRes['amt'] = $rs->fields['amt'];
			 $nRes['state'] = $rs->fields['state'];
			 $nRes['idx'] = $rs->fields['sheet_typset_seqno'];
		 } else if ($rs->EOF) {
			 $nRes = "FAILED";
		 } else {
			 $nRes = "ERROR";
		 }

		 return $nRes;
	 }


	 /***********************************************************************************
	  *** 병합 데이터 삽입완료
	  ***********************************************************************************/

	 function setDivAmtOrderDetailSheetMergeDataInsertComplete($conn, $amtRes, $nIdx) {
		 $this->Init();

		 $this->sql = "INSERT INTO "._TBL_AMT_ORDER_DETAIL_SHEET." (";
		 $this->sql .= "amt, state, sheet_typset_seqno, order_detail_count_file_seqno, order_num";
		 $this->sql .= ") VALUE (";
		 $this->sql .= "'".$amtRes['amt']."', '".$amtRes['state']."', ";

		 if (strlen(trim($amtRes['idx'])) <= 0) {
			 $this->sql .= "NULL, ";
		 } else {
			 $this->sql .= "'".$amtRes['idx']."', ";
		 }

		 $this->sql .= "'".$nIdx."','" . $amtRes['order_num'] . "'";

		 $this->sql .= ")";
		 $rs = $conn->Execute($this->sql);

		 if ($rs == true) {
			 $nRes = "SUCCESS";
		 } else {
			 $nRes = "ERROR";
		 }

		 return $nRes;
	 }

 }
?>