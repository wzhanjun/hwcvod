<?php
/**
 * 媒资取消发布
 */

require dirname(__DIR__).'/vendor/autoload.php';
require 'init.php';

use hwcvod\vod\client\VodConfig;
use hwcvod\vod\client\VodClient;
use hwcvod\vod\model\PublishAssetReq;

$vodConfig = new VodConfig();
$vodConfig->setAk(AK);
$vodConfig->setSk(SK);
$vodConfig->setProjectId(PID);
$vodClient = new VodClient($vodConfig);

$req = new PublishAssetReq();
$req ->setAssetId(array('55576e74bfd6d828e8c8d445500bf961'));

$rsp = $vodClient->unPublishAssets($req);
echo $rsp->getBody();