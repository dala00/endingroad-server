<?php
class AssetsManagerController extends AppController {
	public $name = 'AssetsManager';
	public $uses = array();
	private $version = '1.0.1';
	
	public function beforeFilter() {
	}
	
	public function admin_generate() {
		$commonExcepts = array(
			'/^\.$/',
			'/^\.\.$/',
			'/^Thumbs\.db$/',
			'/^release_true/',
			'/^miproj$/',
		);
		//android
		$excepts = array(
			'/\.js$/',
		);
		$excepts = array_merge($commonExcepts, $excepts);
		$this->generateFunc('android', true, $excepts);
		$this->generateFunc('android', false, $excepts);
		
		//ios
		$path = WWW_ROOT . 'assets_ios';
		exec("cd $path; zip -r src.zip src");
		chmod($path . '/src.zip', 0666);
		$excepts = array(
			'/\.jsc$/',
			'/\.ogg$/',
//			'/\.zip$/',
			'/^src$/',
		);
		$excepts = array_merge($commonExcepts, $excepts);
		$this->generateFunc('ios', true, $excepts);
		$this->generateFunc('ios', false, $excepts);
	}
	
	private function generateFunc($name, $forClient, $excepts) {
		$json = $this->getVersion($name);
		$jsonString = $this->jsonEncode($json);
		if (!$forClient) {
			$filename = $name . '_version.manifest';
			if ($name == 'ios') {
			//	$jsonString = str_replace('\\/', '/', $jsonString);
			}
			file_put_contents(WWW_ROOT . "manifests/$filename", $jsonString);
		}
		
		$assets = $this->searchPath(WWW_ROOT . "assets_$name", $name, $forClient, $excepts);
		$json['assets'] = $assets;
		$json['searchPaths'] = array(
			'.',
			'res/publish',
			'../..',
		);
		$jsonString = $this->jsonEncode($json);
		if ($forClient) {
			$filename = $name . '_project_for_client.manifest';
		} else {
			$filename = $name . '_project.manifest';
		}
		if ($name == 'ios') {
		//	$jsonString = str_replace('\\/', '/', $jsonString);
		}
		file_put_contents(WWW_ROOT . "manifests/$filename", $jsonString);
	}
	
	private function getVersion($name) {
		$host = $_SERVER['HTTP_HOST'];
		$json = array(
			'packageUrl' => "http://$host/assets_$name/",
			'remoteVersionUrl' => "http://$host/manifests/{$name}_version.manifest",
			'remoteManifestUrl' => "http://$host/manifests/{$name}_project.manifest",
			'version' => $this->version,
			'engineVersion' => 'Cocos2d-JS v3.2',
		);
		return $json;
	}
	
	private function searchPath($basepath, $name, $forClient, $excepts) {
		$files = scandir($basepath);
		$assets = array();
		foreach ($files as $file) {
			$use = true;
			foreach ($excepts as $except) {
				if (preg_match($except, $file)) {
					$use = false;
					break;
				}
			}
			if ($use) {
				$path = "$basepath/$file";
				if (is_dir($path)) {
					$nextAssets = $this->searchPath($path, $name, $forClient, $excepts);
					$assets = array_merge($assets, $nextAssets);
				} else {
					/*
					if (preg_match('/\.js$/', $file)) {
						$jsPath = $path;
						$path = preg_replace('/js$/', 'zip', $path);
						$zip = new ZipArchive();
						$res = $zip->open($path, ZipArchive::CREATE);
						if ($res === true) {
							$zip->addFile($jsPath, $file);
							$zip->close();
						}
					}
					*/
					$assetPath = str_replace(WWW_ROOT . "assets_$name/", '', $path);
					$assets[$assetPath] = array(
						'md5' => $forClient ? '....' : md5_file($path),
//						'md5' => 'all',
					);
					if (preg_match('/\.zip$/', $path)) {
						$assets[$assetPath]['compressed'] = true;
					}
				}
			}
		}
		return $assets;
	}
}
