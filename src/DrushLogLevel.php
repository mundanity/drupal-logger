<?php

namespace Drupal\Log;

use Psr\Log\LogLevel;


/**
 * Provides additional log levels that Drush uses for historical reasons.
 *
 * Standard log levels should be preferred.
 *
 * @see Drush\Log\LogLevel
 *
 */
class DrushLogLevel extends LogLevel
{
    // Things that happen early on.  Like 'notice'
    const BOOTSTRAP = 'bootstrap';
    const PREFLIGHT = 'preflight';

    // Notice that the user is cancelling an operation. Like 'warning'
    const CANCEL = 'cancel';

    // Various 'success' messages.  Like 'notice'
    const OK = 'ok';

    // Means the command was successful. Should appear at most once
    // per command (perhaps more if subcommands are executed, though).
    // Like 'notice'.
    const SUCCESS = 'success';

    // Batch processes. Like 'notice'
    const BATCH = 'batch';
}