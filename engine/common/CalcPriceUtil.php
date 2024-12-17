<?
// 1/2, 1/4, 1/8
define("HALF_QUARTER_EIGHTH", 875);
// 1/2, 1/4
define("HALF_QUARTER", 75);
// 1/2, 1/8
define("HALF_EIGHTH", 625);
// 1/2
define("HALF", 5);
// 1/4, 1/8
define("QUARTER_EIGHTH", 375);
// 1/4
define("QUARTER", 25);
// 1/8
define("EIGHTH", 125);

class CalcPriceUtil {
    /**
     * @brief 단면/양면도수 상품 가격 계산
     * 책자형/낱장형 둘 다 허용됨
     *
     * @detail $param["cate_paper_rs"] = 종이 정보 rs
     * $param["cate_tmpt_rs"] = 인쇄도수 정보 rs
     * $param["cate_size_rs"] = 사이즈 정보 rs
     * $param["amt_arr"] = 수량배열
     * $param["amt_unit"] = 수량단위
     * $param["pos_num_arr"] = 자리수
     * $param["page_number_arr"] = 페이지수
     * $param["extra_paper_amt_arr"] = 여분지수
     * $param["sell_site"] = 판매채널
     * $param["cate_sortcode"] = 카테고리 분류코드
     *
     * @detail $ret의 $key는
     * [페이지수!종이맵핑코드!인쇄맵핑코드!-!출력맵핑코드!페이지구분!페이지상세]
     * 이다
     * $ret[$수량][$key]["paper"] = 종이 가격
     * $ret[$수량][$key]["print"] = 인쇄 가격
     * $ret[$수량][$key]["output"] = 출력 가격
     *
     * @param $conn  = connection identifier
     * @param $dao   = 가격검색을 수행할 dao 객체
     * @param $param = 가격검색에 필요한 정보배열
     *
     * @return 입력 성공시 true, 실패시 false
     */
    function calcPriceSingleBoth($conn, $dao, &$param) {
        // engine/proc_engine/calc/CalcPriceEngine.php 에서 include함

        $sell_site     = $param["sell_site"];
        $cate_sortcode = $param["cate_sortcode"];
        $flattyp_yn    = $param["flattyp_yn"];
        $tb_name       = $param["tb_name"];

        $pos_num_arr = $param["pos_num_arr"];

        $paper_rs = $param["cate_paper_rs"];
        $tmpt_rs  = $param["cate_tmpt_rs"];
        $size_rs  = $param["cate_size_rs"];
        $extra_paper_amt_arr = $param["extra_paper_amt_arr"];

        $amt_arr  = $param["amt_arr"];
        $amt_unit = $param["amt_unit"];
        $amt_arr_count = count($amt_arr);

        $page_info_arr = $param["page_info_arr"];

        // 수량
        for ($i = 0; $i < $amt_arr_count; $i++) {
            $amt = doubleval($amt_arr[$i]);

            // 종이
            while ($paper_rs && !$paper_rs->EOF) {
                $paper_crtr_unit   = $paper_rs->fields["crtr_unit"];
                $paper_prdt_mpcode = $paper_rs->fields["prdt_mpcode"];
                $paper_cate_mpcode = $paper_rs->fields["cate_mpcode"];
                $paper_affil       = $paper_rs->fields["affil"];

                // 인쇄도수
                while ($tmpt_rs && !$tmpt_rs->EOF) {
                    // 인쇄도수에 따른 핀지 계산
                    $print_name        = $tmpt_rs->fields["name"];
                    $print_prdt_mpcode = $tmpt_rs->fields["prdt_mpcode"];
                    $print_cate_mpcode = $tmpt_rs->fields["cate_mpcode"];
                    $print_tot_tmpt    = $tmpt_rs->fields["tot_tmpt"];
                    $print_affil       = $tmpt_rs->fields["affil"];
                    $print_crtr_unit   = $tmpt_rs->fields["crtr_unit"];
                    $print_aft_tmpt    = $tmpt_rs->fields["aftside_tmpt"];
                    $print_bef_tmpt    = $tmpt_rs->fields["beforeside_tmpt"];
                    $output_board_amt  = $tmpt_rs->fields["output_board_amt"];

                    //echo "!! $paper_affil / $print_name : $print_affil\n";

                    // 종이계열과 인쇄계열이 맞지 않으면 넘어감
                    if ($print_affil !== $paper_affil) {
                        $tmpt_rs->MoveNext();
                        continue;
                    }

                    $extra_paper_amt = $extra_paper_amt_arr[$print_name];
                    $extra_paper_amt = doubleval($extra_paper_amt);

                    if ($paper_crtr_unit === 'R') {
                        $extra_paper_amt /= 500.0;
                    }

                    // 사이즈
                    while ($size_rs && !$size_rs->EOF) {
                        $ret = array();

                        // 사이즈에 다른 자리수 계산
                        $output_name        = $size_rs->fields["name"];
                        $output_prdt_mpcode = $size_rs->fields["prdt_mpcode"];
                        $output_cate_mpcode = $size_rs->fields["cate_mpcode"];

                        $pos_num = $pos_num_arr[$output_name];

                        if (empty($pos_num) === true || $pos_num === 0) {
                            return "FAIL!";
                        }

                        // 페이지
                        foreach ($page_info_arr as $page_dvs => $page_arr) {
                            $page_arr_count = count($page_arr);

                            for ($j = 0; $j < $page_arr_count; $j++) {
                                $page_num = $page_arr[$j];
                                $page_num = explode('!', $page_num);
                                $page_num = intval($page_num[0]);
                                $page_detail = $page_num[1];

                                // 실제 인쇄수량 계산
                                $temp = array();
                                $temp["amt"]       = $amt;
                                $temp["pos_num"]   = $pos_num;
                                $temp["page_num"]  = $page_num;
                                $temp["amt_unit"]  = $amt_unit;
                                $temp["crtr_unit"] = $paper_crtr_unit;

                                $real_paper_amt =
                                    $this->getPaperRealPrintAmt($temp);

                                // 여분지 포함 인쇄수량 계산
                                $calc_paper_amt =
                                    $extra_paper_amt + $real_paper_amt;
                                //$calc_paper_amt = ceil($calc_paper_amt);

                                // 종이 가격 계산
                                unset($temp);
                                $temp["sell_site"] = $sell_site;
                                $temp["mpcode"]    = $paper_prdt_mpcode;

                                $paper_price = $dao->selectPaperPrice($conn, $temp);

                                // 가격 없는 종이 건너뜀
                                if ($paper_price === null) {
                                    continue;
                                }

                                //echo "======== $amt / $paper_cate_mpcode / $print_name / $output_name\n";
                                //echo "[PAPER] $real_paper_amt / $calc_paper_amt / $paper_price \n";

                                $paper_price  = intval($paper_price);
                                $paper_price *= $calc_paper_amt;

                                // 인쇄 가격 계산
                                $print_price = 0;
                                if ($flattyp_yn === 'Y') {
                                    unset($temp);
                                    $temp["tot_tmpt"]       = intval($print_tot_tmpt);
                                    $temp["page_num"]       = $page_num;
                                    $temp["crtr_unit"]      = $print_crtr_unit;
                                    $temp["mpcode"]         = $print_prdt_mpcode;
                                    $temp["real_paper_amt"] = $real_paper_amt;
                                    $temp["sell_site"]      = $sell_site;

                                    $print_price = $this->calcSheetPrintPrice($conn,
                                                                              $dao,
                                                                              $temp);
                                } else {
                                    unset($temp);
                                    // 인쇄대수별 종이수량 재계산용
                                    $temp["amt"]             = $amt;
                                    $temp["amt_unit"]        = $amt_unit;
                                    $temp["paper_crtr_unit"] = $paper_crtr_unit;
                                    // 인쇄가격 계산용
                                    $temp["pos_num"]         = $pos_num;
                                    $temp["aft_tot_tmpt"]    = intval($print_aft_tmpt);
                                    $temp["bef_tot_tmpt"]    = intval($print_bef_tmpt);
                                    $temp["page_num"]        = $page_num;
                                    $temp["crtr_unit"]       = $print_crtr_unit;
                                    $temp["bef_mpcode"]      = $print_prdt_mpcode;
                                    $temp["aft_mpcode"]      = '0';
                                    $temp["sell_site"]       = $sell_site;

                                    $print_price = $this->calcBookletPrintPrice($conn,
																									$dao,
                                                                                                    $temp);
                                }

                                // 출력 가격 계산
                                unset($temp);
                                $temp["pos_num"]   = $pos_num;
                                $temp["page_num"]  = $page_num;
                                $temp["board_amt"] = $output_board_amt;
                                $temp["bef_tmpt"]  = $print_bef_tmpt;
                                $temp["aft_tmpt"]  = $print_aft_tmpt;
                                $temp["mpcode"]    = $output_prdt_mpcode;
                                $temp["sell_site"] = $sell_site;

                                if ($page_num > 2) {
                                    $output_price = $this->calcOutputPrice($conn,
                                                                           $dao,
                                                                           $temp);
                                } else {
                                    $output_price = $this->calcSheetOutputPrice($conn,
                                                                                $dao,
                                                                                $temp);
                                }

                                // 결과배열 생성
                                $key =
                                    sprintf("%s!%s!%s!0!0!0!%s!%s!%s!%s", $page_num
                                                                        , $paper_cate_mpcode
                                                                        , $print_cate_mpcode
                                                                        , $output_cate_mpcode
                                                                        , $page_dvs
                                                                        , $page_detail
                                                                        , $paper_affil);

                                $ret[$amt][$key]["paper"]  = $paper_price;
                                $ret[$amt][$key]["print"]  = $print_price;
                                $ret[$amt][$key]["output"] = $output_price;
                            }
                        }

                        // 가격입력
                        //$conn->debug = 1;
                        $dao_ret = $dao->insertCateCalcPrice($conn,
                                                             $tb_name,
                                                             $cate_sortcode,
                                                             $ret);
                        /*
                        */
                        //$conn->debug = 0;
                        if ($dao_ret === false) {
                            goto ERR;
                        }

                        usleep(600);

                        $size_rs->MoveNext();
                        reset($page_info_arr);
                    }

                    $tmpt_rs->MoveNext();
                    $size_rs->MoveFirst();
                }

                $paper_rs->MoveNext();
                $tmpt_rs->MoveFirst();
            }

            $paper_rs->MoveFirst();

        }

        return "SUCCESS!";

        ERR:
            return "FAIL!";
    }

