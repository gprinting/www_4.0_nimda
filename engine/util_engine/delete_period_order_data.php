#! /usr/local/bin/php -f
<?
/**
 * @file delete_period_order_data.php
 *
 * @brief 특정 구간 주문데이터 삭제
 */
include_once(dirname(__FILE__) . '/ConnectionPool.php');
include_once(dirname(__FILE__) . '/CommonDAO.php');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

?>
