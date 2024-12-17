<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . '/com/nexmotion/job/nimda/basic_mng/prdc_prdt_mng/OutputMngDAO.inc');
include_once(INC_PATH . "/common_lib/CommonUtil.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$util = new CommonUtil();
$outputDAO = new OutputMngDAO();
$conn->StartTrans();

$param = array();

$param["table"] = "output";
//출력 대분류
$param["col"]["top"] = $fb->form("output_top");
//출력명
$param["col"]["name"] = $fb->form("pop_output_name");
//판
$param["col"]["board"] = $fb->form("board");
//계열
$param["col"]["affil"] = $fb->form("affil");
//가로사이즈
$param["col"]["wid_size"] = $fb->form("wid_size");
//세로사이즈
$param["col"]["vert_size"] = $fb->form("vert_size");
//기준단위
$param["col"]["crtr_unit"] = $fb->form("crtr_unit");

$param["prk"] = "output_seqno";
$param["prkVal"] = $fb->form("output_seqno");

$result = $outputDAO->updateData($conn, $param);

if ($result) {
    echo "1";
} else {
    echo "2";
}

$conn->CompleteTrans();
$conn->close();
?>