    /**
     * @brief 전면/후면도수 상품 가격 계산
     * 낱장형만 허용됨
     *
     * @detail $param["cate_paper_rs"] = 종이 정보 rs
     * $param["print_purp_arr"] = 인쇄방식 배열
     * $param["cate_aft_tmpt_arr"] = 전면 인쇄도수 정보배열
     * $param["cate_aft_add_tmpt_arr"] = 전면 추가 인쇄도수 정보배열
     * $param["cate_bef_tmpt_arr"] = 후면 인쇄도수 정보배열
     * $param["cate_bef_add_tmpt_arr"] = 후면 추가 인쇄도수 정보배열
     * $param["cate_size_rs"] = 사이즈 정보 rs
     * $param["amt_arr"] = 수량배열
     * $param["amt_unit"] = 수량단위
     * $param["pos_num_arr"] = 자리수
     * $param["page_number_arr"] = 페이지수
     * $param["extra_paper_amt_arr"] = 여분지수
     * $param["sell_site"] = 판매채널
     *
     * @detail $ret의 $key는
     * [페이지수!종이맵핑코드!전면인쇄맵핑코드!후면인쇄맵핑코드!출력맵핑코드!페이지구분!페이지상세]
     * 이다
     * $ret[$수량][$key]["paper"] = 종이 가격
     * $ret[$수량][$key]["print"] = 인쇄 가격
     * $ret[$수량][$key]["output"] = 출력 가격
     *
     * @param $conn  = connection identifier
     * @param $dao   = 가격검색을 수행할 dao 객체
     * @param $param = 가격검색에 필요한 정보배열
     *
     * @return 입력 성공시 true, 실패시 false
     */
    function calcPriceAftBef($conn, $dao, &$param) {
        $sell_site     = $param["sell_site"];
        $cate_sortcode = $param["cate_sortcode"];
        $flattyp_yn    = $param["flattyp_yn"];
        $tb_name       = $param["tb_name"];

        $pos_num_arr = $param["pos_num_arr"];

        $paper_rs = $param["cate_paper_rs"];

        $print_purp_arr = $param["print_purp_arr"];

        $bef_tmpt_arr     = $param["cate_bef_tmpt_arr"];
        $bef_add_tmpt_arr = $param["cate_bef_add_tmpt_arr"];
        $aft_tmpt_arr     = $param["cate_aft_tmpt_arr"];
        $aft_add_tmpt_arr = $param["cate_aft_add_tmpt_arr"];

        $size_rs = $param["cate_size_rs"];

        $extra_paper_amt_arr = $param["extra_paper_amt_arr"];

        $amt_arr  = $param["amt_arr"];
        $amt_unit = $param["amt_unit"];
        $amt_arr_count = count($amt_arr);

        $page_info_arr = $param["page_info_arr"];

        // 수량
        for ($i = 0; $i < $amt_arr_count; $i++) {
            $amt = doubleval($amt_arr[$i]);

            // 종이
            while ($paper_rs && !$paper_rs->EOF) {
                $paper_crtr_unit   = $paper_rs->fields["crtr_unit"];
                $paper_prdt_mpcode = $paper_rs->fields["prdt_mpcode"];
                $paper_cate_mpcode = $paper_rs->fields["cate_mpcode"];
                $paper_affil       = $paper_rs->fields["affil"];

                // 페이지
                foreach ($page_info_arr as $page_dvs => $page_arr) {
                    $page_arr_count = count($page_arr);

                    // 인쇄용도
                    foreach ($print_purp_arr as $print_purp) {
                        $bef_tmpt_page_arr     = $bef_tmpt_arr[$print_purp];
                        $bef_add_tmpt_page_arr = $bef_add_tmpt_arr[$print_purp];
                        $aft_tmpt_page_arr     = $aft_tmpt_arr[$print_purp];
                        $aft_add_tmpt_page_arr = $aft_add_tmpt_arr[$print_purp];

                        $bef_tmpt_page_arr_count     = count($bef_tmpt_page_arr);
                        $bef_add_tmpt_page_arr_count = count($bef_add_tmpt_page_arr);
                        $aft_tmpt_page_arr_count     = count($aft_tmpt_page_arr);
                        $aft_add_tmpt_page_arr_count = count($aft_add_tmpt_page_arr);

                        // 전면 인쇄도수
                        for ($j = 0; $j < $bef_tmpt_page_arr_count; $j++) {
                            $bef_tmpt = $bef_tmpt_page_arr[$j];

                            // 전면 인쇄도수에 따른 핀지 계산
                            $bef_print_name        = $bef_tmpt["name"];
                            $bef_print_prdt_mpcode = $bef_tmpt["prdt_mpcode"];
                            $bef_print_cate_mpcode = $bef_tmpt["cate_mpcode"];
                            $bef_print_tot_tmpt    = $bef_tmpt["tot_tmpt"];
                            $bef_print_affil       = $bef_tmpt["affil"];
                            $bef_print_crtr_unit   = $bef_tmpt["crtr_unit"];
                            $bef_print_tmpt        = $bef_tmpt["beforeside_tmpt"];
                            $bef_output_board_amt  = $bef_tmpt["output_board_amt"];

                            // 종이계열과 인쇄계열이 맞지 않으면 넘어감
                            if ($bef_print_affil !== $paper_affil) {
                                continue;
                            }

                            $bef_extra_paper_amt = $extra_paper_amt_arr[$bef_print_name];
                            $bef_extra_paper_amt = doubleval($bef_extra_paper_amt);

                            if ($paper_crtr_unit === 'R') {
                                $bef_extra_paper_amt /= 500.0;
                            }

                            // 후면 인쇄도수
                            for ($k = 0; $k < $aft_tmpt_page_arr_count; $k++) {
                                $aft_tmpt = $aft_tmpt_page_arr[$k];

                                // 후면 인쇄도수에 따른 핀지 계산
                                $aft_print_name        = $aft_tmpt["name"];
                                $aft_print_prdt_mpcode = $aft_tmpt["prdt_mpcode"];
                                $aft_print_cate_mpcode = $aft_tmpt["cate_mpcode"];
                                $aft_print_tot_tmpt    = $aft_tmpt["tot_tmpt"];
                                $aft_print_affil       = $bef_tmpt["affil"];
                                $aft_print_crtr_unit   = $aft_tmpt["crtr_unit"];
                                $aft_print_tmpt        = $aft_tmpt["aftside_tmpt"];
                                $aft_output_board_amt  = $aft_tmpt["output_board_amt"];

                                // 종이계열과 인쇄계열이 맞지 않으면 넘어감
                                if ($aft_print_affil !== $paper_affil) {
                                    continue;
                                }

                                // 종이가 내지면 같은 도수만 취급함
                                if ($page_dvs === "내지" &&
                                        $bef_print_name !== $aft_print_name) {
                                    continue;
                                }
                                //echo "[org] $page_dvs [$print_purp] : $bef_print_name [$bef_print_cate_mpcode] / $aft_print_name [$aft_print_cate_mpcode]\n";

                                //echo "$print_purp : $bef_print_name / $aft_print_name :: $bef_print_cate_mpcode / $aft_print_cate_mpcode\n";
                                $aft_extra_paper_amt = $extra_paper_amt_arr[$aft_print_name];
                                $aft_extra_paper_amt = doubleval($aft_extra_paper_amt);

                                if ($paper_crtr_unit === 'R') {
                                    $aft_extra_paper_amt /= 500.0;
                                }

                                $extra_paper_amt = $aft_extra_paper_amt + $bef_extra_paper_amt;

                                // 전면 추가도수
                                for ($m = 0; $m < $bef_add_tmpt_page_arr_count; $m++) {
                                    $bef_add_tmpt = $bef_add_tmpt_page_arr[$m];

                                    // 전면 추가도수에 따른 핀지 계산
                                    $bef_add_print_name        = $bef_add_tmpt["name"];
                                    $bef_add_print_prdt_mpcode = $bef_add_tmpt["prdt_mpcode"];
                                    $bef_add_print_cate_mpcode = $bef_add_tmpt["cate_mpcode"];
                                    $bef_add_print_tot_tmpt    = $bef_add_tmpt["tot_tmpt"];
                                    $bef_add_print_affil       = $bef_tmpt["affil"];
                                    $bef_add_print_crtr_unit   = $bef_add_tmpt["crtr_unit"];
                                    $bef_add_output_board_amt  = $bef_add_tmpt["output_board_amt"];

                                    $bef_add_extra_paper_amt = $extra_paper_amt_arr[$bef_add_print_name];
                                    $bef_add_extra_paper_amt = doubleval($bef_add_extra_paper_amt);

                                    // 종이계열과 인쇄계열이 맞지 않으면 넘어감
                                    if ($bef_add_print_affil !== $paper_affil) {
                                        continue;
                                    }

                                    if ($paper_crtr_unit === 'R') {
                                        $bef_add_extra_paper_amt /= 500.0;
                                    }

                                    // 후면 추가도수
                                    for ($n = 0; $n < $aft_add_tmpt_page_arr_count; $n++) {
                                        // 수량부분에서 쿼리 처리하면
                                        // 양이 너무 많아짐
                                        $ret = array();

                                        $aft_add_tmpt = $aft_add_tmpt_page_arr[$n];

                                        // 전면 추가도수에 따른 핀지 계산
                                        $aft_add_print_name        = $aft_add_tmpt["name"];
                                        $aft_add_print_prdt_mpcode = $aft_add_tmpt["prdt_mpcode"];
                                        $aft_add_print_cate_mpcode = $aft_add_tmpt["cate_mpcode"];
                                        $aft_add_print_tot_tmpt    = $aft_add_tmpt["tot_tmpt"];
                                        $aft_add_print_affil       = $bef_tmpt["affil"];
                                        $aft_add_print_crtr_unit   = $aft_add_tmpt["crtr_unit"];
                                        $aft_add_output_board_amt  = $aft_add_tmpt["output_board_amt"];

                                        // 종이계열과 인쇄계열이 맞지 않으면 넘어감
                                        if ($aft_add_print_affil !== $paper_affil) {
                                            continue;
                                        }

                                        // 종이가 내지면 같은 도수만 취급함
                                        if ($page_dvs === "내지" && $bef_add_print_name !== $aft_add_print_name) {
                                            continue;
                                        }
                                        //echo "[add] $page_dvs [$print_purp] : $bef_add_print_name [$bef_add_print_cate_mpcode] / $aft_add_print_name [$aft_add_print_cate_mpcode]\n";

                                        $aft_add_extra_paper_amt = $extra_paper_amt_arr[$aft_add_print_name];
                                        $aft_add_extra_paper_amt = doubleval($aft_add_extra_paper_amt);

                                        if ($paper_crtr_unit === 'R') {
                                            $aft_add_extra_paper_amt /= 500.0;
                                        }

                                        $extra_paper_amt += $aft_extra_paper_amt + $bef_extra_paper_amt;

                                        // 사이즈
                                        while ($size_rs && !$size_rs->EOF) {
                                            // 사이즈에 다른 자리수 계산
                                            $output_name        = $size_rs->fields["name"];
                                            $output_prdt_mpcode = $size_rs->fields["prdt_mpcode"];
                                            $output_cate_mpcode = $size_rs->fields["cate_mpcode"];

                                            $pos_num = $pos_num_arr[$output_name];

                                            if (empty($pos_num) === true || $pos_num === 0) {
                                                return "FAIL!";
                                            }

                                            // 앞에서 뽑은 페이지 배열
                                            for ($l = 0; $l < $page_arr_count; $l++) {
                                                $page_num = $page_arr[$l];
                                                $page_num = explode('!', $page_num);
                                                $page_detail = $page_num[1];
                                                $page_num = intval($page_num[0]);

                                                // 실제 인쇄수량 계산
                                                $temp = array();

                                                $real_paper_amt = 0;
                                                $calc_paper_amt = 0;
                                                if ($page_num !== 0) {
                                                    $temp["amt"]       = $amt;
                                                    $temp["pos_num"]   = $pos_num;
                                                    $temp["page_num"]  = $page_num;
                                                    $temp["amt_unit"]  = $amt_unit;
                                                    $temp["crtr_unit"] = $paper_crtr_unit;

                                                    $real_paper_amt =
                                                        $this->getPaperRealPrintAmt($temp);

                                                    // 여분지 포함 인쇄수량 계산
                                                    $calc_paper_amt =
                                                        $extra_paper_amt + $real_paper_amt;
                                                    //$calc_paper_amt = ceil($calc_paper_amt);
                                                }

                                                //echo "amt : $amt / pos : $pos_num / page : $page_num / real : $real_paper_amt \n";

                                                // 종이 가격 계산
                                                unset($temp);
                                                $temp["sell_site"] = $sell_site;
                                                $temp["mpcode"]    = $paper_prdt_mpcode;

                                                $paper_price = 0;
                                                if ($page_num !== 0) {
                                                    $paper_price = $dao->selectPaperPrice($conn, $temp);
                                                }

                                                // 가격 없는 종이 건너뜀
                                                if ($paper_price === null) {
                                                    continue;
                                                }

                                                $paper_price  = intval($paper_price);
                                                $paper_price *= $calc_paper_amt;

                                                // 인쇄 가격 계산
                                                // 페이지가 0이면 인쇄가격은 0원이다
                                                $print_price = 0;
                                                if ($page_num !== 0 &&
                                                        $aft_print_tot_tmpt !== '0' &&
                                                        $bef_print_tot_tmpt !== '0') {

                                                    if ($flattyp_yn === 'Y') {
                                                        unset($temp);
                                                        // 전면
                                                        $temp["tot_tmpt"]       = intval($bef_print_tot_tmpt);
                                                        $temp["page_num"]       = $page_num;
                                                        $temp["crtr_unit"]      = $bef_print_crtr_unit;
                                                        $temp["mpcode"]         = $bef_print_prdt_mpcode;
                                                        $temp["real_paper_amt"] = $real_paper_amt;
                                                        $temp["sell_site"]      = $sell_site;

                                                        $print_price += $this->calcSheetPrintPrice($conn,
                                                                                                   $dao,
                                                                                                   $temp);
                                                        // 후면
                                                        $temp["tot_tmpt"]       = intval($aft_print_tot_tmpt);
                                                        $temp["mpcode"]         = $aft_print_prdt_mpcode;
                                                        $print_price += $this->calcSheetPrintPrice($conn,
                                                                                                   $dao,
                                                                                                   $temp);
                                                    } else {
                                                        unset($temp);
                                                        // 인쇄대수별 종이수량 재계산용
                                                        $temp["amt"]             = $amt;
                                                        $temp["amt_unit"]        = $amt_unit;
                                                        $temp["paper_crtr_unit"] = $paper_crtr_unit;
                                                        // 인쇄가격 계산용
                                                        $temp["pos_num"]         = $pos_num;
                                                        $temp["aft_tot_tmpt"]    = intval($aft_print_tot_tmpt);
                                                        $temp["bef_tot_tmpt"]    = intval($bef_print_tot_tmpt);
                                                        $temp["page_num"]        = $page_num;
                                                        $temp["crtr_unit"]       = $bef_print_crtr_unit;
                                                        $temp["bef_mpcode"]      = $bef_print_prdt_mpcode;
                                                        $temp["aft_mpcode"]      = $aft_print_prdt_mpcode;
                                                        $temp["sell_site"]       = $sell_site;

                                                        $print_price = $this->calcBookletPrintPrice($conn,
                                                                                                    $dao,
                                                                                                    $temp);
                                                    }
                                                }
                                                // 추가도수 인쇄가격 계산용
                                                // 페이지가 0이면 인쇄가격은 0원이다
                                                $add_print_price = 0;
                                                if ($page_num !== 0 &&
                                                        $aft_add_print_tot_tmpt !== '0' &&
                                                        $bef_add_print_tot_tmpt !== '0') {
                                                    if ($flattyp_yn === 'Y') {
                                                        unset($temp);
                                                        // 전면추가
                                                        $temp["tot_tmpt"]       = intval($bef_print_tot_tmpt);
                                                        $temp["page_num"]       = $page_num;
                                                        $temp["crtr_unit"]      = $bef_print_crtr_unit;
                                                        $temp["mpcode"]         = $bef_add_print_prdt_mpcode;
                                                        $temp["real_paper_amt"] = $real_paper_amt;
                                                        $temp["sell_site"]      = $sell_site;

                                                        $print_price += $this->calcSheetPrintPrice($conn,
                                                                                                   $dao,
                                                                                                   $temp);
                                                        // 후면추가
                                                        $temp["tot_tmpt"]       = intval($aft_add_print_tot_tmpt);
                                                        $temp["mpcode"]         = $aft_add_print_prdt_mpcode;
                                                        $print_price += $this->calcSheetPrintPrice($conn,
                                                                                                   $dao,
                                                                                                   $temp);
                                                    } else {
                                                        unset($temp);
                                                        // 인쇄대수별 종이수량 재계산용
                                                        $temp["amt"]             = $amt;
                                                        $temp["amt_unit"]        = $amt_unit;
                                                        $temp["paper_crtr_unit"] = $paper_crtr_unit;
                                                        // 인쇄가격 계산용
                                                        $temp["pos_num"]         = $pos_num;
                                                        $temp["aft_tot_tmpt"]    = intval($aft_add_print_tot_tmpt);
                                                        $temp["bef_tot_tmpt"]    = intval($bef_add_print_tot_tmpt);
                                                        $temp["page_num"]        = $page_num;
                                                        $temp["crtr_unit"]       = $bef_print_crtr_unit;
                                                        $temp["bef_mpcode"]      = $bef_add_print_prdt_mpcode;
                                                        $temp["aft_mpcode"]      = $aft_add_print_prdt_mpcode;
                                                        $temp["sell_site"]       = $sell_site;

                                                        $add_print_price = $this->calcBookeletPrintPrice($conn,
                                                                                                        $dao,
                                                                                                        $temp);
                                                    }
                                                }

                                                //echo "bef_tmpt : $bef_print_tmpt / aft_tmpt : $aft_print_tmpt / price : $print_price\n";
                                                //echo "bef_add : $bef_add_print_tot_tmpt / aft_add : $aft_add_print_tot_tmpt / add_price : $add_print_price\n";

                                                // 출력 가격 계산
                                                $output_price = 0;
                                                if ($page_num !== 0) {
                                                    unset($temp);
                                                    $temp["pos_num"]   = $pos_num;
                                                    $temp["page_num"]  = $page_num;
                                                    $temp["board_amt"] = $bef_output_board_amt +
                                                                         $aft_output_board_amt +
                                                                         $aft_add_output_board_amt +
                                                                         $bef_add_output_board_amt;
                                                    $temp["bef_tmpt"]  = $bef_print_tmpt;
                                                    $temp["aft_tmpt"]  = $aft_print_tmpt;
                                                    $temp["mpcode"]    = $output_prdt_mpcode;
                                                    $temp["sell_site"] = $sell_site;

                                                    if ($page_num > 2) {
                                                        $output_price =
                                                            $this->calcOutputPrice($conn,
                                                                                   $dao,
                                                                                   $temp);
                                                    } else {
                                                        $output_price =
                                                            $this->calcSheetOutputPrice(
                                                                    $conn,
                                                                    $dao,
                                                                    $temp
                                                            );
                                                    }
                                                }



                                                // 결과배열 생성
                                                $key =
                                                    sprintf("%s!%s!%s!%s!%s!%s!%s!%s!%s!%s", $page_num
                                                                                        , $paper_cate_mpcode
                                                                                        , $bef_print_cate_mpcode
                                                                                        , $bef_add_print_cate_mpcode
                                                                                        , $aft_print_cate_mpcode
                                                                                        , $aft_add_print_cate_mpcode
                                                                                        , $output_cate_mpcode
                                                                                        , $page_dvs
                                                                                        , $page_detail
                                                                                        , $paper_affil);

                                                $ret[$amt][$key]["paper"]  = $paper_price;
                                                $ret[$amt][$key]["print"]  = $print_price + $add_print_price;
                                                $ret[$amt][$key]["output"] = $output_price;
                                            }


                                            $size_rs->MoveNext();
                                        }

                                        // 가격입력
                                        //$conn->debug = 1;
                                        $dao_ret = $dao->insertCateCalcPrice($conn,
                                                                             $tb_name,
                                                                             $cate_sortcode,
                                                                             $ret);
                                        /*
                                        */
                                        //$conn->debug = 0;

                                        if ($dao_ret === false) {
                                            goto ERR;
                                        }

                                        usleep(600);

                                        $size_rs->MoveFirst();
                                    }
                                }
                            }
                        }
                    }
                }

                $paper_rs->MoveNext();
            }

            $paper_rs->MoveFirst();

        }

        return "SUCCESS!";

        ERR:
            return "FAIL!";
    }

