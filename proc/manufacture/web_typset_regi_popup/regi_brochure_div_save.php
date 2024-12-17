<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/manufacture/typset_mng/TypsetListDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new TypsetListDAO();
$check = 1;

$order_detail_dvs_num = $fb->form("order_detail_dvs_num");
$state_arr = $fb->session("state_arr");
$state = $state_arr["조판중"];

if ($fb->form("tot_count") != $fb->form("new_tot_count")) {
    echo 2;
    exit;
}

$param = array();
$param["table"] = "page_order_detail_brochure"; 
$param["col"] = "brochure_typset_seqno";
$param["where"]["order_detail_dvs_num"] = $order_detail_dvs_num;

$brochure_typset_seqno = $dao->selectData($conn, $param)->fields["brochure_typset_seqno"];

$conn->StartTrans();

$param = array();
$param["table"] = "page_order_detail_brochure";
$param["prk"] = "order_detail_dvs_num";
$param["prkVal"] = $order_detail_dvs_num;

$rs = $dao->deleteData($conn, $param);

if (!$rs) {
    $check = 0;
}

if ($brochure_typset_seqno) {

    $param = array();
    $param["table"] = "brochure_typset";
    $param["col"] = "typset_num";
    $param["where"]["brochure_typset_seqno"] = $brochure_typset_seqno;

    $typset_num = $dao->selectData($conn, $param)->fields["typset_num"];

    $param = array();
    $param["table"] = "brochure_typset";
    $param["prk"] = "typset_num";
    $param["prkVal"] = $typset_num;

    $rs = $dao->deleteData($conn, $param);

    if (!$rs) {
        $check = 0;
    }

    $param = array();
    $param["table"] = "output_op";
    $param["prk"] = "typset_num";
    $param["prkVal"] = $typset_num;

    $rs = $dao->deleteData($conn, $param);

    if (!$rs) {
        $check = 0;
    }

    $param = array();
    $param["table"] = "print_op";
    $param["prk"] = "typset_num";
    $param["prkVal"] = $typset_num;

    $rs = $dao->deleteData($conn, $param);

    if (!$rs) {
        $check = 0;
    }

    $param = array();
    $param["table"] = "basic_after_op";
    $param["prk"] = "typset_num";
    $param["prkVal"] = $typset_num;

    $rs = $dao->deleteData($conn, $param);

    if (!$rs) {
        $check = 0;
    }

    $param = array();
    $param["table"] = "produce_process_flow";
    $param["prk"] = "typset_num";
    $param["prkVal"] = $typset_num;

    $rs = $dao->deleteData($conn, $param);

    if (!$rs) {
        $check = 0;
    }
}

$page_arr = explode(",", $fb->form("count"));

$param = array();
$param["table"] = "page_order_detail_brochure";
$param["col"]["order_detail_dvs_num"] = $order_detail_dvs_num;
$param["col"]["state"] = $state;

for ($i = 0; $i < count($page_arr); $i++) {

    $param["col"]["page"] = $page_arr[$i];

    $rs = $dao->insertData($conn, $param);

    if (!$rs) {
        $check = 0;
    }
}

$conn->CompleteTrans();
$conn->Close();
echo $check;
?>
