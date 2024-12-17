<?
include_once(dirname(__FILE__) . '/ExcelUtil.php');

class PlyPriceExcelUtil extends ExcelUtil {
    function __construct() {
    }

    /**
     * @brief 합판 금액정보 배열 중 INFO를 카테고리 분류코드와 맵핑코드로 변경하고<br/>
     * 금액 테이블에서 변경할 row를 전부 삭제한 뒤<br/>
     * 결과배열을 합판_금액_판매채널 테이블에 입력한다<br/>
     *
     * @details [INFO]  => 수량|-|카테고리|종이정보|사이즈|페이지정보|전면도수|-|-|-|기준단위<br/>
     * [PRICE] => 기준금액|매입요율|매입적용금액|매입금액
     *
     * @param $conn       = 디비 커넥션
     * @param $table_name = 합판 가격테이블명
     * @param $priceDAO   = 쿼리를 수행할 dao 객체
     *
     * @return 쿼리실행 성공여부
     */
    function insertSellPriceInfo($conn, $table_name, $priceDAO) {
        global $base_path;
        $ret = "";
        $dup_chk_info = "%s/%s/%s/%s/%s/%s/%s/%s";

        $fp = fopen($base_path . "/log/PlyPrice2_debug2.log", "w");

        $sheet_count = $this->sheet_count;
        for ($i = 0; $i < $sheet_count ; $i++) {
            $sheet = $this->obj_PHPExcel->getSheet($i);
            $info_arr = $this->makePriceInfo($sheet);

            $info_arr_count = count($info_arr);
            $param = array();

            $ret_arr = array();

            $price_arr = array();

            // DB 셀렉트 줄이는 용도로 사용
            $dup_check = array();

            $k = 0;
            for ($j = 0; $j < $info_arr_count; $j++) {
                fwrite($fp, PHP_EOL.
                    "============={$i}번 시트============".
                    PHP_EOL.
                    "카운트 : ".print_r($info_arr_count,true)).
                    PHP_EOL;
                $paper_price_info = $info_arr[$j];

                $paper_info = $paper_price_info["INFO"];
                $price_info = $paper_price_info["PRICE"];

                $paper_info_arr = explode("|", $paper_info);

                $seqno = "";

                $amt          = $paper_info_arr[0];
                $cate         = $paper_info_arr[2];
                $paper_info   = $paper_info_arr[3];
                $size_info    = explode('!', $paper_info_arr[4]);
                $page_info    = explode('!', $paper_info_arr[5]);
                $bef_tmpt     = $paper_info_arr[6];
                $bef_add_tmpt = $paper_info_arr[7];
                $aft_tmpt     = $paper_info_arr[8];
                $aft_add_tmpt = $paper_info_arr[9];

                $page_dvs    = $page_info[0];
                $page        = intval($page_info[1]);
                $page_detail = $page_info[2];

                $page_detail = str_replace("-", '', $page_detail);
                $page_detail = str_replace("페이지_상세", '', $page_detail);

                $param["cate"]       = $cate;
                $param["paper_info"] = explode('!', $paper_info);
                $param["size"]       = $size_info[0];
                $param["size_typ"]   = $size_info[1];
                $param["tmpt"]       = array("bef"     => $bef_tmpt,
                                             "bef_add" => $bef_add_tmpt,
                                             "aft"     => $aft_tmpt,
                                             "aft_add" => $aft_add_tmpt);

                //echo "$paper_info :: $size\n";

                // 엑셀파일에 존재하는 정보로 맵핑코드 검색
                $mpcode_arr = $priceDAO->selectPlyMpcode($conn, $param);

                if ($mpcode_arr === false) {
                    // 정보에 해당하는 mpcode가 없는경우
                    exit("코드없음");
                    continue;
                }

                $dup_info = sprintf($dup_chk_info, $amt
                                                 , $mpcode_arr["CATE"]
                                                 , $mpcode_arr["PAPER"]
                                                 , $mpcode_arr["STAN"]
                                                 , $mpcode_arr["BEF_PRINT"]
                                                 , $mpcode_arr["BEF_ADD_PRINT"]
                                                 , $mpcode_arr["AFT_PRINT"]
                                                 , $mpcode_arr["AFT_ADD_PRINT"]);

                if (isset($dup_check[$dup_info]) && $dup_check[$dup_info] !== null) {
                    // 이미 입력한 정보가 들어왔을 경우
                    //continue;
                }

                $dup_check[$dup_info] = true;

                $price_info_arr = explode('|', $price_info);

                fwrite($fp, PHP_EOL."가격 : ".$price_info_arr[0].PHP_EOL);

                //$basic_price = doubleval($price_info_arr[0]) * 1.1;
                $basic_price = doubleval($price_info_arr[0]);
                $basic_price = $this->ceilVal($basic_price);
                $rate        = doubleval($price_info_arr[1]);
                //$aplc_price  = doubleval($price_info_arr[2]) * 1.1;
                $aplc_price  = doubleval($price_info_arr[2]);
                $new_price   = ($rate / 100.0 * $basic_price) +
                               $basic_price + $aplc_price;

                $ret_arr[$k] = array(
                        "amt"               => $amt,
                        "cate_sortcode"     => $mpcode_arr["CATE"],
                        "cate_paper_mpcode" => $mpcode_arr["PAPER"],
                        "cate_stan_mpcode"  => $mpcode_arr["STAN"],
                        "cate_beforeside_print_mpcode"     => $mpcode_arr["BEF_PRINT"],
                        "cate_beforeside_add_print_mpcode" => $mpcode_arr["BEF_ADD_PRINT"],
                        "cate_aftside_print_mpcode"        => $mpcode_arr["AFT_PRINT"],
                        "cate_aftside_add_print_mpcode"    => $mpcode_arr["AFT_ADD_PRINT"],
                        "page"              => $page,
                        "page_dvs"          => $page_dvs,
                        "page_detail"       => $page_detail,
                        "basic_price"       => $basic_price,
                        "rate"              => $rate,
                        "aplc_price"        => $aplc_price,
                        "new_price"         => $new_price
                );
                fwrite($fp, PHP_EOL."현재 인덱스 : ".$k.PHP_EOL);
                $k++;
            }

            if (count($ret_arr) === 0) {
                fwrite($fp, PHP_EOL."넘어간다아아아".PHP_EOL);
                continue;
            }

            //$conn->debug = 1;
            $delete_ret = $priceDAO->deletePlyPrice($conn,
                                                    $table_name,
                                                    $ret_arr);

            if(!$delete_ret){
                fwrite($fp, PHP_EOL."삭제실패다아악".PHP_EOL.print_r($ret_arr, true).PHP_EOL);
            }
            //$conn->debug = 0;

            //if (!$delete_ret) return "FAIL";

            /***************************
             에러 났을 경우 어떻게 해야될지 협의필요
             */
            //$conn->debug = 1;
            $ret .= $priceDAO->insertPlyPrice($conn, $table_name, $ret_arr);
            fwrite($fp, PHP_EOL."최종 데이터다악".PHP_EOL.print_r($ret_arr, true).PHP_EOL);
            $ret .= '!';
            //$conn->debug = 0;

        }

        fclose($fp);

        return $ret;
    }
}
?>
