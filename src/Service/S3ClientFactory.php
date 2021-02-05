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
     */
    public function getS3Client(): S3Client
    {
        // Use the default credentials provider chain when credentials are set to null.
        // https://docs.aws.amazon.com/sdk-for-php/v3/developer-guide/guide_credentials.html
        $credentials = null;

        // Initialize the credentials only when both key & secret are provided through
        // the var/configuration/configurable_services.yaml file. Setting this overrides
        // the default credentials provider.
        if ($this->key != '' && $this->secret != '')
        {
            $credentials = new Credentials($this->key, $this->secret);
        }

        return new S3Client([
          'region'  => $this->region,
          'version' => $this->version,
          'credentials' => $credentials
        ]);
    }
}
