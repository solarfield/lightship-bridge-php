# Lightship Bridge Plugin

Automates the sharing of configuration and bootstrap functionality between lightship-php and lightship-js.


## Features

* Designed for a PHP back-end, with an HTML + JS front-end 
* Uses the [systemjs](https://github.com/systemjs/systemjs) JS module loader
* Automatic generation of `<script>` elements to initialize JS environment and bootstrap
* Automatic import of the current module's `Controller.js`
* PHP API to forward information to JS, including: 
    * environment vars
    * plugin registrations 
    * model data (as part of the initial HTML output)
    * on-the-fly JS System depCache
* `<script>` elements are deferred, with a single small inline script for the stub
* Overridable methods for `App` level customization of behaviour
* Event dispatch for higher-level extension


## Installation

This plugin requires [systemjs](https://github.com/systemjs/systemjs) version 0.20.x, and assumes that it is accessible 
via your web app at `$deps/systemjs/systemjs`. e.g. normally at file path `$project/www/__/$deps/systemjs/systemjs`.

1. Register the `LightshipBridge` plugin in your lightship-php app.

1. Create a file at `$project/www/__/libs/js/browser.js`. This file is used to set your SystemJS config settings. It 
should contain a call to `System.config()`, etc. See the [example file](docs/example-files/browser.js) to get the
general idea.

1. Create a file at `$project/www/__/libs/js/index.js`. This is the JS entry point file, and is used to bootstrap
your lightship-js app. See the [example file](./docs/example-files/index.js) to get the general idea.


## More

* [Source](https://github.com/solarfield/lightship-bridge)