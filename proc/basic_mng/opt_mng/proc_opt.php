<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . '/com/nexmotion/job/nimda/basic_mng/prdc_prdt_mng/OptMngDAO.inc');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$optDAO = new OptMngDAO();
$conn->StartTrans();

$param = array();

$param["table"] = "opt";
//옵션 이름
$param["col"]["name"] = $fb->form("opt_name");
//기준가격
$param["col"]["basic_price"] = $fb->form("basic_price");
//옵션 depth1
if ($fb->form("pop_depth1") == "") {

    //빈 값이면 "-"를 넣는다.
    $param["col"]["depth1"] = "-";

} else {

    $param["col"]["depth1"] = $fb->form("pop_depth1");
}

//요율
$param["col"]["pur_rate"] = $fb->form("pur_rate");
//옵션 depth2
if ($fb->form("pop_depth2") == "") {

    //빈 값이면 "-"를 넣는다.
    $param["col"]["depth2"] = "-";

} else {

    $param["col"]["depth2"] = $fb->form("pop_depth2");
}
//적용 금액
$param["col"]["pur_aplc_price"] = $fb->form("pur_aplc_price");
//옵션 depth3
if ($fb->form("pop_depth3") == "") {

    //빈 값이면 "-"를 넣는다.
    $param["col"]["depth3"] = "-";

} else {

    $param["col"]["depth3"] = $fb->form("pop_depth3");
}
//기준단위
$param["col"]["crtr_unit"] = $fb->form("crtr_unit");
//수량
$param["col"]["amt"] = $fb->form("amt");
//매입금액 = 기준금액 (1+(요율)/100) + 적용금액
$param["col"]["pur_price"] = ($fb->form("basic_price") * 
                             (1 + $fb->form("pur_rate")/100)) + 
                             $fb->form("pur_aplc_price");
$param["prk"] = "opt_seqno";
$param["prkVal"] = $fb->form("opt_seqno");

$result = $optDAO->updateData($conn, $param);

if ($result) {
    
    echo "1";

} else {

    echo "2";
}

$conn->CompleteTrans();
$conn->close();
?>

