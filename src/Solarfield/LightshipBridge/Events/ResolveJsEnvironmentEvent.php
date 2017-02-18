<?php
namespace Solarfield\LightshipBridge\Events;

use Solarfield\LightshipBridge\Plugins\LightshipBridge\HtmlViewPlugin;
use Solarfield\Ok\Event;

class ResolveJsEnvironmentEvent extends Event {
	public function getTarget() : HtmlViewPlugin {
		return parent::getTarget();
	}
}