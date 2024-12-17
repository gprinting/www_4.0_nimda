<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/produce/typset_mng/TypsetListDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new TypsetListDAO();
$check = 1;

$typset_num = $fb->form("typset_num");
$paper_op_seqno = $fb->form("paper_op_seqno");

$conn->StartTrans();

$state_arr = $fb->session("state_arr");
$state = $state_arr["종이발주대기"];

if ($paper_op_seqno) {
    $param = array();
    $param["table"] = "paper_op";
    $param["col"]["state"] = $state;
    $param["prk"] = "paper_op_seqno";
    $param["prkVal"] = $paper_op_seqno;

    $rs = $dao->updateData($conn, $param);

    if (!$rs) {
        $check = 0;
    }
}

$param = array();
$param["table"] = "paper_op";
$param["col"]["name"] = $fb->form("name");
$param["col"]["dvs"] = $fb->form("dvs");
$param["col"]["color"] = $fb->form("color");
$param["col"]["basisweight"] = $fb->form("basisweight");
$param["col"]["op_affil"] = $fb->form("op_affil");
$param["col"]["op_size"] = $fb->form("op_size");
$param["col"]["storplace"] = $fb->form("storplace");
$param["col"]["stor_subpaper"] = $fb->form("stor_subpaper");
$param["col"]["stor_size"] = $fb->form("stor_size");
$param["col"]["amt"] = $fb->form("amt");
$param["col"]["amt_unit"] = $fb->form("amt_unit");
$param["col"]["memo"] = $fb->form("memo");
$param["col"]["extnl_brand_seqno"] = $fb->form("brand_seqno");
$param["col"]["grain"] = $fb->form("grain");
$param["col"]["typ"] = $fb->form("typ");
$param["col"]["typ_detail"] = $fb->form("typ_detail");
$param["col"]["state"] = $state;
$param["col"]["orderer"] = $fb->session("name");
$param["col"]["flattyp_dvs"] = "Y";
$param["col"]["regi_date"] = date("Y-m-d H:i:s");
$param["col"]["regi_date"] = date("Y-m-d H:i:s");
$param["col"]["typset_num"] = $typset_num;

$rs = $dao->insertData($conn, $param);

if (!$rs) {
    $check = 0;
}

$conn->CompleteTrans();
$conn->Close();
echo $check;
?>
