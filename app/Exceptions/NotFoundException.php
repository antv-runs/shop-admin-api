<?php

namespace App\Exceptions;

use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Represents a business-level not found error.
 * Extends the Eloquent exception so the handler can treat it as a 404 if desired.
 */
class NotFoundException extends ModelNotFoundException
{
    // nothing special for now
}
