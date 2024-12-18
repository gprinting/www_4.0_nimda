<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/produce/produce_plan/PrintProducePlanDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new PrintProducePlanDAO();

//제조사
$param = array();
$param["table"] = "extnl_etprs";
$param["col"] = "extnl_etprs_seqno ,manu_name";
$param["where"]["pur_prdt"] = $fb->form("pur_prdt");

$rs = $dao->selectData($conn, $param);

$option_html = "<option value=\"%s\">%s</option>";
$manu_html = "<option value=\"\">".$fb->form("pur_prdt")." 제조사(전체)</option>";

while ($rs && !$rs->EOF) {

    $manu_html .= sprintf($option_html
            , $rs->fields["extnl_etprs_seqno"]
            , $rs->fields["manu_name"]);

    $rs->moveNext();
}

echo $manu_html;
$conn->close();
?>
