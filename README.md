A collection of simple PSR-3 implementations of loggers for Drupal watchdog and Drush log calls.

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

The watchdog type is set as the function or class method that called the logging code. In the example above, the watchdog type is set as *MyClass::myMethod*.

## Controlling log levels

Log levels below a specified level can be ignored, which may help reduce noise in production systems. When the class is initiated, an optional LogLevel may be provided. Events at or below the provided LogLevel will be ignored.
    $logger = new WatchdogLogger(LogLevel::NOTICE);

    // This debug message will be ignored.
    $logger->debug('Some debugging information');

By default, no events are ignored.
