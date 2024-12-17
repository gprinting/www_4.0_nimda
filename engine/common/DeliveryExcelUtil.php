<?
/**
 * @file ExcelUtil.php
 *
 * @brief 엑셀파일 정보추출 관련 공통함수 클래스
 */
include_once($_SERVER["DOCUMENT_ROOT"] . '/engine/common/ExcelUtilInterface.php');

class DeliveryExcelUtil implements ExcelUtilInterface {

    var $obj_PHPExcel; // 엑셀파일객체
    var $sheet_count;  // 엑셀파일 시트의 개수

    function __construct() {

    }

    function __destruct() {
        if ($this->obj_PHPExcel !== NULL) {
            $this->obj_PHPExcel->disconnectWorksheets();
            $this->obj_PHPExcel = null;
            unset($this->obj_PHPExcel);
        }
    }

    function initExcelFileReadInfo($excel_path,
                                   $price_info_row_idx,
                                   $chunk_col_count,
                                   $chunk_col_remainder) {

        $this->obj_PHPExcel = PHPExcel_IOFactory::load($excel_path);

        $this->sheet_count = $this->obj_PHPExcel->getSheetCount();
    }

    function insertDeliveryInfo($conn, $MoamoaDAO, $userid) {

        $post_param = "";
        $exist_data = false;
        $z = 0;
        for ($i = 0; $i < $this->sheet_count; $i++) {
            $sheet = $this->obj_PHPExcel->getSheet($i);
            $highest_row_idx = $sheet->getHighestRow();
            for ($row_idx = 2; $row_idx <= $highest_row_idx; $row_idx++) {
                $cell1 = $sheet->getCellByColumnAndRow(0, $row_idx);

                $param = array();
                $param["ordernum"] = $cell1->getValue();
                $MoamoaDAO->InitDeliveryInfo($conn, $param);
            }

            for ($row_idx = 2; $row_idx <= $highest_row_idx; $row_idx++) {
                $cell1 = $sheet->getCellByColumnAndRow(0, $row_idx);
                $cell2 = $sheet->getCellByColumnAndRow(1, $row_idx);

                $param = array();
                $param["ordernum"] = $cell1->getValue();
                $param["invo_num"] = $cell2->getValue();
                $param["state"] = "3320";
                $param['empl_id'] = $userid;

                if($param["ordernum"] != "" && $param["invo_num"] != "") {
                    $MoamoaDAO->UpdateDeliveryInfo($conn, $param);
                    //$MoamoaDAO->updateProductStatecode($conn, $param);
                    //$MoamoaDAO->insertStateHistory($conn, $param);
                    $OPI_rs = $MoamoaDAO->selectOPIInfo($conn, $param);

                    while($OPI_rs && !$OPI_rs->EOF) {
                        $exist_data = true;
                        if($OPI_rs->fields["OPI_Date"] != null) {
                            if($post_param == "")
                                $post_param .= "DP-" . $OPI_rs->fields["OPI_Date"] . "-" . $OPI_rs->fields["OPI_Seq"] . "," . $param["invo_num"];
                            else
                                $post_param .= "|" . "DP-" . $OPI_rs->fields["OPI_Date"] . "-" . $OPI_rs->fields["OPI_Seq"] . "," . $param["invo_num"];
                        }

                        $OPI_rs->MoveNext();
                    }
                }
            }

        }
        if($exist_data) {
            $post_data = array();
            $post_data["mode"] = "Deliv_End_Direct_46";
            $post_data["Or_Number2"] = json_encode($post_param);

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "http://30.gprinting.co.kr/ISAF/Libs/php/doquery40.php");
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);

