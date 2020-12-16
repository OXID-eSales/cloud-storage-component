<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\AwsS3Component\Tests\Integration\Service;

use Aws\Credentials\Credentials;
use Aws\S3\S3Client;
use OxidEsales\AwsS3Component\Service\MasterImageHandler;
use OxidEsales\EshopCommunity\Internal\Framework\FileSystem\ImageHandlerInterface;
use OxidEsales\EshopCommunity\Tests\Integration\Internal\TestContainerFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

final class MasterImageHandlerTest extends TestCase
{
    /** @var string */
    private $source;
    /** @var string */
    private $destination;
    /** @var Filesystem */
    private $filesystem;
    /** @var ImageHandlerInterface */
    private $masterImageHandler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->initImageHandler();
        $this->prepareTestFiles();
    }

    protected function tearDown(): void
    {
        $this->cleanupTestFiles();

        parent::tearDown();
    }

    public function testUpload(): void
    {
        $this->assertFalse($this->masterImageHandler->exists($this->destination));

        $this->masterImageHandler->upload($this->source, $this->destination);

        $this->assertTrue($this->masterImageHandler->exists($this->destination));
        $this->assertFalse($this->filesystem->exists($this->source));
    }

    public function testCopy(): void
    {
        $this->assertFalse($this->masterImageHandler->exists($this->destination));

        $this->masterImageHandler->copy($this->source, $this->destination);

        $this->assertTrue($this->masterImageHandler->exists($this->destination));
    }

    public function testRemove(): void
    {
        $this->masterImageHandler->upload($this->source, $this->destination);
        $this->masterImageHandler->remove($this->destination);

        $this->assertFalse($this->masterImageHandler->exists($this->destination));
    }

    public function testExistsWithWrongFile(): void
    {
        $this->assertFalse($this->masterImageHandler->exists('wrong-file'));
    }

    private function initImageHandler(): void
    {
        $container = (new TestContainerFactory())->create();
        $this->filesystem = new Filesystem();
        $s3Client = new S3Client([
            'region' => $container->getParameter('aws.s3.region'),
            'version' => $container->getParameter('aws.s3.version'),
            'credentials' => (new Credentials(
                $container->getParameter('aws.s3.key'),
                $container->getParameter('aws.s3.secret')
            ))
        ]);
        $this->masterImageHandler = new MasterImageHandler(
            $s3Client,
            $this->filesystem,
            $container->getParameter('aws.s3.image.bucket'),
            $container->getParameter('aws.s3.image.bucket.acl')
        );
    }

    private function prepareTestFiles(): void
    {
        $this->source = uniqid(__DIR__ . '/Fixtures/test-file-', true) . '.txt';
        $this->filesystem->touch($this->source);
        $this->destination = uniqid('test-pictures/test-file-', true) . '.txt';
    }

    private function cleanupTestFiles(): void
    {
        $this->filesystem->remove($this->source);
        $this->masterImageHandler->remove($this->destination);
    }
}
