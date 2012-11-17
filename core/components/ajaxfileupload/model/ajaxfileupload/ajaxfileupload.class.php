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
		$actionUrl = $assetsUrl.'upload.php';

		$this->config = array_merge(array(
			'assetsUrl' => $assetsUrl,
			'cssUrl' => $assetsUrl.'css/',
			'jsUrl' => $assetsUrl.'js/',
			'actionUrl' => $actionUrl,
			'corePath' => $corePath,
			'modelPath' => $corePath.'model/',
			'chunksPath' => $corePath.'elements/chunks/',
			'chunkSuffix' => '.chunk.tpl',
			'snippetsPath' => $corePath.'elements/snippets/',
			'processorsPath' => $corePath.'processors/',

			'id' => 'ajaxfileupload',
			'uploadPath' => '',
			'allowedExtensions' => 'jpg,jpeg,png,gif',
			'sizeLimit' => 512000,
			'multiple' => true,
			'maxConnections' => 3,
			'registerCSS' => true,
			'registerJS' => true,
			'uploadTpl' => 'tpl.ajaxfileupload.upload',
			'jsTpl' => 'tpl.ajaxfileupload.js',
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
			$cssFile = isset($this->config['customCss'])?$this->config['customCss']:$this->config['assetsUrl'].'css/fileuploader.css';
			$this->modx->regClientCss($cssFile);
		}
		if ($this->config['registerJS']) {
			$this->modx->regClientScript($this->config['assetsUrl'].'js/web/fileuploader.js');
		}
		if (!empty($this->config['jsTpl'])) {
			$this->modx->regClientScript($this->processJSTpl(), true);
		}
		$output = $this->getChunk($this->config['uploadTpl'], $this->config);
		//return '<div id="ajaxfileupload"></div>';
		return $output;
	}

	/**
	 * Process JS template
	 *
	 * @access private
	 * @return string result of snippet
	 */
	private function processJSTpl() {
		//Make allowedExtension for JS in array format
		$allowedExtensions = array();
		if (!empty($this->config['allowedExtensions'])) {
			$allowedExtensions = explode(',', $this->config['allowedExtensions']);
			$allowedExtensions = array_map('trim', $allowedExtensions);
			$this->config['allowedExtensionsArray'] = '["' . implode('","', $allowedExtensions) . '"]';
		}

		//Assign params for connector
		$params = $this->buildRequestParams($this->config);
		$this->config['actionParams'] = $this->modx->toJSON($params);

		return $this->getChunk($this->config['jsTpl'], $this->config);
	}

	private function buildRequestParams($properties = array()) {
		$params = array(
			'id' => $properties['allowedExtensions'],
			'allowedExtensions' => $properties['allowedExtensions'],
			'sizeLimit'	=> $properties['sizeLimit'],
			'uploadPath' => $properties['uploadPath']
		);
		$params['hash'] = md5(implode('|', $params).'|'.$this->modx->getOption('ajaxfileupload.secret_key',null,'secret'));
		return $params;
	}

	/**
	 * Handle file upload
	 *
	 * @access public
	 * @return array
	 */
	public function handleUpload() {
		$params = $this->buildRequestParams($_REQUEST);
		//We check hash, so additional sanitize don't required??
		if ($_REQUEST['hash']!=$params['hash']) {
			return array('error'=>$this->modx->lexicon('ajaxfileupload.access_denied'));
		}
		$this->config = array_merge($this->config, $params);
		require_once $this->config['modelPath'].'/fileuploader/fileuploader.php';
		$allowedExtensions = explode(',', $this->config['allowedExtensions']);
		$allowedExtensions = array_map('trim',$allowedExtensions);
		$uploader = new qqFileUploader($allowedExtensions, $this->config['sizeLimit']);
		$result = $uploader->handleUpload($this->modx->getOption('base_path').$this->config['uploadPath']);
		return $result;
	}
}