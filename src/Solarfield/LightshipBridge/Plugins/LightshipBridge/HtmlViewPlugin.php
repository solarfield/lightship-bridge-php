<?php
namespace Solarfield\LightshipBridge\Plugins\LightshipBridge;

use Solarfield\Lightship\Events\CreateHtmlEvent;
use Solarfield\Lightship\HtmlView;
use Solarfield\LightshipBridge\JsEnvironment;
use Solarfield\LightshipBridge\Events\ResolveJsEnvironmentEvent;
use Solarfield\Ok\JsonUtils;

class HtmlViewPlugin extends \Solarfield\Lightship\HtmlViewPlugin {
	private $jsEnvironment;
	
	protected function getJsStubData() {
		$this->resolveJsEnvironment();
		
		$view = $this->getView();
		$controller = $view->getController();
		$environment = $view->getEnvironment();

		$stub = [
			'moduleCode' => $view->getCode(),
		];

		$environmentOptions = [];
		$controllerOptions = [];
		$modelOptions = [];

		if ($environment->isDevModeEnabled()) $environmentOptions['devModeEnabled'] = true;

		//get forwarded environment vars
		$vars = [];
		foreach ($this->getJsEnvironment()->getForwardedEnvironmentVars() as $k) {
			$vars[$k] = $environment->getVars()->get($k);
		}
		if ($vars) $environmentOptions['vars'] = $vars;
		
		//get forwarded plugin registrations
		$forwards = $this->getJsEnvironment()->getForwardedPluginRegistrations();
		foreach ($controller->getPlugins()->getRegistrations() as $k => $registration) {
			if (in_array($registration['componentCode'], $forwards)) {
				$controllerOptions['pluginRegistrations'][] = [
					'componentCode' => $registration['componentCode'],
				];
				
				unset($forwards[$k]);
			}
		}
		unset($forwards, $k, $registration);
		
		$sourceOptions = $this->getView()->getOptions();
		foreach ($this->getJsEnvironment()->getForwardedOptions() as $code) {
			if ($sourceOptions->has($code)) {
				$controllerOptions['options'][$code] = $sourceOptions->get($code);
			}
		}
		
		//get any scripts flagged as 'bootstrap'.
		//These will be imported before bootstrap creates the app environment.
		//This is used to ensure scripts/objects involved in component-resolution/dependency-injection are in scope
		$items = [];
		foreach ($view->getScriptIncludes()->getResolvedFiles() as $item) {
			$item = array_replace([
				'bootstrap' => false,
			], $item);
			
			if ($item['bootstrap']) {
				$items[] = $item['resolvedUrl'];
			}
		}
		if ($items) $stub['jsModules'] = $items;
		
		//get any scripts to be pre-cached via System depCache.
		//Pre-cache will be initiated when app/App/Environment is imported
		$depCache = $this->getJsEnvironment()->getSystemDepCache();
		if ($depCache) {
			$stub['jsDepCache'] = $depCache;
		}
		
		//get pending data
		/** @var \Solarfield\Lightship\JsonView $jsonView */
		$jsonView = $controller->createView('Json');
		$pendingData = $jsonView->createJsonData();
		if ($pendingData) $modelOptions['pendingData'] = $pendingData;
		unset($jsonView, $pendingData);
		
		if ($environmentOptions) $stub['environmentOptions'] = $environmentOptions;
		if ($controllerOptions) $stub['controllerOptions'] = $controllerOptions;
		if ($modelOptions) $stub['modelOptions'] = $modelOptions;

		return $stub;
	}
	
	protected function resolveJsEnvironment() {
		$event = new ResolveJsEnvironmentEvent('resolve-js-environment', ['target' => $this]);
		
		$this->dispatchEvent($event, [
			'listener' => [$this, 'onResolveJsEnvironment'],
		]);
		
		$this->dispatchEvent($event);
	}
	
	protected function resolveInitScriptIncludes() {
		$includes = $this->getView()->getScriptIncludes();
		$depsPath = $this->getView()->getEnvironment()->getVars()->get('appDependenciesWebPath');
		$appPackagePath = $this->getView()->getEnvironment()->getVars()->get('appPackageWebPath');
		$bootstrapGroup = 1000;
		
		$includes->addFile("$depsPath/systemjs/systemjs/dist/system.js", [
			'attributes' => ['defer'=>true],
			'group' => $bootstrapGroup,
		]);
		
		$includes->addFile("$appPackagePath/libs/js/browser.js", [
			'attributes' => ['defer'=>true],
			'group' => $bootstrapGroup,
		]);
		
		$includes->addFile("$appPackagePath/libs/js/index.js", [
			'attributes' => ['defer'=>true],
			'group' => $bootstrapGroup,
		]);
	}
	
	protected function resolveModuleScriptIncludes() {
		$includes = $this->getView()->getScriptIncludes();
		
		$moduleCode = $this->getView()->getCode();
		$chain = $this->getView()->getEnvironment()->getComponentChain($moduleCode);
		
		$link = $chain->get('module');
		if ($link) {
			$dirs = str_replace('\\', '/', $link->namespace());
			$includes->addFile("app/$dirs/Controller", [
				'ignore' => true,
				'bootstrap' => true,
				'base' => 'module',
				'onlyIfExists' => true,
				'filePath' => '/Controller.js',
				'group' => 1250000,
			]);
		}
	}
	
	protected function createJsStubScriptElement() {
		ob_start();
		
		?>
		<script>
			if (!self.App) self.App = {};
			App.stub = <?php echo(JsonUtils::toJson($this->getJsStubData())) ?>;
		</script>
		<?php
		
		return ob_get_clean();
	}
	
	protected function onResolveJsEnvironment() {
		
	}
	
	protected function handleViewResolveScriptIncludes() {
		$this->resolveInitScriptIncludes();
		$this->resolveModuleScriptIncludes();
	}
	
	protected function handleViewCreateScriptElements(CreateHtmlEvent $aEvt) {
		$aEvt->getHtml()->prepend($this->createJsStubScriptElement());
	}
	
	public function getJsEnvironment() {
		if (!$this->jsEnvironment) {
			$this->jsEnvironment = new JsEnvironment();
		}
		
		return $this->jsEnvironment;
	}
	
	public function __construct(HtmlView $aView, $aComponentCode) {
		parent::__construct($aView, $aComponentCode);
		$this->getView()->addEventListener('resolve-script-includes', function ($e) {$this->handleViewResolveScriptIncludes($e);});
		$this->getView()->addEventListener('create-script-elements', function ($e) {$this->handleViewCreateScriptElements($e);});
	}
}
