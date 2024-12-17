<?
/*
 * Copyright (c) 2017 Nexmotion, Inc.
 * All rights reserved.
 *
 * 회원 사내닉네임으로 검색 후 json 생성해서 반환
 *
 * REVISION HISTORY (reverse chronological order)
 *=============================================================================
 * 2017/05/01 엄준현 생성
 *=============================================================================
 */
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/business/order_mng/OrderMngDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new OrderMngDAO();

$fb = $fb->getForm();

$office_nick = urldecode($fb["office_nick"]);

$param = array();
$param["office_nick"] = $office_nick;

$rs = $dao->selectMemberByOfficeNick($conn, $param);

$json = '[';
$val_form = "{\"seqno\" : \"%s\", \"nick\" : \"%s\"}";
while ($rs && !$rs->EOF) {
    $fields = $rs->fields;

    $json .= sprintf($val_form, $fields["member_seqno"]
                              , $fields["office_nick"]);

    $rs->MoveNext();
    if (!$rs->EOF) {
        $json .= ',';
    }
}
$json .= ']';

echo $json;
$conn->Close();
?>
