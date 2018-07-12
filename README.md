A simple PSR-3 implementation of a logger for Drupal watchdog calls.

# Details

The PSR-3 parameter ```$context``` is passed to watchdog as variables, for use as placeholders.

    class MyClass
    {
        public function myMethod($param)
        {
            $this->logger->warning('Param value was @param', ['@param' => $param]);

            return $param;
        }
    }

The watch dog type is set as the function or class method that called the logging code. In the example above, the watchdog type is set as *MyClass::myMethod*.

## Controlling log levels

Log levels below a specified level can be ignored, which may help reduce noise in production systems. When the class is initiated, an optional LogLevel may be provided. Events at or below the provided LogLevel will be ignored.
    $logger = new WatchdogLogger(LogLevel::NOTICE);

    // This debug message will be ignored.
    $logger->debug('Some debugging information');

By default, no events are ignored.

## Exception handling

Full logging of exceptions is supported via the `exception` key in the log context array:

    $exception = new \Exception('Example exception.');

    $logger->debug('An exception was thrown.', [
        'exception' => $exception,
    ]);

When used, the equivalent of Drupal's `watchdog_exception()` function will be used instead of `watchdog()`.