            $headers = array();
            $response = curl_exec($ch);
            //$status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            curl_close($ch);
        }
    }

    /**
     * @brief $price_info_arr 필드를 이용해서<br/>
     * 업로드한 엑셀파일로부터 가격을 입력하기 위한<br/>
     * 정보를 추출하고 가격 테이블에 입력하는 함수
     *
     * @param $sheet = 정보를 추출할 워크시트
     *
     * @return 추출한 정보 배열
     */
    function makePriceInfo($sheet) {
        $price_info_arr = array();
        $price_arr = array();
        $dvs_arr = array();

        // 주요 정보가 공백이라서 넘어가야 되는 셀의 인덱스
        $pass_col_idx_arr = array();

        $unit = ""; // 수량 or 평량 단위

        $sheet_title = $sheet->getTitle();

        $highest_row_idx = $sheet->getHighestRow();
        $highest_col = $sheet->getHighestColumn();
        $highest_col_idx = PHPExcel_CELL::columnIndexFromString($highest_col);

        /*
         * A1 셀의 값이 맵핑코드인지 판단하고
         * 맵핑코드가 아닐경우 $price_info_row_idx 값을 1 감소시킨다
         */
        $cell_value = $sheet->getCell("A1")->getValue();
        if ($this->mpcode_flag && $cell_value !== "맵핑코드") {
            $this->price_info_row_idx--;
            $this->price_dvs_row_idx = $this->price_info_row_idx + 1;
            $this->price_row_idx = $this->price_dvs_row_idx + 1;

            $this->mpcode_flag = false;
        }

        $j = 0; // 가격 구분(평량, 수량, etc...) 배열 인덱스
        for ($row_idx = 1; $row_idx <= $highest_row_idx; $row_idx++) {

            for ($col_idx = 1; $col_idx < $highest_col_idx; $col_idx++) {

                if ($row_idx <= $this->price_info_row_idx) {
                    /*
                     * 데이터 행(맵핑코드, 제조사, etc...)에서 정보 추출
                     *
                     * $price_info_arr에 저장되는 형태는
                     * $price_info_arr[$row_idx][$col_idx] = "정보" 의 형태이다.
                     *
                     * ex) $price_info_arr[1][0] = "한솔제지" ...
                     */

                    // i % 4 === 1
                    // 정보 뭉치의 시작점 열 인덱스일 경우
                    if (($col_idx % $this->chunk_col_count) === $this->chunk_col_remainder) {

                        $cell = $sheet->getCellByColumnAndRow($col_idx, $row_idx);
                        $cell_value = $cell->getValue();

                        $title = $sheet->getCellByColumnAndRow(0, $row_idx)->getValue();

                        if ($this->checkNull($cell_value)) {
                            /*
                             * 브랜드에 공백값이 들어올 경우 '-' 처리 해줘야됨
                             * 그 외 셀이 공백일 경우에는 건너뛰는 셀로 처리
                             */
                            if ($title === "브랜드" || $title === "브렌드") {
                                $cell_value = '-';
                            } else {
                                $pass_col_idx_arr[$sheet_title][$col_idx] = true;
                                break;
                            }
                        }

                        $price_info_arr[$row_idx][$col_idx] = $cell_value;
                    }
                } else if ($row_idx === $this->price_dvs_row_idx) {
                    // 가격 구분 행의 첫 번째 셀에서 단위 추출

                    $prefix_idx = null;

                    $cell = $sheet->getCellByColumnAndRow(0, $row_idx);
                    $cell_value = $cell->getValue();

                    if (strpos($cell_value, '(')) {
                        /**
                         * 단위가 따로 존재하는경우
                         */

                        $prefix_idx = strpos($cell_value, '(');
                        //$prefix_idx++;

                        //$unit = substr($cell_value, $prefix_idx, -1);
                        $unit = $cell_value[$prefix_idx + 1];
                    } else {
                        $unit = "";
                    }

                } else if ($row_idx >= $this->price_row_idx) {
                    /*
                     * 가격 행에서 정보 추출
                     *
                     * $price_arr에 저장되는 형태는
                     * $price_arr[$dvs][$col_idx] = "가격" 의 형태이다.
                     *
                     * ex) $price_arr["130g"][0] = "16000" ...
                     */

                    if ($pass_col_idx_arr[$sheet_title][$col_idx]) {
                        break;
                    }

                    // 가격 구분 셀
                    // ex) 수량일 경우 250, 500, ...
                    $dvs_cell = $sheet->getCellByColumnAndRow(0, $row_idx);
                    $dvs  = $dvs_cell->getValue();
                    $dvs .= $unit;

                    $cell = $sheet->getCellByColumnAndRow($col_idx, $row_idx);
                    $cell_value = $cell->getCalculatedValue();

                    // 기본금액이 0이면 제외
                    if (($col_idx % 4 === 1) && ($cell_value === 0.0)) {
                        continue;
                    }

                    $price_arr[$dvs][$col_idx] = $cell_value;
                }
            }

            if ($row_idx >= $this->price_row_idx) {
                /*
                 * 종이 가격 구분 항목 생성
                 *
                 * 종이 - 평량 / 인쇄 - 수량 ...
                 */

                $dvs_cell = $sheet->getCellByColumnAndRow(0, $row_idx);
                $dvs  = $dvs_cell->getValue();
                $dvs .= $unit;

                $dvs_arr[$j++] = $dvs;
            }
        }

        $ret_arr = $this->makeRetArr($sheet,
            $price_info_arr,
            $price_arr,
            $dvs_arr);


        return $ret_arr;
    }

    /**
     * @brief 셀 값이 널인지 판독
     *
     * @param $cell_value = 셀 값
     *
     * @return 널 값이면 true / 아니면 false
     */
    function checkNull($cell_value) {
        if ($cell_value === null) {
            return true;
        }

        return false;
    }

    /**
     * @brief 셀 값이 공백인지 판독
     *
     * @param $cell_value = 셀 값
     *
     * @return 공백이면 true / 아니면 false
     */
    function checkBlank($cell_value) {
        if ($cell_value === "") {
            return true;
        } else if ($cell_value === '') {
            return true;
        }

        return false;
    }

    /**
     * @brief 전단위 반올림
     *
     * @param $val = 올림할 값
     *
     * @return 계산된 값
     */
    function ceilVal($val) {
        /*
        $val = floatval($val);

        $val = round($val * 0.1) * 10;
        */

        return round($val);
    }

    function makeRetArr($sheet, $price_info_arr, $price_arr, $dvs_arr)
    {
        // TODO: Implement makeRetArr() method.
    }
}
?>
