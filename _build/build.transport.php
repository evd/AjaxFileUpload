<?php
/**
 * AjaxFileUpload build script
 *
 * @package ajaxfileupload 
 * @subpackage build
 */
$mtime = microtime();
$mtime = explode(' ', $mtime);
$mtime = $mtime[1] + $mtime[0];
$tstart = $mtime;
set_time_limit(0);

/* define package */
define('PKG_NAME','AjaxFileUpload');
define('PKG_NAME_LOWER',strtolower(PKG_NAME));
define('PKG_VERSION','1.0.0');
define('PKG_RELEASE','beta2');

/* define sources */
$root = dirname(dirname(__FILE__)).'/';
$sources = array(
	'root' => $root,
	'build' => $root . '_build/',
	'data' => $root . '_build/data/',
	'resolvers' => $root . '_build/resolvers/',
	'chunks' => $root.'core/components/'.PKG_NAME_LOWER.'/elements/chunks/',
	'snippets' => $root.'core/components/'.PKG_NAME_LOWER.'/elements/snippets/',
	'plugins' => $root.'core/components/'.PKG_NAME_LOWER.'/elements/plugins/',
	'lexicon' => $root . 'core/components/'.PKG_NAME_LOWER.'/lexicon/',
	'docs' => $root.'core/components/'.PKG_NAME_LOWER.'/docs/',
	'source_assets' => $root.'assets/components/'.PKG_NAME_LOWER,
	'source_core' => $root.'core/components/'.PKG_NAME_LOWER,
);
unset($root);

/* override with your own defines here (see build.config.sample.php) */
require_once $sources['build'] . '/build.config.php';
require_once MODX_CORE_PATH . 'model/modx/modx.class.php';
require_once $sources['build'] . '/includes/functions.php';

$modx= new modX();
$modx->initialize('mgr');
echo '<pre>'; /* used for nice formatting of log messages */
$modx->setLogLevel(modX::LOG_LEVEL_INFO);
$modx->setLogTarget('ECHO');

$modx->loadClass('transport.modPackageBuilder','',false, true);
$builder = new modPackageBuilder($modx);
$builder->createPackage(PKG_NAME_LOWER,PKG_VERSION,PKG_RELEASE);
$builder->registerNamespace(PKG_NAME_LOWER,false,true,'{core_path}components/'.PKG_NAME_LOWER.'/');
$modx->log(modX::LOG_LEVEL_INFO,'Created Transport Package and Namespace.');

/* create category */
$category= $modx->newObject('modCategory');
$category->set('id',1);
$category->set('category',PKG_NAME);

/* add snippets */
$snippets = include $sources['data'].'transport.snippets.php';
if (!is_array($snippets)) {
	$modx->log(modX::LOG_LEVEL_ERROR,'Could not package in snippets.');
} else {
	$category->addMany($snippets);
	$modx->log(modX::LOG_LEVEL_INFO,'Packaged in '.count($snippets).' snippets.');
}

/* add chunks */
$chunks = include $sources['data'].'transport.chunks.php';
if (!is_array($chunks)) {
	$modx->log(modX::LOG_LEVEL_ERROR,'Could not package in chunks.');
} else {
	$category->addMany($chunks);
	$modx->log(modX::LOG_LEVEL_INFO,'Packaged in '.count($chunks).' chunks.');
}

/* add events */
/*$events = include $sources['data'].'transport.events.php';
if (!is_array($events)) {
	$modx->log(modX::LOG_LEVEL_ERROR,'Could not package in events.');
} else {
	$category->addMany($events);
	$modx->log(modX::LOG_LEVEL_INFO,'Packaged in '.count($events).' events.');
}*/

/* create category vehicle */
$attr = array(
	xPDOTransport::UNIQUE_KEY => 'category',
	xPDOTransport::PRESERVE_KEYS => false,
	xPDOTransport::UPDATE_OBJECT => true,
	xPDOTransport::RELATED_OBJECTS => true,
	xPDOTransport::RELATED_OBJECT_ATTRIBUTES => array (
		'Children' => array(
			xPDOTransport::PRESERVE_KEYS => false,
			xPDOTransport::UPDATE_OBJECT => true,
			xPDOTransport::UNIQUE_KEY => 'category',
			xPDOTransport::RELATED_OBJECTS => true,
			xPDOTransport::RELATED_OBJECT_ATTRIBUTES => array (
				'Snippets' => array(
					xPDOTransport::PRESERVE_KEYS => false,
					xPDOTransport::UPDATE_OBJECT => true,
					xPDOTransport::UNIQUE_KEY => 'name',
				),
				'Chunks' => array(
					xPDOTransport::PRESERVE_KEYS => false,
					xPDOTransport::UPDATE_OBJECT => false,
					xPDOTransport::UNIQUE_KEY => 'name',
				),
			),
		),
		'Snippets' => array(
			xPDOTransport::PRESERVE_KEYS => false,
			xPDOTransport::UPDATE_OBJECT => true,
			xPDOTransport::UNIQUE_KEY => 'name',
		),
		'Chunks' => array (
			xPDOTransport::PRESERVE_KEYS => false,
			xPDOTransport::UPDATE_OBJECT => false,
			xPDOTransport::UNIQUE_KEY => 'name',
		),
	),
);
$vehicle = $builder->createVehicle($category,$attr);

$modx->log(modX::LOG_LEVEL_INFO,'Adding file resolvers to category...');
$vehicle->resolve('file',array(
	'source' => $sources['source_assets'],
	'target' => "return MODX_ASSETS_PATH . 'components/';",
));
$vehicle->resolve('file',array(
	'source' => $sources['source_core'],
	'target' => "return MODX_CORE_PATH . 'components/';",
));

$modx->log(modX::LOG_LEVEL_INFO,'Adding in PHP resolvers...');
$vehicle->resolve('php',array(
	'source' => $sources['resolvers'] . 'resolve.settings.php',
));
$builder->putVehicle($vehicle);

/* load events */
$events = include $sources['data'].'transport.events.php';
if (!is_array($events)) {
	$modx->log(modX::LOG_LEVEL_ERROR,'Could not package in events.');
} else {
	$attributes = array (
		xPDOTransport::PRESERVE_KEYS => true,
		xPDOTransport::UPDATE_OBJECT => true,
		xPDOTransport::UNIQUE_KEY => 'name'
	);
	foreach ($events as $event) {
		$vehicle = $builder->createVehicle($event,$attributes);
		$builder->putVehicle($vehicle);
	}
}	
$modx->log(xPDO::LOG_LEVEL_INFO,'Packaged in '.count($events).' events.');

unset ($events, $event, $attributes);

/* now pack in the license file, readme and setup options */
$builder->setPackageAttributes(array(
	'changelog' => file_get_contents($sources['docs'] . 'changelog.txt')
	,'license' => file_get_contents($sources['docs'] . 'license.txt')
	,'readme' => file_get_contents($sources['docs'] . 'readme.txt')
	//'setup-options' => array(
		//'source' => $sources['build'].'setup.options.php',
	//),
));
$modx->log(modX::LOG_LEVEL_INFO,'Added package attributes and setup options.');

/* zip up package */
$modx->log(modX::LOG_LEVEL_INFO,'Packing up transport package zip...');
$builder->pack();

$mtime= microtime();
$mtime= explode(" ", $mtime);
$mtime= $mtime[1] + $mtime[0];
$tend= $mtime;
$totalTime= ($tend - $tstart);
$totalTime= sprintf("%2.4f s", $totalTime);

$modx->log(modX::LOG_LEVEL_INFO,"\n<br />Package Built.<br />\nExecution time: {$totalTime}\n");

exit ();