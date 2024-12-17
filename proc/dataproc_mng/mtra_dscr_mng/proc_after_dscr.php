<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/dataproc_mng/set/MtraDscrMngDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();
$conn->StartTrans();

$fb = new FormBean();
$mtraDAO = new MtraDscrMngDAO();

$param = array();
$param["table"] = "after_dscr";
$param["col"]["name"] = $fb->form("after_name");
$param["col"]["dscr"] = $fb->form("dscr");

//후공정 설명 수정
if ($fb->form("after_dscr_seqno")) {

    $param["prk"] = "after_dscr_seqno";
    $param["prkVal"] = $fb->form("after_dscr_seqno");

    $result = $mtraDAO->updateData($conn, $param);

//후공정 설명 추가
} else {

    $result = $mtraDAO->insertData($conn, $param);

}

if ($result) {

    echo "1";

} else {

    echo "2";

}

$conn->CompleteTrans();
$conn->close();
?>
