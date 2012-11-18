<?php
/**
 * Add events to build
 * 
 * @package ajaxfileupload
 * @subpackage build
 */
$events = array();

$events[0]= $modx->newObject('modEvent');
$events[0]->fromArray(array(
	'name' => 'OnBeforeAjaxFileUpload',
	'service' => 6,
	'groupname' => 'AjaxFileUpload',
),'',true,true);

$events[1]= $modx->newObject('modEvent');
$events[1]->fromArray(array(
	'name' => 'OnAfterAjaxFileUpload',
	'service' => 6,
	'groupname' => 'AjaxFileUpload',
),'',true,true);

return $events;