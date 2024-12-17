<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/manufacture/item_mng/PaperOpMngDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new PaperOpMngDAO();
$check = 1;

$paper_op_seqno = $fb->form("paper_op_seqno");

$conn->StartTrans();

$state_arr = $fb->session("state_arr");
$state = $state_arr["종이발주대기"];

$tmp_param = array();
$tmp_param['typset_num'] = $fb->form("typset_num");

$size_info = $dao->selectSize($conn,$tmp_param);
$width = $size_info->fields['width'];
$height = $size_info->fields['height'];

$grain = "횡목";
if($height > $width) $grain = "종목";


$last_num = $dao->selectPaperOpLastNum($conn);
$last_num = str_pad($last_num, 3, '0', STR_PAD_LEFT);

$param = array();
$param["table"] = "paper_op";
$param["col"]["op_num"] = date("ymd") . $last_num;
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
$param["col"]["extnl_etprs_seqno"] = $fb->form("brand_seqno");
$param["col"]["grain"] = $grain;
$param["col"]["orderer"] = $_SESSION["empl_seqno"];
$param["col"]["state"] = $state;
$param["col"]["flattyp_dvs"] = "Y";
$param["col"]["regi_date"] = date("Y-m-d H:i:s");
$param["col"]["typset_num"] = $fb->form("typset_num");

if ($paper_op_seqno) {

    $param["prk"] = "paper_op_seqno";
    $param["prkVal"] = $paper_op_seqno;

    $rs = $dao->updateData($conn, $param);
} else {
    $rs = $dao->insertData($conn, $param);
}
if (!$rs) {
    $check = 0;
}

$conn->CompleteTrans();
$conn->Close();
echo $check;
?>
