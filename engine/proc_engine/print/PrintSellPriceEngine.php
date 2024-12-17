#! /usr/bin/php -f
<?
/**
 * @file PrintSellPriceEngine.php
 *
 * @brief 인쇄 판매가격 엑셀 등록 엔진
 */

$excel_path = $argv[1]; // 엑셀파일 경로
$base_path  = $argv[2]; // include용 기본경로

include_once($base_path . '/common/ConnectionPool.php');
include_once($base_path . '/common/PrintPriceExcelUtil.php');
include_once($base_path . '/dao/PrintPriceRegiDAO.php');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$priceDAO = new PrintPriceRegiDAO();

$excelUtil = new PrintPriceExcelUtil();

$excelUtil->initExcelFileReadInfo($excel_path, 6, 4, 1);

$insert_ret = null;

//$conn->debug = 1;

$insert_ret = $excelUtil->insertSellPriceInfo($conn, $priceDAO);

// 결과로그 생성
$fp = fopen($base_path . "/log/PrintSellPrice.log", "w");
fwrite($fp, $insert_ret);
fclose($fp);

@unlink($excel_path);
?>
