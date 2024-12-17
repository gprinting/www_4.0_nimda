#! /usr/local/php/bin/php -f
<?
include_once('/home/dprinting/nimda/engine/common/ConnectionPool.php');
include_once(dirname(__FILE__) . '/dao/EngineDAO.php');

/** 
 * 매일 02:00에 실행
 * 1. 월별 통계 집계를 위한 일별통계테이블에 데이터 INSERT 
 * 2. 소멸일자 지날시 쿠폰 사용여부 변경
 */ 

function main() {

    $connectionPool = new ConnectionPool();
    $conn = $connectionPool->getPooledConnection();
    $engineDAO = new EngineDAO();
   
    $conn->StartTrans();

    //1. 월별 통계 집계를 위한 일별통계테이블에 데이터 INSERT 
    $param = array();
    $param["date"] = "ci.issue_date";
    $issue_result = $engineDAO->selectCpStats($conn, $param);

    $param["date"] = "ci.use_date";
    $use_result = $engineDAO->selectCpStats($conn, $param);

    $arr = array();
   
    //발급일 기준으로 얻은 값 
    while ($issue_result && !$issue_result->EOF) {
        $cp_seqno = $issue_result->fields["cp_seqno"]; 
        $arr[$cp_seqno]["issue"] = $issue_result->fields;

        $cnt_param["cp_seqno"] = $cp_seqno;
        $cnt_param["date"] = "issue_date";
        $issue_cnt_result = $engineDAO->countCpStats($conn, $cnt_param);
        
        //발급된 쿠폰 수
        $arr[$cp_seqno]["issue"]["cnt"] = $issue_cnt_result->fields["cnt"];

        $issue_result->MoveNext();
    }

    //사용일 기준으로 얻은 값
    while ($use_result && !$use_result->EOF) {
        $cp_seqno = $use_result->fields["cp_seqno"]; 
        $arr[$cp_seqno]["use"] = $use_result->fields;

        $cnt_param["cp_seqno"] = $cp_seqno;
        $cnt_param["date"] = "use_date";
        $use_cnt_result = $engineDAO->countCpStats($conn, $cnt_param);
       
        //사용된 쿠폰 수 
        $arr[$cp_seqno]["use"]["cnt"] = $use_cnt_result->fields["cnt"];
        
        $use_result->MoveNext();
    }

    $param = array();
    $param["table"] = "day_cp_use_stats";

    //년, 월, 일
    //새벽 2시에 crontab 돌리기 때문에 당시 시간에서 안전하게 3시간 빼줌
    $time = time(); 
    $year = date("Y", strtotime("-3 hour", $time));
    $mon = date("m", strtotime("-3 hour", $time));
    $day = date("d", strtotime("-3 hour", $time));

    $param["col"]["year"] = $year;
    $param["col"]["mon"] = $mon;
    $param["col"]["day"] = $day;

    $sub_param = array();

    foreach($arr as $cp_seqno => $tmp_arr) {
        $issue_arr = $tmp_arr["issue"];
        $use_arr = $tmp_arr["use"];

        //issue_arr 값이 있을경우
        if (empty($issue_arr) === false) {
            //쿠폰 일련번호
            $param["col"]["cp_seqno"] = $issue_arr["cp_seqno"];
            
            //회사관리 일련번호 
            $param["col"]["cpn_admin_seqno"] = $issue_arr["cpn_admin_seqno"];
           
            //발급된 쿠폰 수 
            $param["col"]["issue_count"] = $issue_arr["cnt"];
        }

        //use_arr 값이 있을경우 (겹칠경우 공통항목 덮어씀)
        if (empty($use_arr) === false) {
            //쿠폰 일련번호
            $param["col"]["cp_seqno"] = $use_arr["cp_seqno"];
            
            //회사관리 일련번호 
            $param["col"]["cpn_admin_seqno"] = $use_arr["cpn_admin_seqno"];
            
            //사용된 쿠폰 수 
            $param["col"]["use_count"] = $use_arr["cnt"];

            //사용된 쿠폰의 값
            $param["col"]["use_price"] = $use_arr["use_price"];
        }

        //day_cp_use_stats 데이터 삽입
        $engineDAO->insertData($conn, $param);
    }


    //2. 소멸일자 지날시 쿠폰 사용여부 변경
    $engineDAO->updateCpUseYN($conn);
    
    if ($conn->HasFailedTrans() === true) {
        $conn->FailTrans();
        $conn->RollbackTrans();
        $conn->close();
    }

    $conn->CompleteTrans();
    
    $conn->close();
}

/**
 * 매일 새벽 2시 함수를 실행
 */
main();

?>
