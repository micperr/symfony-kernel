<?php

namespace Micperr\SymfonyKernel;

trait VagrantTrait
{
    protected $memdir = '/dev/shm';

    /**
     * @return bool
     */
    protected function shouldMemoryCacheBeEnabled(): bool
    {
        return
            $this->isVagrantBox()
            && $this->isMemoryCacheSupported()
            && in_array($this->environment, [SuperKernel::ENV_DEV, SuperKernel::ENV_TEST], true)
        ;
    }

    protected function getMemoryCacheDir(): string
    {
        return sprintf('%s/%s/cache/%s', $this->memdir, $this->getContainerClass(), $this->environment);
    }

    protected function getMemoryLogDir(): string
    {
        return sprintf('%s/%s/logs', $this->memdir, $this->getContainerClass());
    }

    protected function isMemoryCacheSupported(): bool
    {
        return is_dir($this->memdir) && is_writable($this->memdir);
    }

    protected function isVagrantBox(): bool
    {
        return is_dir('/home/vagrant');
    }
}