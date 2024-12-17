<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . '/com/nexmotion/job/nimda/basic_mng/prdc_prdt_mng/AfterMngDAO.inc');
include_once(INC_PATH . "/common_lib/CommonUtil.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$util = new CommonUtil();
$afterDAO = new AfterMngDAO();
$conn->StartTrans();

$param = array();

$param["table"] = "after";
//후공정 이름
$param["col"]["name"] = $fb->form("after_name");
//후공정 depth1
if ($fb->form("pop_depth1") == "") {

    //빈 값이면 "-"를 넣는다.
    $param["col"]["depth1"] = "-";

} else {

    $param["col"]["depth1"] = $fb->form("pop_depth1");
}
//후공정 depth2
if ($fb->form("pop_depth2") == "") {

    //빈 값이면 "-"를 넣는다.
    $param["col"]["depth2"] = "-";

} else {

    $param["col"]["depth2"] = $fb->form("pop_depth2");
}
//후공정 depth3
if ($fb->form("pop_depth3") == "") {

    //빈 값이면 "-"를 넣는다.
    $param["col"]["depth3"] = "-";

} else {

    $param["col"]["depth3"] = $fb->form("pop_depth3");
}
//기준단위
$param["col"]["crtr_unit"] = $fb->form("crtr_unit");

$param["prk"] = "after_seqno";
$param["prkVal"] = $fb->form("after_seqno");

$result = $afterDAO->updateData($conn, $param);

if ($result) {
    echo "1";
} else {
    echo "2";
}

$conn->CompleteTrans();
$conn->close();
?>
