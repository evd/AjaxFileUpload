<?php
/**
 * AjaxFileUpload Connector
 *
 * @package ajaxfileupload
 */
DEFINE('MODX_API_MODE', true);
require_once dirname(dirname(dirname(dirname(__FILE__)))).'/config.core.php';
require_once MODX_CORE_PATH.'config/'.MODX_CONFIG_KEY.'.inc.php';
require_once MODX_BASE_PATH.'index.php';

$processorsPath = $modx->getOption('ajaxfileupload.core_path',null,$modx->getOption('core_path').'components/ajaxfileupload/').'processors/';

$modx->getService('error','error.modError');
$modx->setLogLevel(modX::LOG_LEVEL_ERROR);
$modx->setLogTarget('FILE');

$modx->error->message = null;

/* handle request */
$response = $modx->runProcessor('web/upload', array(), array('processors_path' => $processorsPath));
$object = $response->getObject();
echo $modx->toJSON($object);