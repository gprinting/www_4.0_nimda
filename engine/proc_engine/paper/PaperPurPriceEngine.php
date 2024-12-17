#! /usr/bin/php -f
<?
/**
 * @file PaperPurPriceEngine.php
 *
 * @brief 종이 매입가격 엑셀 등록 엔진
 */

$excel_path = $argv[1]; // 엑셀파일 경로
$base_path  = $argv[2]; // include용 기본경로

include_once($base_path . '/common/ConnectionPool.php');
include_once($base_path . '/common/PaperPriceExcelUtil.php');
include_once($base_path . '/dao/PaperPriceRegiDAO.php');
include_once($base_path . '/dao/EngineDAO.php');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$priceDAO = new PaperPriceRegiDAO();
$engineDAO = new EngineDAO();

$excelUtil = new PaperPriceExcelUtil();

$excelUtil->initExcelFileReadInfo($excel_path, 8, 4, 1);

$insert_ret = null;

//$conn->debug = 1;

$insert_ret = $excelUtil->insertPurPriceInfo($conn, $priceDAO);

$conn->Close();

// 결과로그 생성
$fp = fopen($base_path . "/log/PaperPurPrice.log", "w");
fwrite($fp, $insert_ret);
fclose($fp);

@unlink($excel_path);
?>
