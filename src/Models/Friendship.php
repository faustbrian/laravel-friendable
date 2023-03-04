<?php

declare(strict_types=1);

namespace PreemStudio\Friendships\Models;

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
