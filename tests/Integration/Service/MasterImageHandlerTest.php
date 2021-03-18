<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\AwsS3Component\Tests\Integration\Service;

use OxidEsales\AwsS3Component\Service\MasterImageHandler;
use OxidEsales\AwsS3Component\Service\S3ClientService;
use OxidEsales\EshopCommunity\Internal\Framework\FileSystem\ImageHandlerInterface;
use OxidEsales\EshopCommunity\Tests\Integration\Internal\ContainerTrait;
use OxidEsales\EshopCommunity\Tests\Integration\Internal\TestContainerFactory;
use Symfony\Component\Filesystem\Filesystem;
use PHPUnit\Framework\TestCase;

final class MasterImageHandlerTest extends TestCase
{
    use ContainerTrait;

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

        $this->filesystem = new Filesystem();
        $this->masterImageHandler = $this->get(ImageHandlerInterface::class);

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
