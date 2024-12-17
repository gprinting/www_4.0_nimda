<?php
/**
 * @file EngineCommon.php
 *
 * @brief 가격엑셀엔진에서 사용되는 공통함수 클래스
 */

class EngineCommon {
    function __construct() {
    }

    /**
     * @brief 파라미터로 넘어온 프로세스명이 존재하는지 1초 간격으로 확인<br/>
     *
     * @details 별다른 리턴값은 존재하지 않으며 해당 함수가 종료되면<br/>
     * 모든 프로세스가 종료된 것으로 간주됨<br/>
     * vi 등으로 해당 엔진파일을 열고 있을 경우<br/>
     * 제대로 동작하지 않을 수 있음
     *
     * @param $engine_name = 동작을 확인할
     */
    function checkProcess($engine_name) {
        while (1) {
            // 프로세스가 존재하는지 검색하는 명령어
            $pp = popen("ps -ef | grep " . $engine_name, "r");

            // 프로세스 실행 결과값을 가지고 엔진 프로그램명이 존재하는지 확인
            $ps_count = 0;
            while (($buffer = fgets($pp, 4096)) !== false) {
                if (strpos($buffer, "vi") !== false) {
                    continue;
                }

                $strpos = strpos($buffer, $engine_name);

                $ps_count++;
            }

            pclose($pp);

            if ($ps_count === 2) {
                /*
                 * 쉘 실행 명령어와 grep 명령어만 남아있을 경우
                 * 모든 프로세스가 종료된걸로 판별하고 루프 탈출
                 */

                echo "$engine_name IS NOT RUN!\n";
                echo "------------------------------\n";
                break;
            }

            sleep(1);
        }
    }

    /**
     * @brief 넘어온 값이 공백/null, 
     * 배열일 경우 길이가 0일 경우 true 반환
     *
     * @param $val = 체크할 변수
     *
     * @return TRUE / FALSE
     */
    function checkBlank($val) {

        if (is_array($val)) {
            // 변수가 배열일 경우

            $val_count = count($val);

            if ($val_count === 0) return true;
            else return false;
        } else {
            if ($val !== "" && $val !== "''" && $val !== null) {
                return false;
            } else {
                return true;
            }
        }
    }

    /**
     * @brief 커넥션이 살아있는지 체크하는 함수
     *
     * @param $cp    = 커넥션 풀 객체
     * @param $conn  = 디비 커넥션
     *
     * @param 디비 커넥션
     */
    function checkDBConn($cp, $conn) {
        if ($conn) {
            $cp->reConnectDB();
            return $connectionPool->getPooledConnection();
        }

        $rs = $conn->Execute("SELECT 1");

        if ($rs) {
            return $connectionPool->getPooledConnection();
        }
    }

    /**
     * @brief 현재 일자에 해당하는 로그디렉토리가 있는지 확인
     *
     * @param $base_path = 기본경로
     * @param $today     = 오늘 연월일(2015-08-18)
     *
     * @return 존재할 경우 TRUE / 없으면 FALSE
     */
    function checkLogDir($base_path, $today) {
        $today = explode('-', $today);
        $path = sprintf("%s/log/%s/%s/%s", $base_path
                                         , $today[0]
                                         , $today[1]
                                         , $today[2]);

        if (is_dir($path)) {
			return true;
		} else {
			return false;	
		}
    }

    /**
     * @brief 로그디렉토리를 생성하는 함수
     *
     * @param $base_path = 기본경로
     * @param $today     = 오늘 연월일
     *
     * @return 생성에 성공하면 true / 실패하면 false
     */
    function makeLogDir($base_path, $today) {
        $today = explode('-', $today);
        $path = sprintf("%s/log/%s/%s/%s", $base_path
                                         , $today[0]
                                         , $today[1]
                                         , $today[2]);

        if (mkdir($path, 0777, true)) {
            //chown($path, "sitemgr");
            //chgrp($path, "dpgrp");
			return true;
		} else {
			return false;
		}
    }

    /**
     * @brief 현재 일자에 해당하는 일반디렉토리가 있는지 확인
     *
     * @param $base_path = 기본경로
     * @param $today     = 오늘 연월일(2018-07-18)
     *
     * @return 존재할 경우 TRUE / 없으면 FALSE
     */
    function checkNewDir($base_path, $fold_name, $today) {
        $today = explode('-', $today);
        $path = sprintf("%s/%s/%s/%s/%s", $base_path
                                        , $fold_name
                                        , $today[0]
                                        , $today[1]
                                        , $today[2]);

        if (is_dir($path)) {
			return true;
		} else {
			return false;	
		}
    }

    /**
     * @brief 일반디렉토리를 생성하는 함수
     *
     * @param $base_path = 기본경로
     * @param $fold_name = 폴더명
     * @param $today     = 오늘 연월일
     *
     * @return 생성에 성공하면 true / 실패하면 false
     */
    function makeNewDir($base_path, $fold_name, $today) {
        $today = explode('-', $today);
        $path = sprintf("%s/%s/%s/%s/%s", $base_path
                                        , $fold_name
                                        , $today[0]
                                        , $today[1]
                                        , $today[2]);

        if (mkdir($path, 0777, true)) {
            chown($path, "sitemgr");
            chgrp($path, "dpgrp");
			return true;
		} else {
			return false;
		}
    }

    /**
     * @brief 엔진 실행결과에 따라 boolean값 반환
     *
     * @param $ret_check = 엔진 실행결과
     *
     * @return 엔진 결과로그에 FAIL이 있을경우 false 반환, 아니면 true 반환
     */
    function checkFail($ret_check) {
        if (empty($ret_check) === true) {
            return false;
        }

        if (strpos($ret_check, "FAIL") === false) {
            return true;
        } else {
            return false;
        }
    }
}
?>
