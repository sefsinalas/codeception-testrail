<?php

namespace boxblinkracer\CodeceptionTestRail\Extension;

use Codeception\Event\FailEvent;
use Codeception\Event\TestEvent;
use \Codeception\Events;
use Codeception\Exception\ConfigurationException;
use Codeception\Extension;
use boxblinkracer\CodeceptionTestRail\Services\TestRailAPIClient;

/**
 * Class TestRailExtension
 *
 * @copyright Christian Dangl 2019
 * @package boxblinkracer\CodeceptionTestRail\Extension
 */
class TestRailExtension extends Extension
{

    private const ANNOTATION_CASE = 'case';

    private const STATUS_PASSED = 1;
    private const STATUS_BLOCKED = 2;
    private const STATUS_NOT_TESTED = 3;
    private const STATUS_RETEST = 4;
    private const STATUS_FAILED = 5;


    /** @var TestRailAPIClient */
    private $client = null;

    /** @var string */
    private $testRunID = '';

    /** @var bool */
    private $continueOnError = true;


    /**
     * @var array
     */
    public static $events = array(
        Events::TEST_FAIL => 'testFailed',
        Events::TEST_SUCCESS => 'testSuccess',
    );


    /**
     * TestRailExtension constructor.
     *
     * @param $config
     * @param $options
     */
    public function __construct($config, $options)
    {
        parent::__construct($config, $options);

        if (!isset($config['url']) || empty($config['url'])) {
            throw new ConfigurationException('Missing Configuration "url" for TestRail Extension');
        }

        if (!isset($config['user']) || empty($config['user'])) {
            throw new ConfigurationException('Missing Configuration "user" for TestRail Extension');
        }

        if (!isset($config['password']) || empty($config['password'])) {
            throw new ConfigurationException('Missing Configuration "password" for TestRail Extension');
        }

        if (!isset($config['runID']) || empty($config['runID'])) {
            throw new ConfigurationException('Missing Configuration "testRunID" for TestRail Extension');
        }

        if (!isset($config['continueOnError'])) {
            $this->continueOnError = true;
        } else {
            $this->continueOnError = (bool)$config['continueOnError'];
        }

        $url = (string)$config['url'];
        $user = (string)$config['user'];
        $pwd = (string)$config['password'];
        $this->testRunID = (string)$config['runID'];

        $this->client = new TestRailAPIClient(
            $url,
            $user,
            $pwd
        );

        echo PHP_EOL;
        echo ">> starting with TestRail Extension for " . $url . PHP_EOL;
        echo ">> using Test Run ID: " . $this->testRunID . PHP_EOL;
    }


    /**
     * @param TestEvent $e
     * @throws \Exception
     */
    public function testSuccess(TestEvent $e)
    {
        /** @var array $testCase */
        $testCase = $e->getTest()->getMetadata()->getParam(self::ANNOTATION_CASE);

        if ($testCase !== null && count($testCase) > 0) {

            /** @var string $testCase */
            $testCase = $testCase[0];

            echo PHP_EOL;
            echo ">> sending TestRail OK for Test " . $testCase . PHP_EOL;
            $this->sendResult($testCase, self::STATUS_PASSED, "");
        }
    }

    /**
     * @param FailEvent $e
     * @throws \Exception
     */
    public function testFailed(FailEvent $e)
    {
        /** @var array $testCase */
        $testCase = $e->getTest()->getMetadata()->getParam(self::ANNOTATION_CASE);

        if ($testCase !== null && count($testCase) > 0) {

            /** @var string $testCase */
            $testCase = $testCase[0];

            /** @var string $comment */
            $comment = $e->getFail()->getMessage();

            echo PHP_EOL;
            echo ">> sending TestRail FAILED for Test " . $testCase . PHP_EOL;
            $this->sendResult($testCase, self::STATUS_FAILED, $comment);
        }
    }

    /**
     * @param string $caseID
     * @param int $statusID
     * @param string $comment
     * @throws \Exception
     */
    private function sendResult(string $caseID, int $statusID, string $comment)
    {
        $response = $this->client->sendTestRunResult(
            $this->testRunID,
            $caseID,
            $statusID,
            $comment
        );

        if (strpos($response, 'error') !== false) {

            if ($this->continueOnError) {
                echo '** Error when sending result to TestRail: ' . $response . PHP_EOL;
            } else {
                throw new \Exception('Error when sending result to TestRail: ' . $response);
            }
        }
    }

}
