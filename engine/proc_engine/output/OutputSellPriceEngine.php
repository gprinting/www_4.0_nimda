#! /usr/bin/php -f
<?
/**
 * @file OutputSellPriceEngine.php
 *
 * @brief 출력 판매가격 엑셀 등록 엔진
 */

$excel_path = $argv[1]; // 엑셀파일 경로
$base_path  = $argv[2]; // include용 기본경로

include_once($base_path . '/common/ConnectionPool.php');
include_once($base_path . '/common/OutputPriceExcelUtil.php');
include_once($base_path . '/dao/OutputPriceRegiDAO.php');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$priceDAO = new OutputPriceRegiDAO();

$excelUtil = new OutputPriceExcelUtil();

$excelUtil->initExcelFileReadInfo($excel_path, 4, 4, 1);

$insert_ret = null;

//$conn->debug = 1;

$insert_ret = $excelUtil->insertSellPriceInfo($conn, $priceDAO);

// 결과로그 생성
$fp = fopen($base_path . "/log/OutputSellPrice.log", "w");
fwrite($fp, $insert_ret);
fclose($fp);

@unlink($excel_path);
?>
