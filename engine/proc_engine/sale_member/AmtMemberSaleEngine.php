#! /usr/bin/php -f
<?
/**
 * @file AmtMemberSaleEngine.php
 *
 * @brief 수량 회원 할인 엑셀 등록 엔진
 */

$excel_path = $argv[1]; // 엑셀파일 경로
$base_path  = $argv[2]; // include용 기본경로

include_once($base_path . '/common/ConnectionPool.php');
include_once($base_path . '/common/AmtMemberSaleExcelUtil.php');
include_once($base_path . '/dao/AmtMemberSaleRegiDAO.php');
include_once($base_path . '/dao/EngineDAO.php');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$regiDAO = new AmtMemberSaleRegiDAO();
$engineDAO = new EngineDAO();

$excelUtil = new AmtMemberSaleExcelUtil();

$excelUtil->initExcelFileReadInfo($excel_path, 11, 4, 1);

$insert_ret = null;

//$conn->debug = 1;

$insert_ret = $excelUtil->insertAmtMemberSaleInfo($conn, $regiDAO);

$conn->Close();

// 결과로그 생성
$fp = fopen($base_path . "/log/AmtMemberSaleEngine.log", "w");
fwrite($fp, $insert_ret);
fclose($fp);
?>
