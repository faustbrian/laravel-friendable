<?php

declare(strict_types=1);

namespace PreemStudio\Friendships\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Collection;
use PreemStudio\Friendships\Enums\Status;
use PreemStudio\Friendships\Models\Friendship;

trait HasFriendships
{
    public function friends(): MorphMany
    {
        return $this->morphMany(Friendship::class, 'sender');
    }

    public function befriend(Model $recipient): bool
    {
        if ($this->isFriendsWith($recipient)) {
            return true;
        }

        $friendship = (new Friendship)->forceFill([
            'recipient_id'   => $recipient->id,
            'recipient_type' => get_class($recipient),
            'status'         => Status::PENDING,
        ]);

        return (bool) $this->friends()->save($friendship);
    }

    public function unfriend(Model $recipient): bool
    {
        if (! $this->isFriendsWith($recipient)) {
            return false;
        }

        return (bool) $this->findFriendship($recipient)->delete();
    }

    public function isFriendsWith(Model $recipient, $status = null): bool
    {
        $exists = $this->findFriendship($recipient);

        if (! empty($status)) {
            $exists = $exists->where('status', $status);
        }

        return (bool) $exists->count();
    }

    public function acceptFriendRequest(Model $recipient): bool
    {
        if (! $this->isFriendsWith($recipient)) {
            return false;
        }

        return (bool) $this->findFriendship($recipient)->update([
            'status' => Status::ACCEPTED,
        ]);
    }

    public function denyFriendRequest(Model $recipient): bool
    {
        if (! $this->isFriendsWith($recipient)) {
            return false;
        }

        return (bool) $this->findFriendship($recipient)->update([
            'status' => Status::DENIED,
        ]);
    }

    public function blockFriendRequest(Model $recipient): bool
    {
        if (! $this->isFriendsWith($recipient)) {
            return false;
        }

        return (bool) $this->findFriendship($recipient)->update([
            'status' => Status::BLOCKED,
        ]);
    }

    public function unblockFriendRequest(Model $recipient): bool
    {
        if (! $this->isFriendsWith($recipient)) {
            return false;
        }

        return (bool) $this->findFriendship($recipient)->update([
            'status' => Status::PENDING,
        ]);
    }

    public function getFriendship($recipient): Friendship
    {
        return $this->findFriendship($recipient)->first();
    }

    public function getAllFriendships(int $limit = null, int $offset = null): array
    {
        return $this->findFriendshipsByStatus(null, $limit, $offset);
    }

    public function getPendingFriendships(int $limit = null, int $offset = 0): array
    {
        return $this->findFriendshipsByStatus(Status::PENDING, $limit, $offset);
    }

    public function getAcceptedFriendships(int $limit = null, int $offset = 0): array
    {
        return $this->findFriendshipsByStatus(Status::ACCEPTED, $limit, $offset);
    }

    public function getDeniedFriendships(int $limit = null, int $offset = 0): array
    {
        return $this->findFriendshipsByStatus(Status::DENIED, $limit, $offset);
    }

    public function getBlockedFriendships(int $limit = null, int $offset = 0): array
    {
        return $this->findFriendshipsByStatus(Status::BLOCKED, $limit, $offset);
    }

    public function hasBlocked(Model $recipient): bool
    {
        return $this->getFriendship($recipient)->status === Status::BLOCKED;
    }

    public function isBlockedBy(Model $recipient): bool
    {
        $friendship = Friendship::where(function ($query) use ($recipient) {
            $query->where('sender_id', $this->id);
            $query->where('sender_type', get_class($this));

            $query->where('recipient_id', $recipient->id);
            $query->where('recipient_type', get_class($recipient));
        })->first();

        return $friendship ? ($friendship->status === Status::BLOCKED) : false;
    }

    public function getFriendRequests(): Collection
    {
        return Friendship::where(function ($query) {
            $query->where('recipient_id', $this->id);
            $query->where('recipient_type', get_class($this));
            $query->where('status', Status::PENDING);
        })->get();
    }

    private function findFriendship(Model $recipient): Builder
    {
        return Friendship::where(function ($query) use ($recipient) {
            $query->where('sender_id', $this->id);
            $query->where('sender_type', get_class($this));

            $query->where('recipient_id', $recipient->id);
            $query->where('recipient_type', get_class($recipient));
        })->orWhere(function ($query) use ($recipient) {
            $query->where('sender_id', $recipient->id);
            $query->where('sender_type', get_class($recipient));

            $query->where('recipient_id', $this->id);
            $query->where('recipient_type', get_class($this));
        });
    }

    private function findFriendshipsByStatus($status, $limit, $offset): array
    {
        $friendships = [];

        $query = Friendship::where(function ($query) use ($status) {
            $query->where('sender_id', $this->id);
            $query->where('sender_type', get_class($this));

            if (! empty($status)) {
                $query->where('status', $status);
            }
        })->orWhere(function ($query) use ($status) {
            $query->where('recipient_id', $this->id);
            $query->where('recipient_type', get_class($this));

            if (! empty($status)) {
                $query->where('status', $status);
            }
        });

        if (! empty($limit)) {
            $query->take($limit);
        }

        if (! empty($offset)) {
            $query->skip($offset);
        }

        foreach ($query->get() as $friendship) {
            $friendships[] = $this->getFriendship($this->find(
                ($friendship->sender_id == $this->id) ? $friendship->recipient_id : $friendship->sender_id
            ));
        }

        return $friendships;
    }
}
