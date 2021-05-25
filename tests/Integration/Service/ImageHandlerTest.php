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
use OxidEsales\EshopCommunity\Tests\Integration\Internal\TestContainerFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Filesystem\Filesystem as SymfonyFilesystem;

final class ImageHandlerTest extends TestCase
{
    /** @var string */
    private $sourceFile;
    /** @var string */
    private $destinationFile;
    /** @var SymfonyFilesystem */
    private $fixtures;
    /** @var string */
    private $fixturePath = __DIR__ . '/Fixtures';
    /** @var ImageHandlerInterface */
    private $imageHandler;
    /** @var ContainerBuilder */
    private $container;

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
        $this->assertFalse($this->imageHandler->exists($this->destinationFile));

        $this->imageHandler->upload($this->sourceFile, $this->destinationFile);

        $this->assertTrue($this->imageHandler->exists($this->destinationFile));
    }

    public function testUploadWillRemoveSourceFile(): void
    {
        $this->assertTrue($this->fixtures->exists($this->sourceFile));

        $this->imageHandler->upload($this->sourceFile, $this->destinationFile);

        $this->assertFalse($this->fixtures->exists($this->sourceFile));
    }

    public function testCopy(): void
    {
        $this->assertFalse($this->imageHandler->exists($this->destinationFile));

        $this->imageHandler->copy($this->sourceFile, $this->destinationFile);

        $this->assertTrue($this->imageHandler->exists($this->destinationFile));
    }

    public function testRemove(): void
    {
        $this->imageHandler->upload($this->sourceFile, $this->destinationFile);
        $this->imageHandler->remove($this->destinationFile);

        $this->assertFalse($this->imageHandler->exists($this->destinationFile));
    }

    public function testExistsWithWrongFile(): void
    {
        $this->assertFalse($this->imageHandler->exists('wrong-file'));
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
        $this->imageHandler->remove($this->destinationFile);
    }

    private function initImageHandler(): void
    {
        $this->switchFilesystemAdapterForTesting();
        $this->imageHandler = $this->container->get(ImageHandlerInterface::class);
    }

    private function switchFilesystemAdapterForTesting(): void
    {
        /** Use one of easy-configurable implementations of FilesystemAdapter available */
        $adapter = new LocalFilesystemAdapter("$this->fixturePath/destination/");
        $this->container = (new TestContainerFactory())->create();
        $this->container->set(FlyFilesystem::class, new FlyFilesystem($adapter));
        $this->container->compile();
    }
}
