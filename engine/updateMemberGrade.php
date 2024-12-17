#! /usr/local/php/bin/php -f
<?
include_once('/home/dprinting/nimda/engine/common/ConnectionPool.php');
include_once(dirname(__FILE__) . '/dao/EngineDAO.php');

/** 
 * @brief 이전3개월 실적에 따른 회원 등급 변경 
 */ 

function main() {

    $connectionPool = new ConnectionPool();
    $conn = $connectionPool->getPooledConnection();
    $engineDAO = new EngineDAO();

    $conn->StartTrans();

    /**
     * 회원 테이블의 등급 초기화
     * 주문_공통 테이블에서 회원이 결제한 총금액 및 회원번호 조회
     * 회원_등급_정책 테이블에서 결제금액에 따른 등급 조회
     * 회원 테이블의 등급 업데이트
     * 회원_등급 테이블에 데이터 삽입
     */

    $member_seqno = 0;
    $tot_price = 0;
    $member_grade =0;

    //년, 월, 일
    //새벽 2시에 crontab 돌리기 때문에 당시 시간에서 여유있게 3시간 빼줌
    $time = time(); 
    $year = date("Y", strtotime("-3 hour", $time));
    $mon = date("m", strtotime("-3 hour", $time));
    
    /* 회원 테이블의 등급 초기화 */
    $param = array();
    $param["grade"] = "1";
    $param["auto_grade_yn"] = "Y";
    $engineDAO->initMemberGrade($conn, $param);

    /* 주문_공통 테이블에서 회원이 결제한 총금액 및 회원번호 조회 */
    /* 회원 일련번호 가져옴 */
    $result = $engineDAO->selectMemberSeqno($conn);

    while ($result && !$result->EOF) {
        //회원번호
        $member_seqno = $result->fields["member_seqno"];

        //결제금액
        $tot_price = $engineDAO->selectMemberPayPrice($conn, $member_seqno)->fields["tot_price"];
 
        if (!$tot_price) {
           $tot_price = 0;
        }

        /* 회원_등급_정책 테이블에서 결제금액에 따른 등급 조회 */
        $rs_grade = $engineDAO->selectMemberGradePolicy($conn);

        while ($rs_grade && !$rs_grade->EOF) {
            $member_grade_policy_seqno = $rs_grade->fields["member_grade_policy_seqno"];
            $sales_start_price = $rs_grade->fields["sales_start_price"];
            $sales_end_price = $rs_grade->fields["sales_end_price"];
            $grade = $rs_grade->fields["grade"];

            //10등급의 경우(VVIP), 맥스값이 무제한임
            if ((int)$member_grade_policy_seqno === 10) {
                $member_grade = 10;
                break;
            }
            
            //가격에 따라 등급 정함
            if ($tot_price >= $sales_start_price && $tot_price <= $sales_end_price) {
                $member_grade = $grade;
                break;
            }
            
            $rs_grade->moveNext();
        }

        /* 회원 테이블의 등급 업데이트 */
        $param = array();
        $param["member_seqno"] = $member_seqno;
        $param["grade"] = $member_grade;
        $param["auto_grade_yn"] = "Y";
        $engineDAO->updateMemberGrade($conn, $param);

        /* 회원_등급 테이블에 데이터 삽입 */
        $param = array();
        $param["year"] = $year;
        $param["member_seqno"] = $member_seqno;
        if ($mon === '01') {
            $param["mon"] = "m1";
        } else if ($mon === '02') {
            $param["mon"] = "m2";
        } else if ($mon === '03') {
            $param["mon"] = "m3";
        } else if ($mon === '04') {
            $param["mon"] = "m4";
        } else if ($mon === '05') {
            $param["mon"] = "m5";
        } else if ($mon === '06') {
            $param["mon"] = "m6";
        } else if ($mon === '07') {
            $param["mon"] = "m7";
        } else if ($mon === '08') {
            $param["mon"] = "m8";
        } else if ($mon === '09') {
            $param["mon"] = "m9";
        } else if ($mon === '10') {
            $param["mon"] = "m10";
        } else if ($mon === '11') {
            $param["mon"] = "m11";
        } else if ($mon === '12') {
            $param["mon"] = "m12";
        } 
        $param["monVal"] = $member_grade;
        $engineDAO->insertMemberGrade($conn, $param);
    
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
 * 매월 1일 02:00 마다 함수를 실행
 */
main();

?>
