<div class="modal fade" id="candidatePremiumModal" tabindex="-1" aria-labelledby="candidatePremiumModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content cf-premium-modal">
            <div class="modal-body text-center p-4 p-md-5">
                <div class="cf-premium-modal__icon" aria-hidden="true">
                    <i class="mdi mdi-crown"></i>
                </div>
                <h2 class="h5 fw-bold mb-2" id="candidatePremiumModalLabel">Unlock Premium Career Tools</h2>
                <p class="text-muted small mb-4" id="candidatePremiumModalText">
                    Get full access to AI assessments, mock interviews, skill gap analysis, learning resources, and personalized job matches.
                </p>
                <ul class="cf-premium-modal__list text-start small text-muted mb-4">
                    <li><i class="mdi mdi-check-circle text-primary"></i> Skill assessments tailored to your resume</li>
                    <li><i class="mdi mdi-check-circle text-primary"></i> Mock interview practice packs</li>
                    <li><i class="mdi mdi-check-circle text-primary"></i> Skill gap analysis & learning hub</li>
                    <li><i class="mdi mdi-check-circle text-primary"></i> Personalized job matches</li>
                </ul>
                <div class="d-flex flex-column flex-sm-row gap-2 justify-content-center">
                    <a href="{{ $candidatePlanUrl ?? route('pricing') }}" class="cf-btn cf-btn--primary">View plans &amp; upgrade</a>
                    <button type="button" class="cf-btn cf-btn--outline" data-bs-dismiss="modal">Maybe later</button>
                </div>
            </div>
        </div>
    </div>
</div>
