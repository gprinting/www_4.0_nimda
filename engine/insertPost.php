#! /usr/local/php/bin/php -f
<?

include_once('/home/dprinting/public_html/engine/common/ConnectionPool.php');

/** 
 * @brief 데이터 삽입 쿼리 함수 (공통)<br>
 *        param 배열 설명<br>
 *        $param : $param["table"] = "테이블명"<br>
 *        $param["col"]["컬럼명"] = "데이터" (다중)<br>
 * @param $conn DB Connection
 * @param $param 파라미터 인자 배열
 * @return boolean
 */ 
function insertPost($conn, $param) {

    if (!$conn) {
        echo "master connection failed\n";
        return false;
    }
 
    //주문배송, 회원, 주문 공통, 가상계좌, 견적
    if ($param["table"] == "member" || $param["table"] == "order_common" || 
            $param["table"] == "order_dlvr" || $param["table"] == "virt_ba_admin" ||
            $param["table"] == "esti") {
        echo "접근이 허용되지 않는 테이블 입니다.";
        return false;
    }

    $query = "\n INSERT INTO " . $param["table"] . "(";

    $i = 0;
    $col = "";
    $value = "";

    while (list($key, $val) = each($param["col"])) {

        $inchr = $conn->qstr($val,get_magic_quotes_gpc());

        $inchr = $val;
        if ($i == 0) {
            $col  .= "\n " . $key;
            $value  .= "\n '" . $inchr ."'";
        } else {
            $col  .= "\n ," . $key;
            $value  .= "\n ,'" . $inchr . "'";
        }

        $i++;
    }

    $query .= $col;
    $query .= "\n ) VALUES (";
    $query .= $value;
    $query .= "\n )";

    $resultSet = $conn->Execute($query);
        
    if ($resultSet === FALSE) {
        $errorMessage = "데이터 입력에 실패 하였습니다.";
        return false;
    } else {
        return true;
    }

}
    


function main($entry, $area) {

    $connectionPool = new ConnectionPool();
    $conn = $connectionPool->getPooledConnection();

    $filedir = "/home/dprinting/public_html/engine/file/"; 

    $handle = fopen($filedir . $entry, "r");

    $buffer = "";
    $cnt = 0;
    if ($handle) {
        while (!feof($handle)) {
            $buffer = fgets($handle, 4096);
            if ( $cnt % 10000 == 0 ) {
                sleep(3);
            }
            if ( $cnt > 0 ) {
                $rs = explode("|", $buffer);
                $param = "";
                $param["table"] = $area . "_zipcode";
                $param["col"]["zipcode"] = $rs[0];
                $param["col"]["sido"] = $rs[1];
                $param["col"]["gugun"] = $rs[3];
                $param["col"]["eup"] = $rs[5];
                $param["col"]["doro"] = $rs[8];
                $param["col"]["bldg"] = $rs[15];
                $param["col"]["ri"] = $rs[18];
                $param["col"]["bldg_bonbun"] = $rs[11];
                $param["col"]["bldg_bubun"] = $rs[12];
                $param["col"]["dong"] = $rs[19];
                $param["col"]["jibun_bonbun"] = $rs[21];
                $param["col"]["jibun_bubun"] = $rs[23];
                insertPost($conn, $param);
            }
            $cnt ++;
        }
    }


    //      $conn->CompleteTrans($autoComplete=true);
    fclose($handle); 
    $conn->close();

}
main($argv[1], $argv[2]); 

?>
