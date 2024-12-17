#! /usr/bin/php -f
<?
#! /usr/local/php/bin/php -f
include_once('/var/www/html/nimda/engine/common/ConnectionPool.php');
include_once(dirname(__FILE__) . '/dao/EngineDAO.php');

/** 
 * @brief  회원가입 포인트 ,상품주문 포인트 
 *        ,관리자 지급 포인트 ,등급 포인트 통계
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
    //$param["dvs"] = $dvs;
    $result = $engineDAO->selectPointHistory($conn, $param);

    /**
     * $join_point : 회원가입 포인트
     * $order_point : 상품주문 포인트
     * $admin_point : 관리자 지급 포인트
     * $grade_point : 등급 포인트
     * 
     */
    $join_point = array();
    $order_point = array();
    $admin_point = array();
    $grade_point = array();
    $use_point = array();

    while ($result && !$result->EOF) {

        /**
         *$date_tmp[0] = "년도";
         *$date_tmp[1] = "월";
         */
        $regi_date = $result->fields["regi_date"];
        $date_tmp = explode('-', $regi_date);
        $sell_site = $result->fields["cpn_admin_seqno"];
        $point_name = $result->fields["point_name"];
        $dvs = $result->fields["dvs"];

        /**
         * 현재 년/월과 내역에 있는 년/월이 같을때
         * 해당하는 등급에 포인트를 더한다.
         */
        if ($date_tmp[0] == $last_tmp[0] && $date_tmp[1] == $last_tmp[1]) {

            if ($point_name == "회원가입 포인트" && $dvs == "적립") {

                $join_point[$sell_site] += $result->fields["point"];

            } else if ($point_name == "상품주문 포인트" && $dvs == "적립") {

                $order_point[$sell_site] += $result->fields["point"];

            } else if (($point_name == "관리자지급 포인트" || $point_name == "소셜추천") && $dvs == "적립") {

                $admin_point[$sell_site] += $result->fields["point"];

            } else if ($point_name == "등급 포인트" && $dvs == "적립") {

                $grade_point[$sell_site] += $result->fields["point"];

            } else if ($dvs == "사용") {

                $use_point[$sell_site] += $result->fields["point"];

            }
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
    $result = $engineDAO->deletePointStats($conn, $param);

    /**
     * 월 회원 등급 포인트 내역 테이블에 INSERT
     */
    $param = array();
    $param["table"] = "mon_point_stats";
    $param["col"]["year"] = $last_tmp[0];
    $param["col"]["mon"] = $last_tmp[1];

    for ($j = 0; $j < count($site_set); $j++) {

        $param["col"]["cpn_admin_seqno"] = $site_set[$j];

        $param["col"]["member_join_point"] = $join_point[$site_set[$j]];
        $param["col"]["prdtorder_point"] = $order_point[$site_set[$j]];
        $param["col"]["admin_give_point"] = $admin_point[$site_set[$j]];
        $param["col"]["grade_point"] = $grade_point[$site_set[$j]];
        $param["col"]["tot_recoup_point"] = $use_point[$site_set[$j]];

        $result = $engineDAO->insertData($conn, $param);

    }

$conn->close();
}

/**
 * 매월 1일마다 함수를 실행
 */
main();

?>
