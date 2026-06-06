<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Enums\EnaFiliere;
use App\Support\Enums\EnaPreferredFormat;
use App\Support\Enums\EnaRegionalGrade;
use App\Support\Enums\RegistrationConcoursPriority;
use App\Support\Enums\RegistrationConcoursStatus;
use Database\Factories\RegistrationConcoursFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Notifications\Notifiable;

/**
 * Lead captured by the public ENA-prep landing page (/concours-ena).
 *
 * @property int $id
 * @property string $full_name
 * @property string $whatsapp_phone
 * @property string $email
 * @property EnaFiliere $filiere
 * @property EnaRegionalGrade $regional_grade
 * @property string $city
 * @property EnaPreferredFormat $preferred_format
 * @property bool $passed_ena_before
 * @property RegistrationConcoursStatus $status
 * @property RegistrationConcoursPriority $priority
 * @property string|null $admin_notes
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property \Illuminate\Support\Carbon $submitted_at
 * @property \Illuminate\Support\Carbon|null $status_changed_at
 * @property int|null $status_changed_by
 *
 * @property-read User|null $statusChangedBy
 */
class RegistrationConcours extends Model
{
    /** @use HasFactory<RegistrationConcoursFactory> */
    use HasFactory, Notifiable;

    protected $table = 'registrations_concours';

    protected $fillable = [
        'full_name',
        'whatsapp_phone',
        'email',
        'filiere',
        'regional_grade',
        'city',
        'preferred_format',
        'passed_ena_before',
        'status',
        'priority',
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
            'filiere' => EnaFiliere::class,
            'regional_grade' => EnaRegionalGrade::class,
            'preferred_format' => EnaPreferredFormat::class,
            'passed_ena_before' => 'boolean',
            'status' => RegistrationConcoursStatus::class,
            'priority' => RegistrationConcoursPriority::class,
            'submitted_at' => 'datetime',
            'status_changed_at' => 'datetime',
        ];
    }

    public function routeNotificationForMail(): string
    {
        return $this->email;
    }

    public function statusChangedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'status_changed_by');
    }

    /**
     * Compute the priority from the current filière + grade combo.
     * Centralised here so create + update + admin-edit all use the same rule.
     */
    public static function computePriority(EnaFiliere $filiere, EnaRegionalGrade $grade): RegistrationConcoursPriority
    {
        $highGrade = $grade->isHigh();
        $goodFiliere = $filiere->isCompatible();

        return match (true) {
            $highGrade && $goodFiliere => RegistrationConcoursPriority::High,
            $highGrade || $goodFiliere => RegistrationConcoursPriority::Medium,
            default => RegistrationConcoursPriority::Low,
        };
    }

    // ---- Scopes ----------------------------------------------------------

    public function scopeStatus(Builder $query, RegistrationConcoursStatus|string|array $status): Builder
    {
        $values = array_map(
            fn ($s) => $s instanceof RegistrationConcoursStatus ? $s->value : (string) $s,
            is_array($status) ? $status : [$status],
        );

        return $query->whereIn('status', $values);
    }

    public function scopePriority(Builder $query, RegistrationConcoursPriority|string|array $priority): Builder
    {
        $values = array_map(
            fn ($p) => $p instanceof RegistrationConcoursPriority ? $p->value : (string) $p,
            is_array($priority) ? $priority : [$priority],
        );

        return $query->whereIn('priority', $values);
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        $term = trim((string) $term);
        if ($term === '') return $query;
        $like = '%'.$term.'%';

        return $query->where(function (Builder $q) use ($like): void {
            $q->where('full_name', 'like', $like)
                ->orWhere('email', 'like', $like)
                ->orWhere('whatsapp_phone', 'like', $like)
                ->orWhere('city', 'like', $like);
        });
    }

    public function scopeFiliere(Builder $query, ?string $value): Builder
    {
        return $value === null || $value === '' ? $query : $query->where('filiere', $value);
    }

    public function scopeCity(Builder $query, ?string $value): Builder
    {
        return $value === null || $value === '' ? $query : $query->where('city', $value);
    }

    public function scopeFormat(Builder $query, ?string $value): Builder
    {
        return $value === null || $value === '' ? $query : $query->where('preferred_format', $value);
    }

    public function scopeRegionalGrade(Builder $query, ?string $value): Builder
    {
        return $value === null || $value === '' ? $query : $query->where('regional_grade', $value);
    }

    public function scopeSubmittedBetween(Builder $query, ?string $from, ?string $to): Builder
    {
        if ($from) $query->where('submitted_at', '>=', $from);
        if ($to) $query->where('submitted_at', '<=', $to);
        return $query;
    }
}
