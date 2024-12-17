#! /usr/bin/php -f
<?
include_once('/var/www/html/nimda/engine/common/ConnectionPool.php');
include_once(dirname(__FILE__) . '/dao/EngineDAO.php');

/** 
 * @brief 회원 등급별 매달 지급포인트, 사용포인트 통계 
 *
 * @param $dvs 지급포인트 or 사용포인트 여부
 */ 

function main($dvs) {

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

    $param = array();
    $param["dvs"] = $dvs;
    $result = $engineDAO->selectMemberPoint($conn, $param);
    $give_point = array();

    while ($result && !$result->EOF) {

        /**
         *$date_tmp[0] = "년도";
         *$date_tmp[1] = "월";
         */
        $regi_date = $result->fields["regi_date"];
        $date_tmp = explode('-', $regi_date);
        $member_grade = $result->fields["member_grade"];
        $sell_site = $result->fields["cpn_admin_seqno"];

        /**
         * 현재 년/월과 내역에 있는 년/월이 같을때
         * 해당하는 등급에 포인트를 더한다.
         */
        if ($date_tmp[0] == $last_tmp[0] && $date_tmp[1] == $last_tmp[1]) {
                $give_point[$sell_site][$member_grade] += $result->fields["point"];
        }

        $result->moveNext();
    }

    /**
     * 회사 관리 일련번호 SELECT
     */
    $param = array();
    $param["table"] = "cpn_admin";
    $param["col"] = "cpn_admin_seqno";
    $result = $engineDAO->selectData($conn, $param);

    $site_set = array();

    while ($result && !$result->EOF) {

        $site_set[] = $result->fields["cpn_admin_seqno"];
        $result->moveNext();

    }

    /**
     * 월 회원 등급 포인트 내역 테이블 삭제
     */
    $param = array();
    $param["dvs"] = $dvs;
    $param["year"] = $last_tmp[0];
    $param["mon"] = $last_tmp[1];
    $result = $engineDAO->deleteGradePointStats($conn, $param);

    /**
     * 월 회원 등급 포인트 내역 테이블에 INSERT
     */
    $param = array();
    $param["table"] = "mon_member_grade_point_stats";
    $param["col"]["year"] = $last_tmp[0];
    $param["col"]["mon"] = $last_tmp[1];
    $param["col"]["dvs"] = $dvs;

    for ($j = 0; $j < count($site_set); $j++) {

        $param["col"]["cpn_admin_seqno"] = $site_set[$j];

        for ($i = 1; $i<11; $i++) {

            $param["col"]["member_grade"] = $i;
            $param["col"]["point"] = $give_point[$site_set[$j]][$i];

            $result = $engineDAO->insertData($conn, $param);
        }

    }

$conn->close();
}

/**
 * 매월 1일마다 함수를 실행
 */
main("적립");
main("사용");

?>
