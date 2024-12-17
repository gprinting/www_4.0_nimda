#! /usr/bin/php -f
<?php
/**
 * @file PlyPriceEngine.php
 *
 * @brief 합판 판매가격 엑셀 등록 엔진
 */

if(!isset($argv)) $argv = [];
if(!isset($argv[1])) $argv[1] = "";
if(!isset($argv[2])) $argv[2] = "";
if(!isset($argv[3])) $argv[3] = "";
if(!isset($argv[4])) $argv[4] = "";

if(!is_file($argv[1])) return;

$excel_path = $argv[1]; // 엑셀파일 경로
$base_path  = $argv[2]; // include용 기본경로
$sell_site  = $argv[3]; // 판매채널
$etprs_dvs  = $argv[4]; // 업체구분

//echo $excel_path . "111";exit;

include_once($base_path . '/common/ConnectionPool.php');
include_once($base_path . '/common/PlyPriceExcelUtil.php');
include_once($base_path . '/dao/PlyPriceRegiDAO.php');
include_once($base_path . '/dao/EngineDAO.php');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$priceDAO = new PlyPriceRegiDAO();
$engineDAO = new EngineDAO();

$excelUtil = new PlyPriceExcelUtil();
$excelUtil->initExcelFileReadInfo($excel_path, 10, 4, 1);


$insert_ret = null;

$fp = fopen($base_path . "/log/PlyPrice2_debug.log", "w");
fwrite($fp, print_r($priceDAO,true));
fclose($fp);

//$conn->debug = 1;
$insert_ret = $excelUtil->insertSellPriceInfo($conn, "ply_price", $priceDAO);

// 결과로그 생성
// $fp = fopen($base_path . "/log/PlyPrice2.log", "w");
// fwrite($fp, $insert_ret);
// fclose($fp);

@unlink($excel_path);
?>
