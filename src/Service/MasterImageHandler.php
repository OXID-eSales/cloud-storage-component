<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\AwsS3Component\Service;

use OxidEsales\EshopCommunity\Internal\Framework\FileSystem\MasterImageHandlerInterface;
use Symfony\Component\Filesystem\Filesystem;

class MasterImageHandler implements MasterImageHandlerInterface
{
    /** @var CommunicationManagerInterface */
    private $communicationManager;

    /** @var Filesystem */
    private $filesystem;

    /** @var string */
    private $imageBucketName;

    /**
     * @param CommunicationManagerInterface $communicationManager
     * @param Filesystem                    $filesystem
     * @param string                        $imageBucketName
     */
    public function __construct(
        CommunicationManagerInterface $communicationManager,
        Filesystem $filesystem,
        string $imageBucketName
    ) {
        $this->communicationManager = $communicationManager;
        $this->filesystem = $filesystem;
        $this->imageBucketName = $imageBucketName;
    }

    /**
     * @param string $source
     * @param string $destination
     */
    public function upload(string $source, string $destination): void
    {
        $this->communicationManager->publish(
            $source,
            $this->imageBucketName,
            CommunicationManager::ACL_PUBLIC_READ,
            $destination
        );

        $this->filesystem->remove($source);
    }

    /**
     * @param string $source
     * @param string $destination
     */
    public function copy(string $source, string $destination): void
    {
        $this->communicationManager->publish(
            $source,
            $this->imageBucketName,
            CommunicationManager::ACL_PUBLIC_READ,
            $destination
        );
    }

    /**
     * @param string $path
     */
    public function remove(string $path): void
    {
        $this->communicationManager->remove($path, $this->imageBucketName);
    }

    /**
     * @param string $path
     *
     * @return bool
     */
    public function exists(string $path): bool
    {
        return $this->communicationManager->exists($path, $this->imageBucketName);
    }
}
