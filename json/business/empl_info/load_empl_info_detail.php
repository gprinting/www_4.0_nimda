<?php
/*
 * Copyright (c) 2017 Nexmotion, Inc.
 * All rights reserved.
 *
 * 직원관리 수정 데이터 불러오기
 *
 * REVISION HISTORY (reverse chronological order)
 *=============================================================================
 * 2017/09/29 이청산 생성
 *=============================================================================
 */
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/common_lib/CommonUtil.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/business/empl_info/EmplInfoDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new EmplInfoDAO();
$util = new CommonUtil();

$fb = $fb->getForm();

//$conn->debug = 1;

$seqno = $fb["seqno"];

$param = array();
$param["seqno"]  = $seqno;

$rs = $dao->selectEmplDetail($conn, $param);
$fields = $rs->fields;

$json  = '{';
$json .=  "\"empl_num\"     : \"%s\"";
$json .=  ",\"name\"         : \"%s\"";
$json .=  ",\"depar_code\"   : \"%s\"";
$json .=  ",\"posi_code\"    : \"%s\"";
$json .=  ",\"mail\"         : \"%s\"";
$json .=  ",\"tel_num\"      : \"%s\"";
$json .=  ",\"exten_num\"    : \"%s\"";
$json .=  ",\"resign_yn\"    : \"%s\"";
$json .=  ",\"admin_auth\"   : \"%s\"";
$json .= '}';

echo sprintf($json, $fields["empl_num"]
                  , $fieles["name"]
                  , $fieles["depar_code"]
                  , $fieles["posi_code"]
                  , $fieles["mail"]
                  , $fieles["tel_num"]
                  , $fieles["exten_num"]
                  , $fieles["resign_yn"]
                  , $fieles["admin_auth"]);

$conn->Close();

?>
