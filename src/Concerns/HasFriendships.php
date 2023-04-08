<?php

declare(strict_types=1);

namespace PreemStudio\Friendable\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Collection;
use PreemStudio\Friendable\Enums\Status;
use PreemStudio\Friendable\Models\Friendship;

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

        $friendship = (new Friendship())->forceFill([
            'recipient_id' => $recipient->id,
            'recipient_type' => $recipient::class,
            'status' => Status::PENDING,
        ]);

        return (bool) $this->friends()->save($friendship);
    }

    public function unfriend(Model $recipient): bool
    {
        if (!$this->isFriendsWith($recipient)) {
            return false;
        }

        return (bool) $this->findFriendship($recipient)->delete();
    }

    public function isFriendsWith(Model $recipient, $status = null): bool
    {
        $exists = $this->findFriendship($recipient);

        if (!empty($status)) {
            $exists = $exists->where('status', $status);
        }

        return (bool) $exists->count();
    }

    public function acceptFriendRequest(Model $recipient): bool
    {
        if (!$this->isFriendsWith($recipient)) {
            return false;
        }

        return (bool) $this->findFriendship($recipient)->update([
            'status' => Status::ACCEPTED,
        ]);
    }

    public function denyFriendRequest(Model $recipient): bool
    {
        if (!$this->isFriendsWith($recipient)) {
            return false;
        }

        return (bool) $this->findFriendship($recipient)->update([
            'status' => Status::DENIED,
        ]);
    }

    public function blockFriendRequest(Model $recipient): bool
    {
        if (!$this->isFriendsWith($recipient)) {
            return false;
        }

        return (bool) $this->findFriendship($recipient)->update([
            'status' => Status::BLOCKED,
        ]);
    }

    public function unblockFriendRequest(Model $recipient): bool
    {
        if (!$this->isFriendsWith($recipient)) {
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

    public function getAllFriendable(?int $limit = null, ?int $offset = null): array
    {
        return $this->findFriendableByStatus(null, $limit, $offset);
    }

    public function getPendingFriendable(?int $limit = null, int $offset = 0): array
    {
        return $this->findFriendableByStatus(Status::PENDING, $limit, $offset);
    }

    public function getAcceptedFriendable(?int $limit = null, int $offset = 0): array
    {
        return $this->findFriendableByStatus(Status::ACCEPTED, $limit, $offset);
    }

    public function getDeniedFriendable(?int $limit = null, int $offset = 0): array
    {
        return $this->findFriendableByStatus(Status::DENIED, $limit, $offset);
    }

    public function getBlockedFriendable(?int $limit = null, int $offset = 0): array
    {
        return $this->findFriendableByStatus(Status::BLOCKED, $limit, $offset);
    }

    public function hasBlocked(Model $recipient): bool
    {
        return $this->getFriendship($recipient)->status === Status::BLOCKED;
    }

    public function isBlockedBy(Model $recipient): bool
    {
        $friendship = Friendship::where(function ($query) use ($recipient): void {
            $query->where('sender_id', $this->id);
            $query->where('sender_type', \get_class($this));

            $query->where('recipient_id', $recipient->id);
            $query->where('recipient_type', $recipient::class);
        })->first();

        return $friendship ? ($friendship->status === Status::BLOCKED) : false;
    }

    public function getFriendRequests(): Collection
    {
        return Friendship::where(function ($query): void {
            $query->where('recipient_id', $this->id);
            $query->where('recipient_type', \get_class($this));
            $query->where('status', Status::PENDING);
        })->get();
    }

    private function findFriendship(Model $recipient): Builder
    {
        return Friendship::where(function ($query) use ($recipient): void {
            $query->where('sender_id', $this->id);
            $query->where('sender_type', \get_class($this));

            $query->where('recipient_id', $recipient->id);
            $query->where('recipient_type', $recipient::class);
        })->orWhere(function ($query) use ($recipient): void {
            $query->where('sender_id', $recipient->id);
            $query->where('sender_type', $recipient::class);

            $query->where('recipient_id', $this->id);
            $query->where('recipient_type', \get_class($this));
        });
    }

    private function findFriendableByStatus($status, $limit, $offset): array
    {
        $friendships = [];

        $query = Friendship::where(function ($query) use ($status): void {
            $query->where('sender_id', $this->id);
            $query->where('sender_type', \get_class($this));

            if (!empty($status)) {
                $query->where('status', $status);
            }
        })->orWhere(function ($query) use ($status): void {
            $query->where('recipient_id', $this->id);
            $query->where('recipient_type', \get_class($this));

            if (!empty($status)) {
                $query->where('status', $status);
            }
        });

        if (!empty($limit)) {
            $query->take($limit);
        }

        if (!empty($offset)) {
            $query->skip($offset);
        }

        foreach ($query->get() as $friendship) {
            $friendships[] = $this->getFriendship($this->find(
                ($friendship->sender_id === $this->id) ? $friendship->recipient_id : $friendship->sender_id,
            ));
        }

        return $friendships;
    }
}
