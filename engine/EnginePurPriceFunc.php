<?
/**
 * @file EnginePurPriceFunc.php
 *
 * @brief 매입가격 엑셀 업로드시 작업을 수행하는 클래스
 */

include_once(dirname(__FILE__) . '/common/EngineFuncInterface.php');
include_once(dirname(__FILE__) . '/common/EngineCommon.php');
include_once(dirname(__FILE__) . '/dao/EngineDAO.php');
include_once(dirname(__FILE__) . '/define/common.php');

class EnginePurPriceFunc implements EngineFuncInterface {

    function __construct() {
    }

    /**
     * @brief 파라미터를 받아서 해당하는 생산 항목의 가격을 입력하는 함수
     * 
     * @param $param = 정보 파라미터
     *
     * @return 작업실행 성공여부
     */
    function execute($param) {
        $ret = false;

        $param = explode('!', $param);

        $dvs = $param[0];
        $excel_path = sprintf("%s/%s", $param[1], $param[2]);

		switch ($dvs) {
			case "PAPER":
				$ret = $this->paperPurPriceFunc($excel_path);
				break;
			case "OUTPUT":
				$ret = $this->outputPurPriceFunc($excel_path);
				break;
			case "PRINT":
				$ret = $this->printPurPriceFunc($excel_path);
				break;
			case "AFTER":
				$ret = $this->afterPurPriceFunc($excel_path);
				break;
			case "OPTION":
				$ret = $this->optPurPriceFunc($excel_path);
				break;
		}

        return $ret;
    }

    /**
     * @brief 업로드한 엑셀이 종이 매입가격일 때 실행하는 함수
     * 
     * @details 엔진으로 넘기는 파라미터로는<br/>
     * '엑셀파일 경로 / 기본경로'가 있다
     * 
     * @param $excel_path = 엑셀파일 경로
     * 
     * @return 작업실행 성공여부
     */
    function paperPurPriceFunc($excel_path) {
        $util = new EngineCommon();
        $base_path = dirname(__FILE__);

        $engine_path = sprintf("%s/proc_engine/paper/%s.php", $base_path
                                                            , "PaperPurPriceEngine");

        $command = sprintf("%s %s %s > %s &", $engine_path
                                            , $excel_path
                                            , $base_path
                                            , REDIR_PATH);
        system($command);

        $util->checkProcess("PaperPurPriceEngine");

        $fp = fopen($base_path . "/log/PaperPurPrice.log", "r");

        $ret_check = "";

        while (($buffer = fgets($fp, 512)) !== false) {
            $ret_check .= $buffer;
        }

        fclose($fp);

        return $util->checkFail($ret_check);
    }

    /**
     * @brief 업로드한 엑셀이 출력 매입가격일 때 실행하는 함수
     * 
     * @details 엔진으로 넘기는 파라미터로는<br/>
     * '엑셀파일 경로 / 기본경로'가 있다
     * 
     * @param $excel_path = 엑셀파일 경로
     * 
     * @return 작업실행 성공여부
     */
    function outputPurPriceFunc($excel_path) {
        $util = new EngineCommon();
        $base_path = dirname(__FILE__);

        $engine_path = sprintf("%s/proc_engine/output/%s.php", $base_path
                                                             , "OutputPurPriceEngine");

        $command = sprintf("%s %s %s > %s &", $engine_path
                                            , $excel_path
                                            , $base_path
                                            , REDIR_PATH);
        system($command);

        $util->checkProcess("OutputPurPriceEngine");

        $fp = fopen($base_path . "/log/OutputPurPrice.log", "r");

        $ret_check = "";

        while (($buffer = fgets($fp, 512)) !== false) {
            $ret_check .= $buffer;
        }

        fclose($fp);

        return $util->checkFail($ret_check);
    }

    /**
     * @brief 업로드한 엑셀이 인쇄 매입가격일 때 실행하는 함수
     * 
     * @details 엔진으로 넘기는 파라미터로는<br/>
     * '엑셀파일 경로 / 기본경로'가 있다
     * 
     * @param $excel_path = 엑셀파일 경로
     * 
     * @return 작업실행 성공여부
     */
    function printPurPriceFunc($excel_path) {
        $util = new EngineCommon();
        $base_path = dirname(__FILE__);

        $engine_path = sprintf("%s/proc_engine/print/%s.php", $base_path
                                                            , "PrintPurPriceEngine");

        $command = sprintf("%s %s %s > %s &", $engine_path
                                            , $excel_path
                                            , $base_path
                                            , REDIR_PATH);
        system($command);

        $util->checkProcess("PrintPriceEngine");

        $fp = fopen($base_path . "/log/PrintPurPrice.log", "r");

        $ret_check = "";

        while (($buffer = fgets($fp, 512)) !== false) {
            $ret_check .= $buffer;
        }

        fclose($fp);

        return $util->checkFail($ret_check);
    }

    /**
     * @brief 업로드한 엑셀이 후공정 매입가격일 때 실행하는 함수
     * 
     * @details 엔진으로 넘기는 파라미터로는<br/>
     * '엑셀파일 경로 / 기본경로'가 있다
     * 
     * @param $excel_path = 엑셀파일 경로
     * 
     * @return 작업실행 성공여부
     */
    function afterPurPriceFunc($excel_path) {
        $util = new EngineCommon();
        $base_path = dirname(__FILE__);

        $engine_path = sprintf("%s/proc_engine/after/%s.php", $base_path
                                                            , "AfterPurPriceEngine");

        $command = sprintf("%s %s %s > %s &", $engine_path
                                            , $excel_path
                                            , $base_path
                                            , REDIR_PATH);
        system($command);

        $util->checkProcess("AfterPrintPriceEngine");

        $fp = fopen($base_path . "/log/AfterPurPrice.log", "r");

        $ret_check = "";

        while (($buffer = fgets($fp, 512)) !== false) {
            $ret_check .= $buffer;
        }

        fclose($fp);

        return $util->checkFail($ret_check);
    }

    /**
     * @brief 업로드한 엑셀이 옵션 매입가격일 때 실행하는 함수
     * 
     * @details 엔진으로 넘기는 파라미터로는<br/>
     * '엑셀파일 경로 / 기본경로'가 있다
     * 
     * @param $excel_path = 엑셀파일 경로
     * 
     * @return 작업실행 성공여부
     */
    function optPurPriceFunc($excel_path) {
        $util = new EngineCommon();
        $base_path = dirname(__FILE__);

        $engine_path = sprintf("%s/proc_engine/option/%s.php", $base_path
                                                             , "OptPurPriceEngine");

        $command = sprintf("%s %s %s > %s &", $engine_path
                                            , $excel_path
                                            , $base_path
                                            , REDIR_PATH);
        system($command);

        $util->checkProcess("OptPrintPriceEngine");

        $fp = fopen($base_path . "/log/OptPurPrice.log", "r");

        $ret_check = "";

        while (($buffer = fgets($fp, 512)) !== false) {
            $ret_check .= $buffer;
        }

        fclose($fp);

        return $util->checkFail($ret_check);
    }
}
?>