    /**
     * @brief 책자형 인쇄물일 경우 전면/후면으로 정보 구분
     *
     * @param $rs = 책자형 카테고리에 해당하는 인쇄도수검색결과
     *
     * @return $ret["aft_side"] = 전면도수 정보배열
     * $ret["aft_side_add"] = 정면추가도수 정보배열
     * $ret["bef_side"] = 후면도수 정보배열
     * $ret["bef_side_add"] = 후면추가도수 정보배열
     * $ret["purp_dvs"] = 인쇄 용도
     */
    function getPrintSideDvsArr($rs) {
        $ret = array(
            "purp_dvs"     => array(),
            "aft_side"     => array(),
            "aft_side_add" => array(),
            "bef_side"     => array(),
            "bef_side_add" => array()
        );

        $i = 0;
        $j = 0;
        $k = 0;
        $l = 0;
        $bef_purp = null;
        while ($rs && !$rs->EOF) {
            $name        = $rs->fields["name"];
            $side_dvs    = $rs->fields["side_dvs"];
            $purp_dvs    = $rs->fields["purp_dvs"];
            $bef_tmpt    = $rs->fields["beforeside_tmpt"];
            $aft_tmpt    = $rs->fields["aftside_tmpt"];
            $add_tmpt    = $rs->fields["add_tmpt"];
            $tot_tmpt    = $rs->fields["tot_tmpt"];
            $board_amt   = $rs->fields["output_board_amt"];
            $crtr_unit   = $rs->fields["crtr_unit"];
            $prdt_mpcode = $rs->fields["prdt_mpcode"];
            $cate_mpcode = $rs->fields["cate_mpcode"];

            if ($bef_purp !== $purp_dvs) {
                $bef_purp = $purp_dvs;
                $i = 0;
                $j = 0;
                $k = 0;
                $l = 0;
            }

            $ret["purp_dvs"][$purp_dvs] = $purp_dvs;

            $info = array(
                "name"             => $name,
                "beforeside_tmpt"  => $bef_tmpt,
                "aftside_tmpt"     => $aft_tmpt,
                "add_tmpt"         => $add_tmpt,
                "tot_tmpt"         => $tot_tmpt,
                "output_board_amt" => $board_amt,
                "crtr_unit"        => $crtr_unit,
                "prdt_mpcode"      => $prdt_mpcode,
                "cate_mpcode"      => $cate_mpcode
            );

            if ($side_dvs === "전면") {
                $ret["bef_side"][$purp_dvs][$i++] = $info;
            } else if ($side_dvs === "후면") {
                $ret["aft_side"][$purp_dvs][$j++] = $info;
            } else if ($side_dvs === "전면추가") {
                $ret["bef_side_add"][$purp_dvs][$k++] = $info;
            } else if ($side_dvs === "후면추가") {
                $ret["aft_side_add"][$purp_dvs][$l++] = $info;
            }

            $rs->MoveNext();
        }

        return $ret;
    }

