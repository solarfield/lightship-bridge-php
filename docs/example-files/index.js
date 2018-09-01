System.import('solarfield/lightship-js/src/Solarfield/Lightship/Bootstrapper')
	.then(function (Bootstrapper) {
		return (System.isModule(Bootstrapper) ? Bootstrapper.default : Bootstrapper)
			.go(App.stub)
			.then(function () {
				delete App.stub;
			});
	});
