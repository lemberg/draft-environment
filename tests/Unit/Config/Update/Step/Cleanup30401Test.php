<?php

declare(strict_types=1);

namespace Lemberg\Tests\Unit\Draft\Environment\Config\Update\Step;

use Lemberg\Draft\Environment\Config\Update\Step\Cleanup30401;

/**
 * Tests updating PHP configuration.
 *
 * @covers \Lemberg\Draft\Environment\Config\Update\Step\AbstractUpdateStep
 * @covers \Lemberg\Draft\Environment\Config\Update\Step\Cleanup30401
 */
final class Cleanup30401Test extends Cleanup30400Test {

  /**
   * {@inheritdoc}
   */
  final public function testGetWeight(): void {
    $step = new Cleanup30401($this->composer, $this->io, $this->configUpdateManager);
    self::assertSame(11, $step->getWeight());
  }

  /**
   * Tests update step execution.
   *
   * @param array<string,mixed> $config
   * @param array<string,mixed> $expectedConfig
   *
   * @dataProvider updateDataProvider
   */
  final public function testUpdate(array $config, array $expectedConfig): void {
    $step = new Cleanup30401($this->composer, $this->io, $this->configUpdateManager);

    $step->update($config);
    self::assertSame($expectedConfig, $config);
  }

  /**
   * Data provider for the ::testUpdate().
   *
   * @return array<int,array<int,string|array<string,mixed>>>
   */
  public function updateDataProvider(): array {
    $data = parent::updateDataProvider();
    $data[] = [
      [
        'mysql_sql_mode' => '~',
      ],
      [
        'mysql_sql_mode' => NULL,
      ],
    ];

    return $data;
  }

}