    /**
     * @brief 낱장형 인쇄 가격 계산
     *
     * @detail $info["tot_tmpt"] = 총도수
     * $info["page_num"] = 페이지수
     * $info["mpcode"] = 인쇄 맵핑코드
     * $info["crtr_unit"] = 기준 단위
     * $info["real_paper_amt"] = 종이 실제 수량
     * $info["sell_site"] = 판매채널
     *
     * @detail 낱장형 인쇄물에서 인쇄 대수는 1대로 계산한다
     *
     * @param $conn  = connection identifier
     * @param $dao   = 가격 검색용 dao
     * @param $param = 가격검색에 필요한 정보배열
     *
     * @return 인쇄 가격
     */
    function calcSheetPrintPrice($conn, $dao, $info) {
        $page_num       = doubleval($info["page_num"]);
        $crtr_unit      = $info["crtr_unit"];
        $real_paper_amt = $info["real_paper_amt"];

        // 종이 수량단위와 인쇄 수량단위가 틀릴경우
        if ($crtr_unit !== "R") {
            $real_paper_amt *= 500;
        }

        $param = array();
        $param["sell_site"] = $info["sell_site"];
        $param["mpcode"]    = $info["mpcode"];
        $param["amt"]       = $real_paper_amt;
        $param["tot_tmpt"]  = $info["tot_tmpt"];
        $param["page_num"]  = $page_num;

        return $this->getPrintSellPrice($conn, $dao, $param);
    }

