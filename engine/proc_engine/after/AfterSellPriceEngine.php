#! /usr/bin/php -f
<?php
/**
 * @file AfterSellPriceEngine.php
 *
 * @brief 후공정 판매가격 엑셀 등록 엔진
 */

// SMSM
if(!isset($argv[1]) || !isset($argv[2])){
    $fp = fopen("/var/www/html/nimda/engine/log/AfterSellPrice.log", "w");
    fwrite($fp, "엑셀파일 또는 인클루드용 경로가 지정되지 않았습니다.");
    fclose($fp);
    exit('엑셀파일 또는 인클루드용 경로가 지정되지 않았습니다.');
}

$fp = fopen("/var/www/html/nimda/engine/log/AfterSellPrice-json.log", "w");
fwrite($fp, json_encode($argv));
fclose($fp);

$excel_path = $argv[1]; // 엑셀파일 경로
$base_path  = $argv[2]; // include용 기본경로

include_once($base_path . '/common/ConnectionPool.php');
include_once($base_path . '/common/AfterPriceExcelUtil.php');
include_once($base_path . '/dao/AfterPriceRegiDAO.php');
include_once($base_path . '/dao/EngineDAO.php');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$priceDAO = new AfterPriceRegiDAO();
$engineDAO = new EngineDAO();

$excelUtil = new AfterPriceExcelUtil();

$excelUtil->initExcelFileReadInfo($excel_path, 10, 4, 1);

$insert_ret = null;

//$conn->debug = 1;

$insert_ret = $excelUtil->insertSellPriceInfo($conn, $priceDAO);

// 결과로그 생성
$fp = fopen($base_path . "/log/AfterSellPrice.log", "w");
fwrite($fp, $insert_ret);
fclose($fp);

@unlink($excel_path);
?>
