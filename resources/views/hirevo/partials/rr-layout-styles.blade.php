    .rr-page { background: linear-gradient(180deg, #f8fafc 0%, #ffffff 22%); }
    .rr-hero {
        border-radius: 1.25rem;
        background: linear-gradient(135deg, rgba(11, 31, 59, 0.06) 0%, rgba(16, 185, 129, 0.08) 100%);
        border: 1px solid rgba(11, 31, 59, 0.08);
        padding: 1.75rem 1.5rem;
    }
    @media (min-width: 768px) {
        .rr-hero { padding: 2rem 2.25rem; }
    }
    .rr-score-ring {
        width: 132px;
        height: 132px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.85rem;
        font-weight: 800;
        letter-spacing: -0.02em;
        position: relative;
        margin: 0 auto;
    }
    .rr-score-ring::before {
        content: '';
        position: absolute;
        inset: 0;
        border-radius: 50%;
        padding: 5px;
        background: linear-gradient(135deg, var(--ring-a), var(--ring-b));
        -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
        mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
        -webkit-mask-composite: xor;
        mask-composite: exclude;
    }
    .rr-score-ring.score-high { --ring-a: #10b981; --ring-b: #34d399; color: #047857; background: rgba(16, 185, 129, 0.1); }
    .rr-score-ring.score-mid { --ring-a: #f59e0b; --ring-b: #fbbf24; color: #b45309; background: rgba(245, 158, 11, 0.12); }
    .rr-score-ring.score-low { --ring-a: #ef4444; --ring-b: #f87171; color: #b91c1c; background: rgba(239, 68, 68, 0.1); }
    .rr-score-ring span.pct { font-size: 0.95rem; font-weight: 700; opacity: 0.85; }
    .rr-referral-card {
        border-radius: 1.25rem;
        border: none;
        background: linear-gradient(135deg, #0b1f3b 0%, #1e3a5f 50%, #0f766e 100%);
        color: #fff;
        box-shadow: 0 16px 40px rgba(11, 31, 59, 0.25);
        overflow: hidden;
        position: relative;
    }
    .rr-referral-card::after {
        content: '';
        position: absolute;
        top: -40%;
        right: -20%;
        width: 180px;
        height: 180px;
        background: radial-gradient(circle, rgba(16, 185, 129, 0.35) 0%, transparent 70%);
        pointer-events: none;
    }
    .rr-referral-card .card-body { position: relative; z-index: 1; }
    .rr-referral-card .btn-light {
        font-weight: 600;
        border: none;
        box-shadow: 0 4px 14px rgba(0,0,0,0.15);
    }
    .rr-referral-card .btn-outline-light {
        border-width: 1.5px;
        font-weight: 600;
    }
    .job-goal-card { transition: transform 0.2s ease, box-shadow 0.2s ease; }
    .job-goal-card:hover { transform: translateY(-2px); box-shadow: 0 0.75rem 2rem rgba(11, 31, 59, 0.1) !important; }
    .match-bar { height: 6px; border-radius: 999px; background: #e2e8f0; overflow: hidden; }
    .match-bar-fill { height: 100%; border-radius: 999px; transition: width 0.5s ease; }
    .rr-upskill-shell {
        border-radius: 1.25rem;
        background: linear-gradient(160deg, #f0f9ff 0%, #fdf4ff 45%, #ffffff 100%);
        border: 1px solid rgba(99, 102, 241, 0.12);
        box-shadow: 0 12px 40px rgba(15, 23, 42, 0.06);
    }
    .rr-upskill-head {
        padding: 1.25rem 1.25rem 0.5rem;
        border-bottom: 1px solid rgba(15, 23, 42, 0.06);
    }
    .rr-upskill-body { padding: 1.25rem; }
    .rr-upskill-item {
        border-radius: 1rem;
        background: #fff;
        border: 1px solid rgba(15, 23, 42, 0.08);
        box-shadow: 0 4px 20px rgba(15, 23, 42, 0.04);
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
        overflow: hidden;
    }
    .rr-upskill-item:hover {
        border-color: rgba(99, 102, 241, 0.25);
        box-shadow: 0 8px 28px rgba(99, 102, 241, 0.12);
    }
    .rr-upskill-item-top {
        height: 4px;
        background: linear-gradient(90deg, #6366f1, #10b981);
        opacity: 0.9;
    }
    .rr-pill-gap {
        font-size: 0.72rem;
        font-weight: 600;
        padding: 0.35rem 0.65rem;
        border-radius: 999px;
        background: linear-gradient(135deg, rgba(244, 63, 94, 0.12), rgba(251, 113, 133, 0.08));
        color: #be123c;
        border: 1px solid rgba(244, 63, 94, 0.22);
    }
    .rr-pill-focus {
        font-size: 0.72rem;
        font-weight: 600;
        padding: 0.35rem 0.65rem;
        border-radius: 999px;
        background: linear-gradient(135deg, rgba(16, 185, 129, 0.14), rgba(52, 211, 153, 0.1));
        color: #047857;
        border: 1px solid rgba(16, 185, 129, 0.28);
    }
    .rr-skill-section-label {
        font-size: 0.65rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: #64748b;
        margin-bottom: 0.5rem;
    }
    .rr-sticky-col { position: sticky; top: 5.5rem; }
    @media (max-width: 991px) {
        .rr-sticky-col { position: relative; top: 0; }
    }
    @keyframes rrReveal {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .rr-consult-card {
        border-radius: 1.25rem;
        border: 1px solid rgba(99, 102, 241, 0.22);
        background: linear-gradient(125deg, rgba(99, 102, 241, 0.08) 0%, rgba(16, 185, 129, 0.09) 55%, rgba(255, 255, 255, 0.9) 100%);
        box-shadow: 0 8px 28px rgba(15, 23, 42, 0.06);
        position: relative;
        overflow: hidden;
    }
    .rr-consult-card::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0; height: 3px;
        background: linear-gradient(90deg, #6366f1, #10b981, #6366f1);
        background-size: 200% 100%;
        animation: rrShimmer 4s ease infinite;
    }
    @keyframes rrShimmer {
        0%, 100% { background-position: 0% 50%; }
        50% { background-position: 100% 50%; }
    }
    .rr-consult-card .card-body { position: relative; z-index: 1; }
    .rr-match-feed {
        border-radius: 1.25rem;
        border: 1px solid rgba(15, 23, 42, 0.08);
        background: #fff;
        box-shadow: 0 12px 36px rgba(15, 23, 42, 0.06);
        overflow: hidden;
    }
    .rr-match-feed-head {
        position: relative;
        overflow: hidden;
        padding: 1.35rem 1.35rem 1.15rem;
        background: linear-gradient(155deg, #0b1220 0%, #151d2e 45%, #1e293b 100%);
        border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        color: #e2e8f0;
    }
    .rr-match-feed-head::before {
        content: '';
        position: absolute;
        inset: 0;
        background: repeating-linear-gradient(
            0deg,
            transparent,
            transparent 2px,
            rgba(255, 255, 255, 0.015) 2px,
            rgba(255, 255, 255, 0.015) 4px
        );
        pointer-events: none;
        opacity: 0.6;
    }
    .rr-match-feed-head::after {
        content: '';
        position: absolute;
        top: 0;
        left: -40%;
        width: 45%;
        height: 100%;
        background: linear-gradient(95deg, transparent, rgba(52, 211, 153, 0.18), transparent);
        animation: rrSuspenseScan 4s ease-in-out infinite;
        pointer-events: none;
    }
    @keyframes rrSuspenseScan {
        0% { transform: translateX(-10%); opacity: 0; }
        12% { opacity: 1; }
        45% { transform: translateX(320%); opacity: 1; }
        55% { opacity: 0; }
        100% { transform: translateX(320%); opacity: 0; }
    }
    .rr-suspense-inner { position: relative; z-index: 1; }
    .rr-suspense-eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        font-size: 0.62rem;
        font-weight: 700;
        letter-spacing: 0.16em;
        text-transform: uppercase;
        color: #6ee7b7;
        margin-bottom: 0.15rem;
    }
    .rr-suspense-dot {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: #34d399;
        box-shadow: 0 0 0 0 rgba(52, 211, 153, 0.45);
        animation: rrSuspensePing 1.6s ease-out infinite;
    }
    @keyframes rrSuspensePing {
        0% { box-shadow: 0 0 0 0 rgba(52, 211, 153, 0.45); }
        70% { box-shadow: 0 0 0 10px rgba(52, 211, 153, 0); }
        100% { box-shadow: 0 0 0 0 rgba(52, 211, 153, 0); }
    }
    .rr-suspense-title {
        font-size: clamp(1.15rem, 2.8vw, 1.45rem);
        font-weight: 800;
        letter-spacing: -0.02em;
        color: #f8fafc;
        line-height: 1.2;
        margin: 0.5rem 0 0.35rem;
        text-shadow: 0 2px 24px rgba(0, 0, 0, 0.35);
    }
    .rr-suspense-sub {
        font-size: 0.8125rem;
        line-height: 1.6;
        color: rgba(203, 213, 225, 0.92);
        max-width: 40rem;
        margin: 0;
    }
    .rr-suspense-sub .rr-suspense-hi {
        color: #fde68a;
        font-weight: 700;
    }
    .rr-suspense-sub .rr-suspense-drama {
        color: #f1f5f9;
        font-weight: 700;
    }
    .rr-unlock-vault {
        position: relative;
        min-width: 118px;
        padding: 0.85rem 1rem 0.95rem;
        border-radius: 1rem;
        background: rgba(0, 0, 0, 0.38);
        border: 1px solid rgba(52, 211, 153, 0.38);
        box-shadow:
            inset 0 1px 0 rgba(255, 255, 255, 0.06),
            0 10px 28px rgba(0, 0, 0, 0.35);
        text-align: center;
    }
    .rr-unlock-vault--empty {
        border-color: rgba(148, 163, 184, 0.35);
    }
    .rr-unlock-label {
        display: block;
        font-size: 0.58rem;
        font-weight: 700;
        letter-spacing: 0.14em;
        text-transform: uppercase;
        color: rgba(226, 232, 240, 0.55);
        margin-bottom: 0.4rem;
    }
    .rr-unlock-num {
        display: block;
        font-size: 1.85rem;
        font-weight: 800;
        font-variant-numeric: tabular-nums;
        color: #fff;
        line-height: 1;
        letter-spacing: -0.03em;
        animation: rrDecryptNum 1.15s cubic-bezier(0.22, 1, 0.36, 1) 0.25s both;
    }
    @keyframes rrDecryptNum {
        from {
            opacity: 0;
            filter: blur(12px);
            transform: scale(0.88) translateY(6px);
        }
        to {
            opacity: 1;
            filter: blur(0);
            transform: scale(1) translateY(0);
        }
    }
    .rr-unlock-vault--empty .rr-unlock-num {
        font-size: 1rem;
        font-weight: 700;
        animation: rrDecryptNum 0.9s cubic-bezier(0.22, 1, 0.36, 1) 0.15s both;
    }
    .rr-suspense-foot {
        position: relative;
        z-index: 1;
        margin-top: 1.15rem;
        padding-top: 1rem;
        border-top: 1px solid rgba(255, 255, 255, 0.1);
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.35rem 0.65rem;
    }
    .rr-suspense-foot a {
        color: #6ee7b7 !important;
        font-size: 0.8rem;
        font-weight: 600;
        text-decoration: none !important;
        transition: color 0.15s ease;
    }
    .rr-suspense-foot a:hover { color: #a7f3d0 !important; }
    .rr-suspense-foot .sep {
        color: rgba(255, 255, 255, 0.28);
        font-size: 0.75rem;
        user-select: none;
    }
    .rr-match-row {
        display: flex;
        gap: 0.85rem;
        align-items: flex-start;
        padding: 1rem 1.25rem;
        border-bottom: 1px solid rgba(15, 23, 42, 0.06);
        animation: rrReveal 0.5s ease forwards;
        opacity: 0;
        transition: background 0.2s ease;
    }
    .rr-match-row:last-child { border-bottom: none; }
    .rr-match-row:hover { background: rgba(99, 102, 241, 0.03); }
    .rr-match-rank {
        flex: 0 0 2.25rem;
        width: 2.25rem;
        height: 2.25rem;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.8rem;
        font-weight: 800;
        background: #f1f5f9;
        color: #475569;
    }
    .rr-match-rank.top-1 {
        background: linear-gradient(135deg, #fde68a, #fbbf24);
        color: #78350f;
        box-shadow: 0 4px 12px rgba(245, 158, 11, 0.35);
    }
    .rr-match-rank.top-2 {
        background: linear-gradient(135deg, #e2e8f0, #cbd5e1);
        color: #334155;
        box-shadow: 0 2px 8px rgba(100, 116, 139, 0.2);
    }
    .rr-match-rank.top-3 {
        background: linear-gradient(135deg, #fed7aa, #fdba74);
        color: #9a3412;
        box-shadow: 0 2px 8px rgba(234, 88, 12, 0.2);
    }
    .rr-match-teaser {
        font-size: 0.72rem;
        color: #64748b;
        font-style: italic;
        margin-top: 0.35rem;
    }
    .rr-funnel-rail {
        position: relative;
        padding-top: 0.25rem;
    }
    @media (min-width: 576px) {
        .rr-funnel-rail::before {
            content: '';
            position: absolute;
            left: 0.95rem;
            top: 3.25rem;
            bottom: 1.5rem;
            width: 3px;
            border-radius: 3px;
            background: linear-gradient(180deg, #6366f1 0%, #10b981 42%, rgba(203, 213, 225, 0.85) 100%);
            opacity: 0.5;
            pointer-events: none;
        }
    }
    .rr-funnel-hero {
        background: linear-gradient(135deg, rgba(99, 102, 241, 0.09) 0%, #fff 45%, rgba(16, 185, 129, 0.08) 100%);
        border: 1px solid rgba(99, 102, 241, 0.18);
        box-shadow: 0 10px 36px rgba(15, 23, 42, 0.07);
    }
    .rr-funnel-step {
        position: relative;
    }
    @media (min-width: 576px) {
        .rr-funnel-step {
            padding-left: 3.1rem;
        }
    }
    .rr-funnel-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 2.15rem;
        height: 2.15rem;
        padding: 0 0.35rem;
        border-radius: 50%;
        font-size: 0.82rem;
        font-weight: 800;
        color: #fff;
        margin-bottom: 0.65rem;
        box-shadow: 0 6px 18px rgba(99, 102, 241, 0.35);
        background: linear-gradient(135deg, #6366f1, #4f46e5);
    }
    @media (min-width: 576px) {
        .rr-funnel-badge {
            position: absolute;
            left: 0;
            top: 0.5rem;
            margin-bottom: 0;
            z-index: 2;
        }
    }
    .rr-funnel-step--skills .rr-funnel-badge {
        background: linear-gradient(135deg, #0ea5e9, #6366f1);
        box-shadow: 0 6px 18px rgba(14, 165, 233, 0.35);
    }
    .rr-funnel-step--consult .rr-funnel-badge {
        background: linear-gradient(135deg, #10b981, #059669);
        box-shadow: 0 6px 18px rgba(16, 185, 129, 0.32);
    }
    .rr-funnel-step--shortlist .rr-funnel-badge {
        background: linear-gradient(135deg, #0f172a, #475569);
        box-shadow: 0 6px 18px rgba(15, 23, 42, 0.28);
    }
    .rr-funnel-step--refer .rr-funnel-badge {
        background: linear-gradient(135deg, #7c3aed, #6366f1);
    }
    .rr-funnel-step--upskill .rr-funnel-badge {
        background: linear-gradient(135deg, #f59e0b, #ea580c);
        box-shadow: 0 6px 18px rgba(245, 158, 11, 0.35);
    }
    .rr-funnel-step-body {
        min-width: 0;
    }
    .rr-funnel-icon {
        flex-shrink: 0;
    }
    .rr-skills-sequenced-accent {
        height: 4px;
        background: linear-gradient(90deg, #0ea5e9, #6366f1, #10b981);
    }
    .rr-skill-chip {
        font-size: 0.78rem;
        font-weight: 600;
        padding: 0.4rem 0.9rem;
        border-radius: 999px;
        background: linear-gradient(135deg, rgba(99, 102, 241, 0.14), rgba(14, 165, 233, 0.1));
        color: #4338ca;
        border: 1px solid rgba(99, 102, 241, 0.25);
    }
    .rr-skill-chip--more {
        background: #f8fafc;
        color: #64748b;
        border-color: #e2e8f0;
    }
    .rr-match-feed--jobs-panel {
        border-radius: 1.25rem;
        border: 1px solid rgba(15, 23, 42, 0.08);
        box-shadow: 0 12px 36px rgba(15, 23, 42, 0.06);
    }
    .rr-match-feed--shortlist-teaser {
        box-shadow: 0 12px 36px rgba(15, 23, 42, 0.06);
    }
    .rr-suspense-pointer {
        color: rgba(248, 250, 252, 0.92);
    }
    .rr-jobs-only-kicker {
        font-size: 0.68rem;
        font-weight: 700;
        letter-spacing: 0.11em;
        text-transform: uppercase;
        color: #64748b;
    }
    .rr-jobs-scroll-col {
        min-width: 0;
    }
    .rr-rail-sticky {
        position: sticky;
        top: 5rem;
        align-self: flex-start;
        overflow: visible;
    }
    @media (max-width: 991px) {
        .rr-rail-sticky {
            position: relative;
            top: auto;
        }
    }
    .rr-rail-compact .rr-funnel-rail {
        gap: 0.85rem !important;
    }
    .rr-rail-compact .rr-funnel-rail::before {
        display: none !important;
    }
    .rr-rail-compact .rr-funnel-step {
        padding-left: 0 !important;
    }
    .rr-rail-compact .rr-funnel-badge {
        position: static !important;
        width: 1.65rem;
        height: 1.65rem;
        min-width: 1.65rem;
        font-size: 0.72rem;
        margin-bottom: 0.35rem;
        margin-right: 0.5rem;
        vertical-align: middle;
        display: inline-flex !important;
    }
    .rr-rail-compact .rr-funnel-step {
        display: block;
    }
    .rr-rail-compact .rr-funnel-step-body {
        display: block;
        width: 100%;
    }
    .rr-rail-compact .rr-score-ring--rail {
        width: 84px;
        height: 84px;
        font-size: 1.35rem;
        margin: 0 auto;
    }
    .rr-rail-compact .rr-suspense-title {
        font-size: 1rem;
        margin: 0.35rem 0 0.25rem;
    }
    .rr-rail-compact .rr-suspense-sub {
        font-size: 0.75rem;
        line-height: 1.5;
    }
    .rr-rail-compact .rr-match-feed-head {
        padding: 0.95rem 1rem 0.85rem;
    }
    .rr-rail-compact .rr-unlock-vault {
        min-width: 86px;
        padding: 0.5rem 0.6rem 0.6rem;
    }
    .rr-rail-compact .rr-unlock-num {
        font-size: 1.35rem;
    }
    .rr-rail-compact .rr-suspense-foot {
        margin-top: 0.75rem;
        padding-top: 0.65rem;
    }
    .rr-rail-compact .rr-upskill-head {
        padding: 0.85rem 1rem 0.4rem;
    }
    .rr-rail-compact .rr-upskill-head h3 {
        font-size: 1rem;
    }
    .rr-rail-compact .rr-upskill-body {
        padding: 0.75rem 1rem 1rem;
    }
    .rr-rail-compact .rr-funnel-hero {
        padding: 0.85rem 1rem !important;
    }
    .rr-rail-compact .rr-funnel-hero h2 {
        font-size: 1rem !important;
    }
