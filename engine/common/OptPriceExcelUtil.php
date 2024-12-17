<?
include_once(dirname(__FILE__) . '/ExcelUtil.php');

class OptPriceExcelUtil extends ExcelUtil {
    function __construct() {
    }

    /**
     * @brief 금액정보 배열 중 INFO를 seqno로 변경하고<br/>
     * 금액 테이블에서 변경할 seqno를 가지고 있는 row를 삭제한 뒤<br/>
     * 결과배열을 금액 테이블에 입력한다<br/>
     *
     * @details [INFO]  => 수량|-|옵션명|Depth1|Depth2|Depth3|기준단위<br/>
     * [PRICE] => 기준가격|요율|적용금액|매입가격
     *
     * @param $conn     = 디비 커넥션
     * @param $priceDAO = 쿼리를 수행할 dao 객체
     *
     * @return 쿼리실행 성공여부
     */
    function insertPurPriceInfo($conn, $priceDAO) {

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
                $print_price_info = $info_arr[$j];

                $print_info = $print_price_info["INFO"];
                $price_info = $print_price_info["PRICE"];

                $print_info_arr = explode("|", $print_info);

                $amt       = $print_info_arr[0];
                $name      = $print_info_arr[2];
                $depth1    = $print_info_arr[3];
                $depth2    = $print_info_arr[4];
                $depth3    = $print_info_arr[5];
                $crtr_unit = $print_info_arr[6];

                $param["name"]      = $name;
                $param["depth1"]    = $depth1;
                $param["depth2"]    = $depth2;
                $param["depth3"]    = $depth3;
                $param["crtr_unit"] = $crtr_unit;
                $param["amt"]       = $amt;

                // 엑셀파일에 존재하는 정보로 seqno 검색
                $rs = $priceDAO->selectOptSeqno($conn, $param);

                $seqno = $rs->fields["seqno"];

                if ($rs->EOF) {
                    $seqno = false;
                }

                if ($seqno !== false && $dup_check[$seqno] !== null) {
                    // 이미 입력한 정보가 들어왔을 경우
                    continue;
                }

                $dup_check[$seqno] = true;

                $price_info_arr = explode('|', $price_info);

                $ret_arr[$k++] = array( "seqno"          => $seqno
                                       ,"amt"            => $amt
                                       ,"basic_price"    => $price_info_arr[0]
                                       ,"pur_rate"       => $price_info_arr[1]
                                       ,"pur_aplc_price" => $price_info_arr[2]
                                       ,"pur_price"      => $price_info_arr[3]
                                       ,"name"              => $name
                                       ,"depth1"            => $depth1
                                       ,"depth2"            => $depth2
                                       ,"depth3"            => $depth3
                                       ,"crtr_unit"         => $crtr_unit);
            }

            if (count($ret_arr) === 0) continue;

            // 기본 생산 테이블 삭제
            $delete_ret = $priceDAO->deletePrice($conn,
                                                 "default_produce_opt",
                                                 "opt_seqno",
                                                 $ret_arr,
                                                 "seqno");
            if ($delete_ret === false) {
                return "FAIL";
            }

            // 본 테이블 삭제
            $delete_ret = $priceDAO->deletePrice($conn,
                                                 "opt",
                                                 "opt_seqno",
                                                 $ret_arr,
                                                 "seqno");
            if ($delete_ret === false) {
                return "FAIL";
            }

