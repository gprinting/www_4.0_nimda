<?
/**
 * ExcelUtil.php 파일에 반드시 구현되야 하는 함수목록
 */
interface ExcelUtilInterface {
    function initExcelFileReadInfo($excel_path,
                                   $price_info_row_idx,
                                   $chunk_col_count,
                                   $chunk_col_remainder);

    function makePriceInfo($sheet);

    function makeRetArr($sheet,
                        $price_info_arr,
                        $price_arr,
                        $dvs_arr);

    function checkNull($cell_value);

    function checkBlank($cell_value);
}
?>
