<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\AwsS3Component\Tests\Integration\Service;

use League\Flysystem\Filesystem as FlyFilesystem;
use League\Flysystem\Local\LocalFilesystemAdapter;
use OxidEsales\EshopCommunity\Internal\Framework\FileSystem\ImageHandlerInterface;
use OxidEsales\EshopCommunity\Tests\Integration\Internal\ContainerTrait;
use OxidEsales\EshopCommunity\Tests\Integration\Internal\TestContainerFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem as SymfonyFilesystem;

final class MasterImageHandlerTest extends TestCase
{
    use ContainerTrait;

    /** @var string */
    private $sourceFile;
    /** @var string */
    private $destinationFile;
    /** @var SymfonyFilesystem */
    private $fixtures;
    /** @var string */
    private $fixturePath = __DIR__ . '/Fixtures';
    /** @var ImageHandlerInterface */
    private $masterImageHandler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->initImageHandler();
        $this->prepareFixtureFiles();
    }

    protected function tearDown(): void
    {
        $this->clearFixtureFiles();

        parent::tearDown();
    }

    public function testUpload(): void
    {
        $this->assertFalse($this->masterImageHandler->exists($this->destinationFile));

        $this->masterImageHandler->upload($this->sourceFile, $this->destinationFile);

        $this->assertTrue($this->masterImageHandler->exists($this->destinationFile));
    }

    public function testUploadWillRemoveSourceFile(): void
    {
        $this->assertTrue($this->fixtures->exists($this->sourceFile));

        $this->masterImageHandler->upload($this->sourceFile, $this->destinationFile);

        $this->assertFalse($this->fixtures->exists($this->sourceFile));
    }

    public function testCopy(): void
    {
        $this->assertFalse($this->masterImageHandler->exists($this->destinationFile));

        $this->masterImageHandler->copy($this->sourceFile, $this->destinationFile);

        $this->assertTrue($this->masterImageHandler->exists($this->destinationFile));
    }

    public function testRemove(): void
    {
        $this->masterImageHandler->upload($this->sourceFile, $this->destinationFile);
        $this->masterImageHandler->remove($this->destinationFile);

        $this->assertFalse($this->masterImageHandler->exists($this->destinationFile));
    }

    public function testExistsWithWrongFile(): void
    {
        $this->assertFalse($this->masterImageHandler->exists('wrong-file'));
    }

    private function prepareFixtureFiles(): void
    {
        $this->fixtures = new SymfonyFilesystem();
        $this->sourceFile = uniqid("$this->fixturePath/source/source-file-", true);
        $this->fixtures->touch($this->sourceFile);
        $this->destinationFile = uniqid('destination-file-', true);
    }

    private function clearFixtureFiles(): void
    {
        $this->fixtures->remove($this->sourceFile);
        $this->masterImageHandler->remove($this->destinationFile);
    }

    private function initImageHandler(): void
    {
        $this->switchFilesystemAdapterForTesting();
        $this->masterImageHandler = $this->get(ImageHandlerInterface::class);
    }

    private function switchFilesystemAdapterForTesting(): void
    {
        if ($this->container === null) {
            /** Use one of easy-configurable implementations of FilesystemAdapter available */
            $adapter = new LocalFilesystemAdapter("$this->fixturePath/destination/");
            $this->container = (new TestContainerFactory())->create();
            $this->container->set(FlyFilesystem::class, new FlyFilesystem($adapter));
            $this->container->compile();
        }
    }
}
