<?
/**
 * @file ExcelUtil.php
 *
 * @brief 엑셀파일 정보추출 관련 공통함수 클래스
 */

include_once(dirname(__FILE__) . '/excel/PHPExcel.php');
include_once(dirname(__FILE__) . '/ExcelUtilInterface.php');

class ExcelUtil implements ExcelUtilInterface {

    var $obj_PHPExcel; // 엑셀파일객체
    var $sheet_count;  // 엑셀파일 시트의 개수

    var $price_info_row_idx;  // 가격정보 행 끝 위치
    var $price_dvs_row_idx;   // 가격 구분 행 위치
    var $price_row_idx;   // 가격 행 시작위치
    var $chunk_col_count; // 정보 한 덩어리가 몇 개의 셀인지 저장하는 값
    var $chunk_col_remainder; // 정보 한 덩어리를 순회할 때 시작위치값(% 연산시 나머지값)

    var $mpcode_flag = true; // 엑셀에 맵핑코드가 존재하는지 플래그

    function __construct() {
    }

    function __destruct() {
        if ($this->obj_PHPExcel !== NULL) {
            $this->obj_PHPExcel->disconnectWorksheets();
            $this->obj_PHPExcel = null;
            unset($this->obj_PHPExcel);
        }
    }

    /**
     * @brief 업로드한 엑셀파일을 이동하고 정보 초기화
     *
     * @detail $price_info_row_idx의 경우에는 맵핑코드 컬럼이
     * 포함되어있다고 가정하기 때문에 엑셀파일에 맵핑코드 행이
     * 존재하지 않는다면 기존 정보 행 수에 1을 더해준다
     *
     * @param $excel_path          = 엑셀파일 경로
     * @param $price_info_row_idx  = 가격정보 행 마지막 인덱스(맵핑코드가 있으면 수량 직전까지, 없으면 수량까지)
     * @param $price_row_idx       = 가격 행 시작 인덱스
     * @param $chunk_col_count     = 항목의 셀 수(한 항목에 기준가격|요율|적용금액|신규가격이라면 4셀이다.)
     * @param $chunk_col_remainder = 항목 셀 시작위치(A열 -> 0, B열 -> 1 ...)
     */
    function initExcelFileReadInfo($excel_path,
                                   $price_info_row_idx,
                                   $chunk_col_count,
                                   $chunk_col_remainder) {

        $this->obj_PHPExcel = PHPExcel_IOFactory::load($excel_path);

        $this->sheet_count = $this->obj_PHPExcel->getSheetCount();

        $this->price_info_row_idx = $price_info_row_idx;
        $this->price_dvs_row_idx = $price_info_row_idx + 1;
        $this->price_row_idx = $price_info_row_idx + 2;
        $this->chunk_col_count = $chunk_col_count;
        $this->chunk_col_remainder = $chunk_col_remainder;
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

                    if (isset($pass_col_idx_arr[$sheet_title][$col_idx])) {
                        //break;
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
                        //continue;
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
     * @brief makePriceInfo() 에서 생성된 정보를 취합해서 반환하는 함수
     *
     * $details $ret_arr[idx]["INFO"]  = mpcode를 얻기위한 정보<br/>
     * $ret_arr[idx]["PRICE"] = 가격정보<br/>
     * <br/>
     * ex) 종이 가격정보<br/>
     * [INFO]  => 가격구분(평량, 수량)[|맵핑코드]|제조사|브랜드|종이분류|종이정보|계열|사이즈|기준단위<br/>
     * [PRICE] => 기준가격|요율|적용금액|가격<br/>
     *
     * @param $sheet          = 현재 작업 워크시트, 최대 열 인덱스 구하는용
     * @param $price_info_arr = makePriceInfo() 에서 만들어진 정보배열
     * @param $price_arr      = makePriceInfo() 에서 만들어진 가격배열
     * @param $dvs_arr        = 가격구분 배열
     *
     * @return 만들어진 정보 배열
     *
     */
    function makeRetArr($sheet,
                        $price_info_arr,
                        $price_arr,
                        $dvs_arr) {

        $highest_col = $sheet->getHighestColumn();
        $highest_col_idx = PHPExcel_CELL::columnIndexFromString($highest_col);

        $dvs_arr_count = count($dvs_arr);

        $ret_arr = array();

        /*
         * $price_info_arr, $price_arr은 *_arr[$row_idx][$col_idx]의 형식이다 
         * 그러므로 하나의 정보 단위를 만들기 위해서 열 단위로 정보를 추출한다
         * 
         * ex) *_arr[1][1] -> 제조사 / *_arr[2][1] -> 브랜드 ...
         *     ==> 제조사/브랜드/...
         */
        $k = 0;
        for ($col_idx = 1; $col_idx < $highest_col_idx; $col_idx++) {

            // 덩어리의 처음부분 인덱스 일 때
            if (($col_idx % $this->chunk_col_count) ===
                    $this->chunk_col_remainder) {

                for ($i = 0; $i < $dvs_arr_count; $i++) {
					// 가격 구분(평량, 수량 ...)
                    $dvs =  $dvs_arr[$i];

                    // 정보부분 생성
                    $paper_info = $dvs;

					// 엑셀에 맵핑코드 없을경우 기존 로직과 꼬이지 않도록
					// 빈 값으로 추가해줌
                    if ($this->mpcode_flag === false) {
                        $paper_info .= "|-";
                    }

					// 행은 고정된 상태로 열만 움직여서 정보 추출
                    for ($row_idx = 1;
                            $row_idx <= $this->price_info_row_idx;
                            $row_idx++) {

                        $price_info = $price_info_arr[$row_idx];

                        if ($this->checkNull($price_info[$col_idx])) {
                            /*
                             * 가격 정보 행에 비어있는 값이 있으면
                             * 입력되지 않은것으로 판단하고 반복문 전체탈출
                             */

                            goto LOOP_ESCAPE;
                        }

                        $paper_info .= "|" . $price_info[$col_idx];
                    }

                    // 가격부분 생성
					// 현재 행에서 항목 덩어리만큼 더해줌
					// ex) 1행이 기본가격이면 요율, 적용금액, 신규가격을
					//	   읽기 위해서 4행만큼 더해줌
                    $limit = $col_idx + $this->chunk_col_count;

					// 결과 배열에 저장할 가격 문자열
                    $ret_price = null;

					// 가격 구분에 해당하는 가격 배열
					$dvs_price_arr = $price_arr[$dvs];
					
                    for ($j = $col_idx; $j < $limit; $j++) {
						$temp_price = $dvs_price_arr[$j];
						
                        if ($j === $col_idx) {
							// 기준가격 열
							
                            if ($this->checkNull($temp_price) ||
                                    $this->checkBlank($temp_price)) {
                                /*
                                 * 기준가격이 비어있을 경우
                                 * 가격이 입력되어있지 않다고 판단하고 반복문 탈출
                                 */
                                  
                                break;
                            }

                            $ret_price = $temp_price;
                        } else {
							//기준가격 외 열

                            if ($this->checkNull($temp_price)) {
                                $temp_price = '0';
                            }

                            $ret_price .= "|" . $temp_price;
                        }
                    }

                    if (empty($ret_price) === true) {
                        continue;
                    } 

                    $ret_arr[$k] = array();
                    $ret_arr[$k]["INFO"]    = $paper_info;
                    $ret_arr[$k++]["PRICE"] = $ret_price;
                }
            }
        }

    LOOP_ESCAPE:
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
}
?>
