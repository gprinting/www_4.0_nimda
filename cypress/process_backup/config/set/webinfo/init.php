<?
 /***********************************************************************************
 *** 프로 젝트 : CyPress
 *** 개발 영역 : 로그인
 *** 개  발  자 : 김성진
 *** 개발 날짜 : 2016.06.15
 ***********************************************************************************/

 /***********************************************************************************
 *** 초기 설정 영역
 ***********************************************************************************/

 ob_start();
 ini_set('error_reporting', E_ALL & ~E_NOTICE);
 ini_set('memory_limit', -1);

 set_time_limit(0);
 date_default_timezone_set('Asia/Seoul');


 /***********************************************************************************
 *** 기본 정의
 ***********************************************************************************/

 define("_URL", $_SERVER["HTTP_HOST"]);

 define("_CONF_DIR", $curDirectory);
 define("_SET_DIR", $curDirectory."/config/set");
 define("_LIB_DIR", $curDirectory."/config/lib");

 define("_DTB_DIR", _LIB_DIR."/database");
 define("_MOD_DIR", _LIB_DIR."/modules");
 define("_LOC_DIR", _LIB_DIR."/local");
 define("_PLG_DIR", _LIB_DIR."/plugin");

 define("_DBI_DIR", _SET_DIR."/dbinfo");
 define("_WBI_DIR", _SET_DIR."/webinfo");


 /***********************************************************************************
 *** DB 서버 선언
 ***********************************************************************************/

 define("_DB_SERVER", "web");


 /***********************************************************************************
 *** 테이블 정의 (웹 DB)
 ***********************************************************************************/

 define("_TBL_MEMBERS", "member");
 define("_TBL_CATE", "cate");
 define("_TBL_ORDER", "order_common");
 define("_TBL_ORDER_DETAIL", "order_detail");
 define("_TBL_ORDER_FILE", "order_file");
 define("_TBL_ORDER_DETAIL_COUNT_FILE", "order_detail_count_file");
 define("_TBL_CATE_PAPER", "cate_paper");
 define("_TBL_PRDT_PAPER", "prdt_paper");
 define("_TBL_CATE_PRINT", "cate_print");
 define("_TBL_PRDT_PRINT", "prdt_print");
 define("_TBL_CATE_STAN", "cate_stan");
 define("_TBL_PRDT_STAN", "prdt_stan");
 define("_TBL_ORDER_DELEVERY", "order_dlvr");
 define("_TBL_LICENSE_INFO", "licensee_info");
 define("_TBL_SHEET_TYPESET", "sheet_typset");
 define("_TBL_SHEET_TYPESET_FILE", "sheet_typset_file");
 define("_TBL_SHEET_TYPESET_PREVIEW_FILE", "sheet_typset_preview_file");
 define("_TBL_BROCHURE_TYPESET", "brochure_typset");
 define("_TBL_BROCHURE_TYPESET_FILE", "brochure_typset_file");
 define("_TBL_BROCHURE_TYPESET_PREVIEW_FILE", "brochure_typset_preview_file");
 define("_TBL_TYPSET_FORMAT", "typset_format");
 define("_TBL_ORDER_AFTER_HISTORY", "order_after_history");
 define("_TBL_AFTER", "after");
 define("_TBL_BASIC_PRODUCE_AFTER", "basic_produce_after");
 define("_TBL_OPT", "opt");
 define("_TBL_BASIC_PRODUCE_OPT", "basic_produce_opt");
 define("_TBL_PRINT", "print");
 define("_TBL_BASIC_PRODUCE_PRINT", "basic_produce_print");
 define("_TBL_PAPER", "paper");
 define("_TBL_BASIC_PRODUCE_PAPER", "basic_produce_paper");
 define("_TBL_EMPL", "empl");
 define("_TBL_OUTPUT_OP", "output_op");
 define("_TBL_OUTPUT", "output");
 define("_TBL_BASIC_PRODUCE_OUTPUT", "basic_produce_output");
 define("_TBL_EXTNL_ETPRS", "extnl_etprs");
 define("_TBL_EXTNL_BRAND", "extnl_brand");
 define("_TBL_PRINT_OP", "print_op");
 define("_TBL_PAPER_OP", "paper_op");
 define("_TBL_PRODUCE_PROCESS_FLOW", "produce_process_flow");
 define("_TBL_STATE_ADMIN", "state_admin");
 define("_TBL_AMT_ORDER_DETAIL_SHEET", "amt_order_detail_sheet");
 define("_TBL_BASIC_AFTER_OP", "basic_after_op");
 define("_TBL_ORDER_OPT_HISTORY", "order_opt_history");
 define("_TBL_PRODUCE_ORD", "produce_ord");


 /***********************************************************************************
 *** 공통 선언
 ***********************************************************************************/

 define("_CYP_SUCCESS", "0");
 define("_CYP_MON_UNIT", "원");
 define("_CYP_TYPESET_PAGE_DIV1", "낱장");
 define("_CYP_TYPESET_PAGE_DIV2", "책자");
 define("_CYP_TYPESET_AUTO_CONTRACT", "자동발주");
 define("_CYP_TYPESET_AUTO_CREATE", "자동생성");
 define("_CYP_AUTORER", "AUTO");


 /***********************************************************************************
 *** 공통 오류
 ***********************************************************************************/

 define("_CYP_COMM_ERR_CD_01", "11");
 define("_CYP_COMM_ERR_DC_01", "예기치 않은 쿼리 오류입니다.");
 define("_CYP_COMM_ERR_CD_02", "12");
 define("_CYP_COMM_ERR_DC_02", "예기치 않은 오류가 발생하였습니다. 관리자에게 문의하시길 바랍니다.");
 define("_CYP_COMM_ERR_CD_03", "13");
 define("_CYP_COMM_ERR_DC_03", "해당 데이터가 존재하지 않습니다.");


 /***********************************************************************************
 *** 로그인 오류
 ***********************************************************************************/

 define("_CYP_LOG_ERR_CD_01", "21");
 define("_CYP_LOG_ERR_DC_01", "아이디와 비밀번호가 존재하지 않습니다.");
 define("_CYP_LOG_ERR_CD_02", "22");
 define("_CYP_LOG_ERR_DC_02", "아이디가 존재하지 않습니다.");
 define("_CYP_LOG_ERR_CD_03", "23");
 define("_CYP_LOG_ERR_DC_03", "비밀번호가 존재하지 않습니다.");
 define("_CYP_LOG_ERR_CD_04", "24");
 define("_CYP_LOG_ERR_DC_04", "아이디가 잘못 되었습니다.");
 define("_CYP_LOG_ERR_CD_05", "25");
 define("_CYP_LOG_ERR_DC_05", "비밀번호가 잘못 되었습니다.");


 /***********************************************************************************
 *** 주문번호 오류
 ***********************************************************************************/

 define("_CYP_ORD_NUM_ERR_CD_01", "31");
 define("_CYP_ORD_NUM_ERR_DC_01", "주문번호가 존재하지 않습니다.");
 define("_CYP_ORD_NUM_ERR_CD_02", "32");
 define("_CYP_ORD_NUM_ERR_DC_02", "주문번호가 잘못 되었습니다.");
 define("_CYP_ORD_NUM_ERR_CD_03", "33");
 define("_CYP_ORD_NUM_ERR_DC_03", "주문번호 및 작업자 아이디가 잘못 되었습니다.");


 /***********************************************************************************
 *** 주문상태 1
 ***********************************************************************************/

 define("_CYP_STS_CD_READY", "2120");
 define("_CYP_STS_CD_GOING", "2130");
 define("_CYP_STS_CD_MISS", "2170");
 define("_CYP_STS_CD_COMP", "2210");
 define("_CYP_STS_CD_OUTPUT", "2220");
 define("_CYP_STS_CD_PRINT", "2310");
 define("_CYP_STS_CD_AFTER", "2410");

 /*$ordStateCode['110'] = "주문대기";
 $ordStateCode['120'] = "주문취소";
 $ordStateCode['210'] = "입금대기";
 $ordStateCode['310'] = "접수대기";
 $ordStateCode['320'] = "접수중";
 $ordStateCode['330'] = "접수시안확인";
 $ordStateCode['340'] = "접수보류";
 $ordStateCode['410'] = "조판대기";
 $ordStateCode['420'] = "조판중";
 $ordStateCode['430'] = "조판누락";
 $ordStateCode['510'] = "종이발주대기";
 $ordStateCode['520'] = "종이발주완료";
 $ordStateCode['530'] = "종이발주취소";
 $ordStateCode['605'] = "출력준비";
 $ordStateCode['605'] = "출력대기";
 $ordStateCode['605'] = "출력중";
 $ordStateCode['705'] = "인쇄준비";
 $ordStateCode['710'] = "인쇄대기";
 $ordStateCode['720'] = "인쇄중";
 $ordStateCode['805'] = "후공정준비";
 $ordStateCode['810'] = "후공정대기";
 $ordStateCode['820'] = "후공정중";
 $ordStateCode['910'] = "입고대기";
 $ordStateCode['920'] = "입고중";
 $ordStateCode['950'] = "출고대기";
 $ordStateCode['960'] = "출고중";
 $ordStateCode['010'] = "배송대기";
 $ordStateCode['011'] = "배송중";
 $ordStateCode['020'] = "구매확정대기";
 $ordStateCode['021'] = "구매확정완료";/*


 /***********************************************************************************
 *** 면수
 ***********************************************************************************/

 $ordSide['단면'] = "1";
 $ordSide['양면'] = "2";
 $ordSide['양면도무송'] = "3";


 /***********************************************************************************
 *** 면수
 ***********************************************************************************/

 $ordDelyWay['01'] = "택배";
 $ordDelyWay['02'] = "직배";
 $ordDelyWay['03'] = "화물";
 $ordDelyWay['04'] = "퀵";
 $ordDelyWay['05'] = "지하철";


 /***********************************************************************************
 *** 판등록 (추가) 오류
 ***********************************************************************************/

 define("_CYP_ORD_PEN_ERR_CD_01", "41");
 define("_CYP_ORD_PEN_ERR_DC_01", "판번호 또는 호수판번호가 존재하지 않습니다.");
 define("_CYP_ORD_PEN_ERR_CD_02", "42");
 define("_CYP_ORD_PEN_ERR_DC_02", "이미 등록되어 있는 판번호 입니다.");

 define("_CYP_ORD_PENO_ERR_CD_01", "51");
 define("_CYP_ORD_PENO_ERR_DC_01", "판 생성시간이 존재하지 않습니다.");
 define("_CYP_ORD_PENO_ERR_CD_02", "52");
 define("_CYP_ORD_PENO_ERR_DC_02", "작업자 아이디가 존재하지 않습니다.");
 define("_CYP_ORD_PENO_ERR_CD_03", "53");
 define("_CYP_ORD_PENO_ERR_DC_03", "판형 이름이 존재하지 않습니다.");
 define("_CYP_ORD_PENO_ERR_CD_04", "54");
 define("_CYP_ORD_PENO_ERR_DC_04", "판사이즈 이름이 존재하지 않습니다.");
 define("_CYP_ORD_PENO_ERR_CD_05", "55");
 define("_CYP_ORD_PENO_ERR_DC_05", "판사이즈 가로가 존재하지 않습니다.");
 define("_CYP_ORD_PENO_ERR_CD_06", "56");
 define("_CYP_ORD_PENO_ERR_DC_06", "판사이즈 세로가 존재하지 않습니다.");
 define("_CYP_ORD_PENO_ERR_CD_07", "57");
 define("_CYP_ORD_PENO_ERR_DC_07", "대첩방식이 존재하지 않습니다.");
 define("_CYP_ORD_PENO_ERR_CD_08", "58");
 define("_CYP_ORD_PENO_ERR_DC_08", "재질이 존재하지 않습니다.");
 define("_CYP_ORD_PENO_ERR_CD_09", "59");
 define("_CYP_ORD_PENO_ERR_DC_09", "판매수가 존재하지 않습니다.");
 define("_CYP_ORD_PENO_ERR_CD_10", "60");
 define("_CYP_ORD_PENO_ERR_DC_10", "판 면수가 존재하지 않습니다.");
 define("_CYP_ORD_PENO_ERR_CD_11", "61");
 define("_CYP_ORD_PENO_ERR_DC_11", "후공정 수가 존재하지 않습니다.");
 define("_CYP_ORD_PENO_ERR_CD_12", "62");
 define("_CYP_ORD_PENO_ERR_DC_12", "총주문 개수가 존재하지 않습니다.");
 define("_CYP_ORD_PENO_ERR_CD_13", "63");
 define("_CYP_ORD_PENO_ERR_DC_13", "파일폴더 위치가 존재하지 않습니다.");
