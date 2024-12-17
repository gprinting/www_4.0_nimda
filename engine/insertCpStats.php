#! /usr/local/php/bin/php -f
<?
include_once('/home/dprinting/nimda/engine/common/ConnectionPool.php');
include_once(dirname(__FILE__) . '/dao/EngineDAO.php');

/** 
 * @brief 쿠폰통계
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
    $last_date = date("Y-m-d", strtotime("-1 days"));
    $last_tmp = explode('-', $last_date);

    $param = array();
    $result = $engineDAO->selectCp($conn, $param);

    while ($result && !$result->EOF) {


        $result->moveNext();
    }

    $conn->close();
}

/**
 * 매월 1일 02:05 마다 함수를 실행
 */
main();

?>
