<?php

namespace Drupal\Log;

use Psr\Log\LogLevel;
use Psr\Log\AbstractLogger;


/**
 * Provides a Drupal watchdog logger.
 *
 */
class WatchdogLogger extends AbstractLogger
{
    /**
     * The minimum level of logging to ignore. All events at or below this level
     * will be ignored. This is a LogLevel constant.
     *
     * @var string
     *
     */
    protected $ignore = null;


    /**
     * Constructor
     *
     * @param string $ignore_level
     *   The minimum level of logging to ignore. All events at or below this
     *   level will be ignored.
     *
     */
    public function __construct($ignore_level = null)
    {
        $this->ignore = $ignore_level;
    }


    /**
     * {@inheritdoc}
     *
     */
    public function log($level, $message, array $context = array())
    {
        $map = array(
              LogLevel::EMERGENCY => WATCHDOG_EMERGENCY,
              LogLevel::ALERT     => WATCHDOG_ALERT,
              LogLevel::CRITICAL  => WATCHDOG_CRITICAL,
              LogLevel::ERROR     => WATCHDOG_ERROR,
              LogLevel::WARNING   => WATCHDOG_WARNING,
              LogLevel::NOTICE    => WATCHDOG_NOTICE,
              LogLevel::INFO      => WATCHDOG_INFO,
              LogLevel::DEBUG     => WATCHDOG_DEBUG,
        );

        $ignore   = isset($map[$this->ignore]) ? $map[$this->ignore] : null;
        $severity = isset($map[$level]) ? $map[$level] : WATCHDOG_NOTICE;

        if (is_int($ignore) && $severity >= $ignore) {
          return;
        }

        // This is pretty hacky. Basically, we want to find the first thing that
        // isn't a logger, and assume that is the actual caller.
        $trace = debug_backtrace();
        $index = 1;

        foreach($trace as $key => $entry) {
            if (isset($entry['class'])) {
                $interfaces = class_implements($entry['class']);

                if (empty($interfaces['Psr\Log\LoggerInterface'])) {
                    $index = $key;
                    break;
                }
            }
        }

        $facility = $trace[$index]['function'];

        if (!empty($trace[$index]['class'])) {
            $facility = $trace[$index]['class'] . '::' . $facility;
        }

        // \Throwable is PHP 7+ only.
        $throwable_class = interface_exists('\Throwable') ? \Throwable::class : \Exception::class;

        if (isset($context['exception']) && $context['exception'] instanceof $throwable_class) {
            $exception = $context['exception'];
            unset($context['exception']);
            watchdog_exception($facility, $exception, $message, $context, $severity);
        }
        else {
            watchdog($facility, $message, $context, $severity);
        }
    }

}