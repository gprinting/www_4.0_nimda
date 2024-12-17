#! /usr/local/php/bin/php -f
<?php
/**
 * @file insert_all_empl_info.php
 *
 * @brief 직원 사번을 생성
 */

include_once(dirname(__FILE__) . '/ConnectionPool.php');
include_once(dirname(__FILE__) . '/CommonDAO.php');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$dao = new CommonDAO();

$path = dirname(__FILE__) . '/csv/empl_info.csv';

$fd = fopen($path, 'r');

$empl_info = [];
$human_info = [];

$i = 0;
$j = 1;
$pre_ym = null;
while ($arr = fgetcsv($fd, 1000)) {
    if ($i === 0) {
        $i++;
        continue;
    }
    /*
       [0] =>
       [1] => 2017
       [2] => 9
       [3] => 1
       [4] => 양명석
       [5] => 남
       [6] => 640321-1031018
       [7] => 1003
       [8] => 서울특별시 강북구 삼양로159나길 19, D동 201호 (우이동,오성아트맨션)
       [9] =>  생산본부
       [10] => 2절팀
       [11] => 1공장
       [12] =>
       [13] => 2_보조
       [14] => A
     */

    $ym = $arr[1] . $arr[2];
    if (empty($pre_ym)) {
        $pre_ym = $ym;
    }
    if ($pre_ym !== $ym) {
        $pre_ym = $ym;
        $j = 1;
    }

    // 1. 사번생성
    $empl_num = '';
    if ($arr[11] === "본사" || $arr[11] == "1공장" || $arr[11] == "2공장") {
        $empl_num .= "G"; 
    } else if ($arr[11] == "디프") {
        $empl_num .= "D";
    } else {
        $empl_num .= "P";
    }

    $empl_num .= substr($arr[1], 2, 2);
    $empl_num .= str_pad($arr[2], 2, '0', STR_PAD_LEFT);
    $empl_num .= str_pad($j++, 2, '0', STR_PAD_LEFT);

    // 2. 관리권한
    $admin_auth = trim($arr[14]);

    // 3. 부서정보
    $depar_info = $dao->selectDeparAdmin($conn, trim($arr[10]));

    $high_depar_code = $depar_info["high_depar_code"];
    $depar_code = $depar_info["depar_code"];

    // 4. 소속
    $belong = trim($arr[11]);

    // 5. 직위정보
    $posi_info = $dao->selectPosiAdmin($conn, trim($arr[12]));

    $posi_code = $posi_info["posi_code"];

    // 6. 직책
    $job = trim($arr[13]);

    $empl_info = [
         "empl_num"         => $empl_num
        ,"admin_auth"       => $admin_auth
        ,"high_depar_code"  => $high_depar_code
        ,"depar_code"       => $depar_code
        ,"belong"           => $belong
        ,"posi_code"        => $posi_code
        ,"job"              => $job
    ];

    /////////////////////////////////////////////// 인적사항

    // 1. 입사일
    $enter_date = $arr[1] . '-'
                  . str_pad($arr[2], 2, '0', STR_PAD_LEFT) . '-'
                  . str_pad($arr[3], 2, '0', STR_PAD_LEFT);
    // 2. 이름
    $name = $arr[4];
    // 3. 주민번호
    $reginum = $arr[6];
    // 4. 성별
    $sex = $arr[5];
    // 5. 우편번호
    $zipcode = str_pad($arr[7], 5, '0', STR_PAD_LEFT);
    // 6. 주소
    $addr = $arr[8];

    $human_info = [
         "enter_date" => $enter_date
        ,"name"       => $name
        ,"reginum"    => $reginum
        ,"sex"        => $sex
        ,"zipcode"    => $zipcode
        ,"addr"       => $addr
    ];

    $conn->debug = 1;
    $dao->insertEmplNum($conn, $empl_info);

    $key = $conn->insert_ID();
    $human_info["empl_seqno"] = $key;
    $dao->insertEmplHumanInfo($conn, $human_info);
}

$conn->Close();


