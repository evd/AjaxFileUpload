<?php
/**
 * The base class for AjaxFileUpload.
 *
 * @package ajaxfileupload
 */
class AjaxFileUpload {
	function __construct(modX &$modx,array $config = array()) {
		$this->modx =& $modx;

		$corePath = $this->modx->getOption('ajaxfileupload.core_path',null,$this->modx->getOption('core_path').'components/ajaxfileupload/');
		$assetsUrl = $this->modx->getOption('ajaxfileupload.assets_url',null,$this->modx->getOption('assets_url').'components/ajaxfileupload/');
		$endpointUrl = $assetsUrl.'upload.php';

		$this->config = array_merge(array(
			'assetsUrl' => $assetsUrl,
			'cssUrl' => $assetsUrl.'css/',
			'jsUrl' => $assetsUrl.'js/',
			'endpointUrl' => $endpointUrl,
			'corePath' => $corePath,
			'modelPath' => $corePath.'model/',
			'chunksPath' => $corePath.'elements/chunks/',
			'chunkSuffix' => '.chunk.tpl',
			'snippetsPath' => $corePath.'elements/snippets/',
			'processorsPath' => $corePath.'processors/',

			'id' => 'ajaxfileupload',
			'uploadPath' => '',
			'allowedExtensions' => $modx->getOption('upload_files'),
			'sizeLimit' => $modx->getOption('upload_maxsize'),
			'multiple' => true,
			'maxConnections' => 3,
			'registerCSS' => true,
			'registerJS' => true,
			'uploadTpl' => 'tpl.ajaxfileupload.upload',
			'jsTpl' => 'tpl.ajaxfileupload.js',
			'toPlaceholder' => false,
			'placeholderName' => 'ajaxfileupload.upload'
		),$config);

        $lang = isset($this->config['lang']) ? $this->config['lang'] . ':' : '';
        $this->modx->lexicon->load($lang.'ajaxfileupload:default');
	}

	/**
	 * Gets a Chunk and caches it; also falls back to file-based templates
	 * for easier debugging.
	 *
	 * @access public
	 * @param string $tpl The name of the Chunk
	 * @param array $placeholders The properties for the Chunk
	 * @return string The processed content of the Chunk
	 */
	public function getChunk($tpl,array $placeholders = array()) {
		if(preg_match('#^(@CODE:)#', $tpl))
		{
			$chunk = $this->modx->newObject('modChunk');
			$chunk->setCacheable(false);
			$chunk->setContent(substr($tpl, 6));
		}
		elseif(preg_match('#^(@FILE:)#', $tpl))
		{
			$chunk = $this->modx->newObject('modChunk');
			$chunk->setCacheable(false);
			$chunk->setContent(file_get_contents(substr($tpl, 6)));
		}
		else
		{
			$chunk = $this->modx->getObject('modChunk', array('name' => $tpl), true);
			if(!is_object($chunk))
			{
				$chunk = $this->modx->newObject('modChunk');
				$chunk->setCacheable(false);
				$chunk->setContent('');
			}
		}
		return $chunk->process($placeholders);
	}

	/**
	 * Execute snippet
	 *
	 * @access public
	 * @return string result of snippet
	 */
	public function process() {
		if ($this->config['registerCSS']) {
			$cssFile = isset($this->config['customCss'])?$this->config['customCss']:$this->config['assetsUrl'].'css/fineuploader.css';
			$this->modx->regClientCss($cssFile);
		}
		if ($this->config['registerJS']) {
			$this->modx->regClientScript($this->config['assetsUrl'].'js/web/jquery.fineuploader-3.0.min.js');
		}
		if (!empty($this->config['jsTpl'])) {
			$this->modx->regClientScript($this->processJSTpl(), true);
		}
		$output = $this->getChunk($this->config['uploadTpl'], $this->config);

		if ($this->config['toPlaceholder']) {
			$this->modx->setPlaceholder($this->config['placeholderName'], $output);
			$output = '';
		}
		return $output;
	}

	/**
	 * Process JS template chunk
	 *
	 * @access private
	 * @return string result of processed chunk
	 */
	private function processJSTpl() {
		//Make allowedExtensionArray for JS in array format
		$allowedExtensions = array();
		if (!empty($this->config['allowedExtensions'])) {
			$allowedExtensions = explode(',', $this->config['allowedExtensions']);
			$allowedExtensions = array_map('trim', $allowedExtensions);
			$this->config['allowedExtensionsArray'] = '["' . implode('","', $allowedExtensions) . '"]';
		}

		//Build request params for connector
		$params = $this->calcHash($this->getRequestParamsFromConfig());
		$this->config['requestParams'] = $this->modx->toJSON($params);

		return $this->getChunk($this->config['jsTpl'], $this->config);
	}

