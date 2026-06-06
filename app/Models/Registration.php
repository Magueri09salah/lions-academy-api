<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Enums\RegistrationStatus;
use Database\Factories\RegistrationFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Notifications\Notifiable;

/**
 * A public registration request submitted through the inscription form
 * (lion-s-roar-academy/src/routes/inscription.tsx).
 *
 * Storage columns use snake_case canonical names per the backend spec;
 * the API boundary still accepts the frontend's HTML field names
 * (name/phone/level/formation) in StoreRegistrationRequest.
 *
 * @property int $id
 * @property string $full_name
 * @property string $whatsapp_phone
 * @property string $email
 * @property string $city
 * @property string $education_level
 * @property string|null $profession
 * @property int|null $formation_id
 * @property string|null $formation_title           snapshot at submission time
 * @property string|null $address
 * @property string|null $message
 * @property bool $privacy_accepted
 * @property RegistrationStatus $status
 * @property string|null $admin_notes               internal back-office notes
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property \Illuminate\Support\Carbon $submitted_at
 * @property \Illuminate\Support\Carbon|null $status_changed_at
 * @property int|null $status_changed_by
 *
 * @property-read Formation|null $formation
 * @property-read User|null $statusChangedBy
 * @property-read \Illuminate\Database\Eloquent\Collection<int, MediaAsset> $documents
 */
class Registration extends Model
{
    /** @use HasFactory<RegistrationFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'full_name',
        'whatsapp_phone',
        'email',
        'city',
        'address',
        'education_level',
        'profession',
        'formation_id',
        'formation_title',
        'message',
        'privacy_accepted',
        'status',
        'admin_notes',
        'ip_address',
        'user_agent',
        'submitted_at',
        'status_changed_at',
        'status_changed_by',
    ];

    protected function casts(): array
    {
        return [
            'privacy_accepted' => 'boolean',
            'status' => RegistrationStatus::class,
            'submitted_at' => 'datetime',
            'status_changed_at' => 'datetime',
        ];
    }

    /**
     * The Notifiable trait will mail anyone we route notifications to.
     * Public applicants don't have user accounts, so we route on their
     * email directly via `routeNotificationForMail`.
     */
    public function routeNotificationForMail(): string
    {
        return $this->email;
    }

    public function formation(): BelongsTo
    {
        return $this->belongsTo(Formation::class);
    }

    public function statusChangedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'status_changed_by');
    }

    /**
     * Files attached to the registration (photo, CIN, etc.). Stored on the
     * `private` disk; the join row carries a `label` distinguishing them.
     */
    public function documents(): BelongsToMany
    {
        return $this->belongsToMany(MediaAsset::class, 'registration_documents')
            ->withPivot(['label'])
            ->withTimestamps();
    }

    // ---- Query scopes ----------------------------------------------------

    public function scopeStatus(Builder $query, RegistrationStatus|string|array $status): Builder
    {
        $values = array_map(
            fn ($s) => $s instanceof RegistrationStatus ? $s->value : (string) $s,
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
            $q->where('full_name', 'like', $like)
                ->orWhere('email', 'like', $like)
                ->orWhere('whatsapp_phone', 'like', $like)
                ->orWhere('city', 'like', $like)
                ->orWhere('formation_title', 'like', $like);
        });
    }

    public function scopeFormation(Builder $query, int|string|null $formation): Builder
    {
        if ($formation === null || $formation === '') {
            return $query;
        }

        return is_numeric($formation)
            ? $query->where('formation_id', (int) $formation)
            : $query->whereHas('formation', fn (Builder $q) => $q->where('slug', $formation));
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
