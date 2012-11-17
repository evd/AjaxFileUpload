<?php
require_once $modx->getOption('ajaxfileupload.core_path',null,$modx->getOption('core_path').'components/ajaxfileupload/').'model/ajaxfileupload/ajaxfileupload.class.php';
$ajaxfileupload = new AjaxFileUpload($modx,$scriptProperties);
return $ajaxfileupload->process();