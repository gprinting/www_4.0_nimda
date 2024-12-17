<?
include_once(dirname(__FILE__) . '/CommonDAO.php');

class AfterPriceRegiDAO extends CommonDAO {
    function __construct() {
    }

    /**
     * @brief 후공정 테이블에서 엑셀정보를 바탕으로 일련번호 검색
     *
     * @param $conn  = 디비 커넥션
     * @param $param = 종이 정보
     *
     * @return 
     */
    function selectAfterSeqno($conn, $param) {
        if (!$this->connectionCheck($conn)) return false;

        $temp = array();
        $temp["col"] = "after_seqno AS seqno";

        $temp["table"] = "after";

        $temp["where"]["extnl_brand_seqno"] = $param["extnl_brand_seqno"];
        $temp["where"]["affil"]             = $param["affil"];
        $temp["where"]["name"]              = $param["name"];
        $temp["where"]["depth1"]            = $param["depth1"];
        $temp["where"]["depth2"]            = $param["depth2"];
        $temp["where"]["depth3"]            = $param["depth3"];
        $temp["where"]["affil"]             = $param["affil"];
        $temp["where"]["subpaper"]          = $param["subpaper"];
        $temp["where"]["crtr_unit"]         = $param["crtr_unit"];

        return $this->selectData($conn, $temp);
    }

    /**
     * @brief 후공정 정보 입력
	 *
     * @param $conn  = 디비 커넥션 
     * @param $param = 입력정보
     *
     * @return 쿼리 실행결과
     */
    function insertAfter($conn, $param) {
        if (!$this->connectionCheck($conn)) return false;
        
        $query  = "\n INSERT INTO after ( extnl_brand_seqno";
        $query .= "\n                    ,name";
        $query .= "\n                    ,depth1";
        $query .= "\n                    ,depth2";
        $query .= "\n                    ,depth3";
        $query .= "\n                    ,affil";
        $query .= "\n                    ,subpaper";
        $query .= "\n                    ,crtr_unit";
        $query .= "\n                    ,search_check";
        $query .= "\n                    ,regi_date) VALUES ";
        
        $values_base  = "\n (";
        $values_base .= "\n    %s, %s, %s, %s, %s,";
        $values_base .= "\n    %s, %s, %s, %s, now()";
        $values_base .= "\n )";

        $search_check = sprintf("%s|%s|%s|%s", $param["name"]
                                             , $param["depth1"]
                                             , $param["depth2"]
                                             , $param["depth3"]);

        $param["search_check"] = $search_check;
        $param = $this->parameterArrayEscape($conn, $param);

        $query .= sprintf($values_base, $param["extnl_brand_seqno"]
                                      , $param["name"]
                                      , $param["depth1"]
                                      , $param["depth2"]
                                      , $param["depth3"]
                                      , $param["affil"]
                                      , $param["subpaper"]
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
     * @brief 후공정 매입가격을 입력
	 *
     * @param $conn      = 디비 커넥션 
     * @param $param_arr = 정보 파라미터 배열
     *
     * @return 쿼리 실행결과
     */
    function insertAfterPurPrice($conn, $param_arr) {
        if (!$this->connectionCheck($conn)) return false;
        
        $query  = "\n INSERT INTO after_price ( basic_price";
        $query .= "\n                          ,pur_rate";
        $query .= "\n                          ,pur_aplc_price";
        $query .= "\n                          ,pur_price";
        $query .= "\n                          ,amt";
        $query .= "\n                          ,after_seqno) VALUES ";
        
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
     * @brief 엑셀에 맵핑코드가 없을경우 추출한 정보를 기반으로<br/>
     * 맵핑코드를 검색하는 함수
     *
     * @details 상품_후공정과 카테고리_후공정 테이블에서
     * 후공정명/Depth1/2/3/카테고리 분류코드 조건으로 검색 
     *
     * @param $conn  = 디비 커넥션
     * @param $param = 종이 정보
     *
     * @return 쿼리 실행결과
     */
    function selectCateAfterMpcode($conn, $param) {
        if (!$this->connectionCheck($conn)) return false;

        $param = $this->parameterArrayEscape($conn, $param);

        $query  = "\n SELECT  A.mpcode";
        $query .= "\n   FROM  cate_after AS A";
        $query .= "\n        ,prdt_after AS B";
        $query .= "\n  WHERE  A.prdt_after_seqno = B.prdt_after_seqno";
        $query .= "\n    AND  A.cate_sortcode = %s";
        $query .= "\n    AND  A.basic_yn   = %s";
        $query .= "\n    AND  B.after_name = %s";
        $query .= "\n    AND  B.depth1     = %s";
        $query .= "\n    AND  B.depth2     = %s";
        $query .= "\n    AND  B.depth3     = %s";
        $query .= "\n    AND  A.size       = %s";

        $query  = sprintf($query, $param["cate_sortcode"]
                                , $param["basic_yn"]
                                , $param["name"]
                                , $param["depth1"]
                                , $param["depth2"]
                                , $param["depth3"]
                                , $param["size"]);
        
        return $conn->Execute($query);
    }

    /**
     * @brief 후공정 판매가격을 입력
     *
     * @param $conn      = 디비 커넥션 
     * @param $param_arr = 정보 파라미터 배열
     *
     * @return 쿼리 실행결과
     */
    function insertCateAfterPrice($conn, $param_arr) {
        if (!$this->connectionCheck($conn)) return false;
        
        $query  = "\n INSERT INTO cate_after_price ( amt";
        $query .= "\n                               ,basic_price";
        $query .= "\n                               ,sell_rate";
        $query .= "\n                               ,sell_aplc_price";
        $query .= "\n                               ,sell_price";
        $query .= "\n                               ,cate_after_mpcode) VALUES ";
        
        $param_arr_count = count($param_arr);

        $values_base = "\n (%s, %s, %s, %s, %s, %s)";

        for ($i = 0; $i < $param_arr_count; $i++) {
            $param = $param_arr[$i];

            $param = $this->parameterArrayEscape($conn, $param);

            $query .= sprintf($values_base, $param["amt"]
                                          , $param["basic_price"]
                                          , $param["sell_rate"]
                                          , $param["sell_aplc_price"]
                                          , $param["sell_price"]
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