define("_CYP_ORD_PENO_ERR_CD_14", "64");
define("_CYP_ORD_PENO_ERR_DC_14", "프리셋 카테고리가 존재하지 않습니다.");
define("_CYP_ORD_PENO_ERR_CD_15", "65");
define("_CYP_ORD_PENO_ERR_DC_15", "프리셋 이름이 존재하지 않습니다.");


/***********************************************************************************
 *** 프리셋 오류
 ***********************************************************************************/

define("_CYP_PRES_NUM_ERR_CD_01", "71");
define("_CYP_PRES_NUM_ERR_DC_01", "프리셋 카테고리 이름이 존재하지 않습니다.");
define("_CYP_PRES_NUM_ERR_CD_02", "72");
define("_CYP_PRES_NUM_ERR_DC_02", "프리셋 이름이 존재하지 않습니다.");
define("_CYP_PRES_NUM_ERR_CD_03", "73");
define("_CYP_PRES_NUM_ERR_DC_03", "작업시간이 존재하지 않습니다.");
define("_CYP_PRES_NUM_ERR_CD_04", "74");
define("_CYP_PRES_NUM_ERR_DC_04", "작업자 아이디가 존재하지 않습니다.");
define("_CYP_PRES_NUM_ERR_CD_05", "75");
define("_CYP_PRES_NUM_ERR_DC_05", "판형 이름이 존재하지 않습니다.");
define("_CYP_PRES_NUM_ERR_CD_06", "76");
define("_CYP_PRES_NUM_ERR_DC_06", "대지사이즈 이름이 존재하지 않습니다.");
define("_CYP_PRES_NUM_ERR_CD_07", "77");
define("_CYP_PRES_NUM_ERR_DC_07", "대지사이즈 가로가 존재하지 않습니다.");
define("_CYP_PRES_NUM_ERR_CD_08", "78");
define("_CYP_PRES_NUM_ERR_DC_08", "대지사이즈 세로가 존재하지 않습니다.");
define("_CYP_PRES_NUM_ERR_CD_09", "79");
define("_CYP_PRES_NUM_ERR_DC_09", "재질이 존재하지 않습니다.");


