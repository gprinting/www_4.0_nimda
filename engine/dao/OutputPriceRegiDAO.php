<?
include_once(dirname(__FILE__) . '/CommonDAO.php');

class OutputPriceRegiDAO extends CommonDAO {
    function __construct() {
    }

    /**
     * @brief 엑셀에 맵핑코드가 없을경우 추출한 정보를 기반으로<br/>
     * 맵핑코드를 검색하는 함수
     *
     * @details 출력명을 기반으로 종이_이름 테이블에서 seqno을 검색하고<br/>
     * 해당 seqno를 기반으로 구분/색상/평량 테이블의 seqno를 검색한 후<br/>
     * 종이 테이블에서 mpcode를 검색한다<br/>
     * 제조사/브랜드 정보가 있는경우 해당 seqno도 검색한다
     *
     * @param $conn  = 디비 커넥션
     * @param $param = 종이 정보
     *
     * @return 
     */
    function selectOutputSeqno($conn, $param) {
        if (!$this->connectionCheck($conn)) return false;

        $temp = array();
        $temp["col"] = "output_seqno AS seqno";

        $temp["table"] = "output";

        $temp["where"]["extnl_brand_seqno"] = $param["extnl_brand_seqno"];
        $temp["where"]["top"]               = $param["top"];
        $temp["where"]["name"]              = $param["name"];
        $temp["where"]["board"]             = $param["board"];
        $temp["where"]["affil"]             = $param["affil"];
        $temp["where"]["wid_size"]          = $param["wid_size"];
        $temp["where"]["vert_size"]         = $param["vert_size"];
        $temp["where"]["crtr_unit"]         = $param["crtr_unit"];

        return $this->selectData($conn, $temp);
    }

    /**
     * @brief 출력 정보 입력
	 *
     * @param $conn  = 디비 커넥션 
     * @param $param = 입력정보
     *
     * @return 쿼리 실행결과
     */
    function insertOutput($conn, $param) {
        if (!$this->connectionCheck($conn)) return false;
        
        $query  = "\n INSERT INTO output ( extnl_brand_seqno";
        $query .= "\n                     ,top";
        $query .= "\n                     ,name";
        $query .= "\n                     ,board";
        $query .= "\n                     ,affil";
        $query .= "\n                     ,wid_size";
        $query .= "\n                     ,vert_size";
        $query .= "\n                     ,crtr_unit";
        $query .= "\n                     ,search_check";
        $query .= "\n                     ,regi_date) VALUES ";
        
        $values_base  = "\n (";
        $values_base .= "\n    %s, %s, %s, %s, %s,";
        $values_base .= "\n    %s, %s, %s, %s, now()";
        $values_base .= "\n )";

        $search_check = sprintf("%s|%s|%s*%s", $param["name"]
                                             , $param["board"]
                                             , $param["wid_size"]
                                             , $param["vert_size"]);

        $param["search_check"] = $search_check;
        $param = $this->parameterArrayEscape($conn, $param);

        $query .= sprintf($values_base, $param["extnl_brand_seqno"]
                                      , $param["top"]
                                      , $param["name"]
                                      , $param["board"]
                                      , $param["affil"]
                                      , $param["wid_size"]
                                      , $param["vert_size"]
                                      , $param["crtr_unit"]
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
     * @brief 출력 매입가격을 입력
     *
     * @param $conn      = 디비 커넥션 
     * @param $param_arr = 정보 파라미터 배열
     *
     * @return 쿼리 실행결과
     */
    function insertOutputPrice($conn, $param_arr) {
        if (!$this->connectionCheck($conn)) return false;
        
        $query  = "\n INSERT INTO output_price ( basic_price";
        $query .= "\n                           ,pur_rate";
        $query .= "\n                           ,pur_aplc_price";
        $query .= "\n                           ,pur_price";
        $query .= "\n                           ,amt";
        $query .= "\n                           ,output_seqno) VALUES ";
        
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
                                          , $param["board_amt"]
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
     * 해당 출력항목의 mpcode를 반환하는 함수
     *
     * @param $conn  = 디비 커넥션
     * @param $param = 조건정보 파라미터
     *
     * @return 쿼리실행결과
     */
    function selectPrdtOutputMpcode($conn, $param) {
        if (!$this->connectionCheck($conn)) return false;

        $temp = array();
        $temp["col"] = "mpcode";

        $temp["table"] = "prdt_output_info";

        $temp["where"]["output_name"]      = $param["name"];
        $temp["where"]["output_board_dvs"] = $param["board"];
        //$temp["where"]["crtr_unit"]        = $param["crtr_unit"];

        return $this->selectData($conn, $temp);
    }

    /**
     * @brief 출력 판매가격을 입력
     *
     * @param $conn      = 디비 커넥션 
     * @param $param_arr = 정보 파라미터 배열
     *
     * @return 쿼리 실행결과
     */
    function insertPrdtOutputPrice($conn, $param_arr) {
        if (!$this->connectionCheck($conn)) return false;
        
        $query  = "\n INSERT INTO prdt_stan_price ( basic_price";
        $query .= "\n                              ,sell_rate";
        $query .= "\n                              ,sell_aplc_price";
        $query .= "\n                              ,sell_price";
        $query .= "\n                              ,board_amt";
        $query .= "\n                              ,prdt_output_info_mpcode) VALUES ";
        
        $param_arr_count = count($param_arr);

        $values_base = "\n (%s, %s, %s, %s, %s, %s)";

        for ($i = 0; $i < $param_arr_count; $i++) {
            $param = $param_arr[$i];

            $param = $this->parameterArrayEscape($conn, $param);

            $query .= sprintf($values_base, $param["basic_price"]
                                          , $param["sell_rate"]
                                          , $param["sell_aplc_price"]
                                          , $param["sell_price"]
                                          , $param["board_amt"]
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
