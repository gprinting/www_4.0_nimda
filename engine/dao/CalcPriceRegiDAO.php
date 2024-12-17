<?
include_once(dirname(__FILE__) . '/CommonDAO.php');

class CalcPriceRegiDAO extends CommonDAO {
    function __construct() {
    }

    /**
     * @brief 가격 계산방식이 모두이거나 계산형인 카테고리 검색
     *
     * @param $conn          = connection identifer
     * @param $cate_sortcode = 카테고리 분류코드
     *
     * @return 검색결과
     */
    function selectCalcCate($conn, $cate_sortcode) {
        if ($this->connectionCheck($conn) === false) {
            return false;
        }

        $query  = "\n SELECT  sortcode";
        $query .= "\n        ,flattyp_yn";
        $query .= "\n        ,mono_dvs";
        $query .= "\n        ,tmpt_dvs";

        $query .= "\n   FROM  cate";

        $query .= "\n  WHERE  mono_dvs IN ('1', '3')";
        $query .= "\n    AND  cate_level = '3'";
        if ($this->checkBlank($cate_sortcode)) {
            $query .= "\n    AND  sortcode = ";
            $query .= $this->parameterEscape($conn, $cate_sortcode);
        }

        return $conn->Execute($query);
    }

    /**
     * @brief 카테고리 종이 목록 검색
     *
     * @param $conn          = connection identifer
     * @param $cate_sortocde = 카테고리 분류코드
     *
     * @return 검색결과
     */
    function selectCatePaper($conn, $cate_sortcode) {
        if ($this->connectionCheck($conn) === false) {
            return false;
        }

        $cate_sortcode = $this->parameterEscape($conn, $cate_sortcode);

        $query  = "\n SELECT  A.name";
        $query .= "\n        ,A.dvs";
        $query .= "\n        ,A.color";
        $query .= "\n        ,A.basisweight";
        $query .= "\n        ,A.basisweight_unit";
        $query .= "\n        ,A.crtr_unit";
        $query .= "\n        ,A.mpcode AS prdt_mpcode";
        $query .= "\n        ,B.mpcode AS cate_mpcode";
        $query .= "\n        ,A.affil";

        $query .= "\n   FROM  prdt_paper AS A";
        $query .= "\n        ,cate_paper AS B";

        $query .= "\n  WHERE  A.search_check  = ";
        $query .= "CONCAT(B.name, '|', B.dvs, '|', B.color, '|', B.basisweight)";
        $query .= "\n    AND  B.cate_sortcode = %s";

        $query  = sprintf($query, $cate_sortcode);

        return $conn->Execute($query);
    }

    /**
     * @brief 카테고리 인쇄도수 목록 검색
     *
     * @param $conn          = connection identifer
     * @param $cate_sortocde = 카테고리 분류코드
     *
     * @return 검색결과
     */
    function selectCateTmpt($conn, $cate_sortcode) {
        if ($this->connectionCheck($conn) === false) {
            return false;
        }

        $cate_sortcode_mid = substr($cate_sortcode, 0, 6);
        $cate_sortcode     = $this->parameterEscape($conn, $cate_sortcode);
        $cate_sortcode_mid = $this->parameterEscape($conn, $cate_sortcode_mid);

        $query  = "\n    SELECT  A.name";
        $query .= "\n           ,A.side_dvs";
        $query .= "\n           ,A.purp_dvs";
        $query .= "\n           ,A.beforeside_tmpt";
        $query .= "\n           ,A.aftside_tmpt";
        $query .= "\n           ,A.add_tmpt";
        $query .= "\n           ,A.tot_tmpt";
        $query .= "\n           ,A.output_board_amt";
        $query .= "\n           ,B.affil";
        $query .= "\n           ,B.crtr_unit";
        $query .= "\n           ,B.mpcode AS prdt_mpcode";
        $query .= "\n           ,C.mpcode AS cate_mpcode";

        $query .= "\n      FROM  prdt_print      AS A";
        $query .= "\n           ,prdt_print_info AS B";
        $query .= "\n           ,cate_print      AS C";

        $query .= "\n     WHERE  A.print_name       = B.print_name";
        $query .= "\n       AND  A.purp_dvs         = B.purp_dvs";
        $query .= "\n       AND  A.prdt_print_seqno = C.prdt_print_seqno";
        $query .= "\n       AND  B.cate_sortcode    = %s";
        $query .= "\n       AND  C.cate_sortcode    = %s";

        $query  = sprintf($query, $cate_sortcode_mid
                                , $cate_sortcode);

        return $conn->Execute($query);
    }