    /**
     * @brief 책자형 인쇄 가격 계산
     *
     * @detail $info["aft_tot_tmpt"] = 전면 총도수
     * $info["bef_tot_tmpt"] = 후면 총도수
     * $info["page_num"] = 페이지수
     * $info["aft_mpcode"] = 전면 인쇄 맵핑코드
     * $info["bef_mpcode"] = 후면 인쇄 맵핑코드
     * $info["crtr_unit"] = 인쇄 기준 단위
     * $info["real_paper_amt"] = 종이 실제 수량
     * $info["sell_site"] = 판매채널
     * $info["amt"] = 상품수량
     * $info["amt_unit"] = 상품수량단위
     * $info["paper_crtr_unit"] = 종이 기준단위
     * $info["pos_num"] = 자리수
     *
     * @detail 책자형 인쇄는 낱장형 인쇄와 다르게 출력과 똑같이
     * 인쇄 대수가 적용된다.
     *
     * 홍각기 / 돈땡에 따라서 페이지가 분할되고 분할된 페이지에 따른
     * 종이 수량을 계산해서 종이 가격을 별도로 계산해서 합친다.
     *
     * @param $conn  = connection identifier
     * @param $dao   = 가격 검색용 dao
     * @param $param = 가격검색에 필요한 정보배열
     *
     * @return 인쇄 가격
     */
    function calcBookletPrintPrice($conn, $dao, $info) {
        $pos_num   = doubleval($info["pos_num"]);
        $page_num  = doubleval($info["page_num"]);
        $crtr_unit = $info["crtr_unit"];

        // 인쇄 대수로부터 대수별 페이지 계산
        // 인쇄 대수 계산
        $calc_info = $this->getMachineCount($page_num, $pos_num);
        $calc_info["pos_num"] = $pos_num;

        $hong_count = $calc_info["hong"];

        // 대수별 페이지 계산
        $calc_info = $this->getPrintBookletPageNum($calc_info);

        $param = array();
        $param["pos_num"]   = $pos_num;
        $param["amt"]       = $info["amt"];
        $param["amt_unit"]  = $info["amt_unit"];
        $param["crtr_unit"] = $info["paper_crtr_unit"];

        // 홍각기 종이수량
        $param["page_num"] = $calc_info["hong_page_num"];
        $hong_paper_amt = $this->getPaperRealPrintAmt($param);
        // 1/2 돈땡 종이수량
        $param["page_num"] = $calc_info["don_h_page_num"];
        $don_h_paper_amt = $this->getPaperRealPrintAmt($param);
        // 1/4 돈땡 종이수량
        $param["page_num"] = $calc_info["don_q_page_num"];
        $don_q_paper_amt = $this->getPaperRealPrintAmt($param);
        // 1/8 돈땡 종이수량
        $param["page_num"] = $calc_info["don_e_page_num"];
        $don_e_paper_amt = $this->getPaperRealPrintAmt($param);

        // 종이 수량단위와 인쇄 수량단위가 틀릴경우
        if ($crtr_unit !== "R") {
            $hong_paper_amt  *= 500;
            $don_h_paper_amt *= 500;
            $don_q_paper_amt *= 500;
            $don_e_paper_amt *= 500;
        }

        unset($calc_info);
        unset($param["amt_unit"]);
        unset($param["crtr_unit"]);

        $param["sell_site"] = $info["sell_site"];

        // 전면도수 가격
        $param["tot_tmpt"] = $info["bef_tot_tmpt"];
        $param["mpcode"]   = $info["bef_mpcode"];
        // 홍각기 인쇄가격
        $param["amt"] = $hong_paper_amt;
        $bef_hong_price  = $this->getPrintSellPrice($conn, $dao, $param);
        $bef_hong_price *= $hong_count;
        // 1/2 돈땡 인쇄가격
        $param["amt"] = $don_h_paper_amt;
        $bef_don_h_price = $this->getPrintSellPrice($conn, $dao, $param);
        // 1/4 돈땡 인쇄가격
        $param["amt"] = $don_q_paper_amt;
        $bef_don_q_price = $this->getPrintSellPrice($conn, $dao, $param);
        // 1/8 돈땡 인쇄가격
        $param["amt"] = $don_e_paper_amt;
        $bef_don_e_price = $this->getPrintSellPrice($conn, $dao, $param);

        $bef_print_price_sum = $bef_hong_price +
                               $bef_don_h_price +
                               $bef_don_q_price +
                               $bef_don_e_price;

        // 후면도수 가격
        $param["tot_tmpt"] = $info["aft_tot_tmpt"];
        $param["mpcode"]   = $info["aft_mpcode"];
        // 홍각기 인쇄가격
        $param["amt"] = $hong_paper_amt;
        $aft_hong_price  = $this->getPrintSellPrice($conn, $dao, $param);
        $aft_hong_price *= $hong_count;
        // 1/2 돈땡 인쇄가격
        $param["amt"] = $don_h_paper_amt;
        $aft_don_h_price = $this->getPrintSellPrice($conn, $dao, $param);
        // 1/4 돈땡 인쇄가격
        $param["amt"] = $don_q_paper_amt;
        $aft_don_q_price = $this->getPrintSellPrice($conn, $dao, $param);
        // 1/8 돈땡 인쇄가격
        $param["amt"] = $don_e_paper_amt;
        $aft_don_e_price = $this->getPrintSellPrice($conn, $dao, $param);

        /*
        echo "$hong_paper_amt / $don_h_paper_amt / $don_q_paper_amt / $don_e_paper_amt\n";
        echo "$bef_hong_price / $bef_don_h_price / $bef_don_q_price / $bef_don_e_price\n";
        echo "$aft_hong_price / $aft_don_h_price / $aft_don_q_price / $aft_don_e_price\n";
        echo "---\n";
        */

        $aft_print_price_sum = $aft_hong_price +
                               $aft_don_h_price +
                               $aft_don_q_price +
                               $aft_don_e_price;

        $ret = $bef_print_price_sum + $aft_print_price_sum;

        return $ret;
    }

