define(
	[
		'app/App/Environment',
		'solarfield/ok-kit-js/src/Solarfield/Ok/ObjectUtils',
		'solarfield/lightship-js/src/Solarfield/Lightship/ControllerPlugin',
		'solarfield/ok-kit-js/src/Solarfield/Ok/StructUtils',
		'solarfield/ok-kit-js/src/Solarfield/Ok/HttpLoaderResult',
		'solarfield/lightship-bridge-php/src/Solarfield/LightshipBridge/Plugins/LightshipBridge/EnvironmentPlugin',
	],
	function (
		Environment, ObjectUtils, LightshipControllerPlugin, StructUtils, HttpLoaderResult,
		EnvironmentPlugin
	) {
		"use strict";

		/**
		 * @class ControllerPlugin
		 * @extends Solarfield.Lightship.ControllerPlugin
		 */
		var ControllerPlugin = ObjectUtils.extend(LightshipControllerPlugin, {
			handleConduitData: function (aEvt) {
				var bundles; //will hold the raw JSON data, which we will check for known bundles
				var t;

				if (aEvt.data instanceof HttpLoaderResult) {
					if (aEvt.data.response.constructor === Object) {
						bundles = aEvt.data.response;
					}
				}

				else if (aEvt.data.constructor === Object) {
					bundles = aEvt.data;
				}


				if (bundles) {
					t = StructUtils.get(bundles, 'app.stdoutMessages');
					if (t) {
						this.getController().getEnvironment()
							.getPlugins().getByClass(EnvironmentPlugin)
							.processStdoutMessages(t);
					}
				}
			},

			constructor: function () {
				ControllerPlugin.super.apply(this, arguments);

				this.getController().getMainConduit().addEventListener('data', this.handleConduitData.bind(this));

				this.getController().addEventListener('do-task', function (aEvt) {
					//if there is pending data
					if (aEvt.target.getModel().get('app.pendingData')) {
						//push it into the main conduit
						aEvt.target.getMainConduit().push(aEvt.target.getModel().get('app.pendingData'))

						//if any error occurs, push the error onto the conduit as well (app can handle it, etc.)
							.catch(function (e) {
								aEvt.target.getMainConduit().push(e);
							}.bind(this))

							//finally
							.then(function () {
								//clear the pending data from the model
								aEvt.target.getModel().set('app.pendingData', null);
							}.bind(this));
					}
				});
			}
		});

		return ControllerPlugin;
	}
);
