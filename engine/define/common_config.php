<?
/******************** 파일 저장 경로 ****************/
define("SITE_NET_DRIVE", "/"); //사이트 기본정보
define("SITE_DEFAULT_ATTACH", "/attach/gp"); //사이트 기본정보
define("SITE_DEFAULT_ESTI_FILE", SITE_DEFAULT_ATTACH . "/esti_file"); //견적 파일
define("SITE_DEFAULT_AFTER_OP_WORK_FILE", SITE_DEFAULT_ATTACH . "/after_op_work_file"); //후공정 발주 작업 파일
define("SITE_DEFAULT_ORDER_FILE", SITE_DEFAULT_ATTACH . "/order_file"); //주문 파일
define("SITE_DEFAULT_ORDER_DETAIL_COUNT_FILE", SITE_DEFAULT_ATTACH . "/order_detail_count_file"); //주문 건수 상세 작업 파일
define("SITE_DEFAULT_ORDER_DETAIL_COUNT_TMP_FILE", SITE_DEFAULT_ATTACH . "/order_detail_count_tmp_file"); //주문 건수 상세 작업 임시 파일
define("SITE_DEFAULT_ORDER_DETAIL_COUNT_PREVIEW_FILE", SITE_DEFAULT_ATTACH . "/order_detail_count_preview_file"); //주문 건수 상세 미리보기 파일
define("SITE_DEFAULT_CLAIM_SAMPLE_FILE", SITE_DEFAULT_ATTACH . "/claim_sample_file"); //클레임 견본 파일
define("SITE_DEFAULT_OTO_INQ_REPLY_FILE", SITE_DEFAULT_ATTACH . "/oto_inq_reply_file"); //1:1문의 답변 파일
define("SITE_DEFAULT_POINT_FILE", SITE_DEFAULT_ATTACH . "/point_file"); //포인트 파일 저장
define("SITE_DEFAULT_MEMBER_CERTI_FILE", SITE_DEFAULT_ATTACH . "/member_certi_file"); //회원 인증정보
define("SITE_DEFAULT_CATE_PHOTO_FILE", SITE_DEFAULT_ATTACH . "/cate_photo_file"); //카테고리 사진 파일
define("SITE_DEFAULT_CATE_BANNER_FILE", SITE_DEFAULT_ATTACH . "/cate_banner_file"); //카테고리배너 파일
define("SITE_DEFAULT_CATE_TEMPLATE_FILE", SITE_DEFAULT_ATTACH . "/cate_template_file"); //카테고리 템플릿 파일
define("SITE_DEFAULT_POPUP_FILE", SITE_DEFAULT_ATTACH . "/popup_file"); //팝업 파일
define("SITE_DEFAULT_BANNER_FILE", SITE_DEFAULT_ATTACH . "/banner_file"); //메인베너 파일
define("SITE_DEFAULT_NOTICE_FILE", SITE_DEFAULT_ATTACH . "/notice_file"); //공지사항 파일
define("SITE_DEFAULT_NOTICE_IMG_FILE", SITE_DEFAULT_ATTACH . "/notice_file/editor"); //공지사항 에디터 이미지 파일
define("SITE_DEFAULT_SHEET_TYPSET_FILE", "typeset/sheet_typset_file"); //낱장조판파일
define("SITE_DEFAULT_BROCHURE_TYPSET_FILE", "typeset/brochure_typset_file"); //책자조판파일
define("SITE_DEFAULT_PAPER_PREVIEW_FILE", SITE_DEFAULT_ATTACH . "/paper_preview_file"); //재질 미리보기 파일
define("SITE_DEFAULT_RECEIPT_WORK_FILE", SITE_DEFAULT_ATTACH . "/receipt_work_file"); //책자조판_지시서공정
define("SITE_DEFAULT_RECEIPT_OP_WORK_FILE", SITE_DEFAULT_ATTACH . "/op_receipt_work_file"); //책자조판_지시서공정_후공정
define("NO_IMAGE", "/design_template/images/no_image.jpg");
define("TEMPLATE_POPUP", $_SERVER["SiteHome"] ."/front/ajax/product/template_pop"); // 카테고리별 템플릿 다운로드 팝업 파일 저장 경로

define("OEVENT_HTML",    $_SERVER["SiteHome"] ."/front/design_template/common/oEvent.html");
define("NOWADAYS_HTML",  $_SERVER["SiteHome"] ."/front/design_template/common/nowADays.html");
define("NOTICE_HTML",    $_SERVER["SiteHome"] ."/front/design_template/common/notice.html");
define("M_NOTICE_HTML",  $_SERVER["SiteHome"] ."/front/design_template/m/common/notice.html");
define("SHARE_LIBRARY_FILE", SITE_DEFAULT_ATTACH . "/share_library_file"); //책자조판_지시서공정
define("_CYP_FILE_ENZINE_OUTPUT",  $_SERVER["SiteHome"] ."/nimda/typeset/CYPRESS/output");  //싸이프레스 미리보기, PDF 경로
define("EXCEL_TEMPLATE", "/excel_template/"); // 엑셀 다운로드용 템플릿 저장

define("SITE_DEFAULT_FONT_INPUT",   "font_input");   //아포지 작업 파일 - 서채 O
define("SITE_DEFAULT_APOGEE_INPUT", "apogee_input"); //아포지 작업 파일 - 서체 X
define("SITE_DEFAULT_ORDER_PREVIEW_FILE", "/preview"); //주문 시안(미리보기)파일
?>
