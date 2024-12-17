#! /usr/bin/php -f
<?php
/**
 * @file price_engine.php
 *
 * @brief 가격 관련 작업을 수행하는 엔진이다<br/>
 * 모든 작업은 순차적으로 실행되며<br/>
 * 작업을 가져오기 위해 감시하는 테이블은 엔진_큐 테이블이다<br/>
 * 
 * @details 가격 관련 동작은 아래와 같다<br/>
 * <br/>
 * - 판매가격(SELL_PRICE) 관련 동작(상품 정보관련)<br/>
 *   * 상품 등록/수정 - 상품 가격등록/수정 - 상품 기본 가격등록/수정<br/>
 *                                         - 상품 후공정 가격등록/수정<br/>
 *                                         - 상품 옵션 가격등록/수정<br/>
 *   * 표준가격관리 - *<br/>
 *   * 이 작업에 해당하는 파라미터 형식은 다음과 같다<br/>
 *     - 기본가격<br/>
 *       + PLY!파일저장경로!파일명!판매채널!업체구분<br/>
 *     - 후공정/옵션<br/>
 *       + 생산구분!파일저장경로!파일명<br/>
 * <br/>
 * - 매입가격(PUR_PRICE) 관련 동작(생산 정보관련)<br/>
 *   * 종이 관리 - 종이 업체/가격등록/수정<br/>
 *   * 출력 관리 - 출력 업체/가격등록/수정<br/>
 *   * 인쇄 관리 - 인쇄 업체/가격등록/수정<br/>
 *   * 후공정 관리 - 후공정 업체/가격등록/수정<br/>
 *   * 옵션 관리 - 옵션 업체/가격등록/수정<br/>
 *   * 이 작업에 해당하는 파라미터 형식은 다음과 같다<br/>
 *     + 생산구분!파일저장경로!파일명<br/>
 * <br/>
 * - 계산형가격(CALC_PRICE) 관련 동작<br/>
 *   * 파라미터에 카테고리 분류코드가 없을 경우 계산형 가격 카테고리 전체 검색
 *     + 카테고리 분류코드가 없는경우 : 계산형 가격 변경시, 상품 기초등록 변경시
 *     + 카테고리 분류코드가 있는경우 : 상품구성아이템 변경시
 *   * 카테고리에 해당하는 정보들 검색
 *   * 정보 검색 후 가격 계산 후 입력 or 수정
 *   * 이 작업에 해당하는 파라미터 형식은 다음과 같다<br/>
 *     + 생산구분!판매채널![카테고리 분류코드]<br/>
 */

//*************** 프로세스 종료시 처리부분
declare(ticks=1);

function termProc() {
    echo "Kill PROCESS\n";
    @unlink(dirname(__FILE__) . "/temp/price_engine.pid");
    exit;
}
pcntl_signal(SIGINT , "termProc");
pcntl_signal(SIGTERM, "termProc");
//*************** 프로세스 종료시 처리부분

//*************** 프로세스 중복실행 방지부분


$pid_fd = fopen(dirname(__FILE__) . "/temp/price_engine.pid", 'w');
fwrite($pid_fd, getmypid());
fclose($pid_fd);
/*
*/
//*************** 프로세스 중복실행 방지부분

include_once(dirname(__FILE__) . '/common/ConnectionPool.php');
include_once(dirname(__FILE__) . '/dao/EngineDAO.php');
include_once(dirname(__FILE__) . '/common/EngineCommon.php');
include_once(dirname(__FILE__) . '/EngineCommonFunc.php');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$engineDAO = new EngineDAO();
$util = new EngineCommon();
$commonFunc = new EngineCommonFunc();

$base_path = dirname(__FILE__);

$log_path = "";
$log_str = "[%s] %s %s %s\r\n";

$fp = null;

while(1) {

    $date = date("Y-m-d");

    if (!$util->checkLogDir($base_path, $date)) {
        if ($fp !== NULL) {
            fclose($fp);
        }

        if ($util->makeLogDir($base_path, $date)) {
            $date = explode('-', $date);
            $log_path = sprintf("%s/log/%s/%s/%s/engine_log.log", $base_path
                                                                , $date[0]
                                                                , $date[1]
                                                                , $date[2]);
            $fp = fopen($log_path, "w+");
            chown($log_path, "root");
            chgrp($log_path, "root");

            if (!$fp) goto FATAL_ERR;

            $log = sprintf($log_str, "LOG"
                                   , date("Y-m-d G:i:s")
                                   , "mkdir"
                                   , "SUCCESS");
            fwrite($fp, $log);
        } else {
            goto FATAL_ERR;
        }
    } else if (!$fp) {
        $date = explode('-', $date);
        $log_path = sprintf("%s/log/%s/%s/%s/engine_log.log", $base_path
                                                            , $date[0]
                                                            , $date[1]
                                                            , $date[2]);
        $fp = fopen($log_path, "w+");

        if (!$fp) goto FATAL_ERR;

            $log = sprintf($log_str, "LOG"
                                   , date("Y-m-d G:i:s")
                                   , "Log File Open"
                                   , "SUCCESS");
            fwrite($fp, $log);
    }

    $rs = $engineDAO->selectStayWork($conn);

    if ($rs->RecordCount() === 0 || $rs->RecordCount() === false) {
        // 작업이 없는 경우

        echo "STAY WORK IS NOTHING\n";
        exit;

        sleep(3);
        continue;
    } else {

        echo "뭔가 들어옴\n";

        $dvs = $rs->fields["dvs"];

        $seqno = $rs->fields["engine_que_seqno"];
        $param = $rs->fields["param"];

        $work_name = explode('!', $param)[0];

        echo "일 분배 시작\n";
        $ret = $commonFunc->execute($dvs, $param);
        /////////////////////////////////////////////////
        if ($ret === true) {
            echo "ret 있음\n";
            $log = sprintf($log_str, $dvs
                                   , date("Y-m-d G:i:s")
                                   , $work_name
                                   , "SUCCESS");
            fwrite($fp, $log);
            $engineDAO->updateState($conn, $seqno, "SUCCESS");
        } else {
            echo "ret 없음\n";
            $log = sprintf($log_str, $dvs
                                   , date("Y-m-d G:i:s")
                                   , $work_name
                                   , $commonFunc->getErrMsg());
            fwrite($fp, $log);
            $engineDAO->updateState($conn, $seqno, "FAIL");
        }
        /*
         */
    }

    sleep(3);

}

FATAL_ERR:
    fclose($fp);
    $err_fp = fopen(dirname(__FILE__) . "/[fatal_err]price_excel_engine.log", "w+");
    fwrite($err_fp, date("Y-m-d") . "[ERR] Log Dir OR Log File Make Fail");
    fclose($err_fp);
?>
