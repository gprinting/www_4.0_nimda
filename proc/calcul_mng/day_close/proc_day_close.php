<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/calcul_mng/settle/DayCloseDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new DayCloseDAO();
$check = 1;
$conn->StartTrans();

$close_date = $fb->form("day");
$close_yn = $fb->form("close_yn");
$sell_site = $fb->form("sell_site");

$param = array();
$param["table"] = "day_close";
$param["prk"] = "close_date";
$param["prkVal"] = $close_date;
$result = $dao->deleteData($conn, $param);
if (!$result) $check = 0;

$param = array();
$param["table"] = "day_close";
$param["col"]["close_date"] = $close_date;
$param["col"]["close_yn"] = $close_yn;
$param["col"]["year"] = substr($close_date, 0,4);
$param["col"]["mon"] = substr($close_date, 5,2);
$param["col"]["cpn_admin_seqno"] = $sell_site;
$result = $dao->insertData($conn, $param);
if (!$result) $check = 0;

$conn->CompleteTrans();
$conn->Close();
echo $check;
?>
