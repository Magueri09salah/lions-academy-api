<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\ContactMessage;
use App\Models\Formation;
use App\Models\MediaAsset;
use App\Models\Principle;
use App\Models\ProgramMonth;
use App\Models\Project;
use App\Models\Registration;
use App\Models\RegistrationConcours;
use App\Models\Trainer;
use App\Models\User;
use App\Policies\ContactMessagePolicy;
use App\Policies\FormationPolicy;
use App\Policies\MediaAssetPolicy;
use App\Policies\PrinciplePolicy;
use App\Policies\ProgramMonthPolicy;
use App\Policies\ProjectPolicy;
use App\Policies\RegistrationConcoursPolicy;
use App\Policies\RegistrationPolicy;
use App\Policies\TrainerPolicy;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /** @var array<class-string, class-string> */
    protected $policies = [
        User::class => UserPolicy::class,
        MediaAsset::class => MediaAssetPolicy::class,
        Registration::class => RegistrationPolicy::class,
        ContactMessage::class => ContactMessagePolicy::class,
        Principle::class => PrinciplePolicy::class,
        ProgramMonth::class => ProgramMonthPolicy::class,
        Trainer::class => TrainerPolicy::class,
        Project::class => ProjectPolicy::class,
        Formation::class => FormationPolicy::class,
        RegistrationConcours::class => RegistrationConcoursPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();
    }
}
