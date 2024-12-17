<?
include_once(dirname(__FILE__) . '/ExcelUtil.php');

class PrintPriceExcelUtil extends ExcelUtil {
    function __construct() {
    }

    /**
     * @brief 금액정보 배열 중 INFO를 seqno로 변경하고<br/>
     * 금액 테이블에서 변경할 seqno를 가지고 있는 row를 삭제한 뒤<br/>
     * 결과배열을 금액 테이블에 입력한다<br/>
     *
     * @details [INFO] => 수량|-|제조사|브랜드|대분류|인쇄명|계열|사이즈|기준도수|기준수량<br/>
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
                $manu      = $print_info_arr[2];
                $brand     = $print_info_arr[3];
                $top       = $print_info_arr[4];
                $name      = $print_info_arr[5];
                $affil     = $print_info_arr[6];
                $size      = $print_info_arr[7];
                $crtr_tmpt = $print_info_arr[8];
                $crtr_unit = $print_info_arr[9];

                // 브랜드 일련번호 검색
                $param["manu_name"] = $manu;
                $param["pur_prdt"]  = "인쇄";
                $extnl_etprs_seqno = $priceDAO->selectExtnlEtprsSeqno($conn,
                                                                      $param);
                unset($param);

                $param["extnl_etprs_seqno"] = $extnl_etprs_seqno;
                $param["brand"] = $brand;
                $extnl_brand_seqno = $priceDAO->selectExtnlBrandSeqno($conn,
                                                                      $param);
                // 사이즈 정보 추출
                $size_arr  = explode('*', $size);
                $size_width = $size_arr[0];
                $size_vert  = $size_arr[1];

                $param["extnl_brand_seqno"] = $extnl_brand_seqno;
                $param["top"]               = $top;
                $param["name"]              = $name;
                $param["crtr_tmpt"]         = $crtr_tmpt;
                $param["crtr_unit"]         = $crtr_unit;
                $param["affil"]             = $affil;
                $param["wid_size"]          = $size_width;
                $param["vert_size"]         = $size_vert;

                // 엑셀파일에 존재하는 정보로 seqno 검색
                $rs = $priceDAO->selectPrintSeqno($conn, $param);
                $seqno = $rs->fields["seqno"];

                if ($rs->EOF) {
                    // 정보 없으면 print 테이블에 정보 입력
                    $ret = $priceDAO->insertPrint($conn, $param);
                    if ($ret === false) {
                        return "FAIL";
                    }

                    $seqno = $conn->Insert_ID();
                }

                if ($dup_check[$print_info] !== null) {
                    // 이미 입력한 정보가 들어왔을 경우
                    continue;
                }

                $dup_check[$print_info] = true;

                $price_info_arr = explode('|', $price_info);

                $basic_price     = doubleval($price_info_arr[0]) * 1.1;
                $basic_price     = $this->ceilVal($basic_price);
                $pur_rate        = doubleval($price_info_arr[1]);
                $pur_aplc_price  = doubleval($price_info_arr[2]) * 1.1;
                $pur_price       = ($pur_rate / 100.0 * $basic_price) +
                                   $basic_price + $pur_aplc_price;
                $search_check    = sprintf("%s|%s*%s", $name
                                                     , $size_width
                                                     , $size_vert);

                $ret_arr[$k++] = array( "seqno"          => $seqno
                                       ,"amt"            => $amt
                                       ,"basic_price"    => $basic_price
                                       ,"pur_rate"       => $pur_rate
                                       ,"pur_aplc_price" => $pur_aplc_price
                                       ,"pur_price"      => $pur_price);
            }

            print_r($ret_arr);

            if (count($ret_arr) === 0) continue;

            // 기본 생산 테이블 삭제
            $delete_ret = $priceDAO->deletePrice($conn,
                                                 "basic_produce_print",
                                                 "print_seqno",
                                                 $ret_arr,
                                                 "seqno");
            if ($delete_ret === false) {
                return "FAIL";
            }

            // 본 테이블 삭제
            $delete_ret = $priceDAO->deletePrice($conn,
                                                 "print_price",
                                                 "print_seqno",
                                                 $ret_arr,
                                                 "seqno");
            if ($delete_ret === false) {
                return "FAIL";
            }

            /***************************
             에러 났을 경우 어떻게 해야될지 협의필요
             */
            $ret .= $priceDAO->insertPrintPurPrice($conn, $ret_arr);
            $ret .= '!';
        }

        return $ret;        
    }

    /**
     * @brief 금액정보 배열 중 INFO를 mpcode로 변경하고<br/>
     * 금액 테이블에서 변경할 mpcode를 가지고 있는 row를 전부 삭제한 뒤<br/>
     * 결과배열을 금액 테이블에 입력한다<br/>
     *
     * @details [INFO] => 수량|[[맵핑코드][-]]|카테고리|인쇄명|용도구분|계열|기준단위<br/>
     * [PRICE] => 기준가격|요율|적용금액|판매가격
     *
     * @param $conn     = 디비 커넥션
     * @param $priceDAO = 쿼리를 수행할 dao 객체
     *
     * @return 쿼리실행 성공여부
     */
    function insertSellPriceInfo($conn, $priceDAO) {

        $ret = "";

        $dup_chk_info = "%s/%s";

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
                $print_price_info = $info_arr[$j];

                $print_info = $print_price_info["INFO"];
                $price_info = $print_price_info["PRICE"];

                $print_info_arr = explode("|", $print_info);

                $amt    = $print_info_arr[0];
                $mpcode = $print_info_arr[1];

                // 엑셀에 맵핑코드가 없을 경우
                // 엑셀파일에 존재하는 정보로 맵핑코드 검색
                if ($mpcode === "-") {
                    $cate_name = $print_info_arr[2];
                    $name      = $print_info_arr[3];
                    $purp_dvs  = $print_info_arr[4];
                    $affil     = $print_info_arr[5];
                    $crtr_unit = $print_info_arr[6];

                    $cate_sortcode = $priceDAO->selectCateSortcode($conn,
                                                                   $cate_name,
                                                                   2);
                    $param = array();
                    $param["cate_sortcode"] = $cate_sortcode;
                    $param["name"]          = $name;
                    $param["purp_dvs"]      = $purp_dvs;
                    $param["affil"]         = $affil;
                    $param["crtr_unit"]     = $crtr_unit;

                    $rs = $priceDAO->selectPrdtPrintMpcode($conn, $param);

                    if ($rs->EOF) {
                        continue;
                    }

                    $mpcode = $rs->fields["mpcode"];
                }

                $seqno = $rs->fields["seqno"];

                $dup_info = sprintf($dup_chk_info, $mpcode
                                                 , $amt);

                if ($dup_check[$dup_info] !== null) {
                    // 이미 입력한 정보가 들어왔을 경우
                    continue;
                }

                $dup_check[$dup_info] = true;

                $price_info_arr = explode('|', $price_info);

                $basic_price      = doubleval($price_info_arr[0]) * 1.1;
                $basic_price      = $this->ceilVal($basic_price);
                $sell_rate        = doubleval($price_info_arr[1]);
                $sell_aplc_price  = doubleval($price_info_arr[2]) * 1.1;
                $sell_price       = ($sell_rate / 100.0 * $basic_price) +
                                    $basic_price + $sell_aplc_price;

                $ret_arr[$k++] = array( "mpcode"          => $mpcode
                                       ,"amt"             => $amt
                                       ,"basic_price"     => $basic_price
                                       ,"sell_rate"       => $sell_rate
                                       ,"sell_aplc_price" => $sell_aplc_price
                                       ,"sell_price"      => $sell_price);
            }

            if (count($ret_arr) === 0) continue;

            $delete_ret = $priceDAO->deletePrice($conn,
                                                 "prdt_print_price",
                                                 "prdt_print_info_mpcode",
                                                 $ret_arr);

            if (!$delete_ret) return "FAIL";

            /***************************
             에러 났을 경우 어떻게 해야될지 협의필요
             */
            $ret .= $priceDAO->insertPrintSellPrice($conn, $ret_arr);
            $ret .= '!';
        }

        return $ret;        
    }
}
?>
