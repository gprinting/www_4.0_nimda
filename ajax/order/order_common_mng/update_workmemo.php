<?
define(INC_PATH, $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/business/order_mng/OrderCommonMngDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$dao = new OrderCommonMngDAO();
$fb = new FormBean();

$session = $fb->getSession();
$param = [];
$param['order_common_seqno'] = $fb->form("seqno");
$param['work_memo'] = $fb->form("memo");

$rs = $dao->updateWorkMemo($conn, $param);

$param = array();
$param["table"] = "order_common";
$param["col"] = "order_num";
$param["where"]["order_common_seqno"] = $fb->form("seqno");
$result = $dao->selectData($conn, $param);


try {
    $str_param = "jumunno=" . $result->fields["order_num"] . "&memo=" . $fb->form("memo");
echo $str_param;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "http://211.206.147.196:7777/update_memo");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $str_param);

    $headers = array();
    $response = curl_exec($ch);
    if ($response === false) {
        throw new Exception(curl_error($ch), curl_errno($ch));
    }
    curl_close($ch);
} catch (Exception $e) {
    var_dump($e);
}


?>