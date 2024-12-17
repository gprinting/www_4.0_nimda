#! /usr/bin/php -f
<?
/**
 * @file OutputPurPriceEngine.php
 *
 * @brief 출력 매입가격 엑셀 등록 엔진
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

$excelUtil->initExcelFileReadInfo($excel_path, 9, 4, 1);

$insert_ret = null;

//$conn->debug = 1;

$insert_ret = $excelUtil->insertPurPriceInfo($conn, $priceDAO);

$conn->Close();

// 결과로그 생성
$fp = fopen($base_path . "/log/OutputPurPrice.log", "w");
fwrite($fp, $insert_ret);
fclose($fp);
?>
