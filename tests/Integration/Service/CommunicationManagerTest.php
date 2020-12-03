<?php

namespace OxidEsales\AwsS3Component\Tests\Integration\Service;

use OxidEsales\AwsS3Component\Service\S3ClientService;
use OxidEsales\AwsS3Component\Service\S3ClientServiceInterface;
use OxidEsales\AwsS3Component\Service\CommunicationManager;
use OxidEsales\AwsS3Component\Exception\BucketNameDoesNotExistException;
use OxidEsales\AwsS3Component\Exception\UploadFailedException;
use OxidEsales\AwsS3Component\Exception\FileDoesNotExistException;
use OxidEsales\AwsS3Component\Service\CommunicationManagerInterface;
use OxidEsales\EshopCommunity\Tests\Integration\Internal\ContainerTrait;
use OxidEsales\EshopCommunity\Tests\Integration\Internal\TestContainerFactory;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use PHPUnit\Framework\TestCase;

final class CommunicationManagerTest extends TestCase
{
    use ContainerTrait;

    /** @var CommunicationManager */
    private $communicationManager;

    /** @var string */
    private $imagePath = __DIR__ . '/Fixtures/test-file.txt';

    /** @var string */
    private $bucketName;

    /** @var string */
    private $destination = 'master/product/2/test-file.txt';

    protected function setUp(): void
    {
        parent::setUp();

        $container = (new TestContainerFactory())->create();
        $this->bucketName = $container->getParameter('aws.s3.image.bucket');

        $this->communicationManager = $this->get(CommunicationManagerInterface::class);
    }

    public function testPublishAnObject(): void
    {
        $result = $this->communicationManager->publish(
            $this->imagePath,
            $this->bucketName,
            CommunicationManager::ACL_PUBLIC_READ,
            $this->destination
        );

        $this->assertEquals(
            'https://' . $this->bucketName . '.s3.eu-central-1.amazonaws.com/' . $this->destination,
            $result
        );
    }

    public function testPublishAnObjectWithWrongBucket(): void
    {
        $this->expectException(BucketNameDoesNotExistException::class);

        $this->communicationManager->publish(
            $this->imagePath,
            'wrong-bucket',
            CommunicationManager::ACL_PUBLIC_READ,
            $this->destination
        );
    }

    public function testPublishAnObjectWithUploadFailedException(): void
    {
        $this->expectException(UploadFailedException::class);

        $this->communicationManager->publish(
            $this->imagePath,
            $this->bucketName,
            'wrong-ACL',
            $this->destination
        );
    }

    public function testRemoveAnObject(): void
    {
        $this->communicationManager->publish(
            $this->imagePath,
            $this->bucketName,
            CommunicationManager::ACL_PUBLIC_READ,
            $this->destination
        );

        $this->communicationManager->remove($this->destination, $this->bucketName);
    }

    public function testRemoveWithNoneExistenceObject(): void
    {
        $this->expectException(FileDoesNotExistException::class);

        $this->communicationManager->remove('wrong-file', $this->bucketName);
    }

    public function testObjectExistence(): void
    {
        $this->communicationManager->publish(
            $this->imagePath,
            $this->bucketName,
            CommunicationManager::ACL_PUBLIC_READ,
            $this->destination
        );

        $this->assertTrue($this->communicationManager->exists($this->destination, $this->bucketName));
    }

    public function testObjectExistenceWithWrongFile(): void
    {
        $this->assertFalse($this->communicationManager->exists('wrong-file', $this->bucketName));
    }
}
