<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\AwsS3Component\Service;

use Aws\S3\S3Client;
use OxidEsales\EshopCommunity\Internal\Framework\FileSystem\ImageHandlerInterface;
use Symfony\Component\Filesystem\Filesystem;

class MasterImageHandler implements ImageHandlerInterface
{
    /** @var S3Client */
    private $s3Client;

    /** @var Filesystem */
    private $filesystem;

    /** @var array */
    private $imageBucketInfo;

    /**
     * @param S3Client   $s3Client
     * @param Filesystem $filesystem
     * @param array      $imageBucketInfo
     */
    public function __construct(
        S3Client $s3Client,
        Filesystem $filesystem,
        array $imageBucketInfo
    ) {
        $this->s3Client = $s3Client;
        $this->filesystem = $filesystem;
        $this->imageBucketInfo = $imageBucketInfo;
    }

    /**
     * @param string $source
     * @param string $destination
     */
    public function upload(string $source, string $destination): void
    {
        $this->putObject(
            $source,
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
        $this->putObject(
            $source,
            $destination
        );
    }

    /**
     * @param string $path
     */
    public function remove(string $path): void
    {
        $this->s3Client->deleteObject([
            'Bucket' => $this->imageBucketInfo['name'],
            'Key'    => $path
        ]);
    }

    /**
     * @param string $path
     *
     * @return bool
     */
    public function exists(string $path): bool
    {
        return $this->s3Client->doesObjectExist(
            $this->imageBucketInfo['name'],
            $path
        );
    }

    private function putObject(string $sourceFile, string $destinationInBucket): void
    {
        $object = [
            'Bucket' => $this->imageBucketInfo['name'],
            'Key'    => $destinationInBucket,
            'SourceFile' => $sourceFile,
            'ACL'    => $this->imageBucketInfo['acl'] ?? 'public-read',
        ];

        $this->s3Client->putObject($object);
    }
}