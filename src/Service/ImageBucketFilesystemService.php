<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\AwsS3Component\Service;

use Aws\S3\S3Client;
use League\Flysystem\AwsS3V3\AwsS3V3Adapter;
use League\Flysystem\Filesystem;

class ImageBucketFilesystemService implements ImageBucketFilesystemServiceInterface
{
    /** @var S3Client */
    private $s3Client;

    /** @var string */
    private $imageBucketName;

    /**
     * @param S3ClientServiceInterface $s3ClientService
     * @param string                   $imageBucketName
     */
    public function __construct(S3ClientServiceInterface $s3ClientService, string $imageBucketName)
    {
        $this->s3Client = $s3ClientService->getS3Client();
        $this->imageBucketName = $imageBucketName;
    }

    /**
     * @return Filesystem
     */
    public function getFilesystem(): Filesystem
    {
        $imageBucketAdapter = new AwsS3V3Adapter($this->s3Client, $this->imageBucketName);

        return new Filesystem($imageBucketAdapter, ['visibility' => 'public']) ;
    }
}