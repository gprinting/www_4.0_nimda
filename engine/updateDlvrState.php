#! /usr/local/php/bin/php
<?
include_once('/var/www/html/nimda/engine/common/ConnectionPool.php');
include_once(dirname(__FILE__) . '/dao/EngineDAO.php');

function main() {

    $connectionPool = new ConnectionPool();
    $conn = $connectionPool->getPooledConnection();
    $engineDAO = new EngineDAO();

    /* 주문_공통 테이블에서 회원이 결제한 총금액 및 회원번호 조회 */
    /* 회원 일련번호 가져옴 */
    $result = $engineDAO->SelectParselOrders($conn);

    while ($result && !$result->EOF) {
        $parcel = explode(',', $result->fields["invo_num"])[0];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://ftr.alps.llogis.com:18260/openapi/ftr/getCustomerInvTracking?invNo=" . $parcel);
        curl_setopt($ch, CURLOPT_POST, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        curl_close($ch);

        //var_dump($response);
        $res = json_decode($response);
        if($res->result) {
            if((int)$res->tracking[0]->GODS_STAT_CD >= 10) {
                $param = array();
                $param["order_common_seqno"] = $result->fields["order_common_seqno"];
                $engineDAO->UpdateParselInfo($conn, $param);
            }
        } else {
            echo "FAIL : " . $result->fields["order_num"] . " " . $result->fields["invo_num"];
        }
        $result->moveNext();
    }
}

/**
 * 매월 1일 02:00 마다 함수를 실행
 */
main();

?>