    /**
     * @brief 인쇄 책자형 홍각기/돈땡별 페이지수 계산
     *
     * @detail $info["hong"] = 홍각기 대수
     * $info["don"] = 돈땡 대수
     * $info["pos_num"] = 자리수
     *
     * @param $info = 정보배열
     *
     * @return 인쇄 가격
     */
    function getPrintBookletPageNum($info) {
        $hong_count = $info["hong"];
        $don_count  = $info["don"];
        $pos_num    = $info["pos_num"];

        // 기본 페이지수
        $def_page_num = $pos_num * 2;

        // 홍각기 페이지수
        $hong_page_num = $hong_count * $def_page_num;
        // 1/2 돈땡 페이지수
        $don_h_page_num = 0;
        // 1/4 돈땡 페이지수
        $don_q_page_num = 0;
        // 1/8 돈땡 페이지수
        $don_e_page_num = 0;

        switch ($don_count) {
            case HALF_QUARTER_EIGHTH :
                // 1/2(8p), 1/4(4p), 1/8(2p) 돈땡
                $don_h_page_num = $def_page_num / 2;
                $don_q_page_num = $def_page_num / 4;
                $don_e_page_num = $def_page_num / 8;

                break;
            case HALF_QUARTER :
                // 1/2, 1/4 돈땡
                $don_h_page_num = $def_page_num / 2;
                $don_q_page_num = $def_page_num / 4;

                break;
            case HALF_EIGHTH :
                // 1/2, 1/8 돈땡
                $don_h_page_num = $def_page_num / 2;
                $don_e_page_num = $def_page_num / 8;

                break;
            case HALF :
                // 1/2 돈땡
                $don_h_page_num = $def_page_num / 2;

                break;
            case QUARTER_EIGHTH :
                // 1/4, 1/8 돈땡
                $don_q_page_num = $def_page_num / 4;
                $don_e_page_num = $def_page_num / 8;

                break;
            case QUARTER :
                // 1/4 돈땡
                $don_q_page_num = $def_page_num / 4;

                break;
            case EIGHTH :
                // 1/8 돈땡
                $don_e_page_num = $def_page_num / 8;

                break;
        }

        //echo "$hong_count / $don_count\n";
        //echo "$hong_page_num / $don_h_page_num / $don_q_page_num / $don_e_page_num\n";

        return array(
            "hong_page_num"  => $hong_page_num,
            "don_h_page_num" => $don_h_page_num,
            "don_q_page_num" => $don_q_page_num,
            "don_e_page_num" => $don_e_page_num
        );
    }

