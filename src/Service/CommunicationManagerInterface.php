<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\AwsS3Component\Service;

use OxidEsales\AwsS3Component\Exception\BucketNameDoesNotExistException;
use OxidEsales\AwsS3Component\Exception\ConnectionFailedException;
use OxidEsales\AwsS3Component\Exception\DeletingFileFailedException;
use OxidEsales\AwsS3Component\Exception\FileDoesNotExistException;
use OxidEsales\AwsS3Component\Exception\UploadFailedException;

interface CommunicationManagerInterface
{
    /**
     * @param string      $file
     * @param string      $bucketName
     * @param string      $acl
     * @param string|null $destination
     *
     * @return string
     * @throws UploadFailedException
     */
    public function publish(string $file, string $bucketName, string $acl, string $destination = null): string;

    /**
     * @param string $filePath
     * @param string $bucketName
     *
     * @throws DeletingFileFailedException
     * @throws FileDoesNotExistException
     */
    public function remove(string $filePath, string $bucketName): void;

    /**
     * @param string $filePath
     * @param string $bucketName
     *
     * @return bool
     * @throws ConnectionFailedException
     */
    public function exists(string $filePath, string $bucketName): bool;

    /**
     * @param string $bucketName
     *
     * @throws BucketNameDoesNotExistException
     */
    public function checkBucketExistence(string $bucketName): void;
}
