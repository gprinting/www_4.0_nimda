<?
include_once(dirname(__FILE__) . '/CommonDAO.php');

class OptPriceRegiDAO extends CommonDAO {
    function __construct() {
    }

    /**
     * @brief 옵션 테이블에서 엑셀정보를 바탕으로 일련번호 검색
     *
     * @param $conn  = 디비 커넥션
     * @param $param = 종이 정보
     *
     * @return 
     */
    function selectOptSeqno($conn, $param) {
        if (!$this->connectionCheck($conn)) return false;

        $temp = array();
        $temp["col"] = "opt_seqno AS seqno";

        $temp["table"] = "opt";

        $temp["where"]["name"]              = $param["name"];
        $temp["where"]["depth1"]            = $param["depth1"];
        $temp["where"]["depth2"]            = $param["depth2"];
        $temp["where"]["depth3"]            = $param["depth3"];
        $temp["where"]["crtr_unit"]         = $param["crtr_unit"];
        $temp["where"]["amt"]               = $param["amt"];

        return $this->selectData($conn, $temp);
    }

    /**
     * @brief 옵션 매입가격을 입력
     *
     * @param $conn      = 디비 커넥션 
     * @param $param_arr = 정보 파라미터 배열
     *
     * @return 쿼리 실행결과
     */
    function insertOptPurPrice($conn, $param_arr) {
        if (!$this->connectionCheck($conn)) return false;
        
        $query  = "\n INSERT INTO opt ( basic_price";
        $query .= "\n                  ,pur_rate";
        $query .= "\n                  ,pur_aplc_price";
        $query .= "\n                  ,pur_price";
        $query .= "\n                  ,amt";
        $query .= "\n                  ,name";
        $query .= "\n                  ,depth1";
        $query .= "\n                  ,depth2";
        $query .= "\n                  ,depth3";
        $query .= "\n                  ,crtr_unit";
        $query .= "\n                  ,regi_date) VALUES ";
        
        $param_arr_count = count($param_arr);

        $values_base  = "\n (";
        $values_base .= "\n    %s, %s, %s, %s, %s,";
        $values_base .= "\n    %s, %s, %s, %s, %s, now()";
        $values_base .= "\n )";

        for ($i = 0; $i < $param_arr_count; $i++) {
            $param = $param_arr[$i];

            $param = $this->parameterArrayEscape($conn, $param);

            $query .= sprintf($values_base, $param["basic_price"]
                                          , $param["pur_rate"]
                                          , $param["pur_aplc_price"]
                                          , $param["pur_price"]
                                          , $param["amt"]
                                          , $param["name"]
                                          , $param["depth1"]
                                          , $param["depth2"]
                                          , $param["depth3"]
                                          , $param["crtr_unit"]);

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
     * @brief 엑셀에 맵핑코드가 없을경우 추출한 정보를 기반으로<br/>
     * 맵핑코드를 검색하는 함수
     *
     * @details 상품_후공정과 카테고리_후공정 테이블에서
     * 후공정명/Depth1/2/3/카테고리 분류코드/기준단위 조건으로 검색 
     *
     * @param $conn  = 디비 커넥션
     * @param $param = 종이 정보
     *
     * @return 
     */
    function selectCateOptMpcode($conn, $param) {
        if (!$this->connectionCheck($conn)) return false;

        $param = $this->parameterArrayEscape($conn, $param);

        $query  = "\n SELECT  A.mpcode";
        $query .= "\n   FROM  cate_opt AS A";
        $query .= "\n         prdt_opt AS B";
        $query .= "\n  WHERE  A.prdt_opt_seqno = B.prdt_opt_seqno";
        $query .= "\n    AND  A.cate_sortcode = %s";
        $query .= "\n    AND  B.opt_name   = %s";
        $query .= "\n    AND  B.depth1     = %s";
        $query .= "\n    AND  B.depth2     = %s";
        $query .= "\n    AND  B.depth3     = %s";

        $query  = sprintf($query, $param["cate_sortcode"]
                                , $param["name"]
                                , $param["depth1"]
                                , $param["depth2"]
                                , $param["depth3"]);
        
        return $conn->Execute($query);
    }

    /**
     * @brief 종이 판매가격을 입력
     *
     * @param $conn      = 디비 커넥션 
     * @param $param_arr = 정보 파라미터 배열
     *
     * @return 쿼리 실행결과
     */
    function insertCateOptPrice($conn, $param_arr) {
        if (!$this->connectionCheck($conn)) return false;
        
        $query  = "\n INSERT INTO cate_opt_price ( amt";
        $query .= "\n                             ,basic_price";
        $query .= "\n                             ,sell_rate";
        $query .= "\n                             ,sell_aplc_price";
        $query .= "\n                             ,sell_price";
        $query .= "\n                             ,cate_opt_mpcode";
        $query .= "\n                             ,cpn_admin_seqno) VALUES ";
        
        $param_arr_count = count($param_arr);

        $values_base = "\n (%s, %s, %s, %s, %s, %s, %s)";

        for ($i = 0; $i < $param_arr_count; $i++) {
            $param = $param_arr[$i];

            $param = $this->parameterArrayEscape($conn, $param);

            $query .= sprintf($values_base, $param["amt"]
                                          , $param["basic_price"]
                                          , $param["sell_rate"]
                                          , $param["sell_aplc_price"]
                                          , $param["sell_price"]
                                          , $param["mpcode"]
                                          , $param["cpn_admin_seqno"]);

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
