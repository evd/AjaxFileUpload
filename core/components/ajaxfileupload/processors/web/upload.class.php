<?php
class AjaxFileUploadProcessor extends modProcessor {
    private $ajaxfileupload;

    public function initialize() {
		require_once $this->modx->getOption('ajaxfileupload.core_path',null,$this->modx->getOption('core_path').'components/ajaxfileupload/').'model/ajaxfileupload/ajaxfileupload.class.php';
		$this->ajaxfileupload = new AjaxFileUpload($this->modx, $this->getProperties());
    	return true;
    }

    public function getLanguageTopics() {
        return array('ajaxfileupload:default');
    }


	public function process() {
		$result = $this->ajaxfileupload->handleUpload();
		if (isset($result['success'])) 
			return $this->success('',$result);
		else
			return $this->failure('',$result);
	}
}

return 'AjaxFileUploadProcessor';