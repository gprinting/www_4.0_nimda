<?php
include_once(dirname(__FILE__) . '/CommonDAO.php');

class EngineDAO extends CommonDAO {
    function __construct() {
    }

    public function selectPayPriceByMonth($conn, $param) {
        if ($this->connectionCheck($conn) === false) {
            return false;
        }

        $query = " SELECT A.member_seqno, A.grade as now_grade, SUM(B.pay_price) as pay_price, (SELECT grade FROM member_grade_policy where mon_sales_start_price <= SUM(B.pay_price) AND mon_sales_end_price >= SUM(B.pay_price)) AS grade ";
        $query .= " FROM member  AS A ";
        $query .= " INNER JOIN member_pay_history AS B ON A.member_seqno = B.member_seqno ";
        $query .= " WHERE B.deal_date LIKE '%s%%' ";
        $query .= " GROUP BY A.member_seqno ";

        $query = sprintf($query, $param['date']);
        $rs = $conn->Execute($query);
        return $rs;
    }

    public function insertGradeHistory($conn, $param) {
        if ($this->connectionCheck($conn) === false) {
            return false;
        }

        $query = " INSERT INTO grade_change_history ";
        $query .= " (member_seqno, calculate_period, before_grade, pay_price, after_grade, state) VALUES ";
        $query .= " (%s,'%s',%s,%s,%s,%s) ";

        $query = sprintf($query,
            $param['member_seqno'],
            $param['calculate_period'],
            $param['now_grade'],
            $param['pay_price'],
            $param['grade'],
            $param['state']);
        return $conn->Execute($query);;
    }

    public function UpdateGrade($conn, $param) {
        if ($this->connectionCheck($conn) === false) {
            return false;
        }

        $query = " UPDATE member ";
        $query .= " SET grade = %s";
        $query .= " WHERE member_seqno = %s ";

        $query = sprintf($query,
            $param['grade'],
            $param['member_seqno']);

        return $conn->Execute($query);;
    }

    public function SelectParselOrders($conn) {
        if ($this->connectionCheck($conn) === false) {
            return false;
        }

        $query = " SELECT * FROM order_common AS A ";
        $query .= " INNER JOIN order_dlvr AS B ON A.order_common_seqno = B.order_common_seqno AND B.tsrs_dvs = '수신' ";
        $query .= " WHERE A.order_state IN ('3120','3420') AND B.dlvr_way = '01' AND invo_num != '' ";
        $query .= " Order by B.order_dlvr_seqno desc ";
        return $conn->Execute($query);
    }

    public function UpdateParselInfo($conn, $param) {
        $query = " UPDATE order_common ";
        $query .= " SET order_state = '3480' ";
        $query .= " WHERE order_common_seqno = %s ";

        $query = sprintf($query,
            $param['order_common_seqno']);

        return $conn->Execute($query);
    }

    /**
     * 큐에 대기중인 작업 목록 중 가장 오래된 한 개를 가져온다.
     */
    function selectStayWork($conn) {
        if (!$this->connectionCheck($conn)) return false;

        $temp = array();

        $temp["col"]  = " engine_que_seqno";
        $temp["col"] .= ",dvs";
        $temp["col"] .= ",param";

        $temp["table"] = "engine_que";

        $temp["where"]["state"] = "stay";

        $temp["order"] = "engine_que_seqno";

        $temp["limit"]["start"] = "0";
        $temp["limit"]["end"] = "1";

        return $this->selectData($conn, $temp);
    }

    /**
     * 해당 파라미터에 해당하는 작업이 이미 존재하는지 확인
     */
    function selectWork($conn, $param) {
        if (!$this->connectionCheck($conn)) return false;
        sleep(5);
        echo "업무 탐색 중...입니다...";
        
        $temp = array();
        $temp["col"] = "1";

        $temp["table"] = "engine_que";

        $temp["where"]["dvs"]   = $param["dvs"];
        $temp["where"]["param"] = $param["param"];
        $temp["where"]["state"] = "STAY";

        return $this->selectData($conn, $temp);
    }

    /**
     * 엔진 큐에 작업 추가
     */
    function insertWork($conn, $param) {
        if (!$this->connectionCheck($conn)) return false;

        $param = $this->parameterArrayEscape($conn, $param);

        $query  = "\n INSERT INTO engine_que( dvs";
        $query .= "\n                        ,param";
        $query .= "\n                        ,state";
        $query .= "\n                        ,startday";
        $query .= "\n ) VALUES ( %s";
        $query .= "\n           ,%s";
        $query .= "\n           ,%s";
        $query .= "\n           ,now()";
        $query .= "\n )";
        $query  = sprintf($query, $param["dvs"]
                                , $param["param"]
                                , $param["state"]);

        $conn->StartTrans();

        $ret = $conn->Execute($query);

        $conn->CompleteTrans();
        
        if (!$ret) {
            return "데이터 입력에 실패 하였습니다.";
        } else {
            return TRUE;
        }
    }

    /**
     * 해당 작업 상태 업데이트
     */
    function updateState($conn, $seqno, $state) {
        if (!$this->connectionCheck($conn)) return false;

        $temp = array();
        $temp["table"] = "engine_que";
        $temp["col"]["state"] = $state;
        $temp["prk"] = "engine_que_seqno";
        $temp["prkVal"] = $seqno;

        $conn->StartTrans();

        $ret = $this->updateData($conn, $temp);

        $conn->CompleteTrans();
        
        if (!$ret) {
            return "데이터 입력에 실패 하였습니다.";
        } else {
            return TRUE;
        }
    }

    /**
     * @brief 회원 포인트 조회
     * 
     * @detail 
     *
     * @param $conn  = 디비 커넥션
     * @param $param = 검색조건 파라미터
     *
     * @return
     */

