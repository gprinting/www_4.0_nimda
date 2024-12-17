<?
include_once(dirname(__FILE__) . '/CommonDAO.php');

class AmtPaperSaleRegiDAO extends CommonDAO {
    function __construct() {
    }

    /**
     * @brief 카테고리 종이 맵핑코드 검색
     *
     * @param $conn  = connection identifier
     * @param $param = 조건용 파라미터
     *
     * @return 검색결과
     */
    function selectCatePaperMpcode($conn, $param) {
        if ($this->connectionCheck($conn) === false) {
            return false;
        }

        $param = $this->parameterArrayEscape($conn, $param);

        $query  = "\n SELECT  A.mpcode";
        $query .= "\n   FROM  cate_paper AS A";
        $query .= "\n  WHERE  A.cate_sortcode = %s";
        $query .= "\n    AND  A.name          = %s";
        $query .= "\n    AND  A.dvs           = %s";
        $query .= "\n    AND  A.color         = %s";
        $query .= "\n    AND  A.basisweight   = %s";

        $query  = sprintf($query, $param["cate_sortcode"]
                                , $param["name"]
                                , $param["dvs"]
                                , $param["color"]
                                , $param["basisweight"]);

        $rs = $conn->Execute($query);

        return $rs->fields["mpcode"];
    }

    /**
     * @brief 카테고리 규격 맵핑코드 검색
     *
     * @param $conn  = connection identifier
     * @param $param = 조건용 파라미터
     *
     * @return 검색결과
     */
    function selectCateStanMpcode($conn, $param) {
        if ($this->connectionCheck($conn) === false) {
            return false;
        }

        $param = $this->parameterArrayEscape($conn, $param);

        $query  = "\n SELECT  B.mpcode";
        $query .= "\n   FROM  prdt_stan AS A";
        $query .= "\n        ,cate_stan AS B";
        $query .= "\n  WHERE  A.prdt_stan_Seqno = B.prdt_stan_seqno";
        $query .= "\n    AND  B.cate_sortcode = %s";
        $query .= "\n    AND  A.name  = %s";
        $query .= "\n    AND  A.typ   = %s";
        $query .= "\n    AND  A.affil = %s";

        $query  = sprintf($query, $param["cate_sortcode"]
                                , $param["name"]
                                , $param["typ"]
                                , $param["affil"]);

        $rs = $conn->Execute($query);

        return $rs->fields["mpcode"];
    }

    /**
     * @brief 수량 종이 할인 데이터 삭제
     *
     * @param $conn  = connection identifier
     * @param $param = 조건용 파라미터
     *
     * @return 검색결과
     */
    function deleteAmtPaperSale($conn, $param) {
        if (!$this->connectionCheck($conn)) return false;

        $param_count = count($param);
        $dup_check = array();
        $prk_val_arr = array();

        $loop_count = ceil(($param_count / 500));

        $j = 0;
        for ($i = 0; $i < $loop_count; $i++) {
            $k = 0;
            for ($j; $j < $param_count; $j++) {
                $temp = $param[$j];
                $sell_site     = $temp["cpn_admin_seqno"];
                $cate_sortcode = $temp["cate_sortcode"];
                $paper_mpcode  = $temp["cate_paper_mpcode"];
                $stan_mpcode   = $temp["cate_stan_mpcode"];

                $dup_key = sprintf("%s|%s|%s|%s", $sell_site
                                                , $cate_sortcode
                                                , $paper_mpcode
                                                , $stan_mpcode);

                if ($dup_check[$dup_key] !== null) {
                    continue;
                }

                $dup_check[$dup_key] = true;
                $prk_val_arr[$i][$k++] = $temp;
            }
        }

        if (count($prk_val_arr) === 0) {
            return true;
        }

        $query  = "\n DELETE FROM amt_paper_sale";
        $query .= "\n WHERE ";
        $query .= "\n   cate_sortcode = %s";
        $query .= "\n   AND cate_paper_mpcode = %s";
        $query .= "\n   AND cate_stan_mpcode = %s";


        for ($i = 0; $i < $loop_count; $i++) {
            $prk_val = $prk_val_arr[$i];
            $prk_val_count = count($prk_val);

            $conn->StartTrans();
            for ($j = 0; $j < $prk_val_count; $j++) {
                $temp = $prk_val[$j];
                $temp = $this->parameterArrayEscape($conn, $temp);


                $q = sprintf($query, $temp["cate_sortcode"]
                                   , $temp["cate_paper_mpcode"]
                                   , $temp["cate_stan_mpcode"]);

                $ret = $conn->Execute($q);

                if ($ret === false) {
                    return false;
                }

            }
            $conn->CompleteTrans();
            sleep(1);
        }

        return $ret;
    }

    /**
     * @brief 수량 종이 할인 할인정보 입력
     *
     * @param $conn      = 디비 커넥션 
     * @param $param_arr = 정보 파라미터 배열
     *
     * @return 쿼리 실행결과
     */
    function insertAmtPaperSale($conn, $param_arr) {
        if (!$this->connectionCheck($conn)) return false;
        
        $query  = "\n INSERT INTO amt_paper_sale ( cate_sortcode";
        $query .= "\n                             ,cate_paper_mpcode";
        $query .= "\n                             ,cate_stan_mpcode";
        $query .= "\n                             ,amt";
        $query .= "\n                             ,rate";
        $query .= "\n                             ,aplc_price";
        //$query .= "\n                             ,cpn_admin_seqno";
        $query .= "\n                             ,typ";
        $query .= "\n                             ,page_amt ";
        $query .= "\n                             ,singleside_price) VALUES ";
        
        $param_arr_count = count($param_arr);

        $values_base = "\n (%s, %s, %s, %s, %s, %s, %s, %s, %s)";

        for ($i = 0; $i < $param_arr_count; $i++) {
            $param = $param_arr[$i];

            $param = $this->parameterArrayEscape($conn, $param);

            $query .= sprintf($values_base, $param["cate_sortcode"]
                                          , $param["cate_paper_mpcode"]
                                          , $param["cate_stan_mpcode"]
                                          , $param["amt"]
                                          , $param["rate"]
                                          , $param["aplc_price"]
                                          , $param["typ"]
                                          , $param["page_amt"]
                                          , $param["singleside_price"]);

            if ($i + 1 < $param_arr_count) {
                $query .= ", ";
            }
        }

        $conn->StartTrans();
        
        $ret = $conn->Execute($query);

        $conn->CompleteTrans();

        if ($ret) {
            return $query;
        } else {
            return "FAIL";
        }
    }
}
?>
