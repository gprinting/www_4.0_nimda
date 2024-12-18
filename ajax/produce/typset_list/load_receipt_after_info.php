<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/doc/nimda/produce/receipt_mng/ReceiptListDOC.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/produce/receipt_mng/ReceiptListDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new ReceiptListDAO();

$seqno = $fb->form("seqno");
$order_detail_dvs_num = $fb->form("order_detail_dvs_num");

$param = array();
$param["table"] = "order_after_history";
$param["col"] = "max(seq) AS maxseq";
$param["where"]["order_detail_dvs_num"] = $order_detail_dvs_num;
$after_rs = $dao->selectData($conn, $param);

$maxseq = $after_rs->fields["maxseq"];

$param = array();
$param["table"] = "order_after_history";
$param["col"] = "basic_yn, after_name, depth1, depth2
                ,depth3, seq, order_after_history_seqno";
$param["where"]["order_detail_dvs_num"] = $order_detail_dvs_num;
$param["order"] = "seq ASC";
$after_rs = $dao->selectData($conn, $param);

//후공정 요약 html
$html  = "\n<tr>";
$html .= "\n  <td class=\"fwb\">%s</td>";
$html .= "\n  <td>%s</td>";
$html .= "\n  <td>%s</td>";
$html .= "\n<tr>";
$i = 1;

//추가 후공정일 경우 버튼html
$button_html .= "\n<button type=\"button\" class=\"btn btn-sm btn-info\" onclick=\"getReceiptSeqDown('%s', '%s', '%s');\" %s>▼</button>";
$button_html .= "\n<button type=\"button\" class=\"btn btn-sm btn-info\" onclick=\"getReceiptSeqUp('%s', '%s', '%s');\" %s>▲</button>";
$button_html .= "\n<button class=\"bgreen btn_pu btn fix_height20 fix_width40\" onclick=\"getAfterPop('%s', '%s', '%s');\">등록</button>";

while ($after_rs && !$after_rs->EOF) {

    $subject = "";
    if ($i === 1) {
        $subject = "후공정";
    }

    $seq_up_dis = "";
    $seq_down_dis = "";
    if ($after_rs->fields["seq"] == 1) {
        $seq_up_dis = "disabled=\"disabled\"";
    }

    if ($after_rs->fields["seq"] == $maxseq) {
        $seq_down_dis = "disabled=\"disabled\"";
    }

    //기본후공정 여부가 N일때(추가 후공정일경우)
    if ($after_rs->fields["basic_yn"] === "N") {
        $button = sprintf($button_html,
                          $seqno,
                          $order_detail_dvs_num,
                          $after_rs->fields["seq"],
                          $seq_down_dis,
                          $seqno,
                          $order_detail_dvs_num,
                          $after_rs->fields["seq"],
                          $seq_up_dis,
                          $seqno,
                          $after_rs->fields["order_after_history_seqno"],
                          $order_detail_dvs_num);
    }

    $after_name = "";
    if ($after_rs->fields["after_name"]) {
        $after_name .= $after_rs->fields["after_name"];
    }
    if ($after_rs->fields["depth1"]) {
        $after_name .= "-". $after_rs->fields["depth1"];
    }
    if ($after_rs->fields["depth2"]) {
        $after_name .= "-". $after_rs->fields["depth2"];
    }
    if ($after_rs->fields["depth3"]) {
        if ($after_rs->fields["depth3"] == "-") {
            $after_name .= "";
        } else {
            $after_name .= "-". $after_rs->fields["depth3"];
        }
    }

    $after_html .= sprintf($html, $subject,
                           $after_name,
                           $button);
    $i++;
    $after_rs->moveNext();
}

echo $after_html;
$conn->close();
?>