	/**
	 * Pick paramaters for request from config
	 *
	 * @access private
	 * @return array The request params
	 */
	private function getRequestParamsFromConfig() {
		$params = array(
			'id' => $this->config['id'],
			'allowedExtensions' => $this->config['allowedExtensions'],
			'sizeLimit'	=> $this->config['sizeLimit'],
			'uploadPath' => $this->config['uploadPath']
		);
		if (isset($this->config['requestParams'])) {
			$params = array_merge($this->modx->fromJSON($this->config['requestParams']), $params);
		}
		return $params;
	}


	/**
	 * Pick paramaters from request
	 *
	 * @access private
	 * @return array The request params
	 */
	private function getRequestParamsFromRequest() {
		$params = $_GET;
		//Hash calculated without this parameters, so unset it
		unset($params['qqfile']);
		unset($params['hash']);
		return $params;
	}

	/**
	 * Calculate hash for request params
	 *
	 * @access private
	 * @return array The request params
	 */
	private function calcHash($params = array()) {
		$params['hash'] = md5(implode('|', $params).'|'.$this->modx->getOption('ajaxfileupload.secret_key',null,'secret'));
		return $params;
	}

	/**
	 * Handle file upload
	 *
	 * @access public
	 * @return array The result of upload operation
	 */
	public function handleUpload() {
		$params = $this->calcHash($this->getRequestParamsFromRequest());
		//We check hash, so additional sanitize don't required??
		if ($_REQUEST['hash']!=$params['hash']) {
			return array('error'=>$this->modx->lexicon('ajaxfileupload.access_denied'));
		}
		$this->config = array_merge($this->config, $params);

		//Assing to config for possible modification in event
		$this->config['qqfile'] = $this->sanitizeFilename();

		//May override config in event
		$this->invokeBeforeUploadEvent();
		if (isset($this->config['result'])) {
			//Event mark that something wrong, cancel upload file
			return $this->config['result'];
		}


		require_once $this->config['modelPath'].'/fineuploader/fineuploader.php';
		$allowedExtensions = explode(',', $this->config['allowedExtensions']);
		$allowedExtensions = array_map('trim',$allowedExtensions);
		$uploader = new qqFileUploader($allowedExtensions, $this->config['sizeLimit']);

		//If we change upload filename by sanitize or in event, so change it
		if (isset($_FILES['qqfile']))
			$_FILES['qqfile']['name'] = $this->config['qqfile'];
		else
			$_GET['qqfile'] = $this->config['qqfile'];

		$this->config['result'] = $uploader->handleUpload($this->processPath($this->config['uploadPath']));

		//May override result in event
		$this->invokeAfterUploadEvent();

		return $this->config['result'];
	}

	/**
	 * Invoke OnBeforeAjaxUploadEvent
	 * Event may override config params
	 *
	 * @access private
	 */
	private function invokeBeforeUploadEvent() {
		$eventResults = $this->modx->invokeEvent('OnBeforeAjaxFileUpload', $this->config);
		if (!empty($eventResults)) {
			foreach($eventResults as $eventResult) {
				if (is_array($eventResult))
					$this->config = array_merge($this->config, $eventResult);
			}
		}
	}

	/**
	 * Invoke OnAfterAjaxUploadEvent
	 * Event may override result
	 *
	 * @access private
	 */
	private function invokeAfterUploadEvent() {
		$eventResults = $this->modx->invokeEvent('OnAfterAjaxFileUpload', $this->config);
		if (!empty($eventResults)) {
			foreach($eventResults as $eventResult) {
				if (is_array($eventResult))
					$this->config['result'] = array_merge($this->config['result'], $eventResult);
			}
		}
	}

	/**
	 * Get path from config, convert relative path to abslolute
	 *
	 * @access private
	 * @param $path The path for check and convert
	 * @return string converted path
	 */
	private function processPath($path = '') {
		//Get path from modx config, if exists, else path from request var or event
		$path = $this->modx->getOption('ajaxfileupload.'.$this->config['id'].'_upload_path', null, $path);

		if (sizeof($path)>0) {
			if ($path[0]!='/')
				//Path relative, add base path
				$path = $this->modx->getOption('base_path').$path;
		}
		//Path must ends with separator
		$path = rtrim($path,'/').'/';
		return $path;
	}

	/**
	 * Sanitize filename
	 *
	 * @access private
	 * @param $file The filename for sanitize
	 * @return string sanitized filename
	 */
	private function sanitizeFilename() {
		if (isset($_FILES['qqfile']))
			$filename = $_FILES['qqfile']['name'];
		else
			$filename = $_GET['qqfile'];
		$filename = preg_replace("/([^\w\s\d\-_\.]|[\.]{2,})/", '', $filename);
		return $filename;
	}
}