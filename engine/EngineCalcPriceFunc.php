<?
/**
 * @file EngineCalcPriceFunc.php
 *
 * @brief 판매가격 엑셀 업로드시 작업을 수행하는 클래스
 */

include_once(dirname(__FILE__) . '/common/EngineFuncInterface.php');
include_once(dirname(__FILE__) . '/common/EngineCommon.php');
include_once(dirname(__FILE__) . '/dao/EngineDAO.php');
include_once(dirname(__FILE__) . '/define/common.php');

class EngineCalcPriceFunc implements EngineFuncInterface {

    function __construct() {
    }

    function execute($param) {
        $param = explode('!', $param);

        $util = new EngineCommon();

        $base_path = dirname(__FILE__);

        $engine_path = sprintf("%s/proc_engine/calc/%s.php", $base_path
                                                           , "CalcPriceEngine");

        $command = sprintf("%s %s %s %s > %s &", $engine_path
                                               , $base_path
                                               , $param[1]
                                               , $param[2]
                                               , REDIR_PATH);
        system($command);

        $util->checkProcess("CalcPriceEngine");

        $fp = fopen($base_path . "/log/CalcPrice.log", "r");

        $ret_check = "";

        while (($buffer = fgets($fp, 512)) !== false) {
            $ret_check .= $buffer;
        }

        fclose($fp);

        return $util->checkFail($ret_check);
    }
}
?>
