<?
 /***********************************************************************************
 *** 프로젝트 : CyPress
 *** 개발영역 : State Module
 *** 개 발 자 : 김성진
 *** 개발날짜 : 2016.09.08
 ***********************************************************************************/

 class CLS_State {

	 var $dbi;
	 var $sql;
	 var $rowsData;
	 var $idx;
	 var $fileOrderNum;
	 var $detailOrderNum;
	 var $commonOrderNum;
	 var $state;
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
		 $this->fileOrderNum   = null;
		 $this->detailOrderNum = null;
		 $this->commonOrderNum = null;
		 $this->state   	   = null;
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
	 *** 상태값 변경 프로세스
	 ***********************************************************************************/

	 function setStateValueDataChangeComplete($_DBS, $OrderNum, $curStCode, $chgStCode) {
		$dbc = $this->DBCon($_DBS);

		$this->Init();
		$this->fileOrderNum = $OrderNum;
		$this->detailOrderNum = substr($OrderNum, 0, strlen($OrderNum) - 2);
		$this->commonOrderNum = substr($OrderNum, 0, strlen($OrderNum) - 4);
		$fileTotalCount = 0;
		$detailTotalCount = 0;

		$wpath = "/home/dprinting/nimda/cypress/process/logs/".date("Y_m_d");
		$this->FileWrite($wpath, "-----------------\n$OrderNum\n", "a+");

		// 주문 디테일 파일
		$this->sql = "SELECT order_detail_count_file_seqno AS idx, order_detail_file_num AS file_order_num, state ";
		$this->sql .= "FROM "._TBL_ORDER_DETAIL_COUNT_FILE." ";
		$this->sql .= "WHERE order_detail_file_num LIKE '".$this->detailOrderNum."%' ORDER BY seq ASC";
		$this->rowsData = new CLS_DBQuery($this->sql, $dbc);
		$odcfError = $this->rowsData->error;
		$odcfTotalCount = $this->rowsData->num_rows;

		 $this->FileWrite($wpath, $this->sql."\n", "a+");

		if (!trim($odcfError)) {
			$this->FileWrite($wpath, $odcfTotalCount."\n", "a+");

			for ($i = 0; $i < $odcfTotalCount; $i++) {
				 $fnRes[$i]['idx'] = $this->rowsData->data[$i]['idx'];
				 $fnRes[$i]['file_order_num'] = $this->rowsData->data[$i]['file_order_num'];
				 $fnRes[$i]['state'] = $this->rowsData->data[$i]['state'];

				 $this->FileWrite($wpath, $this->fileOrderNum."-".$fnRes[$i]['file_order_num']."-".$fnRes[$i]['state']."-".$curStCode."\n", "a+");

				 if ($this->fileOrderNum == $fnRes[$i]['file_order_num'] && $fnRes[$i]['state'] == $curStCode) {
					 // amt_order_detail_sheet update
					 $stRes = $this->getLocalSheetTypsetDataValue($dbc);
					 $this->FileWrite($wpath, $stRes."\n", "a+");

					 if ($stRes != "ERROR" && $stRes != "FAILED") {
						 $amtRes = $this->setLocalAmtOrderDetailSheetUpdateComplete($dbc, $fnRes[$i]['idx'], $stRes, $chgStCode);

						 if ($amtRes == "SUCCESS") {
							 $fpRes = $this->setLocalOrderDetailFileStateUpdateComplete($dbc, $this->fileOrderNum, $chgStCode);

							 if ($fpRes == "SUCCESS") {
								 $fileTotalCount++;
							 }
						 } else {
							 $nRes = $amtRes;
						 }
					 } else {
						 $nRes = $stRes;
					 }
				 } else if ($this->fileOrderNum == $fnRes[$i]['file_order_num'] && $fnRes[$i]['state'] != $curStCode) {
					 $fileTotalCount++;
				 } else if ($this->fileOrderNum != $fnRes[$i]['file_order_num'] && $fnRes[$i]['state'] != $curStCode) {
					 $fileTotalCount++;
				 }
			}

			if ($fileTotalCount == $odcfTotalCount) {
				// 주문 디테일
				$this->sql = "SELECT order_detail_dvs_num AS or_detail_num, state ";
				$this->sql .= "FROM "._TBL_ORDER_DETAIL." ";
				$this->sql .= "WHERE order_detail_dvs_num LIKE '".$this->commonOrderNum ."%' ORDER BY order_detail_seqno ASC";
				$this->rowsData = new CLS_DBQuery($this->sql, $dbc);
				$odError = $this->rowsData->error;
				$odTotalCount = $this->rowsData->num_rows;

				if (!trim($odError)) {
					for ($i = 0; $i < $odTotalCount; $i++) {
						 $odRes[$i]['or_detail_num'] = $this->rowsData->data[$i]['or_detail_num'];
						 $odRes[$i]['state'] = $this->rowsData->data[$i]['state'];

						 if ($this->detailOrderNum == $odRes[$i]['or_detail_num'] && $odRes[$i]['state'] == $curStCode) {
							 $opRes = $this->setLocalOrderDetailStateUpdateComplete($dbc, $this->detailOrderNum, $chgStCode);

							 if ($opRes == "SUCCESS") {
								 $detailTotalCount++;
							 }
						 } else if ($this->detailOrderNum == $odRes[$i]['or_detail_num'] && $odRes[$i]['state'] != $curStCode) {
							 $detailTotalCount++;
						 } else if ($this->detailOrderNum != $odRes[$i]['or_detail_num'] && $odRes[$i]['state'] != $curStCode) {
							 $detailTotalCount++;
						 }
					}

					if ($detailTotalCount == $odTotalCount) {
						// 주문공통
						$this->setLocalOrderCommonStateUpdateComplete($dbc, substr($this->commonOrderNum, 1, strlen($this->commonOrderNum)), $chgStCode);
					}
				} else {
					$nRes = "ERROR";
				}
			}
		} else {
			$nRes = "ERROR";
		}

		$dbc->DBClose();

		return $nRes;
	 }


	 /***********************************************************************************
	 *** 주문 디테일 파일 상태 업데이트
	 ***********************************************************************************/

 	function setLocalOrderDetailFileStateUpdateComplete($dbc, $OrderNum, $chgStCode) {
		$this->sql = "UPDATE "._TBL_ORDER_DETAIL_COUNT_FILE." SET state = '".$chgStCode."' ";
		$this->sql .= "WHERE order_detail_file_num = '".$OrderNum."' LIMIT 1";
		$this->rowsData = new CLS_DBQuery($this->sql, $dbc);

		if (!trim($this->rowsData->error)) {
			$nRes = "SUCCESS";
		} else {
			$nRes = "ERROR";
		}

		return $nRes;
	}


	 /***********************************************************************************
	 *** 주문 디테일 상태 업데이트
	 ***********************************************************************************/

	 function setLocalOrderDetailStateUpdateComplete($dbc, $OrderNum, $chgStCode) {
		 $this->sql = "UPDATE "._TBL_ORDER_DETAIL." SET state = '".$chgStCode."' ";
		 $this->sql .= "WHERE order_detail_dvs_num = '".$OrderNum."' LIMIT 1";
		 $this->rowsData = new CLS_DBQuery($this->sql, $dbc);

		 if (!trim($this->rowsData->error)) {
			 $nRes = "SUCCESS";
		 } else {
			 $nRes = "ERROR";
		 }

		 return $nRes;
	 }


	 /***********************************************************************************
	 *** 주문 공통 상태 업데이트
	 ***********************************************************************************/

	 function setLocalOrderCommonStateUpdateComplete($dbc, $OrderNum, $chgStCode) {
		 $this->sql = "UPDATE "._TBL_ORDER." SET order_state = '".$chgStCode."' ";
		 $this->sql .= "WHERE order_num = '".$OrderNum."' LIMIT 1";
		 $this->rowsData = new CLS_DBQuery($this->sql, $dbc);

		 if (!trim($this->rowsData->error)) {
			 $nRes = "SUCCESS";
		 } else {
			 $nRes = "ERROR";
		 }

		 return $nRes;
	 }


	 /***********************************************************************************
	  *** 낱장형 테이블 최상위 1개 데이터 가져오기
	  ***********************************************************************************/

	 function getLocalSheetTypsetDataValue($dbc) {
		 $this->sql = "SELECT sheet_typset_seqno AS idx FROM "._TBL_SHEET_TYPESET." ORDER BY sheet_typset_seqno DESC LIMIT 1 ";
		 $this->rowsData = new CLS_DBQuery($this->sql, $dbc);

		 if (!trim($this->rowsData->error)) {
			 if ($this->rowsData->num_rows > 0) {
				 $nRes = $this->rowsData->data[0]['idx'];
			 } else {
				 $nRes = "FAILED";
			 }
		 } else {
			 $nRes = "ERROR";
		 }

		 return $nRes;
	 }


	 /***********************************************************************************
	  *** amt order detail sheet 업데이트
	  ***********************************************************************************/

	 function setLocalAmtOrderDetailSheetUpdateComplete($dbc, $dtfIdx, $shsIdx, $chgStCode) {
		 $wpath = "/home/dprinting/nimda/cypress/process/logs/amt_".date("Y_m_d");

		 $this->sql = "UPDATE "._TBL_AMT_ORDER_DETAIL_SHEET." SET state = '".$chgStCode."', sheet_typset_seqno = '".$shsIdx."' ";
		 $this->sql .= "WHERE order_detail_count_file_seqno = '".$dtfIdx."'";
		 $this->rowsData = new CLS_DBQuery($this->sql, $dbc);

		 $this->FileWrite($wpath, "\n".date("H:i:s")." -> ".$this->sql."\n", "a+");

		 if (!trim($this->rowsData->error)) {
			 $nRes = "SUCCESS";
		 } else {
			 $nRes = "ERROR";
		 }

		 return $nRes;
	 }


	 /***********************************************************************************
	  *** 낱장형 죠판완료된 상태값 체크
	  ***********************************************************************************/

	 function getStateCompleteValueDataCheckValue($_DBS, $ONS, $ON, $state) {
		 $dbc = $this->DBCon($_DBS);
		 $this->Init();
		 $chkCount = 0;
		 $errCount = 0;

		 $wpath = "/home/dprinting/nimda/cypress/process/logs/plateenrol_".date("Y_m_d");

		 for ($i = 1; $i <= $ONS; $i++) {
			  $this->sql = "SELECT order_detail_count_file_seqno AS idx FROM " . _TBL_ORDER_DETAIL_COUNT_FILE . " ";
			  $this->sql .= "WHERE order_detail_file_num = '" .$ON[$i]. "' AND state = '".$state."'";
			  $this->rowsData = new CLS_DBQuery($this->sql, $dbc);

			  $this->FileWrite($wpath, date("H:i:s")."->".$this->sql."\n", "a+");

			  if (!trim($this->rowsData->error)) {
				  if ($this->rowsData->num_rows > 0) {
					  $chkCount++;
				  }
			  } else {
				  $errCount++;
			  }
		 }

		 if ($chkCount > 0) {
			 $nRes = "FAILED";
		 } else if ($chkCount <= 0) {
			 $nRes = "SUCCESS";
		 } else {
			 $nRes = "ERROR";
		 }

		 $dbc->DBClose();

		 return $nRes;
	 }


	 function FileWrite($fileName, $content, $mode) {
		 if (!$fileName || !$content) {
			 echo "파일명 또는 내용을 입력하세요!!";
		 } else {
			 $fp = fopen($fileName, $mode);
			 if(!$fp) echo "파일을 여는데 실패했습니다. 다시 확인하시길 바랍니다.";
			 fwrite($fp, $content);
			 fclose($fp);
		 }
	 }

 }
?>