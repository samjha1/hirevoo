@push('styles')
<style>
/* ─── Buy Packages — refined employer UI ─────────────────── */
.bp-page {
    max-width: 1140px;
    margin: 0 auto;
    padding-bottom: 8px;
}

/* Alerts */
.bp-alert {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    padding: 13px 16px;
    border-radius: var(--radius, 12px);
    margin-bottom: 14px;
    font-size: 0.875rem;
    line-height: 1.45;
    animation: bp-fade-in 0.35s ease;
}
.bp-alert > i { font-size: 1.2rem; flex-shrink: 0; margin-top: 2px; }
.bp-alert strong { display: block; font-weight: 600; margin-bottom: 2px; }
.bp-alert span { opacity: 0.88; }
.bp-alert--warning { background: #fffbeb; border: 1px solid #fde68a; color: #92400e; }
.bp-alert--info { background: #eff6ff; border: 1px solid #bfdbfe; color: #1e40af; }

@keyframes bp-fade-in {
    from { opacity: 0; transform: translateY(6px); }
    to   { opacity: 1; transform: translateY(0); }
}

/* Status cards */
.bp-status-row {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 14px;
    margin-bottom: 20px;
}
.bp-status-card {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 16px 18px;
    background: var(--surface, #fff);
    border: 1px solid var(--border, #e5e8ee);
    border-radius: var(--radius-lg, 16px);
    box-shadow: var(--shadow-xs);
    text-decoration: none;
    color: inherit;
    position: relative;
    overflow: hidden;
    transition: border-color 0.2s, box-shadow 0.2s, transform 0.2s;
}
.bp-status-card::before {
    content: '';
    position: absolute;
    left: 0; top: 0; bottom: 0;
    width: 3px;
    border-radius: 3px 0 0 3px;
}
.bp-status-card--plan::before { background: linear-gradient(180deg, #16a34a, #22c55e); }
.bp-status-card--pool::before { background: linear-gradient(180deg, #2563eb, #60a5fa); }
.bp-status-card--help::before { background: linear-gradient(180deg, #0f2a50, #2563eb); }
.bp-status-card--link:hover {
    border-color: #c7d2fe;
    box-shadow: var(--shadow-md);
    transform: translateY(-2px);
}
.bp-status-card__icon {
    width: 42px;
    height: 42px;
    border-radius: 11px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    flex-shrink: 0;
}
.bp-status-card--plan .bp-status-card__icon { background: #ecfdf5; color: #16a34a; }
.bp-status-card--pool .bp-status-card__icon { background: #eff6ff; color: #2563eb; }
.bp-status-card--help .bp-status-card__icon { background: #f0f4fa; color: #0f2a50; }
.bp-status-card__body { flex: 1; min-width: 0; }
.bp-status-card__label {
    display: block;
    font-size: 0.65rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.07em;
    color: var(--ink-300, #9ca3af);
    margin-bottom: 3px;
}
.bp-status-card__value {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 0.95rem;
    font-weight: 700;
    color: var(--ink-900, #0d1117);
}
.bp-status-card__value--muted { color: var(--ink-500); font-weight: 600; }
.bp-status-card__meta {
    display: flex;
    align-items: center;
    gap: 4px;
    font-size: 0.75rem;
    color: var(--ink-300);
    margin-top: 2px;
}
.bp-status-card__arrow { color: var(--ink-300); font-size: 1.2rem; flex-shrink: 0; transition: transform 0.2s; }
.bp-status-card--link:hover .bp-status-card__arrow { transform: translateX(3px); color: var(--accent); }

.bp-dot {
    width: 7px; height: 7px;
    border-radius: 50%;
    background: var(--ink-300);
    flex-shrink: 0;
}
.bp-dot--active {
    background: #22c55e;
    box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.2);
}

/* Main shell */
.bp-shell {
    background: var(--surface, #fff);
    border: 1px solid var(--border, #e5e8ee);
    border-radius: var(--radius-xl, 20px);
    box-shadow: var(--shadow-sm);
    overflow: hidden;
}

/* Tabs */
.bp-tabs-wrap {
    padding: 16px 16px 0;
    background: linear-gradient(180deg, #fafbfc 0%, #fff 100%);
    border-bottom: 1px solid var(--border-soft, #f0f2f5);
}
.bp-tabs {
    display: flex;
    gap: 6px;
    overflow-x: auto;
    scrollbar-width: none;
    padding-bottom: 12px;
}
.bp-tabs::-webkit-scrollbar { display: none; }
.bp-tab {
    flex-shrink: 0;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 9px 16px;
    border: 1px solid transparent;
    border-radius: var(--radius-pill, 999px);
    background: transparent;
    font-family: var(--font, inherit);
    font-size: 0.8125rem;
    font-weight: 500;
    color: var(--ink-500);
    cursor: pointer;
    transition: all 0.2s;
    white-space: nowrap;
}
.bp-tab i { font-size: 1rem; opacity: 0.75; }
.bp-tab:hover {
    color: var(--ink-700);
    background: var(--ink-50);
}
.bp-tab.is-active {
    color: var(--brand, #0f2a50);
    font-weight: 600;
    background: #fff;
    border-color: var(--border);
    box-shadow: var(--shadow-xs);
}
.bp-tab.is-active i { opacity: 1; color: var(--brand); }

/* Panels */
.bp-panels { padding: 24px 20px 28px; }
.bp-panel { display: none; animation: bp-panel-in 0.3s ease; }
.bp-panel.is-active { display: block; }
@keyframes bp-panel-in {
    from { opacity: 0; transform: translateY(8px); }
    to   { opacity: 1; transform: translateY(0); }
}
.bp-panel-head {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 16px;
    margin-bottom: 22px;
    flex-wrap: wrap;
}
.bp-panel-title {
    font-size: 1.2rem;
    font-weight: 700;
    color: var(--ink-900);
    margin: 0 0 4px;
    letter-spacing: -0.02em;
}
.bp-panel-desc {
    font-size: 0.875rem;
    color: var(--ink-500);
    margin: 0;
    line-height: 1.55;
    max-width: 520px;
}
.bp-link-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 14px;
    border: 1px solid var(--border);
    border-radius: var(--radius-sm, 8px);
    background: var(--ink-50);
    font-family: var(--font, inherit);
    font-size: 0.8125rem;
    font-weight: 600;
    color: var(--brand);
    cursor: pointer;
    transition: all 0.18s;
    white-space: nowrap;
}
.bp-link-btn:hover {
    background: var(--brand-light, rgba(15,42,80,.06));
    border-color: #c7d2e0;
}

/* Launch program hero card */
.bp-launch-card {
    position: relative;
    margin-bottom: 24px;
    padding: 22px 24px 24px;
    border-radius: var(--radius-xl, 20px);
    border: 1.5px solid #f59e0b;
    background: linear-gradient(135deg, #fffbeb 0%, #fff 45%, #fefce8 100%);
    box-shadow: 0 8px 28px rgba(245, 158, 11, 0.12);
    overflow: hidden;
}
.bp-launch-card--moneyback {
    border-color: #10b981;
    background: linear-gradient(145deg, #ecfdf5 0%, #fff 38%, #f0fdf4 72%, #fffbeb 100%);
    box-shadow: 0 12px 40px rgba(16, 185, 129, 0.14);
}
.bp-launch-card__glow {
    position: absolute;
    top: -80px;
    right: -60px;
    width: 260px;
    height: 260px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(16, 185, 129, 0.12) 0%, transparent 70%);
    pointer-events: none;
}
.bp-launch-card::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 4px;
    background: linear-gradient(90deg, #f59e0b, #fbbf24, #16a34a);
}
.bp-launch-card--moneyback::before {
    background: linear-gradient(90deg, #059669, #10b981, #34d399, #f59e0b);
}
.bp-launch-card--current {
    border-color: #16a34a;
    background: linear-gradient(135deg, #f0fdf4 0%, #fff 50%, #fffbeb 100%);
}
.bp-launch-card__badges {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-bottom: 16px;
}
.bp-launch-card__badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: 0.65rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    padding: 5px 10px;
    border-radius: 100px;
    background: linear-gradient(135deg, #f59e0b, #d97706);
    color: #fff;
}
.bp-launch-card__badge i { font-size: 0.75rem; }
.bp-launch-card__badge--duration {
    background: #fff;
    color: #b45309;
    border: 1px solid #fde68a;
}
.bp-launch-card__badge--new {
    background: rgba(15, 42, 80, 0.08);
    color: var(--brand);
}
.bp-launch-card__badge--promise {
    background: linear-gradient(135deg, #059669, #10b981);
    color: #fff;
}
.bp-launch-card__badge--riskfree {
    background: #fff;
    color: #047857;
    border: 1px solid #a7f3d0;
}
.bp-launch-card__grid {
    display: grid;
    grid-template-columns: minmax(260px, 320px) 1fr;
    gap: 24px;
    align-items: start;
    position: relative;
    z-index: 1;
}
.bp-launch-card__tier {
    display: block;
    font-size: 0.65rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: #b45309;
    margin-bottom: 4px;
}
.bp-launch-card--moneyback .bp-launch-card__tier { color: #047857; }
.bp-launch-card__title {
    font-size: 1.35rem;
    font-weight: 800;
    color: var(--ink-900);
    margin: 0 0 8px;
    letter-spacing: -0.03em;
    line-height: 1.2;
}
.bp-launch-card__tagline {
    font-size: 0.875rem;
    color: var(--ink-500);
    margin: 0 0 18px;
    line-height: 1.55;
}
.bp-launch-card__price {
    display: flex;
    align-items: baseline;
    flex-wrap: wrap;
    gap: 4px 8px;
    margin-bottom: 16px;
}
.bp-launch-card__currency {
    font-size: 1.1rem;
    font-weight: 700;
    color: var(--ink-600);
}
.bp-launch-card__amount {
    font-size: 2.25rem;
    font-weight: 800;
    color: var(--ink-900);
    letter-spacing: -0.04em;
    line-height: 1;
}
.bp-launch-card__period {
    font-size: 0.8rem;
    color: var(--ink-400);
    width: 100%;
}
.bp-launch-card__period em {
    font-style: normal;
    color: #059669;
    font-weight: 600;
}
.bp-launch-card__stats {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 10px;
    margin-bottom: 18px;
}
.bp-launch-card__stat {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    gap: 2px;
    padding: 12px 12px 10px;
    background: rgba(255, 255, 255, 0.85);
    border: 1px solid #d1fae5;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(16, 185, 129, 0.06);
}
.bp-launch-card__stat-icon {
    width: 28px;
    height: 28px;
    border-radius: 8px;
    background: #ecfdf5;
    color: #059669;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.95rem;
    margin-bottom: 4px;
}
.bp-launch-card__stat-value {
    font-size: 1.35rem;
    font-weight: 800;
    color: var(--ink-900);
    letter-spacing: -0.03em;
    line-height: 1.1;
}
.bp-launch-card__stat-label {
    font-size: 0.68rem;
    font-weight: 600;
    color: var(--ink-500);
    line-height: 1.3;
}
.bp-launch-card__ideal { margin-bottom: 18px; }
.bp-launch-card__ideal-label {
    display: block;
    font-size: 0.65rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: var(--ink-400);
    margin-bottom: 8px;
}
.bp-launch-card__ideal-pills {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
}
.bp-launch-card__ideal-pills span {
    font-size: 0.72rem;
    font-weight: 500;
    color: var(--ink-700);
    background: #fff;
    border: 1px solid #fde68a;
    padding: 4px 10px;
    border-radius: 100px;
}
.bp-launch-card__action { max-width: 300px; }
.bp-launch-card__cta-note {
    margin: 10px 0 0;
    font-size: 0.72rem;
    color: var(--ink-500);
    line-height: 1.45;
    display: flex;
    align-items: flex-start;
    gap: 5px;
}
.bp-launch-card__cta-note i {
    color: #059669;
    font-size: 0.85rem;
    margin-top: 1px;
    flex-shrink: 0;
}
.bp-btn--launch {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    color: #fff !important;
    box-shadow: 0 4px 14px rgba(245, 158, 11, 0.35);
}
.bp-btn--launch:hover:not(:disabled) {
    transform: translateY(-1px);
    box-shadow: 0 6px 20px rgba(245, 158, 11, 0.4);
}
.bp-launch-card--moneyback .bp-btn--launch {
    background: linear-gradient(135deg, #059669 0%, #10b981 55%, #34d399 100%);
    box-shadow: 0 4px 16px rgba(16, 185, 129, 0.35);
}
.bp-launch-card--moneyback .bp-btn--launch:hover:not(:disabled) {
    box-shadow: 0 6px 22px rgba(16, 185, 129, 0.42);
}
.bp-launch-card__details {
    display: flex;
    flex-direction: column;
    gap: 14px;
}
.bp-launch-card__promise {
    display: flex;
    gap: 14px;
    align-items: flex-start;
    padding: 16px 18px;
    border-radius: 14px;
    background: linear-gradient(135deg, #ecfdf5 0%, #fff 100%);
    border: 1.5px solid #6ee7b7;
    box-shadow: 0 4px 16px rgba(16, 185, 129, 0.1);
}
.bp-launch-card__promise-icon {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    background: linear-gradient(135deg, #059669, #10b981);
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.35rem;
    flex-shrink: 0;
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
}
.bp-launch-card__promise h4 {
    margin: 0 0 6px;
    font-size: 0.85rem;
    font-weight: 800;
    color: #047857;
    letter-spacing: -0.01em;
}
.bp-launch-card__promise p {
    margin: 0;
    font-size: 0.8rem;
    color: var(--ink-600);
    line-height: 1.55;
}
.bp-launch-card__sections {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 12px;
}
.bp-launch-section {
    background: rgba(255, 255, 255, 0.9);
    border: 1px solid var(--border);
    border-radius: 14px;
    padding: 14px 16px;
    transition: box-shadow 0.2s, border-color 0.2s;
}
.bp-launch-section:hover {
    box-shadow: 0 6px 20px rgba(15, 42, 80, 0.06);
}
.bp-launch-section--teal { border-top: 3px solid #14b8a6; }
.bp-launch-section--blue { border-top: 3px solid #3b82f6; }
.bp-launch-section--slate { border-top: 3px solid #64748b; }
.bp-launch-section--gold { border-top: 3px solid #f59e0b; }
.bp-launch-section__title {
    display: flex;
    align-items: center;
    gap: 8px;
    margin: 0 0 10px;
    font-size: 0.72rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: var(--ink-700);
}
.bp-launch-section__title i {
    font-size: 1rem;
    opacity: 0.85;
}
.bp-launch-section--teal .bp-launch-section__title i { color: #0d9488; }
.bp-launch-section--blue .bp-launch-section__title i { color: #2563eb; }
.bp-launch-section--slate .bp-launch-section__title i { color: #475569; }
.bp-launch-section--gold .bp-launch-section__title i { color: #d97706; }
.bp-launch-section__list {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    flex-direction: column;
    gap: 6px;
}
.bp-launch-section__list li {
    display: flex;
    align-items: flex-start;
    gap: 7px;
    font-size: 0.76rem;
    color: var(--ink-700);
    line-height: 1.4;
}
.bp-launch-section__list li i {
    color: #10b981;
    font-size: 0.72rem;
    margin-top: 3px;
    flex-shrink: 0;
}
.bp-launch-card__features,
.bp-launch-card__bonus {
    background: rgba(255, 255, 255, 0.75);
    border: 1px solid #fde68a;
    border-radius: var(--radius);
    padding: 14px 16px;
}
.bp-launch-card__features h4,
.bp-launch-card__bonus h4 {
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: var(--ink-600);
    margin: 0 0 10px;
    display: flex;
    align-items: center;
    gap: 6px;
}
.bp-launch-card__features ul,
.bp-launch-card__bonus ul {
    list-style: none;
    padding: 0;
    margin: 0;
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 6px 12px;
}
.bp-launch-card__features li {
    display: flex;
    align-items: flex-start;
    gap: 6px;
    font-size: 0.78rem;
    color: var(--ink-700);
    line-height: 1.4;
}
.bp-launch-card__features li i {
    color: #16a34a;
    font-size: 0.85rem;
    flex-shrink: 0;
    margin-top: 2px;
}
.bp-launch-card__bonus {
    background: linear-gradient(135deg, #fff 0%, #fef3c7 100%);
}
.bp-launch-card__bonus li {
    font-size: 0.78rem;
    color: var(--ink-600);
    line-height: 1.45;
    padding-left: 14px;
    position: relative;
}
.bp-launch-card__bonus li::before {
    content: '★';
    position: absolute;
    left: 0;
    color: #f59e0b;
    font-size: 0.65rem;
}

.bp-plans-divider {
    display: flex;
    align-items: center;
    gap: 14px;
    margin: 8px 0 20px;
    color: var(--ink-400);
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.06em;
}
.bp-plans-divider::before,
.bp-plans-divider::after {
    content: '';
    flex: 1;
    height: 1px;
    background: var(--border);
}

.bp-plan--launch::after { background: linear-gradient(90deg, #f59e0b, #fbbf24); }
.bp-plan--launch .bp-plan__icon { background: #fffbeb; color: #d97706; }

.bp-table th.bp-table__launch,
.bp-table td.bp-table__launch {
    background: rgba(245, 158, 11, 0.06);
}
.bp-table th.bp-table__launch { color: #b45309; font-weight: 700; }
.bp-table__launch-badge {
    display: block;
    font-size: 0.55rem;
    font-weight: 700;
    text-transform: uppercase;
    color: #d97706;
    margin-top: 2px;
}

/* Plan cards */
.bp-plans-track {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 14px;
    margin-bottom: 20px;
    align-items: stretch;
}
.bp-plan {
    position: relative;
    display: flex;
    flex-direction: column;
    background: #fff;
    border: 1.5px solid var(--border);
    border-radius: var(--radius-lg, 16px);
    padding: 22px 18px 18px;
    transition: border-color 0.22s, box-shadow 0.22s, transform 0.22s;
    overflow: hidden;
}
.bp-plan::after {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 3px;
    opacity: 0.85;
}
.bp-plan--starter::after  { background: linear-gradient(90deg, #94a3b8, #cbd5e1); }
.bp-plan--growth::after   { background: linear-gradient(90deg, #2563eb, #60a5fa); }
.bp-plan--scale::after    { background: linear-gradient(90deg, #7c3aed, #a78bfa); }
.bp-plan--enterprise::after { background: linear-gradient(90deg, #d97706, #fbbf24); }
.bp-plan:hover {
    border-color: #c7d2e0;
    box-shadow: var(--shadow-md);
    transform: translateY(-3px);
}
.bp-plan--popular {
    border-color: var(--brand);
    box-shadow: 0 8px 28px rgba(15, 42, 80, 0.14);
    z-index: 1;
}
.bp-plan--popular:hover { box-shadow: 0 12px 36px rgba(15, 42, 80, 0.18); }
.bp-plan--current {
    border-color: #16a34a;
    background: linear-gradient(180deg, #f0fdf4 0%, #fff 35%);
}
.bp-plan__ribbon {
    position: absolute;
    top: 14px;
    right: 12px;
    display: inline-flex;
    align-items: center;
    gap: 3px;
    background: var(--brand);
    color: #fff;
    font-size: 0.6rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    padding: 4px 9px;
    border-radius: 100px;
}
.bp-plan__ribbon i { font-size: 0.65rem; }
.bp-plan__ribbon--current { background: #16a34a; }
.bp-plan__top {
    display: flex;
    align-items: center;
    gap: 11px;
    margin-bottom: 10px;
    padding-top: 6px;
}
.bp-plan__icon {
    width: 40px;
    height: 40px;
    border-radius: 11px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.15rem;
    flex-shrink: 0;
    background: var(--ink-50);
    color: var(--ink-500);
}
.bp-plan--starter .bp-plan__icon   { background: #f1f5f9; color: #64748b; }
.bp-plan--growth .bp-plan__icon    { background: #eff6ff; color: #2563eb; }
.bp-plan--scale .bp-plan__icon     { background: #f5f3ff; color: #7c3aed; }
.bp-plan--enterprise .bp-plan__icon { background: #fffbeb; color: #d97706; }
.bp-plan--popular .bp-plan__icon   { background: rgba(15,42,80,0.08); color: var(--brand); }
.bp-plan__tier {
    display: block;
    font-size: 0.625rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: var(--ink-300);
    margin-bottom: 1px;
}
.bp-plan__name {
    font-size: 1.05rem;
    font-weight: 700;
    color: var(--ink-900);
    margin: 0;
    letter-spacing: -0.02em;
}
.bp-plan__tagline {
    font-size: 0.75rem;
    color: var(--ink-500);
    margin: 0 0 16px;
    line-height: 1.5;
    min-height: 2.4em;
}
.bp-plan__price {
    margin-bottom: 14px;
    padding-bottom: 14px;
    border-bottom: 1px dashed var(--border-soft, #f0f2f5);
}
.bp-plan__price-row {
    display: flex;
    align-items: flex-start;
    gap: 2px;
}
.bp-plan__currency {
    font-size: 0.95rem;
    font-weight: 600;
    color: var(--ink-500);
    margin-top: 4px;
}
.bp-plan__amount {
    font-size: 1.85rem;
    font-weight: 800;
    color: var(--ink-900);
    letter-spacing: -0.04em;
    line-height: 1;
    font-variant-numeric: tabular-nums;
}
.bp-plan__period {
    display: block;
    font-size: 0.72rem;
    color: var(--ink-300);
    margin-top: 5px;
}
.bp-plan__duration {
    margin-bottom: 14px;
}
.bp-plan__duration-label {
    display: block;
    font-size: 0.65rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: var(--ink-400, #9ca3af);
    margin-bottom: 8px;
}
.bp-plan__duration-options {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 6px;
}
.bp-plan__duration-btn {
    border: 1px solid var(--border-soft, #e5e7eb);
    background: #fff;
    border-radius: 8px;
    padding: 7px 4px;
    font-size: 0.72rem;
    font-weight: 700;
    color: var(--ink-500);
    cursor: pointer;
    transition: border-color 0.15s, background 0.15s, color 0.15s;
}
.bp-plan__duration-btn:hover {
    border-color: #93c5fd;
    color: #2563eb;
}
.bp-plan__duration-btn.is-active {
    border-color: #2563eb;
    background: #eff6ff;
    color: #1d4ed8;
    box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.12);
}
.bp-plan__feat-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 10px;
    font-size: 0.65rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: var(--ink-400, #9ca3af);
}
.bp-plan__feat-count {
    background: var(--ink-50);
    padding: 2px 8px;
    border-radius: 100px;
    font-size: 0.6rem;
}
.bp-plan__features {
    list-style: none;
    padding: 0;
    margin: 0 0 18px;
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 7px;
}
.bp-plan__features li {
    display: flex;
    align-items: flex-start;
    gap: 8px;
    font-size: 0.8rem;
    color: var(--ink-700);
    line-height: 1.4;
}
.bp-plan__features li i {
    color: #16a34a;
    font-size: 0.85rem;
    flex-shrink: 0;
    margin-top: 2px;
}
.bp-plan__more {
    color: var(--ink-300) !important;
    font-size: 0.72rem !important;
    font-style: italic;
    padding-left: 1.3rem !important;
}
.bp-plan__action { margin-top: auto; }

/* Buttons */
.bp-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    width: 100%;
    padding: 11px 16px;
    border-radius: 10px;
    font-size: 0.875rem;
    font-weight: 600;
    font-family: var(--font, inherit);
    text-decoration: none;
    border: none;
    cursor: pointer;
    transition: background 0.18s, transform 0.15s, box-shadow 0.18s;
}
.bp-btn i { font-size: 1rem; transition: transform 0.15s; }
.bp-btn:hover i { transform: translateX(2px); }
.bp-btn--primary {
    background: linear-gradient(135deg, #16a34a 0%, #15803d 100%);
    color: #fff !important;
    box-shadow: 0 3px 10px rgba(22, 163, 74, 0.28);
}
.bp-btn--primary:hover:not(:disabled) {
    transform: translateY(-1px);
    box-shadow: 0 5px 16px rgba(22, 163, 74, 0.35);
}
.bp-plan--popular .bp-btn--primary {
    background: linear-gradient(135deg, #0f2a50 0%, #163d73 100%);
    box-shadow: 0 3px 12px rgba(15, 42, 80, 0.25);
}
.bp-plan--popular .bp-btn--primary:hover:not(:disabled) {
    box-shadow: 0 6px 18px rgba(15, 42, 80, 0.32);
}
.bp-btn--disabled {
    background: var(--ink-100);
    color: var(--ink-500) !important;
    cursor: default;
    border: 1px solid var(--border);
    box-shadow: none;
}
.bp-btn--pending {
    background: #fffbeb;
    color: #b45309 !important;
    border: 1px solid #fde68a;
    cursor: default;
    box-shadow: none;
}
.bp-btn--cta {
    background: #fff;
    color: var(--brand) !important;
    width: auto;
    padding: 12px 22px;
    border-radius: 11px;
    box-shadow: 0 4px 16px rgba(0,0,0,0.15);
    flex-shrink: 0;
}
.bp-btn--cta:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 22px rgba(0,0,0,0.2);
}

/* Info card */
.bp-info-card {
    display: flex;
    gap: 14px;
    padding: 15px 18px;
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    font-size: 0.8125rem;
    color: var(--ink-500);
    line-height: 1.55;
}
.bp-info-card--tip {
    margin-top: 16px;
    background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);
    border-color: #fde68a;
}
.bp-info-card__icon {
    width: 36px;
    height: 36px;
    border-radius: 9px;
    background: #fff;
    border: 1px solid var(--border);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.1rem;
    color: var(--accent);
    flex-shrink: 0;
}
.bp-info-card--tip .bp-info-card__icon { color: #d97706; border-color: #fde68a; }
.bp-info-card strong {
    display: block;
    color: var(--ink-700);
    font-weight: 600;
    margin-bottom: 4px;
}
.bp-info-card p { margin: 0; }

/* Table */
.bp-table-wrap {
    overflow-x: auto;
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    background: #fff;
    box-shadow: var(--shadow-xs);
}
.bp-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.8125rem;
    min-width: 680px;
}
.bp-table th, .bp-table td {
    padding: 13px 16px;
    text-align: center;
    border-bottom: 1px solid var(--border-soft);
}
.bp-table th {
    background: #f8fafc;
    font-weight: 600;
    color: var(--ink-700);
    position: sticky;
    top: 0;
    z-index: 1;
}
.bp-table th:first-child, .bp-table td:first-child {
    text-align: left;
    color: var(--ink-600, #4b5563);
    font-weight: 500;
}
.bp-table th.bp-table__highlight,
.bp-table td.bp-table__highlight { background: rgba(15, 42, 80, 0.035); }
.bp-table th.bp-table__highlight { color: var(--brand); font-weight: 700; }
.bp-table tbody tr:hover td { background: rgba(37, 99, 235, 0.03); }
.bp-table tbody tr:hover td.bp-table__highlight { background: rgba(15, 42, 80, 0.06); }
.bp-table tr:last-child td { border-bottom: none; }
.bp-table__badge {
    display: block;
    font-size: 0.55rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: #2563eb;
    margin-top: 2px;
}
.bp-table__cat td {
    background: #f1f5f9;
    font-weight: 700;
    font-size: 0.65rem;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: var(--ink-500);
}
.bp-table__check {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 24px;
    height: 24px;
    border-radius: 50%;
    background: #ecfdf5;
    color: #16a34a;
    font-size: 0.75rem;
}
.bp-table__no { color: var(--ink-300); }
.bp-table__tag {
    font-size: 0.65rem;
    font-weight: 600;
    color: var(--brand);
    background: rgba(15,42,80,0.07);
    padding: 3px 9px;
    border-radius: 100px;
}
.bp-table__text { font-weight: 600; color: var(--ink-700); }

/* Pay per hire */
.bp-pph-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 12px;
    margin-bottom: 4px;
}
.bp-pph-item {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 16px 18px;
    background: #fff;
    border: 1px solid var(--border);
    border-radius: var(--radius);
    transition: border-color 0.18s, box-shadow 0.18s;
}
.bp-pph-item:hover {
    border-color: #c7d2e0;
    box-shadow: var(--shadow-xs);
}
.bp-pph-item__icon {
    width: 46px;
    height: 46px;
    border-radius: 11px;
    background: linear-gradient(135deg, #f8fafc, #f1f5f9);
    border: 1px solid var(--border-soft);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.3rem;
    flex-shrink: 0;
}
.bp-pph-item__body { flex: 1; min-width: 0; }
.bp-pph-item__level {
    display: block;
    font-size: 0.65rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: var(--ink-300);
}
.bp-pph-item__name {
    display: block;
    font-size: 0.9rem;
    font-weight: 600;
    color: var(--ink-900);
}
.bp-pph-item__price {
    font-size: 0.8rem;
    color: var(--ink-500);
    text-align: right;
    flex-shrink: 0;
    max-width: 42%;
}
.bp-pph-item__price strong { color: var(--brand); font-size: 0.9rem; }

/* Add-ons */
.bp-addons-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 14px;
}
.bp-addon {
    padding: 18px 16px;
    background: #fff;
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    transition: border-color 0.2s, box-shadow 0.2s, transform 0.2s;
}
.bp-addon:hover {
    border-color: #c7d2e0;
    box-shadow: var(--shadow-sm);
    transform: translateY(-2px);
}
.bp-addon--featured {
    background: linear-gradient(145deg, #0f2a50, #1a3a6b);
    border-color: transparent;
    color: #fff;
}
.bp-addon__top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 10px;
}
.bp-addon__icon { font-size: 1.4rem; }
.bp-addon__chip {
    font-size: 0.55rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    background: rgba(255,255,255,0.15);
    padding: 3px 8px;
    border-radius: 100px;
}
.bp-addon__name {
    font-size: 0.9rem;
    font-weight: 700;
    margin: 0 0 4px;
    color: var(--ink-900);
}
.bp-addon--featured .bp-addon__name { color: #fff; }
.bp-addon__price { font-size: 0.8rem; color: var(--ink-500); margin-bottom: 8px; }
.bp-addon--featured .bp-addon__price { color: rgba(255,255,255,0.65); }
.bp-addon__price strong { color: var(--brand); font-weight: 700; }
.bp-addon--featured .bp-addon__price strong { color: #86efac; }
.bp-addon__desc { font-size: 0.75rem; color: var(--ink-500); line-height: 1.5; margin: 0; }
.bp-addon--featured .bp-addon__desc { color: rgba(255,255,255,0.6); }
.bp-addon__link {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    margin-top: 12px;
    font-size: 0.8rem;
    font-weight: 600;
    color: var(--accent);
    text-decoration: none;
}
.bp-addon--featured .bp-addon__link { color: #93c5fd; }

/* Footer CTA */
.bp-footer-cta {
    position: relative;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 24px;
    flex-wrap: wrap;
    margin-top: 24px;
    padding: 26px 28px;
    background: linear-gradient(135deg, #0f2a50 0%, #163d73 55%, #1e4d8f 100%);
    border-radius: var(--radius-xl);
    overflow: hidden;
    box-shadow: 0 8px 32px rgba(15, 42, 80, 0.22);
}
.bp-footer-cta__glow {
    position: absolute;
    top: -60px; right: -40px;
    width: 200px; height: 200px;
    background: radial-gradient(circle, rgba(96,165,250,0.25), transparent 70%);
    pointer-events: none;
}
.bp-footer-cta__content { position: relative; z-index: 1; flex: 1; min-width: 220px; }
.bp-footer-cta__tag {
    display: inline-block;
    font-size: 0.65rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: #93c5fd;
    background: rgba(255,255,255,0.1);
    padding: 4px 10px;
    border-radius: 100px;
    margin-bottom: 8px;
}
.bp-footer-cta h3 {
    font-size: 1.1rem;
    font-weight: 700;
    color: #fff;
    margin: 0 0 6px;
    letter-spacing: -0.02em;
}
.bp-footer-cta p {
    font-size: 0.8125rem;
    color: rgba(255,255,255,0.6);
    margin: 0 0 12px;
    max-width: 480px;
    line-height: 1.5;
}
.bp-trust-pills {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}
.bp-trust-pill {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: 0.7rem;
    color: rgba(255,255,255,0.55);
    background: rgba(255,255,255,0.08);
    padding: 4px 10px;
    border-radius: 100px;
    border: 1px solid rgba(255,255,255,0.1);
}
.bp-trust-pill i { font-size: 0.65rem; color: #86efac; }

/* Responsive */
@media (max-width: 1024px) {
    .bp-plans-track { grid-template-columns: repeat(2, 1fr); }
    .bp-addons-grid { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 900px) {
    .bp-launch-card__grid { grid-template-columns: 1fr; }
    .bp-launch-card__sections { grid-template-columns: 1fr; }
    .bp-launch-card__features ul { grid-template-columns: 1fr; }
    .bp-launch-card__action { max-width: none; }
}
@media (max-width: 640px) {
    .bp-status-row { grid-template-columns: 1fr; }
    .bp-plans-track { grid-template-columns: 1fr; }
    .bp-pph-grid { grid-template-columns: 1fr; }
    .bp-addons-grid { grid-template-columns: 1fr; }
    .bp-panels { padding: 18px 14px 22px; }
    .bp-tabs-wrap { padding: 12px 12px 0; }
    .bp-tab { padding: 8px 12px; font-size: 0.75rem; }
    .bp-tab i { display: none; }
    .bp-footer-cta { flex-direction: column; align-items: stretch; padding: 22px 18px; }
    .bp-btn--cta { width: 100%; }
    .bp-panel-head { flex-direction: column; }
}
</style>
@endpush
