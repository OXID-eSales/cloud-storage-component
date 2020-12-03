<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\AwsS3Component\Service;

use OxidEsales\AwsS3Component\Exception\ConnectionFailedException;
use Aws\S3\S3Client;
use Aws\Credentials\Credentials;

class S3ClientService implements S3ClientServiceInterface
{
    /** @var string */
    private $key;

    /** @var string */
    private $secret;

    /** @var string */
    private $region;

    /** @var string */
    private $version;

    /**
     * @param string $key
     * @param string $secret
     * @param string $region
     * @param string $version
     */
    public function __construct(string $key, string $secret, string $region, string $version)
    {
        $this->key = $key;
        $this->secret = $secret;
        $this->region = $region;
        $this->version = $version;
    }

    /**
     * @return S3Client
     * @throws ConnectionFailedException
     */
    public function getS3Client(): S3Client
    {
        $credentials = new Credentials($this->key, $this->secret);

        try {
            $s3Client = new S3Client([
              'region'  => $this->region,
              'version' => $this->version,
              'credentials' => $credentials
            ]);

            $s3Client->listBuckets();
        } catch (\Throwable $e) {
            Throw new ConnectionFailedException("Connection to AWS S3 failed. Please check the credentials.");
        }

        return $s3Client;
    }
}