    /**
     * @brief 인쇄 가격 계산
     *
     * @detail 실제 계산 공식은 아래와 같다
     *  - 인쇄 기계 대수 공식 =
     *      소수점 올림{$사용자가 입력한 페이지 수$ / ($규격의 자리수$ * 2)}
     *  - 인쇄 가격 공식 =
     *      ($전체 도수$ * $인쇄 대수$ * 소수점 올림{$인쇄 연수$}) * $인쇄 단가$
     *
     * @param $conn  = connection identifier
     * @param $dao   = 가격 검색용 dao
     * @param $param = 가격검색에 필요한 정보배열
     *
     * @return 인쇄 가격
     */
    function getPrintSellPrice($conn, $dao, $param) {
        $page_num = $param["page_num"];
        $tot_tmpt = $param["tot_tmpt"];
        $amt      = ceil($param["amt"]);

        if ($amt == 0) {
            return 0;
        }

        $sell_price = $dao->selectPrintPrice($conn, $param);

        $price = $tot_tmpt * $amt * $sell_price;

        //echo "[PRINT] $tot_tmpt * $amt * $sell_price = $price \n";

        return intval($price);
    }

    /**
     * @brief 낱장형 출력 가격 계산
     *
     * @param $conn  = connection identifier
     * @param $dao   = 가격 검색용 dao
     * @param $info = 가격검색에 필요한 정보배열
     *
     * @return 출력 가격
     */
    function calcSheetOutputPrice($conn, $dao, $info) {
        $sell_site = $info["sell_site"];

        $pos_num   = doubleval($info["pos_num"]);
        $board_amt = $info["board_amt"];
        $mpcode    = $info["mpcode"];

        if ($pos_num > 4) {
            $board_amt /= 2;
        }

        $param = array();
        $param["sell_site"] = $sell_site;
        $param["mpcode"]    = $mpcode;

        $sell_price = $dao->selectOutputPrice($conn, $param);

        $price = $board_amt * intval($sell_price);

        //echo "[OUTPUT] $board_amt * $sell_price = $price \n";

        return $price;
    }