    /**
     * @brief 카테고리 사이즈 목록 검색
     *
     * @param $conn          = connection identifer
     * @param $cate_sortocde = 카테고리 분류코드
     *
     * @return 검색결과
     */
    function selectCateSize($conn, $cate_sortcode) {
        if ($this->connectionCheck($conn) === false) {
            return false;
        }

        $cate_sortcode = $this->parameterEscape($conn, $cate_sortcode);

        $query  = "\n SELECT  A.name";
        $query .= "\n        ,B.mpcode AS prdt_mpcode";
        $query .= "\n        ,C.mpcode AS cate_mpcode";

        $query .= "\n   FROM  prdt_stan          AS A";
        $query .= "\n        ,prdt_output_info   AS B";
        $query .= "\n        ,cate_stan          AS C";

        $query .= "\n  WHERE  A.prdt_stan_seqno = C.prdt_stan_seqno";
        $query .= "\n    AND  A.output_name = B.output_name";
        $query .= "\n    AND  A.output_board_dvs = B.output_board_dvs";
        $query .= "\n    AND  C.cate_sortcode = %s";

        $query  = sprintf($query, $cate_sortcode);

        return $conn->Execute($query);
    }

    /**
     * @brief 카테고리 수량정보 검색
     *
     * @detail $param["table_name"] = 가격 테이블명
     * @param["cate_sortcode"] = 카테고리 분류코드
     *
     * @param $conn  = connection identifier
     * @param $param = 정보 배열
     *
     * @return 검색결과
     */
    function selectCateAmt($conn, $param) {
        if ($this->connectionCheck($conn) === false) {
            return false; 
        }

        $temp = array();
        $temp["col"]    = "amt";
        $temp["table"]  = $param["table_name"];
        $temp["where"]["cate_sortcode"] = $param["cate_sortcode"];
        $temp["order"]  = "amt + 0";

        return $this->distinctData($conn, $temp);
    }

    /**
     * @brief 종이 가격 검색
     *
     * @detail $param["sell_site"] = 판매채널
     * @param["mpcode"] = 상품 종이 맵핑코드
     *
     * @param $conn  = connection identifier
     * @param $param = 검색정보 파라미터
     *
     * @return 종이 판매가격
     */
    function selectPaperPrice($conn, $param) {
        if ($this->connectionCheck($conn) === false) {
            return false; 
        }

        $temp = array();
        $temp["col"]   = "sell_price";
        $temp["table"] = "prdt_paper_price";
        $temp["where"]["cpn_admin_seqno"]   = $param["sell_site"];
        $temp["where"]["prdt_paper_mpcode"] = $param["mpcode"];

        $rs = $this->selectData($conn, $temp);

        return $rs->fields["sell_price"];
    }

    /**
     * @brief 인쇄 가격 검색
     *
     * @detail $param["sell_site"] = 판매채널
     * @param["mpcode"] = 상품 종이 맵핑코드
     *
     * @param $conn  = connection identifier
     * @param $param = 검색정보 파라미터
     *
     * @return 검색결과
     */
    function selectPrintPrice($conn, $param) {
        if ($this->connectionCheck($conn) === false) {
            return false; 
        }

        $except_arr = array("amt" => true);

        $param = $this->parameterArrayEscape($conn, $param, $except_arr);

        $query  = "\n    SELECT  sell_price";
        $query .= "\n      FROM  prdt_print_price";
        $query .= "\n     WHERE  cpn_admin_seqno = %s";
        $query .= "\n       AND  prdt_print_info_mpcode = %s";
        $query .= "\n       AND  %s <= (amt + 0)";
        $query .= "\n  ORDER BY  (amt + 0) ASC";
        $query .= "\n     LIMIT  1";

        $query  = sprintf($query, $param["sell_site"]
                                , $param["mpcode"]
                                , $param["amt"]);

        $rs = $conn->Execute($query);

        // 해당하는 수량이 없을경우 제일 마지막 수량 판매가격 반환
        if ($rs->EOF) {
            $query  = "\n    SELECT  sell_price";
            $query .= "\n      FROM  prdt_print_price";
            $query .= "\n     WHERE  cpn_admin_seqno = %s";
            $query .= "\n       AND  prdt_print_info_mpcode = %s";
            $query .= "\n  ORDER BY  (amt + 0) DESC";
            $query .= "\n     LIMIT  1";

            $query  = sprintf($query, $param["sell_site"]
                                    , $param["mpcode"]);

            $rs = $conn->Execute($query);
        }

        return $rs->fields["sell_price"];
    }

