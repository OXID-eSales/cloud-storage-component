<?php

namespace OxidEsales\AwsS3Component\Tests\Integration\Service;

use OxidEsales\AwsS3Component\Service\MasterImageHandler;
use OxidEsales\EshopCommunity\Tests\Integration\Internal\TestContainerFactory;
use Symfony\Component\Filesystem\Filesystem;
use Aws\S3\Exception\S3Exception;
use Aws\Credentials\Credentials;
use Aws\S3\S3Client;
use PHPUnit\Framework\TestCase;

final class MasterImageHandlerTest extends TestCase
{
    /** @var S3Client */
    private $s3Client;

    /** @var string */
    private $testFilePath = __DIR__ . '/Fixtures/test-file.txt';

    /** @var string */
    private $bucketName;

    /** @var string */
    private $destination = 'tets-picture/test-file.txt';

    /** @var string */
    private $acl;

    /** @var Filesystem */
    private $filesystem;

    protected function setUp(): void
    {
        parent::setUp();

        $container = (new TestContainerFactory())->create();
        $this->bucketName = $container->getParameter('aws.s3.image.bucket');
        $this->acl = $container->getParameter('aws.s3.image.bucket.acl');

        $credentials = new Credentials(
            $container->getParameter('aws.s3.key'),
            $container->getParameter('aws.s3.secret')
        );

        $this->s3client = new S3Client([
            'region'  => $container->getParameter('aws.s3.region'),
            'version' => $container->getParameter('aws.s3.version'),
            'credentials' => $credentials
        ]);

        $this->filesystem = new Filesystem();
        $this->filesystem->touch($this->testFilePath);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->filesystem->remove($this->testFilePath);
    }

    public function testUpload(): void
    {
        $masterImageHandler = new MasterImageHandler(
            $this->s3client,
            $this->filesystem,
            $this->bucketName,
            $this->acl
        );

        $masterImageHandler->upload(
            $this->testFilePath,
            $this->destination
        );

        self::assertFalse($this->filesystem->exists($this->testFilePath));
    }

    public function testCopy(): void
    {
        $masterImageHandler = new MasterImageHandler(
            $this->s3client,
            $this->filesystem,
            $this->bucketName,
            $this->acl
        );

        $masterImageHandler->copy(
            $this->testFilePath,
            $this->destination
        );

        self::assertTrue($this->filesystem->exists($this->testFilePath));
    }

    public function testUploadWithWrongBucket(): void
    {
        $masterImageHandler = new MasterImageHandler(
            $this->s3client,
            $this->filesystem,
            'wrongBucket',
            $this->acl
        );

        self::expectException(S3Exception::class);

        $masterImageHandler->upload(
            $this->testFilePath,
            $this->destination
        );
    }

    public function testUploadWithWrongFile(): void
    {
        $masterImageHandler = new MasterImageHandler(
            $this->s3client,
            $this->filesystem,
            $this->bucketName,
            $this->acl
        );

        self::expectException(\RuntimeException::class);

        $masterImageHandler->upload(
            'wrong-file',
            $this->destination
        );
    }

    public function testUploadWithWrongAcl(): void
    {
        $masterImageHandler = new MasterImageHandler(
            $this->s3client,
            $this->filesystem,
            $this->bucketName,
            'wrong-acl'
        );

        self::expectException(S3Exception::class);

        $masterImageHandler->upload(
            $this->testFilePath,
            $this->destination
        );
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testRemove(): void
    {
        $masterImageHandler = new MasterImageHandler(
            $this->s3client,
            $this->filesystem,
            $this->bucketName,
            $this->acl
        );

        $masterImageHandler->upload(
            $this->testFilePath,
            $this->destination
        );

        $masterImageHandler->remove($this->destination);
    }

    public function testRemoveWithWrongBucket(): void
    {
        $masterImageHandler = new MasterImageHandler(
            $this->s3client,
            $this->filesystem,
            'wrongBucket',
            $this->acl
        );

        self::expectException(S3Exception::class);

        $masterImageHandler->remove($this->destination);
    }

    public function testExists(): void
    {
        $masterImageHandler = new MasterImageHandler(
            $this->s3client,
            $this->filesystem,
            $this->bucketName,
            $this->acl
        );

        $masterImageHandler->upload(
            $this->testFilePath,
            $this->destination
        );

        self::assertTrue($masterImageHandler->exists($this->destination));
    }

    public function testExistsWithWrongFile(): void
    {
        $masterImageHandler = new MasterImageHandler(
            $this->s3client,
            $this->filesystem,
            $this->bucketName,
            $this->acl
        );

        self::assertFalse($masterImageHandler->exists('wrong-file'));
    }

    public function testExistsWithWrongBucket(): void
    {
        $masterImageHandler = new MasterImageHandler(
            $this->s3client,
            $this->filesystem,
            'wrongBucket',
            $this->acl
        );

        self::assertFalse($masterImageHandler->exists($this->destination));
    }
}
