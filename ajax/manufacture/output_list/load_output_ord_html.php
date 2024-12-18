<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . '/com/nexmotion/job/nimda/manufacture/output_mng/OutputListDAO.inc');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new OutputListDAO();

$typset_num = $fb->form("typset_num");

$param = array();
$param["table"] = "sheet_typset";
$param["col"] = "regi_date, oper_sys, typset_format_seqno, paper_name, beforeside_tmpt, aftside_tmpt,
    print_amt, print_amt_unit, dlvrboard, specialty_items, memo, print_title,
    sheet_typset_seqno, print_etprs, dvs, size ";
$param["where"]["typset_num"] = $typset_num;

$rs = $dao->selectData($conn, $param);
$print_etprs = $rs->fields['print_etprs'];
$regi_date = $rs->fields["regi_date"];
$typset_format_seqno = $rs->fields["typset_format_seqno"];
$sheet_typset_seqno = $rs->fields["sheet_typset_seqno"];
$print_amt = $rs->fields["print_amt"] . $rs->fields["print_amt_unit"];
$dlvrboard = $rs->fields["dlvrboard"];
$specialty_items = $rs->fields["specialty_items"];

$paper = $rs->fields["paper_name"];
$tmpt = ($rs->fields["beforeside_tmpt"] + $rs->fields["aftside_tmpt"]) . "도";
$memo = $rs->fields["memo"];
$print_title = $rs->fields["print_title"];

$paper_name = $rs->fields["paper_name"];
$size = $rs->fields["size"];

$param = array();
$param["table"] = "bylabel_print_record";
$param["col"]["sheet_typset_seqno"] = $sheet_typset_seqno;
$param["col"]["empl_seqno"] = $_SESSION["empl_seqno"];
$param["col"]["page"] = "output";

$rs = $dao->insertData($conn, $param);


switch ($paper) {
    case "#91 백색 레쟈크":
        $paper = "레자크 #91(체크) 백색 110g";
        break;
    case "#92 백색 레쟈크":
        $paper = "레자크 #92(줄) 백색 110g";
        break;
    case "100 모조":
        $paper = "모조지 백색 100g";
        break;
    case "120 모조":
        $paper = "모조지 백색 120g";
        break;
    case "150 모조":
        $paper = "모조지 백색 150g";
        break;
}

$param = array();
$param["col"] = "order_num, order_num_seq, amt";
$param["table"] = "order_typset";
$param["where"]["typset_num"] =  $typset_num;
$param["group"] = "order_num, order_num_seq";

$sel_rs = $dao->selectData($conn, $param);


$tr_html  = "\n<tr>";
$tr_html .= "\n  <td headers=\"text1\">%s</td>";
$tr_html .= "\n  <td headers=\"text2\">%s</td>";
$tr_html .= "\n  <td headers=\"text3\">%s</td>";
$tr_html .= "\n  <td headers=\"text4\">%s</td>";
$tr_html .= "\n  <td style=\"width: 180px;\" headers=\"text5\">%s</td>";
$tr_html .= "\n  <td headers=\"text6\">%s*%s</td>";
$tr_html .= "\n  <td headers=\"text7\">%s</td>";
$tr_html .= "\n  <td headers=\"text8\">%s도</td>";
$tr_html .= "\n  <td headers=\"text9\">%s</td>";
$tr_html .= "\n  <td headers=\"text10\">%s</td>";
$tr_html .= "\n  <td headers=\"text11\">%s</td>";
$tr_html .= "\n</tr>";

$i = 1;
$tb_html = "";

while ($sel_rs && !$sel_rs->EOF) {

    $order_num = $sel_rs->fields["order_num"];
    if($order_num != '') {
        $param = array();
        $param["order_num"] = $order_num;
        $rs = $dao->selectProduceOrdPrint($conn, $param);
        $after_detail = $dao->selectOrderAfterInfoByOrderNum($conn, $param);
        while ($rs && !$rs->EOF) {
            if($rs->fields["cate_sortcode"] == "008001005")
                $after_detail .= "문고리가공";
            if($rs->fields["cate_sortcode"] == "008001003")
                $after_detail .= explode(" / ", $rs->fields["order_detail"])[2];
            $tb_html .= sprintf($tr_html, $i,
                $rs->fields["sell_site"],
                $rs->fields["name"],
                $rs->fields["member_name"],
                $rs->fields["title"] . " (" . $sel_rs->fields["order_num_seq"] . ")",
                $rs->fields["work_size_wid"],
                $rs->fields["work_size_vert"],
                (($rs->fields["amt"] * 10) / 10) . $rs->fields["amt_unit_dvs"] . " x " . $rs->fields["count"] . "건",
                $rs->fields["tot_tmpt"],
                $after_detail,
                $rs->fields["work_memo"],
                $rs->fields["invo_cpn"]);
            $rs->moveNext();
        }
        $i++;
    }
    $sel_rs->moveNext();
}

