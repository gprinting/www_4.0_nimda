<?
/*
 * Copyright (c) 2017 Nexmotion, Inc.
 * All rights reserved.
 *
 * 영업팀 리스트 검색/집계 후
 * table html 생성 후 반환
 *
 * REVISION HISTORY (reverse chronological order)
 *=============================================================================
 * 2017/04/20 엄준현 생성
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

// 기본 검색정보에서 선택한 정보
$cpn_admin_seqno  = $fb["cpn_admin_seqno"];
$basic_from       = $fb["basic_from"];
$basic_to         = $fb["basic_to"];
$depar            = $fb["depar"];
$empl             = $fb["empl"];
//$oper_sys        = $fb["oper_sys"];
//$pro             = $fb["pro"];
$member_typ       = $fb["member_typ"];
$member_grade     = $fb["member_grade"];
$search_dvs       = $fb["search_dvs"];
$search_keyword   = $fb["search_keyword"];
$business_typ     = $fb["business_typ"];
$dlvr_dvs         = $fb["dlvr_dvs"];
$dlvr_code        = $fb["dlvr_code"];
// 집계리스트에서 회원명 검색
$stat_member_name = $fb["stat_member_name"];
// 페이징용
$page        = empty($fb["page"]) ? 1 : intval($fb["page"]);

// 미수금 유무
$oa_yn           = $fb["oa_yn"];

$first_seqno = $fb["first_seqno"];
$last_seqno  = $fb["last_seqno"];
// 페이지 이동인지 아닌지 구분값, SQL_CALC_FOUND_ROWS 사용때문에 필요
$page_dvs    = $fb["page_dvs"];


$param = array();
$param["first_seqno"]     = $first_seqno;
$param["last_seqno"]      = $last_seqno;
$param["cpn_admin_seqno"] = $cpn_admin_seqno;
$param["depar"]           = $depar;
$param["empl"]            = $empl;
$param["member_typ"]      = $member_typ;
$param["grade"]           = $member_grade;
$param["dlvr_dvs"]        = $dlvr_dvs;
$param["dlvr_code"]       = $dlvr_code;
$param["to"]              = $basic_to;
$param["from"]            = $basic_from;
$param["page_dvs"]        = $page_dvs;
$param[$search_dvs]       = $search_keyword;

$page_count = ($page - 1) * 5;

if (!empty($oa_yn)) {
    // TODO 미수금 관련 판별
    $oa_rs = $dao->selectMemberHasOa($conn, $param, $page_count, $oa_yn);
    //$member_seq_arr = array();
    if (!$oa_rs || $oa_rs->EOF) {
        $ret  = "<tr style=\"height:170px;\">";
        $ret .= "<td colspan='10' style=\"text-align:center;\">검색결과없음</td>";
        $ret .= "</tr>";
    
        $json = "{\"list\" : \"%s\", \"total_cnt\" : \"%s\", \"result_cnt\" : \"%s\"}";
    
        $result_cnt = '';
        $total_cnt = '';
        if (empty($page_dvs)) {
            $result_cnt = $dao->selectFoundRows($conn);
            $total_cnt  = $dao->selectMemberCount($conn);
        }
    
        echo sprintf($json, $util->convJsonStr($ret)
                          , $total_cnt 
                          , $result_cnt);
        exit;
    }
    
    while(!$oa_rs->EOF) {
        $fields = $oa_rs->fields;
        $member_seq_arr[] = $fields["member_seqno"];
        
        $oa_rs->MoveNext();
    }
    
    $param["member_seqno"] = $dao->arr2paramStr($conn, $member_seq_arr);
    $param_rs = $param["member_seqno"];
}

if (!empty($stat_member_name)) {
    unset($param["empl"]);
    $param["member_name"] = $stat_member_name;
}

// 미수금 판별값이 0이 아니면 $page=0
/*if (!empty($oa_rs)) {
    //$page = 0;
}*/

// 키워드 검색 조건에 따라서 먼저 검색하는 테이블이 틀려짐
if ($search_dvs === "title" && !empty($search_keyword)) {
    // order_common에서 기간으로 끊어서 member_seqno 검색
    $rs = $dao->selectOrderCommon2Member($conn, $param, $page_count);
} else if ($search_dvs === "receiver" && !empty($search_keyword)) {
    // order_dlvr에서 수신으로 검색 후 order_common으로 join 후 기간으로 끊음
    $rs = $dao->selectOrderDlvr2Member($conn, $param, $page_count);
} else if ($search_dvs === "virt_ba" && !empty($search_keyword)) {
    $rs = $dao->selectVirtBaAdmin2Member($conn, $param, $page_count);
} else /*if ($search_dvs === "office_nick" ||
        $search_dvs === "member_name" ||
        $search_dvs === "member_tel" ||
        $search_dvs === "member_cell" ||
        $search_dvs === "member_addr")*/ {
    // 팀, 담당자 정보로 member_mng에서 member로 join
    $rs = $dao->selectMemberMng2Member($conn, $param, $page_count);
}

$result_cnt = '';
$total_cnt = '';
if (empty($page_dvs)) {
    $result_cnt = $dao->selectFoundRows($conn);
    $total_cnt  = $dao->selectMemberCount($conn);
}

