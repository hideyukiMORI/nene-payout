<?php

declare(strict_types=1);

/**
 * Process bootstrap, wired via Composer `autoload.files`.
 *
 * Forces the process timezone to UTC so every ambient `date()` and stored
 * timestamp is a UTC instant (ADR 0012). User-facing output converts to JST.
 */

date_default_timezone_set('UTC');
