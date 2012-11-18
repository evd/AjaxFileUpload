<?php
/**
 * Add snippets to build
 * 
 * @package ajaxfileupload
 * @subpackage build
 */
$snippets = array();

$snippets[0]= $modx->newObject('modSnippet');
$snippets[0]->fromArray(array(
	'id' => 0,
	'name' => 'AjaxFileUpload',
	'description' => 'Ajax file upload',
	'snippet' => getSnippetContent($sources['source_core'].'/elements/snippets/snippet.ajaxfileupload.php'),
),'',true,true);
$properties = include $sources['build'].'properties/properties.ajaxfileupload.php';
$snippets[0]->setProperties($properties);
unset($properties);

return $snippets;