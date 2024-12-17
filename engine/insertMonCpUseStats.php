#! /usr/local/php/bin/php -f
<?
include_once('/home/dprinting/nimda/engine/common/ConnectionPool.php');
include_once(dirname(__FILE__) . '/dao/EngineDAO.php');
/**
 * @brief 월 쿠폰 통계 데이터 입력
 * 일_쿠폰_사용_통계 테이블의 데이터를 합계하여 매월 1일 02:05에
 * 월_쿠폰_사용_통계 테이블에 데이터 입력
 */

function main() {

    $connectionPool = new ConnectionPool();
    $conn = $connectionPool->getPooledConnection();
    $engineDAO = new EngineDAO();
   
    $conn->StartTrans();

    //년, 월
    //새벽 2시에 crontab 돌리기 때문에 당시 시간에서 안전하게 3시간 빼줌
    $time = time(); 
    $year = date("Y", strtotime("-3 hour", $time));
    $mon = date("m", strtotime("-3 hour", $time));
   
    $param = array();
    $param["table"] = "day_cp_use_stats";
    $param["col"] = "cp_seqno, cpn_admin_seqno";
    $param["where"]["year"] = $year;
    $param["where"]["mon"] = $mon;
    $param["group"] = "cp_seqno";

    $result = $engineDAO->selectData($conn, $param);

    $param = array();
    $param["table"] = "mon_cp_use_stats";
    $param["col"]["year"] = $year;
    $param["col"]["mon"] = $mon;
    
    $sub_param = array();
    $sub_param["year"] = $year;
    $sub_param["mon"] = $mon;

    while ($result && !$result->EOF) {
        //쿠폰 일련번호
        $cp_seqno = $result->fields["cp_seqno"];
        $sub_param["cp_seqno"] = $cp_seqno;
        $param["col"]["cp_seqno"] = $cp_seqno;

        //회사 관리 일련번호
        $cpn_admin_seqno = $result->fields["cpn_admin_seqno"];
        $param["col"]["cpn_admin_seqno"] = $cpn_admin_seqno;

        //발급된 쿠폰 수
        $sub_param["sum"] = "issue_count";
        $issue_count_result = $engineDAO->selectSumCpUse($conn, $sub_param);
        $issue_count = $issue_count_result->fields["sum"];
        $param["col"]["issue_count"] = $issue_count;

        //사용된 쿠폰 수
        $sub_param["sum"] = "use_count";
        $use_count_result = $engineDAO->selectSumCpUse($conn, $sub_param);
        $use_count = $use_count_result->fields["sum"];
        $param["col"]["use_count"] = $use_count;

        //사용된 쿠폰의 총 값
        $sub_param["sum"] = "use_price";
        $use_price_result = $engineDAO->selectSumCpUse($conn, $sub_param);
        $use_price = $use_price_result->fields["sum"];
        $param["col"]["use_price"] = $use_price;

        //mon_cp_use_stats 테이블에 값 입력
        $engineDAO->insertData($conn, $param);

        $result->moveNext();
    }

    if ($conn->HasFailedTrans() === true) {
        $conn->FailTrans();
        $conn->RollbackTrans();
        $conn->close();
    }

    $conn->CompleteTrans();
    
    $conn->close();
}

/**
 * 매월 1일 02:05 함수를 실행
 */
main();
?>
