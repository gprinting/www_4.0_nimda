<?
include_once(dirname(__FILE__) . '/CommonDAO.php');

class PrintPriceRegiDAO extends CommonDAO {
    function __construct() {
    }

    /**
     * @brief 인쇄 테이블에서 일련번호 검색
     *
     * @param $conn  = 디비 커넥션
     * @param $param = 검색조건 파라미터
     *
     * @return 
     */
    function selectPrintSeqno($conn, $param) {
        if (!$this->connectionCheck($conn)) return false;

        $temp = array();
        $temp["col"] = "print_seqno AS seqno";

        $temp["table"] = "print";

        $temp["where"]["extnl_brand_seqno"] = $param["extnl_brand_seqno"];
        $temp["where"]["top"]               = $param["top"];
        $temp["where"]["name"]              = $param["name"];
        $temp["where"]["crtr_tmpt"]         = $param["crtr_tmpt"];
        $temp["where"]["crtr_unit"]         = $param["crtr_unit"];
        $temp["where"]["affil"]             = $param["affil"];
        $temp["where"]["wid_size"]          = $param["wid_size"];
        $temp["where"]["vert_size"]         = $param["vert_size"];

        return $this->selectData($conn, $temp);
    }

    /**
     * @brief 인쇄 정보 입력
	 *
     * @param $conn  = 디비 커넥션 
     * @param $param = 입력정보
     *
     * @return 쿼리 실행결과
     */
    function insertPrint($conn, $param) {
        if (!$this->connectionCheck($conn)) return false;
        
        $query  = "\n INSERT INTO print ( extnl_brand_seqno";
        $query .= "\n                    ,top";
        $query .= "\n                    ,name";
        $query .= "\n                    ,crtr_tmpt";
        $query .= "\n                    ,crtr_unit";
        $query .= "\n                    ,affil";
        $query .= "\n                    ,wid_wize";
        $query .= "\n                    ,vert_size";
        $query .= "\n                    ,search_check";
        $query .= "\n                    ,regi_date) VALUES ";
        
        $values_base  = "\n (";
        $values_base .= "\n    %s, %s, %s, %s, %s,";
        $values_base .= "\n    %s, %s, %s, %s, now()";
        $values_base .= "\n )";

        $search_check = sprintf("%s|%s|%s|%s", $param["top"]
                                             , $param["name"]
                                             , $param["wid_size"]
                                             , $param["vert_size"]);

        $param["search_check"] = $search_check;
        $param = $this->parameterArrayEscape($conn, $param);

        $query .= sprintf($values_base, $param["extnl_brand_seqno"]
                                      , $param["top"]
                                      , $param["name"]
                                      , $param["crtr_tmpt"]
                                      , $param["crtr_unit"]
                                      , $param["affil"]
                                      , $param["wid_size"]
                                      , $param["vert_size"]
                                      , $param["search_check"]);

        //$conn->StartTrans();
        
        $ret = $conn->Execute($query);

        //$conn->CompleteTrans();

        if ($ret) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @brief 인쇄 매입가격을 입력
     *
     * @param $conn      = 디비 커넥션 
     * @param $param_arr = 정보 파라미터 배열
     *
     * @return 쿼리 실행결과
     */
    function insertPrintPurPrice($conn, $param_arr) {
        if (!$this->connectionCheck($conn)) return false;
        
        $query  = "\n INSERT INTO print_price ( basic_price";
        $query .= "\n                          ,pur_rate";
        $query .= "\n                          ,pur_aplc_price";
        $query .= "\n                          ,pur_price";
        $query .= "\n                          ,amt";
        $query .= "\n                          ,print_seqno) VALUES ";
        
        $param_arr_count = count($param_arr);

        $values_base  = "\n (";
        $values_base .= "\n    %s, %s, %s, %s, %s, %s";
        $values_base .= "\n )";

        for ($i = 0; $i < $param_arr_count; $i++) {
            $param = $param_arr[$i];

            $param = $this->parameterArrayEscape($conn, $param);

            $query .= sprintf($values_base, $param["basic_price"]
                                          , $param["pur_rate"]
                                          , $param["pur_aplc_price"]
                                          , $param["pur_price"]
                                          , $param["amt"]
                                          , $param["seqno"]);

            if ($i + 1 < $param_arr_count) {
                $query .= ", ";
            }
        }

        $conn->StartTrans();
        
        $ret = $conn->Execute($query);

        $conn->CompleteTrans();

        if ($ret) {
            return "SUCCESS";
        } else {
            return "FAIL";
        }
    }

    /**
     * @brief 엑셀에 입력된 정보를 기초로<br/>
     * 해당 항목의 mpcode를 반환하는 함수
     *
     * @param $conn  = 디비 커넥션
     * @param $param = 조건절 정보 파라미터
     *
     * @return 쿼리 실행결과
     */
    function selectPrdtPrintMpcode($conn, $param) {
        if (!$this->connectionCheck($conn)) return false;

        $temp = array();
        $temp["col"] = "mpcode";

        $temp["table"] = "prdt_print_info";

        $temp["where"]["cate_sortcode"] = $param["cate_sortcode"];
        $temp["where"]["print_name"]    = $param["name"];
        $temp["where"]["purp_dvs"]      = $param["purp_dvs"];
        $temp["where"]["affil"]         = $param["affil"];
        $temp["where"]["crtr_unit"]     = $param["crtr_unit"];

        return $this->selectData($conn, $temp);
    }

    /**
     * @brief 인쇄 판매가격을 입력
     *
     * @param $conn      = 디비 커넥션 
     * @param $param_arr = 정보 파라미터 배열
     *
     * @return 쿼리 실행결과
     */
    function insertPrintSellPrice($conn, $param_arr) {
        if (!$this->connectionCheck($conn)) return false;
        
        $query  = "\n INSERT INTO prdt_print_price ( basic_price";
        $query .= "\n                               ,sell_rate";
        $query .= "\n                               ,sell_aplc_price";
        $query .= "\n                               ,sell_price";
        $query .= "\n                               ,amt";
        $query .= "\n                               ,prdt_print_info_mpcode) VALUES ";
        
        $param_arr_count = count($param_arr);

        $values_base = "\n (%s, %s, %s, %s, %s, %s)";

        for ($i = 0; $i < $param_arr_count; $i++) {
            $param = $param_arr[$i];

            $param = $this->parameterArrayEscape($conn, $param);

            $query .= sprintf($values_base, $param["basic_price"]
                                          , $param["sell_rate"]
                                          , $param["sell_aplc_price"]
                                          , $param["sell_price"]
                                          , $param["amt"]
                                          , $param["mpcode"]);

            if ($i + 1 < $param_arr_count) {
                $query .= ", ";
            }
        }

        $conn->StartTrans();
        
        $ret = $conn->Execute($query);

        $conn->CompleteTrans();

        if ($ret) {
            return "SUCCESS";
        } else {
            return "FAIL";
        }
    }
}
?>
