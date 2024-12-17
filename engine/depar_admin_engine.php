#! /usr/local/php/bin/php -f
<?
/**
 * @file depar_admin_engine.php
 *
 * @brief 부서관리 입력 데이터 생성, DB입력
 */

include_once(dirname(__FILE__) . '/common/ConnectionPool.php');
include_once(dirname(__FILE__) . '/dao/EngineDAO.php');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$dao = new EngineDAO();

// csv파일 경로
$file_dir  = dirname(__FILE__) . '/util_engine/csv/';
$file_dir .= "부서관리.csv";

// 함수 한글깨지는것 방지
//setlocale(LC_CTYPE, 'ko_KR.eucKR');
$target_file = fopen($file_dir, "r");

$param = array();
$conn->StartTrans();

$i = 0;
while ($line = fgetcsv($target_file, 10000, ',', '"')) {

    if ($i != 0) {
        $depar_name      = $line[0]; 
        $depar_code      = intval($line[1]); 
        $high_depar_code = $line[2]; 
        $eng_name        = $line[3]; 
        $eng_abb         = $line[4]; 
        $use_yn          = $line[5]; 
        $level           = $line[6]; 

        /* Parameter */
        $param["depar_name"]      = $depar_name;
        $param["depar_code"]      = $depar_code;
        $param["high_depar_code"] = $high_depar_code;
        $param["eng_name"]        = $eng_name;
        $param["eng_abb"]         = $eng_abb;
        $param["use_yn"]          = $use_yn;
        $param["level"]           = $level;

        // Insert Query
        $rs = $dao->insertDeparAdmin($conn, $param);

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
