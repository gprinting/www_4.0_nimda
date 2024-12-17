<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . '/com/nexmotion/job/nimda/calcul_mng/settle/AdjustRegiDAO.inc');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$adjustDAO = new AdjustRegiDAO();
$conn->StartTrans();
$check = 1;

//거래일자
$deal_date = $fb->form("deal_date");
$member_seqno = $fb->form("member_seqno");
$deposit_dvs = $fb->form("deposit_dvs");
$cash_price = $fb->form("cash_price");
$cash_name = $fb->form("cash_name");
$cash_memo = $fb->form("cash_memo");
$cash_dvs = $fb->form("cash_dvs");

$input_dvs_detail = "적립";
if($cash_price < 0) {
    $input_dvs_detail = "차감";
    //$cash_price *= -1;
}

//조정 테이블에 입력
$param = array();
$param["table"] = "adjust";
$param["col"]["member_seqno"] = $member_seqno;
$param["col"]["cont"] = $cash_memo;
$param["col"]["deal_date"] = $deal_date;
$param["col"]["regi_date"] = date("Y-m-d H:i:s", time());
$param["col"]["price"] = $cash_price;
$param["col"]["input_dvs"] = "입금";
$param["col"]["input_dvs_detail"] = $input_dvs_detail;
$param["col"]["deposit_dvs"] = $deposit_dvs;
$param["col"]["cash_dvs"] = $cash_dvs;
$param["col"]["cpn_admin_seqno"] = "1";
$param["col"]["empl_seqno"] = $_SESSION["empl_seqno"];

$result = $adjustDAO->insertData($conn, $param);

$param_pay_history = array();
$param_pay_history["table"] = "member_pay_history";
$param_pay_history["col"]["member_seqno"] = $member_seqno;
$param_pay_history["col"]["deal_date"] = $deal_date;
$param_pay_history["col"]["dvs"] = "입금증가";
$param_pay_history["col"]["cont"] = $cash_memo;
$param_pay_history["col"]["adjust_price"] = 0;
$param_pay_history["col"]["depo_price"] = $cash_price;

$param_pay_history["col"]["pay_year"] = explode('-', $deal_date)[0];
$param_pay_history["col"]["pay_mon"] = explode('-', $deal_date)[1];
$param_pay_history["col"]["input_typ"] = "입금";
$param_pay_history["col"]["dvs_detail"] = $input_dvs_detail;
$param_pay_history["col"]["adjust_seqno"] = $conn->Insert_ID("adjust");
$param_pay_history["col"]["public_dvs"] = "미발행";
$param_pay_history["col"]["public_state"] = "";

$result = $adjustDAO->insertData($conn, $param_pay_history);
if (!$result) $check = 0;


echo $check;

$conn->CompleteTrans();
$conn->close();
?>
