#! /usr/local/php/bin/php -f
<?
include_once(dirname(__FILE__) . "/common/PasswordEncrypt.php");
include_once(dirname(__FILE__) . '/common/ConnectionPool.php');
include_once(dirname(__FILE__) . '/dao/EngineDAO.php');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

if (count($argv) < 2) {
    echo "Usage :  updateMemberPasswd.php start_seqno [end_seqno]\n";
    exit;
}

$start_seq = $argv[1];
$end_seq   = $argv[2];

$select_base  = "\n SELECT  member_seqno";
$select_base .= "\n        ,passwd";
$select_base .= "\n   FROM  member";
$select_base .= "\n  WHERE  %s <= member_seqno";
if (empty($end_seq) === false) {
    $se_baselect .= "\n    AND  member_seqno <= {$end_seq}";
}
$select_base .= "\n  LIMIT  500";

$update_base  =  "\n UPDATE member";
$update_base .=  "\n    SET passwd = '%s'";
$update_base .=  "\n  WHERE member_seqno = %s";

$select = sprintf($select_base, $start_seq);
$conn->debug = 1;
$rs = $conn->Execute($select);
$conn->debug = 0;

$is_loop = true;

while ($is_loop) {
    $last_seq = $start_seq;

    $record_count = $rs->RecordCount();

    $j = 1;
    while ($rs && !$rs->EOF) {
        $fields = $rs->fields;

        $seqno  = $fields["member_seqno"];
        $passwd = $fields["passwd"];
        $passwd = password_hash($passwd, PASSWORD_DEFAULT);

        $last_seq = intval($seqno);

        $update = sprintf($update_base, $passwd, $seqno);

        $conn->Execute($update);

        $rs->MoveNext();

        echo $last_seq . " : " . $j++ . " / " . $record_count . "\r";
    }

    echo "\n";

    usleep(500);

    $last_seq += 1;
    $select = sprintf($select_base, $last_seq);
    $conn->debug = 1;
    $rs = $conn->Execute($select);
    $conn->debug = 0;

    if ($rs->EOF) {
        $is_loop = false;
    }
}

$conn->Close();
?>
