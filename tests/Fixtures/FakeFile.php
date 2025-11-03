<?php

declare(strict_types=1);

namespace Tourze\SiliconFlowBundle\Tests\Fixtures;

use Tourze\FileStorageBundle\Entity\File;

/**
 * 测试专用的File实体替身
 */
final class FakeFile extends File
{
    public function getPublicUrl(): ?string
    {
        return 'http://test.localhost/fake-file.jpg';
    }

    public function getUrl(): ?string
    {
        return 'http://test.localhost/fake-file.jpg';
    }
}