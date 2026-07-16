<?php

declare(strict_types=1);

namespace NenePayout\Widget;

use RuntimeException;

/**
 * Raised when a widget token is missing, malformed, expired, wrongly scoped, or
 * has an invalid signature. Mapped to 401 by {@see WidgetTokenExceptionHandler}.
 */
final class WidgetTokenException extends RuntimeException
{
}
