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
$param["table"] = "paper_dscr";
$param["col"]["top"]         = $fb->form("top");
$param["col"]["name"]        = $fb->form("paper_name");
$param["col"]["dvs"]         = $fb->form("dvs");
$param["col"]["color"]       = $fb->form("color");
$param["col"]["basisweight"] = $fb->form("basisweight");
$param["col"]["dscr"]        = $fb->form("dscr");
$param["col"]["purp"]        = $fb->form("purp");
$param["col"]["paper_sense"] = $fb->form("sense");
$param["col"]["able_after"]  = $fb->form("able_after");
$param["col"]["warn"]        = $fb->form("warn");

//종이 설명 수정
if ($fb->form("paper_dscr_seqno")) {

    $param["prk"] = "paper_dscr_seqno";
    $param["prkVal"] = $fb->form("paper_dscr_seqno");

    $result = $mtraDAO->updateData($conn, $param);

//종이 설명 추가
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

