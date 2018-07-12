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
            $this->logThrowable($facility, $exception, $message, $context, $severity);
        } else {
            $this->watchdog($facility, $message, $context, $severity);
        }
    }


    /**
     * Logs a throwable (exception or error) using watchdog.
     *
     * This is equivalent to Drupal's watchdog_exception() with added support
     * for PHP 7's throwables.
     *
     * @param string $type
     *   The category to which this message belongs.
     * @param \Throwable|\Exception $throwable
     *   The throwable that is going to be logged.
     * @param string|null $message
     *   The message to store in the log. If empty, a text that contains all useful
     *   information about the passed-in exception is used.
     * @param array $variables
     *   Array of variables to replace in the message on display. Defaults to the
     *   return value of _drupal_decode_exception().
     * @param int $severity
     *   The severity of the message, as per RFC 3164.
     * @param string $link
     *   A link to associate with the message.
     *
     */
    protected function logThrowable($type, $exception, $message = null, $variables = [], $severity = WATCHDOG_ERROR, $link = null)
    {
        // Use a default value if $message is not set.
        if (empty($message)) {
            // The exception message is run through check_plain() by _drupal_decode_exception().
            $message = '%type: !message in %function (line %line of %file).';
        }

        // $variables must be an array so that we can add the exception information.
        if (!is_array($variables)) {
            $variables = array();
        }

        require_once DRUPAL_ROOT . '/includes/errors.inc';
        $variables += _drupal_decode_exception($exception);

        $this->watchdog($type, $message, $variables, $severity, $link);
    }


    /**
     * Logs a message using Watchdog.
     *
     * This is mostly just a wrapper for the watchdog() function.
     *
     * @param string $type
     *   The category to which this message belongs. Can be any string, but the
     *   general practice is to use the name of the module calling watchdog().
     * @param string $message
     *   The message to store in the log. Keep $message translatable
     *   by not concatenating dynamic values into it! Variables in the
     *   message should be added by using placeholder strings alongside
     *   the variables argument to declare the value of the placeholders.
     *   See t() for documentation on how $message and $variables interact.
     * @param array $variables
     *   Array of variables to replace in the message on display or
     *   NULL if message is already translated or not possible to
     *   translate.
     * @param int $severity
     *   The severity of the message; one of the following values as defined in
     *   RFC 3164.
     *   - WATCHDOG_EMERGENCY: Emergency, system is unusable.
     *   - WATCHDOG_ALERT: Alert, action must be taken immediately.
     *   - WATCHDOG_CRITICAL: Critical conditions.
     *   - WATCHDOG_ERROR: Error conditions.
     *   - WATCHDOG_WARNING: Warning conditions.
     *   - WATCHDOG_NOTICE: (default) Normal but significant conditions.
     *   - WATCHDOG_INFO: Informational messages.
     *   - WATCHDOG_DEBUG: Debug-level messages.
     * @param string $link
     *   A link to associate with the message.
     *
     */
    protected function watchdog($type, $message, $variables = [], $severity = WATCHDOG_NOTICE, $link = null)
    {
        watchdog($type, $message, $variables, $severity, $link);
    }
}