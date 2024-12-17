<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . '/com/nexmotion/job/nimda/basic_mng/prdc_prdt_mng/PrintMngDAO.inc');
include_once(INC_PATH . "/common_lib/CommonUtil.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$util = new CommonUtil();
$printDAO = new PrintMngDAO();
$conn->StartTrans();

$param = array();

$param["table"] = "print";
//인쇄 대분류
$param["col"]["top"] = $fb->form("print_top");
//인쇄명
$param["col"]["name"] = $fb->form("pop_print_name");
//인쇄 색도
$param["col"]["crtr_tmpt"] = $fb->form("crtr_tmpt");
//계열
$param["col"]["affil"] = $fb->form("affil");
//가로사이즈
$param["col"]["wid_size"] = $fb->form("wid_size");
//세로사이즈
$param["col"]["vert_size"] = $fb->form("vert_size");
//기준단위
$param["col"]["crtr_unit"] = $fb->form("crtr_unit");

$param["prk"] = "print_seqno";
$param["prkVal"] = $fb->form("print_seqno");

$result = $printDAO->updateData($conn, $param);

if ($result) {
    echo "1";
} else {
    echo "2";
}

$conn->CompleteTrans();
$conn->close();
?>
