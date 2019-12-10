<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\ORM\Loggable;

use Psr\Log\LoggerInterface;

final class LoggerCallable
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(?LoggerInterface $logger = null)
    {
        $this->logger = $logger;
    }

    public function __invoke($message): void
    {
        $this->logger->debug($message);
    }
}
