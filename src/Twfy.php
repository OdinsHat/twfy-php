<?php

declare(strict_types=1);

/*
 * This file is part of Tyre Label Generator.
 * (c) Doug Bromley <doug@tintophat.com>
 * This source file is subject to the BSD license that is bundled
 * with this source code in the file LICENSE.
 */

namespace OdinsHat\Twfy;

use OdinsHat\Twfy\Exception\TwfyException;

class Twfy
{
    private string $apiKey;
    private $curlHandle;

    public function __construct(string $apiKey)
    {
        try {
            if (!$apiKey) {
                throw new TwfyException('No API key provided.');
            }

            if (!preg_match('/^[A-Za-z0-9]+$/', $apiKey)) {
                throw new TwfyException('Invalid API key provided.');
            }
        } catch (TwfyException $e) {
            echo 'Issue with API key '.$e->getMessage();
            exit;
        }

        $this->apiKey = $apiKey;

        // Create a new instance of cURL
        $this->curlHandle = curl_init();

        curl_setopt($this->curlHandle, CURLOPT_USERAGENT, 'TheyWorkForYou.com API PHP interface (+https://github.com/OdinsHat/twfy-php)');
        curl_setopt($this->curlHandle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->curlHandle, CURLOPT_FOLLOWLOCATION, true);
    }

    public function __destruct()
    {
        curl_close($this->curlHandle);
    }

    public function constructQuery($func, $args = []): string
    {
        if (!isset($func) || '' === $func || !isset($args) || '' === $args || !\is_array($args)) {
            throw new TwfyException('Function name or arguments not provided.');
        }

        $query = new TwfyRequest($func, $args, $this->apiKey);

        return $this->executeQuery($query);
    }

    private function executeQuery($query)
    {
        $url = $query->encode_arguments();

        curl_setopt($this->curlHandle, CURLOPT_URL, $url);

        $result = curl_exec($this->curlHandle);

        if (!$result) {
            throw new TwfyException('cURL error occurred: '.curl_error($this->curlHandle));
        }

        if (404 === curl_getinfo($this->curlHandle, CURLINFO_HTTP_CODE)) {
            throw new TwfyException('Could not reach TWFY server.');
        }

        return $result;
    }
}
