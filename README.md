# TestRail Extension


## Installation

This package uses Composer, please checkout the composer website for more information.

The following command will install codeception-testrail into your project. 
It will also add a new entry in your composer.json and update the composer.lock as well.

```bash
$ composer require boxblinkracer/codeception-testrail
```

This package follows the PSR-4 convention names for its classes, which means you can easily integrate codeception-testrail classes loading in your own autoloader.


## How to use

### Extension
The extension provides an easy way to integrate the test suite with a TestRail Run.

#### Configuration
Enable the extension in your suite configuration file for the environments you want:

```yaml
env:
    dev:
        extensions:
            enabled:
                -  \boxblinkracer\CodeceptionTestRail\Extension\TestRailExtension:
                        url:  https://company.testrail.io
                        user: "xxx"
                        password: "xxx"
                        runID: R1234
```


## Configuration
Please configure your extension with
parameters like TestRail Endpoint, Test Run ID and more.
These can vary from environment to environment.

* **url:** (required) Base URL of the TestRail API, https://company.testrail.io
* **user:** (required) The E-Mail Address for the API login
* **password:** (required) The E-Mail Address for the API login
* **runID:** (required) This is the ID of the prepared Test Run that will receive the Codeception Test Results
* **continueOnError:** (optional) Continue/Stop Codeception if a problem with the Extension occurs. Default is "Yes". Possible values: [true/false/1/0]


#### Tests
All you need to do, is to define what PHP test equals what TestRail test.
Do this, by simply appending a new annotation to your tests.

The extension will now automatically look for this annotation, and send the test result
of this ID to the configured TestRail Test Run.

```ruby
 /**
  * @case C123
  */
 public function testMyE2EProcess(...)
 {
        ..
 }
```


### Copying / License
This repository is distributed under the MIT License (MIT). You can find the whole license text in the [LICENSE](LICENSE) file.

