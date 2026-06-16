<?php

namespace App\View\Components;

use App\Services\CandidatePremiumService;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class CandidatePremiumGate extends Component
{
    public bool $unlocked;

    public function __construct(
        CandidatePremiumService $premium,
        public string $feature = 'this feature',
        public bool $compact = false,
        ?bool $unlocked = null,
    ) {
        $user = auth()->user();
        $this->unlocked = $unlocked ?? (
            $user !== null
            && $user->isCandidate()
            && $premium->hasAiCareerToolsAccess($user)
        );
    }

    public function render(): View
    {
        return view('components.candidate-premium-gate');
    }
}
