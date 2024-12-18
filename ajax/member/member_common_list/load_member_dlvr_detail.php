<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/member/member_mng/MemberCommonListDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$memberCommonListDAO = new MemberCommonListDAO();

$dlvr_dvs = $fb->form("dlvr_dvs");

if($dlvr_dvs == "직배") {
    $param = array();
    $param["table"] = "direct_dlvr_info";
    $param["col"] = "*";
    $param["where"]["is_using"] = "Y";

    $rs = $memberCommonListDAO->selectData($conn, $param);

    echo makeOptionHtml($rs,"vehi_num","vehi_num",null,"N");
} else {
    echo "<option>-</option>";
}

$conn->close();

?>
