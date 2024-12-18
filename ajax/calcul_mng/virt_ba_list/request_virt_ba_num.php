<?php
//ini_set('display_errors', 1);
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/calcul_mng/virt_ba_mng/VirtBaListDAO.inc");

//$url = "https://testpgapi.easypay.co.kr/directapi/trades/directVacctApproval";
$url = "https://pgapi.easypay.co.kr/directapi/trades/directVacctApproval";

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new VirtBaListDAO();
$bank_name = $fb->form("bank_name"); //한페이지에 출력할 게시물 개수
$member_seqno = $fb->form("member_seqno");

$rs = $dao->selectChannelInfo(
    $conn, ["member_seqno" => $member_seqno]
);

$mallId = $rs["pay_easypay_virt"];

$bank_code = "";
switch ($bank_name) {
    case "기업은행" :
        $bank_code = "003";
        break;
    case "국민은행" :
        $bank_code = "004";
        break;
    case "농협중앙회" :
        $bank_code = "011";
        break;
    case "우리은행" :
        $bank_code = "020";
        break;
    case "SC제일은행" :
        $bank_code = "023";
        break;
    case "신한은행" :
        $bank_code = "088";
        break;
    case "부산은행" :
        $bank_code = "032";
        break;
    case "우체국" :
        $bank_code = "071";
        break;
    case "하나은행" :
        $bank_code = "081";
        break;
}

$directCommonInfo = [
    "mallId"         => $mallId,          // <!-- KICC에서 발급한 상점ID -->
    "amount"         => 0,          // <!-- KICC에서 발급한 상점ID -->
    "currency"         => "00",          // <!-- 통화코드 -->
    "clientVersion"         => "N8WI",
    "shopReqDate"         => date('Ymd'),
    "shopTransactionId"         => uniqid()
];



$directOrderInfo = [
    "shopOrderNo"   => uniqid(),
    "goodsAmount" => 0,
    "customerNo" => $member_seqno,
    "customerId" => $member_seqno
];


$directVacctInfo = [
    "vacctTxtype"   => "30",
    "expiryDate" => "22221231",
    "expiryTime" => "000000",
    "bankCode" => $bank_code
];

$headers = array( "content-type: application/json" );


$post_data = array(
    "directCommonInfo"         => $directCommonInfo,
    "directOrderInfo"         => $directOrderInfo,
    "directVacctInfo"         => $directVacctInfo,
);


$ch  = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post_data));


$data = curl_exec($ch);
curl_close($ch);
$decoded = json_decode($data);

$directCommonInfo1 = [
    "mallId"         => $mallId,          // <!-- KICC에서 발급한 상점ID -->
    "amount"         => 0,          // <!-- KICC에서 발급한 상점ID -->
    "currency"         => "00",          // <!-- 통화코드 -->
    "clientVersion"         => "N8WI",
    "shopReqDate"         => date('Ymd'),
    "shopTransactionId"         => uniqid()
];



$directOrderInfo1 = [
    "shopOrderNo"   => uniqid(),
    "goodsAmount" => 0,
];

$directVacctInfo1 = [
    "vacctTxtype"   => "21",
    "bankCode" => $bank_code,
    "expiryDate" => "22221231",
    "expiryTime" => "000000",
    "vacctAccount" => $decoded->accountNo,
    "product_amt" => 0
];

$post_data1 = array(
    "directCommonInfo"         => $directCommonInfo1,
    "directOrderInfo"         => $directOrderInfo1,
    "directVacctInfo"         => $directVacctInfo1,
);



$ch1  = curl_init();
curl_setopt($ch1, CURLOPT_URL, $url);
curl_setopt($ch1, CURLOPT_POST, 1);
curl_setopt($ch1, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch1, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch1, CURLOPT_POSTFIELDS, json_encode($post_data1));

$data1 = curl_exec($ch1);
//$decoded = json_decode($data1);
echo $data1;

curl_close($ch1);

?>

