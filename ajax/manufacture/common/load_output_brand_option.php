<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . '/com/nexmotion/job/nimda/common/ManufactureCommonDAO.inc');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new ManufactureCommonDAO();

//브랜드
$param = array();
$param["table"] = "extnl_brand";
$param["col"] = "extnl_brand_seqno ,name";
$param["where"]["extnl_etprs_seqno"] = $fb->form("seqno");

$rs = $dao->selectData($conn, $param);

$option_html = "\n<option value=\"%s\">%s</option>";
$brand_html = "";
while ($rs && !$rs->EOF) {

    $brand_html .= sprintf($option_html
            , $rs->fields["extnl_brand_seqno"]
            , $rs->fields["name"]);

    $rs->moveNext();
}

echo $brand_html;
$conn->close();
?>
