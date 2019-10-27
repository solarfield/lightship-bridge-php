<?php
declare(strict_types=1);
namespace Solarfield\LightshipBridge\Plugins\LightshipBridge;

use Solarfield\Lightship\EnvironmentInterface;
use Solarfield\Lightship\StandardOutputEvent;

class EnvironmentPlugin extends \Solarfield\Lightship\EnvironmentPlugin {
	private $stdoutMessages = [];

	public function takeBufferedStdoutMessages() : array {
		return array_splice($this->stdoutMessages, 0);
	}

	public function __construct(EnvironmentInterface $aEnvironment, $aComponentCode) {
		parent::__construct($aEnvironment, $aComponentCode);

		$this->getEnvironment()->getStandardOutput()->addEventListener('standard-output', function (StandardOutputEvent $aEvt) {
			// store the message for later
			$this->stdoutMessages[] = [
				'message' => $aEvt->getText(),
				'level' => $aEvt->getLevel(),
				'context' => $aEvt->getContext(),
			];
		});
	}
}
