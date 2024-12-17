<?
include_once(dirname(__FILE__) . '/ExcelUtil.php');

class AmtMemberSaleExcelUtil extends ExcelUtil {
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
    function insertAmtMemberSaleInfo($conn, $priceDAO) {
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

                // 0  1   2                 3        4                     5                6        7          8 91011
                //200|-|회원명!사내닉네임|코팅명함|-!스노우지!-!백색!250g|86*52!투터치재단|표지!2p!|단면칼라4도|-|-|-|장
                $amt             = $sale_info_arr[0];
                $member_info_arr = explode("!", $sale_info_arr[2]);
                $cate_name       = $sale_info_arr[3];
                $paper_info_arr  = explode("!", $sale_info_arr[4]);
                $stan_info_arr   = explode("!", $sale_info_arr[5]);
                $page_info_arr   = explode("!", $sale_info_arr[6]);
                $bef_tmpt        = $sale_info_arr[7];
                $bef_add_tmpt    = $sale_info_arr[8];
                $aft_tmpt        = $sale_info_arr[9];
                $aft_add_tmpt    = $sale_info_arr[10];
                $amt_unit        = $sale_info_arr[11];

                $param["cate"]       = $cate_name;
                $param["paper_info"] = $paper_info_arr;
                $param["size"]       = $stan_info_arr[0];
                $param["size_typ"]   = $stan_info_arr[1];
                $param["tmpt"]       = array("bef"     => $bef_tmpt,
                                             "bef_add" => $bef_add_tmpt,
                                             "aft"     => $aft_tmpt,
                                             "aft_add" => $aft_add_tmpt);


                // 엑셀파일에 존재하는 정보로 맵핑코드 검색
                $mpcode_arr = $priceDAO->selectPlyMpcode($conn, $param);
                unset($param);

                // 회원정보 검색
                $param["member_name"] = $member_info_arr[0];
                $param["office_nick"] = $member_info_arr[1];
                $member_seqno = $priceDAO->selectMemberInfo($conn, $param);

                $price_info_arr = explode('|', $price_info);

                $rate       = doubleval($price_info_arr[1]);
                $aplc_price = doubleval($price_info_arr[2]) * 1.1;

                $ret_arr[$k++] = array(
                        "member_seqno"      => $member_seqno,
                        "amt"               => $amt,
                        "cate_sortcode"     => $mpcode_arr["CATE"],
                        "cate_paper_mpcode" => $mpcode_arr["PAPER"],
                        "cate_stan_mpcode"  => $mpcode_arr["STAN"],
                        "cate_beforeside_print_mpcode"     => $mpcode_arr["BEF_PRINT"],
                        "cate_beforeside_add_print_mpcode" => $mpcode_arr["BEF_ADD_PRINT"],
                        "cate_aftside_print_mpcode"        => $mpcode_arr["AFT_PRINT"],
                        "cate_aftside_add_print_mpcode"    => $mpcode_arr["AFT_ADD_PRINT"],
                        "rate"              => $rate,
                        "aplc_price"        => $aplc_price
                );
            }

            if (count($ret_arr) === 0) {
                continue;
            }

            // 본 테이블 삭제
            //$conn->debug = 1;
            $delete_ret = $priceDAO->deleteAmtMemberSale($conn, $ret_arr);
            if ($delete_ret === false) {
                return "FAIL";
            }

            /***************************
             에러 났을 경우 어떻게 해야될지 협의필요
             */
            $conn->debug = 1;
            $ret .= $priceDAO->insertAmtMemberSale($conn, $ret_arr);
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