    /**
     * @brief 출력 가격 검색
     *
     * @detail $param["sell_site"] = 판매채널
     * @param["mpcode"] = 상품 종이 맵핑코드
     *
     * @param $conn  = connection identifier
     * @param $param = 검색정보 파라미터
     *
     * @return 출력 판매가격
     */
    function selectOutputPrice($conn, $param) {
        if ($this->connectionCheck($conn) === false) {
            return false; 
        }

        $param = $this->parameterArrayEscape($conn, $param);

        $query  = "\n    SELECT  sell_price";
        $query .= "\n      FROM  prdt_stan_price";
        $query .= "\n     WHERE  cpn_admin_seqno = %s";
        $query .= "\n       AND  prdt_output_info_mpcode = %s";
        $query .= "\n       AND  board_amt = '1'";

        $query  = sprintf($query, $param["sell_site"]
                                , $param["mpcode"]);

        $rs = $conn->Execute($query);

        return $rs->fields["sell_price"];
    }

    /**
     * @brief 카테고리에 해당하는 계산형 가격 전부 삭제
     *
     * @param $conn          = connection identifer
     * @param $cate_sortcode = 정보 파라미터
     * @param $tb_name       = 가격테이블명
     *
     * @return 검색결과
     */
    function deleteCateCalcPrice($conn, $tb_name, $cate_sortcode) {
        if ($this->connectionCheck($conn) === false) {
            return false; 
        }

        $temp = array();
        $temp["table"]  = $tb_name;
        $temp["prk"]    = "cate_sortcode";
        $temp["prkVal"] = $cate_sortcode;

        $ret = $this->deleteData($conn, $temp);

        return $ret;
    }

    /**
     * @brief 계산형 가격 입력
     *
     * @param $conn          = connection identifer
     * @param $cate_sortcode = 정보 파라미터
     * @param $tb_name       = 가격테이블명
     * @param $ret           = 계산형 가격 정보 배열
     *
     * @return 검색결과
     */
    function insertCateCalcPrice($conn, $tb_name, $cate_sortcode, $ret) {
        if (empty($ret) === true) {
            return true;
        }

        $query  = "\n INSERT INTO %s (";
        $query .= "\n      cate_sortcode";
        $query .= "\n     ,cate_paper_mpcode";
        $query .= "\n     ,cate_beforeside_print_mpcode";
        $query .= "\n     ,cate_beforeside_add_print_mpcode";
        $query .= "\n     ,cate_aftside_print_mpcode";
        $query .= "\n     ,cate_aftside_add_print_mpcode";
        $query .= "\n     ,cate_stan_mpcode";
        $query .= "\n     ,amt";
        $query .= "\n     ,affil";
        $query .= "\n     ,page";
        $query .= "\n     ,page_dvs";
        $query .= "\n     ,page_detail";
        $query .= "\n     ,paper_price";
        $query .= "\n     ,print_price";
        $query .= "\n     ,output_price";
        $query .= "\n     ,sum_price";
        $query .= "\n ) VALUES";

        $query  = sprintf($query, $tb_name);

        $values_base  = "\n ('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s',";
        $values_base .= " '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')";

        foreach ($ret as $amt => $key_arr) {
            foreach ($key_arr as $key => $info_arr) {
                $temp_arr = explode('!', $key);

                $page_num                  = $temp_arr[0];
                $cate_paper_mpcode         = $temp_arr[1];
                $cate_bef_print_mpcode     = $temp_arr[2];
                $cate_bef_add_print_mpcode = $temp_arr[3];
                $cate_aft_print_mpcode     = $temp_arr[4];
                $cate_aft_add_print_mpcode = $temp_arr[5];
                $cate_output_mpcode        = $temp_arr[6];
                $page_dvs                  = $temp_arr[7];
                $page_detail               = $temp_arr[8];
                $affil                     = $temp_arr[9];

                $paper_price  = intval($info_arr["paper"]);
                $print_price  = intval($info_arr["print"]);
                $output_price = intval($info_arr["output"]);

                $calc_price = $paper_price + $print_price + $output_price;

                $query .= sprintf($values_base, $cate_sortcode
                                              , $cate_paper_mpcode
                                              , $cate_bef_print_mpcode
                                              , $cate_bef_add_print_mpcode
                                              , $cate_aft_print_mpcode
                                              , $cate_aft_add_print_mpcode
                                              , $cate_output_mpcode
                                              , $amt
                                              , $affil
                                              , $page_num
                                              , $page_dvs
                                              , $page_detail
                                              , $paper_price
                                              , $print_price
                                              , $output_price
                                              , $calc_price);
                $query .= ',';
            }
        }

        $query = substr($query, 0, -1);

        $conn->StartTrans();

        $rs = $conn->Execute($query);
        
        $conn->CompleteTrans();

        return $rs;
    }
}
?>
