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

    /** @var string */
    private $imageBucketName;

    /** @var string */
    private $imageBucketAcl;

    /**
     * @param S3Client   $s3Client
     * @param Filesystem $filesystem
     * @param string     $imageBucketName
     * @param string     $imageBucketAcl
     */
    public function __construct(
        S3Client $s3Client,
        Filesystem $filesystem,
        string $imageBucketName,
        string $imageBucketAcl
    ) {
        $this->s3Client = $s3Client;
        $this->filesystem = $filesystem;
        $this->imageBucketName = $imageBucketName;
        $this->imageBucketAcl = $imageBucketAcl;
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
            'Bucket' => $this->imageBucketName,
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
            $this->imageBucketName,
            $path
        );
    }

    private function putObject(string $sourceFile, string $destinationInBucket): void
    {
        $object = [
            'Bucket' => $this->imageBucketName,
            'Key'    => $destinationInBucket,
            'SourceFile' => $sourceFile,
            'ACL'    => $this->imageBucketAcl,
        ];

        $this->s3Client->putObject($object);
    }
}