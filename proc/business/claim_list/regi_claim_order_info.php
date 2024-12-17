<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/define/nimda/common_config.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/business/claim_mng/ClaimListDAO.inc");
include_once(INC_PATH . "/com/nexmotion/job/front/order/CartDAO.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/front/FrontCommonUtil.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new ClaimListDAO();
$cartDAO = new CartDAO();
$frontUtil = new FrontCommonUtil();
$check = 1;

$conn->StartTrans();
$order_common_seqno = $fb->form("seqno");
$is_today           = $fb->form("is_today");

//$conn->debug=1;

$count = $fb->form("count");
$order_file_seqno = $fb->form("order_file_seqno");

//낱장여부
$rs = $dao->selectFlattypYn($conn, $order_common_seqno);
$flattyp_yn = $rs->fields["flattyp_yn"];

//주문공통 재주문을 위한 정보 SELECT
$reorder_data = $dao->selectReOrder($conn, $order_common_seqno);

$insert_param = array();
//주문번호 새로 생성
$insert_param["order_num"]   = $frontUtil->makeOrderNum($conn,
                                                        $cartDAO,
                                                        $reorder_data->fields["cate_sortcode"]); 
$insert_param["order_state"]       = 1320;     // 주문상태   - 주문대기
$insert_param["claim_yn"]          = "N";      // 클레임여부 - N
$insert_param["receipt_dvs"]       = "Manual"; // 접수구분   - Manual
//가격은 전부 0 
$insert_param["use_point_price"]   = 0;        // 사용 포인트 금액
$insert_param["grade_sale_price"]  = 0;        // 등급 할인 금액
$insert_param["member_sale_price"] = 0;        // 회원 할인 금액
$insert_param["add_after_price"]   = 0;        // 추가 후공정 금액
$insert_param["add_opt_price"]     = 0;        // 추가 옵션 금액
$insert_param["event_price"]       = 0;        // 이벤트 금액
$insert_param["cp_price"]          = 0;        // 쿠폰 금액
$insert_param["sell_price"]        = 0;        // 판매 금액
$insert_param["pay_price"]         = 0;        // 결제 금액
$insert_param["depo_price"]        = 0;        // 입금 금액
$insert_param["order_lack_price"]  = 0;        // 주문 부족 금액

//주문공통 재주문 데이터 입력
$reorder_common = $dao->insertReOrder($conn, $reorder_data, $insert_param);

if (!$reorder_common) {
    $check = 0 . " : 주문공통 재주문 입력에 실패했습니다.";
    goto ERR;
}

// 새 주문공통 seqno
$new_order_common_seqno = $conn->Insert_ID();

//order_file Update (order_common_seqno)
$rs = $dao->selectMemberSeqno($conn, $order_common_seqno);
$member_seqno = $rs->fields["member_seqno"];
$order_num    = $rs->fields["order_num"];
$opt_use_yn   = $rs->fields["opt_use_yn"];

//입력과 수정을 동시에 할 수 없는 문제로 트랜젝션을 잠시 끊음
$conn->CompleteTrans();
$conn->StartTrans();

$param = array();
$param["table"] = "order_file";
$param["col"]["order_common_seqno"] = $new_order_common_seqno;
$param["col"]["member_seqno"] = $member_seqno;
$param["prk"] = "order_file_seqno";
$param["prkVal"] = $order_file_seqno;

$rs = $dao->updateData($conn,$param);
if (!$rs) {
    $check = 0;
}

//입력과 수정을 동시에 할 수 없는 문제로 트랜젝션을 잠시 끊음
$conn->CompleteTrans();
$conn->StartTrans();

//주문 상세 재주문
$rs = "";
$order_detail_dvs_num = "";

//낱장 형일 경우
if ($flattyp_yn == "Y") {
    $param = array();
    $param["table"] = "order_detail";
    $param["col"] = "cate_sortcode, order_detail_dvs_num 
        ,state ,typ ,work_size_wid ,work_size_vert
        ,cut_size_wid ,cut_size_vert ,tomson_size_wid ,tomson_size_vert 
        ,cate_paper_mpcode ,cate_beforeside_print_mpcode ,cate_beforeside_add_print_mpcode
        ,cate_aftside_print_mpcode ,cate_aftside_add_print_mpcode ,cate_output_mpcode
        ,order_detail ,mono_yn ,stan_name ,print_tmpt_name
        ,spc_dscr ,print_purp_dvs ,tot_tmpt ,page_amt
        ,amt ,amt_unit_dvs ,count ,expec_weight
        ,after_use_yn ,prdt_basic_info ,prdt_add_info ,receipt_memo 
        ,receipt_start_date ,receipt_finish_date, side_dvs
        ,tomson_yn";
    $param["where"]["order_common_seqno"] = $order_common_seqno;

    $rs = $dao->selectData($conn, $param);

    $order_detail_dvs_num = $rs->fields["order_detail_dvs_num"];
    $bef = substr($order_detail_dvs_num, 0, 1);
    $aft = substr($order_detail_dvs_num, -2);
    $order_detail_dvs_num = $bef . $order_num . $aft;

    $param = array();
    $param["table"] = "order_detail";
    $param["col"]["order_common_seqno"]   = $new_order_common_seqno;
    $param["col"]["cate_sortcode"]        = $rs->fields["cate_sortcode"];
    $param["col"]["order_detail_dvs_num"] = $order_detail_dvs_num;
    $param["col"]["state"]                = 1320;
    $param["col"]["typ"]                  = $rs->fields["typ"];
    $param["col"]["paper_price"]          = 0;
    $param["col"]["output_price"]         = 0; 
    $param["col"]["print_price"]          = 0;
    $param["col"]["paper_sum_price"]      = 0;
    $param["col"]["output_sum_price"]     = 0;
    $param["col"]["print_sum_price"]      = 0;
    $param["col"]["sell_price"]           = 0;
    $param["col"]["grade_sale_price"]     = 0;
    $param["col"]["member_sale_price"]    = 0;
    $param["col"]["use_point_price"]      = 0;
    $param["col"]["add_after_price"]      = 0;
    $param["col"]["cp_price"]             = 0;
    $param["col"]["pay_price"]            = 0;
    $param["col"]["del_yn"]               = 'N';
    $param["col"]["spc_dscr"]             = $rs->fields["spc_dscr"];
    $param["col"]["work_size_wid"]        = $rs->fields["work_size_wid"];
    $param["col"]["work_size_vert"]       = $rs->fields["work_size_vert"];
    $param["col"]["cut_size_wid"]         = $rs->fields["cut_size_wid"];
    $param["col"]["cut_size_vert"]        = $rs->fields["cut_size_vert"];
    $param["col"]["tomson_size_wid"]      = $rs->fields["tomson_size_wid"];
    $param["col"]["tomson_size_vert"]     = $rs->fields["tomson_size_vert"];
    $param["col"]["cate_paper_mpcode"]    = $rs->fields["cate_paper_mpcode"];
    $param["col"]["cate_beforeside_print_mpcode"]     = $rs->fields["cate_beforeside_print_mpcode"];
    $param["col"]["cate_beforeside_add_print_mpcode"] = $rs->fields["cate_beforeside_add_print_mpcode"];
    $param["col"]["cate_aftside_print_mpcode"]        = $rs->fields["cate_aftside_print_mpcode"];
    $param["col"]["cate_aftside_add_print_mpcode"]    = $rs->fields["cate_aftside_add_print_mpcode"];
    $param["col"]["cate_output_mpcode"]   = $rs->fields["cate_output_mpcode"];
    $param["col"]["order_detail"]         = $rs->fields["order_detail"];
    $param["col"]["mono_yn"]              = $rs->fields["mono_yn"];
    $param["col"]["stan_name"]            = $rs->fields["stan_name"];
    $param["col"]["print_tmpt_name"]      = $rs->fields["print_tmpt_name"];
    $param["col"]["print_purp_dvs"]       = $rs->fields["print_purp_dvs"];
    $param["col"]["tot_tmpt"]             = $rs->fields["tot_tmpt"];
    $param["col"]["page_amt"]             = $rs->fields["page_amt"];
    $param["col"]["amt"]                  = $rs->fields["amt"];
    $param["col"]["amt_unit_dvs"]         = $rs->fields["amt_unit_dvs"];
    $param["col"]["count"]                = $rs->fields["count"];
    $param["col"]["expec_weight"]         = $rs->fields["expec_weight"];
    $param["col"]["after_use_yn"]         = $rs->fields["after_use_yn"];
    $param["col"]["receipt_mng"]          = null;
    $param["col"]["prdt_basic_info"]      = $rs->fields["prdt_basic_info"];
    $param["col"]["prdt_add_info"]        = $rs->fields["prdt_add_info"];
    $param["col"]["receipt_memo"]         = $rs->fields["receipt_memo"];
    $param["col"]["receipt_start_date"]   = null;
    $param["col"]["receipt_finish_date"]  = null;
    $param["col"]["side_dvs"]             = $rs->fields["side_dvs"];
    $param["col"]["tomson_yn"]            = $rs->fields["tomson_yn"];
    $param["col"]["produce_memo"]         = null;
    $param["col"]["typset_way"]           = null;

    $ins = "";
    for ($i=1; $i<=$count; $i++) {
        $ins = $dao->insertData($conn, $param);
    }
    if (!$ins) {
        $check = 0 . " : 낱장형 데이터 입력에 실패하였습니다.";
        goto ERR;
    }

// TODO 책자형일 경우 (테스트 필요)
} else {

    $param = array();
    /*
    $param["table"] = "order_detail_brochure";
    $param["col"] = "cate_sortcode, order_detail_dvs_num
        ,state ,typ ,work_size_wid ,work_size_vert
        ,cut_size_wid ,cut_size_vert ,tomson_size_wid ,tomson_size_vert 
        ,cut_front_wing_size_wid ,cut_front_wing_size_vert ,cut_rear_wing_size_wid ,cut_rear_wing_size_vert
        ,work_front_wing_size_wid ,work_front_wing_size_vert ,seneca_size
        ,cate_paper_mpcode ,cate_beforeside_print_mpcode ,cate_beforeside_add_print_mpcode
        ,cate_aftside_print_mpcode ,cate_aftside_add_print_mpcode ,cate_output_mpcode
        ,order_detail ,mono_yn ,stan_name ,print_tmpt_name";
    $param["where"]["order_common_seqno"] = $order_common_seqno; 
    */
    $param["order_common_seqno"] = $order_common_seqno;

    $rs = $dao->selectOrderDetailBrochure($conn, $param);

    //$rs = $dao->selectData($conn, $param);

    $ins = "";

    while ($rs && !$rs->EOF) {
        $order_detail_dvs_num = $rs->fields["order_detail_dvs_num"];
        $bef = substr($order_detail_dvs_num, 0, 1);
        $aft = substr($order_detail_dvs_num, -2);
        $order_detail_dvs_num = $bef . $order_num . $aft;

        $param = array();
        $param["table"] = "order_detail_brochure";
        $param["col"]["order_common_seqno"]   = $new_order_common_seqno;
        $param["col"]["cate_sortcode"]        = $rs->fields["cate_sortcode"];
        $param["col"]["order_detail_dvs_num"] = $order_detail_dvs_num;
        $param["col"]["state"]                = 1320;
        $param["col"]["typ"]                  = $rs->fields["typ"];
        $param["col"]["paper_price"]          = 0;
        $param["col"]["output_price"]         = 0; 
        $param["col"]["print_price"]          = 0;
        $param["col"]["paper_sum_price"]      = 0;
        $param["col"]["output_sum_price"]     = 0;
        $param["col"]["print_sum_price"]      = 0;
        $param["col"]["sell_price"]           = 0;
        $param["col"]["grade_sale_price"]     = 0;
        $param["col"]["member_sale_price"]    = 0;
        $param["col"]["use_point_price"]      = 0;
        $param["col"]["add_after_price"]      = 0;
        $param["col"]["cp_price"]             = 0;
        $param["col"]["pay_price"]            = 0;
        $param["col"]["del_yn"]               = "N";
        $param["col"]["work_size_wid"]        = $rs->fields["work_size_wid"];
        $param["col"]["work_size_vert"]       = $rs->fields["work_size_vert"];
        $param["col"]["cut_size_wid"]         = $rs->fields["cut_size_wid"];
        $param["col"]["cut_size_vert"]        = $rs->fields["cut_size_vert"];
        $param["col"]["tomson_size_wid"]      = $rs->fields["tomson_size_wid"];
        $param["col"]["tomson_size_vert"]     = $rs->fields["tomson_size_vert"];
        $param["col"]["cut_front_wing_size_wid"]   = $rs->fields["cut_front_wing_size_wid"];
        $param["col"]["cut_front_wing_size_vert"]  = $rs->fields["cut_front_wing_size_vert"];
        $param["col"]["cut_rear_wing_size_wid"]    = $rs->fields["cut_rear_wing_size_wid"];
        $param["col"]["cut_rear_wing_size_vert"]   = $rs->fields["cut_rear_wing_size_vert"];
        $param["col"]["work_front_wing_size_wid"]  = $rs->fields["work_front_wing_size_wid"];
        $param["col"]["work_front_wing_size_vert"] = $rs->fields["work_front_wing_size_vert"];
        $param["col"]["work_rear_wing_size_wid"]   = $rs->fields["work_rear_wing_size_wid"];
        $param["col"]["work_rear_wing_size_vert"]  = $rs->fields["work_rear_wing_size_vert"];
        $param["col"]["seneca_size"]                      = $rs->fields["seneca_size"];
        $param["col"]["cate_paper_mpcode"]                = $rs->fields["cate_paper_mpcode"];
        $param["col"]["cate_beforeside_print_mpcode"]     = $rs->fields["cate_beforeside_print_mpcode"];
        $param["col"]["cate_beforeside_add_print_mpcode"] = $rs->fields["cate_beforeside_add_print_mpcode"];
        $param["col"]["cate_aftside_print_mpcode"]        = $rs->fields["cate_aftside_print_mpcode"];
        $param["col"]["cate_aftside_add_print_mpcode"]    = $rs->fields["cate_aftside_add_print_mpcode"];
        $param["col"]["cate_output_mpcode"] = $rs->fields["cate_output_mpcode"];
        $param["col"]["order_detail"]       = $rs->fields["order_detail"];
        $param["col"]["mono_yn"]            = $rs->fields["mono_yn"];
        $param["col"]["stan_name"]          = $rs->fields["stan_name"];
        $param["col"]["print_tmpt_name"]    = $rs->fields["print_tmpt_name"];
        $param["col"]["spc_dscr"]           = $rs->fields["spc_dscr"];
        $param["col"]["print_purp_dvs"]     = $rs->fields["print_purp_dvs"];
        $param["col"]["tot_tmpt"]           = $rs->fields["tot_tmpt"];
        $param["col"]["page_amt"]           = $rs->fields["page_amt"];
        $param["col"]["amt"]                = $rs->fields["amt"];
        $param["col"]["amt_unit_dvs"]       = $rs->fields["amt_unit_dvs"];
        $param["col"]["expec_weight"]       = $rs->fields["expec_weight"];
        $param["col"]["after_use_yn"]       = $rs->fields["after_use_yn"];
        $param["col"]["receipt_mng"]        = null;
        $param["col"]["prdt_basic_info"]    = $rs->fields["prdt_basic_info"];
        $param["col"]["prdt_add_info"]      = $rs->fields["prdt_add_info"];
        $param["col"]["side_dvs"]           = $rs->fields["side_dvs"];
        $param["col"]["produce_memo"]       = null;

        $ins = $dao->insertData($conn, $param);

        $rs->MoveNext();

    }
    if (!$ins) {
        $check = 0 . " : 책자형 데이터 입력에 실패하였습니다.";
        goto ERR;
    }
 // $dao->selectReOrderDetailBrochure($conn, $param_det);
}

// 후공정
$aft_use = $rs->fields["after_use_yn"];

if ($aft_use == "Y") {

    // 후공정 데이터 검색
    $param_aft = array();
    $param_aft["table"] = "order_after_history";
    $param_aft["col"] = "basic_yn ,after_name ,depth1 
        ,depth2 ,depth3 ,detail ,seq";
    $param_aft["where"]["order_common_seqno"] = $order_common_seqno;
    
    $rs = $dao->selectData($conn, $param_aft);

    $ins = "";
    while ($rs && !$rs->EOF) {

        $param_aft = array();
        $param_aft["table"] = "order_after_history";
        $param_aft["col"]["order_common_seqno"]   = $new_order_common_seqno;
        $param_aft["col"]["order_detail_dvs_num"] = $order_detail_dvs_num;
        $param_aft["col"]["depth1"]               = $rs->fields["depth1"];
        $param_aft["col"]["depth2"]               = $rs->fields["depth2"];
        $param_aft["col"]["depth3"]               = $rs->fields["depth3"];
        $param_aft["col"]["price"]                = 0;
        $param_aft["col"]["basic_yn"]             = $rs->fields["basic_yn"];
        $param_aft["col"]["after_name"]           = $rs->fields["after_name"];
        $param_aft["col"]["seq"]                  = $rs->fields["seq"];
        $param_aft["col"]["detail"]               = $rs->fields["detail"];

        $ins = $dao->insertData($conn, $param_aft);

        $rs->MoveNext();
    }
    if (!$ins) {
        $check = 0 . " : 후공정 데이터 입력에 실패하였습니다.";
        goto ERR;
    }
    
}

// 옵션
if ($opt_use_yn == "Y") {
    
    // 옵션 데이터 검색
    $param_opt = array();
    $param_opt["table"] = "order_opt_history";
    $param_opt["col"] = "opt_name, depth1, depth2
        ,depth3 ,price ,basic_yn ,detail";
    $param_opt["where"]["order_common_seqno"] = $order_common_seqno;

    $rs = $dao->selectData($conn, $param_opt);

    // 옵션 데이터 입력
    $ins = "";
    while ($rs && !$rs->EOF) {
    
        $param_opt = array();
        $param_opt["table"] = "order_opt_history";
        $param_opt["col"]["opt_name"]           = $rs->fields["opt_name"];
        $param_opt["col"]["depth1"]             = $rs->fields["depth1"];
        $param_opt["col"]["depth2"]             = $rs->fields["depth2"];
        $param_opt["col"]["depth3"]             = $rs->fields["depth3"];
        $param_opt["col"]["price"]              = $rs->fields["price"];
        $param_opt["col"]["order_common_seqno"] = $new_order_common_seqno;
        $param_opt["col"]["basic_yn"]           = $rs->fields["basic_yn"];
        $param_opt["col"]["detail"]             = $rs->fields["detail"];

        $ins = $dao->insertData($conn, $param_opt);

        $rs->MoveNext();
    }

    if (!$ins) {
        $check = 0 . " : 옵션 데이터 입력에 실패하였습니다.";
        goto ERR;
    }

    // 재주문 시 당일판 체크가 있을 경우
    if ($is_today == 1) { 
        $param_tod = array();
        $param_tod["table"] = "order_opt_history";     
        $param_tod["col"] = "opt_name";
        $param_tod["where"]["order_common_seqno"] = $new_order_common_seqno;
        $param_tod["where"]["opt_name"] = "당일판";

        $rs = $dao->selectData($conn, $param_tod);

        // 당일판 체크가 되어있으나 기존에 데이터가 없는 경우만 입력
        if (!$rs) {

            $param_tod = array();
            $param_tod["table"] = "order_opt_history";     
            $param_tod["col"]["opt_name"]           = "당일판";
            $param_tod["col"]["depth1"]             = "-";
            $param_tod["col"]["depth2"]             = "-";
            $param_tod["col"]["depth3"]             = "-";
            $param_tod["col"]["price"]              = 0; 
            $param_tod["col"]["order_common_seqno"] = $new_order_common_seqno;
            $param_tod["col"]["basic_yn"]           = 'N'; 

            $ins = $dao->insertData($conn, $param_tod);
            if (!$ins) {
                $check = 0 . " : 당일판 입력에 실패했습니다";
            }
        }
    }
}

// 주문 배송
$param_dlvr = array();
$param_dlvr["table"] = "order_dlvr";
$param_dlvr["col"] = "tsrs_dvs ,name ,tel_num ,cell_num
    ,addr ,addr_detail ,zipcode ,order_common_seqno
    ,sms_yn ,dlvr_way ,dlvr_sum_way ,dlvr_price
    ,invo_num ,invo_cpn ,bun_dlvr_order_num ,bun_group
    ,lump_count ,release_date";
$param_dlvr["where"]["order_common_seqno"] = $order_common_seqno;

$rs = $dao->selectData($conn, $param_dlvr);

$ins = "";
while ($rs && !$rs->EOF) {
    $param_dlvr = array();
    $param_dlvr["table"] = "order_dlvr";
    $param_dlvr["col"]["tsrs_dvs"]           = $rs->fields["tsrs_dvs"];
    $param_dlvr["col"]["name"]               = $rs->fields["name"];
    $param_dlvr["col"]["tel_num"]            = $rs->fields["tel_num"];
    $param_dlvr["col"]["cell_num"]           = $rs->fields["cell_num"];
    $param_dlvr["col"]["addr"]               = $rs->fields["addr"];
    $param_dlvr["col"]["addr_detail"]        = $rs->fields["addr_detail"];
    $param_dlvr["col"]["zipcode"]            = $rs->fields["zipcode"];
    $param_dlvr["col"]["order_common_seqno"] = $new_order_common_seqno;
    $param_dlvr["col"]["sms_yn"]             = $rs->fields["sms_yn"];
    $param_dlvr["col"]["dlvr_way"]           = $rs->fields["dlvr_way"];
    $param_dlvr["col"]["dlvr_sum_way"]       = $rs->fields["dlvr_sum_way"];
    $param_dlvr["col"]["dlvr_price"]         = 0;
    $param_dlvr["col"]["invo_num"]           = $rs->fields["invo_num"];
    $param_dlvr["col"]["invo_cpn"]           = $rs->fields["invo_cpn"];
    $param_dlvr["col"]["bun_dlvr_order_num"] = $rs->fields["bin_dlvr_order_num"];
    $param_dlvr["col"]["bun_group"]          = $rs->fields["bun_group"];
    $param_dlvr["col"]["lump_count"]         = $rs->fields["lump_count"];
    $param_dlvr["col"]["release_date"]       = null;

    $ins = $dao->insertData($conn, $param_dlvr);

    $rs->MoveNext();
}
if (!$ins) {
    $check = 0 . "주문 배송정보 입력에 실패했습니다.";
    goto ERR;
}


// 주문 처리
$param = array();
$param["table"] = "order_claim";
$param["col"]["count"] = $fb->form("count");
$param["col"]["order_yn"] = "Y";
$param["prk"] = "order_common_seqno";
$param["prkVal"] = $order_common_seqno;

$rs = $dao->updateData($conn,$param);

if (!$rs) {
    $check = 0 . " : 주문처리가 실패했습니다.";
    goto ERR;
}

goto END;

ERR:
    $conn->FailTrans();
    $conn->RollbackTrans();
    echo $check;

END:
    $conn->CompleteTrans();
    $conn->Close();
    echo $check;
?>
