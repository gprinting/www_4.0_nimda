<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/common_lib/CommonUtil.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/produce/typset_mng/TypsetListDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new TypsetListDAO();
$util = new CommonUtil();

$seqno = $fb->form("seqno");
$order_common_seqno = $fb->form("order_common_seqno");

//후공정
$param = array();
$param["table"] = "order_detail_brochure";
$param["col"] = "order_detail_dvs_num";
$param["where"]["order_common_seqno"] = $order_common_seqno;
$order_after_rs = $dao->selectData($conn, $param);

$order_detail_dvs_num = $order_after_rs->fields["order_detail_dvs_num"];

$param = array();
$param["table"] = "order_after_history";
$param["col"] = "max(seq) AS maxseq";
$param["where"]["order_detail_dvs_num"] = $order_detail_dvs_num;
$order_after_rs = $dao->selectData($conn, $param);

$maxseq = $order_after_rs->fields["maxseq"];

$param = array();
$param["table"] = "order_after_history";
$param["col"] = "basic_yn ,after_name ,depth1 ,depth2
                ,depth3 ,seq ,order_after_history_seqno";
$param["where"]["order_detail_dvs_num"] = $order_detail_dvs_num;
$param["order"] = "seq ASC";
$order_after_rs = $dao->selectData($conn, $param);

//후공정 요약 html
$html  = "\n<tr>";
$html .= "\n  <td class=\"fwb\">%s</td>";
$html .= "\n  <td>%s</td>";
$html .= "\n  <td>%s</td>";
$html .= "\n<tr>";
$i = 1;

$button_html .= "\n<button type=\"button\" class=\"btn btn-sm btn-info\" onclick=\"getReceiptSeqDown('%s', '%s', '%s');\" %s>▼</button>";
$button_html .= "\n<button type=\"button\" class=\"btn btn-sm btn-info\" onclick=\"getReceiptSeqUp('%s', '%s', '%s');\" %s>▲</button>";
$button_html .= "\n<button class=\"bgreen btn_pu btn fix_height20 fix_width40\" onclick=\"getAfterPop('%s', '%s', '%s');\">등록</button>";

while($order_after_rs && !$order_after_rs->EOF) {
   
    $subject = "";
    if ($i === 1) {
        $subject = "후공정";
    }

    $seq_up_dis = "";
    $seq_down_dis = "";
    if ($order_after_rs->fields["seq"] == 1) {
        $seq_up_dis = "disabled=\"disabled\"";
    }

    if ($order_after_rs->fields["seq"] == $maxseq) {
        $seq_down_dis = "disabled=\"disabled\"";
    }

    //기본후공정 여부가 N일때(추가 후공정일경우)
    if ($order_after_rs->fields["basic_yn"] === "N") {
        $button = sprintf($button_html,
                $order_common_seqno,
                $order_detail_dvs_num,
                $order_after_rs->fields["seq"],
                $seq_down_dis,
                $order_common_seqno,
                $order_detail_dvs_num,
                $order_after_rs->fields["seq"],
                $seq_up_dis,
                $order_common_seqno,
                $order_after_rs->fields["order_after_history_seqno"],
                $order_detail_dvs_num);
    }

    $after_name = "";
    if ($order_after_rs->fields["after_name"]) {
        $after_name .= $order_after_rs->fields["after_name"];
    }
    if ($order_after_rs->fields["depth1"]) {
        $after_name .= "-". $order_after_rs->fields["depth1"];
    }
    if ($order_after_rs->fields["depth2"]) {
        $after_name .= "-". $order_after_rs->fields["depth2"];
    }
    if ($order_after_rs->fields["depth3"]) {
        if ($order_after_rs->fields["depth3"] == "-") {
            $after_name .= "";
        } else {
            $after_name .= "-". $order_after_rs->fields["depth3"];
        }
    }

    $after_html .= sprintf($html, $subject,
                           $after_name,
                           $button);

    $i++;
    $order_after_rs->moveNext();
}

$after_html = $util->convJsonStr($after_html);


//배송지
$param = array();
$param["order_common_seqno"] = $order_common_seqno;
$param["tsrs_dvs"] = "수신";
$order_dlvr_rs = $dao->selectReceiptOrderDlvr($conn, $param);

//원본파일
$param = array();
$param["table"] = "order_file";
$param["col"] = "order_file_seqno, origin_file_name";
$param["where"]["order_common_seqno"] = $order_common_seqno; 
$order_file_rs = $dao->selectData($conn, $param);

//그외 모든것
$param = array();
$param["order_common_seqno"] = $order_common_seqno;
$rs = $dao->selectReceiptDirectionsView($conn, $param);

$ret  = "{";
$ret .= " \"order_detail\"      : \"%s\",";
$ret .= " \"stan_name\"         : \"%s\",";
$ret .= " \"print_tmpt_name\"   : \"%s\",";
$ret .= " \"memo\"              : \"%s\",";
$ret .= " \"count\"             : \"%s\",";
$ret .= " \"order_file_seqno\"  : \"%s\",";
$ret .= " \"origin_file_name\"  : \"%s\",";
$ret .= " \"after\"             : \"%s\",";
$ret .= " \"addr\"              : \"%s\"";
$ret .= "}";

echo sprintf($ret, $rs->fields["order_detail"]
        , $rs->fields["stan_name"]
        , $rs->fields["print_tmpt_name"]
        , $rs->fields["memo"]
        , $rs->fields["count"]
        , $order_file_rs->fields["order_file_seqno"]
        , $order_file_rs->fields["origin_file_name"]
        , $after_html
        , $order_dlvr_rs->fields["addr"]);

$conn->close();
?>