            /***************************
             에러 났을 경우 어떻게 해야될지 협의필요
             */
            $ret .= $priceDAO->insertOptPurPrice($conn, $ret_arr);
            $ret .= '!';
        }

        return $ret;        
    }

    /**
     * @brief 옵션 금액정보 배열 중 INFO를 mpcode로 변경하고<br/>
     * 금액 테이블에서 mpcode를 가지고 있는 row를 전부 삭제한 뒤<br/>
     * 결과배열을 카테고리_옵션_가격 테이블에 입력한다<br/>
     *
     * @details [INFO]  => 수량|[[맵핑코드][-]]|판매채널|옵션명|Depth1|Depth2|Depth3<br/>
     * [PRICE] => 기준금액/매입요율/매입적용금액/매입금액
     *
     * @param $conn     = 디비 커넥션
     * @param $priceDAO = 쿼리를 수행할 dao 객체
     *
     * @return 쿼리실행 성공여부
     */
    function insertSellPriceInfo($conn, $priceDAO) {

        $ret = "";

        $dup_check_form = "%s/%s";

        for ($i = 0; $i < $this->sheet_count; $i++) {
            $sheet = $this->obj_PHPExcel->getSheet($i);
            $info_arr = $this->makePriceInfo($sheet);

            $info_arr_count = count($info_arr);

            $ret_arr = array();

            $price_arr = array();

            // DB 셀렉트 줄이는 용도로 사용
            $dup_check = array();

            $k = 0;
            for ($j = 0; $j < $info_arr_count; $j++) {
                $exist_seqno = true;

                $opt_price_info = $info_arr[$j];

                $opt_info = $opt_price_info["INFO"];
                $price_info = $opt_price_info["PRICE"];

                $paper_info_arr = explode("|", $opt_info);

                $seqno = "";

                $amt       = $paper_info_arr[0];
                $mpcode    = $paper_info_arr[1];
                $sell_site = $paper_info_arr[2];
                $cpn_admin_seqno = $priceDAO->selectCpnAdminSeqno($conn,
                                                                  $sell_site);

                // 판매채널이 존재하지 않을경우
                if ($cpn_admin_seqno === null) {
                    continue;
                }

                // 엑셀에 맵핑코드가 없을 경우
                // 엑셀파일에 존재하는 정보로 맵핑코드 검색
                if ($mpcode === "-") {
                    $cate_name = $paper_info_arr[3];
                    $name      = $paper_info_arr[4];
                    $depth1    = $paper_info_arr[5];
                    $depth2    = $paper_info_arr[6];
                    $depth3    = $paper_info_arr[7];

                    $cate_sortcode = $priceDAO->selectCateSortcode($conn,
                                                                   $cate_name);

                    $param = array();
                    $param["cate_sortcode"] = $cate_sortcode;
                    $param["name"]          = $name;
                    $param["depth1"]        = $depth1;
                    $param["depth2"]        = $depth2;
                    $param["depth3"]        = $depth3;

                    $rs = $priceDAO->selectCateOptMpcode($conn, $param);
                    if ($rs->EOF) {
                        // 정보에 해당하는 seqno가 없는경우
                        continue;
                    }

                    $mpcode = $rs->fields["mpcode"];
                }

                if ($dup_check[$dup_check_form] !== null) {
                    // 이미 입력한 정보가 들어왔을 경우
                    continue;
                }

                $dup_check_val = sprintf($dup_check_form, $amt, $mpcode);
                $dup_check[$dup_check_val] = true;

                $price_info_arr = explode('|', $price_info);

                $ret_arr[$k++] = array( "mpcode"          => $mpcode
                                       ,"cpn_admin_seqno" => $cpn_admin_seqno
                                       ,"amt"             => $amt
                                       ,"basic_price"     => $price_info_arr[0]
                                       ,"sell_rate"       => $price_info_arr[1]
                                       ,"sell_aplc_price" => $price_info_arr[2]
                                       ,"sell_price"      => $price_info_arr[3]);
            }

            if (count($ret_arr) === 0) {
                continue;
            }

            $delete_ret = $priceDAO->deletePrice($conn,
                                                 "cate_opt_price",
                                                 "cate_opt_mpcode",
                                                 $ret_arr);

            if (!$delete_ret) return "FAIL";

            /***************************
             에러 났을 경우 어떻게 해야될지 협의필요
             */
            $ret .= $priceDAO->insertCateOptPrice($conn, $ret_arr);
            $ret .= '!';
        }

        return $ret;        
    }
}
?>
