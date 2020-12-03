<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\AwsS3Component\Service;

use OxidEsales\AwsS3Component\Exception\ConnectionFailedException;
use Aws\S3\S3Client;

interface S3ClientServiceInterface
{
    /**
     * @return S3Client
     * @throws ConnectionFailedException
     */
    public function getS3Client(): S3Client;
}