$html  = "\n<!DOCTYPE HTML>";
$html .= "\n<html lang=\"ko\">";
$html .= "\n<head>";
$html .= "\n<link href=\"https://fonts.googleapis.com/css2?family=Libre+Barcode+128&display=swap\" rel=\"stylesheet\">";
$html .= "\n<meta charset=\"UTF-8\">";
$html .= "\n<title>생산지시서</title>";
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
$html .= "\n  <h1>디지탈 프린팅</h1>";
$html .= "\n  <p>판번호 :" . $typset_num . "</p>";
$html .= "\n</div>";
$html .= "\n<div style='width: 100%;'>";
$html .= "\n  <br><br><p id='bcTarget1' style='font-size: 40px;float: right;margin-right: -215px;'></p>";
$html .= "\n</div>";
$html .= "\n<!-- test_tt -->";
$html .= "\n<div class=\"cc\">";
$html .= "\n  <p>작업  일자 : <strong>" . $regi_date . "</strong></p>";
$html .= "\n</div>";
$html .= "\n<!-- cc -->";
$html .= "\n<table id=\"type1\">";
$html .= "\n<caption></caption>";
$html .= "\n<tr>";
$html .= "\n  <th scope=\"row\" id=\"txt1\">절수</th>";
$html .= "\n  <td headers=\"txt1\" colspan=\"3\">" . $size . "</td>";
$html .= "\n</tr>";
$html .= "\n<tr>";
$html .= "\n  <th scope=\"row\" id=\"txt3\">종이</th>";
$html .= "\n  <td headers=\"txt3\">" . $paper . "</td>";
$html .= "\n  <th scope=\"row\" id=\"txt4\">출력실</th>";
$html .= "\n  <td headers=\"txt4\">자사출력실</td>";
$html .= "\n</tr>";
$html .= "\n<tr>";
$html .= "\n  <th scope=\"row\" id=\"txt5\">인쇄도수</th>";
$html .= "\n  <td headers=\"txt5\">" . $tmpt . "</td>";
$html .= "\n  <th scope=\"row\" id=\"txt6\">인쇄소</th>";
$html .= "\n  <td headers=\"txt6\">" . $print_etprs . "</td>";
$html .= "\n</tr>";
$html .= "\n<tr>";
$html .= "\n  <th scope=\"row\" id=\"txt7\">인쇄수량</th>";
$html .= "\n  <td headers=\"txt7\">" . $print_amt . "</td>";
$html .= "\n  <th scope=\"row\" id=\"txt8\">판구분</th>";
$html .= "\n  <td headers=\"txt8\">" . $dlvrboard . "</td>";
$html .= "\n</tr>";
$html .= "\n<tr>";
$html .= "\n  <th scope=\"row\" id=\"txt9\">특이사항</th>";
$html .= "\n  <td headers=\"txt9\" class=\"txt9\" colspan=\"3\">" . $memo . "</td>";
$html .= "\n</tr>";
$html .= "\n</table>";
$html .= "\n<!--type1-->";
$html .= "\n<table id=\"type2\">";
$html .= "\n<caption></caption>";
$html .= "\n<tr>";
$html .= "\n  <th scope=\"row\" id=\"text1\">NO</th>";
$html .= "\n  <th scope=\"row\" id=\"text2\">판매채널</th>";
$html .= "\n  <th scope=\"row\" id=\"text3\">접수자</th>";
$html .= "\n  <th scope=\"row\" id=\"text4\">회원명</th>";
$html .= "\n  <th scope=\"row\" id=\"text5\">인쇄제목</th>";
$html .= "\n  <th scope=\"row\" id=\"text6\">규격</th>";
$html .= "\n  <th scope=\"row\" id=\"text7\">수량</th>";
$html .= "\n  <th scope=\"row\" id=\"text8\">도수</th>";
$html .= "\n  <th scope=\"row\" id=\"text9\">후가공</th>";
$html .= "\n  <th scope=\"row\" id=\"text10\">비고</th>";
$html .= "\n  <th scope=\"row\" id=\"text11\">배송</th>";
$html .= "\n</tr>";
$html .= $tb_html;
$html .= "\n</table>";
$html .= "\n</body>";
$html .= "\n<script type='text/javascript'>";
$html .= "\n $('#bcTarget1').barcode('".$typset_num."', 'code128');";
$html .= "\n</script>";
$html .= "\n</html>";

echo $html;
$conn->close();

?>
