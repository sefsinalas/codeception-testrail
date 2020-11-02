<?php

namespace sefsinalas\CodeceptionTestRail\Services;

/**
 * Class TestRailAPIClient
 *
 * @copyright Christian Dangl 2019
 * @package sefsinalas\CodeceptionTestRail\Services
 */
class TestRailAPIClient
{
    /** @var string */
    private $url = '';

    /** @var string */
    private $user = '';

    /** @var string */
    private $pwd = '';


    /**
     * TestRailAPIClient constructor.
     *
     * @param string $url
     * @param string $user
     * @param string $pwd
     */
    public function __construct(string $url, string $user, string $pwd)
    {
        # build the testrail url
        $this->url = trim($url, '/') . '/';
        $this->url = $url . '/index.php?/api/v2/';

        $this->user = $user;
        $this->pwd = $pwd;
    }

    /**
     * Sends the provided status along with the comment as
     * a new test result for the provided test case
     * in the defined test run in TestRail.
     *
     * @param string $runID
     * @param string $caseID
     * @param int $statusID
     * @param string $comment
     * @return string
     */
    public function sendTestRunResult(string $runID, string $caseID, int $statusID, string $comment): string
    {
        # replace any characters in the IDs.
        # IDs are digits only, however identification includes a character.
        $runID = str_replace('R', '', $runID);
        $caseID = str_replace('C', '', $caseID);

        $jsonParams = array(
            'status_id' => $statusID,
            'comment' => $comment,
        );

        /** @var string $response */
        $response = $this->post('add_result_for_case/' . $runID . '/' . $caseID, json_encode($jsonParams));

        return $response;
    }

    /**
     * @param string $slug
     * @return string
     */
    private function get(string $slug): string
    {
        $ch = $this->getBaseCurl($slug);

        $output = curl_exec($ch);
        curl_close($ch);

        return $output;
    }

    /**
     * @param string $slug
     * @param string $json
     * @return bool|string
     */
    private function post(string $slug, string $json)
    {
        $ch = $this->getBaseCurl($slug);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);

        $output = curl_exec($ch);
        curl_close($ch);

        return $output;
    }

    /**
     * @param $slug
     * @return false|resource
     */
    private function getBaseCurl($slug)
    {
        $header = array();
        $header[] = 'Content-type: application/json';
        $header[] = 'Authorization: Basic ' . base64_encode($this->user . ':' . $this->pwd);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->url . $slug);

        curl_setopt($ch, CURLOPT_USERPWD, "$this->user:$this->pwd");
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);

        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 2);

        return $ch;
    }

}
