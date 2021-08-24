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

class TwfyRequest
{
    private string $url = 'https://www.theyworkforyou.com/api/';
    private string $func;
    private array $args;

    /**
     * @throws TwfyException
     */
    public function __construct(string $func, array $args, string $api_key)
    {
        // Set function, arguments and API key
        $this->func = $func;
        $this->args = $args;
        $this->api_key = $api_key;

        // Get and set the URL
        $this->url = $this->generateUrlForQuery($this->func);

        // Check to see if valid URL has been set
        if (!isset($this->url) || '' === $this->url) {
            throw new TwfyException('Invalid function: '.$this->func.'. Please look at the documentation for supported functions.');
        }
    }

    /**
     * @throws TwfyException
     */
    public function encodeQueryArguments(): string
    {
        // Validate the output argument if it exists
        if (\array_key_exists('output', $this->args)) {
            if (!$this->validateOutput($this->args['output'])) {
                throw new TwfyException('Invalid output type: '.$this->args['output'].'. Please look at the documentation for supported output types.');
            }
        }

        // Make sure all mandatory arguments for a particular function are present
        if (!$this->validateQueryArguments($this->func, $this->args)) {
            throw new TwfyException('All mandatory arguments for '.$this->func.' not provided.');
        }

        // Assemble the URL
        $full_url = $this->url.'?key='.$this->api_key.'&';

        foreach ($this->args as $name => $value) {
            $full_url .= $name.'='.urlencode($value).'&';
        }

        return substr($full_url, 0, -1);
    }

    private function generateUrlForQuery(string $func): string
    {
        if (!isset($func) || '' === $func) {
            return '';
        }

        $valid_functions = [
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

        if (true === \array_key_exists($func, $valid_functions)) {
            return $this->url.$func;
        }

        return '';
    }

    private function validateOutput(string $output): bool
    {
        if (!isset($output) || '' === $output) {
            return false;
        }

        $valid_params = [
            'xml' => 'XML output',
            'php' => 'Serialized PHP',
            'js' => 'a JavaScript object',
            'rabx' => 'RPC over Anything But XML',
        ];

        return \array_key_exists($output, $valid_params);
    }

    private function validateQueryArguments(string $func, array $args): bool
    {
        // Define manadatory arguments
        $functions_params = [
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

        // Check to see if all mandatory arguments are present
        $required_params = $functions_params[$func];

        foreach ($required_params as $param) {
            if (!isset($args[$param])) {
                return false;
            }
        }

        return true;
    }
}
