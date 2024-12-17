SET FOREIGN_KEY_CHECKS = 0;
/* 주문 후공정_작업_일지 */
TRUNCATE after_work_report;
/* 생산_지시서 */
TRUNCATE produce_ord;
/* 주문 후공정_발주_작업파일 */
TRUNCATE after_op_work_file;
/* 주문 후공정_발주 */
TRUNCATE after_op;
/* 조판 후공정_발주 */
TRUNCATE basic_after_op;
/* 조판 후공정_작업_일지 */
TRUNCATE basic_after_work_report;
/* 종이_발주 */
TRUNCATE paper_op;
/* 인쇄_작업_일지 */
TRUNCATE print_work_report;
/* 인쇄_발주 */
TRUNCATE print_op;
/* 출력_작업_일지 */
TRUNCATE output_work_report;
/* 출력_발주 */
TRUNCATE output_op;
/* 책자_조판_파일 */
TRUNCATE brochure_typset_file;
/* 페이지_주문_상세_책자 */
TRUNCATE page_order_detail_brochure;
/* 책자_조판 */
TRUNCATE brochure_typset;
/* 낱장_조판_미리보기_파일 */
TRUNCATE sheet_typset_preview_file;
/* 낱장_조판_파일 */
TRUNCATE sheet_typset_file;
/* 낱장_조판 */
TRUNCATE sheet_typset;
SET FOREIGN_KEY_CHECKS = 1;
