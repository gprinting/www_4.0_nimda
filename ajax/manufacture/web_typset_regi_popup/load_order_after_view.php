<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/common_define/common_info.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/manufacture/typset_mng/TypsetListDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new TypsetListDAO();

$order_after_history_seqno = $fb->form("order_after_history_seqno");
$order_detail_dvs_num = $fb->form("order_detail_dvs_num");

$param = array();
$param["table"] = "order_after_history";
$param["col"] = "after_name, depth1, depth2, depth3,
    order_common_seqno, seq, detail";
$param["where"]["order_after_history_seqno"] = $order_after_history_seqno;

$sel_rs = $dao->selectData($conn, $param);

$after_name = $sel_rs->fields["after_name"];
$depth1 = $sel_rs->fields["depth1"];
$depth2 = $sel_rs->fields["depth2"];
$depth3 = $sel_rs->fields["depth3"];
$seq = $sel_rs->fields["seq"];
$detail = $sel_rs->fields["detail"];
$order_common_seqno = $sel_rs->fields["order_common_seqno"];

$param = array();
$param["table"] = "order_opt_history";
$param["col"] = "opt_name";
$param["where"]["order_common_seqno"] = $order_common_seqno;

$sel_rs = $dao->selectData($conn, $param);

$dlvrboard = "본판";
while($sel_rs && !$sel_rs->EOF) {

    if ($sel_rs->fields["opt_name"] == "당일판") {
        $dlvrboard = "당일판";
    }
    $sel_rs->moveNext();
}

$param = array();
$param["table"] = "after_op";
$param["col"] = "after_op_seqno, after_name, 
    depth1, depth2, depth3, amt, amt_unit, 
    memo, specialty_items, extnl_brand_seqno";
$param["where"]["order_detail_dvs_num"] = $order_detail_dvs_num;
$param["where"]["after_name"] = $after_name;
$param["where"]["depth1"] = $depth1;
$param["where"]["depth2"] = $depth2;
$param["where"]["depth3"] = $depth3;

$sel_rs = $dao->selectData($conn, $param);

$option_html = "<option value=\"%s\" %s>%s</option>";

if ($sel_rs && !$sel_rs->EOF) {

    $param = array();
    $param["table"] = "extnl_brand";
    $param["col"] = "extnl_etprs_seqno";
    $param["where"]["extnl_brand_seqno"] = $sel_rs->fields["extnl_brand_seqno"];

    $extnl_etprs_seqno = $dao->selectData($conn, $param)->fields["extnl_etprs_seqno"]; 

    $param = array();
    $param["table"] = "extnl_etprs";
    $param["col"] = "extnl_etprs_seqno ,manu_name";
    $param["where"]["pur_prdt"] = "후공정";

    $rs = $dao->selectData($conn, $param);

    $manu_html = "\n<option value=\"\">후공정업체(선택)</option>";
    while ($rs && !$rs->EOF) {

        $selected = "";
        if ($extnl_etprs_seqno == $rs->fields["extnl_etprs_seqno"]) {
            $selected = "selected";
        }
        $manu_html .= sprintf($option_html
                , $rs->fields["extnl_etprs_seqno"]
                , $selected
                , $rs->fields["manu_name"]);

        $rs->moveNext();
    }

    $param = array();
    $param["table"] = "extnl_brand";
    $param["col"] = "extnl_brand_seqno ,name";
    $param["where"]["extnl_etprs_seqno"] = $extnl_etprs_seqno;

    $rs = $dao->selectData($conn, $param);

    $brand_html = "\n<option value=\"\">브랜드(선택)</option>";
    while ($rs && !$rs->EOF) {

        $selected = "";
        if ($sel_rs->fields["extnl_brand_seqno"] == $rs->fields["extnl_brand_seqno"]) {
            $selected = "selected";
        }
        $brand_html .= sprintf($option_html
                , $rs->fields["extnl_brand_seqno"]
                , $selected
                , $rs->fields["name"]);

        $rs->moveNext();
    }

    $param = array();
    $param["after_op_seqno"] = $sel_rs->fields["after_op_seqno"];
    $param["after_name"] = $sel_rs->fields["after_name"];
    $param["depth1"] = $sel_rs->fields["depth1"];
    $param["depth2"] = $sel_rs->fields["depth2"];
    $param["depth3"] = $sel_rs->fields["depth3"];
    $param["detail"] = $detail;
    $param["amt"] = $sel_rs->fields["amt"];
    $param["amt_unit"] = $sel_rs->fields["amt_unit"];
    $param["memo"] = $sel_rs->fields["memo"];
    $param["specialty_items"] = $sel_rs->fields["specialty_items"];
    $param["seq"] = $seq;
    $param["order_common_seqno"] = $order_common_seqno;
    $param["manu_html"] = $manu_html;
    $param["brand_html"] = $brand_html;

} else {
    $param = array();
    $param["table"] = "extnl_etprs";
    $param["col"] = "extnl_etprs_seqno ,manu_name";
    $param["where"]["pur_prdt"] = "후공정";
 
    $rs = $dao->selectData($conn, $param);

    $i = 1; 
    $manu_html = "\n<option value=\"\">후공정업체(선택)</option>";
    while ($rs && !$rs->EOF) {

        if ($i == 1) {
            $extnl_etprs_seqno = $rs->fields["extnl_etprs_seqno"];
        } 
        $selected = "";
        $manu_html .= sprintf($option_html
                , $rs->fields["extnl_etprs_seqno"]
                , $selected
                , $rs->fields["manu_name"]);

        $i++;
        $rs->moveNext();
    }

    $param = array();
    $param["table"] = "extnl_brand";
    $param["col"] = "extnl_brand_seqno ,name";
    $param["where"]["extnl_etprs_seqno"] = $extnl_etprs_seqno;

    $rs = $dao->selectData($conn, $param);

    $brand_html = "\n<option value=\"\">브랜드(선택)</option>";
    while ($rs && !$rs->EOF) {

        $selected = "";
        $brand_html .= sprintf($option_html
                , $rs->fields["extnl_brand_seqno"]
                , $selected
                , $rs->fields["name"]);

        $rs->moveNext();
    }

    $param = array();
    $param["after_name"] = $after_name;
    $param["depth1"] = $depth1;
    $param["depth2"] = $depth2;
    $param["depth3"] = $depth3;
    $param["seq"] = $seq;
    $param["order_common_seqno"] = $order_common_seqno;
    $param["manu_html"] = $manu_html;
    $param["brand_html"] = $brand_html;
}

if (!$param["detail"]) {
    $param["detail"] = "없음";
}

$param["dlvrboard"] = $dlvrboard;
$param["order_after_history_seqno"] = $order_after_history_seqno;

echo getAfterView($param);
$conn->close();
?>
