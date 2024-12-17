<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . '/com/nexmotion/job/nimda/common/BasicMngCommonDAO.inc');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$basicDAO = new BasicMngCommonDAO();
$conn->StartTrans();

$check = 1;

$seqno_set = explode(",", $fb->form("select_prdt"));
$count_seqno_set = count($seqno_set);

for ($i = 0; $i < $count_seqno_set; $i++) {
    $seqno = $seqno_set[$i];

    $is_basic_produce = $basicDAO->selectBasicProduce($conn, "paper", $seqno);

    if (!$is_pass && $is_basic_produce) {
        $is_pass = true;
    }

    if ($is_basic_produce) {
        continue;
    }

    $param = array();
    $param["table"] = "print_price";
    $param["prk"] = "print_seqno";
    $param["prkVal"] = $seqno;
    $result = $basicDAO->deleteData($conn, $param);

    if (!$result) {
        $check = 0;
        break;
    }

    $param["table"] = "print";
    $param["prk"] = "print_seqno";
    $param["prkVal"] = $seqno;
    $result = $basicDAO->deleteData($conn, $param);

    if (!$result) {
        $check = 0;
        break;
    }
}

$ret = 2;

if ($check == 1) {
    if ($is_pass) {
        $ret = "3";
    }

    $ret = "1";
}

echo $ret;

$conn->CompleteTrans();
$conn->close();
?>

