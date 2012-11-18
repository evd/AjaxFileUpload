<?php
/**
 * Add chunks to build
 * 
 * @package ajaxfileupload
 * @subpackage build
 */
$chunks = array();

$chunks[0]= $modx->newObject('modChunk');
$chunks[0]->fromArray(array(
	'id' => 0,
	'name' => 'ajaxfileupload.tpl.upload',
	'description' => 'Chunk for upload html template code.',
	'snippet' => file_get_contents($sources['source_core'].'/elements/chunks/ajaxfileupload.tpl.upload.chunk.tpl'),
),'',true,true);

$chunks[1]= $modx->newObject('modChunk');
$chunks[1]->fromArray(array(
	'id' => 1,
	'name' => 'ajaxfileupload.tpl.js',
	'description' => 'Chunk for upload html template code.',
	'snippet' => file_get_contents($sources['source_core'].'/elements/chunks/ajaxfileupload.tpl.js.chunk.tpl'),
),'',true,true);

return $chunks;