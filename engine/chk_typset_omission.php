#! /usr/local/php/bin/php -f
<?
/**
 * @file chk_typset_omission.php
 *
 * @brief 매일 아침 6시에 조판대기인 주문들을 조판 누락으로 처리한다
 *
 * @detail 조판대기 상태 주문검색 -> 조판누락 상태로 수정
 */

//*************** 프로세스 종료시 처리부분
declare(ticks=1);

function termProc() {
    echo "Kill PROCESS\n";
    @unlink(dirname(__FILE__) . "/temp/chk_typset.pid");
    exit;
}
pcntl_signal(SIGINT , "termProc");
pcntl_signal(SIGTERM, "termProc");
//*************** 프로세스 종료시 처리부분

//*************** 프로세스 중복실행 방지부분
if (is_file(dirname(__FILE__) . "/temp/chk_typset.pid") === true) {
    echo "process is running!\r\n";
    exit;
}

$pid_fd = fopen(dirname(__FILE__) . "/temp/chk_typset.pid", 'w');
fwrite($pid_fd, getmypid());
fclose($pid_fd);
//*************** 프로세스 중복실행 방지부분

include_once(dirname(__FILE__) . '/common/ConnectionPool.php');
include_once(dirname(__FILE__) . '/dao/EngineDAO.php');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$dao = new EngineDAO();

$err_line = 0;

$state_arr = array();
$state_rs = $dao->selectStateAdminDvs($conn);

while ($state_rs && !$state_rs->EOF) {
    $fields = $state_rs->fields;
    $state_arr[$fields["erp_state_name"]] = $fields["state_code"];

    $state_rs->MoveNext();
}
unset($state_rs);

$query  = "\n UPDATE order_common";
$query .= "\n    SET order_state = '%s'";
$query .= "\n  WHERE order_common_seqno = '%s'";

while(true) {
    //$conn->debug = 1;
    
    $rs = $dao->selectTypsetOrderCommon($conn, $state_arr["조판대기"]);

    while($rs && !$rs->EOF) {
        $fields = $rs->fields;

        $order_common_seqno = $fields["order_common_seqno"];

        $q = sprintf($query, $state_arr["조판누락"], $order_common_seqno);

        $conn->Execute($q);

        $rs->MoveNext();
        sleep(1);
    }

    echo "LOOP OUT\n";

    sleep(5);
   //$conn->debug = 0;
}
?>
