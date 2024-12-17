<?
 /***********************************************************************************
 *** 프로젝트 : CyPress
 *** 개발영역 : Order Module
 *** 개 발 자 : 김성진
 *** 개발날짜 : 2016.06.16
 ***********************************************************************************/

 class CLS_Order {

	 var $dbi;
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
		 $this->dbi		    = null;
		 $this->sql		    = null;
		 $this->rowsData 	= null;
		 $this->idx		    = null;
		 $this->order       = null;
		 $this->code        = null;
		 $this->curtime     = time();
		 $this->remoteip    = $_SERVER["REMOTE_ADDR"];
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
	 *** 주문 정보 가져오기
	 *** 수량 amt / 수량단위구분 amt_unit_dvs / 건수 count
	 ***********************************************************************************/

	 function getOrderInfoDataValue($_DBS, $OrderNum) {
		$dbc = $this->DBCon($_DBS);

		$this->Init();
		$this->order = $OrderNum;

		$this->sql = "SELECT oc.order_common_seqno AS oc_idx, oc.group_seqno AS oc_grp_idx, oc.order_num AS oc_ord_num, ";
		$this->sql .= "oc.order_state AS oc_ord_state, oc.oper_sys AS oc_oper_sys, ";
		//$this->sql .= "oc.req_cont AS oc_req_cont, oc.basic_price AS oc_bs_price, oc.grade_sale_price AS oc_grd_sa_price, ";
		$this->sql .= "oc.basic_price AS oc_bs_price, oc.grade_sale_price AS oc_grd_sa_price, ";
		$this->sql .= "oc.event_price AS oc_evt_price, oc.use_point_price AS oc_use_pit_price, oc.sell_price AS oc_sel_price, ";
		$this->sql .= "oc.cp_price AS oc_cp_price, oc.pay_price AS oc_pay_price, oc.order_regi_date AS oc_ord_regi_date, ";
		$this->sql .= "oc.member_seqno AS oc_mb_idx, oc.mono_yn AS oc_mono_yn, oc.claim_yn AS oc_clm_yn, oc.order_detail AS oc_ord_detail, ";
		$this->sql .= "oc.title AS oc_title, oc.expec_weight AS oc_exp_weight, oc.bun_group AS oc_bun_group, ";
		//$this->sql .= "oc.depo_finish_date AS oc_depo_hns_date, oc.memo AS oc_memo, oc.cpn_admin_seqno AS oc_cpn_admin_idx, ";
		 $this->sql .= "oc.depo_finish_date AS oc_depo_hns_date, oc.cpn_admin_seqno AS oc_cpn_admin_idx, ";
		$this->sql .= "oc.del_yn AS oc_del_yn, oc.eraser AS oc_eraser, oc.point_use_yn AS oc_pit_use_yn, ";
		$this->sql .= "oc.owncompany_img_num AS oc_owncpn_img_num, oc.pay_way AS oc_pay_way, oc.cate_sortcode AS oc_cat_scode, ";
		$this->sql .= "oc.opt_use_yn AS oc_opt_use_yn, oc.prdt_basic_info AS oc_prdt_bs_info, oc.prdt_add_info AS oc_prdt_add_info, ";
		$this->sql .= "oc.prdt_price_info AS oc_prdt_prc_info, oc.bun_yn AS oc_bun_yn, oc.prdt_pay_info AS oc_prdt_pay_info, ";
		$this->sql .= "oc.add_after_price AS oc_add_aft_price, oc.add_opt_price AS oc_add_opt_price, oc.expenevid_req_yn AS oc_exp_req_yn, ";
		$this->sql .= "oc.expenevid_dvs AS oc_exp_dvs, oc.expenevid_num AS oc_exp_num, oc.event_yn AS oc_evt_yn, oc.receipt_dvs AS oc_rec_dvs, ";
		//$this->sql .= "oc.stor_release_yn AS oc_stor_rel_yn, oc.receipt_mng AS oc_rec_mng, oc.dlvr_finish_date AS oc_dlvr_fns_date, ";
		$this->sql .= "oc.stor_release_yn AS oc_stor_rel_yn, oc.dlvr_finish_date AS oc_dlvr_fns_date, ";
		$this->sql .= "oc.order_mng AS oc_ord_mng, oc.file_upload_dvs AS oc_file_up_dvs, oc.amt AS oc_amt, ";
		$this->sql .= "oc.amt_unit_dvs AS oc_amt_unit_dvs, oc.count AS oc_count, oc.order_lack_price AS oc_ord_lack_price, ";
		$this->sql .= "od.order_detail_seqno AS od_ord_dtl_idx, od.order_detail_dvs_num AS od_ord_dtl_dvs_num, od.state AS od_state, ";
		$this->sql .= "od.typ AS od_type, od.page_amt AS od_page_amt, od.cate_paper_mpcode AS od_cat_pp_mpcode, od.spc_dscr AS od_spc_dscr, ";
		$this->sql .= "od.work_size_wid AS od_wk_sz_width, od.work_size_vert AS od_wk_sz_height, od.cut_size_wid AS od_cut_sz_width, ";
		$this->sql .= "od.cut_size_vert AS od_cut_sz_height, od.tomson_size_wid AS od_tms_sz_width, od.tomson_size_vert AS od_tms_sz_height, ";
		$this->sql .= "od.cate_beforeside_print_mpcode AS od_cat_bfsd_prt_mpcode, od.cate_beforeside_add_print_mpcode AS od_cat_bfsd_add_prt_mpcode, ";
		$this->sql .= "od.cate_aftside_print_mpcode AS od_cat_afsd_prt_mpcode, od.cate_aftside_add_print_mpcode AS od_cat_afsd_add_prt_mpcode, ";
		$this->sql .= "od.print_purp_dvs AS od_prt_purp_dvs, od.basic_price AS od_bs_price, od.sell_price AS od_sel_price, ";
		$this->sql .= "od.grade_sale_price AS od_grd_sale_price, od.add_after_price AS od_add_aft_price, od.cp_price AS od_cp_price, ";
		$this->sql .= "od.pay_price AS od_pay_price, od.del_yn AS od_del_yn, od.use_point_price AS od_use_pit_price, ";
		$this->sql .= "od.order_detail AS od_ord_detail, od.mono_yn AS od_mono_yn, od.stan_name AS od_stan_name, od.amt AS od_amt,  ";
		$this->sql .= "od.count AS od_count, od.expec_weight AS od_exp_weight, od.amt_unit_dvs AS od_amt_unit_dvs, ";
		$this->sql .= "od.after_use_yn AS od_aft_use_yn, od.cate_sortcode AS od_cat_scode, od.tot_tmpt AS od_tot_tmpt, ";
		$this->sql .= "od.receipt_mng AS od_rec_mng, od.print_tmpt_name AS od_prt_tmpt_name, od.prdt_basic_info AS od_prdt_bs_info, ";
		//$this->sql .= "od.prdt_add_info AS od_prdt_add_info, od.receipt_dvs AS od_rec_dvs, od.receipt_memo AS od_rec_memo, ";
		$this->sql .= "od.prdt_add_info AS od_prdt_add_info, od.receipt_memo AS od_rec_memo, ";
		$this->sql .= "od.receipt_finish_date AS od_rec_fns_date, ";
		$this->sql .= "odcf.order_detail_count_file_seqno AS odcf_idx, odcf.order_detail_seqno AS odcf_od_idx, odcf.seq AS odcf_seq, ";
		$this->sql .= "odcf.order_detail_file_num AS odcf_ord_dtl_file_num, odcf.state AS odcf_state, odcf.file_path AS odcf_fpath, ";
		$this->sql .= "odcf.save_file_name AS odcf_sv_fname, ";
		$this->sql .= "odcf.origin_file_name AS odcf_org_fname, odcf.size AS odcf_size, odcf.print_file_path AS odcf_prt_fpath, ";
		$this->sql .= "odcf.print_file_name AS odcf_prt_fname, odcf.tmp_file_path AS odcf_tmp_fpath, odcf.tmp_file_name AS odcf_tmp_fname ";
		$this->sql .= "FROM "._TBL_ORDER." AS oc, "._TBL_ORDER_DETAIL." AS od, "._TBL_ORDER_DETAIL_COUNT_FILE." AS odcf ";
		$this->sql .= "WHERE oc.order_common_seqno = od.order_common_seqno ";
	 	$this->sql .= "AND od.order_detail_seqno = odcf.order_detail_seqno ";
		$this->sql .= "AND odcf.barcode_num = '".$this->order."' ";
		$this->sql .= "AND odcf.state = '"._CYP_STS_CD_READY."' LIMIT 1";
		$this->rowsData = new CLS_DBQuery($this->sql, $dbc);

		if (!trim($this->rowsData->error)) {
			if ($this->rowsData->num_rows > 0) {
				// 주문 공통
				$nRes['oc_idx'] = $this->rowsData->data[0]['oc_idx'];								// 순서
				$nRes['oc_grp_idx'] = $this->rowsData->data[0]['oc_grp_idx'];						// 그룹 순서
				$nRes['oc_ord_num'] = $this->rowsData->data[0]['oc_ord_num'];						// 주문번호
				$nRes['oc_ord_state'] = $this->rowsData->data[0]['oc_ord_state'];					// 상태
				$nRes['oc_oper_sys'] = $this->rowsData->data[0]['oc_oper_sys'];						// OS
				//$nRes['oc_req_cont'] = $this->rowsData->data[0]['oc_req_cont'];						// 요청내용
				$nRes['oc_bs_price'] = $this->rowsData->data[0]['oc_bs_price'];						// 기본금액
				$nRes['oc_grd_sa_price'] = $this->rowsData->data[0]['oc_grd_sa_price'];				// 등급 할인 금액
				$nRes['oc_evt_price'] = $this->rowsData->data[0]['oc_evt_price'];					// 이벤트 금액
				$nRes['oc_use_pit_price'] = $this->rowsData->data[0]['oc_use_pit_price'];			// 사용 포인트 금액
				$nRes['oc_sel_price'] = $this->rowsData->data[0]['oc_sel_price'];					// 판매금액
				$nRes['oc_cp_price'] = $this->rowsData->data[0]['oc_cp_price'];						// 쿠폰금액
				$nRes['oc_pay_price'] = $this->rowsData->data[0]['oc_pay_price'];					// 결제금액
				$nRes['oc_ord_regi_date'] = $this->rowsData->data[0]['oc_ord_regi_date'];			// 주문등록일자
				$nRes['oc_mb_idx'] = $this->rowsData->data[0]['oc_mb_idx'];							// 회원 일련번호
				$nRes['oc_mono_yn'] = $this->rowsData->data[0]['oc_mono_yn'];						// 독판 여부
				$nRes['oc_clm_yn'] = $this->rowsData->data[0]['oc_clm_yn'];							// 클레임 여부
				$nRes['oc_ord_detail'] = $this->rowsData->data[0]['oc_ord_detail'];					// 주문 상세
				$nRes['oc_title'] = trim($this->rowsData->data[0]['oc_title']);						// 제목
				$nRes['oc_exp_weight'] = $this->rowsData->data[0]['oc_exp_weight'];					// 예상무게
				$nRes['oc_bun_group'] = $this->rowsData->data[0]['oc_bun_group'];					// 묶음 그룹
				$nRes['oc_depo_hns_date'] = $this->rowsData->data[0]['oc_depo_hns_date'];			// 입금완료일자
				//$nRes['oc_memo'] = $this->rowsData->data[0]['oc_memo'];								// 메모
				$nRes['oc_cpn_admin_idx'] = $this->rowsData->data[0]['oc_cpn_admin_idx'];			// 회사관리 일련번호
				$nRes['oc_del_yn'] = $this->rowsData->data[0]['oc_del_yn'];							// 삭제유무
				$nRes['oc_eraser'] = $this->rowsData->data[0]['oc_eraser'];							// 삭제자
				$nRes['oc_pit_use_yn'] = $this->rowsData->data[0]['oc_pit_use_yn'];					// 포인트 사용여부
				$nRes['oc_owncpn_img_num'] = $this->rowsData->data[0]['oc_owncpn_img_num'];	        // 자사 이미지 번호
				$nRes['oc_pay_way'] = $this->rowsData->data[0]['oc_pay_way'];						// 결제 방식
				$nRes['oc_cat_scode'] = $this->rowsData->data[0]['oc_cat_scode'];					// 카테고리 분류코드
				$nRes['oc_opt_use_yn'] = $this->rowsData->data[0]['oc_opt_use_yn'];					// 옵션 사용여부
				$nRes['oc_prdt_bs_info'] = $this->rowsData->data[0]['oc_prdt_bs_info'];				// 상품 기본 정보
				$nRes['oc_prdt_add_info'] = $this->rowsData->data[0]['oc_prdt_add_info'];			// 상품 추가 정보
				$nRes['oc_prdt_prc_info'] = $this->rowsData->data[0]['oc_prdt_prc_info'];			// 상품 금액 정보
				$nRes['oc_bun_yn'] = $this->rowsData->data[0]['oc_bun_yn'];							// 묶음 여부
				$nRes['oc_prdt_pay_info'] = $this->rowsData->data[0]['oc_prdt_pay_info'];			// 상품 결제 정보
				$nRes['oc_add_aft_price'] = $this->rowsData->data[0]['oc_add_aft_price'];			// 추가 후공정 금액
				$nRes['oc_add_opt_price'] = $this->rowsData->data[0]['oc_add_opt_price'];			// 추가 옵션 금액
				$nRes['oc_exp_req_yn'] = $this->rowsData->data[0]['oc_exp_req_yn'];					// 지출증빙 요청여부
				$nRes['oc_exp_dvs'] = $this->rowsData->data[0]['oc_exp_dvs'];						// 지출증비구분
				$nRes['oc_exp_num'] = $this->rowsData->data[0]['oc_exp_num'];						// 지출증빙번호
				$nRes['oc_evt_yn'] = $this->rowsData->data[0]['oc_evt_yn'];							// 이벤트 여부
				$nRes['oc_rec_dvs'] = $this->rowsData->data[0]['oc_rec_dvs'];						// 접수 구분
				$nRes['oc_stor_rel_yn'] = $this->rowsData->data[0]['oc_stor_rel_yn'];				// 입출고여부
				$nRes['oc_rec_mng'] = $this->rowsData->data[0]['oc_rec_mng'];						// 접수 담당자
				$nRes['oc_dlvr_fns_date'] = $this->rowsData->data[0]['oc_dlvr_fns_date'];			// 배송완료일자
				$nRes['oc_ord_mng'] = $this->rowsData->data[0]['oc_ord_mng'];						// 주문담당자
				$nRes['oc_file_up_dvs'] = $this->rowsData->data[0]['oc_file_up_dvs'];				// 파일업로드 구분
				$nRes['oc_amt'] = $this->rowsData->data[0]['oc_amt'];								// 수량
				$nRes['oc_amt_unit_dvs'] = $this->rowsData->data[0]['oc_amt_unit_dvs'];				// 수량 단위구분
				$nRes['oc_count'] = $this->rowsData->data[0]['oc_count'];							// 건수
				$nRes['oc_ord_lack_price'] = $this->rowsData->data[0]['oc_ord_lack_price'];			// 주문부족금액

				//주문 상세
				$nRes['od_ord_dtl_idx'] = $this->rowsData->data[0]['od_ord_dtl_idx'];				// 순서
				$nRes['od_ord_dtl_dvs_num'] = $this->rowsData->data[0]['od_ord_dtl_dvs_num'];		// 주문상세 구분번호
				$nRes['od_state'] = $this->rowsData->data[0]['od_state'];							// 상태
				$nRes['od_type'] = $this->rowsData->data[0]['od_type'];								// 종류
				$nRes['od_page_amt'] = $this->rowsData->data[0]['od_page_amt'];						// 페이지 수량
				$nRes['od_cat_pp_mpcode'] = $this->rowsData->data[0]['od_cat_pp_mpcode'];			// 카테고리 종이 맵핑코드
				$nRes['od_spc_dscr'] = $this->rowsData->data[0]['od_spc_dscr'];						// 별색 설명
				$nRes['od_wk_sz_width'] = $this->rowsData->data[0]['od_wk_sz_width'];				// 작업사이즈 가로
				$nRes['od_wk_sz_height'] = $this->rowsData->data[0]['od_wk_sz_height'];				// 작업사이즈 세로
				$nRes['od_cut_sz_width'] = $this->rowsData->data[0]['od_cut_sz_width'];				// 재단사이즈 가로
				$nRes['od_cut_sz_height'] = $this->rowsData->data[0]['od_cut_sz_height'];			// 재단사이즈 세로
				$nRes['od_tms_sz_width'] = $this->rowsData->data[0]['od_tms_sz_width'];				// 도무송사이즈 가로
				$nRes['od_tms_sz_height'] = $this->rowsData->data[0]['od_tms_sz_height'];			// 도무송사이즈 세로
				$nRes['od_cat_bfsd_prt_mpcode'] = $this->rowsData->data[0]['od_cat_bfsd_prt_mpcode'];			// 카테고리 전면인쇄 맵핑코드
				$nRes['od_cat_bfsd_add_prt_mpcode'] = $this->rowsData->data[0]['od_cat_bfsd_add_prt_mpcode'];	// 카테고리 전면추가 인쇄 맵핑코드
				$nRes['od_cat_afsd_prt_mpcode'] = $this->rowsData->data[0]['od_cat_afsd_prt_mpcode'];			// 카테고리 후면인쇄 맵핑코드
				$nRes['od_cat_afsd_add_prt_mpcode'] = $this->rowsData->data[0]['od_cat_afsd_add_prt_mpcode'];	// 카테고리 후면추가 인쇄 맵핑코드
				$nRes['od_prt_purp_dvs'] = $this->rowsData->data[0]['od_prt_purp_dvs'];							// 인쇄 용도구분
				$nRes['od_bs_price'] = $this->rowsData->data[0]['od_bs_price'];									// 기본금액
				$nRes['od_sel_price'] = $this->rowsData->data[0]['od_sel_price'];								// 판매금액
				$nRes['od_grd_sale_price'] = $this->rowsData->data[0]['od_grd_sale_price'];						// 등급 할인금액
				$nRes['od_add_aft_price'] = $this->rowsData->data[0]['od_add_aft_price'];						// 추가 후공정금액
				$nRes['od_cp_price'] = $this->rowsData->data[0]['od_cp_price'];									// 쿠폰금액
				$nRes['od_pay_price'] = $this->rowsData->data[0]['od_pay_price'];								// 결제금액
				$nRes['od_del_yn'] = $this->rowsData->data[0]['od_del_yn'];										// 삭제유무
				$nRes['od_use_pit_price'] = $this->rowsData->data[0]['od_use_pit_price'];						// 사용 포인트 금액
				$nRes['od_ord_detail'] = $this->rowsData->data[0]['od_ord_detail'];								// 주무상세
				$nRes['od_mono_yn'] = $this->rowsData->data[0]['od_mono_yn'];									// 독판여부
				$nRes['od_stan_name'] = $this->rowsData->data[0]['od_stan_name'];								// 규격이름
				$nRes['od_amt'] = $this->rowsData->data[0]['od_amt'];											// 수량
				$nRes['od_count'] = $this->rowsData->data[0]['od_count'];										// 건수
				$nRes['od_exp_weight'] = $this->rowsData->data[0]['od_exp_weight'];								// 예상무게
				$nRes['od_amt_unit_dvs'] = $this->rowsData->data[0]['od_amt_unit_dvs'];							// 수량 단위구분
				$nRes['od_aft_use_yn'] = $this->rowsData->data[0]['od_aft_use_yn'];								// 후공정 사용유무
				$nRes['od_cat_scode'] = $this->rowsData->data[0]['od_cat_scode'];								// 카테고리 분류코드
				$nRes['od_tot_tmpt'] = $this->rowsData->data[0]['od_tot_tmpt'];									// 전체 도수
				$nRes['od_rec_mng'] = $this->rowsData->data[0]['od_rec_mng'];									// 접수 담당자
				$nRes['od_prt_tmpt_name'] = $this->rowsData->data[0]['od_prt_tmpt_name'];						// 인쇄도수 이름
				$nRes['od_prdt_bs_info'] = $this->rowsData->data[0]['od_prdt_bs_info'];							// 상품 기본정보
				$nRes['od_prdt_add_info'] = $this->rowsData->data[0]['od_prdt_add_info'];						// 상품 추가정보
				$nRes['od_rec_dvs'] = $this->rowsData->data[0]['od_rec_dvs'];									// 접수구분
				$nRes['od_rec_memo'] = $this->rowsData->data[0]['od_rec_memo'];									// 접수메모
				$nRes['od_rec_fns_date'] = $this->rowsData->data[0]['od_rec_fns_date'];							// 접수완료일자

				$nRes['oc_state_name'] = $this->getLocalStatDataValue($dbc, $nRes['oc_ord_state']);				// 상태이름

				// 제품 및 코드값
				$nRes['od_big_code'] = substr($nRes['od_cat_scode'], 0, 3);
				$nRes['od_prdt_name'] = $this->getLocalCateProductNameValue($dbc, $nRes['od_big_code']);		// 제품명칭
				$nRes['od_prdt_code'] = $nRes['od_big_code'];													// 제품코드값

				$nRes['od_mid_code'] = substr($nRes['od_cat_scode'], 0, 6);
				$nRes['od_item_name'] = $this->getLocalCateProductNameValue($dbc, $nRes['od_mid_code']);		// 품목명칭
				$nRes['od_item_code'] = $nRes['od_mid_code'];													// 품목코드값

				$nRes['od_sma_code'] = substr($nRes['od_cat_scode'], 0, 9);
				$nRes['od_kind_name'] = $this->getLocalCateProductNameValue($dbc, $nRes['od_sma_code']);		// 종류명칭
				$nRes['od_kind_code'] = $nRes['od_sma_code'];													// 종류코드값

				// 면수 및 도수
				$nSCData = explode("/", $nRes['od_ord_detail']);
				$nRes['od_sc_data'] = trim($nSCData[3]);

				$nRes['od_size_name'] = trim($nSCData[2]);														// 사이즈 명칭
				$nRes['od_size_code'] = trim($nSCData[2]);														// 사이즈 코드값

				$nRes['od_paper_name'] = trim($nSCData[1]);														// 지질 명칭
				$nRes['od_paper_code'] = "";																	// 지질 코드값

				$nRes['res_after'] = $this->getLocalBasicAfterDataValue($dbc, $OrderNum);						// 기본 후공정

				// 기본 후공정
				if (is_array($nRes['res_after'])) {
					for ($i = 0; $i < count($nRes['res_after']); $i++) {
						 $nRes['bs_after'] .= $nRes['res_after'][$i]['name']."<".$i.">::";
					}

					$nRes['bs_after'] = substr($nRes['bs_after'], 0, strlen($nRes['bs_after']) - 2);
				}

				// 배송
				$nRes['delevery'] = $this->getLocalDeleveryDataValue($dbc, $nRes['oc_idx']);

				if (is_array($nRes['delevery'])) {
					$nRes['od_dely_cpn_name'] = $nRes['delevery']['cpn_name'];									// 택배사
					$nRes['od_dely_name'] = $nRes['delevery']['name'];											// 수령인
					$nRes['od_dely_phone'] = $nRes['delevery']['phone'];										// 수신전화
					$nRes['od_dely_mobile'] = $nRes['delevery']['mobile'];										// 수신휴대폰
					$nRes['od_dely_addr'] = $nRes['delevery']['addr'];											// 배송주소
					$nRes['od_dely_dlvr_way'] = $nRes['delevery']['dlvr_way'];									// 배송방법
				}

				// 접수
				if (strpos($nRes['od_rec_mng'], "Auto") === false) {
					$nRes['od_empl_id'] = $this->getLocalReceiptMngNameValue($dbc, $nRes['od_rec_mng']);					// 접수아이디
					$nRes['od_rec_dvs'] = "M";
				} else {
					$nRes['od_empl_id'] = "Auto";
					$nRes['od_rec_dvs'] = "A";
				}

				$nRes['div_data'] = $this->getLocalDivDataValue($dbc, $OrderNum);

				if (is_array($nRes['div_data'])) {
					$nRes['div'] = $nRes['div_data']['opt_nick'];
				} else if ($nRes['div_data'] == "FAILED" ) {
					$nRes['div'] = _CYP_OPT_NAME_NORM;
				} else {
					$nRes['div'] = _CYP_OPT_NAME_NORM;
				}

				// 고객
				$nRes['member'] = $this->getLocalMemberDataValue($dbc, $nRes['oc_mb_idx']);

				$nRes['od_cus_dvs'] = $nRes['member']['mb_dvs'];
				$nRes['od_cus_id'] = $nRes['member']['mb_id'];											// 회원id
				$nRes['od_cus_cp_name'] = $nRes['member']['mb_name'];
				$nRes['od_cus_name'] = $nRes['member']['mb_name'];										// 담당자명
				$nRes['od_cus_phone'] = $nRes['member']['mb_phone'];									// 연락처
				$nRes['od_cus_mobile'] = $nRes['member']['mb_mobile'];									// 휴대폰
				$nRes['od_cus_addr'] = $nRes['member']['mb_addr'];										// 주소

				if ($nRes['member']['mb_dvs'] == "기업" || $nRes['member']['mb_dvs'] == "기업개인") {
					$nRes['customer'] = $this->getLocalCustomerDataValue($dbc, $nRes['oc_mb_idx']);

					if ($nRes['customer'] != "FAILED" && $nRes['customer'] != "ERROR") {
						$nRes['od_cus_cp_name'] = $nRes['customer'];                          // 회사명
					}
				}

				// 주문상세 건수파일
				$nRes['odcf_idx'] = $this->rowsData->data[0]['odcf_idx'];										// 순서
				$nRes['odcf_od_idx'] = $this->rowsData->data[0]['odcf_od_idx'];									// 주무 디테일 번호
				$nRes['odcf_seq'] = $this->rowsData->data[0]['odcf_seq'];										// 일련번호
				$nRes['odcf_ord_dtl_file_num'] = $this->rowsData->data[0]['odcf_ord_dtl_file_num'];				// 주문상세 파일번호
				$nRes['odcf_state'] = $this->rowsData->data[0]['odcf_state'];									// 상태
				$nRes['odcf_fpath'] = $this->rowsData->data[0]['odcf_fpath'];									// 파일경로
				$nRes['odcf_sv_fname'] = $this->rowsData->data[0]['odcf_sv_fname'];								// 저장 파일이름
				$nRes['odcf_org_fname'] = $this->rowsData->data[0]['odcf_org_fname'];							// 원본 파일이름
				$nRes['odcf_size'] = $this->rowsData->data[0]['odcf_size'];										// 사이즈
				$nRes['odcf_prt_fpath'] = $this->rowsData->data[0]['odcf_prt_fpath'];							// 인쇄 파일경로
				$nRes['odcf_prt_fname'] = $this->rowsData->data[0]['odcf_prt_fname'];							// 인쇄 원본 파일이름
				$nRes['odcf_tmp_fpath'] = $this->rowsData->data[0]['odcf_tmp_fpath'];							// 인쇄 임시 파일경로
				$nRes['odcf_tmp_fname'] = $this->rowsData->data[0]['odcf_tmp_fname'];							// 인쇄 임시 파일이름
			} else {
				$nRes = "FAILED";
			}
		} else {
			$nRes = "ERROR";
		}

		$dbc->DBClose();

		return $nRes;
	 }


	 function getOrderInfoDataValue2($_DBS, $OrderNum) {
		 $dbc = $this->DBCon($_DBS);

		 $this->Init();
		 $this->order = $OrderNum;

		 $this->sql = "SELECT order_common_seqno, order_state, oper_sys, pay_price, ";
		 $this->sql .= "member_seqno, order_detail, title, memo, cate_sortcode ";
		 $this->sql .= "FROM "._TBL_ORDER." ";
		 $this->sql .= "WHERE order_num = '".$this->order."' LIMIT 1";
		 // echo $this->sql;
		 $this->rowsData = new CLS_DBQuery($this->sql, $dbc);

		 if (!trim($this->rowsData->error)) {
			 if ($this->rowsData->num_rows > 0) {
				 $nRes['idx'] = $this->rowsData->data[0]['order_common_seqno'];
				 $nRes['order_state'] = $this->rowsData->data[0]['order_state'];
				 $nRes['oper_sys'] = $this->rowsData->data[0]['oper_sys'];
				 $nRes['title'] = $this->rowsData->data[0]['title'];
				 $nRes['order_detail'] = $this->rowsData->data[0]['order_detail'];

				 $nRes['cate_sortcode'] = $this->rowsData->data[0]['cate_sortcode'];

				 $nRes['big_code'] = substr($nRes['cate_sortcode'], 0, 3);
				 $nRes['product_name'] = $this->getLocalCateProductNameValue($dbc, $nRes['big_code']);
				 $nRes['product_code'] = $nRes['big_code'];

				 $nRes['mid_code'] = substr($nRes['cate_sortcode'], 0, 6);
				 $nRes['item_name'] = $this->getLocalCateProductNameValue($dbc, $nRes['mid_code']);
				 $nRes['item_code'] = $nRes['mid_code'];

				 $nRes['sma_code'] = substr($nRes['cate_sortcode'], 0, 9);
				 $nRes['kind_name'] = $this->getLocalCateProductNameValue($dbc, $nRes['sma_code']);
				 $nRes['kind_code'] = $nRes['sma_code'];

				 $nRes['color'] = $this->getLocalColorDataValue($dbc, $nRes['cate_sortcode']);

				 if (is_array($nRes['color'])) {
					 $nRes['color_idx'] = $nRes['color']['color_idx'];
					 $nRes['side_name'] = $nRes['color']['side_name'];
					 $nRes['color_name'] = $nRes['color']['color_name'];
					 $nRes['color_count'] = $nRes['color']['color_count'];
				 } else {
					 $nRes['color_idx'] = "";
					 $nRes['side_name'] = "";
					 $nRes['color_name'] = "";
					 $nRes['color_count'] = "";
				 }

				 $nRes['paper'] = $this->getLocalPaperDataValue($dbc, $nRes['cate_sortcode']);

				 if (is_array($nRes['paper'])) {
					 $nRes['paper_idx'] = $nRes['paper']['paper_idx'];
					 $nRes['paper_name'] = $nRes['paper']['paper_name'];
					 $nRes['paper_affil'] = $nRes['paper']['paper_affil'];
					 $nRes['paper_bweight'] = $nRes['paper']['paper_bweight'];
					 $nRes['paper_size'] = $nRes['paper']['paper_size'];
				 } else {
					 $nRes['paper_idx'] = "";
					 $nRes['paper_name'] = "";
					 $nRes['paper_affil'] = "";
					 $nRes['paper_bweight'] = "";
					 $nRes['paper_size'] = "";
				 }

				 $nRes['stan'] = $this->getLocalStanDataValue($dbc, $nRes['cate_sortcode']);

				 if (is_array($nRes['stan'])) {
					 $nRes['work_wsize'] = $nRes['stan']['work_wsize'];
					 $nRes['work_vsize'] = $nRes['stan']['work_vsize'];
					 $nRes['cut_wsize'] = $nRes['stan']['cut_wsize'];
					 $nRes['cut_vsize'] = $nRes['stan']['cut_vsize'];
				 } else {
					 $nRes['work_wsize'] = "";
					 $nRes['work_vsize'] = "";
					 $nRes['cut_wsize'] = "";
					 $nRes['cut_vsize'] = "";
				 }

				 $nRes['detail'] = $this->getLocalOrderDetailDataValue($dbc, $nRes['idx']);

				 if (is_array($nRes['detail'])) {
					 $nRes['quantity_name'] = $nRes['detail']['od_amt'] . $nRes['detail']['od_amt_unit_dvs'];
					 $nRes['quantity_code'] = $nRes['detail']['od_amt'];
					 $nRes['quantity_value'] = $nRes['detail']['od_amt'];

					 $nRes['case_name'] = $nRes['detail']['od_count'];
					 $nRes['case_code'] = $nRes['detail']['od_idx'];
					 $nRes['case_value'] = $nRes['detail']['od_count'];
				 } else {
					 $nRes['quantity_name'] = "";
					 $nRes['quantity_code'] = "";
					 $nRes['quantity_value'] = "";

					 $nRes['case_name'] = "";
					 $nRes['case_code'] = "";
					 $nRes['case_value'] = "";
				 }

				 $nRes['delevery'] = $this->getLocalDeleveryDataValue($dbc, $nRes['idx']);

				 if (is_array($nRes['delevery'])) {
					 $nRes['dely_cpn_name'] = $nRes['delevery']['cpn_name'];
					 $nRes['dely_name'] = $nRes['delevery']['name'];
					 $nRes['dely_phone'] = $nRes['delevery']['phone'];
					 $nRes['dely_mobile'] = $nRes['delevery']['mobile'];
					 $nRes['dely_addr'] = $nRes['delevery']['addr'];
					 $nRes['dely_dlvr_way'] = $nRes['delevery']['dlvr_way'];
					 $nRes['dely_price'] = $nRes['delevery']['price'];
				 } else {
					 $nRes['dely_cpn_name'] = "";
					 $nRes['dely_name'] = "";
					 $nRes['dely_phone'] = "";
					 $nRes['dely_mobile'] = "";
					 $nRes['dely_addr'] = "";
					 $nRes['dely_dlvr_way'] = "";
					 $nRes['dely_price'] = "";
				 }

				 $nRes['memo'] = $nRes['memo'];
				 $nRes['price'] = $nRes['pay_price'];
				 $nRes['mb_idx'] = $nRes['member_seqno'];

				 $nRes['receipt'] = $this->getLocalReceiptDataValue($dbc, $OrderNum);

				 if (is_array($nRes['receipt'])) {
					 $nRes['receipt_dvs'] = $nRes['receipt']['receipt_dvs'];
					 $nRes['order_regi_date'] = $nRes['receipt']['order_regi_date'];
					 $nRes['receipt_regi_date'] = $nRes['receipt']['receipt_regi_date'];
					 $nRes['member_id'] = $nRes['receipt']['member_id'];
					 $nRes['receipt_mng'] = $nRes['receipt']['receipt_mng'];
				 } else {
					 $nRes['receipt_dvs'] = "";
					 $nRes['order_regi_date'] = "";
					 $nRes['receipt_regi_date'] = "";
					 $nRes['member_id'] = "";
					 $nRes['receipt_mng'] = "";
				 }

				 $nRes['customer'] = $this->getLocalCustomerDataValue($dbc, $OrderNum);

				 if (is_array($nRes['customer'])) {
					 $nRes['mb_dvs'] = $nRes['customer']['mb_dvs'];
					 $nRes['mb_id'] = $nRes['customer']['mb_id'];
					 $nRes['cpn_name'] = $nRes['customer']['cpn_name'];
					 $nRes['mb_name'] = $nRes['customer']['mb_name'];
					 $nRes['mb_phone'] = $nRes['customer']['mb_phone'];
					 $nRes['mb_mobile'] = $nRes['customer']['mb_mobile'];
					 $nRes['mb_addr'] = $nRes['customer']['mb_addr'];
				 } else {
					 $nRes['mb_dvs'] = "";
					 $nRes['mb_id'] = "";
					 $nRes['cpn_name'] = "";
					 $nRes['mb_name'] = "";
					 $nRes['mb_phone'] = "";
					 $nRes['mb_mobile'] = "";
					 $nRes['mb_addr'] = "";
				 }


				 $nRes['repository'] = $this->getLocalRepositoryDataValue($dbc, $nRes['idx']);

				 if (is_array($nRes['repository'])) {
					 $nRes['file_path'] = $nRes['repository']['of_fpash'];
					 $nRes['pdf_path'] = $nRes['repository']['odcf_fpath'];
					 $nRes['pre_path'] = $nRes['repository']['pre_fpath'];
				 } else {
					 $nRes['file_path'] = "";
					 $nRes['pdf_path'] = "";
					 $nRes['pre_path'] = "";
				 }
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
	 *** 상태코드로 상태값 가져오기 (Local)
	 ***********************************************************************************/

 	function getLocalStatDataValue($dbc, $sCode) {
		$this->Init();
		$this->code = $sCode;

		$this->sql = "SELECT erp_state_name FROM "._TBL_STATE_ADMIN." ";
		$this->sql .= "WHERE state_code = '".$this->code."' LIMIT 1";
		$this->rowsData = new CLS_DBQuery($this->sql, $dbc);

		if (!trim($this->rowsData->error)) {
			if ($this->rowsData->num_rows > 0) {
				$nRes = $this->rowsData->data[0]['erp_state_name'];
			} else {
				$nRes = "FAILED";
			}
		} else {
			$nRes = "ERROR";
		}

		return $nRes;
	}


	 /***********************************************************************************
	 *** 카테고리 상품명 가져오기 (Local)
	 ***********************************************************************************/

	 function getLocalCateProductNameValue($dbc, $sCode) {
		$this->Init();
		$this->code = $sCode;

		$this->sql = "SELECT cate_name FROM "._TBL_CATE." ";
		$this->sql .= "WHERE sortcode = '".$this->code."' LIMIT 1";
		$this->rowsData = new CLS_DBQuery($this->sql, $dbc);

		if (!trim($this->rowsData->error)) {
			if ($this->rowsData->num_rows > 0) {
				$nRes = $this->rowsData->data[0]['cate_name'];
			} else {
				$nRes = "FAILED";
			}
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
				$nRes['color_idx'] = $this->rowsData->data[0]['idx'];
				$nRes['side_name'] = $this->rowsData->data[0]['side_name'];
				$nRes['color_name'] = $this->rowsData->data[0]['color_name'];
				$nRes['color_count'] = $this->rowsData->data[0]['color_count'];
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
				$nRes['paper_idx'] = $this->rowsData->data[0]['idx'];
				$nRes['paper_name'] = $this->rowsData->data[0]['name'];
				$nRes['paper_affil'] = $this->rowsData->data[0]['affil'];
				$nRes['paper_bweight'] = $this->rowsData->data[0]['bweight'];
				$nRes['paper_size'] = $this->rowsData->data[0]['size'];
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
				$nRes['work_wsize'] = $this->rowsData->data[0]['work_wsize'];
				$nRes['work_vsize'] = $this->rowsData->data[0]['work_vsize'];
				$nRes['cut_wsize'] = $this->rowsData->data[0]['cut_wsize'];
				$nRes['cut_vsize'] = $this->rowsData->data[0]['cut_vsize'];
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
				 $nRes['od_idx'] = $this->rowsData->data[0]['order_detail_seqno'];
				 $nRes['od_amt'] = $this->rowsData->data[0]['amt'];
				 $nRes['od_count'] = $this->rowsData->data[0]['count'];
				 $nRes['od_amt_unit_dvs'] = $this->rowsData->data[0]['amt_unit_dvs'];
			 } else {
				 $nRes = "FAILED";
			 }
		 } else {
			 $nRes = "ERROR";
		 }

		 return $nRes;
	 }


	 /***********************************************************************************
	  *** 후가공 정보 가져오기 (Local)
	  ***********************************************************************************/

	 function getLocalBasicAfterDataValue($dbc, $OrderNum) {
		 $this->Init();
		 $OrderNum = substr($OrderNum, 0, strlen($OrderNum) - 2);

		 $this->sql = "SELECT after_name AS name FROM "._TBL_ORDER_AFTER_HISTORY." ";
		 $this->sql .= "WHERE order_detail_dvs_num = '".$OrderNum."' AND basic_yn = 'Y'";
		 $this->rowsData = new CLS_DBQuery($this->sql, $dbc);

		 if (!trim($this->rowsData->error)) {
			 if ($this->rowsData->num_rows > 0) {
				 for ($i = 0; $i < $this->rowsData->num_rows; $i++) {
					  $nRes[$i]['name'] = $this->rowsData->data[$i]['name'];
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
	 *** 배송 정보 가져오기 (Local)
	 ***********************************************************************************/

	 function getLocalDeleveryDataValue($dbc, $nIdx) {
		$this->Init();
		$this->idx = $nIdx;

		$this->sql = "SELECT name, tel_num, cell_num, addr, dlvr_way, dlvr_price, invo_cpn ";
		$this->sql .= "FROM "._TBL_ORDER_DELEVERY." ";
		$this->sql .= "WHERE tsrs_dvs = '수신' AND order_common_seqno = '".$this->idx."' LIMIT 1";
		$this->rowsData = new CLS_DBQuery($this->sql, $dbc);

		if (!trim($this->rowsData->error)) {
			if ($this->rowsData->num_rows > 0) {
				$nRes['name'] = $this->rowsData->data[0]['name'];
				$nRes['phone'] = $this->rowsData->data[0]['tel_num'];
				$nRes['mobile'] = $this->rowsData->data[0]['cell_num'];
				$nRes['addr'] = $this->rowsData->data[0]['addr'];
				$nRes['dlvr_way'] = $this->rowsData->data[0]['dlvr_way'];
				$nRes['price'] = $this->rowsData->data[0]['dlvr_price'];
				$nRes['cpn_name'] = $this->rowsData->data[0]['invo_cpn'];
			} else {
				$nRes = "FAILED";
			}
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
		 $this->sql .= "mb.member_id AS member_id, oc.receipt_mng AS receipt_mng ";
		 $this->sql .= "FROM "._TBL_ORDER." AS oc, "._TBL_MEMBERS." AS mb ";
		 $this->sql .= "WHERE oc.member_seqno = mb.member_seqno AND oc.order_num = '".$this->order."' LIMIT 1";
		 $this->rowsData = new CLS_DBQuery($this->sql, $dbc);

		 if (!trim($this->rowsData->error)) {
			 if ($this->rowsData->num_rows > 0) {
				 $nRes['receipt_dvs'] = $this->rowsData->data[0]['receipt_dvs'];
				 $nRes['order_regi_date'] = $this->rowsData->data[0]['order_regi_date'];
				 //$nRes['receipt_regi_date'] = $this->rowsData->data[0]['receipt_regi_date'];
				 $nRes['member_id'] = $this->rowsData->data[0]['member_id'];
				 $nRes['receipt_mng'] = $this->rowsData->data[0]['receipt_mng'];
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

	 function getLocalReceiptMngNameValue($dbc, $name) {
		 $this->Init();

		 $this->sql = "SELECT empl_id FROM "._TBL_EMPL." ";
		 $this->sql .= "WHERE name = '".$name."' AND resign_yn = 'N' LIMIT 1";
		 $this->rowsData = new CLS_DBQuery($this->sql, $dbc);

		 if (!trim($this->rowsData->error)) {;
			 if ($this->rowsData->num_rows > 0) {
				 $nRes = $this->rowsData->data[0]['empl_id'];
			 } else {
				 $nRes = "FAILED";
			 }
		 } else {
			 $nRes = "ERROR";
		 }

		 return $nRes;
	 }


	 /***********************************************************************************
	 ***  회원 정보 가져오기 (Local)
	 ***********************************************************************************/

	 function getLocalMemberDataValue($dbc, $mb_idx) {
		$this->Init();

		$this->sql = "SELECT member_dvs AS mb_dvs, member_id AS mb_id, member_name AS mb_name, ";
		$this->sql .= "tel_num AS mb_phone, cell_num AS mb_mobile, addr AS mb_addr ";
		$this->sql .= "FROM  "._TBL_MEMBERS." ";
		$this->sql .= "WHERE member_seqno = '".$mb_idx."' LIMIT 1";
		$this->rowsData = new CLS_DBQuery($this->sql, $dbc);

		if (!trim($this->rowsData->error)) {
			if ($this->rowsData->num_rows > 0) {
				$nRes['mb_dvs'] = $this->rowsData->data[0]['mb_dvs'];
				$nRes['mb_id'] = $this->rowsData->data[0]['mb_id'];
				$nRes['mb_name'] = $this->rowsData->data[0]['mb_name'];
				$nRes['mb_phone'] = $this->rowsData->data[0]['mb_phone'];
				$nRes['mb_mobile'] = $this->rowsData->data[0]['mb_mobile'];
				$nRes['mb_addr'] = $this->rowsData->data[0]['mb_addr'];
			} else {
				$nRes = "FAILED";
			}
		} else {
			$nRes = "ERROR";
		}

		return $nRes;
	 }


	 /***********************************************************************************
	  *** 고객 정보 가져오기 (Local)
	  ***********************************************************************************/

	 function getLocalCustomerDataValue($dbc, $mb_idx) {
		 $this->Init();

		 $this->sql = "SELECT li.corp_name AS mb_cp_name ";
		 $this->sql .= "FROM  "._TBL_MEMBERS." AS mb, "._TBL_LICENSE_INFO." AS li ";
		 $this->sql .= "WHERE mb.member_seqno = li.member_seqno AND mb.member_seqno = '".$mb_idx."' LIMIT 1";
		 $this->rowsData = new CLS_DBQuery($this->sql, $dbc);

		 if (!trim($this->rowsData->error)) {
			 if ($this->rowsData->num_rows > 0) {
				 $nRes = $this->rowsData->data[0]['mb_cp_name'];
			 } else {
				 $nRes = "FAILED";
			 }
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

		 $this->sql = "SELECT mb.member_dvs AS mb_dvs, mb.member_id AS mb_id, li.corp_name AS cpn_name, mb.member_name AS mb_name, ";
		 $this->sql .= "mb.tel_num AS mb_phone, mb.cell_num AS mb_mobile, mb.addr AS mb_addr ";
		 $this->sql .= "FROM  "._TBL_MEMBERS." AS mb, "._TBL_LICENSE_INFO." AS li, "._TBL_ORDER." AS oc ";
		 $this->sql .= "WHERE mb.member_seqno = li.member_seqno AND mb.member_seqno = oc.member_seqno ";
		 $this->sql .= "AND oc.order_num = '".$this->order."' LIMIT 1";
		 $this->rowsData = new CLS_DBQuery($this->sql, $dbc);

		 if (!trim($this->rowsData->error)) {
			 if ($this->rowsData->num_rows > 0) {
				 $nRes['mb_dvs'] = $this->rowsData->data[0]['mb_dvs'];
				 $nRes['mb_id'] = $this->rowsData->data[0]['mb_id'];
				 $nRes['cpn_name'] = $this->rowsData->data[0]['cpn_name'];
				 $nRes['mb_name'] = $this->rowsData->data[0]['mb_name'];
				 $nRes['mb_phone'] = $this->rowsData->data[0]['mb_phone'];
				 $nRes['mb_mobile'] = $this->rowsData->data[0]['mb_mobile'];
				 $nRes['mb_addr'] = $this->rowsData->data[0]['mb_addr'];
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

	 function getOrderCancelDataCheck($_DBS, $rData) {
		$dbc = $this->DBCon($_DBS);

		$this->Init();

		$this->sql = "SELECT order_common_seqno FROM "._TBL_ORDER." ";
		$this->sql .= "WHERE order_num = '".$rData['order_num']."' AND receipt_mng = '".$rData['work_id']."' ";
		$this->rowsData = new CLS_DBQuery($this->sql, $dbc);

		if (!trim($this->rowsData->error)) {
			if ($this->rowsData->num_rows > 0) {
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
	 *** 주문취소 업데이트
	 ***********************************************************************************/

	 function setOrderCancelDataUpdateComplete($_DBS, $rData) {
		$dbc = $this->DBCon($_DBS);

		$this->Init();

		$this->sql = "UPDATE "._TBL_ORDER." SET del_yn = '1' ";
		$this->sql .= "WHERE order_num = '".$rData['order_num']."' AND receipt_mng = '".$rData['work_id']."' ";
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
	 *** 조판가능질의 체크
	 ***********************************************************************************/

	 function getOrderComposeAvailCheck($_DBS, $rData) {
		$dbc = $this->DBCon($_DBS);

		$this->Init();
		$oNRes = "";
		$sucCount = 0;
		$errCount = 0;

		for ($i = 1; $i <= $rData['ONS']; $i++) {
			  $this->sql = "SELECT order_detail_count_file_seqno FROM "._TBL_ORDER_DETAIL_COUNT_FILE." ";
			  $this->sql .= "WHERE order_detail_file_num = '".$rData['ON'.$i]."' AND state = '"._CYP_STS_CD_READY."' ";
			  //$this->sql .= "AND state <= '"._CYP_STS_CD_GOING."'";
			  $this->rowsData = new CLS_DBQuery($this->sql, $dbc);

			  if (!trim($this->rowsData->error)) {
				  if ($this->rowsData->num_rows <= 0) {
					  $oNRes .= $rData['ON'.$i].";";
				  } else {
					  $sucCount++;
				  }
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

		$dbc->DBClose();

		return $nRes;
	 }


	 /***********************************************************************************
	  *** 조판가능질의 체크
	  ***********************************************************************************/

	 function getOrderComposeAvailCheck2($_DBS, $rData) {
		 $dbc = $this->DBCon($_DBS);

		 $this->Init();
		 $oNRes = "";
		 $sucCount = 0;
		 $errCount = 0;

		 for ($i = 1; $i <= $rData['ONS']; $i++) {
			 $this->sql = "SELECT order_detail_count_file_seqno FROM "._TBL_ORDER_DETAIL_COUNT_FILE." ";
			 $this->sql .= "WHERE order_detail_file_num = '".$rData['ON'.$i]."' AND state = '"._CYP_STS_CD_GOING."' ";
			 //$this->sql .= "AND state <= '"._CYP_STS_CD_GOING."'";
			 $this->rowsData = new CLS_DBQuery($this->sql, $dbc);

			 if (!trim($this->rowsData->error)) {
				 if ($this->rowsData->num_rows <= 0) {
					 $oNRes .= $rData['ON'.$i].";";
				 } else {
					 $sucCount++;
				 }
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

		 $dbc->DBClose();

		 return $nRes;
	 }


	 /***********************************************************************************
	 *** 판번호 PEN 체크
	 ***********************************************************************************/

	 function getPlateEnrolPenCheck($_DBS, $rData) {
		$dbc = $this->DBCon($_DBS);

		$this->Init();

		$this->sql = "SELECT sheet_typset_seqno FROM "._TBL_SHEET_TYPESET." ";
		$this->sql .= "WHERE typset_num = '".$rData['pen']."' LIMIT 1";
		$this->rowsData = new CLS_DBQuery($this->sql, $dbc);

		if (!trim($this->rowsData->error)) {
			if ($this->rowsData->num_rows <= 0) {
				$nRes = "SUCCESS";
			} else {
				$nRes = $this->rowsData->data[0]['sheet_typset_seqno'];
			}
		} else {
			$nRes = "ERROR";
		}

		$dbc->DBClose();

		return $nRes;
	 }


	 /***********************************************************************************
	  *** 조판번호로 기존데이터 삭제처리
	  ***********************************************************************************/

	 function setPlateEnrolPenDataDeleteComplete($_DBS, $nIdx) {
		 $dbc = $this->DBCon($_DBS);

		 $this->Init();
		 $wpath = "/home/dprinting/nimda/cypress/process/logs/plateenrol_".date("Y_m_d");
		 $this->FileWrite($wpath, date("H:i:s")."->".$nIdx."\n", "a+");

		 $this->sql = "DELETE FROM "._TBL_SHEET_TYPESET." WHERE sheet_typset_seqno = '".$nIdx."'";
		 $this->rowsData = new CLS_DBQuery($this->sql, $dbc);

		 $this->FileWrite($wpath, date("H:i:s")."->".$this->sql."\n", "a+");

		 if (!trim($this->rowsData->error)) {
			 $nRes = "SUCCESS";
		 } else {
			 $nRes = "ERROR";
		 }

		 $dbc->DBClose();

		 return $nRes;
	 }


	 /***********************************************************************************
	  *** 조판번호로 기존데이터 파일 삭제처리
	  ***********************************************************************************/

	 function setPlateEnrolPenFileDataDeleteComplete($_DBS, $nIdx) {
		 $dbc = $this->DBCon($_DBS);

		 $this->Init();

		 $this->sql = "DELETE FROM "._TBL_SHEET_TYPESET_FILE." WHERE sheet_typset_seqno = '".$nIdx."'";
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
	  *** 조판번호로 기존데이터 파일 삭제처리
	  ***********************************************************************************/

	 function setPlateEnrolPenPreviewFileDataDeleteComplete($_DBS, $nIdx) {
		 $dbc = $this->DBCon($_DBS);

		 $this->Init();

		 $this->sql = "DELETE FROM "._TBL_SHEET_TYPESET_PREVIEW_FILE." WHERE sheet_typset_seqno = '".$nIdx."'";
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

	 function setPlateEnrolPenoITypeSetInsertDataComplete($_DBS, $rData, $OrderNum) {
		$dbc = $this->DBCon($_DBS);

		$tsRes = $this->getLocalTypeSetDataValue($dbc, $rData['prn']);   // 판정보

		if (is_array($tsRes)) {
			$clrRes = $this->getLocalColorDoCountDataValue($dbc, $tsRes['ts_idx']);  // 전면도수 후면도수

			if ($clrRes != "ERROR") {
				$alRes = $this->getLocalAfterListDataValue($dbc, $tsRes['ts_idx']);  // 후공정리스트

				if ($alRes != "ERROR") {
					$opRes = $this->getLocalOptionListDataValue($dbc, $tsRes['ts_idx']);  // 옵션 리스트

					if ($opRes != "ERROR") {
						$ptRes = $this->getLocalPrintAmtUnitDataValue($dbc, $OrderNum);  // 인쇄 단위

						if ($ptRes != "ERROR") {
							$emRes = $this->getLocalEmployeeSeqDataValue($dbc, $rData['pw']);  // 직원 일련번호

							if ($emRes != "ERROR") {
								//$ppRes = $this->getLocalPaperNDCBDataValue($dbc, $tsRes['ts_idx']);  // 종이 명/구분/색상/평량
								$ppRes = $this->getLocalPaperNDCBDataValue($dbc, $OrderNum);  // 종이 명/구분/색상/평량

								if (is_array($ppRes)) {
									$this->Init();
									$today = date("Y-m-d H:i:s");
									$ts_num = substr( $rData['peno'], 0, strlen( $rData['peno']) - 3);

									if ($rData['pl'] == 2 || $rData['pl'] == 3 || $rData['pl'] == 4) $honggak_yn = "Y";
									else $honggak_yn = "N";

									$this->sql = "INSERT INTO "._TBL_SHEET_TYPESET." (";
									$this->sql .= "typset_num, state, beforeside_tmpt, beforeside_spc_tmpt, aftside_tmpt, aftside_spc_tmpt, ";
									$this->sql .= "honggak_yn, after_list, opt_list, print_amt, print_amt_unit, prdt_page, ";
									$this->sql .= "prdt_page_dvs, dlvrboard, memo, op_typ, op_typ_detail, empl_seqno, typset_format_seqno, ";
									$this->sql .= "paper_name, paper_dvs, paper_color, paper_basisweight, print_title, regi_date , cate_sortcode, ";
									$this->sql .= "oper_sys, save_path, save_yn";
									$this->sql .= ") VALUE (";
									$this->sql .= "'".$ts_num."', '"._CYP_STS_CD_OUTPUT."', '".$clrRes['bf_do']."', '0', '".$clrRes['bc_do']."', '0', ";
									$this->sql .= "'".$honggak_yn."', '".$alRes."', '". $opRes."', ";
									$this->sql .= "'".$rData['pq']."', '".$ptRes."', '".$rData['pn']."', '"._CYP_TYPESET_PAGE_DIV1."', ";
									$this->sql .= "'".$tsRes['ts_dlvrboard']."', '".$tsRes['ts_dscr']."', '"._CYP_TYPESET_AUTO_CONTRACT."', ";
									$this->sql .= "'"._CYP_TYPESET_AUTO_CREATE."', '".$emRes."', '".$tsRes['ts_idx']."', '".$ppRes['pp_name']."', ";
									$this->sql .= "'".$ppRes['pp_dvs']."', '".$ppRes['pp_color']."', '".$ppRes['pp_bw']."', ";
									$this->sql .= "'".$tsRes['ts_name']."', '".$today."', '".$tsRes['ts_cate_sortcode']."', ";
									$this->sql .= "'".$clrRes['bc_op_sys']."', '".$rData['files']."', 'N'";
									$this->sql .= ")";
									$this->rowsData = new CLS_DBQuery($this->sql, $dbc);

									if (!trim($this->rowsData->error)) {
										$nRes = $this->thisLocalProcessFlowInsertComplete($dbc, $ts_num);
									} else {
										$nRes = "ERROR";
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

		$dbc->DBClose();

		return $nRes;
	 }


	 /***********************************************************************************
	  *** 출력 등록 완료 1
	  ***********************************************************************************/

	 function setPlateEnrolPenoIOutPutOPInsertDataComplete($_DBS, $rData) {
		 $dbc = $this->DBCon($_DBS);

		 $tsRes = $this->getLocalTypeSetDataValue($dbc, $rData['prn']);   // 판정보

		 if (is_array($tsRes)) {
			 $clrRes = $this->getLocalColorDoCountDataValue($dbc, $tsRes['ts_idx']);  // 전면도수 후면도수

			 if (is_array($clrRes)) {
				 $totalTmpt = $clrRes['bf_do'] + $clrRes['bc_do'];
				 $opRes = $this->getLocalOutPutOPDataValue($dbc, $tsRes['ts_idx']);   // 출력정보

				 if (is_array($opRes)) {
					 $this->Init();
					 $today = date("Y-m-d H:i:s");
					 $ts_num = substr($rData['peno'], 0, strlen($rData['peno']) - 3);

					 $this->sql = "INSERT INTO " . _TBL_OUTPUT_OP . " (";
					 $this->sql .= "typset_num, name, affil, subpaper, size, amt, amt_unit, memo, board, typ, typ_detail, ";
					 $this->sql .= "regi_date, flattyp_dvs, state, orderer, extnl_brand_seqno";
					 $this->sql .= ") VALUE (";
					 $this->sql .= "'" . $ts_num . "', '" . $opRes['op_name'] . "', '" . $opRes['op_affil'] . "','" . $tsRes['ts_subpaper'] . "', '" . $opRes['op_size'] . "', ";
					 $this->sql .= "'" . $totalTmpt . "', '" . $opRes['op_unit'] . "', '" . $rData['pm'] . "', '" . $opRes['op_board'] . "', ";
					 $this->sql .= "'" . _CYP_TYPESET_AUTO_CONTRACT . "', '" . _CYP_TYPESET_AUTO_CREATE . "', '" . $today . "', 'Y', ";
					 $this->sql .= "'" . _CYP_STS_CD_OUTPUT . "', '" . $rData['pw'] . "', '" . $opRes['op_eb_idx'] . "'";
					 $this->sql .= ")";
					 $this->rowsData = new CLS_DBQuery($this->sql, $dbc);

					 if (!trim($this->rowsData->error)) {
						 $nRes = $this->thisLocalProcessFlowOutputUpdateComplete($dbc, "Y", $ts_num);
					 } else {
						 $nRes = "ERROR";
					 }
				 } else if ($opRes == "FAILED") {
					 $nRes = $this->thisLocalProcessFlowOutputUpdateComplete($dbc, "N", $ts_num);
				 } else {
					 $nRes = $opRes;
				 }
			 }

		 } else {
			 $nRes = $tsRes;
		 }

		 $dbc->DBClose();

		 return $nRes;
	 }


	 /***********************************************************************************
	  *** 인쇄 등록 완료
	  ***********************************************************************************/

	 function setPlateEnrolPenoIPrintOPInsertDataComplete($_DBS, $rData) {
		 $dbc = $this->DBCon($_DBS);

		 $tsRes = $this->getLocalTypeSetDataValue($dbc, $rData['prn']);   // 판정보

		 if (is_array($tsRes)) {
			 $clrRes = $this->getLocalColorDoCountDataValue($dbc, $tsRes['ts_idx']);  // 전면도수 후면도수

			 if (is_array($clrRes)) {
				 $totalTmpt = $clrRes['bf_do'] + $clrRes['bc_do'];
				 $prtRes = $this->getLocalPrintOPDataValue($dbc, $tsRes['ts_idx']);   // 인쇄정보

				 if (is_array($prtRes)) {
					 $this->Init();
					 $today = date("Y-m-d H:i:s");
					 $ts_num = substr($rData['peno'], 0, strlen($rData['peno']) - 3);

					 $this->sql = "INSERT INTO " . _TBL_PRINT_OP . " (";
					 $this->sql .= "typset_num, beforeside_tmpt, beforeside_spc_tmpt, aftside_tmpt, aftside_spc_tmpt, tot_tmpt, ";
					 $this->sql .= "amt, amt_unit, name, affil, size, memo, typ, flattyp_dvs, typ_detail, regi_date, state, orderer, extnl_brand_seqno";
					 $this->sql .= ") VALUE (";
					 $this->sql .= "'".$ts_num."', '".$clrRes['bf_do']."', '0', '".$clrRes['bc_do']."', '0', '".$totalTmpt."', '" . $rData['pq']."', '" . $prtRes['prt_unit'] . "', ";
					 $this->sql .= "'".$prtRes['prt_name']."', '". $prtRes['prt_affil']."', '" . $prtRes['prt_size'] . "', '".$rData['pm']."', '" . _CYP_TYPESET_AUTO_CONTRACT . "', 'Y', '" . _CYP_TYPESET_AUTO_CREATE . "', ";
					 $this->sql .= "'" . $today . "', '" . _CYP_STS_CD_PRINT . "', '" . $rData['pw'] . "', '" . $prtRes['prt_eb_idx'] . "'";
					 $this->sql .= ")";
					 $this->rowsData = new CLS_DBQuery($this->sql, $dbc);

					 if (!trim($this->rowsData->error)) {
						 $nRes = $this->thisLocalProcessFlowPrintUpdateComplete($dbc, "Y", $ts_num);
					 } else {
						 $nRes = "ERROR";
					 }
				 } else if ($prtRes == "FAILED") {
					 $nRes = $this->thisLocalProcessFlowPrintUpdateComplete($dbc, "N", $ts_num);
				 } else {
					 $nRes = $prtRes;
				 }
			 } else {
				 $nRes = $clrRes;
			 }

		 } else {
			 $nRes = $tsRes;
		 }

		 $dbc->DBClose();

		 return $nRes;
	 }


	 /***********************************************************************************
	  *** 후공정 발주 완료
	  ***********************************************************************************/

	 function setPlateEnrolPenoIAfterOPInsertDataComplete($_DBS, $rData) {
		 $dbc = $this->DBCon($_DBS);

		 //$wpath = "/home/dprinting/nimda/cypress/process/logs/plateenrol_".date("Y_m_d");

		 $tsRes = $this->getLocalTypeSetDataValue($dbc, $rData['prn']);   // 판정보

		 //$this->FileWrite($wpath, "-----------------------------------\n", "a+");
		 //$this->FileWrite($wpath,  $tsRes['ts_idx']."\n", "a+");
		 //$this->FileWrite($wpath, "-----------------------------------\n", "a+");

		 if (is_array($tsRes)) {
			 $aftRes = $this->getLocalAfterOPDataValue($dbc, $tsRes['ts_idx']);   // 후공정정보

			 //$this->FileWrite($wpath, "-----------------------------------\n", "a+");
			 //$this->FileWrite($wpath,  date("Y-m-d")." -> ".$aftRes."\n", "a+");
			 //$this->FileWrite($wpath, "-----------------------------------\n", "a+");

			 if (is_array($aftRes) || $aftRes == "FAILED") {
				 $this->Init();
				 $today = date("Y-m-d H:i:s");
				 $ts_num = substr($rData['peno'], 0, strlen($rData['peno']) - 3);
				 $sucCount = 0;
				 $errCount = 0;
				 $aftLen = count($aftRes);

				 for ($i = 0; $i < $aftLen; $i++) {
					 $this->sql = "INSERT INTO " . _TBL_BASIC_AFTER_OP . " (";
					 $this->sql .= "cate_sortcode, seq, after_name, amt, amt_unit, memo, op_typ, op_typ_detail, regi_date, orderer, ";
					 $this->sql .= "state, depth1, depth2, depth3, extnl_brand_seqno, typset_num, flattyp_dvs";
					 $this->sql .= ") VALUE (";

					 if ($aftRes != "FAILED") $this->sql .= "'" . $aftRes[$i]['aft_cate_sortcode'] . "', ";
					 else $this->sql .= "NULL, ";

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

					 //$this->FileWrite($wpath, "-----------------------------------\n", "a+");
					 //$this->FileWrite($wpath, date("Y-m-d") . " -> [" . $aftRes . "]" . $this->sql . "\n", "a+");
					 //$this->FileWrite($wpath, "-----------------------------------\n", "a+");

					 $this->rowsData = new CLS_DBQuery($this->sql, $dbc);

					 if (!trim($this->rowsData->error)) {
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

		 $dbc->DBClose();

		 return $nRes;
	 }


	 /***********************************************************************************
	  *** 종이 등록 완료
	  ***********************************************************************************/

	 function setPlateEnrolPenoIPaperOPInsertDataComplete($_DBS, $rData, $panName) {
		 $dbc = $this->DBCon($_DBS);

		 $tsRes = $this->getLocalTypeSetDataValue($dbc, $panName);   // 판정보

		 if (is_array($tsRes)) {
			 $ppRes = $this->getLocalPaperOPDataValue($dbc, $tsRes['ts_idx']);   // 종이정보

			 if ($ppRes != "ERROR") {
				 $this->Init();
				 $sucCount = 0;
				 $errCount = 0;
				 $today = date("Y-m-d H:i:s");
				 $ts_num = substr( $rData['peno'], 0, strlen( $rData['peno']) - 3);

				 for ($i = 0; $i < count($ppRes); $i++) {
					  $this->sql = "INSERT INTO "._TBL_PAPER_OP." (";
					  $this->sql .= "typset_num, name, grain, amt, amt_unit, memo, typ, typ_detail, orderer, flattyp_dvs, ";
					  $this->sql .= "state, regi_date, extnl_brand_seqno, dvs, color, basisweight ";
					  $this->sql .= ") VALUE (";
					  $this->sql .= "'".$ts_num."', '".$prtRes[$i]['pp_name']."', '".$prtRes[$i]['pp_grain']."', '".$rData['pq']."', ";
					  $this->sql .= "'".$prtRes[$i]['pp_unit']."', '', '"._CYP_TYPESET_AUTO_CONTRACT."', '"._CYP_TYPESET_AUTO_CREATE."', ";
					  $this->sql .= "'"._CYP_AUTORER."', 'Y', '".$rData['pp_state']."', ".$today."', '".$rData['prt_eb_idx']."', ";
					  $this->sql .= "'".$opRes['pp_dvs']."', '".$opRes['pp_color']."', '".$opRes['pp_bw']."'";
					  $this->sql .= ")";
					  $this->rowsData = new CLS_DBQuery($this->sql, $dbc);

					  if (!trim($this->rowsData->error)) {
						  $sucCount++;
					  } else {
						  $errCount++;
					  }
				 }

				 if (count($ppRes) == $sucCount && $errCount == 0) {
					 $nRes = $this->thisLocalProcessFlowPaperUpdateComplete($dbc, $ts_num);
				 } else {
					 $nRes = "ERROR";
				 }

			 } else {
				 $nRes = $ppRes;
			 }

		 } else {
			 $nRes = $tsRes;
		 }

		 $dbc->DBClose();

		 return $nRes;
	 }


	 /***********************************************************************************
	 *** 판형 정보가져오기 (Local)
	 ***********************************************************************************/

	 function getLocalTypeSetDataValue($dbc, $panName)	 {
		 $this->Init();

		 $this->sql = "SELECT typset_format_seqno AS idx, name, affil, subpaper, wid_size, vert_size, dscr, ";
		 $this->sql .= "cate_sortcode, honggak_yn, purp, dlvrboard, process_yn, freeset_name, freeset_cate, format_name, mat ";
		 $this->sql .= "FROM "._TBL_TYPSET_FORMAT." ";
		 $this->sql .= "WHERE name = '".$panName."' LIMIT 1";
		 $this->rowsData = new CLS_DBQuery($this->sql, $dbc);

		 if (!trim($this->rowsData->error)) {
			 if ($this->rowsData->num_rows > 0) {
				 $nRes['ts_idx'] = $this->rowsData->data[0]['idx'];
				 $nRes['ts_name'] = $this->rowsData->data[0]['name'];
				 $nRes['ts_affil'] = $this->rowsData->data[0]['affil'];
				 $nRes['ts_subpaper'] = $this->rowsData->data[0]['subpaper'];
				 $nRes['ts_wid_size'] = $this->rowsData->data[0]['wid_size'];
				 $nRes['ts_vert_size'] = $this->rowsData->data[0]['vert_size'];
				 $nRes['ts_dscr'] = $this->rowsData->data[0]['dscr'];
				 $nRes['ts_cate_sortcode'] = $this->rowsData->data[0]['cate_sortcode'];
				 $nRes['ts_honggak_yn'] = $this->rowsData->data[0]['honggak_yn'];
				 $nRes['ts_purp'] = $this->rowsData->data[0]['purp'];
				 $nRes['ts_dlvrboard'] = $this->rowsData->data[0]['dlvrboard'];
				 $nRes['ts_process_yn'] = $this->rowsData->data[0]['process_yn'];
				 $nRes['ts_frs_name'] = $this->rowsData->data[0]['freeset_name'];
				 $nRes['ts_frs_cate'] = $this->rowsData->data[0]['freeset_cate'];
				 $nRes['ts_fm_name'] = $this->rowsData->data[0]['format_name'];
				 $nRes['ts_mat'] = $this->rowsData->data[0]['mat'];
			 } else {
				 $nRes = "FAILED-TS";
			 }
		 } else {
			 $nRes = "ERROR-TS";
		 }

		 return $nRes;

	 }


	 /***********************************************************************************
	  *** 전면 후면 도수 가져오기 (Local)
	  ***********************************************************************************/

	 function getLocalColorDoCountDataValue($dbc, $ts_idx) {
		 $this->Init();

		 $this->sql = "SELECT pp.beforeside_tmpt AS bf_do, aftside_tmpt AS bc_do, oc.oper_sys AS bc_op_sys ";
		 $this->sql .= "FROM "._TBL_TYPSET_FORMAT." AS tsf, "._TBL_CATE_PRINT." AS cp, "._TBL_PRDT_PRINT." AS pp, "._TBL_ORDER." AS oc ";
		 $this->sql .= "WHERE tsf.cate_sortcode = cp.cate_sortcode AND cp.prdt_print_seqno = pp.prdt_print_seqno ";
		 $this->sql .= "AND tsf.typset_format_seqno = '".$ts_idx."' AND cp.cate_sortcode = oc.cate_sortcode ORDER BY oc.order_common_seqno DESC LIMIT 1";
		 $this->rowsData = new CLS_DBQuery($this->sql, $dbc);

		 if (!trim($this->rowsData->error)) {
			 $nRes['bf_do'] = $this->rowsData->data[0]['bf_do'];
			 $nRes['bc_do'] = $this->rowsData->data[0]['bc_do'];
			 $nRes['bc_op_sys'] = $this->rowsData->data[0]['bc_op_sys'];
		 } else {
			 $nRes = "ERROR";
		 }

		 return $nRes;

	 }


	 /***********************************************************************************
	  *** 후공정 리스트 가져오기 (Local)
	  ***********************************************************************************/

	 function getLocalAfterListDataValue($dbc, $ts_idx) {
		 $this->Init();

		 $this->sql = "SELECT at.name AS name ";
		 $this->sql .= "FROM "._TBL_AFTER." AS at, "._TBL_BASIC_PRODUCE_AFTER." AS bpa ";
		 $this->sql .= "WHERE bpa.typset_format_seqno = '".$ts_idx."' ";
		 $this->sql .= "AND bpa.after_seqno = at.after_seqno ORDER BY name DESC";
		 $this->rowsData = new CLS_DBQuery($this->sql, $dbc);

		 if (!trim($this->rowsData->error)) {
			 for ($i = 0; $i < $this->rowsData->num_rows; $i++) {
				  $nRes .= $this->rowsData->data[$i]['name'].",";
			 }

			 $nRes = substr($nRes, 0, strlen($nRes) - 1);
		 } else {
			 $nRes = "ERROR";
		 }

		 return $nRes;

	 }


	 /***********************************************************************************
	  *** 웁션 리스트 가져오기 (Local)
	  ***********************************************************************************/

	 function getLocalOptionListDataValue($dbc, $ts_idx) {
		 $this->Init();

		 $this->sql = "SELECT op.name AS NAME ";
		 $this->sql .= "FROM "._TBL_OPT." AS op, "._TBL_BASIC_PRODUCE_OPT." AS bpo ";
		 $this->sql .= "WHERE bpo.typset_format_seqno = '".$ts_idx."' ";
		 $this->sql .= "AND bpo.opt_seqno = op.opt_seqno ORDER BY NAME DESC";
		 $this->rowsData = new CLS_DBQuery($this->sql, $dbc);

		 if (!trim($this->rowsData->error)) {
			 for ($i = 0; $i < $this->rowsData->num_rows; $i++) {
				 $nRes .= $this->rowsData->data[$i]['name'].",";
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

	 function getLocalPrintAmtUnitDataValue($dbc, $OrderNum) {
		 $this->Init();
		 $OrderNum = substr($OrderNum, 0, strlen($OrderNum) - 2);

		 $wpath = "/home/dprinting/nimda/cypress/process/logs/plateenrol_".date("Y_m_d");

		 $this->sql = "SELECT amt_unit_dvs AS unit FROM "._TBL_ORDER_DETAIL." WHERE order_detail_dvs_num = '".$OrderNum."' LIMIT 1";
		 $this->rowsData = new CLS_DBQuery($this->sql, $dbc);

		 $this->FileWrite($wpath, "-----------------------------------\n", "a+");
		 $this->FileWrite($wpath,  $this->sql."\n", "a+");
		 $this->FileWrite($wpath, "-----------------------------------\n", "a+");

		 if (!trim($this->rowsData->error)) {
			 $nRes = $this->rowsData->data[0]['unit'];
		 } else {
			 $nRes = "ERROR";
		 }

		 return $nRes;

	 }


	 /***********************************************************************************
	  *** 인쇄 단위 가져오기 (Local)
	  ***********************************************************************************/

	 function getLocalEmployeeSeqDataValue($dbc, $usr_id) {
		 $this->Init();

		 $this->sql = "SELECT empl_seqno FROM "._TBL_EMPL." WHERE empl_id = '".$usr_id."' LIMIT 1";
		 $this->rowsData = new CLS_DBQuery($this->sql, $dbc);

		 if (!trim($this->rowsData->error)) {
			 $nRes = $this->rowsData->data[0]['empl_seqno'];
		 } else {
			 $nRes = "ERROR";
		 }

		 return $nRes;

	 }


	 /***********************************************************************************
	  *** 종이 정보 가져오기 (Local)
	  ***********************************************************************************/

	 function getLocalPaperNDCBDataValue($dbc, $OrderNum) {
		 $this->Init();
		 $OrderNum = substr($OrderNum, 0, strlen($OrderNum) - 2);

		 $this->sql = "SELECT cp.name AS name, cp.dvs AS dvs, cp.color AS color, cp.basisweight AS bw ";
		 $this->sql .= "FROM "._TBL_ORDER_DETAIL." AS od, "._TBL_CATE_PAPER." AS cp ";
		 $this->sql .= "WHERE od.cate_paper_mpcode = cp.mpcode AND od.order_detail_dvs_num = '".$OrderNum."' LIMIT 1";
		 $this->rowsData = new CLS_DBQuery($this->sql, $dbc);

		 if (!trim($this->rowsData->error)) {
			 $nRes['pp_name'] = $this->rowsData->data[0]['name'];
			 $nRes['pp_dvs'] = $this->rowsData->data[0]['dvs'];
			 $nRes['pp_color'] = $this->rowsData->data[0]['color'];
			 $nRes['pp_bw'] = $this->rowsData->data[0]['bw'];
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
			 $nRes['pp_name'] = $this->rowsData->data[0]['name'];
			 $nRes['pp_dvs'] = $this->rowsData->data[0]['dvs'];
			 $nRes['pp_color'] = $this->rowsData->data[0]['color'];
			 $nRes['pp_bw'] = $this->rowsData->data[0]['bw'];
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
				 $nRes['of_fpash'] = $this->rowsData->data[0]['of_fpash'];
				 $nRes['odcf_fpath'] = $this->rowsData->data[0]['odcf_fpath'];
				 $nRes['pre_fpath'] = $this->rowsData->data[0]['pre_fpath'];
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

	 function getLocalOutPutOPDataValue($dbc, $ts_idx) {
		 $this->Init();

		 $this->sql = "SELECT op.name AS name, op.affil AS affil, op.wid_size AS wsize, op.vert_size AS hsize, ";
		 $this->sql .= "op.amt AS amt, op.crtr_unit AS unit, op.board AS board, eb.extnl_brand_seqno AS eb_idx ";
		 $this->sql .= "FROM "._TBL_OUTPUT." AS op, "._TBL_BASIC_PRODUCE_OUTPUT." AS bpo, "._TBL_EXTNL_ETPRS." AS ee, "._TBL_EXTNL_BRAND." AS eb ";
		 $this->sql .= "WHERE bpo.typset_format_seqno = '".$ts_idx."' AND bpo.output_seqno = op.output_seqno ";
		 $this->sql .= "AND bpo.extnl_etprs_seqno = ee.extnl_etprs_seqno AND ee.extnl_etprs_seqno = eb.extnl_etprs_seqno LIMIT 1";
		 $this->rowsData = new CLS_DBQuery($this->sql, $dbc);

		 if (!trim($this->rowsData->error)) {
			 if ($this->rowsData->num_rows > 0) {
				 $nRes['op_name'] = $this->rowsData->data[0]['name'];
				 $nRes['op_affil'] = $this->rowsData->data[0]['affil'];
				 $nRes['op_size'] = $this->rowsData->data[0]['wsize'] . "*" . $this->rowsData->data[0]['hsize'];
				 $nRes['op_amt'] = $this->rowsData->data[0]['amt'];
				 $nRes['op_unit'] = $this->rowsData->data[0]['unit'];
				 $nRes['op_board'] = $this->rowsData->data[0]['board'];
				 $nRes['op_eb_idx'] = $this->rowsData->data[0]['eb_idx'];
			 } else {
				 $nRes = "FAILED";
			 }
		 } else {
			 $nRes = "ERROR";
		 }

		 return $nRes;
	 }


	 /***********************************************************************************
	  *** 인쇄정보 가져오기 (Local)
	  ***********************************************************************************/

	 function getLocalPrintOPDataValue($dbc, $ts_idx) {
		 $this->Init();

		 $this->sql = "SELECT prt.amt AS amt, prt.crtr_unit AS unit, prt.name AS name, prt.affil AS affil, ";
		 $this->sql .= "prt.wid_size AS wsize, prt.vert_size AS hsize, prt.extnl_brand_seqno AS eb_idx ";
		 $this->sql .= "FROM "._TBL_PRINT." AS prt, "._TBL_BASIC_PRODUCE_PRINT." AS bpp ";
		 $this->sql .= "WHERE bpp.typset_format_seqno = '".$ts_idx."' AND bpp.print_seqno = prt.print_seqno LIMIT 1 ";
		 $this->rowsData = new CLS_DBQuery($this->sql, $dbc);

		 if (!trim($this->rowsData->error)) {
			 if ($this->rowsData->num_rows > 0) {
				 $nRes['prt_amt'] = $this->rowsData->data[0]['amt'];
				 $nRes['prt_unit'] = $this->rowsData->data[0]['unit'];
				 $nRes['prt_name'] = $this->rowsData->data[0]['name'];
				 $nRes['prt_affil'] = $this->rowsData->data[0]['affil'];
				 $nRes['prt_size'] = $this->rowsData->data[0]['wsize'] . "*" . $this->rowsData->data[0]['hsize'];
				 $nRes['prt_eb_idx'] = $this->rowsData->data[0]['eb_idx'];
			 } else {
				 $nRes = "FAILED";
			 }
		 } else {
			 $nRes = "ERROR";
		 }

		 return $nRes;
	 }


	 /***********************************************************************************
	  *** 후공정 정보 가져오기 (Local)
	  ***********************************************************************************/

	 function getLocalAfterOPDataValue($dbc, $ts_idx) {
		 $this->Init();

		 $wpath = "/home/dprinting/nimda/cypress/process/logs/plateenrol_".date("Y_m_d");

		 $this->sql = "SELECT oc.cate_sortcode AS cate_sortcode, oc.amt AS amt, oc.amt_unit_dvs amt_unit, oah.after_name AS aft_name, ";
		 $this->sql .= "oah.depth1 AS depth1, oah.depth2 AS depth2, oah.depth3 AS depth3, aft.extnl_brand_seqno AS eb_idx ";
		 $this->sql .= "FROM "._TBL_ORDER." AS oc, "._TBL_ORDER_AFTER_HISTORY." AS oah, "._TBL_AFTER." AS aft, "._TBL_BASIC_PRODUCE_AFTER." AS bpa ";
		 $this->sql .= "WHERE oc.order_common_seqno = oah.order_common_seqno AND oah.after_name = aft.name ";
		 $this->sql .= "AND oah.basic_yn = 'Y' AND bpa.typset_format_seqno = '".$ts_idx."' AND bpa.after_seqno = aft.after_seqno ";
		 $this->sql .= "GROUP BY oah.after_name";
		 $this->rowsData = new CLS_DBQuery($this->sql, $dbc);

		 $this->FileWrite($wpath, "-----------------------------------\n", "a+");
		 $this->FileWrite($wpath,  date("Y-m-d H:i:s").$this->sql."\n", "a+");
		 $this->FileWrite($wpath, "-----------------------------------\n", "a+");

		 if (!trim($this->rowsData->error)) {
			 if ($this->rowsData->num_rows > 0) {
				 for ($i = 0; $i < $this->rowsData->num_rows; $i++) {
					 $nRes[$i]['aft_cate_sortcode'] = $this->rowsData->data[$i]['cate_sortcode'];
					 $nRes[$i]['aft_name'] = $this->rowsData->data[$i]['aft_name'];
					 $nRes[$i]['aft_amt'] = $this->rowsData->data[$i]['amt'];
					 $nRes[$i]['aft_amt_unit'] = $this->rowsData->data[$i]['amt_unit'];
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

	 function getLocalPaperOPDataValue($dbc, $ts_idx) {
		 $this->Init();

		 $this->sql = "SELECT pp.name AS name, pp.affil AS affil, pp.wid_size AS wsize, pp.vert_size AS hsize, bpp.grain AS grain, ";
		 $this->sql .= "pp.crtr_unit AS unit, eb.extnl_brand_seqno AS eb_idx, pp.dvs AS dvs, pp.color AS color, pp.basisweight AS bw ";
		 $this->sql .= "FROM "._TBL_PAPER_OP." AS pp, "._TBL_BASIC_PRODUCE_PAPER." AS bpp, "._TBL_EXTNL_ETPRS." AS ee, "._TBL_EXTNL_BRAND." AS eb ";
		 $this->sql .= "WHERE bpp.typset_format_seqno = '".$ts_idx."' AND bpp.paper_seqno = pp.paper_seqno ";
		 $this->sql .= "AND bpp.extnl_etprs_seqno = ee.extnl_etprs_seqno AND ee.extnl_etprs_seqno = eb.extnl_etprs_seqno";
		 $this->rowsData = new CLS_DBQuery($this->sql, $dbc);

		 if (!trim($this->rowsData->error)) {
			 for ($i = 0; $i < $this->rowsData->num_rows; $i++) {
				  $nRes[$i]['pp_name'] = $this->rowsData->data[$i]['name'];
				  $nRes[$i]['pp_affil'] = $this->rowsData->data[$i]['affil'];
				  $nRes[$i]['pp_size'] = $this->rowsData->data[$i]['wsize']."*".$this->rowsData->data[0]['hsize'];
				  $nRes[$i]['pp_grain'] = $this->rowsData->data[$i]['grain'];
				  $nRes[$i]['pp_unit'] = $this->rowsData->data[$i]['unit'];
				  $nRes[$i]['pp_eb_idx'] = $this->rowsData->data[$i]['eb_idx'];
				  $nRes[$i]['pp_dvs'] = $this->rowsData->data[$i]['eb_dvs'];
				  $nRes[$i]['pp_color'] = $this->rowsData->data[$i]['eb_color'];
				  $nRes[$i]['pp_bw'] = $this->rowsData->data[$i]['eb_bw'];
			 }
		 } else {
			 $nRes = "ERROR";
		 }

		 return $nRes;
	 }


	 /***********************************************************************************
	 *** 프로세스 플로우 (Local)
	 ***********************************************************************************/

	 function thisLocalProcessFlowInsertComplete($dbc, $ts_num) {
		 $this->Init();

		 $this->sql = "SELECT produce_process_flow_seqno FROM "._TBL_PRODUCE_PROCESS_FLOW." ";
		 $this->sql .= "WHERE typset_num = '".$ts_num."' LIMIT 1";
		 $this->rowsData = new CLS_DBQuery($this->sql, $dbc);

		 if (!trim($this->rowsData->error)) {
			 $this->Init();

			 $this->sql = "INSERT INTO "._TBL_PRODUCE_PROCESS_FLOW." SET typset_num = '".$ts_num."'";
			 $this->rowsData = new CLS_DBQuery($this->sql, $dbc);

			 if (!trim($this->rowsData->error)) {
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

	 function thisLocalProcessFlowPaperUpdateComplete($dbc, $ts_num) {
		 $this->Init();

		 $this->sql = "UPDATE "._TBL_PRODUCE_PROCESS_FLOW." SET paper_yn = 'Y' ";
		 $this->sql .= "WHERE typset_num = '".$ts_num."' LIMIT 1";
		 $this->rowsData = new CLS_DBQuery($this->sql, $dbc);

		 if (!trim($this->rowsData->error)) {
			 $nRes = "SUCCESS";
		 } else {
			 $nRes = "ERROR";
		 }

		 return $nRes;
	 }


	 /***********************************************************************************
	 *** 출력 프로세스 업데이트 (Local)
	 ***********************************************************************************/

	 function thisLocalProcessFlowOutputUpdateComplete($dbc, $state, $ts_num) {
		 $this->Init();

		 $this->sql = "UPDATE "._TBL_PRODUCE_PROCESS_FLOW." SET output_yn = '".$state."' ";
		 $this->sql .= "WHERE typset_num = '".$ts_num."' LIMIT 1";
		 $this->rowsData = new CLS_DBQuery($this->sql, $dbc);

		 if (!trim($this->rowsData->error)) {
			 $nRes = "SUCCESS";
		 } else {
			 $nRes = "ERROR";
		 }

		 return $nRes;
	 }


	 /***********************************************************************************
	  *** 인쇄 프로세스 업데이트 (Local)
	  ***********************************************************************************/

	 function thisLocalProcessFlowPrintUpdateComplete($dbc, $state, $ts_num) {
		 $this->Init();

		 $this->sql = "UPDATE "._TBL_PRODUCE_PROCESS_FLOW." SET print_yn = '".$state."' ";
		 $this->sql .= "WHERE typset_num = '".$ts_num."' LIMIT 1";
		 $this->rowsData = new CLS_DBQuery($this->sql, $dbc);

		 if (!trim($this->rowsData->error)) {
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

		 $this->sql = "SELECT sheet_typset_seqno AS idx FROM "._TBL_SHEET_TYPESET." ORDER BY sheet_typset_seqno DESC LIMIT 1";
		 $this->rowsData = new CLS_DBQuery($this->sql, $dbc);

		 if (!trim($this->rowsData->error)) {
			 $nRes = $this->rowsData->data[0]['idx'];
		 } else {
			 $nRes = "ERROR";
		 }

		 return $nRes;
	 }


	 /***********************************************************************************
	  *** 구분 가져오기 (Local)
	  ***********************************************************************************/

	 function getLocalDivDataValue($dbc, $OrderNum) {
		 $this->Init();
		 $OrderNum = substr($OrderNum, 1, strlen($OrderNum) - 5);

		 $this->sql = "SELECT oi.color_name AS clr_name, oi.color_code AS clr_code, oi.nick AS nick ";
		 $this->sql .= "FROM order_common AS oc, order_opt_history AS ooh, opt_info AS oi ";
		 $this->sql .= "WHERE oc.order_common_seqno = ooh.order_common_seqno AND oc.order_num =  '".$OrderNum."' ";
		 $this->sql .= "AND ooh.basic_yn = 'N' AND ooh.opt_name = oi.opt_name ORDER BY oi.seq ASC";
		 $this->rowsData = new CLS_DBQuery($this->sql, $dbc);

		 if (!trim($this->rowsData->error)) {
			 if ($this->rowsData->num_rows > 0) {
				 for ($i = 0; $i < $this->rowsData->num_rows; $i++) {
					  $nRes['opt_clr_name'] .= $this->rowsData->data[$i]['clr_name']."/";
					  $nRes['opt_clr_code'] .= $this->rowsData->data[$i]['clr_code']."/";
					  $nRes['opt_nick'] .= $this->rowsData->data[$i]['nick']."/";
				 }

				 $nRes['opt_clr_name'] = substr($nRes['opt_clr_name'], 0, strlen($nRes['opt_clr_name']) - 1);
				 $nRes['opt_clr_code'] = substr($nRes['opt_clr_code'], 0, strlen($nRes['opt_clr_code']) - 1);
				 $nRes['opt_nick'] = substr($nRes['opt_nick'], 0, strlen($nRes['opt_nick']) - 1);
			 } else {
				 $nRes = "FAILED";
			 }
		 } else {
			 $nRes = "ERROR";
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