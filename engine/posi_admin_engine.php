#! /usr/local/php/bin/php -f
<?
/**
 * @file posi_admin_engine.php
 *
 * @brief 직위관리 입력 데이터 생성, DB입력
 */

include_once(dirname(__FILE__) . '/common/ConnectionPool.php');
include_once(dirname(__FILE__) . '/dao/EngineDAO.php');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$dao = new EngineDAO();

// csv파일 경로
$file_dir  = dirname(__FILE__) . '/util_engine/csv/';
$file_dir .= "직위관리.csv";

// 함수 한글깨지는것 방지
//setlocale(LC_CTYPE, 'ko_KR.eucKR');
$target_file = fopen($file_dir, "r");

$param = array();
$conn->StartTrans();

$i = 0;
while ($line = fgetcsv($target_file, 10000, ',', '"')) {

    if ($i != 0) {
        $posi_code = "0" . $line[0]; 
        $posi_name = $line[1]; 
        $eng_name  = $line[2]; 
        $eng_abb   = $line[3]; 

        /* Parameter */
        $param["posi_code"] = $posi_code;
        $param["posi_name"] = $posi_name;
        $param["eng_name"]  = $eng_name;
        $param["eng_abb"]   = $eng_abb;

        // Insert Query
        $rs = $dao->insertPosiAdmin($conn, $param);

        if (!$rs) {
            $conn->FailTrans();
            $conn->RollbackTrans();
            echo "부서 입력에 실패하였습니다.";
            exit;
        }

    }

    $i++;
}

fclose($target_file);
?>
