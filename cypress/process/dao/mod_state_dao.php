<?
 /***********************************************************************************
 *** 프로젝트 : CyPress
 *** 개발영역 : State Module
 *** 개 발 자 : 김성진
 *** 개발날짜 : 2016.09.08
 ***********************************************************************************/

 class CLS_State {

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
	 *** 판등록 상태값 변경 프로세스
	 ***********************************************************************************/

	 function setStateValueDataChangeComplete($conn, $OrderNum, $curStCode, $chgStCode) {
		$this->Init();
		$this->fileOrderNum = $OrderNum;
		$this->detailOrderNum = substr($OrderNum, 0, strlen($OrderNum) - 2);
		$this->commonOrderNum = substr($OrderNum, 0, strlen($OrderNum) - 4);
		$fileTotalCount = 0;
		$detailTotalCount = 0;

		// 주문 디테일 파일
		$this->sql = "SELECT order_detail_count_file_seqno, order_detail_file_num, state ";
		$this->sql .= "FROM "._TBL_ORDER_DETAIL_COUNT_FILE." ";
		$this->sql .= "WHERE order_detail_file_num LIKE '".$this->detailOrderNum."%' ORDER BY seq ASC";
		$rs = $conn->Execute($this->sql);

		 $CCFile = new CLS_File;
		 $CCFile->FileWrite(_WPATH . "_판등록", "\n".date("H:i:s")." ORDER DETAIL COUNT FILE-> ".$this->sql."\n", "a+");

		if ($rs && !$rs->EOF) {
			$CCFile->FileWrite(_WPATH . "_판등록", "\n".date("H:i:s")." 상태 UPDATE 시작 \n", "a+");
			$i = 0;
			while ($rs && !$rs->EOF) {
					$fnRes[$i]['idx'] = $rs->fields['order_detail_count_file_seqno'];
					$fnRes[$i]['file_order_num'] = $rs->fields['order_detail_file_num'];
					$fnRes[$i]['state'] = $rs->fields['state'];

					$stRes = $this->getLocalSheetTypsetDataValue($conn);	// amt_order_detail_sheet update

                    if ($stRes != "ERROR" && $stRes != "FAILED") {
                        $amtRes = $this->setLocalAmtOrderDetailSheetUpdateComplete($conn, $fnRes[$i]['idx'], $stRes, $chgStCode);

                        if ($amtRes == "SUCCESS") {
                            $fpRes = $this->setLocalOrderDetailFileStateUpdateComplete($conn, $this->fileOrderNum, $chgStCode);

                            if ($fpRes == "SUCCESS") {
                                $fileTotalCount++;
                                $CCFile->FileWrite(_WPATH . "_판등록", "\n".date("H:i:s")." 상태 단계 1\n", "a+");
                            }
                        } else {
                            $nRes = $amtRes;
                        }
                    } else {
                        $nRes = $stRes;
                    }

                    /*
					if ($this->fileOrderNum == $fnRes[$i]['file_order_num'] && $fnRes[$i]['state'] == $curStCode) {
						$stRes = $this->getLocalSheetTypsetDataValue($conn);	// amt_order_detail_sheet update

						if ($stRes != "ERROR" && $stRes != "FAILED") {
							$amtRes = $this->setLocalAmtOrderDetailSheetUpdateComplete($conn, $fnRes[$i]['idx'], $stRes, $chgStCode);

							if ($amtRes == "SUCCESS") {
								$fpRes = $this->setLocalOrderDetailFileStateUpdateComplete($conn, $this->fileOrderNum, $chgStCode);

								if ($fpRes == "SUCCESS") {
									$fileTotalCount++;
									$CCFile->FileWrite(_WPATH, "\n".date("H:i:s")." 상태 단계 1\n", "a+");
								}
							} else {
								$nRes = $amtRes;
							}
						} else {
							$nRes = $stRes;
						}
					} else if ($this->fileOrderNum == $fnRes[$i]['file_order_num'] && $fnRes[$i]['state'] != $curStCode) {
						$fileTotalCount++;
						$CCFile->FileWrite(_WPATH, "\n".date("H:i:s")." 상태 단계 2\n", "a+");
					} else if ($this->fileOrderNum != $fnRes[$i]['file_order_num'] && $fnRes[$i]['state'] != $curStCode) {
						$fileTotalCount++;
						$CCFile->FileWrite(_WPATH, "\n".date("H:i:s")." 상태 단계 3\n", "a+");
					}
                    */

					$i++;
					$rs->moveNext();
			}

			$CCFile->FileWrite(_WPATH, "\n".date("H:i:s")." 상태 카운터 ".$fileTotalCount."/".$i."\n", "a+");

			if ($fileTotalCount == $i) {
				// 주문 디테일
				$this->sql = "SELECT order_detail_dvs_num, state FROM "._TBL_ORDER_DETAIL." ";
				$this->sql .= "WHERE order_detail_dvs_num LIKE '".$this->commonOrderNum ."%' ORDER BY order_detail_seqno ASC";
				$rs = $conn->Execute($this->sql);

				$CCFile->FileWrite(_WPATH, "\n".date("H:i:s")." 상태 order detail -> ".$this->sql."\n", "a+");

				if ($rs && !$rs->EOF) {
					$n = 0;
					while ($rs && !$rs->EOF) {
						$odRes[$n]['or_detail_num'] = $rs->fields['order_detail_dvs_num'];
						$odRes[$n]['state'] = $rs->fields['state'];

						if ($this->detailOrderNum == $odRes[$n]['or_detail_num'] && $odRes[$n]['state'] == $curStCode) {
							$opRes = $this->setLocalOrderDetailStateUpdateComplete($conn, $this->detailOrderNum, $chgStCode);

							if ($opRes == "SUCCESS") {
								$detailTotalCount++;
							}
						} else if ($this->detailOrderNum == $odRes[$n]['or_detail_num'] && $odRes[$n]['state'] != $curStCode) {
							$detailTotalCount++;
						} else if ($this->detailOrderNum != $odRes[$n]['or_detail_num'] && $odRes[$n]['state'] != $curStCode) {
							$detailTotalCount++;
						}

						$n++;
						$rs->moveNext();
					}

					if ($detailTotalCount == $n) {
						// 주문공통
						$this->setLocalOrderCommonStateUpdateComplete($conn, substr($this->commonOrderNum, 1, strlen($this->commonOrderNum)), $chgStCode);
					}
				} else {
					$nRes = "ERROR";
				}
			}
		} else {
			$nRes = "ERROR";
		}

		 $CCFile->FileWrite(_WPATH, "\n".date("H:i:s")." 상태결과 -> ".$nRes."\n", "a+");

		return $nRes;
	 }


	 /***********************************************************************************
	  *** 추가 상태값 변경 프로세스
	  ***********************************************************************************/

	 function setItemStateValueDataAddChangeComplete($conn, $OrderNum, $curStCode, $chgStCode) {
		 $this->Init();
		 $this->fileOrderNum = $OrderNum;
		 $this->detailOrderNum = substr($OrderNum, 0, strlen($OrderNum) - 2);
		 $this->commonOrderNum = substr($OrderNum, 0, strlen($OrderNum) - 4);
		 $fileTotalCount = 0;
		 $detailTotalCount = 0;

		 // 주문 디테일 파일
		 $this->sql = "SELECT order_detail_count_file_seqno, order_detail_file_num, state ";
		 $this->sql .= "FROM "._TBL_ORDER_DETAIL_COUNT_FILE." ";
		 $this->sql .= "WHERE order_detail_file_num LIKE '".$this->detailOrderNum."%' ORDER BY seq ASC";
		 $rs = $conn->Execute($this->sql);

		 $CCFile = new CLS_File;
		 $CCFile->FileWrite(_WPATH . "_판등록", "\n".date("H:i:s")." -> ".$this->sql."\n", "a+");

		 if ($rs && !$rs->EOF) {
			 $i = 0;
			 while ($rs && !$rs->EOF) {
					 $fnRes[$i]['idx'] = $rs->fields['order_detail_count_file_seqno'];
					 $fnRes[$i]['file_order_num'] = $rs->fields['order_detail_file_num'];
					 $fnRes[$i]['state'] = $rs->fields['state'];

				 	 $CCFile->FileWrite(_WPATH . "_판등록", "\n".date("H:i:s")." -> 업데이트 시작 \n", "a+");

					 if ($this->fileOrderNum == $fnRes[$i]['file_order_num'] && $fnRes[$i]['state'] == $curStCode) {
						 $amtRes = $this->setLocalAmtOrderDetailSheetUpdateComplete($conn, $fnRes[$i]['idx'], "", $chgStCode);

						 if ($amtRes == "SUCCESS") {
							 $fpRes = $this->setLocalOrderDetailFileStateUpdateComplete($conn, $this->fileOrderNum, $chgStCode);

							 if ($fpRes == "SUCCESS") {
								 $fileTotalCount++;
								 $CCFile->FileWrite(_WPATH . "_판등록", "\n".date("H:i:s")." 상태 단계 1\n", "a+");
							 }
						 } else {
							 $nRes = $amtRes;
						 }
					 } else if ($this->fileOrderNum == $fnRes[$i]['file_order_num'] && $fnRes[$i]['state'] != $curStCode) {
						 $fileTotalCount++;
						 $CCFile->FileWrite(_WPATH . "_판등록", "\n".date("H:i:s")." 상태 단계 2\n", "a+");
					 } else if ($this->fileOrderNum != $fnRes[$i]['file_order_num'] && $fnRes[$i]['state'] != $curStCode) {
						 $fileTotalCount++;
						 $CCFile->FileWrite(_WPATH . "_판등록", "\n".date("H:i:s")." 상태 단계 3\n", "a+");
					 }

					 $i++;
					 $rs->moveNext();
			 }

			 if ($fileTotalCount == $i) {
				 // 주문 디테일
				 $this->sql = "SELECT order_detail_dvs_num, state FROM "._TBL_ORDER_DETAIL." ";
				 $this->sql .= "WHERE order_detail_dvs_num LIKE '".$this->commonOrderNum ."%' ORDER BY order_detail_seqno ASC";
				 $rs = $conn->Execute($this->sql);

				 if ($rs && !$rs->EOF) {
					 $n = 0;
					 while ($rs && !$rs->EOF) {
						 $odRes[$n]['or_detail_num'] = $rs->fields['order_detail_dvs_num'];
						 $odRes[$n]['state'] = $rs->fields['state'];

						 if ($this->detailOrderNum == $odRes[$n]['or_detail_num'] && $odRes[$n]['state'] == $curStCode) {
							 $opRes = $this->setLocalOrderDetailStateUpdateComplete($conn, $this->detailOrderNum, $chgStCode);

							 if ($opRes == "SUCCESS") {
								 $detailTotalCount++;
							 }
						 } else if ($this->detailOrderNum == $odRes[$n]['or_detail_num'] && $odRes[$n]['state'] != $curStCode) {
							 $detailTotalCount++;
						 } else if ($this->detailOrderNum != $odRes[$n]['or_detail_num'] && $odRes[$n]['state'] != $curStCode) {
							 $detailTotalCount++;
						 }

						 $n++;
						 $rs->moveNext();
					 }

					 if ($detailTotalCount == $n) {
						 // 주문공통
						 $this->setLocalOrderCommonStateUpdateComplete($conn, substr($this->commonOrderNum, 1, strlen($this->commonOrderNum)), $chgStCode);
					 }
				 } else {
					 $nRes = "ERROR";
				 }
			 }
		 } else {
			 $nRes = "ERROR";
		 }

		 return $nRes;
	 }


	 /***********************************************************************************
	  *** 삭제 상태값 변경 프로세스
	  ***********************************************************************************/

	 function setItemStateValueDataDelChangeComplete($conn, $OrderNum, $chgStCode) {
		 $this->Init();
		 $this->fileOrderNum = $OrderNum;
		 $this->detailOrderNum = substr($OrderNum, 0, strlen($OrderNum) - 2);
		 $this->commonOrderNum = substr($OrderNum, 0, strlen($OrderNum) - 4);
		 $fileTotalCount = 0;
		 $detailTotalCount = 0;

		 // 주문 디테일 파일
		 $this->sql = "SELECT order_detail_count_file_seqno, order_detail_file_num, state ";
		 $this->sql .= "FROM "._TBL_ORDER_DETAIL_COUNT_FILE." ";
		 $this->sql .= "WHERE order_detail_file_num LIKE '".$this->detailOrderNum."%' ORDER BY seq ASC";
		 $rs = $conn->Execute($this->sql);

		 $CCFile = new CLS_File;
		 $CCFile->FileWrite(_WPATH . "_판등록", "\n".date("H:i:s")." -> ".$this->sql."\n", "a+");

		 if ($rs && !$rs->EOF) {
			 $i = 0;
			 while ($rs && !$rs->EOF) {
				 $fnRes[$i]['idx'] = $rs->fields['order_detail_count_file_seqno'];
				 $fnRes[$i]['file_order_num'] = $rs->fields['order_detail_file_num'];
				 $fnRes[$i]['state'] = $rs->fields['state'];

				 if ($this->fileOrderNum == $fnRes[$i]['file_order_num']) {
					 $amtRes = $this->setLocalAmtOrderDetailSheetUpdateComplete($conn, $fnRes[$i]['idx'], "", $chgStCode);

					 if ($amtRes == "SUCCESS") {
						 $fpRes = $this->setLocalOrderDetailFileStateUpdateComplete($conn, $this->fileOrderNum, $chgStCode);

						 if ($fpRes == "SUCCESS") {
							 $fileTotalCount++;
						 }
					 } else {
						 $nRes = $amtRes;
					 }
				 }

				 $i++;
				 $rs->moveNext();
			 }

			 if ($fileTotalCount == $i) {
				 // 주문 디테일
				 $this->sql = "SELECT order_detail_dvs_num, state FROM "._TBL_ORDER_DETAIL." ";
				 $this->sql .= "WHERE order_detail_dvs_num LIKE '".$this->commonOrderNum ."%' ORDER BY order_detail_seqno ASC";
				 $rs = $conn->Execute($this->sql);

				 if ($rs && !$rs->EOF) {
					 $n = 0;
					 while ($rs && !$rs->EOF) {
						 $odRes[$n]['or_detail_num'] = $rs->fields['order_detail_dvs_num'];
						 $odRes[$n]['state'] = $rs->fields['state'];

						 if ($this->detailOrderNum == $odRes[$n]['or_detail_num']) {
							 $opRes = $this->setLocalOrderDetailStateUpdateComplete($conn, $this->detailOrderNum, $chgStCode);

							 if ($opRes == "SUCCESS") {
								 $detailTotalCount++;
							 }
						 }

						 $n++;
						 $rs->moveNext();
					 }

					 if ($detailTotalCount == $n) {
						 // 주문공통
						 $nRes = $this->setLocalOrderCommonStateUpdateComplete($conn, substr($this->commonOrderNum, 1, strlen($this->commonOrderNum)), $chgStCode);
					 }
				 } else {
					 $nRes = "ERROR";
				 }
			 }
		 } else {
			 $nRes = "ERROR";
		 }

		 return $nRes;
	 }


	 /***********************************************************************************
	 *** 주문 디테일 파일 상태 업데이트
	 ***********************************************************************************/

 	function setLocalOrderDetailFileStateUpdateComplete($conn, $OrderNum, $chgStCode) {
		$this->sql = "UPDATE "._TBL_ORDER_DETAIL_COUNT_FILE." SET state = '".$chgStCode."' ";
		$this->sql .= "WHERE order_detail_file_num = '".$OrderNum."' LIMIT 1";
		$rs = $conn->Execute($this->sql);

		$CCFile = new CLS_File;
		$CCFile->FileWrite(_WPATH . "_판등록", "\n".date("H:i:s")."ORDER DETAIL FILE UPDATE -> ".$this->sql."\n", "a+");

		if ($rs == true) {
			$nRes = "SUCCESS";
		} else {
			$nRes = "ERROR";
		}

		return $nRes;
	}


	 /***********************************************************************************
	 *** 주문 디테일 상태 업데이트
	 ***********************************************************************************/

	 function setLocalOrderDetailStateUpdateComplete($conn, $OrderNum, $chgStCode) {
		 $this->sql = "UPDATE "._TBL_ORDER_DETAIL." SET state = '".$chgStCode."' ";
		 $this->sql .= "WHERE order_detail_dvs_num = '".$OrderNum."' LIMIT 1";
		 $rs = $conn->Execute($this->sql);

		 if ($rs == true) {
			 $nRes = "SUCCESS";
		 } else {
			 $nRes = "ERROR";
		 }

		 return $nRes;
	 }


	 /***********************************************************************************
	 *** 주문 공통 상태 업데이트
	 ***********************************************************************************/

	 function setLocalOrderCommonStateUpdateComplete($conn, $OrderNum, $chgStCode) {
		 $this->sql = "UPDATE "._TBL_ORDER." SET order_state = '".$chgStCode."' ";
		 $this->sql .= "WHERE order_num = '".$OrderNum."' LIMIT 1";
		 $rs = $conn->Execute($this->sql);

		 $CCFile = new CLS_File;
		 $CCFile->FileWrite(_WPATH, "\n".date("H:i:s")." 상태 마지막 업데이트 -> ".$this->sql."\n", "a+");

		 if ($rs == true) {
			 $nRes = "SUCCESS";
		 } else {
			 $nRes = "ERROR";
		 }

		 return $nRes;
	 }


	 /***********************************************************************************
	  *** 낱장형 테이블 최상위 1개 데이터 가져오기
	  ***********************************************************************************/

	 function getLocalSheetTypsetDataValue($conn) {
		 $this->sql = "SELECT sheet_typset_seqno FROM "._TBL_SHEET_TYPESET." ORDER BY sheet_typset_seqno DESC LIMIT 1 ";
		 $rs = $conn->Execute($this->sql);

		 $CCFile = new CLS_File;
		 $CCFile->FileWrite(_WPATH . "_판등록", "\n".date("H:i:s")." SHEET TYPE -> ".$this->sql."\n", "a+");

		 if ($rs && !$rs->EOF) {
			 $nRes = $rs->fields['sheet_typset_seqno'];
		 } else if ($rs->EOF) {
			 $nRes = "FAILED";
		 } else {
			 $nRes = "ERROR";
		 }

		 return $nRes;
	 }


	 /***********************************************************************************
	  *** amt order detail sheet 업데이트
	  ***********************************************************************************/

	 function setLocalAmtOrderDetailSheetUpdateComplete($conn, $dtfIdx, $shsIdx, $chgStCode, $amt = null) {
		 $this->sql = "UPDATE "._TBL_AMT_ORDER_DETAIL_SHEET." SET state = '".$chgStCode."', ";

         /*
		 if (strlen(trim($shsIdx)) > 0) $this->sql .= "sheet_typset_seqno = '".$shsIdx."' ";
		 else $this->sql .= "sheet_typset_seqno = NULL ";
         */
         if (!$shsIdx || $shsIdx == 0) {
             $shsIdx = 1;
         }
         $this->sql .= "sheet_typset_seqno = '".$shsIdx."' ";

		 $this->sql .= "WHERE order_detail_count_file_seqno = '" . $dtfIdx . "' ";

		 if($amt !== null) {
			 $this->sql .= " and  amt = '" . $amt ."' ";
		 }
		 $this->sql .= " LIMIT 1 ";
		 $rs = $conn->Execute($this->sql);

		 $CCFile = new CLS_File;
		 $CCFile->FileWrite(_WPATH . "_판등록" . date("H:i:s"), "\n".date("H:i:s")." AMT UPDATE -> ".$this->sql."\n", "a+");

		 if ($rs == true) {
			 $nRes = "SUCCESS";
		 } else {
			 $nRes = "ERROR";
		 }

		 return $nRes;
	 }


	 /***********************************************************************************
	  *** 낱장형 죠판완료된 상태값 체크
	  ***********************************************************************************/

	 function getStateCompleteValueDataCheckValue($conn, $ONS, $ON, $state) {
		 $this->Init();
		 $chkCount = 0;
		 $errCount = 0;

		 for ($i = 1; $i <= $ONS; $i++) {
			  $this->sql = "SELECT order_detail_count_file_seqno FROM " . _TBL_ORDER_DETAIL_COUNT_FILE . " ";
			  $this->sql .= "WHERE order_detail_file_num = '" .$ON[$i]. "' AND state = '".$state."'";
			  $rs = $conn->Execute($this->sql);

			  if ($rs && !$rs->EOF) {
				  $chkCount++;
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

		 return $nRes;
	 }

 }
?>
