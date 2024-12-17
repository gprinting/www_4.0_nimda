<?
/*
 * Copyright (c) 2017 Nexmotion, Inc.
 * All rights reserved.
 *
 * 주문정보 검색 및 집계 후
 * table html 생성 후 반환
 *
 * REVISION HISTORY (reverse chronological order)
 *=============================================================================
 * 2017/05/18 이청산 생성
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

$member_seqno = $fb["seqno"];
$searchDay    = $fb["searchDay"];
$cate_sortcode = null;

$param = array();
$param["cate_sortcode"] = $cate_sortcode;
$param["member_seqno"] = $member_seqno;
$param["searchDay"] = $searchDay;

$rs = $dao->selectCrmInfoList($conn, $param);

//if ($rs) {
    $html_arr = array();
    $html_arr["thead"] = '';
    $html_arr["tbody"] = "<td colspan=\"8\">검색결과없음</td>";

    goto FIN;
//}

FIN :
     $json  = '{';
     $json .= "\"thead\" : \"%s\", \"tbody\" : \"%s\"";
     $json .= '}';

     echo sprintf($json, $util->convJsonStr($html_arr["thead"])
                       , $util->convJsonStr($html_arr["tbody"]));

     $conn->Close();
     exit;

/******************************************************************************
 ******************** 공통사용 함수
 ******************************************************************************/

/**
 * @brief CRM정보 html 생성
 *
 * @param $param = html 생성용 데이터
 *
 * @return array(
 *      "thead_html"
 *      "tbody_html"
 * )
 */
 function makeCrmInfoListHtml($param) {
     $thead_form  = "<th>상담건수</th>";
     $thead_form  = "<th>%s</th>";
     $thead_form  = "<th colspan=\"5\"></th>";
     $thead_form  = "<th>%s</th>";

     $tr_top_form  = "<tr onclick=\"toggleRow('%s', 'mid');\">";
     $tr_top_form .=     "<td>%s</td>";
     $tr_top_form .=     "<td>%s</td>";
     $tr_top_form .=     "<td>%s</td>";
     $tr_top_form .=     "<td>%s</td>";
     $tr_top_form .=     "<td>%s</td>";
     $tr_top_form .=     "<td>%s</td>";
     $tr_top_form .=     "<td>%s</td>";
     $tr_top_form .=     "<td>%s</td>";
     $tr_top_form .= "</tr>";

     $tr_mid_form  = "<tr class=\"toggle_mid_%s row_bg hidden_row\" onclick=\"toggleRow('%s', 'bot');\">";
     $tr_mid_form .=     "<td>%s</td>";
     $tr_mid_form .=     "<td>%s</td>";
     $tr_mid_form .=     "<td>%s</td>";
     $tr_mid_form .=     "<td>%s</td>";
     $tr_mid_form .=     "<td>%s</td>";
     $tr_mid_form .=     "<td>%s</td>";
     $tr_mid_form .=     "<td>%s</td>";
     $tr_mid_form .=     "<td>%s</td>";
     $tr_mid_form .= "</tr>";

     $tr_bot_form .= "<tr class=\"toggle_bot_%s toggle_bot hidden_row hidden_ground\"></tr>";

     $thead_html = sprintf($thead_form, 1
                                      , 1);

     $tbody_html = '';
     $i = 1;
     foreach ($sort_arr as $cate_top => $info_arr) {
       $tr_top = sprintf($tr_top_form, $i
                                     , $i
                                     , $i
                                     , $i
                                     , $i
                                     , $i
                                     , $i
                                     , $i
                                     , $i);

       $tbody_html .= $tr_top;

       $data_arr = $info_arr["data_arr"];
       $data_arr_count = count($data_arr);
       for($j = 0; $j < $data_arr_count; $j++) {
           $data = $data_arr[$j];

           $tr_mid = sprintf($tr_mid_form, $i
                                         , $i
                                         , $i
                                         , $i
                                         , $i
                                         , $i
                                         , $i
                                         , $i);
           $tr_bot = sprintf($tr_bot_form, 1);
       }

       $i++;
     }

     return array(
         "thead" => $thead_html,
         "tbody" => $tbody_html
     );
 }
 ?>
