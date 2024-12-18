<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/business/order_mng/OrderCommonMngDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new OrderCommonMngDAO();

$order_common_seqno = $fb->form("order_common_seqno");


$tr_html  = "\n<tr style=\"width: 550px;\">";
$tr_html .= "\n  <td style=\"width: 20px;\" headers=\"text0\">%s</td>";
$tr_html .= "\n  <td headers=\"text1\">%s</td>";
$tr_html .= "\n  <td style=\"width: 70px;\" headers=\"text2\">%s</td>";
$tr_html .= "\n  <td style=\"width: 100px;\" headers=\"text3\">%s</td>";
$tr_html .= "\n  <td style=\"width: 300px;\" headers=\"text4\">%s</td>";
$tr_html .= "\n  <td style=\"width: 30px;\" headers=\"text5\">%s</td>";
$tr_html .= "\n</tr>";

$i = 1;
$tb_html = "";

$param = array();
$param["order_common_seqno"] = $order_common_seqno;

$rs = $dao->selectBunGroupInfo($conn, $param);
$dlvr_price = 0;
while ($rs && !$rs->EOF) {
    $tb_html .= sprintf($tr_html, $i,
        $rs->fields["order_num"],
        $rs->fields["title"],
        ceil($rs->fields["amt"] / 10 * 10) . "매 x " .$rs->fields["count"] . "건" ,
        $rs->fields["order_detail"],
        $rs->fields["erp_state_name"]);

    $dlvr_price += $rs->fields["dlvr_price"];

    $dlvr_way = "";
    if ($rs->fields["dlvr_way"] == "01") {
        if($rs->fields["dlvr_sum_way"] == "01") {
            $dlvr_way = "선불택배";
        } else if ($rs->fields["dlvr_sum_way"] == "02") {
            $dlvr_way = "착불택배";
        } else {
            $dlvr_way = "택배";
        }
    } else if ($rs->fields["dlvr_way"] == "02"){
        $dlvr_way = "직배";
    } else if ($rs->fields["dlvr_way"] == "03"){
        $dlvr_way = "화물";
    } else if ($rs->fields["dlvr_way"] == "04"){
        $dlvr_way = "퀵";
    } else if ($rs->fields["dlvr_way"] == "05"){
        $dlvr_way = "퀵";
    } else if ($rs->fields["dlvr_way"] == "06"){
        $dlvr_way = "방문";
    } else if ($rs->fields["dlvr_way"] == "07"){
        $dlvr_way = "방문";
    }
    $name = $rs->fields["name"];
    $i++;
    $rs->moveNext();
}

$html  = "\n<!DOCTYPE HTML>";
$html .= "\n<html lang=\"ko\">";
$html .= "\n<head>";
$html .= "\n<link href=\"https://fonts.googleapis.com/css2?family=Libre+Barcode+128&display=swap\" rel=\"stylesheet\">";
$html .= "\n<meta charset=\"UTF-8\">";
$html .= "\n<title>묶음배송 정보</title>";
$html .= "\n<style>";
$html .= "\n@charset \"utf-8\";";
$html .= "\n.test_tt{width:100%; margin:0 auto;}";
$html .= "\n.barcode{font-family: 'Libre Barcode 128', cursive;}";
$html .= "\n.test_tt h1{float:left;}";
$html .= "\n.test_tt p{float:right; font-size:22px; font-weight:bold; height:30px; line-height:30px; margin-top:30px;}";
$html .= "\n.cc p{clear:both;}";
$html .= "\n#type1 caption{display:none;}";
$html .= "\n#type1{clear:both; border-collapse:collapse;}";
$html .= "\n#type1 th{font-weight:bold; text-align:left; background-color:#D7ECFC; color:#333; text-align:center; width:100px; height:30px; border:1px solid #333;}";
$html .= "\n#type1 td{font-weight:normal; width:250px; padding-left:5px; background-color:#fff; border:1px solid #333;}";
$html .= "\n#type2 th{font-size:12px; border:2px solid #333;}";
$html .= "\n#type2 th#text1{width:30px;}";
$html .= "\n#type2 td{font-size:13px; border:1px solid #333;}";
$html .= "\n#type2{border-collapse:collapse; margin-top:10px;}";
$html .= "\n#text2,#text8{width:30px;}";
$html .= "\n#text3, #text9{width:50px;}";
$html .= "\n#text4, #text5, #text9{width:110px;}";
$html .= "\n#text7{width:70px;}";
$html .= "\n</style>";
$html .= "\n<script type=\"text/javascript\" src=\"/design_template/js/lib/jquery-1.11.2.min.js\"></script>";
$html .= "\n<script type=\"text/javascript\" src=\"/design_template/js/jquery-barcode.js\"></script>";
//$html .= "\n<script type=\"text/javascript\" src=\"jquery-barcode.js\"></script>";
$html .= "\n</head>";
$html .= "\n<body>";
$html .= "\n<div class=\"test_tt\">";
$html .= "\n  <h1>묶음배송 정보</h1>";
//$html .= "\n  <p>판번호 :" . $typset_num . "</p>";
$html .= "\n</div>";
$html .= "\n<div style='width: 100%;'>";
$html .= "\n  <br><br><p id='bcTarget1' style='font-size: 40px;float: right;margin-right: -215px;'></p>";
$html .= "\n</div>";
$html .= "\n<!-- test_tt -->";
$html .= "\n<div class=\"cc\">";
$html .= "\n  <p>묶음 주문 정보</p>";
$html .= "\n</div>";
$html .= "\n<!-- cc -->";
$html .= "\n<table id=\"type1\">";
$html .= "\n<caption></caption>";
$html .= "\n<tr>";
$html .= "\n  <th scope=\"row\" id=\"txt3\">배송방법</th>";
$html .= "\n  <td headers=\"txt3\">" . $dlvr_way . "</td>";
$html .= "\n  <th scope=\"row\" id=\"txt4\">배송비</th>";
$html .= "\n  <td headers=\"txt4\">".$dlvr_price." 원</td>";
$html .= "\n</tr>";
$html .= "\n<tr>";
$html .= "\n  <th scope=\"row\" id=\"txt1\">보내는 사람</th>";
$html .= "\n  <td headers=\"txt1\" colspan=\"3\">" . $name . "</td>";
$html .= "\n</tr>";
$html .= "\n</table>";

$html .= "\n<div class=\"cc\">";
$html .= "\n  <p>묶음 제품 목록</p>";
$html .= "\n</div>";
$html .= "\n<!--type1-->";
$html .= "\n<table id=\"type2\">";
$html .= "\n<caption></caption>";
$html .= "\n<tr>";
$html .= "\n  <th scope=\"row\" id=\"text0\">번호</th>";
$html .= "\n  <th scope=\"row\" id=\"text1\">주문번호</th>";
$html .= "\n  <th scope=\"row\" id=\"text2\">제작물내용</th>";
$html .= "\n  <th scope=\"row\" id=\"text3\">수량,건수</th>";
$html .= "\n  <th scope=\"row\" id=\"text4\">재질 및 규격</th>";
$html .= "\n  <th scope=\"row\" id=\"text5\">상태</th>";
$html .= "\n</tr>";
$html .= $tb_html;
$html .= "\n</table>";
$html .= "\n</body>";
$html .= "\n</html>";

echo $html;
$conn->close();

?>
