#! /usr/local/php/bin/php -f
<?
/**
 * @file empl_num_engine.php
 *
 * @brief 직원 사번을 생성
 */

include_once(dirname(__FILE__) . '/common/ConnectionPool.php');
include_once(dirname(__FILE__) . '/dao/EngineDAO.php');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$dao = new EngineDAO();

// csv파일 경로
$file_dir  = dirname(__FILE__) . '/util_engine/csv/';
$file_dir .= "직원명부_old.csv";

// 함수 한글깨지는것 방지용(사용 x)
//setlocale(LC_CTYPE, 'ko_KR.eucKR');
$target_file = fopen($file_dir, "r");

$conn->debug=1;

$i = 0;
$param       = array();
$num_arr     = array();
$num_arr_sec = array();
$conn->StartTrans();
while ($line = fgetcsv($target_file, 10000, ',', '"')) {

    if ($i != 0) {

/**** 1. 사번 생성 시작 ****/
        $mon = "";
        // 10월 아래일때
        if (strlen($line[2]) == 1) {
            $mon = "0" . $line[2];
        // 10월 이상일때
        } else {
            $mon = $line[2];
        }
        $ym = substr($line[1], 2) . $mon;

        // 사번 문자단위 생성(한글깨짐 방지)
        $work_place = iconv("euc-kr", "utf-8", $line[11]); 
        //$work_place = $line[11];
        $auth = "";
        if ($work_place == "본사" || $work_place == "1공장" || $work_place == "2공장") {
            $auth = "G"; 
        } else if ($work_place == "디프") {
            $auth = "D";
        } else {
            $auth = "P";
        }

        $num_arr[$i] = $ym;
        
        if ($i > 0) {
            $j = $i - 1;
            $pre_suf = substr($num_arr_sec[$j], -2);

            if ($num_arr[$i] == $num_arr[$j]) {
                $pre_suf = $pre_suf + 1; 
                if (strlen($pre_suf) == 1) {
                    $pre_suf = "0" . $pre_suf;
                }
                $num_arr_sec[$i] = $num_arr[$i] . $pre_suf;
            } else {
                $num_arr_sec[$i] = $num_arr[$i] . "01";
            }
        }

        $empl_num = $auth . $num_arr_sec[$i];
/**** 1. 사번 생성 끝 ****/

/**** 2. 사업부서 검색 시작 ****/
        $depar_line = iconv("euc-kr", "utf-8", $line[10]); 
        //$depar_line = $line[10];
        $depar_line = trim($depar_line);
        $depar_line = '"'.$depar_line.'"';

        $depar_rs = "";
        if ($depar_line) {
            $depar_rs = $dao->selectDeparAdmin($conn, $depar_line);

            if (!$depar_rs) {
                echo "부서 검색에 실패하였습니다.";
                exit;
            }
        }
        $depar_code = $depar_rs->fields["depar_code"];

/**** 2. 사업부서 검색 끝 ****/

/**** 3. 직급 검색 시작 ****/
        $posi_line = iconv("euc-kr", "utf-8", $line[12]); 
        //$posi_line = $line[12];
        $posi_line = trim($posi_line);
        $posi_line = '"'.$posi_line.'"';

        $posi_rs = "";
        if ($posi_line) {
            $posi_rs = $dao->selectPosiAdmin($conn, $posi_line);

            if (!$posi_rs) {
                echo "직급 검색에 실패하였습니다.";
                exit;
            }
        }
        $posi_code = $posi_rs->fields["posi_code"];

/**** 3. 직급 검색 끝 ****/

        // 4. 직책
        //$job = iconv("euc-kr", "utf-8", $line[10]); 
        $job = $line[13];

        // 5. 등급
        //$admin_auth = iconv("euc-kr", "utf-8", $line[14]); 
        $admin_auth = $line[14];

        // 6. 인코딩 전으로 되돌림
        $work_place = $line[11];

        /* Parameter */
        $param["empl_num"]   = $empl_num;   // 사번
        $param["depar_code"] = $depar_code; // 사업부서
        $param["belong"]     = $work_place; // 소속
        $param["posi_code"]  = $posi_code;  // 직급
        $param["job"]        = $job;        // 직책
        $param["admin_auth"] = $admin_auth; // 등급
        
        // Insert Query 
        $rs = $dao->insertEmplNum($conn, $param);       

        if (!$rs) {
            $conn->FailTrans();
            $conn->RollbackTrans();
            echo "직원정보 입력에 실패하였습니다.";
            exit;
        }

        //print_r($param);
    }

    $i++;
}


$conn->CompleteTrans();
fclose($target_file);
?>
