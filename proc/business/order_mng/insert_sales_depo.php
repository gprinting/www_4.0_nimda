<?
/*
 * Copyright (c) 2017 Nexmotion, Inc.
 * All rights reserved.
 *
 * 매출액,입금액 등록
 *
 * REVISION HISTORY (reverse chronological order)
 *=============================================================================
 * 2017/07/06 이청산 생성 
 * 2017/07/25 이청산 수정(day_sales_stats 연관 로직 추가)
 *=============================================================================
 */
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/business/order_mng/OrderMngDAO.inc");
include_once(INC_PATH . "/common_lib/CommonUtil.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new OrderMngDAO();
$util = new CommonUtil();

$session= $fb->getSession();
$fb = $fb->getForm();

//$conn->debug = 1;

//회원 일련번호
$member_seqno       = $fb["member_seqno"];
//거래날짜
$deal_date          = $fb["deal_date"];
//입력구분
$dvs_input          = $fb["dvs_input"];
//입력유형
$input_typ          = $fb["input_typ"];
//내용
$cont               = $fb["cont"];
//입금액
$depo_price         = $fb["depo_price"];
//입금구분, 입력세부내용(3자리 코드값) - 사용안함
//$dvs_detail      = $fb["dvs_detail"];
//입력세부내용(가상계좌)
$cont_detail_txt    = $fb["cont_detail_txt"];
//입력세부내용(카드)
$cont_detail_select = $fb["cont_detail_select"];
//카드번호
$card_num           = $fb["card_num"];
//할부개월
$mip_mon            = $fb["mip_mon"];
//승인번호
$aprvl_num          = $fb["aprvl_num"];
//승인일시
$aprvl_date         = $fb["aprvl_date"];

$param = array();
$param["member_seqno"]    = $member_seqno;
// 기존 잔액을 불러옴
$pay_load = $dao->selectPrevBal($conn, $param);

$param["deal_date"]       = $deal_date;
$param["dvs_input"]       = $dvs_input;
$param["input_typ"]       = $input_typ;
$param["cont"]            = $cont;
//$param["dvs_detail"]      = $dvs_detail;
$param["empl_name"]       = $session["name"];

if ($input_typ == '100') {
    $param["cont_detail"] = $cont_detail_txt;
} else if ($input_typ == '102') {
    $param["cont_detail"] = $cont_detail_select;
} else {
    $param["cont_detail"] = '';
}
$pre_bal = $pay_load;

if ($dvs_input == "매출증가" || $dvs_input == "입금감소") {
    $param["depo_price"]      = '0';
    $param["pay_price"]       = $depo_price;
    $total = ((intval($pre_bal) - intval($depo_price)));
    $param["pre_bal"]         = $pre_bal;
    $param["total"]           = $total;
} else if ($dvs_input == "매출감소" || $dvs_input == "입금증가") {
    $param["depo_price"]      = $depo_price;
    $param["pay_price"]       = '0';
    $total = ((intval($pre_bal) + intval($depo_price)));
    $param["pre_bal"]         = $pre_bal;
    $param["total"]           = $total;
}

$conn->StartTrans();

// 매출증감,입금증감
$ret = $dao->insertSalesDepo($conn, $param);

// 2017.07.25 추가(수정 요청사항)
$dvs_top = mb_substr($dvs_input, 0, 2);
$dvs_bot = mb_substr($dvs_input, 2, 2);

if ($dvs_top == "매출") {
    if ($dvs_bot == "증가") {
        $param["modi_price"] =  $depo_price;  
    } else if ($dvs_bot == "감소") {
        $param["modi_price"] = -$depo_price;  
    } 
    $ret_day_sales = $dao->modiSalesDaySalesStats($conn, $param);

    if(!$ret_day_sales) {
        $ret_day_sales = "일매출 입력에 실패했습니다.";
        goto ERR;
    }
} else if ($dvs_top == "입금") {
    if ($dvs_bot == "증가") {
        $param["modi_price"] =  $depo_price; 
    } else if ($dvs_bot == "감소") {
        $param["modi_price"] = -$depo_price; 
    }
    $ret_day_sales = $dao->modiDepoDaySalesStats($conn, $param);

    if(!$ret_day_sales) {
        $ret_day_sales = "일매출 입력에 실패했습니다.";
        goto ERR;
    }
}

//세부정보 등록을 위한 일련번호
$sales_depo_seqno = $conn->Insert_ID();

if ($input_typ == '102') {
    
    $param["history_seqno"] = $sales_depo_seqno;
    $param["card_num"]      = $card_num;
    $param["mip_mon"]       = $mip_mon;
    $param["aprvl_num"]     = $aprvl_num;
    $param["aprvl_date"]    = $aprvl_date;

    $rs = $dao->insertSalesDepoDetailInfo($conn, $param);    
    if (!$rs) {
        $rs = "세부정보 입력에 실패했습니다.";
        goto ERR;
    }
}

if (!$ret) {
    $ret = "입력에 실패했습니다.";
    goto ERR;
}

//member 테이블 선입금 업데이트
$upd = $dao->updateSalesDepoToMember($conn, $param);

if (!$upd) {
    $upd = "업데이트에 실패했습니다.";
    goto ERR;
}


goto END;

ERR:
    $conn->FailTrans();
    $conn->RollbackTrans();
    print_r($ret_day_sales);
    echo $ret . $rs . $upd;

END:
    $conn->CompleteTrans();
    $conn->Close();
?>