/***********************************************************************************
 *** 분할
 ***********************************************************************************/

define("_CYP_QTY_NUM_ERR_CD_01", "81");
define("_CYP_QTY_NUM_ERR_DC_01", "주문번호가 존재하지 않습니다.");
define("_CYP_QTY_NUM_ERR_CD_02", "82");
define("_CYP_QTY_NUM_ERR_DC_02", "분할매수가 존재하지 않습니다.");
define("_CYP_QTY_NUM_ERR_CD_03", "83");
define("_CYP_QTY_NUM_ERR_DC_03", "분할할 데이터가 존재하지 않습니다.");


/***********************************************************************************
 *** 판형오류
 ***********************************************************************************/

define("_CYP_TSF_NUM_ERR_CD_01", "101");
define("_CYP_TSF_NUM_ERR_DC_01", "판형 데이터가 존재하지 않습니다.");
define("_CYP_TSF_NUM_ERR_CD_02", "102");
define("_CYP_TSF_NUM_ERR_DC_02", "판형 쿼리가 오류입니다.");
define("_CYP_TSF_NUM_ERR_CD_03", "103");
define("_CYP_TSF_NUM_ERR_DC_03", "조판 완료된 상태에서는 삭제하실 수 없습니다.");
define("_CYP_TSF_NUM_ERR_CD_04", "104");
define("_CYP_TSF_NUM_ERR_DC_04", "조판 완료된 상태에서는 다시 판등록을 하실 수 없습니다.");