    function selectMemberPoint($conn, $param) {

        if (!$conn) {
            echo "master connection failed\n";
            return false;
        }

        $query  = "\n  SELECT  A.regi_date";
        $query .= "\n         ,A.point";
        $query .= "\n         ,A.dvs";
        $query .= "\n         ,A.member_grade";
        $query .= "\n         ,B.cpn_admin_seqno";
        $query .= "\n    FROM  member_point_history A";
        $query .= "\n         ,member B";
        $query .= "\n   WHERE  A.member_seqno = B.member_seqno";
        $query .= "\n     AND  A.dvs = '" . $param["dvs"] . "'";

        //Query Cache 
        if ($param["cache"] == 1) {
            $rs = $conn->CacheExecute(1800, $query);
        } else {
            $rs = $conn->Execute($query);
        }

        return $rs;

    }

    /**
     * @brief 회원 포인트 내역 조회
     * 
     * @detail 
     *
     * @param $conn  = 디비 커넥션
     * @param $param = 검색조건 파라미터
     *
     * @return
     */

    function selectPointHistory($conn, $param) {

        if (!$conn) {
            echo "master connection failed\n";
            return false;
        }

        $query  = "\n  SELECT  A.regi_date";
        $query .= "\n         ,A.point_name";
        $query .= "\n         ,A.point";
        $query .= "\n         ,A.dvs";
        $query .= "\n         ,B.cpn_admin_seqno";
        $query .= "\n    FROM  member_point_history A";
        $query .= "\n         ,member B";
        $query .= "\n   WHERE  A.member_seqno = B.member_seqno";

        //Query Cache 
        if ($param["cache"] == 1) {
            $rs = $conn->CacheExecute(1800, $query);
        } else {
            $rs = $conn->Execute($query);
        }

        return $rs;

    }

    /**
     * @brief 회원 일련번호 검색
     *
     * @param $conn  = 디비 커넥션
     *
     * @return 
     */
    function selectMemberSeqno($conn) {
        if (!$this->connectionCheck($conn)) return false;

        $query  = "\nSELECT member_seqno";
        $query .= "\n  FROM member";

        return $conn->Execute($query);
    }

    /**
     * @brief 주문_공통 테이블에서 세달동안의 회원이 결제한 총금액 조회
     *
     * @param $conn  = 디비 커넥션
     *
     * @return 
     */
    function selectMemberPayPrice($conn, $seqno) {
        if (!$this->connectionCheck($conn)) return false;

        $seqno = $this->parameterEscape($conn, $seqno);

        $query  = "\n SELECT  SUM(pay_price) as tot_price";
        $query .= "\n   FROM  order_common";
        //테스트 아래 주석 처리
        $query .= "\n  WHERE  receipt_regi_date >= DATE_FORMAT(DATE_SUB(sysdate() , INTERVAL 3 MONTH), '%Y-%c-%d 00:00:00')";
        $query .= "\n    AND  receipt_regi_date <= DATE_FORMAT(DATE_SUB(sysdate() , INTERVAL 3 DAY), '%Y-%c-%d 23:59:59')";
        $query .= "\n    AND  member_seqno = " . $seqno;

        return $conn->Execute($query);
    }

    /**
     * @brief 회원_등급_정책 테이블 조회
     *
     * @param $conn  = 디비 커넥션
     *
     * @return
     */
    function selectMemberGradePolicy($conn) {
        if (!$this->connectionCheck($conn)) return false;

        $query  = "\n SELECT  member_grade_policy_seqno";
        $query .= "\n        ,sales_start_price";
        $query .= "\n        ,sales_end_price";
        $query .= "\n        ,grade";
        $query .= "\n   FROM  member_grade_policy";
        $query .= "\nORDER BY member_grade_policy_seqno";

        return $conn->Execute($query);
    }

    /**
     * @brief 회원 등급을 초기화
     * $param["grade"] = 등급;
     * $param["auto_grade_yn"] = 엔진에서(자동) 등급의 변경 여부;
     *
     * @param $conn  = 디비 커넥션
     *
     * @return
     */
    function initMemberGrade($conn, $param) {
    
        if (!$conn) {
            echo "master connection failed\n";
            return false;
        }

        $param = $this->parameterArrayEscape($conn, $param);
        
        $query  = "\n UPDATE member";
        $query .= "\n    SET grade = %s";
        $query .= "\n  WHERE auto_grade_yn = %s";
        
        $query  = sprintf($query, $param["grade"]
                                , $param["auto_grade_yn"]);

        return $conn->Execute($query);
    }

    /**
     * @brief 회원 등급을 변경
     * $param["member_seqno"] = 회원 일련번호;
     * $param["grade"] = 등급;
     * $param["auto_grade_yn"] = 엔진에서(자동) 등급의 변경 여부;
     *
     * @param $conn  = 디비 커넥션
     *
     * @return
     */
    function updateMemberGrade($conn, $param) {
    
        if (!$conn) {
            echo "master connection failed\n";
            return false;
        }

        $param = $this->parameterArrayEscape($conn, $param);
        
        $query  = "\n UPDATE member";
        $query .= "\n    SET grade = %s";
        $query .= "\n  WHERE member_seqno = %s";
        $query .= "\n    AND auto_grade_yn = %s";
        
        $query  = sprintf($query, $param["grade"]
                                , $param["member_seqno"]
                                , $param["auto_grade_yn"]);
        
        return $conn->Execute($query);
    }

