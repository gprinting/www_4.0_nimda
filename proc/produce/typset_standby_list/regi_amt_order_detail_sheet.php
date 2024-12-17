<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/produce/typset_mng/TypsetStandbyListDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new TypsetStandbyListDAO();
$check = 1;

if ($fb->form("tot_count") != $fb->form("new_tot_count")) {
    echo 2;
    exit;
}

$conn->StartTrans();

$param = array();
$param["table"] = "amt_order_detail_sheet";
$param["prk"] = "order_detail_count_file_seqno";
$param["prkVal"] = $fb->form("seqno");

$rs = $dao->deleteData($conn, $param);

if (!$rs) {
    $check = 0;
}

$amt_arr = explode(",", $fb->form("count"));

$param = array();
$param["table"] = "amt_order_detail_sheet";
$param["col"]["order_detail_count_file_seqno"] = $fb->form("seqno");
$param["col"]["state"] = "2120";

for ($i = 0; $i < count($amt_arr); $i++) {

    $param["col"]["amt"] = $amt_arr[$i];

    $rs = $dao->insertData($conn, $param);

    if (!$rs) {
        $check = 0;
    }
}

$conn->CompleteTrans();
$conn->Close();
echo $check;
?>
