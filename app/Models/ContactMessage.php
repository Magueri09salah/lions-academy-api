<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Enums\ContactMessageStatus;
use Database\Factories\ContactMessageFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Notifications\Notifiable;

/**
 * A message submitted through the public /contact form.
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string|null $phone
 * @property string $subject
 * @property string $message
 * @property ContactMessageStatus $status
 * @property string|null $admin_notes
 * @property \Illuminate\Support\Carbon|null $read_at
 * @property \Illuminate\Support\Carbon|null $replied_at
 * @property int|null $handled_by
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property \Illuminate\Support\Carbon $submitted_at
 *
 * @property-read User|null $handler
 */
class ContactMessage extends Model
{
    /** @use HasFactory<ContactMessageFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'subject',
        'message',
        'status',
        'admin_notes',
        'read_at',
        'replied_at',
        'handled_by',
        'ip_address',
        'user_agent',
        'submitted_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => ContactMessageStatus::class,
            'read_at' => 'datetime',
            'replied_at' => 'datetime',
            'submitted_at' => 'datetime',
        ];
    }

    /**
     * Public submitters have no user account, so notifications routed to
     * "this message" go to the email captured on the form.
     */
    public function routeNotificationForMail(): string
    {
        return $this->email;
    }

    public function handler(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handled_by');
    }

    // ---- Query scopes ------------------------------------------------------

    public function scopeStatus(Builder $query, ContactMessageStatus|string|array $status): Builder
    {
        $values = array_map(
            fn ($s) => $s instanceof ContactMessageStatus ? $s->value : (string) $s,
            is_array($status) ? $status : [$status],
        );

        return $query->whereIn('status', $values);
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        $term = trim((string) $term);
        if ($term === '') {
            return $query;
        }

        $like = '%'.$term.'%';

        return $query->where(function (Builder $q) use ($like): void {
            $q->where('name', 'like', $like)
                ->orWhere('email', 'like', $like)
                ->orWhere('subject', 'like', $like)
                ->orWhere('message', 'like', $like);
        });
    }

    public function scopeSubmittedBetween(Builder $query, ?string $from, ?string $to): Builder
    {
        if ($from) {
            $query->where('submitted_at', '>=', $from);
        }
        if ($to) {
            $query->where('submitted_at', '<=', $to);
        }

        return $query;
    }
}
