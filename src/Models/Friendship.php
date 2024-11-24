<?php declare(strict_types=1);

/**
 * Copyright (C) BaseCode Oy - All Rights Reserved
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace BaseCodeOy\Friendable\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

final class Friendship extends Model
{
    public function sender(): MorphTo
    {
        return $this->morphTo('sender');
    }

    public function recipient(): MorphTo
    {
        return $this->morphTo('recipient');
    }
}
