<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\AwsS3Component\Service;

use League\Flysystem\Filesystem as ExternalFilesystem;
use OxidEsales\EshopCommunity\Internal\Framework\FileSystem\ImageHandlerInterface;
use Symfony\Component\Filesystem\Filesystem as LocalFilesystem;

class ImageHandler implements ImageHandlerInterface
{
    /** @var LocalFilesystem */
    private $localFilesystem;

    /** @var ExternalFilesystem */
    private $externalFilesystem;

    /**
     * @param LocalFilesystem $filesystem
     * @param ExternalFilesystem $externalFilesystem
     */
    public function __construct(
        LocalFilesystem $filesystem,
        ExternalFilesystem $externalFilesystem
    ) {
        $this->localFilesystem = $filesystem;
        $this->externalFilesystem = $externalFilesystem;
    }

    /**
     * @param string $source
     * @param string $destination
     */
    public function upload(string $source, string $destination): void
    {
        $this->externalFilesystem->write(
            $destination,
            file_get_contents($source)
        );

        $this->localFilesystem->remove($source);
    }

    /**
     * @param string $source
     * @param string $destination
     */
    public function copy(string $source, string $destination): void
    {
        $this->externalFilesystem->write(
            $destination,
            file_get_contents($source)
        );
    }

    /**
     * @param string $path
     */
    public function remove(string $path): void
    {
        $this->externalFilesystem->delete($path);
    }

    /**
     * @param string $path
     *
     * @return bool
     */
    public function exists(string $path): bool
    {
        return $this->externalFilesystem->fileExists($path);
    }
}
