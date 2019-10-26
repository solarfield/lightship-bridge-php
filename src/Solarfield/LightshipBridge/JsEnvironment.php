<?php
namespace Solarfield\LightshipBridge;

class JsEnvironment {
	private $environmentVars = [];
	private $pluginRegistrations = [];
	private $options = [];
	private $depCache = [];
	
	public function forwardEnvironmentVar(string $aName) {
		if (!in_array($aName, $this->environmentVars)) {
			$this->environmentVars[] = $aName;
		}
	}
	
	public function forwardPluginRegistration(string $aComponentCode) {
		if (!in_array($aComponentCode, $this->pluginRegistrations)) {
			$this->pluginRegistrations[] = $aComponentCode;
		}
	}
	
	public function forwardOption(string $aOption) {
		if (!in_array($aOption, $this->options)) {
			$this->options[] = $aOption;
		}
	}
	
	public function addSystemDepCache($aParentModule, $aChildModules) {
		$parentModule = $aParentModule ?: 'app/App/Environment';
		$childModules = is_array($aChildModules) ? $aChildModules : [(string)$aChildModules];
		
		if (!array_key_exists($parentModule, $this->depCache)) {
			$this->depCache[$parentModule] = [];
		}
		$cache = &$this->depCache[$parentModule];
		
		foreach ($childModules as $module) {
			if (!array_key_exists($module, $cache)) {
				$cache[] = $module;
			}
		}
	}
	
	public function getForwardedEnvironmentVars() : array {
		return $this->environmentVars;
	}
	
	public function getForwardedPluginRegistrations() : array {
		return $this->pluginRegistrations;
	}
	
	public function getForwardedOptions() : array {
		return $this->options;
	}
	
	public function getSystemDepCache() : array {
		return $this->depCache;
	}

	public function __construct() {
		$this->forwardEnvironmentVar('loggingLevel');
	}
}
