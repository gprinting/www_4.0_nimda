<?
include_once(dirname(__FILE__) . '/CommonDAO.php');

class PlyPriceRegiDAO extends CommonDAO {
    function __construct() {
    }
	
    /**
     * @brief 합판 가격 테이블에서 조건에 해당하는 가격 전부 삭제
     * 
     * @param $conn       = 디비 커넥션
     * @param $table_name = 가격 테이블명
     * @param $param      = 정보 파라미터
     * 
     * @return 작업실행 성공여부
     */
    function deletePlyPrice($conn, $table_name, $param_arr) {
        if (!$this->connectionCheck($conn)) return false;

        $query_base  = "\n DELETE FROM %s";
        $query_base .= "\n  WHERE cate_sortcode     = %s";
        $query_base .= "\n    AND cate_paper_mpcode = %s";
        $query_base .= "\n    AND cate_stan_mpcode  = %s";
        $query_base .= "\n    AND cate_beforeside_print_mpcode     = %s";
        $query_base .= "\n    AND cate_beforeside_add_print_mpcode = %s";
        $query_base .= "\n    AND cate_aftside_print_mpcode        = %s";
        $query_base .= "\n    AND cate_aftside_add_print_mpcode    = %s";

        $param_arr_count = count($param_arr);
        $dup_check = array();
        $info_arr = array();

        $loop_count = ceil(($param_arr_count / 500));

        $dup_chk_info = "%s/%s/%s/%s/%s/%s/%s";

        $j = 0;
        for ($i = 0; $i < $loop_count; $i++) {
            $k = 0;
            for ($j; $j < $param_arr_count; $j++) {
                if ($j !== 0 && $j % 500 === 0) {
                    $j++;
                    break;
                }

                $param = $this->parameterArrayEscape($conn, $param_arr[$j]);

                $cate_sortcode     = $param["cate_sortcode"];
                $cate_paper_mpcode = $param["cate_paper_mpcode"];
                $cate_stan_mpcode  = $param["cate_stan_mpcode"];
                $cate_beforeside_print_mpcode     = $param["cate_beforeside_print_mpcode"];
                $cate_beforeside_add_print_mpcode = $param["cate_beforeside_add_print_mpcode"];
                $cate_aftside_print_mpcode        = $param["cate_aftside_print_mpcode"];
                $cate_aftside_add_print_mpcode    = $param["cate_aftside_add_print_mpcode"];

                $dup_info = sprintf($dup_chk_info, $cate_sortcode
                                                 , $cate_paper_mpcode
                                                 , $cate_stan_mpcode
                                                 , $cate_beforeside_print_mpcode
                                                 , $cate_beforeside_add_print_mpcode
                                                 , $cate_aftside_print_mpcode
                                                 , $cate_aftside_add_print_mpcode);

                if (isset($dup_check[$dup_info]) && $dup_check[$dup_info] !== NULL) {
                    continue;
                }

                $dup_check[$dup_info] = true;
                $mpcode_arr[$i][$k++] = $dup_info;
            }
        }

        unset($param);
        unset($dup_check);

        for ($i = 0; $i < $loop_count; $i++) {
            $mpcode = $mpcode_arr[$i];
            $mpcode_count = count($mpcode);

            $conn->StartTrans();

            for ($j = 0; $j < $mpcode_count; $j++) {
                $temp = explode('/', $mpcode[$j]);

                $query = sprintf($query_base, $table_name
                                            , $temp[0]
                                            , $temp[1]
                                            , $temp[2]
                                            , $temp[3]
                                            , $temp[4]
                                            , $temp[5]
                                            , $temp[6]);
                $ret = $conn->Execute($query);

                if (!$ret) {
                    return false;
                }
            }
            $conn->CompleteTrans();
            sleep(1);
        }

        return true;
    }

    /**
     * @brief 합판 판매가격을 입력
     *
     * @param $conn      = 디비 커넥션 
     * @param $param_arr = 정보 파라미터 배열
     *
     * @return 쿼리 실행결과
     */
    function insertPlyPrice($conn, $table_name, $param_arr) {
        // global $base_path;
        if (!$this->connectionCheck($conn)) return false;

        // $fp = fopen($base_path . "/log/PlyPrice2_debug3.log", "w");
        
        $query_base  = "\n INSERT INTO %s ( amt";
        $query_base .= "\n                 ,basic_price";
        $query_base .= "\n                 ,rate";
        $query_base .= "\n                 ,aplc_price";
        $query_base .= "\n                 ,new_price";
        $query_base .= "\n                 ,cate_sortcode";
        $query_base .= "\n                 ,cate_paper_mpcode";
        $query_base .= "\n                 ,cate_beforeside_print_mpcode";
        $query_base .= "\n                 ,cate_beforeside_add_print_mpcode";
        $query_base .= "\n                 ,cate_aftside_print_mpcode";
        $query_base .= "\n                 ,cate_aftside_add_print_mpcode";
        $query_base .= "\n                 ,cate_stan_mpcode";
        $query_base .= "\n                 ,page";
        $query_base .= "\n                 ,page_dvs";
        $query_base .= "\n                 ,page_detail) VALUES ";
        $query_base  = sprintf($query_base, $table_name);
        
        $param_arr_count = count($param_arr);
        $loop_count = ceil(($param_arr_count / 500));

        $values_base  = "\n (%s, %s, %s, %s, %s, %s, %s, ";
        $values_base .= "%s, %s, %s, %s, %s, %s, %s, %s),";

        //echo "loop_count : $loop_count\n";

        $j = 0;
        //if(!isset($ret)) $ret = "";
        for ($i = 0; $i < $loop_count; $i++) {
            //echo "$i / $loop_count\n";

            $query  = $query_base;
            $values = '';

            while (true) {
                //echo "while : $j\r";

                //  if(!isset($param_arr[$j++]))
                //      break;
                
                $param = $param_arr[$j++];

                if (empty($param) === true) {
                    // fwrite($fp, PHP_EOL."데이터 읎다".PHP_EOL);
                    break;
                }

                $param = $this->parameterArrayEscape($conn, $param);

                $values .= sprintf($values_base, $param["amt"]
                                               , $param["basic_price"]
                                               , $param["rate"]
                                               , $param["aplc_price"]
                                               , $param["new_price"]
                                               , $param["cate_sortcode"]
                                               , $param["cate_paper_mpcode"]
                                               , $param["cate_beforeside_print_mpcode"]
                                               , $param["cate_beforeside_add_print_mpcode"]
                                               , $param["cate_aftside_print_mpcode"]
                                               , $param["cate_aftside_add_print_mpcode"]
                                               , $param["cate_stan_mpcode"]
                                               , $param["page"]
                                               , $param["page_dvs"]
                                               , $param["page_detail"]);

                if ($j !== 0 && $j % 500 === 0) {
                    // fwrite($fp, PHP_EOL."마무리당함".PHP_EOL);
                    break;
                }
            }

            $query .= substr($values, 0, -1);

            $conn->StartTrans();
            // fwrite($fp, PHP_EOL."실행한 쿼리문".$query.PHP_EOL);
            if(!isset($ret)) $ret = "";
            $ret .= $conn->Execute($query) === false ? "FAIL" : "SUCCESS";
            $ret .= '!';

            if ($ret === false) {
                $conn->FailTrans();
                $conn->RollbackTrans();
                return "FAIL";
            }

            $conn->CompleteTrans();
        }

        return $ret;
    }
}
?>
