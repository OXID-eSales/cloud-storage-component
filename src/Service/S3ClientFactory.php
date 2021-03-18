<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\AwsS3Component\Service;

use Aws\S3\S3Client;
use Aws\Credentials\Credentials;

class S3ClientFactory
{
    /** @var array */
    private $configs;

    /**
     * @param array $configs
     */
    public function __construct(array $configs)
    {
        $this->configs = $configs;
    }

    /**
     * @return S3Client
     */
    public function getS3Client(): S3Client
    {
        $this->setCredentials();

        return new S3Client($this->configs);
    }

    private function setCredentials(): void
    {
        if (array_key_exists('credentials', $this->configs)) {
            $credentials = $this->configs['credentials'];

            $this->configs['credentials'] = new Credentials(
                $credentials['key'],
                $credentials['secret'],
                $credentials['token'] ?? null,
                $credentials['expires'] ?? null
            );
        }
    }
}
