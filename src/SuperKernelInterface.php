<?php

namespace Micperr\SymfonyKernel;

interface SuperKernelInterface
{
    public function run(): void;
    public function enableCache(array $cacheOptions = [], string $cacheDir = null): self;
    public function enableProductionEnvironment(array $cacheOptions = [], string $cacheDir = null): self;
    public function isEnvDev(): bool;
    public function isEnvTest(): bool;
    public function isEnvProd(): bool;
}