<?php

namespace Micperr\SymfonyKernel;

use Symfony\Component as Sf;
use Symfony\Component\HttpFoundation\Request as SfRequest;

abstract class SuperKernel extends Sf\HttpKernel\Kernel implements SuperKernelInterface
{
    const ENV_DEV = 'dev';
    const ENV_TEST = 'test';
    const ENV_PROD = 'prod';

    private $cacheKernel;

    use VagrantTrait;

    public function getCacheDir()
    {
        if ($this->shouldMemoryCacheBeEnabled()) {
            return $this->getMemoryCacheDir();
        }

        return parent::getCacheDir();
    }

    public function getLogDir()
    {
        if ($this->shouldMemoryCacheBeEnabled()) {
            return $this->getMemoryLogDir();
        }

        return parent::getLogDir();
    }

    public function __construct(string $environment = null, bool $debug = null)
    {
        if (null === $environment) {
            $environment = $_SERVER['APP_ENV'] ?? self::ENV_DEV;
        }

        if (null === $debug) {
            $debug = (bool) $_SERVER['APP_DEBUG'] ?? true;
        }

        parent::__construct($environment, $debug);

        if ($this->isDebug()) {
            if( ! class_exists('Symfony\Component\Debug\Debug')) {
                throw new \RuntimeException('symfony/debug package is required in order to enable debugging.');
            }
            umask(0000);
            Sf\Debug\Debug::enable();
        }

        if ($trustedProxies = $_SERVER['TRUSTED_PROXIES'] ?? $_ENV['TRUSTED_PROXIES'] ?? false) {
            SfRequest::setTrustedProxies(explode(',', $trustedProxies), SfRequest::HEADER_X_FORWARDED_ALL ^ SfRequest::HEADER_X_FORWARDED_HOST);
        }

        if ($trustedHosts = $_SERVER['TRUSTED_HOSTS'] ?? $_ENV['TRUSTED_HOSTS'] ?? false) {
            SfRequest::setTrustedHosts([$trustedHosts]);
        }
    }

    /**
     * Terminates execution of a script
     */
    public function run(): void
    {
        $kernel = $this->cacheKernel ?? $this;

        $request = SfRequest::createFromGlobals();
        $response = $kernel->handle($request, Sf\HttpKernel\HttpKernelInterface::MASTER_REQUEST, true);
        $response->send();
        $kernel->terminate($request, $response);
    }

    public function enableCache(array $cacheOptions = [], string $cacheDir = null): SuperKernel
    {
        if( ! class_exists('Symfony\Bundle\FrameworkBundle\HttpCache\HttpCache')) {
            throw new \RuntimeException('symfony/framework-bundle is required in order to run CacheKernel.');
        }

        $this->cacheKernel = new CacheKernel($this, $cacheDir, $cacheOptions);
        return $this;
    }

    public function enableProductionEnvironment(array $cacheOptions = [], string $cacheDir = null): SuperKernel
    {
        $this->enableCache($cacheOptions, $cacheDir);
        $this->environment = self::ENV_PROD;
        return $this;
    }

    public function isEnvDev(): bool
    {
        return self::ENV_DEV === $this->getEnvironment();
    }

    public function isEnvTest(): bool
    {
        return self::ENV_TEST === $this->getEnvironment();
    }

    public function isEnvProd(): bool
    {
        return self::ENV_PROD === $this->getEnvironment();
    }
}