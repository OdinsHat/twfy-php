<?php

declare(strict_types=1);

/*
 * This file is part of TheyWorkForYou PHP SDK.
 * (c) Doug Bromley <doug@tintophat.com>
 * This source file is subject to the BSD 3 Clause License that
 * is bundled with this source code in the file LICENSE.
 */

namespace OdinsHat\Twfy;

use OdinsHat\Twfy\Exception\TwfyException;

class TwfyRequest
{
    private string $url = 'https://www.theyworkforyou.com/api/';
    private string $apiKey;
    private string $func;
    private array $args;

    /**
     * @throws TwfyException
     */
    public function __construct(string $func, array $args, string $apiKey)
    {
        $this->func = $func;
        $this->args = $args;
        $this->apiKey = $apiKey;

        $this->url = $this->generateUrlForQuery($this->func);

        if ('' === $this->url) {
            throw new TwfyException(
                'Invalid function: ' . $this->func . '. Please look at the documentation for supported functions.'
            );
        }
    }

    /**
     * @throws TwfyException
     */
    public function encodeQueryArguments(): string
    {
        if (\array_key_exists('output', $this->args)) {
            if (!$this->validateOutput($this->args['output'])) {
                throw new TwfyException(
                    'Invalid output type: ' .
                    $this->args['output'] .
                    '. Please look at the documentation for supported output types.'
                );
            }
        }

        if (!$this->validateQueryArguments($this->func, $this->args)) {
            throw new TwfyException('All mandatory arguments for ' . $this->func . ' not provided.');
        }

        $fullUrl = $this->url . '?key=' . $this->apiKey . '&';

        foreach ($this->args as $name => $value) {        // Define manadatory arguments
            $fullUrl .= $name . '=' . urlencode($value) . '&';
        }

        return substr($fullUrl, 0, -1);
    }

    private function generateUrlForQuery(string $func): string
    {
        if ('' === $func) {
            return '';
        }

        $validFunctions = [
            'convertURL' => 'Converts a parliament.uk URL into a TheyWorkForYou one, if possible',
            'getConstituency' => 'Searches for a constituency',
            'getConstituencies' => 'Returns list of constituencies',
            'getPerson' => 'Returns main details for a person',
            'getMP' => 'Returns main details for an MP',
            'getMPInfo' => 'Returns extra information for a person',
            'getMPsInfo' => 'Returns extra information for one or more people',
            'getMPs' => 'Returns list of MPs',
            'getLord' => 'Returns details for a Lord',
            'getLords' => 'Returns list of Lords',
            'getMLA' => 'Returns details for an MLA',
            'getMLAs' => 'Returns list of MLAs',
            'getMSP' => 'Returns details for an MSP',
            'getMSPs' => 'Returns list of MSPs',
            'getGeometry' => 'Returns centre, bounding box of constituencies',
            'getBoundary' => 'Returns boundary polygon of UK Parliament constituency',
            'getCommittee' => 'Returns members of Select Committee',
            'getDebates' => 'Returns Debates (either Commons, Westminster Hall, or Lords)',
            'getWrans' => 'Returns Written Answers',
            'getWMS' => 'Returns Written Ministerial Statements',
            'getHansard' => 'Returns any of the above',
            'getComments' => 'Returns comments',
        ];

        if (true === \array_key_exists($func, $validFunctions)) {
            return $this->url . $func;
        }

        return '';
    }

    private function validateOutput(string $output): bool
    {
        if ('' === $output) {
            return false;
        }

        $validParams = [
            'xml' => 'XML output',
            'php' => 'Serialized PHP',
            'js' => 'a JavaScript object',
            'rabx' => 'RPC over Anything But XML',
        ];

        return \array_key_exists($output, $validParams);
    }

    private function validateQueryArguments(string $func, array $args): bool
    {
        $functionsParams = [
            'convertURL' => ['url'],
            'getConstituency' => ['postcode'],
            'getConstituencies' => [],
            'getPerson' => ['id'],
            'getMP' => [],
            'getMPInfo' => ['id'],
            'getMPs' => [],
            'getLord' => ['id'],
            'getLords' => [],
            'getMLA' => [],
            'getMLAs' => [],
            'getMSPs' => [],
            'getGeometry' => [],
            'getBoundary' => ['name'],
            'getCommittee' => ['name'],
            'getDebates' => ['type'],
            'getWrans' => [],
            'getWMS' => [],
            'getHansard' => [],
            'getComments' => [],
        ];

        $requiredParams = $functionsParams[$func];

        foreach ($requiredParams as $param) {
            if (!isset($args[$param])) {
                return false;
            }
        }

        return true;
    }
}
