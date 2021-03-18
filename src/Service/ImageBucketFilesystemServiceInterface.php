<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\AwsS3Component\Service;

use League\Flysystem\Filesystem;

interface ImageBucketFilesystemServiceInterface
{
    /**
     * @return Filesystem
     */
    public function getFilesystem(): Filesystem;
}