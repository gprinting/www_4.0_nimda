#! /usr/local/bin/php -f
<?
/**
 * @file insert_after_tomson.php
 *
 * @brief 2.0 자유형 도무송 데이터 3.0 테이블로 이동입력
 */
include_once(dirname(__FILE__) . '/ConnectionPool.php');
include_once(dirname(__FILE__) . '/CommonDAO.php');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

// 자유형 도무송 사이즈별 금액
$query  = "\n INSERT INTO after_tomson_price (";
$query .= "\n      size_start";
$query .= "\n     ,size_end";
$query .= "\n     ,basic_price";
$query .= "\n     ,typ1_price";
$query .= "\n     ,typ2_price";
$query .= "\n     ,typ3_price";
$query .= "\n     ,typ4_price";
$query .= "\n )";
$query .= "\n SELECT  A.F_From";
$query .= "\n        ,A.F_To";
$query .= "\n        ,A.F_Cost";
$query .= "\n        ,A.F_Type1";
$query .= "\n        ,A.F_Type2";
$query .= "\n        ,A.F_Type3";
$query .= "\n        ,A.F_Type4";
$query .= "\n FROM WEB_FDomusong AS A";

$conn->Execute($query);

// 자유형 도무송 금액비율
$query  = "\n INSERT INTO after_tomson_price_per (";
$query .= "\n      amt";
$query .= "\n     ,knife_price_per";
$query .= "\n     ,stick_paper_price_per";
$query .= "\n     ,especial_paper_price_per";
$query .= "\n     ,basic_price";
$query .= "\n )";
$query .= "\n SELECT  A.Qty";
$query .= "\n        ,A.DomuKnifeRatio";
$query .= "\n        ,A.NormalPaperRatio";
$query .= "\n        ,A.EspecialPaperRatio";
$query .= "\n        ,A.BasePrice";
$query .= "\n FROM WEB_FDomusongRatio AS A";

$conn->Execute($query);
?>
