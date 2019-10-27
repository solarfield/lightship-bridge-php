define(
	[
		'app/App/Environment',
		'solarfield/ok-kit-js/src/Solarfield/Ok/ObjectUtils',
		'solarfield/lightship-js/src/Solarfield/Lightship/EnvironmentPlugin',
	],
	function (Environment, ObjectUtils, LightshipEnvironmentPlugin) {
		"use strict";

		/**
		 * @class EnvironmentPlugin
		 * @extends Solarfield.Lightship.EnvironmentPlugin
		 */
		var EnvironmentPlugin = ObjectUtils.extend(LightshipEnvironmentPlugin, {
			/**
			 * @param {Object[]} aMessages
			 * @param {string} aMessages[].message - Text of the message.
			 * @param {string} aMessages[].level - Uppercase name of a level defined by RFC 5424.
			 * @param {Object} aMessages[].context - Additional context information.
			 */
			processStdoutMessages: function (aMessages) {
				var messages = aMessages||[];
				var i;

				for (i = 0; i < messages.length; i++) {
					messages[i].channel = 'server/stdout';
					this.getEnvironment().getLogger().logItem(messages[i]);
				}
			},
		});

		return EnvironmentPlugin;
	}
);
