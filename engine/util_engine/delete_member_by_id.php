#! /usr/local/bin/php -f
<?
/**
 * @file delete_member_by_id.php
 *
 * @brief 
 */
include_once(dirname(__FILE__) . '/ConnectionPool.php');
include_once(dirname(__FILE__) . '/CommonDAO.php');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

if (count($argv) < 2) {
    echo "Useage : ./delete_member_by_id.php [member_id]\n";
    exit;
}

$member_id = $argv[1];

$conn->debug = 1;
$query  = "\n select member_seqno";
$query .= "\n from member as a";
$query .= "\n where a.member_id = '" . $member_id . "'";

$member_rs = $conn->Execute($query);

if ($member_rs->EOF) exit;

$query  = "\n select table_name";
$query .= "\n  from  information_schema.KEY_COLUMN_USAGE";
$query .= "\n  where  referenced_table_name ='member'";
$query .= "\n  and REFERENCED_COLUMN_NAME ='member_seqno'";

$table_rs = $conn->Execute($query);


while ($member_rs && !$member_rs->EOF) {
    $member_seqno = $member_rs->fields["member_seqno"];

    while ($table_rs && !$table_rs->EOF) {
        $table_name = $table_rs->fields["table_name"];

        $q_str = '';

        if ($table_name === "virt_ba_admin") {
            $q_str = sprintf("\n update %s set member_seqno = null where member_seqno = '%s'", $table_name, $member_seqno);
        } else {
            $q_str = sprintf("\n delete from %s where member_seqno = '%s'", $table_name, $member_seqno);
        }

        //echo $q_str;
        $conn->Execute($q_str);

        $table_rs->MoveNext();
    }

    $q_str = sprintf("\n delete from member where member_seqno = '%s'", $member_seqno);
    //echo $q_str;
    $conn->Execute($q_str);

    echo "\n";
    $table_rs->MoveFirst();
    $member_rs->MoveNext();
}

$conn->Close();
?>