    /**
     * @brief 출력 가격 계산
     *
     * @detail $info["page_num"] = 페이지수
     * $info["board_amt"] = 출력판 수량
     * $info["aft_tmpt"] = 전면도수
     * $info["bef_tmpt"] = 후면도수
     * $info["mpcode"] = 여분지 수량
     * $info["sell_site"] = 판매채널
     *
     * @detail 실제 계산 공식은 아래와 같다
     *  - 출력 기계 대수 공식 =
     *      $사용자가 입력한 페이지 수$ / ($규격의 자리수$ * 2)
     *  - 출력판수 산출 공식 =
     *      ($홍각기 대 수$ * $판수$) + ($돈땡 대 수$ + ($판수$ / 2))
     *  - 출력 가격 공식 =
     *      $전체 출력판 수$ * $출력판당 가격$
     *
     * @detail 홍각기/돈땡 구분법은 전/후면 도수로 구분한다
     *  - 홍각기 => 전면도수 != 후면도수
     *  - 돈땡   => 전면도수 == 후면도수
     *
     * @param $conn  = connection identifier
     * @param $dao   = 가격 검색용 dao
     * @param $info = 가격검색에 필요한 정보배열
     *
     * @return 출력 가격
     */
    function calcOutputPrice($conn, $dao, $info) {
        $sell_site = $info["sell_site"];

        $pos_num   = doubleval($info["pos_num"]);
        $page_num  = doubleval($info["page_num"]);
        $board_amt = $info["board_amt"];
        $bef_tmpt  = $info["bef_tmpt"];
        $aft_tmpt  = $info["aft_tmpt"];
        $mpcode    = $info["mpcode"];

        $count_arr  = $this->getMachineCount($page_num, $pos_num);
        $hong_count = $count_arr["hong"];
        $don_count  = $count_arr["don"];

        switch ($don_count) {
            case HALF_QUARTER_EIGHTH :
                // 1/2, 1/4, 1/8 돈땡
                $don_count = 3;
                break;
            case HALF_QUARTER :
                // 1/2, 1/4 돈땡
                $don_count = 2;
                break;
            case HALF_EIGHTH :
                // 1/2, 1/8 돈땡
                $don_count = 2;
                break;
            case HALF :
                // 1/2 돈땡
                $don_count = 1;
                break;
            case QUARTER_EIGHTH :
                // 1/4, 1/8 돈땡
                $don_count = 2;
                break;
            case QUARTER :
                // 1/4 돈땡
                $don_count = 1;
                break;
            case EIGHTH :
                // 1/8 돈땡
                $don_count = 1;
                break;
        }

        // 돈땡 판수 계산용, 각 도수가 1도보다 커야 돈땡 가능
        if (('1' < $bef_tmpt) &&
                ('1' < $aft_tmpt) &&
                ($bef_tmpt === $aft_tmpt)) {
            $board_count = $don_count * ($board_amt >> 1);
        } else {
            if ((('1' === $bef_tmpt) ||
                    ('1' === $aft_tmpt)) &&
                    $don_count !== 0) {
                $don_count = 0;
                $hong_count++;
            }

            $board_count = $don_count * $board_amt;
        }

        $board_count += $hong_count * $board_amt;

        $param = array();
        $param["sell_site"] = $sell_site;
        $param["mpcode"]    = $mpcode;

        $sell_price = $dao->selectOutputPrice($conn, $param);

        $price = $board_count * intval($sell_price);

        //echo "aft_tmpt : $aft_tmpt / bef_tmpt = $bef_tmpt | hong : $hong_count / don : $don_count | $board_count * $sell_price = $price \n";

        return $price;
    }

    /**
     * @brief 인쇄/출력 기계 대수 반환
     *
     * @param $page_num = 페이지 수
     * @param $pos_num  = 자리 수
     *
     * @return $ret["hone"] = 홍각기 상수값
     * $ret["don"] = 돈땡 상수값
     */
    function getMachineCount($page_num, $pos_num) {
        $count = strval($page_num / ($pos_num * 2.0));
        $count = explode('.', $count);
        // 홍각기 대수
        $hong_count = intval($count[0]);
        // 돈땡 대수
        $don_count = intval($count[1]);

        return array(
            "hong" => $hong_count,
            "don"  => $don_count
        );
    }

    /**
     * @brief 검색결과를 배열로 변환
     *
     * @param $rs = 검색결과
     * @param $field = 배열에 저장할 필드명
     *
     * @return 변환된 배열
     */
    function rs2arr($rs, $field) {
        $ret = array();

        $i = 0;
        while ($rs && !$rs->EOF) {
            $ret[$i++] = $rs->fields[$field];
            $rs->MoveNext();
        }

        return $ret;
    }

    /**
     * @brief 실제 종이 인쇄 수량 계산
     *
     * @detail $param["amt"] = 상품 수량
     * $param["pos_num"] = 자리수
     * $param["page_num"] = 페이지수
     * $param["amt_unit"] = 상품 수량 단위
     * $param["crtr_unit"] = 종이 기준 단위
     *
     * @detail 실제 계산 공식은 아래와 같다
     * ((소수점_올림{
     *     (($수량$ / $자리수$) / (2 / $페이지수$)) / $카테고리 적용 절 수$
     * }) + $핀장수$ / $카테고리 적용 절 수$)[ / 500]
     *
     * @param $param = 가격검색에 필요한 정보배열
     *
     * @return 실제 종이 인쇄 수량
     */
    function getPaperRealPrintAmt($info) {
        $amt       = $info["amt"];
        $pos_num   = $info["pos_num"];
        $page_num  = $info["page_num"];
        $amt_unit  = $info["amt_unit"];
        $crtr_unit = $info["crtr_unit"];

        // 0page일 경우 인쇄 수량 0 반환
        if ($page_num == 0) {
            return 0;
        }

        $ret = ceil(($amt / $pos_num) / (2 / $page_num));

        //echo "$amt : $pos_num : $page_num : $ret\n";

        if ($crtr_unit === 'R') {
            if ($amt_unit !== '연' && $amt_unit !== 'R') {
                $ret /= 500.0;
            }
        }

        return $ret;
    }
}
?>
