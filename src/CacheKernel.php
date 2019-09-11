<?php

namespace Micper\SymfonyKernel;

use Symfony\Bundle\FrameworkBundle\HttpCache\HttpCache;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;

final class CacheKernel extends HttpCache
{
    private $options = [
        'default_ttl' => 60
    ];

    private $validOptions = [
        'debug',
        'default_ttl',
        'private_headers',
        'allow_reload',
        'allow_revalidate',
        'stale_while_revalidate',
        'stale_if_error'
    ];

    public function __construct(KernelInterface $kernel, string $cacheDir = null, array $options = [])
    {
        if($options) {
            $this->options = array_merge($this->options, $options);
        }

        parent::__construct($kernel, $cacheDir);
    }

    protected function invalidate(Request $request, $catch = false)
    {
        if ('PURGE' !== $request->getMethod()) {
            return parent::invalidate($request, $catch);
        }

        if ('127.0.0.1' !== $request->getClientIp()) {
            return new Response(
                'Invalid HTTP method',
                Response::HTTP_BAD_REQUEST
            );
        }

        $response = new Response();
        if ($this->getStore()->purge($request->getUri())) {
            $response->setStatusCode(Response::HTTP_OK, 'Purged');
        } else {
            $response->setStatusCode(Response::HTTP_NOT_FOUND, 'Not found');
        }

        return $response;
    }

    protected function setOption(string $option, $value)
    {
        if($this->isValidOption($option)) {
            $this->options[$option] = $value;
        }
    }

    protected function isValidOption(string $option): bool
    {
        return in_array($option, $this->validOptions);
    }

    protected function getOptions()
    {
        return $this->options;
    }

    /**
     * The available options are:
     *
     *   * debug:                 If true, the traces are added as a HTTP header to ease debugging
     *
     *   * default_ttl            The number of seconds that a cache entry should be considered
     *                            fresh when no explicit freshness information is provided in
     *                            a response. Explicit Cache-Control or Expires headers
     *                            override this value. (default: 0)
     *
     *   * private_headers        Set of request headers that trigger "private" cache-control behavior
     *                            on responses that don't explicitly state whether the response is
     *                            public or private via a Cache-Control directive. (default: Authorization and Cookie)
     *
     *   * allow_reload           Specifies whether the client can force a cache reload by including a
     *                            Cache-Control "no-cache" directive in the request. Set it to ``true``
     *                            for compliance with RFC 2616. (default: false)
     *
     *   * allow_revalidate       Specifies whether the client can force a cache revalidate by including
     *                            a Cache-Control "max-age=0" directive in the request. Set it to ``true``
     *                            for compliance with RFC 2616. (default: false)
     *
     *   * stale_while_revalidate Specifies the default number of seconds (the granularity is the second as the
     *                            Response TTL precision is a second) during which the cache can immediately return
     *                            a stale response while it revalidates it in the background (default: 2).
     *                            This setting is overridden by the stale-while-revalidate HTTP Cache-Control
     *                            extension (see RFC 5861).
     *
     *   * stale_if_error         Specifies the default number of seconds (the granularity is the second) during which
     *                            the cache can serve a stale response when an error is encountered (default: 60).
     *                            This setting is overridden by the stale-if-error HTTP Cache-Control extension
     *                            (see RFC 5861).
     */
}