<?
include_once(dirname(__FILE__) . '/CommonDAO.php');

class PaperPriceRegiDAO extends CommonDAO {
    function __construct() {
    }

    /**
     * @brief 엑셀 데이터에 해당하는 종이 seqno 검색
     *
     * @param $conn  = 디비 커넥션
     * @param $param = 종이 정보
     *
     * @return 쿼리 실행결과
     */
    function selectPaperSeqno($conn, $param) {
        if (!$this->connectionCheck($conn)) return false;

        $temp = array();
        $temp["col"] = "paper_seqno AS seqno";

        $temp["table"] = "paper";

        $temp["where"]["extnl_brand_seqno"] = $param["extnl_brand_seqno"];
        $temp["where"]["search_check"]      = $param["search_check"];
        $temp["where"]["sort"]              = $param["sort"];
        $temp["where"]["affil"]             = $param["affil"];
        $temp["where"]["crtr_unit"]         = $param["crtr_unit"];

        return $this->selectData($conn, $temp);
    }

    /**
     * @brief 종이 매입가격을 입력
     *
     * @param $conn      = 디비 커넥션 
     * @param $param_arr = 정보 파라미터 배열
     *
     * @return 쿼리 실행결과
     */
    function insertPaperPrice($conn, $param_arr) {
        if ($this->connectionCheck($conn) === false) {
            return false;
        }
        
        $query  = "\n INSERT INTO paper ( basic_price";
        $query .= "\n                    ,pur_rate";
        $query .= "\n                    ,pur_aplc_price";
        $query .= "\n                    ,pur_price";
        $query .= "\n                    ,extnl_brand_seqno";
        $query .= "\n                    ,sort";
        $query .= "\n                    ,name";
        $query .= "\n                    ,dvs";
        $query .= "\n                    ,color";
        $query .= "\n                    ,basisweight";
        $query .= "\n                    ,basisweight_unit";
        $query .= "\n                    ,wid_size";
        $query .= "\n                    ,vert_size";
        $query .= "\n                    ,affil";
        $query .= "\n                    ,crtr_amt";
        $query .= "\n                    ,crtr_unit";
        $query .= "\n                    ,regi_date";
        $query .= "\n                    ,search_check) VALUES ";
        
        $param_arr_count = count($param_arr);

        $values_base  = "\n (";
        $values_base .= "\n    %s, %s, %s, %s, %s, %s, %s, %s, %s,";
        $values_base .= "\n    %s, %s, %s, %s, %s, '1', %s, now(), %s";
        $values_base .= "\n )";

        for ($i = 0; $i < $param_arr_count; $i++) {
            $param = $param_arr[$i];

            $param = $this->parameterArrayEscape($conn, $param);

            $query .= sprintf($values_base, $param["basic_price"]
                                          , $param["pur_rate"]
                                          , $param["pur_aplc_price"]
                                          , $param["pur_price"]
                                          , $param["extnl_brand_seqno"]
                                          , $param["sort"]
                                          , $param["name"]
                                          , $param["dvs"]
                                          , $param["color"]
                                          , $param["basisweight"]
                                          , $param["basisweight_unit"]
                                          , $param["wid_size"]
                                          , $param["vert_size"]
                                          , $param["affil"]
                                          , $param["crtr_unit"]
                                          , $param["search_check"]);

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
     * @brief 상품_종이 테이블에서 넘어온 정보를 기반으로
     * 가격 테이블에 입력할 맵핑코드를 검색하는 함수
     *
     * @param $conn  = 디비 커넥션
     * @param $param = 조건절 정보 파라미터
     *
     * @return 쿼리 실행결과
     */
    function selectPrdtPaperMpcode($conn, $param) {
        if (!$this->connectionCheck($conn)) return false;

        $temp = array();
        $temp["col"] = "mpcode";

        $temp["table"] = "prdt_paper";

        $temp["where"]["sort"]         = $param["sort"];
        $temp["where"]["search_check"] = $param["info"];
        $temp["where"]["affil"]        = $param["affil"];
        $temp["where"]["crtr_unit"]    = $param["crtr_unit"];

        return $this->selectData($conn, $temp);
    }

    /**
     * @brief 종이 판매가격을 입력
     *
     * @param $conn      = 디비 커넥션 
     * @param $param_arr = 정보 파라미터 배열
     *
     * @return 쿼리 실행결과
     */
    function insertPrdtPaperPrice($conn, $param_arr) {
        if (!$this->connectionCheck($conn)) return false;
        
        $query  = "\n INSERT INTO prdt_paper_price ( basic_price";
        $query .= "\n                               ,sell_rate";
        $query .= "\n                               ,sell_aplc_price";
        $query .= "\n                               ,sell_price";
        $query .= "\n                               ,prdt_paper_mpcode) VALUES ";
        
        $param_arr_count = count($param_arr);

        $values_base = "\n (%s, %s, %s, %s, %s)";

        for ($i = 0; $i < $param_arr_count; $i++) {
            $param = $param_arr[$i];

            $param = $this->parameterArrayEscape($conn, $param);

            $query .= sprintf($values_base, $param["basic_price"]
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
