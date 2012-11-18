<?php
/**
 * Resolve settings.
 *
 * @package ajaxfileupload
 * @subpackage build
 */
function createSetting(&$modx,$key,$value,$type='textfield') {
	$ct = $modx->getCount('modSystemSetting',array(
		'key' => 'ajaxfileupload.'.$key,
	));
	if (empty($ct)) {
		$setting = $modx->newObject('modSystemSetting');
		$setting->set('key','ajaxfileupload.'.$key);
		$setting->set('xtype', $type);
		$setting->set('value',$value);
		$setting->set('namespace','ajaxfileupload');
		$setting->set('area','');
		$setting->save();
	}
}
if ($object->xpdo) {
	switch ($options[xPDOTransport::PACKAGE_ACTION]) {
		case xPDOTransport::ACTION_INSTALL:
		case xPDOTransport::ACTION_UPGRADE:
			$modx =& $object->xpdo;

			/* create secret key */
			createSetting($modx,'secret_key', sha1(uniqid(mt_rand(), true)), 'text-password');
		break;
	}
}
return true;