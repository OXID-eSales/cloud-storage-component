<?php

namespace OxidEsales\AwsS3Component\Tests\Integration\Service;

use OxidEsales\AwsS3Component\Service\S3ClientService;
use OxidEsales\AwsS3Component\Exception\ConnectionFailedException;
use OxidEsales\AwsS3Component\Service\S3ClientServiceInterface;
use OxidEsales\EshopCommunity\Tests\Integration\Internal\ContainerTrait;
use PHPUnit\Framework\TestCase;

final class S3ClientServiceTest extends TestCase
{
    use ContainerTrait;

    public function testGetS3Client(): void
    {
        $s3Client = $this->get(S3ClientServiceInterface::class);
        $s3Client->getS3Client();
    }

    public function testGetS3ClientWithWrongCredentials(): void
    {
        $key = 'wrong-key';
        $secret = 'wrong-secret';
        $region = 'eu-central-1';
        $version = 'latest';

        $s3Client = new S3ClientService($key, $secret, $region, $version);

        $this->expectException(ConnectionFailedException::class);

        $s3Client->getS3Client();
    }
}
