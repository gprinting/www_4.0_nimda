<?
/*
 * Copyright (c) 2017 Nexmotion, Inc.
 * All rights reserved.
 *
 * 생산투입 한도설정 입력
 *
 * 1. 회원의 입금대기 상품 검색
 * 2. 1의 주문상태 접수대기로 변경 / 주문 부족금액 0으로 변경
 *
 * 기존 엔진에서는 회원의 주문부족금액과 주문의 주문부족금액을 같이 차감하나
 * 여기서는 주문의 주문부족금액만 차감하므로 예외회원이 아니면서
 * 회원의 주문부족금액은 남아있고 주문의 주문부족금액이 없다면 미입금
 * 둘 다 주문부족금액이 없다면 입금상태로 판별한다
 *
 * REVISION HISTORY (reverse chronological order)
 *=============================================================================
 * 2017/07/04 이청산 생성 
 * 2017/07/25 이청산 수정(수정사항 반영)
 * 2017/07/26 엄준현 수정(로직 추가)
 *=============================================================================
 */
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/business/order_mng/OrderMngDAO.inc");
include_once(INC_PATH . "/common_lib/CommonUtil.inc");
include_once(INC_PATH . "/define/nimda/order_mng_define.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new OrderMngDAO();
$util = new CommonUtil();

$session = $fb->getSession();
$fb = $fb->getForm();

$state_arr = $session["state_arr"];

//담당자는 로그인한 사람

//$conn->debug = 1;

//회원 일련번호
$member_seqno    = $fb["member_seqno"];
//제한금액
$limit_price     = intval($fb["limit_price"]);
//거래일자
$deal_date       = $fb["deal_date"];
//담당자
//$regi_empl       = $fb["regi_empl"];
$regi_empl       = $session["name"];
//조정상품
//$limit_cate      = $fb["limit_cate"];
//내용
$memo            = $fb["memo"];
//출고직원
//$release_empl    = $fb["release_empl"];
//입금여부
//$depo_yn         = $fb["depo_yn"];
//입금약속일자
$depo_promi_date = $fb["depo_promi_date"];

// 명함/전단 출고자
$resp_arr = $dao->selectNcBlReleaseResp($conn);

$param = array();
$param["member_seqno"] = $member_seqno;

// 입금대기 주문 검색
$param["order_state"] = $state_arr["입금대기"];
$order_rs = $dao->selectOrderCommonDepoWait($conn, $param);

$conn->StartTrans();

unset($param);
$insert_data_arr = [];

$param["order_state"] = $state_arr["접수대기"];
while ($order_rs && !$order_rs->EOF) {
    $fields = $order_rs->fields;

    $cate_top = $fields["cate_top"];
    $order_lack_price = intval($fields["order_lack_price"]);

    if (($limit_price -= $order_lack_price) >= 0) {
        // 한도금액 내의 입금대기 주문만 접수대기로 변경
        $param["order_common_seqno"] = $fields["order_common_seqno"];
        $dao->updateOrderCommonDepoWait($conn, $param);

        if ($cate_top === "001" || $cate_top === "002") {
            $release_empl = $resp_arr["명함출고팀"];
        } else {
            $release_empl = $resp_arr["전단출고팀"];
        }

        if (empty($insert_data_arr[$cate_top])) {
            $insert_data_arr[$cate_top]["member_seqno"]    = $member_seqno;
            $insert_data_arr[$cate_top]["limit_price"]     = $order_lack_price;
            $insert_data_arr[$cate_top]["deal_date"]       = $deal_date;
            $insert_data_arr[$cate_top]["depo_promi_date"] = $depo_promi_date;
            $insert_data_arr[$cate_top]["regi_empl"]       = $regi_empl;
            $insert_data_arr[$cate_top]["limit_cate"]      = $cate_top;
            $insert_data_arr[$cate_top]["memo"]            = $memo;
            $insert_data_arr[$cate_top]["release_empl"]    = $release_empl;
            $insert_data_arr[$cate_top]["depo_yn"]         = "01";
        } else {
            $insert_data_arr[$cate_top]["limit_price"] += $order_lack_price;
        }

    } else {
        // 넘치는 부분은 제외, 나중에 예외처리 하거나 하면 이쪽에 추가
    }

    $order_rs->MoveNext();
}

foreach ($insert_data_arr as $param) {
    $ret = $dao->insertManuLimit($conn, $param);

    if (!$ret) {
        $ret = "입력에 실패했습니다.";
        goto ERR;
    }
}


goto END;

ERR:
    $conn->FailTrans();
    $conn->RollbackTrans();
    echo $ret;

END:
    $conn->CompleteTrans();
    $conn->Close();
?>
