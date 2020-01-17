<?php

declare(strict_types=1);

namespace Lemberg\Tests\Draft\Environment\Utility;

use Lemberg\Draft\Environment\Utility\Filesystem;
use org\bovigo\vfs\vfsStream;
use phpmock\phpunit\PHPMock;
use PHPUnit\Framework\TestCase;

/**
 * Tests Draft Environment file system utility class.
 *
 * @covers \Lemberg\Draft\Environment\Utility\Filesystem
 */
final class FilesystemTest extends TestCase {

  use PHPMock;

  /**
   * @var string
   */
  private $root;

  /**
   * @var \Lemberg\Draft\Environment\Utility\Filesystem
   */
  private $fs;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    // Mock source and target configuration directories.
    $this->root = vfsStream::setup()->url();
    $fs = new Filesystem();
    $fs->mkdir(["$this->root/wd"]);

    $this->fs = new Filesystem();
  }

  /**
   * Tests ::loadFile().
   */
  public function testLoadFile(): void {
    $expected = 'content';
    $filename = "$this->root/wd/file.txt";

    $this->fs->dumpFile($filename, $expected);
    self::assertSame($expected, $this->fs->loadFile('text file', $filename));
  }

  /**
   * Tests ::loadFile().
   *
   * @runInSeparateProcess
   * @preserveGlobalState disabled
   */
  public function testLoadFileThrowsExceptionWhenFileCannotBeRead(): void {
    $filename = 'text file';
    $filepath = "$this->root/wd/file-does-not-exists.txt";
    $this->fs->dumpFile($filepath, '');

    $fileGetContents = $this->getFunctionMock('Lemberg\Draft\Environment\Utility', 'file_get_contents');
    $fileGetContents->expects(self::once())->willReturn(FALSE);

    $this->expectException(\RuntimeException::class);
    $this->expectExceptionMessage(sprintf("Draft Environment Composer plugin was not able to read %s at '%s'", $filename, $filepath));

    $this->fs->loadFile($filename, $filepath);
  }

}
