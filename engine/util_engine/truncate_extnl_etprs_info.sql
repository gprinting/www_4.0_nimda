SET FOREIGN_KEY_CHECKS = 0;
/** 외부_브랜드 연관 테이블 삭제 **/
/* 기본_생산_종이 */
TRUNCATE basic_produce_paper;
/* 종이 */
TRUNCATE paper;
/* 기본_생산_출력 */
TRUNCATE basic_produce_output;
/* 출력 */
TRUNCATE output;
/* 기본_생산_인쇄 */
TRUNCATE basic_produce_print;
/* 인쇄 */
TRUNCATE print;
/* 기본_생산_후공정 */
TRUNCATE basic_produce_after;
/* 후공정 */
TRUNCATE after;
/* 종이_발주 */
TRUNCATE paper_op;
/* 출력_작업_일지 */
TRUNCATE output_work_report;
/* 출력_발주 */
TRUNCATE output_op;
/* 인쇄_작업_일지 */
TRUNCATE print_work_report;
/* 인쇄_발주 */
TRUNCATE print_op;
/* 후공정_발주_작업_파일 */
TRUNCATE after_op_work_file;
/* 후공정_작업_일지 */
TRUNCATE after_work_report;
/* 후공정_발주 */
TRUNCATE after_op;
/* 외부_브랜드 */
TRUNCATE extnl_brand;

/** 외부_업체 연관 테이블 삭제 **/
/* 금전출납부 */
TRUNCATE cashbook;
/* 인쇄_생산_계획 */
TRUNCATE print_produce_sch;
/* 거래명세서 */
TRUNCATE dealspec;
/* 외부_업체_사업자등록증_정보 */
TRUNCATE extnl_etprs_bls_info;
/* 외부_업체_회원 */
TRUNCATE extnl_etprs_member;
/* 외부_담당자 */
TRUNCATE extnl_mng;
/* 주문_클레임 */
TRUNCATE order_claim;

/* 외부_업체 */
TRUNCATE extnl_etprs;
SET FOREIGN_KEY_CHECKS = 1;