$list_html = makeListHtml($conn, $dao, $rs, array("to" => $basic_to,
                                                  "from" => $basic_from), $page);

$json = "{\"list\" : \"%s\", \"total_cnt\" : \"%s\", \"result_cnt\" : \"%s\"}";
echo sprintf($json, $util->convJsonStr($list_html)
                  , $total_cnt
                  , $result_cnt);

$conn->Close();
exit;

/******************************************************************************
 ******************** 공통사용 함수
 ******************************************************************************/
/**
 * @brief 회원 집계리스트 tbody html 생성
 *
 * @param $conn  = db connection
 * @param $dao   = dao 객체
 * @param $rs    = 회원 일련번호 검색결과
 * @param $param = 시작일, 종료일
 *
 * @return tbody html
 */
function makeListHtml($conn, $dao, $rs, $param, $page) {
    $ret = '';

    $tr_form  = "\n <tr style=\"cursor:pointer;\" ";
    $tr_form .=            "id=\"member_stats_tr_%s\" ";
    $tr_form .=            "class=\"member_stats_tr %s\" ";
    $tr_form .=            "onclick=\"loadMemberStatsInfo.exec('%s', '%s', '%s');\">";
    $tr_form .= "\n     <td>%s</td>"; // No
    $tr_form .= "\n     <td style=\"text-align:left;\">%s %s</td>"; // 회원명
    $tr_form .= "\n     <td style=\"text-align:right;\">%s</td>"; // 총미수액
    $tr_form .= "\n     <td style=\"text-align:right;\">%s</td>"; // 에누리
    $tr_form .= "\n     <td style=\"text-align:right;\">%s</td>"; // 순매출액
    $tr_form .= "\n     <td style=\"text-align:right;\">%s</td>"; // 입금액
    $tr_form .= "\n     <td style=\"text-align:right;\">%s</td>"; // 사용가능금액
    $tr_form .= "\n     <td style=\"text-align:right;\">%s</td>"; // 기말 미수액
    $tr_form .= "\n     <td><button type=\"button\" class=\"btn_yellow\" data-reveal-id=\"manu_limit_modal\" data-animation=\"none\" onclick=\"showManuLimitModal('%s', '%s');\">한도설정</button></td>"; // 생산투입한도조회
    $tr_form .= "\n     <td><button type=\"button\" class=\"btn_yellow\" onclick=\"showSalesDepoModal('%s', '%s')\">금액조정</button></td>"; // 매출액/입금액 조정
    $tr_form .= "\n </tr>";

    if (!$rs || $rs->EOF) {
        $ret  = "<tr style=\"height:170px;\">";
        $ret .= "<td colspan='10' style=\"text-align:center;\">검색결과없음</td>";
        $ret .= "</tr>";
        return $ret;
    }

    $i = 0;
    $page_block = ($page * 5) - 4;
    $num = 0;
    while (!$rs->EOF) {
        $fields = $rs->fields;
        $param["member_seqno"] = $fields["member_seqno"];

        $num = $page_block;

        if ($num % 2 == 0) {
            $class = "cellbg";
        } else if ($num % 2 == 1) {
            $class = "";
        }

        $stats_rs = $dao->selectDaySalesStats($conn, $param);
        $prepay_price = $dao->selectMemberPrepayPrice($conn,
                                                      $fields["member_seqno"]);

        $sum_sales_price = doubleval($stats_rs["sum_sales_price"]);
        $sum_sale_price  = doubleval($stats_rs["sum_sale_price"]);
        $sum_net_price   = doubleval($stats_rs["sum_net_price"]);
        $sum_depo_price  = doubleval($stats_rs["sum_depo_price"]);
        $period_end_oa   = doubleval($stats_rs["period_end_oa"]);
        $sum_oa = doubleval($period_end_oa) +
                  doubleval($stats_rs["carryforward_oa"]);

        $page_block++;

        $office_nick = "";
        if ($fields["office_nick"]) {
            $office_nick = "[" . $fields["office_nick"]  . "]";
        }

        $ret .= sprintf($tr_form, $fields["member_seqno"]
                                , $class
                                , $fields["member_seqno"]
                                , $fields["member_name"]
                                , $office_nick
                                , $fields["member_seqno"] // No
                                , $fields["member_name"] // 회원명
                                , $office_nick
                                , number_format($sum_oa) // 총미수액
                                , number_format($sum_sale_price) // 에누리
                                , number_format($sum_net_price) // 순매출액
                                , number_format($sum_depo_price) // 입금액
                                , number_format($prepay_price)
                                , number_format($period_end_oa) // 기말미수액
                                , $fields["member_seqno"] // 생산투입한도조회
                                , $fields["member_name"] // 회원명
                                , $fields["member_seqno"] // 매출액/입금액 조정
                                , $fields["member_name"] // 회원명
                                );

        $rs->MoveNext();
        $i++;
    }

    if ($i < 5) {
        $height = 34 * (5 - $i);
        $ret .= "<tr style=\"height:" . $height . "px;\">";
        $ret .= "<td colspan=\"10\"></td></tr>";
    }

    return $ret;
}
?>
