#! /usr/local/php/bin/php -f
<?
include_once('/home/dprinting/nimda/engine/common/ConnectionPool.php');
include_once(dirname(__FILE__) . '/dao/EngineDAO.php');

/** 
 * @brief  세금계산서 회원별 발급 금액 매월 통계
 *        
 * 
 */ 
function main() {

    $connectionPool = new ConnectionPool();
    $conn = $connectionPool->getPooledConnection();
    $engineDAO = new EngineDAO();

    /**
     * 엔진 돌리는 현재 년/월을 저장
     *
     * 매월 1일에 돌리면 하루 전인 달의
     * 데이터를 INSERT 해야 하므로 하루전 날짜 저장 
     * 
     */
    $last_date = date("Y-m-d", strtotime(" -1 day"));
    $last_tmp = explode('-', $last_date);

    //회원 결제 내역 테이블에 해당회원 결제 합 가져오기
    $param = array();
    $param["year"] = $last_tmp[0];
    $param["mon"] = $last_tmp[1];
    $result = $engineDAO->selectPayHistory($conn, $param);

    while ($result && !$result->EOF) {

        $pay_sum = 0;
        $discount_sum = 0;

        $member_seqno = $result->fields["member_seqno"];
        $pay_sum = $result->fields["pay_sum"];

        //조정테이블에 해당 회원 에누리 합 가져오기
        $adj_param = array();
        $adj_param["member_seqno"]  = $member_seqno;
        $adj_param["year"]  = $last_tmp[0];
        $adj_param["mon"]  = $last_tmp[1];
        $adj_rs = $engineDAO->selectAdjustDiscount($conn, $adj_param);

        $discount_sum = $adj_rs->fields["discount_sum"];
        $tab_price = (int)$pay_sum - (int)$discount_sum;

        //해당회원 사업자등록증 정보 가져오기
        $info_param = array();
        $info_param["member_seqno"] = $member_seqno;
        $info_rs = $engineDAO->selectMemberInfo($conn, $info_param);

        //발급관리 테이블에 INSERT
        $param = array();
        $param["table"] = "public_admin";
        $param["col"]["member_seqno"] = $member_seqno;
        $param["col"]["public_dvs"] = "세금계산서";
        $param["col"]["req_year"] = $last_tmp[0];
        $param["col"]["req_mon"] = $last_tmp[1];
        $param["col"]["crn"] = $info_rs->fields["crn"];
        $param["col"]["repre_name"] = $info_rs->fields["repre_name"];
        $param["col"]["addr"] = $info_rs->fields["addr"] . " ";
        $param["col"]["addr"] .= $info_rs->fields["addr_detail"];
        $param["col"]["bc"] = $info_rs->fields["bc"];
        $param["col"]["tob"] = $info_rs->fields["tob"];
        $param["col"]["supply_price"] = ceil($tab_price/1.1);
        $param["col"]["vat"] = $tab_price - $param["col"]["supply_price"];
        $param["col"]["public_state"] = "대기";
        $param["col"]["tel_num"] = $info_rs->fields["tel_num"];
        $param["col"]["tab_public"] = "미발행";
        $param["col"]["zipcode"] = $info_rs->fields["zipcode"];
        $param["col"]["pay_price"] = $tab_price;
        $param["col"]["card_price"] = 0;
        $param["col"]["etc_price"] = 0;
        $param["col"]["money_price"] = $tab_price;
        $param["col"]["member_name"] = $info_rs->fields["member_name"];
        $param["col"]["unitprice"] = ceil($tab_price/1.1);
        $param["col"]["object_price"] = $tab_price;
        $param["col"]["req_date"] = date("Y-m-d H:i:s",time());

        $rs = $engineDAO->insertData($conn, $param);
        $result->moveNext();
    }


$conn->close();
}

/**
 * 매월 1일마다 함수를 실행
 */
main();

?>
