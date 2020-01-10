<?php

declare(strict_types=1);

namespace Lemberg\Draft\Environment\Messanger;

/**
 * Collects and returns messages.
 */
trait MessangerTrait {

  /**
   * @var string[]
   */
  protected $messages = [];

  /**
   * @return string
   */
  final public function getMessages(): string {
    return implode("\n", $this->messages);
  }

  /**
   * @param string $message
   */
  final protected function addMessage(string $message): void {
    $this->messages[] = $message;
  }

}
