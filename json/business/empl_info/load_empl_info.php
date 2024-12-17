<?php
/*
 * Copyright (c) 2017 Nexmotion, Inc.
 * All rights reserved.
 *
 * 직원관리 리스트 생성
 *
 * REVISION HISTORY (reverse chronological order)
 *=============================================================================
 * 2017/09/25 이청산 생성
 *=============================================================================
 */
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/common_lib/CommonUtil.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/business/empl_info/EmplInfoDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new EmplInfoDAO();
$util = new CommonUtil();

$fb = $fb->getForm();

//$conn->debug = 1;

$page = empty($fb["page"]) ? 1 : intval($fb["page"]);

$search_keyword = $fb["search_keyword"];

$param = array();
$param["search_keyword"]  = $search_keyword;

$page_count = ($page - 1) * 5;

$rs = $dao->selectEmplInfo($conn, $param, $page_count);

$json = "{\"list\" : \"%s\", \"total\" : \"%s\", \"result_cnt\" : \"%s\"}";

if ($rs->EOF) {
    $list  = "<tr>";
    $list .= "<td colspan=13 style=\"text-align:center\">검색결과없음</td>";
    $list .= "</tr>";
    $result_cnt = 0;

    goto FIN;
}

$result_cnt = '';
if (empty($page_dvs)) {
    $result_cnt = $dao->selectFoundRows($conn);
}

$total = '';
$total = makeEmplListTotalHtml(array(
    "result_cnt" => $result_cnt,
));
$list = makeEmplListByHtml($rs, $page, $conn, $dao);

FIN :
    echo sprintf($json, $util->convJsonStr($list)
                      , $util->convJsonStr($total)
                      , $result_cnt);

    $conn->Close();
    exit;


/*************************************함수 영역 ********************************************/

/**
 * @brief 직원 리스트 총계 생성
 *
 * @param $rs = 검색결과
 *
 * @return total_html
 */
function makeEmplListTotalHtml($param) {
    $total_form  = "<th class=\"th_table_accent\"></th>";
    $total_form .= "<th class=\"th_table_accent\">TOTAL</th>";
    $total_form .= "<th class=\"th_table_accent\">%s</th>";
    $total_form .= "<th class=\"th_table_accent\" colspan=\"10\"></th>";

    $total_html = sprintf($total_form, $param["result_cnt"]);

    return $total_html;
}

/**
 * @brief 직원 리스트 생성
 *
 * @param $rs = 검색결과
 *
 * @return list_html
 */
function makeEmplListByHtml($rs, $page, $conn, $dao) {

    $list_form .= "<tr class='%s'>";
    $list_form .= "   <td><input type=\"checkbox\" id=\"empl_chk_%s\" name=\"empl_chk\"></td>";
    $list_form .= "   <td>%s</td>";
    $list_form .= "   <td>%s</td>";
    $list_form .= "   <td>%s / %s</td>";
    $list_form .= "   <td>%s</td>";
    $list_form .= "   <td>%s</td>";
    $list_form .= "   <td>%s</td>";
    $list_form .= "   <td>%s</td>";
    $list_form .= "   <td>%s</td>";
    $list_form .= "   <td>%s</td>";
    $list_form .= "   <td>%s</td>";
    $list_form .= "   <td>%s</td>";
    $list_form .= "   <td><button type=\"button\" class=\"orge btn_pu btn fix_height20 fix_width75\" onclick=\"modiEmpl('%s');\">수정</button></td>";
    $list_form .= "</tr>";

    $list_html  = '';

    $page_block = ($page * 5) - 4;
    $class = "";
    $num = 0;

    $param = array();

    while ($rs && !$rs->EOF) {

        $fields = $rs->fields;

        // 부서 확인
        $depar_name = "";
        $param["depar_code"] = $fields["depar_code"];
        if (empty($param["depar_code"])) {
            $depar_name = "없음";
        } else {
            $rs_depar = $dao->selectEmplDepar($conn, $param);
            $depar_name = $rs_depar->fields["depar_name"];
        }

        // 직급 확인
        $posi_name = "";
        $param["posi_code"] = $fields["posi_code"];
        if (empty($param["posi_code"])) {
            $posi_name = "사원";
        } else {
            $rs_posi = $dao->selectEmplPosition($conn, $param);
            $posi_name = $rs_posi->fields["posi_name"];
        }


        $num = $page_block;
        if ($num % 2 == 0) {
            $class = "cellbg";
        } else if ($num % 2 == 1) {
            $class = "";
        }
        $page_block++;

        $list_html .= sprintf($list_form, $class
                                        , $fields["empl_seqno"] // 일련번호
                                        , $fields["empl_num"]   // 사번
                                        , $depar_name           // 부서 
                                        , $fields["name"]       // 이름
                                        , $posi_name            // 직급
                                        , ""                    // 이메일
                                        , "수신"                // 수신여부
                                        , ""                    // 휴대폰
                                        , ""                    // 전화번호
                                        , ""                    // 상태
                                        , $fields["admin_auth"] // 권한
                                        , ""                    // 최종접속
                                        , ""                    // 가입일
                                        , $fields["empl_seqno"]);                  // 관리
        $rs->moveNext();
    }

    return $list_html;
}

?>
