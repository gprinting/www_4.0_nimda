<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/manufacture/cooperator_mng/CooperatorListDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new CooperatorListDAO();

//제조사
$param = array();
$param["table"] = "extnl_etprs";
$param["col"] = "extnl_etprs_seqno ,manu_name";
$param["where"]["pur_prdt"] = $fb->form("outsource_etprs_cate_name");
$rs = $dao->selectData($conn, $param);

$option_html = "<option value=\"%s\">%s</option>";
//$manu_html = "<option value=\"\">협력 업체(전체)</option>";
$manu_html = "";

while ($rs && !$rs->EOF) {
    $manu_html .= sprintf($option_html
            , $rs->fields["extnl_etprs_seqno"]
            , $rs->fields["manu_name"]);

    $rs->moveNext();
}

echo $manu_html;
$conn->close();
?>