/***********************************************************************************
 *** 공유폴더
 ***********************************************************************************/

define("_CYP_DIR_NUM_ERR_CD_01", "201");
define("_CYP_DIR_NUM_ERR_DC_01", "OUTPUT 폴더에 접근할 수 없습니다.");
define("_CYP_DIR_NUM_ERR_CD_02", "202");
define("_CYP_DIR_NUM_ERR_DC_02", "파일 리스트를 불러올 수 없습니다.");


/***********************************************************************************
 *** 파일엔진 폴더
 ***********************************************************************************/

define("_CYP_FILE_ENZINE_INPUT", "/home/dprinting/nimda/typeset/CYPRESS/input");
define("_CYP_FILE_ENZINE_OUTPUT", "/home/dprinting/nimda/typeset/CYPRESS/output");
define("_WEB_FILE_ENZINE_PREVIEW", "/home/dprinting/nimda/typeset/typset_preview_file");


/***********************************************************************************
 *** 후공정 구분
 ***********************************************************************************/

define("_CYP_OPT_NAME_TODAY", "당일판");
define("_CYP_OPT_NAME_EMERG", "긴급");
define("_CYP_OPT_NAME_JUNGM", "정매");
define("_CYP_OPT_NAME_SAMPL", "견본");
define("_CYP_OPT_NAME_AUDIT", "감리");
define("_CYP_OPT_NAME_FELL", "베다");
define("_CYP_OPT_NAME_ACCIT", "사고");
define("_CYP_OPT_NAME_CARE", "재단주의");
define("_CYP_OPT_NAME_NORM", "일반");

?>
