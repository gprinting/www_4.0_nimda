<?

define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . '/com/nexmotion/job/nimda/basic_mng/prdt_mng/PrdtBasicRegiDAO.inc');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new PrdtBasicRegiDAO();

$select_el = $fb->form("selectEl");
$seqno = $fb->form("seqno");
$amt = $fb->form("amt");
$count = $fb->form("count");
$price = $fb->form("price");

$param = [];
$param["count"] = $count;
$param["amt"] = $amt;
$param["seq"] = $seqno;
$param["price"] = $price;

$val = [];
$val["seq"] = $seqno;
$rs = $dao->selectOrderInfoHistory($conn,$param);

$tbody_form = "";
while($rs && !$rs->EOF) {
    $tmp_tbody_form = "<tr ";
    $tmp_tbody_form .=     "class=\"sales_depo_tr\"> ";
    $tmp_tbody_form .=     "<td>%s</td>";
    $tmp_tbody_form .=     "<td>%s</td>";
    $tmp_tbody_form .=     "<td style=\"white-space:pre-wrap; word-wrap:break-word\">%s</td>";
    $tmp_tbody_form .=     "<td style=\"white-space:pre-wrap; word-wrap:break-word\">%s</td>";
    $tmp_tbody_form .=     "<td>%s</td>";
    $tmp_tbody_form .= "</tr>";
    $tmp_tbody_form = sprintf($tmp_tbody_form
        ,$rs->fields["update_regi_date"]
        ,$rs->fields["kind"]
        ,$rs->fields["before_detail"]
        ,$rs->fields["after_detail"]
        ,$rs->fields["name"]);

    $tbody_form .= $tmp_tbody_form;
    $rs->MoveNext();
}

echo orderHistoryPopupHtml($param, $val, $tbody_form);

?>