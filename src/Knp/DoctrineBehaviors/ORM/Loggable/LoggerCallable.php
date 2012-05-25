<?php

/**
 * This file is part of the KnpDoctrineBehaviors package.
 *
 * (c) KnpLabs <http://knplabs.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Knp\DoctrineBehaviors\ORM\Loggable;

use Symfony\Component\HttpKernel\Log\LoggerInterface;

/**
 * LoggerCallable can be invoked to log messages using symfony2 logger
 */
class LoggerCallable
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @constructor
     *
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function __invoke($message)
    {
        $this->logger->log($message);
    }
}
