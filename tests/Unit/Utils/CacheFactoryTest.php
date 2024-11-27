<?php

declare(strict_types=1);
namespace Test\Unit\Utils;

use App\Utils\CacheFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\FilesystemTagAwareAdapter;
use Symfony\Contracts\Cache\CacheInterface;

class CacheFactoryTest extends TestCase
{
    private string $projectDir;
    private string $env;

    protected function setUp(): void
    {
        $this->projectDir = sys_get_temp_dir().'/project';
        $this->env = 'test';

        if (!is_dir($this->projectDir.'/var/cache/test')) {
            mkdir($this->projectDir.'/var/cache/test', 0777, true);
        }
    }

    protected function tearDown(): void
    {
        if (is_dir($this->projectDir.'/var/cache/test')) {
            rmdir($this->projectDir.'/var/cache/test');
        }
    }

    public function testCreateReturnsFilesystemTagAwareAdapter(): void
    {
        $cache = CacheFactory::create($this->projectDir, $this->env);
        $this->assertInstanceOf(FilesystemTagAwareAdapter::class, $cache);
    }

    public function testCacheImplementsCacheInterface(): void
    {
        $cache = CacheFactory::create($this->projectDir, $this->env);
        $this->assertInstanceOf(CacheInterface::class, $cache);
    }
}
