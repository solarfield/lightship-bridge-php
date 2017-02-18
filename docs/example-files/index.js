System.import('solarfield/lightship-js/src/Solarfield/Lightship/Bootstrapper')
.then(function (Bootstrapper) {
	return Bootstrapper.go(App.stub);
});