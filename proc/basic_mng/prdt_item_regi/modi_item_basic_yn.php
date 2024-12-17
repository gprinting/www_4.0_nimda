<?php
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . '/com/nexmotion/common/util/ConnectionPool.inc');
include_once(INC_PATH . '/com/nexmotion/common/entity/FormBean.inc');
include_once(INC_PATH . '/com/nexmotion/job/nimda/basic_mng/prdt_mng/PrdtItemRegiDAO.inc');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new PrdtItemRegiDAO();

$fb = $fb->getForm();

$dvs           = $fb["dvs"];
$seqno         = $fb["seqno"];
$yn            = $fb["yn"];
$cate_sortcode = $fb["cate_sortcode"];

$param = [];
$param["cate_sortcode"] = $cate_sortcode;
$param["basic_yn"]      = $yn;
$param["seqno"]         = $seqno;

if (!$dao->updatePrdtItemBasicYn($conn, $param, $dvs)) {
    echo "기본아이템 등록에 실패했습니다.";
}

$conn->Close();

