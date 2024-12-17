<?
/*
 * Copyright (c) 2017 Nexmotion, Inc.
 * All rights reserved.
 *
 * 영업팀 리스트 에서 회원 선택시 검색회원정보 검색 후 json 반환
 *
 * REVISION HISTORY (reverse chronological order)
 *=============================================================================
 * 2017/04/25 엄준현 생성
 * 2017/09/08 이청산 수정
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

$fb = $fb->getForm();
//$conn->debug = 1;

$member_seqno = $fb["seqno"];
$y = date("Y");
$m = date("m");
$from = $y . '-' . $m . "-01";
$to   = $y . '-' . $m . '-' . date("t", mktime(-1, -1, 0, $m, 0, $y));

$param = array();
$param["member_seqno"] = $member_seqno;

// 회원정보 검색
$member_info = $dao->selectMemberInfo($conn, $param);
$grade_lack_price = calcOrderLackPrice($conn,
                                       $dao,
                                       $member_info["grade"],
                                       $member_info["cumul_sales_price"]);
// 3개월 평균 금액 계산
$param["from"] = $from;
$param["to"]   = $to;
$three_mon_avr_price = calcThreeMonthAveragePrice($conn, $dao, $param);
// 현재 총미수 매출액 등 검색
$cur_mon_price = $dao->selectDaySalesStats($conn, $param);
$cur_mon_oa = $dao->selectDayOaStats($conn, $param);
$sum_oa = intval($cur_mon_oa["period_end_oa"]) +
          intval($cur_mon_oa["carryforward_oa"]);
// 전월 매출액 계산
$param["from"] = date("Y-m-d", strtotime($param["from"] . " -1 month"));
$param["to"]   = date("Y-m-d", strtotime($param["to"] . " -1 month"));
$last_mon_sales = $dao->selectDaySalesStats($conn, $param)["sum_sales_price"];
// 예외회원 정보 검색
if ($member_info["member_typ"] === "예외업체") {

    $excpt_info = $dao->selectExcptMemberInfo($conn, $param);
    // 한도 잔여금액
    $rest_loan_price = intval($excpt_info["loan_limit_price"]) - $sum_oa;
    $loan_useage = 0;
    // 여신한도 소지금액
    if ($rest_loan_price != 0 ) {
        $loan_useage = (doubleval($rest_loan_price) /
                        doubleval($excpt_info["loan_limit_price"])) * 100;
    }
}

$json  = '{';
$json .=   "\"member_name\"         : \"%s\""; // 회원명
$json .=  ",\"office_nick\"         : \"%s\""; // 사내닉네임
$json .=  ",\"member_tel\"          : \"%s\""; // 대표전화
$json .=  ",\"member_cell\"         : \"%s\""; // 대표자 핸드폰
//$json .=  ",\"\" : \"%s\""; // 작업자 번호
$json .=  ",\"accting_tel\"         : \"%s\""; // 결제담당자
$json .=  ",\"member_typ\"          : \"%s\""; // 회원구분
$json .=  ",\"member_grade\"        : \"%s\""; // 회원등급
$json .=  ",\"grade_lack_price\"    : \"%s\""; // 등급부족금액
$json .=  ",\"first_join_date\"     : \"%s\""; // 최초가입일
$json .=  ",\"final_order_date\"    : \"%s\""; // 최근주문일
$json .=  ",\"sum_oa\"              : \"%s\""; // 총미수액
$json .=  ",\"carryforward_oa\"     : \"%s\""; // 이월미수액
$json .=  ",\"sum_sales_price\"     : \"%s\""; // 총매출액
$json .=  ",\"sum_sale_price\"      : \"%s\""; // 에누리액
$json .=  ",\"sum_net_price\"       : \"%s\""; // 순매출액
$json .=  ",\"sum_depo_price\"      : \"%s\""; // 입금액
$json .=  ",\"loan_collect_dvs\"    : \"%s\""; // 결재종류
$json .=  ",\"three_mon_avr_price\" : \"%s\""; // 3개월 평균
$json .=  ",\"last_mon_sales\"      : \"%s\""; // 전월매출액
$json .=  ",\"loan_limit_price\"    : \"%s\""; // 여신한도금액
$json .=  ",\"rest_loan_price\"     : \"%s\""; // 한도 잔여금액
$json .=  ",\"loan_useage\"         : \"%s\""; // 여신한도소지금액
$json .= '}';

echo sprintf($json, $member_info["member_name"]
                  , $member_info["office_nick"]
                  , $member_info["tel_num"]
                  , $member_info["cell_num"]
                  //, '-'
                  , $member_info["accting_tel"]
                  , $member_info["member_typ"]
                  , $member_info["grade"]
                  , $grade_lack_price
                  , $member_info["first_join_date"]
                  , $member_info["final_order_date"]
                  , $sum_oa
                  , $cur_mon_price["period_end_oa"]
                  , $cur_mon_price["sum_sales_price"]
                  , $cur_mon_price["sum_sale_price"]
                  , $cur_mon_price["sum_net_price"]
                  , $cur_mon_price["sum_depo_price"]
                  , $excpt_info["loan_collect_dvs"]
                  , $three_mon_avr_price
                  , $last_mon_sales
                  , $excpt_info["loan_limit_price"]
                  , $rest_loan_price
                  , $loan_useage);

$conn->Close();

/******************************************************************************
 ******************** 함수영역
 ******************************************************************************/
/**
 * @brief 등급부족금액 계산
 *
 * @param $conn             = db connection
 * @param $dao              = dao 객체
 * @param $grades           = 현 회원 등급
 * @param $cumul_sales_pric = 누적매출금액e
 *
 * @return 등급부족금액
 */
function calcOrderLackPrice($conn, $dao, $grade, $cumul_sales_price) {
    $grade = intval($grade);
    if ($grade === 1) {
        return 0;
    }

    $grade--;
    $cumul_sales_price = intval($cumul_sales_price);

    $start_price = $dao->selectMemberGradePolicy($conn, $grade);

    return $start_price - $cumul_sales_price;
}

/**
 * @brief 3개월 평균금액 계산
 *
 * @param $conn  = db connection
 * @param $dao   = dao 객체
 * @param $param = 회원 일련번호, 시작일자, 종료일자
 *
 * @return 3개월 평균 매출액, 전월 매출액
 */
function calcThreeMonthAveragePrice($conn, $dao, $param) {
    $ret = null;

    if (explode('-', $param["from"])[1] !== explode('-', $param["to"])[1]) {
        $ret = "시작월과 종료월이 같아야 계산이 가능합니다.";
        return $ret;
    }

    // 한 달 전
    $param["from"] = date("Y-m-d", strtotime($param["from"] . " -1 month"));
    $param["to"]   = date("Y-m-d", strtotime($param["to"] . " -1 month"));
    $m1 = $dao->selectDaySalesStats($conn, $param)["sum_sales_price"];
    // 두 달 전
    $param["from"] = date("Y-m-d", strtotime($param["from"] . " -1 month"));
    $param["to"]   = date("Y-m-d", strtotime($param["to"] . " -1 month"));
    $m2 = $dao->selectDaySalesStats($conn, $param)["sum_sales_price"];
    // 세 달 전
    $param["from"] = date("Y-m-d", strtotime($param["from"] . " -1 month"));
    $param["to"]   = date("Y-m-d", strtotime($param["to"] . " -1 month"));
    $m3 = $dao->selectDaySalesStats($conn, $param)["sum_sales_price"];

    $ret = intval((intval($m1) + intval($m2) + intval($m3)) / 3);

    return $ret;
}
?>
