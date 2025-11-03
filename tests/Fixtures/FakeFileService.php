<?php

declare(strict_types=1);

namespace Tourze\SiliconFlowBundle\Tests\Fixtures;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\UserInterface;
use Tourze\FileStorageBundle\Entity\File;
use Tourze\FileStorageBundle\Entity\Folder;

/**
 * 测试专用的FileService替身，避免依赖注入失败
 */
final class FakeFileService
{
    public function uploadFile(UploadedFile $uploadedFile, ?UserInterface $user, ?Request $request = null, ?Folder $folder = null): File
    {
        // 创建一个简单的FakeFile对象作为返回值
        // 在测试环境中不进行真实的文件上传操作
        return new FakeFile();
    }
}