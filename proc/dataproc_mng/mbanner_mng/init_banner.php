<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/common/NimdaCommonDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();
$conn->StartTrans();

$dao = new NimdaCommonDAO();
$check = 1;

$param = array();
$param["table"] = "main_banner";
$param["col"] = "file_path, save_file_name";

$rs = $dao->selectData($conn, $param);

while ($rs && !$rs->EOF) {

    unlink(INC_PATH . $rs->fields["file_path"] . $rs->fields["save_file_name"]);
    $rs->moveNext();
}

$query = "TRUNCATE main_banner";

$rs = $conn->Execute($query);

if (!$rs) {
    $check = 0;
} 

$query = "TRUNCATE main_banner_set";

$rs = $conn->Execute($query);

if (!$rs) {
    $check = 0;
} 

echo $check;
$conn->CompleteTrans();
$conn->close();
?>
