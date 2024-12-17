<?
 /***********************************************************************************
 *** 프로젝트 : CyPress
 *** 개발영역 : Order Module
 *** 개 발 자 : 김성진
 *** 개발날짜 : 2016.06.16
 ***********************************************************************************/

 class CLS_Order {

	 var $sql;
	 var $rowsData;
	 var $idx;
	 var $order;
	 var $code;
	 var $curtime;
	 var $remoteip;


	 /***********************************************************************************
	 *** 초기화 ***
	 ***********************************************************************************/

	 function Init() {
		 $this->sql		    = null;
		 $this->rowsData 	= null;
		 $this->idx		    = null;
		 $this->order       = null;
		 $this->code        = null;
		 $this->curtime     = time();
		 $this->remoteip    = $_SERVER["REMOTE_ADDR"];
	 }


	 /***********************************************************************************
	 *** 주문 정보 가져오기
	 *** 수량 amt / 수량단위구분 amt_unit_dvs / 건수 count
	 ***********************************************************************************/

	 function getOrderInfoDataValue($conn, $OrderNum) {
		$this->Init();
		$this->order = $OrderNum;

		$this->sql  = "\nSELECT oc.order_common_seqno, oc.order_state, oc.oper_sys, oc.dlvr_produce_dvs ";
		$this->sql .= "\n,oc.order_regi_date, oc.member_seqno, oc.title, oc.page_cnt ";
		$this->sql .= "\n,od.work_size_wid, od.work_size_vert, od.cut_size_wid ";
		$this->sql .= "\n,od.cut_size_vert, od.pay_price, od.order_detail ";
		$this->sql .= "\n,od.amt, od.amt_unit_dvs, od.cate_sortcode, od.side_dvs ";
		$this->sql .= "\n,od.tot_tmpt, od.receipt_mng, od.receipt_memo, od.produce_memo ";
		$this->sql .= "\n,od.receipt_finish_date, od.tomson_yn, od.stan_name, odcf.barcode_num ";
		 $this->sql.= "\n , concat(A.name, \" \" , A.color, \" \", A.basisweight) as paper_name, A.mpcode ";
		 $this->sql.= "\n , A.name as only_paper_name ";
		$this->sql .= "\n  FROM "._TBL_ORDER." AS oc, "._TBL_ORDER_DETAIL." AS od, "._TBL_ORDER_DETAIL_COUNT_FILE." AS odcf, cate_paper AS A ";
		$this->sql .= "\n WHERE oc.order_common_seqno = od.order_common_seqno AND od.order_detail_seqno = odcf.order_detail_seqno AND A.mpcode = od.cate_paper_mpcode ";
		//$this->sql .=\n "AND odcf.order_detail_file_num = '".$this->order."' AND odcf.state = '"._CYP_STS_CD_READY."' LIMIT 1";

		 if(strpos($this->order, "SGPT") !== false) {
			 $this->sql .= "\n   AND odcf.order_detail_file_num = '" . $this->order . "' LIMIT 1";
		 } else {
			 $this->sql .= "\n   AND odcf.barcode_num = '" . $this->order . "' LIMIT 1";
		 }


		 $CCFile = new CLS_File;
		 $CCFile->FileWrite(_WPATH, "\n".date("H:i:s")." PRINT QUERY -> ".$this->sql."\n", "a+");
		$rs = $conn->Execute($this->sql);

		 $nRes = array();
		if ($rs && !$rs->EOF) {
			// 주문 공통
			$nRes['barcode_num'] = $rs->fields['barcode_num'];                   	                     	 // 순서 *
			$nRes['oc_idx'] = $rs->fields['order_common_seqno'];                   	                     	 // 순서 *
			$nRes['oc_ord_state'] = $rs->fields['order_state'];              	                           	 // 상태 *
			$nRes['oc_oper_sys'] = $rs->fields['oper_sys'];              	                               	 // OS *
			$nRes['oc_ord_regi_date'] = $rs->fields['order_regi_date'];        	                         	 // 주문등록일자 *
			$nRes['oc_mb_idx'] = $rs->fields['member_seqno'];                                                // 회원 일련번호 *
			$nRes['oc_title'] = trim($rs->fields['title']);                       	                     	 // 제목 *
			$nRes['oc_page_cnt'] = trim($rs->fields['page_cnt']);                  	                     	 // 수량(단위 장) *
			$nRes['oc_produce_memo'] = $rs->fields['produce_memo'];                                          // 생산메모 *
			$nRes['oc_dlvr_produce_dvs'] = $rs->fields['dlvr_produce_dvs'];                                  // 배송판구분 *

			//주문 상세
			$nRes['od_wk_sz_width'] = $rs->fields['work_size_wid'];              	                     	 // 작업사이즈 가로 *
			$nRes['od_wk_sz_height'] = $rs->fields['work_size_vert'];                                    	 // 작업사이즈 세로 *
			$nRes['od_cut_sz_width'] = $rs->fields['cut_size_wid'];              	                     	 // 재단사이즈 가로 *
			$nRes['od_cut_sz_height'] = $rs->fields['cut_size_vert'];       		                         // 재단사이즈 세로 *
			$nRes['od_pay_price'] = $rs->fields['pay_price'];                                                // 결제금액 *
			$nRes['od_ord_detail'] = $rs->fields['order_detail'];                                            // 주무상세 *
			$nRes['od_amt'] = $rs->fields['amt'];                                  	                     	 // 수량 *
			$nRes['od_amt_unit_dvs'] = $rs->fields['amt_unit_dvs'];           	                          	 // 수량 단위구분 *
			$nRes['od_cat_scode'] = $rs->fields['cate_sortcode'];                                            // 카테고리 분류코드 *
			$nRes['od_tot_tmpt'] = $rs->fields['tot_tmpt'];                                                  // 전체 도수 *
			$nRes['od_rec_mng'] = $rs->fields['receipt_mng'];                                                // 접수 담당자 *
			$nRes['od_rec_memo'] = $rs->fields['receipt_memo'];                                              // 접수메모 *
			$nRes['od_rec_fns_date'] = $rs->fields['receipt_finish_date'];                                   // 접수완료일자 *
			$nRes['od_tomson_yn'] = $rs->fields['tomson_yn'];                                                // 도무송 여부 *
			$nRes['od_stan_name'] = $rs->fields['stan_name'];                                                // 규격이름 *
			$nRes['od_side_dvs'] = $rs->fields['side_dvs'];                                                  // 면구분 *
			$nRes['od_coating'] = ($this->isExistAPInfo($conn, $nRes['oc_idx'], "코팅") == true) ? "코팅" : "무코팅";

			$nRes['oc_state_name'] = $this->getLocalStatDataValue($conn, $nRes['oc_ord_state']);             // 상태이름 *

			// 제품 및 코드값
			$nRes['od_big_code'] = substr($nRes['od_cat_scode'], 0, 3);
			$nRes['od_prdt_name'] = $this->getLocalCateProductNameValue($conn, $nRes['od_big_code']);        // 제품명칭 *
			$nRes['od_prdt_code'] = $nRes['od_big_code'];                                                    // 제품코드값 *

			$nRes['od_mid_code'] = substr($nRes['od_cat_scode'], 0, 6);
			$nRes['od_item_name'] = $this->getLocalCateProductNameValue($conn, $nRes['od_mid_code']);        // 품목명칭 *
			$nRes['od_item_code'] = $nRes['od_mid_code'];                                                    // 품목코드값 *

			$nRes['od_sma_code'] = substr($nRes['od_cat_scode'], 0, 9);
			$nRes['od_kind_name'] = $this->getLocalCateProductNameValue($conn, $nRes['od_sma_code']);        // 종류명칭 *
			$nRes['od_kind_code'] = $nRes['od_sma_code'];                                                    // 종류코드값 *

			// 면수 및 도수
			$nSCData = explode("/", $nRes['od_ord_detail']);
			if($nRes['od_sma_code'] == "004001001" ||
					$nRes['od_sma_code'] == "004002001") {
				if(strpos(trim($nSCData[4]), '원터치') !== false) {
					$nRes['od_touch'] = "원터치";
				} else {
					$nRes['od_touch'] = "투터치";
				}
			}

			$nRes['od_sc_data'] = trim($nSCData[3]);

			$nRes['od_size_name'] = trim($nSCData[2]);                                                        // 사이즈 명칭 *
			$nRes['od_size_code'] = trim($nSCData[2]);                                                        // 사이즈 코드값 *

			$nRes['od_paper_only_name'] = $rs->fields['only_paper_name'];
			$nRes['od_paper_name'] = $rs->fields['paper_name'];                                                       // 지질 명칭 *
			$nRes['od_paper_code'] = $rs->fields['mpcode'];                                                                   	  // 지질 코드값 *

			$nRes['res_after'] = $this->getLocalBasicAfterDataValue($conn, $OrderNum);                        // 기본 후공정

			// 기본 후공정
			if (is_array($nRes['res_after'])) {
				for ($i = 0; $i < count($nRes['res_after']); $i++) {
					//$nRes['bs_after'] .= $nRes['res_after'][$i]['name'] . "<" . $i . ">::";
					$nRes['bs_after'] .= $nRes['res_after'][$i]['name'] . ", ";
				}

				$nRes['bs_after'] = substr($nRes['bs_after'], 0, strlen($nRes['bs_after']) - 2);
			}

			// 배송
			$nRes['delevery'] = $this->getLocalDeleveryDataValue($conn, $nRes['oc_idx']);

			if (is_array($nRes['delevery'])) {
				$nRes['od_dely_cpn_name'] = $nRes['delevery']['cpn_name'];                                    // 택배사 *
				$nRes['od_dely_name'] = $nRes['delevery']['name'];                                            // 수령인 *
				$nRes['od_dely_phone'] = $nRes['delevery']['phone'];                                       	  // 수신전화 *
				$nRes['od_dely_mobile'] = $nRes['delevery']['mobile'];                                        // 수신휴대폰 *
				$nRes['od_dely_addr'] = $nRes['delevery']['addr'];                                            // 배송주소 *
				$nRes['od_dely_dlvr_way'] = $nRes['delevery']['dlvr_way'];                                    // 배송방법 *
				$nRes['od_dely_dlvr_pay_way'] = $nRes['delevery']['dlvr_sum_way'];                                    // 배송방법 *
			}

			// 접수
			if (strpos($nRes['od_rec_mng'], "Auto") === false) {
				$nRes['od_empl_id'] = $this->getLocalReceiptMngNameValue($conn, $nRes['od_rec_mng']);         // 접수아이디 *
				$nRes['od_rec_dvs'] = "M";
			} else {
				$nRes['od_empl_id'] = "Auto";
				$nRes['od_rec_dvs'] = "A";
			}

			$nRes['div_data'] = $this->getLocalDivDataValue($conn, $OrderNum);								  // 접수구분

			if (is_array($nRes['div_data'])) {
				$nRes['div'] = $nRes['div_data']['opt_nick'];

				$clrData = explode("/", $nRes['div_data']['opt_clr_name']);
				$nRes['clr_name'] = $clrData[0];
			} else if ($nRes['div_data'] == "FAILED") {
				$nRes['div'] = _CYP_OPT_NAME_NORM;
			} else {
				$nRes['div'] = _CYP_OPT_NAME_NORM;
			}

			// 고객
			$nRes['member'] = $this->getLocalMemberDataValue($conn, $nRes['oc_mb_idx']);

			$nRes['od_cus_dvs'] = $nRes['member']['mb_dvs'];									              // 회원 구분 *
			$nRes['od_cus_id'] = $nRes['member']['mb_id'];                                                    // 회원id *
			$nRes['od_cus_cp_name'] = $nRes['member']['mb_name'];
			$nRes['od_cus_name'] = $nRes['member']['mb_name'];                                                // 담당자명 *
			$nRes['od_cus_phone'] = $nRes['member']['mb_phone'];                                              // 연락처 *
			$nRes['od_cus_mobile'] = $nRes['member']['mb_mobile'];                                            // 휴대폰 *
			$nRes['od_cus_addr'] = $nRes['member']['mb_addr'];                                                // 주소 *

			if ($nRes['member']['mb_dvs'] == "기업" || $nRes['member']['mb_dvs'] == "기업개인") {
				$nRes['customer'] = $this->getLocalCustomerDataValue($conn, $nRes['oc_mb_idx']);

				if ($nRes['customer'] != "FAILED" && $nRes['customer'] != "ERROR") {
					$nRes['od_cus_cp_name'] = $nRes['customer'];                         			          // 회사명 *
				}
			}
			// 주문상세 건수파일
			$nRes['odcf_ord_dtl_file_num'] = $rs->fields['order_detail_file_num'];                            // 주문상세 파일번호 *
		} else  if ($rs->EOF) {
			$nRes = "FAILED";
		} else {
			$nRes = "ERROR";
		}

		return $nRes;
	 }


	 function isExistAPInfo($conn, $order_common_seqno ,$aft_name) {
		 $this->Init();

		 $this->sql = " SELECT after_name ";
		 $this->sql .= " FROM  " . _TBL_ORDER_AFTER_HISTORY;
		 $this->sql .= " WHERE order_common_seqno = '%s' AND after_name = '%s' ";

		 $this->sql = sprintf($this->sql, $order_common_seqno, $aft_name);
		 CLS_File::FileWrite(_WPATH, "\n".date("H:i:s") . $this->sql . "\n", "a+");
		 $rs = $conn->Execute($this->sql);

		 if ($rs && !$rs->EOF) {
			 return true;
		 } else {
			 return false;
		 }
	 }

	 /***********************************************************************************
	 *** 상태코드로 상태값 가져오기 (Local)
	 ***********************************************************************************/

 	function getLocalStatDataValue($conn, $sCode) {
		$this->Init();
		$this->code = $sCode;

		$this->sql = "SELECT erp_state_name FROM "._TBL_STATE_ADMIN." ";
		$this->sql .= "WHERE state_code = '".$this->code."' LIMIT 1";
		$rs = $conn->Execute($this->sql);

		if ($rs && !$rs->EOF) {
			$nRes = $rs->fields['erp_state_name'];
		} else if ($rs->EOF) {
			$nRes = "FAILED";
		} else {
			$nRes = "ERROR";
		}

		return $nRes;
	}


	 /***********************************************************************************
	 *** 카테고리 상품명 가져오기 (Local)
	 ***********************************************************************************/

	 function getLocalCateProductNameValue($conn, $sCode) {
		$this->Init();
		$this->code = $sCode;

		$this->sql = "SELECT cate_name FROM "._TBL_CATE." ";
		$this->sql .= "WHERE sortcode = '".$this->code."' LIMIT 1";
		$rs = $conn->Execute($this->sql);

		if ($rs && !$rs->EOF) {
			 $nRes = $rs->fields['cate_name'];
		 } else if ($rs->EOF) {
			 $nRes = "FAILED";
		 } else {
			 $nRes = "ERROR";
		 }

		return $nRes;
	 }


	 /***********************************************************************************
	 *** 도수 정보 가져오기 (Local)
	 ***********************************************************************************/

	 function getLocalColorDataValue($dbc, $sCode) {
		$this->Init();
		$this->code = $sCode;

		$this->sql = "SELECT pp.prdt_print_seqno AS idx, pp.side_dvs AS side_name, pp.name AS color_name, pp.tot_tmpt AS color_count ";
		$this->sql .= "FROM "._TBL_CATE_PRINT." AS cp, "._TBL_PRDT_PRINT." AS pp ";
		//echo $this->sql .= "WHERE cp.prdt_print_seqno = pp.prdt_print_seqno AND cp.cate_sortcode = '".$this->code."' LIMIT 1";
		//exit;
		$this->rowsData = new CLS_DBQuery($this->sql, $dbc);

		if (!trim($this->rowsData->error)) {
			if ($this->rowsData->num_rows > 0) {
				$nRes['color_idx'] = $rs->fields['idx'];
				$nRes['side_name'] = $rs->fields['side_name'];
				$nRes['color_name'] = $rs->fields['color_name'];
				$nRes['color_count'] = $rs->fields['color_count'];
			} else {
				$nRes = "FAILED";
			}
		} else {
			$nRes = "ERROR";
		}

		return $nRes;
	 }


	  /***********************************************************************************
	 *** 종이 정보 가져오기 (Local)
	 ***********************************************************************************/

	 function getLocalPaperDataValue($dbc, $sCode) {
		$this->Init();
		$this->code = $sCode;

		$this->sql = "SELECT  pp.prdt_paper_seqno AS idx, pp.name AS name, pp.affil AS affil, pp.basisweight AS bweight, pp.size AS size ";
		$this->sql .= "FROM "._TBL_CATE_PAPER." AS cp, "._TBL_PRDT_PAPER." AS pp ";
		$this->sql .= "WHERE cp.mpcode = pp.mpcode AND cp.cate_sortcode = '".$this->code."' LIMIT 1";
		$this->rowsData = new CLS_DBQuery($this->sql, $dbc);

		if (!trim($this->rowsData->error)) {
			if ($this->rowsData->num_rows > 0) {
				$nRes['paper_idx'] = $rs->fields['idx'];
				$nRes['paper_name'] = $rs->fields['name'];
				$nRes['paper_affil'] = $rs->fields['affil'];
				$nRes['paper_bweight'] = $rs->fields['bweight'];
				$nRes['paper_size'] = $rs->fields['size'];
			} else {
				$nRes = "FAILED";
			}
		} else {
			$nRes = "ERROR";
		}

		return $nRes;
	 }


	 /***********************************************************************************
	 *** 규격 정보 가져오기 (Local)
	 ***********************************************************************************/

	 function getLocalStanDataValue($dbc, $sCode) {
		$this->Init();
		$this->code = $sCode;

		$this->sql = "SELECT ps.work_wid_size AS work_wsize, ps.work_vert_size AS work_vsize, ";
		$this->sql .= "ps.cut_wid_size AS cut_wsize, ps.cut_vert_size AS cut_vsize ";
		$this->sql .= "FROM "._TBL_CATE_STAN." AS cs, "._TBL_PRDT_STAN." AS ps ";
		$this->sql .= "WHERE cs.prdt_stan_seqno = ps.prdt_stan_seqno AND cs.cate_sortcode = '".$this->code."' LIMIT 1";
		$this->rowsData = new CLS_DBQuery($this->sql, $dbc);

		if (!trim($this->rowsData->error)) {
			if ($this->rowsData->num_rows > 0) {
				$nRes['work_wsize'] = $rs->fields['work_wsize'];
				$nRes['work_vsize'] = $rs->fields['work_vsize'];
				$nRes['cut_wsize'] = $rs->fields['cut_wsize'];
				$nRes['cut_vsize'] = $rs->fields['cut_vsize'];
			} else {
				$nRes = "FAILED";
			}
		} else {
			$nRes = "ERROR";
		}

		return $nRes;
	 }


	 /***********************************************************************************
	  *** 주문 상세 정보 가져오기 (Local)
	  ***********************************************************************************/

	 function getLocalOrderDetailDataValue($dbc, $OrderNum) {
		 $this->Init();
		 $this->idx = $nIdx;

		 $this->sql = "SELECT order_detail_seqno, amt, count, amt_unit_dvs FROM "._TBL_ORDER_DETAIL." ";
		 $this->sql .= "WHERE order_common_seqno = '".$this->idx."' LIMIT 1";
		 $this->rowsData = new CLS_DBQuery($this->sql, $dbc);

		 if (!trim($this->rowsData->error)) {
			 if ($this->rowsData->num_rows > 0) {
				 $nRes['od_idx'] = $rs->fields['order_detail_seqno'];
				 $nRes['od_amt'] = $rs->fields['amt'];
				 $nRes['od_count'] = $rs->fields['count'];
				 $nRes['od_amt_unit_dvs'] = $rs->fields['amt_unit_dvs'];
			 } else {
				 $nRes = "FAILED";
			 }
		 } else {
			 $nRes = "ERROR";
		 }

		 return $nRes;
	 }


	 /***********************************************************************************
	  *** 기본 후공정 정보 가져오기 (Local) -> 후공정 전체 가져오기
	  ***********************************************************************************/

	 function getLocalBasicAfterDataValue($conn, $OrderNum) {
		 $this->Init();
		 $CCFile = new CLS_File;
		 $i = 0;
		 //$OrderNum = substr($OrderNum, 0, strlen($OrderNum) - 2);

		 $this->sql = "select * from order_after_history AS A ";
		 $this->sql .= "INNER JOIN order_detail AS B ON A.order_common_seqno = B.order_common_seqno ";
		 $this->sql .= "INNER JOIN order_detail_count_file AS C ON B.order_detail_seqno = C.order_detail_seqno ";
		 $this->sql .= "where C.barcode_num = '".$OrderNum."' AND basic_yn = 'N'";
		 //$this->sql .= "WHERE order_detail_dvs_num = '".$OrderNum."'";
		 $rs = $conn->Execute($this->sql);
		 $CCFile->FileWrite(_WPATH, "\n".date("H:i:s")." PRINT QUERY -> ".$this->sql."\n", "a+");
		 if ($rs && !$rs->EOF) {
			 while ($rs && !$rs->EOF) {
				 /*
				 if($rs->fields['after_name'] != "재단" && $rs->fields['after_name'] != "코팅") {
					 $nRes[$i]['name'] = $rs->fields['after_name'];
					 $i++;
				 }*/
				 $nRes[$i]['name'] = $rs->fields['after_name'];
				 $i++;
				 $rs->moveNext();
			 }
		 } else if ($rs->EOF) {
			 $nRes = "FAILED";
		 } else {
			 $nRes = "ERROR";
		 }

		 return $nRes;
	 }


	 /***********************************************************************************
	 *** 배송 정보 가져오기 (Local)
	 ***********************************************************************************/

	 function getLocalDeleveryDataValue($conn, $nIdx) {
		$this->Init();
		$this->idx = $nIdx;

		$this->sql = "SELECT name, tel_num, cell_num, addr, dlvr_way, dlvr_sum_way, dlvr_price, invo_cpn ";
		$this->sql .= "FROM "._TBL_ORDER_DELEVERY." ";
		$this->sql .= "WHERE tsrs_dvs = '수신' AND order_common_seqno = '".$this->idx."' LIMIT 1";
		$rs = $conn->Execute($this->sql);

		 if ($rs && !$rs->EOF) {
			 $nRes['name'] = $rs->fields['name'];
			 $nRes['phone'] = $rs->fields['tel_num'];
			 $nRes['mobile'] = $rs->fields['cell_num'];
			 $nRes['addr'] = $rs->fields['addr'];
			 $nRes['dlvr_way'] = $rs->fields['dlvr_way'];
			 $nRes['dlvr_sum_way'] = $rs->fields['dlvr_sum_way'];
			 $nRes['price'] = $rs->fields['dlvr_price'];
			 $nRes['cpn_name'] = $rs->fields['invo_cpn'];
		 } else if ($rs->EOF) {
			 $nRes = "FAILED";
		 } else {
			 $nRes = "ERROR";
		 }

		return $nRes;
	 }


	 /***********************************************************************************
  *** 접수 정보 가져오기 (Local)
  ***********************************************************************************/

	 function getLocalReceiptDataValue($dbc, $OrderNum) {
		 $this->Init();
		 $this->order = $OrderNum;

		 //$this->sql = "SELECT oc.receipt_dvs AS receipt_dvs, oc.order_regi_date AS order_regi_date, oc.receipt_regi_date AS receipt_regi_date, ";
		 $this->sql = "SELECT oc.receipt_dvs AS receipt_dvs, oc.order_regi_date AS order_regi_date, ";
		 $this->sql .= "mb.id AS member_id, oc.receipt_mng AS receipt_mng ";
		 $this->sql .= "FROM "._TBL_ORDER." AS oc, "._TBL_MEMBERS." AS mb ";
		 $this->sql .= "WHERE oc.member_seqno = mb.member_seqno AND oc.order_num = '".$this->order."' LIMIT 1";
		 $this->rowsData = new CLS_DBQuery($this->sql, $dbc);

		 if (!trim($this->rowsData->error)) {
			 if ($this->rowsData->num_rows > 0) {
				 $nRes['receipt_dvs'] = $rs->fields['receipt_dvs'];
				 $nRes['order_regi_date'] = $rs->fields['order_regi_date'];
				 //$nRes['receipt_regi_date'] = $rs->fields['receipt_regi_date'];
				 $nRes['member_id'] = $rs->fields['member_id'];
				 $nRes['receipt_mng'] = $rs->fields['receipt_mng'];
			 } else {
				 $nRes = "FAILED";
			 }
		 } else {
			 $nRes = "ERROR";
		 }

		 return $nRes;
	 }


	 /***********************************************************************************
	  *** 접수자 아이디 가져오기 (Local)
	  ***********************************************************************************/

	 function getLocalReceiptMngNameValue($conn, $name) {
		 $this->Init();

		 $this->sql = "SELECT empl_id FROM "._TBL_EMPL." ";
		 $this->sql .= "WHERE name = '".$name."' AND resign_yn = 'N' LIMIT 1";
		 $rs = $conn->Execute($this->sql);

		 if ($rs && !$rs->EOF) {
			 $nRes = $rs->fields['empl_id'];
		 } else if ($rs->EOF) {
			 $nRes = "FAILED";
		 } else {
			 $nRes = "ERROR";
		 }

		 return $nRes;
	 }


	 /***********************************************************************************
	 ***  회원 정보 가져오기 (Local)
	 ***********************************************************************************/

	 function getLocalMemberDataValue($conn, $mb_idx) {
		$this->Init();

		$this->sql = "SELECT id as member_id, member_name ";
		$this->sql .= "FROM  "._TBL_MEMBERS." ";
		$this->sql .= "WHERE member_seqno = '".$mb_idx."' LIMIT 1";
		$rs = $conn->Execute($this->sql);

		 if ($rs && !$rs->EOF) {
			 //$nRes['mb_dvs'] = $rs->fields['member_dvs'];
			 $nRes['mb_id'] = $rs->fields['member_id'];
			 $nRes['mb_name'] = $rs->fields['member_name'];
			 //$nRes['mb_name'] = $rs->fields['office_nick'];
			 $nRes['mb_phone'] = "";
			 $nRes['mb_mobile'] = "";
			 $nRes['mb_addr'] = "";
		 } else if ($rs->EOF) {
			 $nRes = "FAILED";
		 } else {
			 $nRes = "ERROR";
		 }

		return $nRes;
	 }


	 /***********************************************************************************
	  *** 고객 정보 가져오기 (Local)
	  ***********************************************************************************/

	 function getLocalCustomerDataValue($conn, $mb_idx) {
		 $this->Init();

		 $this->sql = "SELECT li.corp_name AS corp_name ";
		 $this->sql .= "FROM  "._TBL_MEMBERS." AS mb, "._TBL_LICENSE_INFO." AS li ";
		 $this->sql .= "WHERE mb.member_seqno = li.member_seqno AND mb.member_seqno = '".$mb_idx."' LIMIT 1";
		 $rs = $conn->Execute($this->sql);

		 if ($rs && !$rs->EOF) {
			 $nRes = $rs->fields['corp_name'];
		 } else if ($rs->EOF) {
			 $nRes = "FAILED";
		 } else {
			 $nRes = "ERROR";
		 }

		 return $nRes;
	 }


	 /***********************************************************************************
	  *** 고객 정보 가져오기 (Local)
	  ***********************************************************************************/

	 function getLocalCustomerDataValue2($dbc, $OrderNum) {
		 $this->Init();
		 $this->order = $OrderNum;

		 $this->sql = "SELECT mb.id AS mb_id, li.corp_name AS cpn_name, mb.member_name AS mb_name, ";
		 $this->sql .= "FROM  "._TBL_MEMBERS." AS mb, "._TBL_LICENSE_INFO." AS li, "._TBL_ORDER." AS oc ";
		 $this->sql .= "WHERE mb.member_seqno = li.member_seqno AND mb.member_seqno = oc.member_seqno ";
		 $this->sql .= "AND oc.order_num = '".$this->order."' LIMIT 1";
		 $this->rowsData = new CLS_DBQuery($this->sql, $dbc);

		 if (!trim($this->rowsData->error)) {
			 if ($this->rowsData->num_rows > 0) {
				 //$nRes['mb_dvs'] = $rs->fields['mb_dvs'];
				 $nRes['mb_id'] = $rs->fields['mb_id'];
				 $nRes['cpn_name'] = $rs->fields['cpn_name'];
				 $nRes['mb_name'] = $rs->fields['mb_name'];
				 //$nRes['mb_phone'] = $rs->fields['mb_phone'];
				 //$nRes['mb_mobile'] = $rs->fields['mb_mobile'];
				 //$nRes['mb_addr'] = $rs->fields['mb_addr'];
			 } else {
				 $nRes = "FAILED";
			 }
		 } else {
			 $nRes = "ERROR";
		 }

		 return $nRes;
	 }


	 /***********************************************************************************
	 *** 주문취소 체크
	 ***********************************************************************************/

	 function getOrderCancelDataCheck($conn, $rData) {
		$this->Init();

		$this->sql = "SELECT order_common_seqno FROM "._TBL_ORDER." ";
		$this->sql .= "WHERE order_num = '".$rData['order_num']."' AND receipt_mng = '".$rData['work_id']."' ";
		$rs = $conn->Execute($this->sql);

		 if ($rs && !$rs->EOF) {
			 $nRes = "SUCCESS";
		 } else if ($rs->EOF) {
			 $nRes = "FAILED";
		 } else {
			 $nRes = "ERROR";
		 }

		return $nRes;
	 }


	 /***********************************************************************************
	 *** 주문취소 업데이트
	 ***********************************************************************************/

	 function setOrderCancelDataUpdateComplete($conn, $rData) {
		$this->Init();

		$this->sql = "UPDATE "._TBL_ORDER." SET del_yn = '1' ";
		$this->sql .= "WHERE order_num = '".$rData['order_num']."' AND receipt_mng = '".$rData['work_id']."' ";
		$rs = $conn->Execute($this->sql);

		 if ($rs == true) {
			 $nRes = "SUCCESS";
		 } else {
			 $nRes = "ERROR";
		 }

		return $nRes;
	 }


	 /***********************************************************************************
	 *** 조판가능질의 체크
	 ***********************************************************************************/

	 function getOrderComposeAvailCheck($conn, $rData) {
		$this->Init();
		$oNRes = "";
		$sucCount = 0;
		$errCount = 0;

		for ($i = 1; $i <= $rData['ONS']; $i++) {
			 $this->sql = "SELECT order_detail_count_file_seqno FROM " . _TBL_ORDER_DETAIL_COUNT_FILE . " ";
			 //$this->sql .= "WHERE order_detail_file_num = '".$rData['ON'.$i]."' AND state = '"._CYP_STS_CD_READY."'";
			 $this->sql .= "WHERE order_detail_file_num = '".$rData['ON'.$i]."'";
			 $rs = $conn->Execute($this->sql);

			 if ($rs && !$rs->EOF) {
				 $sucCount++;
			 } else if ($rs->EOF) {
				 $oNRes .= $rData['ON'.$i].";";
			 } else {
				 $errCount++;
			 }
		}

		if ($errCount > 0) {
			$nRes = "ERROR";
		} else if ($sucCount == $rData['ONS']) {
			$nRes = "SUCCESS";
		} else {
			$nRes = substr($oNRes, 0, strlen($oNRes) - 1);
		}

		return $nRes;
	 }


	 /***********************************************************************************
	  *** 조판가능질의 체크
	  ***********************************************************************************/

	 function getOrderComposeAvailCheck2($conn, $rData) {
		 $this->Init();
		 $oNRes = "";
		 $sucCount = 0;
		 $errCount = 0;

		 for ($i = 1; $i <= $rData['ONS']; $i++) {
			 $this->sql = "SELECT order_detail_count_file_seqno FROM "._TBL_ORDER_DETAIL_COUNT_FILE." ";
			 //$this->sql .= "WHERE order_detail_file_num = '".$rData['ON'.$i]."' AND state >= '"._CYP_STS_CD_GOING."' ";
			 $this->sql .= "WHERE order_detail_file_num = '".$rData['ON'.$i]."'";
			 $rs = $conn->Execute($this->sql);

			 if ($rs && !$rs->EOF) {
				 $sucCount++;
			 } else if ($rs->EOF) {
				 $oNRes .= $rData['ON'.$i].";";
			 } else {
				 $errCount++;
			 }
		 }

		 if ($errCount > 0) {
			 $nRes = "ERROR";
		 } else if ($sucCount == $rData['ONS']) {
			 $nRes = "SUCCESS";
		 } else {
			 $nRes = substr($oNRes, 0, strlen($oNRes) - 1);
		 }

		 return $nRes;
	 }


	 /***********************************************************************************
	  *** 각종 발주 삭제
	  ***********************************************************************************/

	 function setItemStateValueOPTDataDeleteComplete($conn, $OderNum) {
		 $this->Init();

		 $this->sql = "SELECT st.typset_num ";
		 $this->sql .= "FROM order_detail_count_file AS odcf, amt_order_detail_sheet AS aods, sheet_typset AS st ";
		 $this->sql .= "WHERE odcf.order_detail_count_file_seqno = aods.order_detail_count_file_seqno ";
		 $this->sql .= "AND aods.sheet_typset_seqno = st.sheet_typset_seqno AND odcf.order_detail_file_num = '".$OderNum."'";
		 $rs = $conn->Execute($this->sql);

		 $CCFile = new CLS_File;
		 $CCFile->FileWrite(_WPATH, "\n".date("H:i:s")." PLATEITEM DEL QUERY -> ".$this->sql."\n", "a+");

		 if ($rs && !$rs->EOF) {
			 $opRes = $this->setOutPutValueDataDeleteComplete($conn, $rs->fields['typset_num']);                         // 출력 발주 삭제

			 if ($opRes == "SUCCESS") {
				 $ptRes = $this->setPrintValueDataDeleteComplete($conn, $rs->fields['typset_num']);	                     // 인쇄 발주 삭제

				 if ($ptRes == "SUCCESS") {
					 $afRes = $this->setAfterValueDataDeleteComplete($conn, $rs->fields['typset_num']);			         // 후공정 발주 삭제

					 if ($afRes == "SUCCESS") {
						 $tsRes = $this->setTypsetValueDataDeleteComplete($conn, $rs->fields['typset_num']);			 // 판 삭제

						 if ($tsRes == "SUCCESS") {
							 $this->setProduceValueDataDeleteComplete($conn, $rs->fields['typset_num']);		         // 생산지시서 삭제
						 }
					 }
				 }
			 }
		 } else if ($rs->EOF) {
			 $nRes = "FAILED";
		 } else {
			 $nRes = "ERROR";
		 }

		 return $nRes;
	 }


	 /***********************************************************************************
	  *** 출력 발주 삭제
	  ***********************************************************************************/

	 function setOutPutValueDataDeleteComplete($conn, $TypsetNum) {
		 $this->Init();

		 $this->sql = "DELETE FROM "._TBL_OUTPUT_OP." WHERE typset_num = '".$TypsetNum."'";
		 $rs = $conn->Execute($this->sql);

		 $CCFile = new CLS_File;
		 $CCFile->FileWrite(_WPATH, "\n".date("H:i:s")." OUPTUP DEL -> ".$this->sql."\n", "a+");

		 if ($rs == true) {
			 $nRes = "SUCCESS";
		 } else {
			 $nRes = "ERROR";
		 }

		 return $nRes;
	 }


	 /***********************************************************************************
	  *** 인쇄 발주 삭제
	  ***********************************************************************************/

	 function setPrintValueDataDeleteComplete($conn, $TypsetNum) {
		 $this->Init();

		 $this->sql = "DELETE FROM "._TBL_PRINT_OP." WHERE typset_num = '".$TypsetNum."'";
		 $rs = $conn->Execute($this->sql);

		 $CCFile = new CLS_File;
		 $CCFile->FileWrite(_WPATH, "\n".date("H:i:s")." PRINT DEL -> ".$this->sql."\n", "a+");

		 if ($rs == true) {
			 $nRes = "SUCCESS";
		 } else {
			 $nRes = "ERROR";
		 }

		 return $nRes;
	 }


	 /***********************************************************************************
	  *** 후공정 발주 삭제
	  ***********************************************************************************/

	 function setAfterValueDataDeleteComplete($conn, $TypsetNum) {
		 $this->Init();

		 $this->sql = "DELETE FROM "._TBL_BASIC_AFTER_OP." WHERE typset_num = '".$TypsetNum."'";
		 $rs = $conn->Execute($this->sql);

		 $CCFile = new CLS_File;
		 $CCFile->FileWrite(_WPATH, "\n".date("H:i:s")." AFTER DEL -> ".$this->sql."\n", "a+");

		 if ($rs == true) {
			 $nRes = "SUCCESS";
		 } else {
			 $nRes = "ERROR";
		 }

		 return $nRes;
	 }


	 /***********************************************************************************
	  *** 판형 삭제
	  ***********************************************************************************/

	 function setTypsetValueDataDeleteComplete($conn, $TypsetNum) {
		 $this->Init();

		 $conn->Execute("SET foreign_key_checks = 0");

		 $this->sql = "DELETE FROM "._TBL_SHEET_TYPESET." WHERE typset_num = '".$TypsetNum."'";
		 $rs = $conn->Execute($this->sql);

		 $CCFile = new CLS_File;
		 $CCFile->FileWrite(_WPATH, "\n".date("H:i:s")." TYPSET DEL -> ".$this->sql."\n", "a+");

		 if ($rs == true) {
			 $nRes = "SUCCESS";
		 } else {
			 $nRes = "ERROR";
		 }

		 return $nRes;
	 }


	 /***********************************************************************************
	  *** 생산지시서서 삭제
	 ***********************************************************************************/

	 function setProduceValueDataDeleteComplete($conn, $TypsetNum) {
		 $this->Init();

		 $this->sql = "DELETE FROM "._TBL_PRODUCE_ORD." WHERE typset_num = '".$TypsetNum."'";
		 $rs = $conn->Execute($this->sql);

		 $CCFile = new CLS_File;
		 $CCFile->FileWrite(_WPATH, "\n".date("H:i:s")." PRODUCE DEL -> ".$this->sql."\n", "a+");

		 if ($rs == true) {
			 $nRes = "SUCCESS";
		 } else {
			 $nRes = "ERROR";
		 }

		 return $nRes;
	 }


	 /***********************************************************************************
	 *** 판번호 PEN 체크1
	 ***********************************************************************************/

	 function getPlateEnrolPenCheck($conn, $rData) {
		$this->Init();

		$this->sql = "SELECT sheet_typset_seqno, typset_num FROM "._TBL_SHEET_TYPESET." ";
		$this->sql .= "WHERE typset_num = '".$rData['pen']."' LIMIT 1";
		$rs = $conn->Execute($this->sql);

		 $CCFile = new CLS_File;
		 $CCFile->FileWrite(_WPATH, "\n".date("H:i:s")." PEN QUERY-> ".$this->sql."\n", "a+");

		if ($rs && !$rs->EOF) {
			$nRes['st_idx'] = $rs->fields['sheet_typset_seqno'];
			$nRes['typset_num'] = $rs->fields['typset_num'];
		} else if ($rs->EOF) {
			$nRes = "SUCCESS";
		} else {
			$nRes = "ERROR";
		}

		return $nRes;
	 }


	 /***********************************************************************************
	  *** 조판번호로 기존데이터 삭제처리
	  ***********************************************************************************/

	 function setPlateEnrolPenDataDeleteComplete($conn, $nIdx) {
		 $this->Init();

		 $conn->Execute("SET foreign_key_checks = 0");

		 $this->sql = "DELETE FROM "._TBL_SHEET_TYPESET." WHERE sheet_typset_seqno = '".$nIdx."'";
		 $rs = $conn->Execute($this->sql);

		 $CCFile = new CLS_File;
		 $CCFile->FileWrite(_WPATH . "_판등록", "\n".date("H:i:s")." PEN DATA -> ".$this->sql."\n", "a+");

		 if ($rs == true) {
			 $nRes = "SUCCESS";
		 } else {
			 $nRes = "ERROR";
		 }

		 return $nRes;
	 }


	 /***********************************************************************************
	  *** 조판번호로 기존데이터 파일 삭제처리
	  ***********************************************************************************/

	 function setPlateEnrolPenFileDataDeleteComplete($conn, $nIdx) {
		 $this->Init();

		 $this->sql = "DELETE FROM "._TBL_SHEET_TYPESET_FILE." WHERE sheet_typset_seqno = '".$nIdx."'";
		 $rs = $conn->Execute($this->sql);

		 if ($rs == true) {
			 $nRes = "SUCCESS";
		 } else {
			 $nRes = "ERROR";
		 }

		 return $nRes;
	 }


	 /***********************************************************************************
	  *** 조판번호로 기존데이터 파일 삭제처리
	  ***********************************************************************************/

	 function setPlateEnrolPenPreviewFileDataDeleteComplete($conn, $nIdx) {
		 $this->Init();

		 $this->sql = "DELETE FROM "._TBL_SHEET_TYPESET_PREVIEW_FILE." WHERE sheet_typset_seqno = '".$nIdx."'";
		 $rs = $conn->Execute($this->sql);

		 if ($rs == true) {
			 $nRes = "SUCCESS";
		 } else {
			 $nRes = "ERROR";
		 }

		 return $nRes;
	 }


	 /***********************************************************************************
	  *** 플로우 데이터 삭제처리
	  ***********************************************************************************/

	 function setPlateEnrolProcessFlowDataDeleteComplete($conn, $tsNum) {
		 $this->Init();

		 $this->sql = "DELETE FROM "._TBL_PRODUCE_PROCESS_FLOW." WHERE typset_num = '".$tsNum."'";
		 $rs = $conn->Execute($this->sql);

		 $CCFile = new CLS_File;
		 $CCFile->FileWrite(_WPATH, "\n".date("H:i:s")." PROCESS FLOW -> ".$this->sql."\n", "a+");

		 if ($rs == true) {
			 $nRes = "SUCCESS";
		 } else {
			 $nRes = "ERROR";
		 }

		 return $nRes;
	 }


	 /***********************************************************************************
	 *** 호수판번호 PENO 체크
	 ***********************************************************************************/

	 function getPlateEnrolPenoCheck($_DBS, $rData, $pfCode) {
		$dbc = $this->DBCon($_DBS);

		$this->Init();

		$this->sql = "SELECT typset_num FROM ";

		if ($pfCode == "S") {
			$this->sql .=  _TBL_SHEET_TYPESET." ";
		} else {
			$this->sql .= _TBL_BROCHURE_TYPESET." ";
		}

		$this->sql .= "WHERE typset_num LIKE '".substr($rData['peno'], 0, strlen($rData['peno']) - 3)."%'";
		$this->rowsData = new CLS_DBQuery($this->sql, $dbc);

		if (!trim($this->rowsData->error)) {
			if ($this->rowsData->num_rows <= 0) {
				$nRes = "SUCCESS";
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
	 *** 조판 등록 완료
	 ***********************************************************************************/

	 function setPlateEnrolPenoITypeSetInsertDataComplete($conn, $rData, $ON, $PC) {
		$CCFile = new CLS_File;
		$logpath = _WPATH . "_판등록";
		$tsRes = $this->getLocalTypeSetDataValue($conn, $rData['prn']);   // 판정보

		if (is_array($tsRes)) {
			$clrRes = $this->getLocalColorDoCountDataValue($conn, $rData['pn'], $PC, $ON[1]);  // 전면도수 후면도수

			if ($clrRes != "ERROR") {
				$alRes = $this->getLocalAfterListDataValue($conn, $tsRes['ts_idx']);  // 후공정리스트

				if ($alRes != "ERROR") {
					$opRes = $this->getLocalOptionListDataValue($conn, $rData['ons'], $ON);  // 옵션 리스트

					if ($opRes != "ERROR") {
						//$ptRes = $this->getLocalPrintAmtUnitDataValue($conn, $ON[1]);  // 인쇄 단위
                        $ptRes = "장";

						if ($ptRes != "ERROR") {
							$emRes = $this->getLocalEmployeeSeqDataValue($conn, $rData['pw']);  // 직원 일련번호

							if ($emRes != "ERROR") {
								$ppRes = $this->getLocalPaperNDCBDataValue($conn, $ON[1]);  // 종이 명/구분/색상/평량

								if (is_array($ppRes)) {
									$divRes = $this->getLocalDivPenDataValue($conn, $rData['ons'], $ON);	// 당일판, 본판
									$CCFile->FileWrite(_WPATH . "_판등록", "\n".date("H:i:s")." 당일판 -> ".$divRes."\n", "a+");

									if ($divRes != "ERROR") {
										$dmdRes = $this->getLocalDivMultiDataValue($conn, $rData['ons'], $ON);	// 특기사항
										$CCFile->FileWrite(_WPATH. "_판등록", "\n".date("H:i:s")." 특기사항 -> ".$dmdRes."\n", "a+");

										if ($dmdRes != "ERROR") {
											$this->Init();
											$today = date("Y-m-d H:i:s");
											$ts_num = substr($rData['peno'], 0, strlen($rData['peno']) - 3);

											if ($rData['pl'] == 2 || $rData['pl'] == 3 || $rData['pl'] == 4) $honggak_yn = "Y";
											else $honggak_yn = "N";

											if ($dmdRes == "FAILED") {
												$spRes = "";
											} else {
												for ($i = 0; $i < count($dmdRes); $i++) {
													$spRes .= $dmdRes[$i]." ";
												}
											}

											$this->sql = "INSERT INTO " . _TBL_SHEET_TYPESET . " (";
											$this->sql .= "typset_num, state, beforeside_tmpt, beforeside_spc_tmpt, aftside_tmpt, aftside_spc_tmpt, ";
											$this->sql .= "honggak_yn, after_list, opt_list, print_amt, print_amt_unit, prdt_page, ";
											$this->sql .= "prdt_page_dvs, dlvrboard, memo, op_typ, op_typ_detail, empl_seqno, typset_format_seqno, ";
											$this->sql .= "paper_name, paper_dvs, paper_color, paper_basisweight, print_title, regi_date , cate_sortcode, ";
											$this->sql .= "oper_sys, save_path, save_yn, specialty_items, typset_way";
											$this->sql .= ") VALUE (";
											$this->sql .= "'" . $ts_num . "', '" . _CYP_STS_CD_OUTPUT . "', '" . $clrRes['bf_do'] . "', '0', '" . $clrRes['bc_do'] . "', '0', ";
											$this->sql .= "'" . $honggak_yn . "', '" . $alRes . "', '" . $opRes . "', ";
											$this->sql .= "'" . $rData['pq'] . "', '" . $ptRes . "', '" . $rData['pn'] . "', '" . _CYP_TYPESET_PAGE_DIV1 . "', ";
											$this->sql .= "'" . $divRes . "', '" . $tsRes['ts_dscr'] . "', '" . _CYP_TYPESET_AUTO_CONTRACT . "', ";
											$this->sql .= "'" . _CYP_TYPESET_AUTO_CREATE . "', '" . $emRes . "', '" . $tsRes['ts_idx'] . "', '" . $ppRes['pp_name'] . "', ";
											$this->sql .= "'" . $ppRes['pp_dvs'] . "', '" . $ppRes['pp_color'] . "', '" . $ppRes['pp_bw'] . "', ";
											$this->sql .= "'" . $tsRes['ts_name'] . "', '" . $today . "', '" . $tsRes['ts_cate_sortcode'] . "', ";
											$this->sql .= "'" . $clrRes['bc_op_sys'] . "', '" . $rData['files'] . "', 'N', '".$spRes."', 'CYPRESS'";
											$this->sql .= ")";

											$CCFile->FileWrite($logpath,$this->sql, "a+");

											$rs = $conn->Execute($this->sql);

											$CCFile->FileWrite(_WPATH . "_판등록", "\n" . date("H:i:s") . " 낱장 -> " . $this->sql . "\n", "a+");

											if ($rs == true) {
												$nRes = $this->thisLocalProcessFlowInsertComplete($conn, $ts_num);
											} else {
												$nRes = "ERROR";
											}

											$CCFile->FileWrite(_WPATH . "_판등록", "\n" . date("H:i:s") . " 낱장결과 -> " . $nRes . "\n", "a+");
										} else {
											$nRes = $dmdRes;
										}
									} else if ($nRes['div_data'] == "FAILED") {
										$nRes = $divRes;
									}

								} else {
									$nRes = $ppRes;
								}

							} else {
								$nRes = $emRes;
							}

						} else {
							$nRes = $ptRes;
						}

					} else {
						$nRes = $opRes;
					}

				} else {
					$nRes = $alRes;
				}

			} else {
				$nRes = $clrRes;
			}

		} else {
			$nRes = $tsRes;
		}

		return $nRes;
	 }


	 /***********************************************************************************
	  *** 출력 등록 완료 1
	  ***********************************************************************************/

	 function setPlateEnrolPenoIOutPutOPInsertDataComplete($conn, $rData, $ON, $PC) {
		 $tsRes = $this->getLocalTypeSetDataValue($conn, $rData['prn']);   // 판정보

		 if (is_array($tsRes)) {
			 $clrRes = $this->getLocalColorDoCountDataValue($conn, $rData['pn'], $PC, $ON[1]);  // 전면도수 후면도수

			 if (is_array($clrRes)) {
				 $totalTmpt = $clrRes['bf_do'] + $clrRes['bc_do'];
				 $opRes = $this->getLocalOutPutOPDataValue($conn, $tsRes['ts_idx']);   // 출력정보

				 if (is_array($opRes)) {
					 $this->Init();
					 $today = date("Y-m-d H:i:s");
					 $ts_num = substr($rData['peno'], 0, strlen($rData['peno']) - 3);

                     $this->sql = "DELETE FROM " . _TBL_OUTPUT_OP . " WHERE typset_num = '" . $ts_num . "'";
					 $rs = $conn->Execute($this->sql);

					 $this->sql = "INSERT INTO " . _TBL_OUTPUT_OP . " (";
					 $this->sql .= "typset_num, name, affil, subpaper, size, amt, amt_unit, memo, board, typ, typ_detail, ";
					 $this->sql .= "regi_date, flattyp_dvs, state, orderer, extnl_brand_seqno";
					 $this->sql .= ") VALUE (";
					 $this->sql .= "'" . $ts_num . "', '" . $opRes['op_name'] . "', '" . $opRes['op_affil'] . "','" . $tsRes['ts_subpaper'] . "', '" . $opRes['op_size'] . "', ";
					 $this->sql .= "'" . $totalTmpt . "', '" . $opRes['op_unit'] . "', '" . $rData['pm'] . "', '" . $opRes['op_board'] . "', ";
					 $this->sql .= "'" . _CYP_TYPESET_AUTO_CONTRACT . "', '" . _CYP_TYPESET_AUTO_CREATE . "', '" . $today . "', 'Y', ";
					 $this->sql .= "'" . _CYP_STS_CD_OUTPUT . "', '" . $rData['pw'] . "', '" . $opRes['op_eb_idx'] . "'";
					 $this->sql .= ")";
					 $rs = $conn->Execute($this->sql);

					 $CCFile = new CLS_File;
					 $CCFile->FileWrite(_WPATH, "\n".date("H:i:s")." 출력 쿼리 -> ".$this->sql."\n", "a+");

					 if ($rs == true) {
						 $nRes = $this->thisLocalProcessFlowOutputUpdateComplete($conn, "Y", $ts_num);
					 } else {
						 $nRes = "ERROR";
					 }
				 } else if ($opRes == "FAILED") {
					 $nRes = $this->thisLocalProcessFlowOutputUpdateComplete($conn, "N", $ts_num);
				 } else {
					 $nRes = $opRes;
				 }
			 }

		 } else {
			 $nRes = $tsRes;
		 }

		 return $nRes;
	 }


	 /***********************************************************************************
	  *** 인쇄 등록 완료
	  ***********************************************************************************/

	 function setPlateEnrolPenoIPrintOPInsertDataComplete($conn, $rData, $ON, $PC) {
		 $tsRes = $this->getLocalTypeSetDataValue($conn, $rData['prn']);   // 판정보

		 if (is_array($tsRes)) {
			 $clrRes = $this->getLocalColorDoCountDataValue($conn, $rData['pn'], $PC, $ON[1]);  // 전면도수 후면도수

			 if (is_array($clrRes)) {
				 $totalTmpt = $clrRes['bf_do'] + $clrRes['bc_do'];
				 $prtRes = $this->getLocalPrintOPDataValue($conn, $tsRes['ts_idx']);   // 인쇄정보

				 if (is_array($prtRes)) {
					 $this->Init();
					 $today = date("Y-m-d H:i:s");
					 $ts_num = substr($rData['peno'], 0, strlen($rData['peno']) - 3);

                     $this->sql = "DELETE FROM " . _TBL_PRINT_OP . " WHERE typset_num = '" . $ts_num . "'";
					 $rs = $conn->Execute($this->sql);

					 $this->sql = "INSERT INTO " . _TBL_PRINT_OP . " (";
					 $this->sql .= "typset_num, beforeside_tmpt, beforeside_spc_tmpt, aftside_tmpt, aftside_spc_tmpt, tot_tmpt, ";
					 $this->sql .= "amt, amt_unit, name, affil, subpaper, size, memo, typ, flattyp_dvs, typ_detail, regi_date, state, orderer, extnl_brand_seqno";
					 $this->sql .= ") VALUE (";
					 //$this->sql .= "'".$ts_num."', '".$clrRes['bf_do']."', '0', '".$clrRes['bc_do']."', '0', '".$totalTmpt."', '" . $rData['pq']."', '" . $prtRes['prt_unit'] . "', ";
					 $this->sql .= "'".$ts_num."', '".$clrRes['bf_do']."', '0', '".$clrRes['bc_do']."', '0', '".$totalTmpt."', '" . $rData['pq']."', '장', ";
					 $this->sql .= "'".$prtRes['prt_name']."', '".$prtRes['prt_affil']."', '".$prtRes['prt_subpaper']. "', '".$prtRes['prt_size']. "', '".$rData['pm']."', '" . _CYP_TYPESET_AUTO_CONTRACT . "', 'Y', '" . _CYP_TYPESET_AUTO_CREATE . "', ";
					 $this->sql .= "'" . $today . "', '" . _CYP_STS_CD_PRINT . "', '" . $rData['pw'] . "', '" . $prtRes['prt_eb_idx'] . "'";
					 $this->sql .= ")";

					 $CCFile = new CLS_File;
					 $CCFile->FileWrite(_WPATH . "_판등록", "\n".date("H:i:s")." 출력 쿼리 -> ".$this->sql."\n", "a+");

					 $rs = $conn->Execute($this->sql);

					 if ($rs == true) {
						 $nRes = $this->thisLocalProcessFlowPrintUpdateComplete($conn, "Y", $ts_num);
					 } else {
						 $nRes = "ERROR";
					 }
				 } else if ($prtRes == "FAILED") {
					 $nRes = $this->thisLocalProcessFlowPrintUpdateComplete($conn, "N", $ts_num);
				 } else {
					 $nRes = $prtRes;
				 }
			 } else {
				 $nRes = $clrRes;
			 }

		 } else {
			 $nRes = $tsRes;
		 }

		 return $nRes;
	 }


	 /***********************************************************************************
	  *** 후공정 발주 완료
	  ***********************************************************************************/

	 function setPlateEnrolPenoIAfterOPInsertDataComplete($conn, $rData) {
		 $tsRes = $this->getLocalTypeSetDataValue($conn, $rData['prn']);   // 판정보

		 if (is_array($tsRes)) {
			 $aftRes = $this->getLocalAfterOPDataValue($conn, $tsRes['ts_idx']);   // 후공정정보

			 if (is_array($aftRes) || $aftRes == "FAILED") {
				 $this->Init();
				 $today = date("Y-m-d H:i:s");
				 $ts_num = substr($rData['peno'], 0, strlen($rData['peno']) - 3);
				 $sucCount = 0;
				 $errCount = 0;
				 $aftLen = count($aftRes);

                 $this->sql = "DELETE FROM " . _TBL_BASIC_AFTER_OP . " WHERE typset_num = '" . $ts_num . "'";
                 $rs = $conn->Execute($this->sql);

				 for ($i = 0; $i < $aftLen; $i++) {
					 $this->sql = "INSERT INTO " . _TBL_BASIC_AFTER_OP . " (";
					 $this->sql .= "seq, after_name, amt, amt_unit, memo, op_typ, op_typ_detail, regi_date, orderer, ";
					 $this->sql .= "state, depth1, depth2, depth3, extnl_brand_seqno, typset_num, flattyp_dvs";
					 $this->sql .= ") VALUE (";

					 $this->sql .= "'1', ";

					 if ($aftRes != "FAILED") $this->sql .= "'" . $aftRes[$i]['aft_name'] . "', ";
					 else $this->sql .= "NULL, ";

					 if ($aftRes != "FAILED") $this->sql .= "'" . $aftRes[$i]['aft_amt'] . "', ";
					 else $this->sql .= "NULL, ";

					 if ($aftRes != "FAILED") $this->sql .= "'" . $aftRes[$i]['aft_amt_unit'] . "', ";
					 else $this->sql .= "NULL, ";

					 $this->sql .= "NULL, '" . _CYP_TYPESET_AUTO_CONTRACT . "', ";
					 $this->sql .= "'" . _CYP_TYPESET_AUTO_CREATE . "', '" . $today . "', '" . $rData['pw'] . "', '" . _CYP_STS_CD_AFTER . "', ";

					 if ($aftRes != "FAILED") $this->sql .= "'" . $aftRes[$i]['aft_depth1'] . "', ";
					 else $this->sql .= "NULL, ";

					 if ($aftRes != "FAILED") $this->sql .= "'" . $aftRes[$i]['aft_depth2'] . "', ";
					 else $this->sql .= "NULL, ";

					 if ($aftRes != "FAILED") $this->sql .= "'" . $aftRes[$i]['aft_depth3'] . "', ";
					 else $this->sql .= "NULL, ";

					 if ($aftRes != "FAILED") $this->sql .= "'" . $aftRes[$i]['aff_eb_idx'] . "', ";
					 else $this->sql .= "NULL, ";

					 $this->sql .= "'" . $ts_num . "', 'Y'";
					 $this->sql .= ")";

					 $CCFile = new CLS_File;
					 $CCFile->FileWrite(_WPATH . "_판등록", "\n".date("H:i:s")." 출력 쿼리 -> ".$this->sql."\n", "a+");

					 $rs = $conn->Execute($this->sql);

					 if ($rs == true) {
						 $sucCount++;
					 } else {
						 $errCount++;
					 }
				 }

				 if ($aftLen == $sucCount) {
					 $nRes = "SUCCESS";
				 } else {
					 $nRes = "ERROR";
				 }
			 } else {
				 $nRes = $prtRes;
			 }

		 } else {
			 $nRes = $tsRes;
		 }

		 return $nRes;
	 }


	 /***********************************************************************************
	  *** 종이 등록 완료
	  ***********************************************************************************/

	 function setPlateEnrolPenoIPaperOPInsertDataComplete($conn, $rData) {

		 $tsRes = $this->getLocalTypeSetDataValue($conn, $rData['prn']);   // 판정보

		 if (is_array($tsRes)) {
			 $ppRes = $this->getLocalPaperOPDataValue($conn, $tsRes['ts_idx']);   // 종이정보

			 if ($ppRes != "ERROR") {
                 $today = date("Y-m-d");

                 $query  = "\n   SELECT op_num";
                 $query .= "\n     FROM paper_op";
                 $query .= "\n    WHERE '%s 00:00:00' <= regi_date";
                 $query .= "\n      AND regi_date <= '%s 23:59:59'";
                 $query .= "\n ORDER BY paper_op_seqno DESC";
                 $query .= "\n    LIMIT 1";

                 $query  = sprintf($query, $today, $today);

                 $Oprs = $conn->Execute($query);

                 if ($Oprs->EOF) {
                     $last_num = 1;
                 } else {
                     $last_num = intval(substr($Oprs->fields["op_num"], 6)) + 1;
                 }
                 $last_num = str_pad($last_num, 3, '0', STR_PAD_LEFT);

                 $op_num = date("ymd") . $last_num;

				 $today = date("Y-m-d H:i:s");
				 $ts_num = substr( $rData['peno'], 0, strlen( $rData['peno']) - 3);

                 $query .= "\n SELECT paper_yn";
                 $query .= "\n   FROM produce_process_flow"; 
                 $query .= "\n  WHERE typset_num = " . $ts_num;

                 $paperYn = $conn->Execute($query)->fields["paper_yn"];

                 if ($paperYn == "N") {
			         $nRes = "SUCCESS";
                     $CCFile->FileWrite(_WPATH, "\n종이발주 없음\n", "a+");
                 } else {

                     $this->Init();
                     $this->sql = "INSERT INTO paper_op (";
                     $this->sql .= "op_num, typset_num, name, grain, amt, amt_unit, memo, typ, typ_detail, flattyp_dvs, ";
                     $this->sql .= "regi_date, extnl_brand_seqno, dvs, color, basisweight, storplace, state, op_affil, op_size, stor_size, stor_subpaper";
                     $this->sql .= ") VALUE (";
                     $this->sql .= "'".$op_num."', '".$ts_num."', '".$ppRes['pp_name']."', '".$ppRes['pp_grain']."', '".$rData['pq']."', ";
                     $this->sql .= "'장', '', '"._CYP_TYPESET_AUTO_CONTRACT."', '"._CYP_TYPESET_AUTO_CREATE."', ";
                     $this->sql .= "'Y', '".$today."', '".$ppRes['pp_eb_idx']."', ";
                     $this->sql .= "'".$ppRes['pp_dvs']."', '".$ppRes['pp_color']."', '".$ppRes['pp_bw']."', '".$ppRes['pp_ex_idx']."'";
                     $this->sql .= ", '8120', '".$ppRes['pp_affil']."', '".$ppRes['pp_size']."', '".$ppRes['pp_size']."', '전')";
                     $rs = $conn->Execute($this->sql);

                     $CCFile = new CLS_File;
                     $CCFile->FileWrite(_WPATH, "\n".date("H:i:s")." 종이 쿼리 -> ".$this->sql."\n", "a+");

                     if ($rs == true) {
                         $nRes = $this->thisLocalProcessFlowPaperUpdateComplete($conn, $ts_num);
                     } else {
                         $nRes = "ERROR";
                     }
                 }

			 } else {
				 $nRes = $ppRes;
			 }

		 } else {
			 $nRes = $tsRes;
		 }

		 return $nRes;
	 }


	 /***********************************************************************************
	 *** 판형 정보가져오기 (Local)
	 ***********************************************************************************/

	 function getLocalTypeSetDataValue($conn, $panName) {
		 $this->Init();

		 $this->sql = "SELECT typset_format_seqno, affil, subpaper, wid_size, vert_size, dscr, ";
		 $this->sql .= "cate_sortcode, honggak_yn, purp, process_yn, preset_name, preset_cate, format_name, paper ";
		 $this->sql .= "FROM "._TBL_TYPSET_FORMAT." ";
		 $this->sql .= "WHERE preset_name = '".$panName."' LIMIT 1";
		 $rs = $conn->Execute($this->sql);

		 $CCFile = new CLS_File;
		 $CCFile->FileWrite(_WPATH."_판등록", "\n".date("H:i:s")." 조판 -> ".$this->sql."\n", "a+");

		 if ($rs && !$rs->EOF) {
			 $nRes['ts_idx'] = $rs->fields['typset_format_seqno'];
			 $nRes['ts_name'] = $rs->fields['preset_name'];
			 $nRes['ts_affil'] = $rs->fields['affil'];
			 $nRes['ts_subpaper'] = $rs->fields['subpaper'];
			 $nRes['ts_wid_size'] = $rs->fields['wid_size'];
			 $nRes['ts_vert_size'] = $rs->fields['vert_size'];
			 $nRes['ts_dscr'] = $rs->fields['dscr'];
			 $nRes['ts_cate_sortcode'] = $rs->fields['cate_sortcode'];
			 $nRes['ts_honggak_yn'] = $rs->fields['honggak_yn'];
			 $nRes['ts_purp'] = $rs->fields['purp'];
			 $nRes['ts_process_yn'] = $rs->fields['process_yn'];
			 $nRes['ts_frs_name'] = $rs->fields['preset_name'];
			 $nRes['ts_frs_cate'] = $rs->fields['preset_cate'];
			 $nRes['ts_fm_name'] = $rs->fields['format_name'];
			 $nRes['ts_mat'] = $rs->fields['paper'];
		 } else if ($rs->EOF) {
			 $nRes = "FAILED-TS";
		 } else {
			 $nRes = "ERROR-TS";
		 }

		 return $nRes;
	 }


	 /***********************************************************************************
	  *** 전면 후면 도수 가져오기 (Local)
	  ***********************************************************************************/

	 function getLocalColorDoCountDataValue($conn, $pn, $pc, $OrderNum) {
		 $this->Init();
		 $OrderNum = substr(substr($OrderNum, 1, strlen($OrderNum)), 0, strlen($OrderNum) - 5);

		 $this->sql = "SELECT oper_sys FROM "._TBL_ORDER." WHERE order_num = '".$OrderNum."' LIMIT 1";
		 $rs = $conn->Execute($this->sql);

		 $CCFile = new CLS_File;
		 $CCFile->FileWrite(_WPATH . "_판등록", "\n".date("H:i:s")." OS -> ".$this->sql."\n", "a+");

		 if ($rs && !$rs->EOF) {
			 if ($pn == 1) {
				 $nRes['bf_do'] = $pc[1];
				 $nRes['bc_do'] = 0;
			 } else if ($pn == 2) {
				 $nRes['bf_do'] = $pc[1];
				 $nRes['bc_do'] = $pc[2];
			 } else {
				 $nRes['bf_do'] = 0;
				 $nRes['bc_do'] = 0;
			 }

			 $nRes['bc_op_sys'] = $rs->fields['oper_sys'];
		 } else {
			 $nRes = "ERROR";
		 }

		 return $nRes;
	 }


	 function getLocalColorDoCountDataValue2($conn, $ts_idx) {
		 $this->Init();

		 $this->sql = "SELECT pp.beforeside_tmpt, pp.aftside_tmpt, oc.oper_sys ";
		 $this->sql .= "FROM "._TBL_TYPSET_FORMAT." AS tsf, "._TBL_CATE_PRINT." AS cp, "._TBL_PRDT_PRINT." AS pp, "._TBL_ORDER." AS oc ";
		 $this->sql .= "WHERE tsf.cate_sortcode = cp.cate_sortcode AND cp.prdt_print_seqno = pp.prdt_print_seqno ";
		 $this->sql .= "AND tsf.typset_format_seqno = '".$ts_idx."' AND cp.cate_sortcode = oc.cate_sortcode ORDER BY oc.order_common_seqno DESC LIMIT 1";
		 $rs = $conn->Execute($this->sql);

		 $CCFile = new CLS_File;
		 $CCFile->FileWrite(_WPATH, "\n".date("H:i:s")." 전면후면 -> ".$this->sql."\n", "a+");

		 if ($rs && !$rs->EOF) {
			 $nRes['bf_do'] = $rs->fields['beforeside_tmpt'];
			 $nRes['bc_do'] = $rs->fields['aftside_tmpt'];
			 $nRes['bc_op_sys'] = $rs->fields['oper_sys'];
		 } else {
			 $nRes = "ERROR";
		 }

		 return $nRes;

	 }


	 /***********************************************************************************
	  *** 후공정 리스트 가져오기 (Local)
	  ***********************************************************************************/

	 function getLocalAfterListDataValue($conn, $ts_idx) {
		 $this->Init();

		 $this->sql = "SELECT at.name FROM "._TBL_AFTER." AS at, "._TBL_BASIC_PRODUCE_AFTER." AS bpa ";
		 $this->sql .= "WHERE bpa.typset_format_seqno = '".$ts_idx."' ";
		 $this->sql .= "AND bpa.after_seqno = at.after_seqno ORDER BY name DESC";
		 $rs = $conn->Execute($this->sql);

		 $CCFile = new CLS_File;
		 $CCFile->FileWrite(_WPATH . "_판등록", "\n".date("H:i:s")." 후공정 -> ".$this->sql."\n", "a+");

		 if ($rs && !$rs->EOF) {
			 while ($rs && !$rs->EOF) {
				 	 $nRes .= $rs->fields['name'].",";
					 $rs->moveNext();
			 }

			 $nRes = substr($nRes, 0, strlen($nRes) - 1);
		 } else {
			 $nRes = "";
		 }

		 return $nRes;

	 }


	 /***********************************************************************************
	  *** 웁션 리스트 가져오기 (Local)
	  ***********************************************************************************/

	 function getLocalOptionListDataValue($conn, $ONS, $ON) {
		 $this->Init();
		 $nulCount = 0;
		 $errCount = 0;

		 for ($i = 1; $i <= $ONS; $i++) {
			  $OrderNum = substr(substr($ON[$i], 1, strlen($ON[$i])), 0, strlen($ON[$i]) - 4);

			  $this->sql = "SELECT oi.nick FROM "._TBL_ORDER_OPT_HISTORY." AS ooh, "._TBL_OPT_INFO." AS oi, "._TBL_ORDER." AS oc ";
			  $this->sql .= "WHERE ooh.opt_name = oi.opt_name AND ooh.basic_yn = 'N' ";
			  $this->sql .= "AND ooh.order_common_seqno = oc.order_common_seqno AND oc.order_num = '".$OrderNum."' ";
			  $this->sql .= "ORDER BY oi.seq DESC ";
			  $rs = $conn->Execute($this->sql);

			  $CCFile = new CLS_File;
			  $CCFile->FileWrite(_WPATH . "_판등록", "\n".date("H:i:s")." 옵션 -> ".$this->sql."\n", "a+");

			  if ($rs && !$rs->EOF) {
				  while ($rs && !$rs->EOF) {
					  $nRes .= $rs->fields['nick'] . "/";
					  $rs->moveNext();
				  }
			  } else if ($rs->EOF) {
				  $nulCount++;
			  } else {
				  $errCount++;
			  }
		 }

		 if ($errCount > 0) {
			 $nRes = "ERROR";
		 } else {
			 if (strlen($nRes) > 0) $nRes = substr($nRes, 0, strlen($nRes) - 1);
			 else $nRes = "";
		 }

		 return $nRes;

	 }

	 function getLocalOptionListDataValue2($conn, $ts_idx) {
		 $this->Init();

		 $this->sql = "SELECT op.name FROM "._TBL_OPT." AS op, "._TBL_BASIC_PRODUCE_OPT." AS bpo ";
		 $this->sql .= "WHERE bpo.typset_format_seqno = '".$ts_idx."' ";
		 $this->sql .= "AND bpo.opt_seqno = op.opt_seqno ORDER BY NAME DESC";
		 $rs = $conn->Execute($this->sql);

		 $CCFile = new CLS_File;
		 $CCFile->FileWrite(_WPATH, "\n".date("H:i:s")." 옵션 -> ".$this->sql."\n", "a+");

		 if ($rs && !$rs->EOF) {
			 while ($rs && !$rs->EOF) {
				 $nRes .= $rs->fields['name'].",";
				 $rs->moveNext();
			 }

			 $nRes = substr($nRes, 0, strlen($nRes) - 1);
		 } else {
			 $nRes = "ERROR";
		 }

		 return $nRes;

	 }


	 /***********************************************************************************
	  *** 인쇄 단위 가져오기 (Local)1
	  ***********************************************************************************/

	 function getLocalPrintAmtUnitDataValue($conn, $OrderNum) {
		 $this->Init();
		 $OrderNum = substr($OrderNum, 0, strlen($OrderNum) - 2);

		 $this->sql = "SELECT amt_unit_dvs FROM "._TBL_ORDER_DETAIL." WHERE order_detail_dvs_num = '".$OrderNum."' LIMIT 1";
		 $rs = $conn->Execute($this->sql);

		 $CCFile = new CLS_File;
		 $CCFile->FileWrite(_WPATH, "\n".date("H:i:s")." 인쇄단위 -> ".$this->sql."\n", "a+");

		 if ($rs && !$rs->EOF) {
			 $nRes = $rs->fields['amt_unit_dvs'];
		 } else {
			 $nRes = "ERROR";
		 }

		 return $nRes;

	 }


	 /***********************************************************************************
	  *** 직원 정보 가져오기 (Local)
	  ***********************************************************************************/

	 function getLocalEmployeeSeqDataValue($conn, $usr_id) {
		 $this->Init();

		 $this->sql = "SELECT empl_seqno FROM "._TBL_EMPL." WHERE empl_id = '".$usr_id."' LIMIT 1";
		 $rs = $conn->Execute($this->sql);

		 $CCFile = new CLS_File;
		 $CCFile->FileWrite(_WPATH . "_판등록", "\n".date("H:i:s")." 직원 -> ".$this->sql."\n", "a+");

		 if ($rs && !$rs->EOF) {
			 $nRes = $rs->fields['empl_seqno'];
		 } else {
			 $nRes = "ERROR";
		 }

		 return $nRes;

	 }


	 /***********************************************************************************
	  *** 종이 정보 가져오기 (Local)
	  ***********************************************************************************/

	 function getLocalPaperNDCBDataValue($conn, $OrderNum) { //ON1
		 $this->Init();
		 $OrderNum = substr($OrderNum, 0, strlen($OrderNum) - 2);

		 $this->sql = "SELECT cp.name, cp.dvs, cp.color, cp.basisweight ";
		 $this->sql .= "FROM "._TBL_ORDER_DETAIL." AS od, "._TBL_CATE_PAPER." AS cp ";
		 $this->sql .= "WHERE od.cate_paper_mpcode = cp.mpcode AND od.order_detail_dvs_num = '".$OrderNum."' LIMIT 1";
		 $rs = $conn->Execute($this->sql);

		 $CCFile = new CLS_File;
		 $CCFile->FileWrite(_WPATH . "_판등록", "\n".date("H:i:s")." 종이 -> ".$this->sql."\n", "a+");

		 if ($rs && !$rs->EOF) {
			 $nRes['pp_name'] = $rs->fields['name'];
			 $nRes['pp_dvs'] = $rs->fields['dvs'];
			 $nRes['pp_color'] = $rs->fields['color'];
			 $nRes['pp_bw'] = $rs->fields['basisweight'];
		 } else {
			 $nRes = "ERROR";
		 }

		 return $nRes;
	 }


	 /***********************************************************************************
	  *** 종이 정보 가져오기 (Local)
	  ***********************************************************************************/

	 function getLocalPaperNDCBDataValue2($dbc, $ts_idx) {
		 $this->Init();

		 $this->sql = "SELECT pp.name AS name, pp.dvs AS dvs, pp.color AS color, pp.basisweight AS bw ";
		 $this->sql .= "FROM "._TBL_PAPER." AS pp, "._TBL_BASIC_PRODUCE_PAPER." AS bpp ";
		 $this->sql .= "WHERE bpp.typset_format_seqno = '".$ts_idx."' ";
		 $this->sql .= "AND bpp.paper_seqno = pp.paper_seqno LIMIT 1";
		 $this->rowsData = new CLS_DBQuery($this->sql, $dbc);

		 if (!trim($this->rowsData->error)) {
			 $nRes['pp_name'] = $rs->fields['name'];
			 $nRes['pp_dvs'] = $rs->fields['dvs'];
			 $nRes['pp_color'] = $rs->fields['color'];
			 $nRes['pp_bw'] = $rs->fields['bw'];
		 } else {
			 $nRes = "ERROR";
		 }

		 return $nRes;
	 }


	 /***********************************************************************************
	  *** 배송 정보 가져오기 (Local)
	  ***********************************************************************************/

	 function getLocalRepositoryDataValue($dbc, $nIdx) {
		 $this->Init();
		 $this->idx = $nIdx;

		 $this->sql = "SELECT of.file_path AS of_fpash, odcf.file_path AS odcf_fpath, odcf.preview_file_path AS pre_fpath ";
		 $this->sql .= "FROM "._TBL_ORDER." AS oc, "._TBL_ORDER_DETAIL." AS od, "._TBL_ORDER_FILE." AS of, "._TBL_ORDER_DETAIL_COUNT_FILE." AS odcf ";
		 $this->sql .= "WHERE oc.order_common_seqno = od.order_common_seqno AND od.order_detail_seqno = odcf.order_detail_seqno ";
		 $this->sql .= "AND oc.order_common_seqno = of.order_common_seqno AND oc.order_common_seqno = '".$this->idx."' ";
		 $this->sql .= "GROUP BY odcf.file_path";
		 $this->rowsData = new CLS_DBQuery($this->sql, $dbc);

		 if (!trim($this->rowsData->error)) {
			 if ($this->rowsData->num_rows > 0) {
				 $nRes['of_fpash'] = $rs->fields['of_fpash'];
				 $nRes['odcf_fpath'] = $rs->fields['odcf_fpath'];
				 $nRes['pre_fpath'] = $rs->fields['pre_fpath'];
			 } else {
				 $nRes = "FAILED";
			 }
		 } else {
			 $nRes = "ERROR";
		 }

		 return $nRes;
	 }


	 /***********************************************************************************
	  *** 출력정보 가져오기 (Local)
	  ***********************************************************************************/

	 function getLocalOutPutOPDataValue($conn, $ts_idx) {
		 $this->Init();

		 $this->sql  = "\nSELECT op.name, op.affil, op.wid_size, op.vert_size, op.crtr_unit, op.board, op.extnl_brand_seqno ";
		 $this->sql .= "\n  FROM "._TBL_OUTPUT." AS op, "._TBL_BASIC_PRODUCE_OUTPUT." AS bpo";
		 $this->sql .= "\n WHERE bpo.typset_format_seqno = '".$ts_idx."'";
         $this->sql .= "\n   AND bpo.output_seqno = op.output_seqno ";
		 $this->sql .= "\n LIMIT 1";
		 $rs = $conn->Execute($this->sql);

		 $CCFile = new CLS_File;
		 $CCFile->FileWrite(_WPATH, "\n".date("H:i:s")." 출력 쿼리 -> ".$this->sql."\n", "a+");

		 if ($rs && !$rs->EOF) {
			 $nRes['op_name'] = $rs->fields['name'];
			 $nRes['op_affil'] = $rs->fields['affil'];
			 $nRes['op_size'] = $rs->fields['wid_size'] . "*" . $rs->fields['vert_size'];
			 $nRes['op_unit'] = $rs->fields['crtr_unit'];
			 $nRes['op_board'] = $rs->fields['board'];
			 $nRes['op_eb_idx'] = $rs->fields['extnl_brand_seqno'];
		 } else if ($rs->EOF) {
			 $nRes = "FAILED";
		 } else {
			 $nRes = "ERROR";
		 }

		 return $nRes;
	 }


	 /***********************************************************************************
	  *** 인쇄정보 가져오기 (Local)
	  ***********************************************************************************/

	 function getLocalPrintOPDataValue($conn, $ts_idx) {
		 $this->Init();

		 $this->sql = "SELECT prt.crtr_unit, prt.name, prt.affil, prt.wid_size, prt.vert_size, prt.extnl_brand_seqno, typ.subpaper ";
		 $this->sql .= "FROM "._TBL_PRINT." AS prt, "._TBL_BASIC_PRODUCE_PRINT." AS bpp, " . _TBL_TYPSET_FORMAT. " AS typ ";
		 $this->sql .= "WHERE bpp.typset_format_seqno = '".$ts_idx."' AND bpp.print_seqno = prt.print_seqno AND typ.typset_format_seqno = bpp.typset_format_seqno LIMIT 1 ";
		 $rs = $conn->Execute($this->sql);

		 if ($rs && !$rs->EOF) {
			 $nRes['prt_unit'] = $rs->fields['crtr_unit'];
			 $nRes['prt_name'] = $rs->fields['name'];
			 $nRes['prt_affil'] = $rs->fields['affil'];
			 $nRes['prt_subpaper'] = $rs->fields['subpaper'];
			 $nRes['prt_size'] = $rs->fields['wid_size'] . "*" . $rs->fields['vert_size'];
			 $nRes['prt_eb_idx'] = $rs->fields['extnl_brand_seqno'];
		 } else if ($rs->EOF) {
			 $nRes = "FAILED";
		 } else {
			 $nRes = "ERROR";
		 }

		 return $nRes;
	 }


	 /***********************************************************************************
	  *** 후공정 정보 가져오기 (Local)
	  ***********************************************************************************/

	 function getLocalAfterOPDataValue($conn, $ts_idx) {
		 $this->Init();

		 $this->sql = "SELECT oc.amt, oc.amt_unit_dvs, oah.after_name, oah.depth1, oah.depth2, oah.depth3, aft.extnl_brand_seqno ";
		 $this->sql .= "FROM "._TBL_ORDER." AS oc, "._TBL_ORDER_AFTER_HISTORY." AS oah, "._TBL_AFTER." AS aft, "._TBL_BASIC_PRODUCE_AFTER." AS bpa ";
		 $this->sql .= "WHERE oc.order_common_seqno = oah.order_common_seqno AND oah.after_name = aft.name ";
		 $this->sql .= "AND oah.basic_yn = 'Y' AND bpa.typset_format_seqno = '".$ts_idx."' AND bpa.after_seqno = aft.after_seqno ";
		 $this->sql .= "GROUP BY oah.after_name";
		 $rs = $conn->Execute($this->sql);

		 if ($rs && !$rs->EOF) {
			 $i = 0;
			 while ($rs && !$rs->EOF) {
					 $nRes[$i]['aft_name'] = $rs->fields['after_name'];
					 $nRes[$i]['aft_amt'] = $rs->fields['amt'];
					 $nRes[$i]['aft_amt_unit'] = $rs->fields['amt_unit_dvs'];
					 $nRes[$i]['aft_depth1'] = $rs->fields['depth1'];
					 $nRes[$i]['aft_depth2'] = $rs->fields['depth2'];
					 $nRes[$i]['aft_depth3'] = $rs->fields['depth3'];
					 $nRes[$i]['aff_eb_idx'] = $rs->fields['extnl_brand_seqno'];

				 	 $i++;
				 	 $rs->moveNext();
			 }
		 } else if ($rs->EOF) {
			 $nRes = "FAILED";
		 } else {
			 $nRes = "ERROR";
		 }

		 return $nRes;
	 }


	 function getLocalAfterOPDataValue2($dbc, $ts_idx) {
		 $this->Init();

		 $this->sql = "SELECT oc.order_detail AS name, oah.depth1 AS depth1, oah.depth2 AS depth2, ";
		 $this->sql .= "oah.depth3 AS depth3, aft.extnl_brand_seqno AS eb_idx ";
		 $this->sql .= "FROM "._TBL_ORDER." AS oc, "._TBL_ORDER_AFTER_HISTORY." AS oah, "._TBL_AFTER." AS aft, "._TBL_BASIC_PRODUCE_AFTER." AS bpa ";
		 $this->sql .= "WHERE oc.order_common_seqno = oah.order_common_seqno AND oah.after_name = aft.name AND oah.depth1 = aft.depth1 ";
		 $this->sql .= "AND oah.depth2 = aft.depth2 AND oah.depth3 = aft.depth3 AND oah.basic_yn = 'Y' ";
		 $this->sql .= "AND bpa.typset_format_seqno = '".$ts_idx."' AND bpa.after_seqno = aft.after_seqno ";
		 $this->rowsData = new CLS_DBQuery($this->sql, $dbc);

		 if (!trim($this->rowsData->error)) {
			 if ($this->rowsData->num_rows > 0) {
				 for ($i = 0; $i < $this->rowsData->num_rows; $i++) {
					 $nRes[$i]['aft_name'] = $this->rowsData->data[$i]['name'];
					 $nRes[$i]['aft_depth1'] = $this->rowsData->data[$i]['depth1'];
					 $nRes[$i]['aft_depth2'] = $this->rowsData->data[$i]['depth2'];
					 $nRes[$i]['aft_depth3'] = $this->rowsData->data[$i]['depth3'];
					 $nRes[$i]['aff_eb_idx'] = $this->rowsData->data[$i]['eb_idx'];
				 }
			 } else {
				 $nRes = "FAILED";
			 }
		 } else {
			 $nRes = "ERROR";
		 }

		 return $nRes;
	 }


	 /***********************************************************************************
	  *** 종이정보 가져오기 (Local)
	  ***********************************************************************************/

	 function getLocalPaperOPDataValue($conn, $ts_idx) {
		 $this->Init();

         $this->sql  = "\nSELECT pp.name AS name ";
         $this->sql .= "\n      ,pp.affil AS affil ";
         $this->sql .= "\n      ,pp.wid_size AS wsize ";
         $this->sql .= "\n      ,pp.vert_size AS hsize ";
         $this->sql .= "\n      ,bpp.grain AS grain ";
         $this->sql .= "\n      ,bpp.extnl_etprs_seqno ";
         $this->sql .= "\n      ,pp.crtr_unit AS unit ";
         $this->sql .= "\n      ,pp.extnl_brand_seqno AS eb_idx ";
         $this->sql .= "\n      ,pp.dvs AS dvs ";
         $this->sql .= "\n      ,pp.color AS color ";
         $this->sql .= "\n      ,pp.basisweight AS bw ";
         $this->sql .= "\n      ,pp.basisweight_unit AS bwu ";
         $this->sql .= "\n      ,bpp.extnl_etprs_seqno AS ex_idx ";
         $this->sql .= "\n FROM paper AS pp ";
         $this->sql .= "\n     ,basic_produce_paper AS bpp ";
         $this->sql .= "\n     ,extnl_etprs AS ee ";
         $this->sql .= "\n     ,extnl_brand AS eb ";
         $this->sql .= "\nWHERE bpp.typset_format_seqno = " . $ts_idx;
         $this->sql .= "\n  AND bpp.paper_seqno = pp.paper_seqno ";
         $this->sql .= "\n  AND pp.extnl_brand_seqno = eb.extnl_brand_seqno ";
         $this->sql .= "\n  AND ee.extnl_etprs_seqno = eb.extnl_etprs_seqno ";
		 $rs = $conn->Execute($this->sql);

		 if ($rs && !$rs->EOF) {
             $nRes['pp_name']  = $rs->fields['name'];
             $nRes['pp_affil'] = $rs->fields['affil'];
             $nRes['pp_size']  = $rs->fields['wsize']."*".$rs->fields['hsize'];
             $nRes['pp_grain'] = $rs->fields['grain'];
          //   $nRes['pp_unit']  = $rs->fields['unit'];
             $nRes['pp_eb_idx']= $rs->fields['eb_idx'];
             $nRes['pp_ex_idx']= $rs->fields['ex_idx'];
             $nRes['pp_dvs']   = $rs->fields['dvs'];
             $nRes['pp_color'] = $rs->fields['color'];
             $nRes['pp_bw']    = $rs->fields['bw'].$rs->fields['bwu'];
		 } else if ($rs->EOF) {
             $nRes = "NODATA";
         } else {
             $nRes = "ERROR";
         }

         return $nRes;
     }


	 /***********************************************************************************
	 *** 프로세스 플로우 (Local)
	 ***********************************************************************************/

	 function thisLocalProcessFlowInsertComplete($conn, $ts_num) {
		 $this->Init();
		 $CCFile = new CLS_File;

		 $this->sql = "SELECT produce_process_flow_seqno FROM "._TBL_PRODUCE_PROCESS_FLOW." ";
		 $this->sql .= "WHERE typset_num = '".$ts_num."' LIMIT 1";
		 $rs = $conn->Execute($this->sql);

		 $CCFile->FileWrite(_WPATH . "_판등록", "\n".date("H:i:s")." 프로세스 sel -> ".$this->sql."\n", "a+");

		 if ($rs->EOF) {
			 $this->Init();

			 $this->sql = "INSERT INTO " . _TBL_PRODUCE_PROCESS_FLOW . " SET typset_num = '" . $ts_num . "'";
			 $rs2 = $conn->Execute($this->sql);

			 $CCFile->FileWrite(_WPATH . "_판등록", "\n" . date("H:i:s") . " 프로세스 ins -> " . $this->sql . "\n", "a+");

			 if ($rs2 == true) {
				 $nRes = "SUCCESS";
			 } else {
				 $nRes = "ERROR";
			 }
		 } else {
			 $nRes = "ERROR";
		 }

		 return $nRes;
	 }


	 /***********************************************************************************
     *** 종이 프로세스 업데이트 (Local)
     ***********************************************************************************/

	 function thisLocalProcessFlowPaperUpdateComplete($conn, $ts_num) {
		 $this->Init();

		 $this->sql = "UPDATE "._TBL_PRODUCE_PROCESS_FLOW." SET paper_yn = 'Y' ";
		 $this->sql .= "WHERE typset_num = '".$ts_num."'";

		 $rs = $conn->Execute($this->sql);

		 if ($rs == true) {
			 $nRes = "SUCCESS";
		 } else {
			 $nRes = "ERROR";
		 }

		 return $nRes;
	 }


	 /***********************************************************************************
	 *** 출력 프로세스 업데이트 (Local)
	 ***********************************************************************************/

	 function thisLocalProcessFlowOutputUpdateComplete($conn, $state, $ts_num) {
		 $this->Init();

		 $this->sql = "UPDATE "._TBL_PRODUCE_PROCESS_FLOW." SET output_yn = '".$state."' ";
		 $this->sql .= "WHERE typset_num = '".$ts_num."'";
		 $rs = $conn->Execute($this->sql);

		 if ($rs == true) {
			 $nRes = "SUCCESS";
		 } else {
			 $nRes = "ERROR";
		 }

		 return $nRes;
	 }


	 /***********************************************************************************
	  *** 인쇄 프로세스 업데이트 (Local)
	  ***********************************************************************************/

	 function thisLocalProcessFlowPrintUpdateComplete($conn, $state, $ts_num) {
		 $this->Init();

		 $this->sql = "UPDATE "._TBL_PRODUCE_PROCESS_FLOW." SET print_yn = '".$state."' ";
		 $this->sql .= "WHERE typset_num = '".$ts_num."'";
		 $rs = $conn->Execute($this->sql);

		 if ($rs == true) {
			 $nRes = "SUCCESS";
		 } else {
			 $nRes = "ERROR";
		 }

		 return $nRes;
	 }


	 /***********************************************************************************
	  *** 낱장형 등록 고유값 가져오기 (Local)
	  ***********************************************************************************/

	 function getLocalSheetTypeSetDataInfoValue($dbc) {
		 $this->Init();

		 $this->sql = "SELECT sheet_typset_seqno FROM "._TBL_SHEET_TYPESET." ORDER BY sheet_typset_seqno DESC LIMIT 1";
		 $this->rowsData = new CLS_DBQuery($this->sql, $dbc);

		 if (!trim($this->rowsData->error)) {
			 $nRes = $rs->fields['sheet_typset_seqno'];
		 } else {
			 $nRes = "ERROR";
		 }

		 return $nRes;
	 }


	 /***********************************************************************************
	  *** 구분 가져오기 (Local)
	  ***********************************************************************************/

	 function getLocalDivDataValue($conn, $OrderNum) {
		 $CCFile = new CLS_File;
		 $this->Init();
		 //$OrderNum = substr($OrderNum, 1, strlen($OrderNum) - 5);

		 $this->sql = "SELECT D.color_name, D.color_code, D.nick ";
		 $this->sql .= "from order_opt_history AS A ";
		 $this->sql .= "INNER JOIN order_detail AS B ON A.order_common_seqno = B.order_common_seqno ";
		 $this->sql .= "INNER JOIN order_detail_count_file AS C ON B.order_detail_seqno = C.order_detail_seqno ";
		 $this->sql .= "INNER JOIN opt_info AS D ON A.opt_name = D.opt_name ";
		 $this->sql .= "where C.barcode_num = '" . $OrderNum .  "' and A.basic_yn = 'N' ";
		 $this->sql .= "ORDER BY D.opt_name ASC ";
		 $rs = $conn->Execute($this->sql);
		 $CCFile->FileWrite(_WPATH, "\n".date("H:i:s")." PRINT QUERY -> ".$this->sql."\n", "a+");


		 if ($rs && !$rs->EOF) {
			 while ($rs && !$rs->EOF) {
				     $nRes['opt_clr_name'] .= $rs->fields['color_name']."/";
				     $nRes['opt_clr_code'] .= $rs->fields['color_code']."/";
				     $nRes['opt_nick'] .= $rs->fields['nick']."/";

					 $rs->moveNext();
			 }

			 $nRes['opt_clr_name'] = substr($nRes['opt_clr_name'], 0, strlen($nRes['opt_clr_name']) - 1);
			 $nRes['opt_clr_code'] = substr($nRes['opt_clr_code'], 0, strlen($nRes['opt_clr_code']) - 1);
			 $nRes['opt_nick'] = substr($nRes['opt_nick'], 0, strlen($nRes['opt_nick']) - 1);
		 } else if ($rs->EOF) {
			 $nRes = "FAILED";
		 } else {
			 $nRes = "ERROR";
		 }

		 return $nRes;
	 }


	 function getLocalDivMultiDataValue($conn, $ONS, $ON) {
		 $this->Init();
		 $CCFile = new CLS_File;
		 $sucCount = 0;
		 $faiCount = 0;
		 $errCount = 0;
		 $n = 0;

		 for ($i = 1; $i <= $ONS; $i++) {
			  $CCFile->FileWrite(_WPATH, "\n".date("H:i:s")." 주문번호 [".$ONS."] -> ".$ON[$i]."\n", "a+");
			  $OrderNum = substr($ON[$i], 1, strlen($ON[$i]) - 5);

			  $this->sql = "SELECT oi.nick ";
			  $this->sql .= "FROM order_common AS oc, order_opt_history AS ooh, opt_info AS oi ";
			  $this->sql .= "WHERE oc.order_common_seqno = ooh.order_common_seqno AND oc.order_num =  '".$OrderNum."' ";
			  $this->sql .= "AND ooh.basic_yn = 'N' AND ooh.opt_name = oi.opt_name ORDER BY oi.seq ASC";
			  $rs = $conn->Execute($this->sql);

			 $CCFile->FileWrite(_WPATH, "\n".date("H:i:s")." 특기사항 -> ".$this->sql."\n", "a+");

			  if ($rs && !$rs->EOF) {
				  while ($rs && !$rs->EOF) {
					  	  $nckRes[$n] = $rs->fields['nick'];

					      $n++;
					  	  $rs->moveNext();
				  }

				  $sucCount++;
			 } else if ($rs->EOF) {
				  $faiCount++;
			 } else {
				  $errCount++;
			 }
		 }

		 if ($sucCount > 0) {
			 $nRes = array_unique($nckRes);
		 } else if($faiCount > 0) {
			 $nRes = "FAILED";
		 } else {
			$nRes = "ERROR";
		 }

		 return $nRes;
	 }


	 /***********************************************************************************
	  *** 본판 당일판 가져오기 (Local)
	  ***********************************************************************************/

	 function getLocalDivPenDataValue($conn, $ONS, $ON) {
		 $this->Init();
		 $bonCount = 0;
		 $dayCount = 0;

 		 for ($i = 1; $i <= $ONS; $i++) {
			  $OrderNum = substr($ON[$i], 1, strlen($ON[$i]) - 5);

			  $this->sql = "SELECT oi.nick, oc.dlvr_produce_dvs ";
			  $this->sql .= "FROM order_common AS oc, order_opt_history AS ooh, opt_info AS oi ";
			  $this->sql .= "WHERE oc.order_common_seqno = ooh.order_common_seqno AND oc.order_num =  '" . $OrderNum . "' ";
			  $this->sql .= "AND ooh.basic_yn = 'N' AND ooh.opt_name = oi.opt_name ORDER BY oi.seq ASC";
			  $rs = $conn->Execute($this->sql);

              $dlvr_produce_dvs = "";
			  if ($rs && !$rs->EOF) {
				  while ($rs && !$rs->EOF) {
						  $nickRes .= $rs->fields['nick'] . "/";
                          $dlvr_produce_dvs = $rs->fields['dlvr_produce_dvs'];

						  $rs->moveNext();
				  }

				  $nickRes = substr($nickRes, 0, strlen($nickRes) - 1);
				  $nkData = explode("/", $nickRes);

				  for ($n = 0; $n < count($nkData); $n++) {
					   if ($nkData[$n] == "당일판") {
						   $dayCount++;
					   } else {
						   $bonCount++;
					   }
				  }
			  } else {
				  $bonCount++;
			  }
		 }

	 	 if ($bonCount > 0) {
			 $nRes = "본판";
             if ($dlvr_produce_dvs) {
                 $nRes .= "-" . $dlvr_produce_dvs;
             }
		 } 
         if ($dayCount > 0) {
 			 $nRes = "당일판";
		 }

		 return $nRes;
	 }


	 function getLocalDivDataValue3($dbc, $OrderNum) {
		 $this->Init();
		 $OrderNum = substr($OrderNum, 1, strlen($OrderNum) - 5);

		 $this->sql = "SELECT ooh.opt_name AS opt_name ";
		 $this->sql .= "FROM "._TBL_ORDER_OPT_HISTORY." AS ooh, "._TBL_ORDER." AS oc ";
		 $this->sql .= "WHERE ooh.order_common_seqno = oc.order_common_seqno AND oc.order_num = '".$OrderNum."' ";
		 $this->sql .= "AND ooh.basic_yn = 'N'";
		 $this->rowsData = new CLS_DBQuery($this->sql, $dbc);

		 if (!trim($this->rowsData->error)) {
			 if ($this->rowsData->num_rows > 0) {
				 for ($i = 0; $i < $this->rowsData->num_rows; $i++) {
					 if ($this->rowsData->data[$i]['opt_name'] == "당일판") {
						 $nRes .= _CYP_OPT_NAME_TODAY."/";
					 } else if ($this->rowsData->data[$i]['opt_name'] == "빠른생산요청") {
						 $nRes .= _CYP_OPT_NAME_EMERG . "/";
					 } else if ($this->rowsData->data[$i]['opt_name'] == "정매생산요청") {
						 $nRes .= _CYP_OPT_NAME_JUNGM . "/";
					 } else if ($this->rowsData->data[$i]['opt_name'] == "색견본참고") {
						 $nRes .= _CYP_OPT_NAME_SAMPL . "/";
					 } else if ($this->rowsData->data[$i]['opt_name'] == "감리요청") {
						 $nRes .= _CYP_OPT_NAME_AUDIT . "/";
					 } else if ($this->rowsData->data[$i]['opt_name'] == "베다인쇄") {
						 $nRes .= _CYP_OPT_NAME_FELL . "/";
					 } else if ($this->rowsData->data[$i]['opt_name'] == "사고") {
						 $nRes .= _CYP_OPT_NAME_ACCIT . "/";
					 } else if ($this->rowsData->data[$i]['opt_name'] == "재단주의") {
						 $nRes .= _CYP_OPT_NAME_CARE . "/";
					 } else {
						 $nRes .= _CYP_OPT_NAME_NORM . "/";
					 }
				 }

				 $nRes = substr($nRes, 0, strlen($nRes) - 1);
			 } else {
				 $nRes = _CYP_OPT_NAME_NORM;
			 }
		 } else {
			 $nRes = "ERROR";
		 }

		 return $nRes;
	 }
 }
?>
