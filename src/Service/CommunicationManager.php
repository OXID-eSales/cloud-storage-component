<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\AwsS3Component\Service;

use OxidEsales\AwsS3Component\Exception\FileDoesNotExistException;
use OxidEsales\AwsS3Component\Exception\UploadFailedException;
use OxidEsales\AwsS3Component\Exception\BucketNameDoesNotExistException;
use OxidEsales\AwsS3Component\Exception\DeletingFileFailedException;
use OxidEsales\AwsS3Component\Exception\ConnectionFailedException;
use OxidEsales\AwsS3Component\Service\S3ClientServiceInterface;
use Aws\S3\S3Client;
use Aws\S3\Exception\S3Exception;
use Webmozart\PathUtil\Path;

class CommunicationManager implements CommunicationManagerInterface
{
    public const ACL_PUBLIC_READ = 'public-read';

    /** @var S3Client */
    private $s3Client;

    /**
     * @param S3ClientServiceInterface $s3ClientService
     */
    public function __construct(S3ClientServiceInterface $s3ClientService)
    {
        $this->s3Client = $s3ClientService->getS3Client();
    }

    /**
     * @param string      $file
     * @param string      $bucketName
     * @param string      $acl
     * @param string|null $destination
     *
     * @return string
     * @throws UploadFailedException
     */
    public function publish(string $file, string $bucketName, string $acl, string $destination = null): string
    {
        $this->checkBucketExistence($bucketName);

        $fileName = Path::getFilename($file);
        $destination = $destination ?? $fileName;

        $object = [
            'Bucket' => $bucketName,
            'Key'    => $destination,
            'Body'   => fopen($file, 'r'),
            'ACL'    => $acl,
        ];

        try {
            $result = $this->s3Client->putObject($object);
        } catch (S3Exception $e) {
            Throw new UploadFailedException("There was an error uploading the file '{$file}'");
        }

        return $result->get('ObjectURL');
    }

    /**
     * @param string $filePath
     * @param string $bucketName
     *
     * @throws DeletingFileFailedException
     * @throws FileDoesNotExistException
     */
    public function remove(string $filePath, string $bucketName): void
    {
        if (!$this->exists($filePath, $bucketName)) {
            throw new FileDoesNotExistException('The file does not exist');
        }

        try {
            $result = $this->s3Client->deleteObject([
                'Bucket' => $bucketName,
                'Key'    => $filePath
            ]);

            if ($this->exists($filePath, $bucketName)) {
                throw new DeletingFileFailedException('Could not remove the file');
            }
        } catch (S3Exception $e) {
            throw new DeletingFileFailedException('Could not remove the file');
        }
    }

    /**
     * @param string $filePath
     * @param string $bucketName
     *
     * @return bool
     * @throws ConnectionFailedException
     */
    public function exists(string $filePath, string $bucketName): bool
    {
        $this->checkBucketExistence($bucketName);

        try
        {
            $result = $this->s3Client->doesObjectExist(
                $bucketName,
                $filePath
            );
        } catch (S3Exception $e) {
            throw new ConnectionFailedException("Could not reach to the file");
        }

        return $result;
    }

    /**
     * @param string $bucketName
     *
     * @throws BucketNameDoesNotExistException
     */
    public function checkBucketExistence(string $bucketName): void
    {
        $ExistingBucketNames = $this->s3Client->listBuckets()->search('Buckets[].Name');

        if(!in_array($bucketName, $ExistingBucketNames)) {
            Throw new BucketNameDoesNotExistException("Bucket '{$bucketName}' does not exist");
        }
    }
}
