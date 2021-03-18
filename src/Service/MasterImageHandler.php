<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\AwsS3Component\Service;

use OxidEsales\EshopCommunity\Internal\Framework\FileSystem\ImageHandlerInterface;
use Symfony\Component\Filesystem\Filesystem;
use League\Flysystem\Filesystem as ImageBucketFilesystem;

class MasterImageHandler implements ImageHandlerInterface
{
    /** @var Filesystem */
    private $filesystem;

    /** @var ImageBucketFilesystem */
    private $imageBucketFilesystem;

    /**
     * @param Filesystem                            $filesystem
     * @param ImageBucketFilesystemServiceInterface $imageBucketFilesystemService
     */
    public function __construct(
        Filesystem $filesystem,
        ImageBucketFilesystemServiceInterface $imageBucketFilesystemService
    ) {
        $this->filesystem = $filesystem;
        $this->imageBucketFilesystem = $imageBucketFilesystemService->getFilesystem();
    }

    /**
     * @param string $source
     * @param string $destination
     */
    public function upload(string $source, string $destination): void
    {
        $this->imageBucketFilesystem->write(
            $destination,
            file_get_contents($source)
        );

        $this->filesystem->remove($source);
    }

    /**
     * @param string $source
     * @param string $destination
     */
    public function copy(string $source, string $destination): void
    {
        $this->imageBucketFilesystem->write(
            $destination,
            file_get_contents($source)
        );
    }

    /**
     * @param string $path
     */
    public function remove(string $path): void
    {
        $this->imageBucketFilesystem->delete($path);
    }

    /**
     * @param string $path
     *
     * @return bool
     */
    public function exists(string $path): bool
    {
        return $this->imageBucketFilesystem->fileExists($path);
    }
}