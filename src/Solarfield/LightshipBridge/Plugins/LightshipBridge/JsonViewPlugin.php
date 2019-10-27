<?php
namespace Solarfield\LightshipBridge\Plugins\LightshipBridge;

use Solarfield\Lightship\Events\CreateJsonDataEvent;
use Solarfield\Lightship\ViewInterface;

class JsonViewPlugin extends \Solarfield\Lightship\JsonViewPlugin {
	public function __construct(ViewInterface $aView, $aComponentCode) {
		parent::__construct($aView, $aComponentCode);

		$this->getView()->addEventListener('create-json-data', function (CreateJsonDataEvent $aEvt) {
			$stdoutMessages = $this->getView()->getEnvironment()->getPlugins()
				->expectByClass('\Solarfield\LightshipBridge\Plugins\LightshipBridge\EnvironmentPlugin')
				->takeBufferedStdoutMessages();

			if ($stdoutMessages) {
				$aEvt->getJsonData()->mergeInto([
					'app' => [
						'stdoutMessages' => $stdoutMessages,
					],
				]);
			}
		});
	}
}
