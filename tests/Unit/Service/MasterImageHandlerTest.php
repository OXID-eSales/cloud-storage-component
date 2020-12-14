<?php

namespace OxidEsales\AwsS3Component\Tests\Unit\Service;

use OxidEsales\AwsS3Component\Service\MasterImageHandler;
use Aws\AwsClientInterface;
use Aws\MockHandler;
use Aws\Result;
use Aws\Sdk;
use Symfony\Component\Filesystem\Filesystem;
use PHPUnit\Framework\TestCase;

final class MasterImageHandlerTest extends TestCase
{
    /** @var MasterImageHandler */
    private $masterImageHandler;

    /** @var MockHandler */
    private $mockHandler;

    /** @var string */
    private $bucketName = 'sample-bucket';

    /** @var string */
    private $destination = 'sample-key';

    /** @var string */
    private $testFilePath = __DIR__ . '/../../Integration/Service/Fixtures/test-file.txt';

    protected function setUp(): void
    {
        parent::setUp();

        $this->masterImageHandler = $this->getMasterImageHandler();

        (new Filesystem())->touch($this->testFilePath);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        (new Filesystem())->remove($this->testFilePath);
    }

    public function testUpload(): void
    {
        $this->masterImageHandler->upload(
            $this->testFilePath,
            $this->destination
        );

        $this->assertEquals(
            'PutObject',
            $this->mockHandler->getLastCommand()->getName()
        );
    }

    public function testCopy(): void
    {
        $result = $this->masterImageHandler->copy(
            $this->testFilePath,
            $this->destination
        );

        $this->assertEquals(
            'PutObject',
            $this->mockHandler->getLastCommand()->getName()
        );
    }

    public function testRemove(): void
    {
        $this->masterImageHandler->remove($this->destination);

        $this->assertEquals(
            'DeleteObject',
            $this->mockHandler->getLastCommand()->getName()
        );
    }

    public function testExists(): void
    {
        $this->masterImageHandler->exists($this->destination);

        $this->assertEquals(
            'HeadObject',
            $this->mockHandler->getLastCommand()->getName()
        );
    }

    private function getMasterImageHandler(): MasterImageHandler
    {
        $s3Client = $this->getTestS3Client();

        return new MasterImageHandler(
            $s3Client,
            new Filesystem(),
            'sampleBucket',
            'private'
        );
    }

    /**
     * @return AwsClientInterface
     */
    private function getTestS3Client(): AwsClientInterface
    {
        $sdk = new Sdk([
            'region'      => 'us-east-1',
            'version'     => 'latest',
            'retries'     => 0,
            'credentials' => [
                'key'    => 'sample-key',
                'secret' => 'sample-secret'
            ]
        ]);

        $s3Client = $sdk->createClient('S3');
        $this->mockHandler = new MockHandler([new Result()]);
        $s3Client->getHandlerList()->setHandler($this->mockHandler);

        return $s3Client;
    }
}