    /**
     * @brief 회원_등급 테이블에 행 추가
     * $param["year"] = 년도;
     * $param["member_seqno"] = 회원 일련번호;
     * $param["m1"] = 1월달 등급;
     * $param["m2"] = 2월달 등급;
     * $param["m3"] = 3월달 등급;
     * $param["m4"] = 4월달 등급;
     * $param["m5"] = 5월달 등급;
     * $param["m6"] = 6월달 등급;
     * $param["m7"] = 7월달 등급;
     * $param["m8"] = 8월달 등급;
     * $param["m9"] = 9월달 등급;
     * $param["m10"] = 10월달 등급;
     * $param["m11"] = 11월달 등급;
     * $param["m12"] = 12월달 등급;
     *
     * @param $conn  = 디비 커넥션
     *
     * @return
     */
    function insertMemberGrade($conn, $param) {
        if (!$this->connectionCheck($conn)) return false;

        $excpt_arr = array("mon" => true);

        $param = $this->parameterArrayEscape($conn, $param, $excpt_arr);

        $query  = "\n INSERT INTO member_grade ( year";
        $query .= "\n                        ,member_seqno";
        $query .= "\n                        ,". $param["mon"];
        $query .= "\n ) VALUES ( %s";
        $query .= "\n           ,%s";
        $query .= "\n           ,%s";
        $query .= "\n )";
        $query  = sprintf($query, $param["year"]
                                , $param["member_seqno"]
                                , $param["monVal"]);

        $conn->StartTrans();

        $ret = $conn->Execute($query);

        $conn->CompleteTrans();
        
        if (!$ret) {
            return "데이터 입력에 실패 하였습니다.";
        } else {
            return TRUE;
        }
    }

    /**
     * @brief 회원 결제 내역(세금계산사 발행 신청)
     *
     * @param $conn  = 디비 커넥션
     *
     * @return
     */
    function selectPayHistory($conn, $param) {
        if (!$this->connectionCheck($conn)) return false;

        $query  = "\n SELECT  SUM(pay_price) AS pay_sum";
        $query .= "\n        ,member_seqno";
        $query .= "\n  FROM  member_pay_history";
        $query .= "\n WHERE  pay_year = '" . $param["year"] . "'";
        $query .= "\n   AND  pay_mon = '". $param["mon"] . "'";
        $query .= "\n   AND  order_cancel_yn = 'N'";
        $query .= "\n   AND  public_dvs = '세금계산서'";
        $query .= "\n   AND  dvs = '사용'";
        $query .= "\n   AND  prepay_use_yn = 'Y'";
        $query .= "\n   GROUP BY  member_seqno";
        $query .= "\n   ORDER BY member_seqno ASC";

        //Query Cache 
        if ($param["cache"] == 1) {
            $rs = $conn->CacheExecute(1800, $query);
        } else {
            $rs = $conn->Execute($query);
        }

        return $rs;
    }

    /**
     * @brief 회원 정보 
     *
     * @param $conn  = 디비 커넥션
     *
     * @return
     */
    function selectMemberInfo($conn, $param) {
        if (!$this->connectionCheck($conn)) return false;

        $query  = "\n SELECT  A.tel_num";
        $query .= "\n        ,A.member_name";
        $query .= "\n        ,B.zipcode";
        $query .= "\n        ,B.crn";
        $query .= "\n        ,B.repre_name";
        $query .= "\n        ,B.addr";
        $query .= "\n        ,B.addr_detail";
        $query .= "\n        ,B.tob";
        $query .= "\n        ,B.bc";
        $query .= "\n  FROM  member A";
        $query .= "\n  LEFT OUTER JOIN licensee_info B";
        $query .= "\n    ON  A.member_seqno = B.member_seqno";
        $query .= "\n WHERE  A.member_seqno = '" . $param["member_seqno"] . "'";

        //Query Cache 
        if ($param["cache"] == 1) {
            $rs = $conn->CacheExecute(1800, $query);
        } else {
            $rs = $conn->Execute($query);
        }

        return $rs;
    }

    /**
     * @brief 조정테이블에 회원 에누리 총합
     *
     * @param $conn  = 디비 커넥션
     *
     * @return
     */
    function selectAdjustDiscount($conn, $param) {
        if (!$this->connectionCheck($conn)) {
            return false;
        }

        $query  = "\n SELECT  SUM(price) AS discount_sum";
        $query .= "\n   FROM  adjust";
        $query .= "\n  WHERE  member_seqno = '" . $param["member_seqno"] . "'"; 
        $query .= "\n    AND  adjust_year = '" . $param["year"] . "'";
        $query .= "\n    AND  adjust_mon = '". $param["mon"] . "'";
        $query .= "\n    AND  discount_yn = 'Y'";

        //Query Cache 
        if ($param["cache"] == 1) {
            $rs = $conn->CacheExecute(1800, $query);
        } else {
            $rs = $conn->Execute($query);
        }

        return $rs;
    }


    function getInvoiceDataCheck($conn, $in_num) {
        if (!$this->connectionCheck($conn)) {
            return "DBCON_LOST";
        }

        $query  = "SELECT invoice_no FROM invoice_number WHERE in_number = '".$in_num."' LIMIT 1";
        $ret = $conn->Execute($query);

        if ($ret->RecordCount() > 0) {
            return "FAILED";
        } else {
            return "SUCCESS";
        }
    }


    function setInvoiceNumberinsertDataComplete($conn, $param) {
        if (!$this->connectionCheck($conn)) {
            return "DBCON_LOST";
        }

        $query  = "INSERT INTO invoice_number (";
        $query .= "ship_div, in_number, status, regi_date";
        $query .= ") VALUES (";
        $query .= "'".$param['ship_div']."', '".$param['in_number']."', '".$param['status']."', '".$param['regi_date']."'";
        $query .= ")";
        $ret = $conn->Execute($query);

        if (!$ret) {
            return "FAILED";
        } else {
            return "SUCCESS";
        }
    }

