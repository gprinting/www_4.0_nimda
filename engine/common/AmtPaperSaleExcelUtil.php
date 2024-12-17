<?
include_once(dirname(__FILE__) . '/ExcelUtil.php');

class AmtPaperSaleExcelUtil extends ExcelUtil {
    function __construct() {
    }

    /**
     * @brief 종이 금액정보 배열 중 INFO를 mpcode로 변경하고<br/>
     * 금액 테이블에서 변경할 prdt_paper_seqno를 가지고 있는 row를 삭제한 뒤<br/>
     * 결과배열을 상품_종이_금액 테이블에 입력한다<br/>
     *
     * @details [INFO]  => 수량|-|판매채널|카테고리|종이정보|계열|사이즈|페이지정보|기준단위<br/>
     * [PRICE] => 종이판매가격|요율|적용금액|할인가격
     *
     * @param $conn     = 디비 커넥션
     * @param $priceDAO = 쿼리를 수행할 dao 객체
     *
     * @return 쿼리실행 성공여부
     */
    function insertAmtPaperSaleInfo($conn, $priceDAO) {
        $ret = "";
        for ($i = 0; $i < $this->sheet_count; $i++) {
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
                $sale_info = $info_arr[$j];

                $price_info = $sale_info["PRICE"];
                $sale_info  = $sale_info["INFO"];

                $sale_info_arr = explode("|", $sale_info);

                $mpcode = null;

                //0.5|-|㈜굿프린팅|일반지 독판전단|아트지!-!백색!250g|국|A4|표지!2p|R
                $amt            = $sale_info_arr[0];
                $sell_site      = $sale_info_arr[2];
                $cate_name      = $sale_info_arr[3];
                $paper_info_arr = explode("!", $sale_info_arr[4]);
                $affil          = $sale_info_arr[5];
                $stan_info_arr  = explode("!", $sale_info_arr[6]);
                $page_info_arr  = explode("!", $sale_info_arr[7]);
                $amt_unit       = $sale_info_arr[8];

                // 판매채널 검색
                $cpn_admin_seqno = $priceDAO->selectCpnAdminSeqno($conn,
                                                                  $sell_site);
                // 판매채널이 존재하지 않을경우
                if (empty($cpn_admin_seqno)) {
                    continue;
                }

                // 카테고리 분류코드 검색
                $cate_sortcode = $priceDAO->selectCateSortcode($conn,
                                                               $cate_name);
                // 카테고리 종이 맵핑코드 검색
                $param["cate_sortcode"] = $cate_sortcode;
                $param["name"]          = $paper_info_arr[0];
                $param["dvs"]           = $paper_info_arr[1];
                $param["color"]         = $paper_info_arr[2];
                $param["basisweight"]   = $paper_info_arr[3];

                $cate_paper_mpcode = $priceDAO->selectCatePaperMpcode($conn,
                                                                      $param);
                if (empty($cate_paper_mpcode)) {
                    continue;
                }

                // 카테고리 규격 맵핑코드 검색
                $param["name"]  = $stan_info_arr[0];
                $param["typ"]   = $stan_info_arr[1];
                $param["affil"] = $affil;

                $cate_stan_mpcode = $priceDAO->selectCateStanMpcode($conn,
                                                                    $param);
                if (empty($cate_stan_mpcode)) {
                    continue;
                }

                $price_info_arr = explode('|', $price_info);

                $rate       = doubleval($price_info_arr[1]);
                //$aplc_price = doubleval($price_info_arr[2]) ;
                $aplc_price = doubleval($price_info_arr[4]) ;
                $singleside_price = doubleval($price_info_arr[3]);

                $ret_arr[$k++] = array(
                     "cate_sortcode"     => $cate_sortcode
                    ,"cate_paper_mpcode" => $cate_paper_mpcode
                    ,"cate_stan_mpcode"  => $cate_stan_mpcode
                    ,"amt"               => $this->ceilValT($amt)
                    ,"rate"              => $rate
                    ,"aplc_price"        => $aplc_price
                    ,"cpn_admin_seqno"   => $cpn_admin_seqno
                    ,"typ"               => $page_info_arr[0]
                    ,"page_amt"          => substr($page_info_arr[1], 0, -1)
                    ,"singleside_price"  => $singleside_price
                );
            }

            if (count($ret_arr) === 0) {
                continue;
            }

            // 본 테이블 삭제
            $delete_ret = $priceDAO->deleteAmtPaperSale($conn, $ret_arr);
            if ($delete_ret === false) {
                return "FAIL";
            }

            /***************************
             에러 났을 경우 어떻게 해야될지 협의필요
             */
            $ret .= $priceDAO->insertAmtPaperSale($conn, $ret_arr);
            $ret .= '!';
            $conn->debug = 0;
        }

        return $ret;
    }

    /**
     * @brief 소수점 4자리 이하 반올림
     *
     * @param $val = 계산할 값
     *
     * @return 계산된 값
     */
    function ceilValT($val) {
        $val = floatval($val);

        $val = round($val * 1000) / 1000;

        return $val;
    }
}
?>
