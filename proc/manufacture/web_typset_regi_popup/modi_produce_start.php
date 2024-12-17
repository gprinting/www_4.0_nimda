<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/produce/typset_mng/TypsetListDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

echo "<pre>";
$conn->debug=1;

$fb = new FormBean();
$dao = new TypsetListDAO();
$check = 1;
$state_arr = $fb->session("state_arr");

$conn->StartTrans();
$typset_num = $fb->form("typset_num");

$param = array();
$param["table"] = "produce_process_flow";
$param["col"] = "paper_yn ,output_yn ,print_yn 
,typset_save_yn ,output_save_yn ,print_save_yn";
$param["where"]["typset_num"] = $typset_num;

$sel_rs = $dao->selectData($conn, $param);

$state = "";
$output_yn = $sel_rs->fields["output_yn"];
$print_yn = $sel_rs->fields["print_yn"];
$typset_save_yn = $sel_rs->fields["typset_save_yn"];
$output_save_yn = $sel_rs->fields["output_save_yn"];
$print_save_yn = $sel_rs->fields["print_save_yn"];

if ($typset_save_yn != "Y") {
    echo "2";
    exit;
}

if ($output_save_yn != "Y") {
    echo "3";
    exit;
}

if ($print_save_yn != "Y") {
    echo "4";
    exit;
}

//출력 대기
if ($output_yn == "Y") {
    $state = $state_arr["출력대기"];

    $param = array();
    $param["table"] = "output_op";
    $param["col"]["regi_date"] = date("Y-m-d H:i:s");
    $param["col"]["state"] = $state;
    $param["col"]["orderer"] = $fb->session("name");
    $param["prk"] = "typset_num";
    $param["prkVal"] = $typset_num;

    $rs = $dao->updateData($conn, $param);

    if (!$rs) {
        $check = 0;
    }
}

//인쇄 대기
if ($output_yn == "N" && $print_yn == "Y") {
    $state = $state_arr["인쇄대기"];

    $param = array();
    $param["table"] = "print_op";
    $param["col"]["regi_date"] = date("Y-m-d H:i:s");
    $param["col"]["state"] = $state;
    $param["col"]["orderer"] = $fb->session("name");
    $param["prk"] = "typset_num";
    $param["prkVal"] = $typset_num;

    $rs = $dao->updateData($conn, $param);

    if (!$rs) {
        $check = 0;
    }
}

//조판후공정 대기
if ($output_yn == "N" && $print_yn == "N") {
    $state = $state_arr["조판후공정대기"];

    $param = array();
    $param["table"] = "basic_after_op";
    $param["col"]["regi_date"] = date("Y-m-d H:i:s");
    $param["col"]["state"] = $state;
    $param["col"]["orderer"] = $fb->session("name");
    $param["prk"] = "typset_num";
    $param["prkVal"] = $typset_num;

    $rs = $dao->updateData($conn, $param);

    if (!$rs) {
        $check = 0;
    }
}

$param = array();
$param["table"] = "brochure_typset";
$param["col"] = "brochure_typset_seqno";
$param["where"]["typset_num"] = $typset_num;

$brochure_typset_seqno = $dao->selectData($conn, $param)->fields["brochure_typset_seqno"];

$param = array();
$param["table"] = "page_order_detail_brochure";
$param["col"]["state"] = $state;
$param["prk"] = "brochure_typset_seqno";
$param["prkVal"] = $brochure_typset_seqno;

$rs = $dao->updateData($conn, $param);

if (!$rs) {
    $check = 0;
}

$param = array();
$param["table"] = "brochure_typset";
$param["col"]["state"] = $state;
$param["prk"] = "typset_num";
$param["prkVal"] = $typset_num;

$rs = $dao->updateData($conn, $param);

if (!$rs) {
    $check = 0;
}

$conn->CompleteTrans();
$conn->Close();
echo $check;
?>