    function createZipcodeTable($conn, $param) {
        $excpt_arr = array(); echo $param['table'];
        //$param['table'] = $this->parameterArrayEscape($conn, $param['table']);

        $query = "CREATE TABLE " . $param['table'] . " (
        new_zipcode CHAR(5) NULL DEFAULT NULL,
	si_do_name VARCHAR(30) NULL DEFAULT NULL,
	si_do_name_eng VARCHAR(30) NULL DEFAULT NULL,
	si_gun_gu VARCHAR(30) NULL DEFAULT NULL,
	si_gun_gu_eng VARCHAR(30) NULL DEFAULT NULL,
	eup_myun VARCHAR(30) NULL DEFAULT NULL,
	eup_myun_eng VARCHAR(30) NULL DEFAULT NULL,
	street_name_code VARCHAR(30) NULL DEFAULT NULL,
	street_name VARCHAR(30) NULL DEFAULT NULL,
	street_name_eng VARCHAR(30) NULL DEFAULT NULL,
	is_underground VARCHAR(30) NULL DEFAULT NULL,
	building_num_major VARCHAR(30) NULL DEFAULT NULL,
	building_num_minor VARCHAR(30) NULL DEFAULT NULL,
	building_mng_num VARCHAR(30) NULL DEFAULT NULL,
	plural_dlvr_name VARCHAR(30) NULL DEFAULT NULL,
	si_gun_gu_building_name VARCHAR(30) NULL DEFAULT NULL,
	law_defined_dong_code VARCHAR(30) NULL DEFAULT NULL,
	law_defined_dong_name VARCHAR(30) NULL DEFAULT NULL,
	ri_name VARCHAR(30) NULL DEFAULT NULL,
	admin_dong_name VARCHAR(30) NULL DEFAULT NULL,
	is_mountain VARCHAR(30) NULL DEFAULT NULL,
	lot_num_major_num VARCHAR(30) NULL DEFAULT NULL,
	eup_myun_dong_serial_num VARCHAR(30) NULL DEFAULT NULL,
	lot_num_minor_num VARCHAR(30) NULL DEFAULT NULL,
	old_zipcode VARCHAR(30) NULL DEFAULT NULL,
	zipcode_serial_num VARCHAR(30) NULL DEFAULT NULL
)";

