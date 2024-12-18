<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/common/NimdaCommonDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new NimdaCommonDAO();

$val = $fb->form("val");

$rs = $dao->selectStateAdmin($conn, $val);

$proc_html = option("전체", '');

while ($rs && !$rs->EOF) {
    $fields = $rs->fields;

    $proc_html .= option($fields["erp_state_name"], $fields["state_code"]);

    $rs->MoveNext();
}

echo $proc_html;
?>
