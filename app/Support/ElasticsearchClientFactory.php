<?php

namespace App\Support;

use Elastic\Elasticsearch\Client;
use Elastic\Elasticsearch\ClientBuilder;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Builds the Elasticsearch PHP client, with optional OpenSearch compatibility.
 *
 * elasticsearch/elasticsearch 8.x rejects servers that do not send X-Elastic-Product: Elasticsearch.
 * OpenSearch and AWS OpenSearch Service omit that header, so we add it on responses when enabled.
 * OpenSearch also rejects elasticsearch-php 8 "compatible-with=8" media types, so we normalize those.
 */
class ElasticsearchClientFactory
{
    public static function make(): Client
    {
        $builder = ClientBuilder::create()
            ->setHosts(config('elasticsearch.hosts'));

        if (filter_var(config('elasticsearch.allow_opensearch', true), FILTER_VALIDATE_BOOLEAN)) {
            $stack = HandlerStack::create();
            $stack->push(Middleware::mapRequest(
                static function (RequestInterface $request): RequestInterface {
                    foreach (['Content-Type', 'Accept'] as $header) {
                        if (! $request->hasHeader($header)) {
                            continue;
                        }

                        $value = $request->getHeaderLine($header);
                        $normalized = preg_replace(
                            '/application\/vnd\.elasticsearch\+([^;,]+);\s*compatible-with=\d+/',
                            'application/$1',
                            $value
                        ) ?? $value;

                        if ($normalized !== $value) {
                            $request = $request->withHeader($header, $normalized);
                        }
                    }

                    return $request;
                }
            ));
            $stack->push(Middleware::mapResponse(
                static function (ResponseInterface $response): ResponseInterface {
                    if ($response->getHeaderLine('X-Elastic-Product') === '') {
                        return $response->withHeader('X-Elastic-Product', 'Elasticsearch');
                    }

                    return $response;
                }
            ));
            $builder->setHttpClient(new GuzzleClient(['handler' => $stack]));
        }

        return $builder->build();
    }
}