        $conn->Execute($query);
    }

    function insertZipcode($conn, $param) {
        if (!$this->connectionCheck($conn)) {
            return "DBCON_LOST";
        }

        $excpt_arr = array();
        $table = str_replace("'","",$param['table']);
        $param = $this->parameterArrayEscape($conn, $param);

        $query  = "INSERT INTO ". $table ." (";
        $query .= "new_zipcode ";
        $query .= ", si_do_name ";
        $query .= ", si_do_name_eng ";
        $query .= ", si_gun_gu ";
        $query .= ", si_gun_gu_eng ";
        $query .= ", eup_myun ";
        $query .= ", eup_myun_eng ";
        $query .= ", street_name_code ";
        $query .= ", street_name ";
        $query .= ", street_name_eng ";
        $query .= ", is_underground ";
        $query .= ", building_num_major ";
        $query .= ", building_num_minor ";
        $query .= ", building_mng_num ";
        $query .= ", plural_dlvr_name ";
        $query .= ", si_gun_gu_building_name ";
        $query .= ", law_defined_dong_code ";
        $query .= ", law_defined_dong_name ";
        $query .= ", ri_name ";
        $query .= ", admin_dong_name ";
        $query .= ", is_mountain ";
        $query .= ", lot_num_major_num ";
        $query .= ", eup_myun_dong_serial_num ";
        $query .= ", lot_num_minor_num ";
        $query .= ", old_zipcode ";
        $query .= ", zipcode_serial_num ";
        $query .= ") VALUES (";
        $query .= $param["new_zipcode"];
        $query .= ", " . $param["si_do_name"];
        $query .= ", " . $param["si_do_name_eng"];
        $query .= ", " . $param["si_gun_gu"];
        $query .= ", " . $param["si_gun_gu_eng"];
        $query .= ", " . $param["eup_myun"];
        $query .= ", " . $param["eup_myun_eng"];
        $query .= ", " . $param["street_name_code"];
        $query .= ", " . $param["street_name"];
        $query .= ", " . $param["street_name_eng"];
        $query .= ", " . $param["is_underground"];
        $query .= ", " . $param["building_num_major"];
        $query .= ", " . $param["building_num_minor"];
        $query .= ", " . $param["building_mng_num"];
        $query .= ", " . $param["plural_dlvr_name"];
        $query .= ", " . $param["si_gun_gu_building_name"];
        $query .= ", " . $param["law_defined_dong_code"];
        $query .= ", " . $param["law_defined_dong_name"];
        $query .= ", " . $param["ri_name"];
        $query .= ", " . $param["admin_dong_name"];
        $query .= ", " . $param["is_mountain"];
        $query .= ", " . $param["lot_num_major_num"];
        $query .= ", " . $param["eup_myun_dong_serial_num"];
        $query .= ", " . $param["lot_num_minor_num"];
        $query .= ", " . $param["old_zipcode"];
        $query .= ", " . $param["zipcode_serial_num"];
        $query .= ")";$conn->debug = 1;
        $ret = $conn->Execute($query);
        $conn->debug = 0;
        if (!$ret) {
            return "FAILED";
        } else {
            return "SUCCESS";
        }


        return $query;
    }

    /**
     * @brief 주문대기(110) 상태인 주문만 선택
     *
     * @param $conn = connection identifier
     *
     * @return 검색결과
     */
    function selectCartOrderCommon($conn) {
        if (!$this->connectionCheck($conn)) return false;

        $query  = "\n SELECT  A.order_common_seqno";
        $query .= "\n   FROM  order_common AS A";
        $query .= "\n  WHERE  A.order_state = '1120'";
        $query .= "\n    AND  A.order_regi_date < date_add(now(), interval -7 day)";

        return $conn->Execute($query);
    }

    /**
     * @brief 주문 삭제용 데이터 검색
     *
     * @param $conn = connection identifer
     * @param $order_common_seqno = 주문_공통_일련번호
     *
     * @return 쿼리실행결과
     */
    function selectOrderInfo($conn, $order_common_seqno) {
        if ($this->connectionCheck($conn) === false) {
            return false;
        }

        $order_common_seqno = $this->parameterEscape($conn, $order_common_seqno);

        $query  = "\n SELECT  B.order_detail_seqno   AS s_detail_seqno";
        $query .= "\n        ,B.order_detail_dvs_num AS s_detail_dvs_num";
        $query .= "\n        ,B.cate_sortcode        AS s_cate_sortcode";
        $query .= "\n        ,B.amt                  AS s_amt";
        $query .= "\n        ,B.amt_unit_dvs         AS s_amt_unit_dvs";
        $query .= "\n        ,B.sell_price           AS s_sell_price";
        $query .= "\n        ,B.add_after_price      AS s_after_price";
        $query .= "\n        ,B.after_use_yn         AS s_after_use_yn";
        $query .= "\n        ,B.stan_name            AS s_stan_name";
        $query .= "\n        ,C.order_detail_brochure_seqno AS b_detail_seqno";
        $query .= "\n        ,C.order_detail_dvs_num        AS b_detail_dvs_num";
        $query .= "\n        ,C.cate_sortcode               AS b_cate_sortcode";
        $query .= "\n        ,C.page_amt                    AS b_page_amt";
        $query .= "\n        ,C.amt                         AS b_amt";
        $query .= "\n        ,C.amt_unit_dvs                AS b_amt_unit_dvs";
        $query .= "\n        ,C.sell_price                  AS b_sell_price";
        $query .= "\n        ,C.add_after_price             AS b_after_price";
        $query .= "\n   FROM order_common AS A";
        $query .= "\n   LEFT OUTER JOIN order_detail AS B";
        $query .= "\n     ON A.order_common_seqno = B.order_common_seqno";
        $query .= "\n   LEFT OUTER JOIN order_detail_brochure AS C";
        $query .= "\n     ON A.order_common_seqno = C.order_common_seqno";
        $query .= "\n  WHERE A.order_common_seqno = %s";

        $query  = sprintf($query, $order_common_seqno);

        $rs = $conn->Execute($query);

        return $rs;
    }

    /**
     * @brief 각 에러단계에 따라서 주문관련 데이터 삭제
     *
     * @param $conn  = connection identifier
     * @param $param = 데이터 파라미터
     *
     * @return option html
     */
    function deleteOrderData($conn, $param) {
        if ($this->connectionCheck($conn) === false) {
            return false;
        }

        $except_arr = array("table" => true);

        $param = $this->parameterArrayEscape($conn, $param, $except_arr);

        $query  = "DELETE";
        $query .= "  FROM %s";
        $query .= " WHERE order_common_seqno = %s";

        $query  = sprintf($query, $param["table"]
                , $param["order_common_seqno"]);

        return $conn->Execute($query);
    }

    /**
     * @brief 주문_상세_건수_파일 데이터 삭제
     *
     * @param $conn  = connection identifier
     * @param $param = 데이터 파라미터
     *
     * @return option html
     */
    function deleteOrderDetailCountFile($conn, $param) {
        if ($this->connectionCheck($conn) === false) {
            return false;
        }

        $order_detail_seqno = $this->arr2paramStr($conn,
                $param["order_detail_seqno"]);

        $query  = "DELETE";
        $query .= "  FROM order_detail_count_file";
        $query .= " WHERE order_detail_seqno IN (%s)";

        $query  = sprintf($query, $order_detail_seqno);

        return $conn->Execute($query);
    }

    /**
     * @brief 주문_후공정_내역 데이터 삭제
     *
     * @param $conn  = connection identifier
     * @param $param = 데이터 파라미터
     *
     * @return option html
     */
    function deleteOrderAfterHistory($conn, $param) {
        if ($this->connectionCheck($conn) === false) {
            return false;
        }

        $order_detail_dvs_num = $this->arr2paramStr($conn,
                $param["order_detail_dvs_num"]);

        $query  = "DELETE";
        $query .= "  FROM order_after_history";
        $query .= " WHERE order_detail_dvs_num IN (%s)";

        $query  = sprintf($query, $order_detail_dvs_num);

        return $conn->Execute($query);
    }

    /**
     * @brief 주문공통 데이터 삭제
     *
     * @param $conn  = connection identifier
     * @param $param = 삭제조건 파라미터
     *
     * @return option html
     */
    function deleteOrderCommon($conn, $param) {
        if ($this->connectionCheck($conn) === false) {
            return false; 
        }

        $param = $this->parameterArrayEscape($conn, $param);

        $query  = "\n DELETE FROM order_common";
        $query .= "\n WHERE order_common_seqno = %s";

        $query  = sprintf($query, $param["order_common_seqno"]);

        return $conn->Execute($query);
    }

    /**
     * @brief 입금대기 상태인 주문만 선택
     *
     * @param $conn = connection identifier
     *
     * @return 검색결과
     */
    function selectDepositOrderCommon($conn) {
        if (!$this->connectionCheck($conn)) return false;

        $query  = "\n SELECT  A.order_common_seqno";
        $query .= "\n        ,A.title";
        $query .= "\n        ,A.member_seqno";
        $query .= "\n        ,A.cate_sortcode";
        $query .= "\n        ,A.order_num";
        $query .= "\n        ,A.sell_price";
        $query .= "\n        ,A.pay_price";
        $query .= "\n        ,A.grade_sale_price";
        $query .= "\n        ,A.member_sale_price";
        $query .= "\n        ,A.use_point_price";
        $query .= "\n        ,A.cp_price";
        $query .= "\n        ,A.order_lack_price";
        $query .= "\n        ,A.add_after_price";
        $query .= "\n        ,A.add_opt_price";
        $query .= "\n   FROM  order_common AS A";
        $query .= "\n  WHERE  A.order_lack_price != 0";

        return $conn->Execute($query);
    }

    /**
     * @brief 카테고리 낱장여부 검색
     *
     * @param $conn  = connection identifier
     * @param $cate_sortcode = 카테고리 분류코드
     *
     * @return 검색결과
     */
    function selectCateFlattypYn($conn, $cate_sortcode) {
        if ($this->connectionCheck($conn) === false) {
            return false;
        }

        $temp = array();
        $temp["col"] = "flattyp_yn";
        $temp["table"] = "cate";
        $temp["where"]["sortcode"] = $cate_sortcode;

        $rs = $this->selectData($conn, $temp);

        return $rs->fields["flattyp_yn"];
    }

    /**
     * @brief 해당 회원의 선입금액 검색
     *
     * @param $conn = connection identifier
     *
     * @return 검색결과
     */
    function selectMemberPriceInfo($conn, $seqno) {
        if (!$this->connectionCheck($conn)) return false;

        $seqno = $this->parameterEscape($conn, $seqno);

        //$query  = "\n SELECT  A.prepay_price";
        $query  = "\n SELECT  A.prepay_price_money";
        $query .= "\n        ,A.prepay_price_card";
        $query .= "\n        ,A.order_lack_price";
        $query .= "\n        ,A.cumul_sales_price";
        $query .= "\n   FROM  member AS A";
        $query .= "\n  WHERE  A.member_seqno = %s";

        $query  = sprintf($query, $seqno);

        $rs = $conn->Execute($query);

        return $rs->fields;
    }

    /**
     * @brief 선입금이 충분한 주문금액의 주문상태 접수대기로 변경
     * 주분부족금액 0원 처리
     *
     * @param $conn = connection identifier
     * @param $param = 수정조건/값 파라미터
     *
     * @return 검색결과
     */
    function updateDepositOrderState($conn, $param) {
        if (!$this->connectionCheck($conn)) return false;

        $param = $this->parameterArrayEscape($conn, $param);

        $query  = "\n UPDATE  order_common";
        $query .= "\n    SET  order_state = %s";
        $query .= "\n        ,order_lack_price = 0";
        $query .= "\n        ,depo_finish_date = now()";
        $query .= "\n  WHERE  order_common_seqno = %s";

        $query  = sprintf($query, $param["state"]
                                , $param["seqno"]);

        $rs = $conn->Execute($query);

        return $rs;
    }

    /**
     * @brief 주문_공통 선입금액 변경
     *
     * @param $conn = connection identifier
     * @param $param = 수정조건/값 파라미터
     *
     * @return 검색결과
     */
    function updateDepositOrderLackPrice($conn, $param) {
        if (!$this->connectionCheck($conn)) return false;

        $param = $this->parameterArrayEscape($conn, $param);

        $query  = "\n UPDATE  order_common";
        $query .= "\n    SET  order_lack_price = 0";
        $query .= "\n  WHERE  order_common_seqno = %s";

        $query  = sprintf($query, $param["order_lack_price"]
                                , $param["seqno"]);

        $rs = $conn->Execute($query);

        return $rs;
    }

    /**
     * @brief 선입금이 충분한 주문금액의 주문상세상태 조판대기로 변경
     * 주분부족금액 0원 처리
     *
     * @param $conn = connection identifier
     * @param $param = 수정조건/값 파라미터
     *
     * @return 검색결과
     */
    function updateDepositOrderDetailState($conn, $param) {
        if (!$this->connectionCheck($conn)) return false;

        $except_arr = array("table" => true);

        $param = $this->parameterArrayEscape($conn, $param, $except_arr);

        $query  = "\n UPDATE  %s";
        $query .= "\n    SET  state = %s";
        $query .= "\n  WHERE  order_common_seqno = %s";

        $query  = sprintf($query, $param["table"]
                                , $param["state"]
                                , $param["seqno"]);

        $rs = $conn->Execute($query);

        return $rs;
    }

    /**
     * @brief 해당 회원의 선입금액/주문부족금액 수정
     *
     * @param $conn = connection identifier
     * @param $param = 수정조건/값 파라미터
     *
     * @return 검색결과
     */
    function updateMemberPriceInfo($conn, $param) {
        if (!$this->connectionCheck($conn)) return false;

        $param = $this->parameterArrayEscape($conn, $param);

        $query  = "\n UPDATE  member";
        $query .= "\n    SET  prepay_price_money = %s";
        $query .= "\n        ,prepay_price_card  = %s";
        $query .= "\n        ,order_lack_price   = %s";
        //$query .= "\n    SET  order_lack_price = order_lack_price + (%s + 0)";
        //$query .= "\n        ,cumul_sales_price = IFNULL(cumul_sales_price, 0) + %s";
        $query .= "\n        ,cumul_sales_price  = %s";
        $query .= "\n  WHERE  member_seqno = %s";

        $query  = sprintf($query, $param["prepay_price_money"]
                                , $param["prepay_price_card"]
                                , $param["order_lack_price"]
                                , $param["cumul_sales_price"]
                                , $param["seqno"]);

        $rs = $conn->Execute($query);

        return $rs;
    }

    /**
     * @brief 해당 회원의 결제내역 입력
     *
     * @param $conn = connection identifier
     * @param $param = 입력값 파라미터
     *
     * @return 검색결과
     */
    function insertMemberPayHistory($conn, $param) {
        if ($this->connectionCheck($conn) === false) {
            return false;
        }

        $param = $this->parameterArrayEscape($conn, $param);

        $query  = "\n INSERT INTO member_pay_history (";
        $query .= "\n      member_seqno";
        $query .= "\n     ,deal_date";
        $query .= "\n     ,order_num";
        $query .= "\n     ,dvs";
        $query .= "\n     ,sell_price";
        $query .= "\n     ,sale_price";
        $query .= "\n     ,pay_price";
        $query .= "\n     ,depo_price";
        $query .= "\n     ,exist_prepay";
        $query .= "\n     ,prepay_bal";
        $query .= "\n     ,state";
        $query .= "\n     ,deal_num";
        $query .= "\n     ,order_cancel_yn";
        $query .= "\n     ,prepay_use_yn";
        $query .= "\n     ,input_typ";
        $query .= "\n     ,cont";
        $query .= "\n ) VALUES (";
        $query .= "\n      %s";
        $query .= "\n     ,now()";
        $query .= "\n     ,%s";
        $query .= "\n     ,%s";
        $query .= "\n     ,%s";
        $query .= "\n     ,%s";
        $query .= "\n     ,%s";
        $query .= "\n     ,%s";
        $query .= "\n     ,%s";
        $query .= "\n     ,%s";
        $query .= "\n     ,%s";
        $query .= "\n     ,%s";
        $query .= "\n     ,%s";
        $query .= "\n     ,%s";
        $query .= "\n     ,%s";
        $query .= "\n     ,%s";
        $query .= "\n )";

        $query  = sprintf($query, $param["member_seqno"]
                                , $param["order_num"]
                                , $param["dvs"]
                                , $param["sell_price"]
                                , $param["sale_price"]
                                , $param["pay_price"]
                                , $param["depo_price"]
                                , $param["exist_prepay"]
                                , $param["prepay_bal"]
                                , $param["state"]
                                , $param["deal_num"]
                                , $param["order_cancel_yn"]
                                , $param["prepay_use_yn"]
                                , $param["input_typ"]
                                , $param["cont"]);

        return $conn->Execute($query);
    }


    /**
     * @brief 주문상태 구분값 검색
     *
     * @param $conn = connection identifier
     *
     * @return 카테고리명
     */
    function selectStateAdmin($conn) {
        if ($this->connectionCheck($conn) === false) {
            return false; 
        }

        $query  = "\n   SELECT  A.state_code";
        $query .= "\n          ,A.erp_state_name";
        $query .= "\n     FROM  state_admin AS A";
        $query .= "\n ORDER BY  A.state_code";

        $rs = $conn->Execute($query);

        return $rs;
    }

    /**
     * @brief 조판대기 상태인 주문만 선택
     *
     * @param $conn = connection identifier
     *
     * @return 검색결과
     */
    function selectTypsetOrderCommon($conn, $order_state) {
        if (!$this->connectionCheck($conn)) return false;

        $query  = "\n SELECT  A.order_common_seqno";
        $query .= "\n   FROM  order_common AS A";
        $query .= "\n  WHERE  A.order_state = '%s'";
        $query .= "\n    AND  A.order_regi_date < now()";

        $query  = sprintf($query, $order_state);

        return $conn->Execute($query);
    }

    /**
     * @brief 조판대기 상태이고 외주 주문만 선택
     *
     * @param $conn = connection identifier
     *
     * @return 검색결과
     */
    function selectOutsourceOrderCommon($conn, $param) {
        if (!$this->connectionCheck($conn)) return false;

        $query  = "\nSELECT A.order_common_seqno";
        $query .= "\n      ,B.order_detail_seqno";
        $query .= "\n      ,C.outsource_etprs_cate";
        $query .= "\n  FROM order_common AS A";
        $query .= "\n      ,order_detail AS B";
        $query .= "\n      ,cate AS C";
        $query .= "\n WHERE A.order_common_seqno = B.order_common_seqno";
        $query .= "\n   AND A.cate_sortcode = C.sortcode";
        $query .= "\n   AND A.order_state = '2120'";
        $query .= "\n   AND C.typset_way = 'OUTSOURCE'";
        $query .= "\n   AND C.outsource_etprs_cate = '%s'";
        $query .= "\n   AND B.receipt_finish_date < '%s'";

        $query  = sprintf($query, $param["outsource_etprs_cate"]
                ,$param["receipt_finish_date"]);

        return $conn->Execute($query);
    }

    /**
     * @brief 주문_파일 정보 검색
     *
     * @param $conn = connection identifier
     * @param $param = 검색조건 파라미터
     *
     * @return 검색결과
     */
    function selectOrderFile($conn, $param) {
        if ($this->connectionCheck($conn) === false) {
            return false;
        }

        $param = $this->parameterArrayEscape($conn, $param);

        $query  = "\n SELECT  A.file_path";
        $query .= "\n        ,A.save_file_name";
        $query .= "\n        ,A.origin_file_name";
        $query .= "\n        ,A.order_file_seqno";
        $query .= "\n   FROM  order_file AS A";
        $query .= "\n  WHERE  1 = 1";
        $query .= "\n    AND  A.order_common_seqno = ";
        $query .= $param["order_common_seqno"];

        $rs = $conn->Execute($query);

        return $rs->fields;
    }

    /**
     * @brief 회원 정보검색
     *
     * @param $conn = connection identifier
     *
     * @return 카테고리명
     */
    function selectMemberDirectDlvrInfo($conn) {
        if ($this->connectionCheck($conn) === false) {
            return false; 
        }

        $query  = "\n   SELECT  A.member_seqno";
        $query .= "\n          ,A.member_name";
        $query .= "\n          ,A.tel_num";
        $query .= "\n          ,A.cell_num";
        $query .= "\n          ,A.final_order_date";
        $query .= "\n          ,A.member_dvs";
        $query .= "\n          ,A.direct_dlvr_yn";
        $query .= "\n     FROM  member AS A";

        $rs = $conn->Execute($query);

        return $rs;

    }

    /**
     * @brief 월배송 정보 검색
     *
     * @param $conn = connection identifier
     *
     * @return 카테고리명
     */
    function selectMemberDirectDlvr($conn, $param) {
        if ($this->connectionCheck($conn) === false) {
            return false; 
        }

        $param = $this->parameterArrayEscape($conn, $param);

        $query  = "\n   SELECT  A.member_seqno";
        $query .= "\n          ,A.start_period";
        $query .= "\n          ,A.end_period";
        $query .= "\n     FROM  direct_dlvr_req AS A";
        $query .= "\n    WHERE  A.member_seqno = %s";
        
        $query  = sprintf($query, $param["member_seqno"]);

        $rs = $conn->Execute($query);

        return $rs;
    }

    /**
     * @brief 회원 월매출 검색
     *
     * @param $conn = connection identifier
     * @param $param = 검색조건 파라미터
     *
     * @return 검색결과
     */
    function selectMemberMonthlySales($conn, $param) {
        if ($this->connectionCheck($conn) === false) {
            return false;
        }

        $param = $this->parameterArrayEscape($conn, $param);

        $query  = "\n SELECT  /* EngineDAO - selectMemberMonthlySales */";
        $query .= "\n         SUM(B.net_sales_price) AS net";
        $query .= "\n        ,SUM(B.card_net_sales_price) AS card_net";
        $query .= "\n   FROM  member AS A";
        $query .= "\n        ,day_sales_stats AS B";
        $query .= "\n  WHERE  1 = 1";
        $query .= "\n    AND  A.member_seqno = B.member_seqno";
        $query .= "\n    AND  A.member_seqno = ";
        $query .= $param["member_seqno"];
        //등록일
        if ($this->blankParameterCheck($param ,"from")) {
            $from = substr($param["from"], 1, -1);
            $query .="\n      AND  B.input_date >= '" . $from . "'";
        }
        if ($this->blankParameterCheck($param ,"to")) {
            $to = substr($param["to"], 1, -1);
            $query .="\n      AND  B.input_date <= '" . $to . "'";
        }

        $rs = $conn->Execute($query);

        return $rs->fields;
    }

    /**
     * @brief 직배여부 업데이트
     *
     * @param $conn  = 디비 커넥션
     *
     * @return
     */
    function updateMemberDirectDlvrYn($conn, $param) {
        if ($this->connectionCheck($conn) === false) {
            return false;
        }

        $param = $this->parameterArrayEscape($conn, $param);
        
        $query  = "\n UPDATE member";
        $query .= "\n    SET direct_dlvr_yn = %s";
        $query .= "\n  WHERE member_seqno = %s";
        
        $query  = sprintf($query, $param["direct_dlvr_yn"]
                                , $param["member_seqno"]);

        return $conn->Execute($query);
    }

    /**
     * @brief 직배기간 update
     *
     * @param $conn  = 디비 커넥션
     *
     * @return
     */
    function updateMemberDirectDlvrPeriod($conn, $param) {
        if ($this->connectionCheck($conn) === false) {
            return false;
        }

        $param = $this->parameterArrayEscape($conn, $param);
        
        $query  = "\n UPDATE direct_dlvr_req";
        $query .= "\n    SET end_period = %s";
        $query .= "\n  WHERE member_seqno = %s";
        
        $query  = sprintf($query, $param["end_period"]
                                , $param["member_seqno"]);

        $resultSet = $conn->Execute($query);

        if ($resultSet === FALSE) {
            return false;
        } else {
            return true;
        }
    }



    /**
     * @brief 일별집계 엔진 회원 일련번호검색
     *
     * @param $conn  = 디비 커넥션
     *
     * @return
     */
    function selectPayHistoryMemberSeqno($conn, $param) {
        if ($this->connectionCheck($conn) === false) {
            return false;
        }

        $query  = "\n SELECT  DISTINCT /* EngineDAO.php */";
        $query .= "\n         /*일별집계 엔진 회원 일련번호 검색*/";
        $query .= "\n         A.member_seqno";
        $query .= "\n   FROM  member_pay_history AS A";
        $query .= "\n  WHERE  1 = 1";
        $query .= "\n    AND  A.deal_date like '%%%s%%'";
        
        $query  = sprintf($query, $param["deal_date"]);

        $rs = $conn->Execute($query);
        
        return $rs;
    }

    /**
     * @brief 일별집계 엔진 회원 일데이터합계
     *
     * @param $conn  = 디비 커넥션
     *
     * @return
     */
    function selectPayHistoryByMemberSeqno($conn, $param) {
        if ($this->connectionCheck($conn) === false) {
            return false;
        }

        // 이스케이프 거니 like절에 문제 생겨서 뺌

        $query  = "\n  SELECT  /* EngineDAO.php */";
        $query .= "\n          /*일별집계 엔진 회원 일련번호 검색*/";
        $query .= "\n          A.sell_price";
        $query .= "\n         ,A.sale_price";
        $query .= "\n         ,A.pay_price";
        $query .= "\n         ,A.card_pay_price";
        $query .= "\n         ,A.depo_price";
        $query .= "\n         ,A.card_depo_price";
        $query .= "\n    FROM  member_pay_history AS A";
        $query .= "\n   WHERE  1 = 1";
        $query .= "\n     AND  A.member_seqno = '%s'";
        $query .= "\n     AND  A.deal_date like '%%%s%%'";
        $query .= "\nORDER BY  A.deal_date ASC";
        
        $query  = sprintf($query, $param["member_seqno"]
                                , $param["deal_date"]);

        $rs = $conn->Execute($query);

        return $rs;
    }

    /**
     * @brief 엔진으로 일매출 업데이트
     *
     * @param $conn = connection identifier
     * @param $param = 수정조건/값 파라미터
     *
     * @return 
     */
    function updateDaySalesStats($conn, $param) {
        if (!$this->connectionCheck($conn)) return false;

        $param = $this->parameterArrayEscape($conn, $param);

        $query  = "\n UPDATE  day_sales_stats";
        $query .= "\n    SET  sales_price = %s";
        $query .= "\n        ,sale_price  = %s";
        $query .= "\n        ,net_sales_price       = %s";
        $query .= "\n        ,card_net_sales_price  = %s";
        $query .= "\n        ,depo_price      = %s";
        $query .= "\n        ,card_depo_price = %s";
        $query .= "\n  WHERE  member_seqno = %s";
        $query .= "\n    AND  input_date   = %s";

        $query  = sprintf($query, $param["sales_price"]
                                , $param["sale_price"]
                                , $param["net_sales_price"]
                                , $param["card_net_sales_price"]
                                , $param["depo_price"]
                                , $param["card_depo_price"]
                                , $param["member_seqno"]
                                , $param["input_date"]);

        $rs = $conn->Execute($query);

        return $rs;
    }

}
?>
