<?
/*
 * Copyright (c) 2017 Nexmotion, Inc.
 * All rights reserved.
 *
 * CRM정보 검색 및 집계 후
 * table html 생성 후 반환
 *
 * REVISION HISTORY (reverse chronological order)
 *=============================================================================
 * 2017/05/18 엄준현 생성
 * 2017/06/05 이청산 수정
 *=============================================================================
 */
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/business/order_mng/OrderMngDAO.inc");
include_once(INC_PATH . "/common_lib/CommonUtil.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();
/*$e3ConnPool = new ConnectionPool("mysqli",
                                 "172.16.33.195",
                                 "root",
                                 "e3ts0408@db",
                                 "E3DP");
$e3Conn = $e3ConnPool->getPooledConnection();
*/
$fb = new FormBean();
$dao = new OrderMngDAO();
$util = new CommonUtil();

$fb = $fb->getForm();

//$conn->debug = 1;

$page = empty($fb["page"]) ? 1 : intval($fb["page"]);

$member_seqno = $fb["member_seqno"];

$param = array();
$param["member_seqno"]= $member_seqno;
$param["depar_name"]  = $fb["depar_name"];
$param["empl_name"]   = $fb["empl_name"];
$param["member_name"] = $fb["member_name"];
$param["from"]        = $fb["from"];
$param["to"]          = $fb["to"];
$param["page"]        = $page;
$param["dvs"]         = $fb["dvs"];
$param["searchTxt"]   = $fb["searchTxt"];

if ($fb["search_dvs"] === "crm_info") {
    $dvs = $fb["dvs"];

    switch ($dvs) {
        case "member_name" :
            unset($param["member_name"]);
            $param["member_name_like"] = $fb["search_txt"];
            break;
    }
}

$page = ($page - 1) * 5;
$rs = $dao->selectCrmInfoSumList($conn, $param, $page);

$json = "{\"thead\" : \"%s\", \"tbody\" : \"%s\", \"result_cnt\" : \"%s\"}";

if ($rs->EOF) {
    $thead_html = '';
    $tbody_html = "<td colspan=\"7\">검색결과없음</td>";

    goto FIN;
}

$result_cnt = '';
if (empty($page_dvs)) {
    $result_cnt = $dao->selectFoundRows($conn);
}

$thead_html = '';
$thead_html = makeCrmInfoTheadHtml(array(
    "result_cnt" => $result_cnt,
));
$tbody_html = makeCrmInfoTbodyHtml($rs, $page);

FIN :
    echo sprintf($json, $util->convJsonStr($thead_html)
                      , $util->convJsonStr($tbody_html)
                      , $result_cnt);

    $conn->Close();
    exit;

/******************************************************************************
 ******************** 공통사용 함수
 ******************************************************************************/

/**
 * @brief CRM정보 thead 생성
 *
 * @param $rs = 검색결과
 *
 * @return tbody_html
 */
function makeCrmInfoTheadHtml($param) {
    $thead_form  = "<th>상담건수</th>";
    $thead_form .= "<th>%s</th>";
    $thead_form .= "<th colspan=\"5\"></th>";

    $thead_html = sprintf($thead_form, $param["result_cnt"]);

    return $thead_html;
}

/** 2017-06-05 리스트 표시방식 변경으로 주석처리
 * @brief CRM정보 tbody 생성
 *
 * @param $rs = 검색결과
 *
 * @return tbody_html
 *
function makeCrmInfoTbodyHtml($rs, $i) {
    $tbody_form  = "<tr>";
    $tbody_form .=     "<td>%s</td>";
    $tbody_form .=     "<td>%s</td>";
    $tbody_form .=     "<td>%s</td>";
    $tbody_form .=     "<td>%s</td>";
    $tbody_form .=     "<td>%s</td>";
    $tbody_form .= "</tr>";

    $tbody_html = '';
    
    while ($rs && !$rs->EOF) {
        $i++;
        $fields = $rs->fields;

        $tbody_html .= sprintf($tbody_form, $i
                                          , $fields["date"]
                                          , $fields["member_name"]
                                          , $fields["empl_name"]
                                          , $fields["cnt"]);

        $rs->MoveNext();
    }

    return $tbody_html;
} */

/** 2017-06-05 리스트 표시방식 변경으로 주석해제
 * @brief CRM정보 tbody 생성
 *
 * @param $rs = 검색결과
 *
 * @return tbody_html
 */
function makeCrmInfoTbodyHtml($rs) {
    $tbody_form  = "<tr style=\"cursor:pointer;\" ";
    $tbody_form .=     "id=\"crm_info_tr_%s\" ";
    $tbody_form .=     "class=\"crm_info_tr\" ";
    $tbody_form .=     "onclick=\"loadCrmCollectDetail.exec('%s');\">";
    $tbody_form .=     "<td>%s</td>";
    $tbody_form .=     "<td>%s</td>"; 
    $tbody_form .=     "<td>%s</td>"; 
    $tbody_form .=     "<td>%s</td>";
    $tbody_form .=     "<td>%s</td>";
    $tbody_form .=     "<td>%s</td>";
    $tbody_form .=     "<td>%s</td>";
    $tbody_form .= "</tr>";

    $tbody_html = '';
    while ($rs && !$rs->EOF) {
        $fields = $rs->fields;

        //$date = explode(' ', $fields["date"])[0];
        $cs_data = $fields["cs_typ"];

        switch($cs_data) {
            case 100 :
                $cs_data = "영업진행중";
                break;
            case 101 :
                $cs_data = "영업대기";
                break;
            case 102 :
                $cs_data = "영업전화중";
                break;
            case 103 :
                $cs_data = "영업전화대기";
                break;
            case 104 :
                $cs_data = "영업완료";
                break;
        }
        $memo_data  = mb_substr($fields["memo"],0,25);
        $memo_length = mb_strlen($memo_data);
        if ($memo_length == 25) {
            $memo_data .= "...";
        }

        $tbody_html .= sprintf($tbody_form, $fields["seqno"]
                                          , $fields["seqno"]
                                          , $fields["seqno"]
                                          , $fields["cs_date"]
                                          , $fields["member_name"]
                                          , $cs_data
                                          , $fields["empl_name"]
                                          , substr($fields["loan_pay_promi_date"],0,10)
                                          , $memo_data);

        $rs->MoveNext();
    }

    return $tbody_html;
}

?>
