<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . '/com/nexmotion/job/nimda/basic_mng/prdc_prdt_mng/PaperMngDAO.inc');
include_once(INC_PATH . "/common_lib/CommonUtil.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$util = new CommonUtil();
$paperDAO = new PaperMngDAO();
$conn->StartTrans();

$basic_price    = $util->rmComma($fb->form("basic_price"));
$pur_rate       = $util->rmComma($fb->form("pur_rate"));
$pur_aplc_price = $util->rmComma($fb->form("pur_aplc_price"));

$param = array();

$param["table"] = "paper";
//종이 대분류
$param["col"]["sort"] = $fb->form("paper_sort");
//기준가격
$param["col"]["basic_price"] = $basic_price;
//종이명
$param["col"]["name"] = $fb->form("pop_paper_name");
//요율
$param["col"]["pur_rate"] = $pur_rate;
//구분
$param["col"]["dvs"] = $fb->form("dvs");
//적용 금액
$param["col"]["pur_aplc_price"] = $pur_aplc_price;
//색상
$param["col"]["color"] = $fb->form("color");
//평량
$param["col"]["basisweight"] = $fb->form("basisweight");
//평량단위
$param["col"]["basisweight_unit"] = $fb->form("basisweight_unit");
//계열
$param["col"]["affil"] = $fb->form("affil");
//가로사이즈
$param["col"]["wid_size"] = $fb->form("wid_size");
//세로사이즈
$param["col"]["vert_size"] = $fb->form("vert_size");
//매입금액 = 기준금액 (1+(요율)/100) + 적용금액
$param["col"]["pur_price"] = ($basic_price * 
                             (1 + $pur_rate/100)) + 
                             $pur_aplc_price;
//기준단위
$param["col"]["crtr_unit"] = $fb->form("crtr_unit");

$param["prk"] = "paper_seqno";
$param["prkVal"] = $fb->form("paper_seqno");

$result = $paperDAO->updateData($conn, $param);

if ($result) {
    
    echo "1";

} else {

    echo "2";
}

$conn->CompleteTrans();
$conn->close();
?>

