<?php

namespace OxidEsales\AwsS3Component\Tests\Unit\Service;

use OxidEsales\AwsS3Component\Service\S3ClientService;
use OxidEsales\AwsS3Component\Service\CommunicationManager;
use Aws\AwsClientInterface;
use Aws\MockHandler;
use Aws\Result;
use Aws\Sdk;
use PHPUnit\Framework\TestCase;

final class CommunicationManagerTest extends TestCase
{

    /** @var CommunicationManager */
    private $communicationManager;

    /** @var MockHandler */
    private $mockHandler;

    /** @var string */
    private $bucketName = 'sample-bucket';

    /** @var string */
    private $destination = 'sample-key';

    protected function setUp(): void
    {
        parent::setUp();

        $this->communicationManager = $this->getCommunicationManager();
    }

    public function testPublishAnObject(): void
    {
        $result = $this->communicationManager->publish(
            __DIR__ . '/../../Integration/Service/Fixtures/test-file.txt',
            $this->bucketName,
            CommunicationManager::ACL_PUBLIC_READ,
            $this->destination
        );

        $this->assertEquals(
            'https://' . $this->bucketName . '.s3.amazonaws.com/' . $this->destination,
            $result
        );

        $this->assertEquals(
            'PutObject',
            $this->mockHandler->getLastCommand()->getName()
        );

        $this->assertTrue($this->mockQueueEmpty());
    }

    public function testObjectExistence(): void
    {
        $result = $this->communicationManager->exists(
            $this->destination,
            $this->bucketName
        );

        $this->assertTrue($result);

        $this->assertEquals(
            '/' . $this->destination,
            $this->mockHandler->getLastRequest()->getUri()->getPath()
        );

        $this->assertTrue($this->mockQueueEmpty());
    }

    private function getCommunicationManager(): CommunicationManager
    {
        $s3Client = $this->getTestS3Client();

        $s3ClientService = $this->getMockBuilder(S3ClientService::class)
            ->setMethods(['getS3Client'])
            ->disableOriginalConstructor()
            ->getMock();
        $s3ClientService->method('getS3Client')->willReturn($s3Client);

        return $this->getMockBuilder(CommunicationManager::class)
            ->setMethods(['checkBucketExistence'])
            ->setConstructorArgs([$s3ClientService])
            ->getMock();
    }

    /**
     * @return AwsClientInterface
     */
    private function getTestS3Client(): AwsClientInterface
    {
        $this->mockHandler = new MockHandler([new Result()]);

        $sdk = new Sdk([
            'region'      => 'us-east-1',
            'version'     => 'latest',
            'retries'     => 0,
            'credentials' => [
                'key'    => 'sample-key',
                'secret' => 'sample-secret'
            ],
            'handler'     => $this->mockHandler
        ]);

        $s3Client = $sdk->createClient('S3');
        $s3Client->getHandlerList()->setHandler($this->mockHandler);

        $command = $s3Client->getCommand(
            'GetObject',
            [
                'Bucket' => $this->bucketName,
                'Key'    => $this->destination
            ]
        );

        $url = (string)$s3Client->createPresignedRequest($command, 123456789)->getUri();

        $this->assertStringStartsWith(
            'https://' . $this->bucketName . '.s3.amazonaws.com/' . $this->destination,
            $url
        );
        $this->stringContains('X-Amz-Expires=', $url);
        $this->stringContains('X-Amz-Credential=', $url);
        $this->stringContains('X-Amz-Signature=', $url);

        return $s3Client;
    }

    private function mockQueueEmpty(): bool
    {
        return 0 === count($this->mockHandler);
    }
}
