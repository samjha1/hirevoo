@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700;800&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;1,9..40,300&display=swap" rel="stylesheet">
<style>
.hp-pricing-bleed {
    margin-left: calc(-1 * var(--ec-inline, 1rem));
    margin-right: calc(-1 * var(--ec-inline, 1rem));
    width: calc(100% + 2 * var(--ec-inline, 1rem));
    max-width: none;
}
.hp-pricing-wrap {
    --hp-teal: #2EC4B6;
    --hp-teal-light: #E6FAF9;
    --hp-teal-mid: #1AA399;
    --hp-blue: #3A7DFF;
    --hp-blue-light: #EBF2FF;
    --hp-dark: #0F172A;
    --hp-dark-800: #1E293B;
    --hp-dark-700: #334155;
    --hp-dark-500: #64748B;
    --hp-dark-400: #94A3B8;
    --hp-dark-200: #CBD5E1;
    --hp-dark-100: #E2E8F0;
    --hp-bg: #F8FAFC;
    --hp-white: #FFFFFF;
    --hp-radius-sm: 8px;
    --hp-radius-md: 12px;
    --hp-radius-lg: 16px;
    --hp-radius-xl: 24px;
    --hp-shadow-sm: 0 1px 3px rgba(15,23,42,0.06), 0 1px 2px rgba(15,23,42,0.04);
    --hp-shadow-md: 0 4px 16px rgba(15,23,42,0.08), 0 2px 6px rgba(15,23,42,0.05);
    --hp-shadow-lg: 0 12px 40px rgba(15,23,42,0.10), 0 4px 16px rgba(15,23,42,0.06);
    font-family: 'DM Sans', sans-serif;
    background: var(--hp-bg);
    color: var(--hp-dark);
    line-height: 1.6;
    -webkit-font-smoothing: antialiased;
}
.hp-pricing-wrap *, .hp-pricing-wrap *::before, .hp-pricing-wrap *::after { box-sizing: border-box; }
.hp-employer-bar {
    background: var(--hp-white);
    border: 1px solid var(--hp-dark-100);
    border-radius: var(--hp-radius-lg);
    padding: 14px 20px;
    margin-bottom: 24px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 12px;
    font-size: 14px;
    box-shadow: var(--hp-shadow-sm);
}
.hp-employer-bar strong { color: var(--hp-teal-mid); }
.hp-hero { text-align: center; padding: 48px 24px 40px; max-width: 860px; margin: 0 auto; }
.hp-hero-badge {
    display: inline-flex; align-items: center; gap: 6px;
    background: var(--hp-teal-light); color: var(--hp-teal-mid);
    border: 1px solid rgba(46,196,182,0.3); border-radius: 100px;
    padding: 5px 14px; font-size: 12px; font-weight: 600;
    letter-spacing: 0.06em; text-transform: uppercase; margin-bottom: 24px;
}
.hp-hero-badge::before { content: ''; width: 6px; height: 6px; background: var(--hp-teal); border-radius: 50%; }
.hp-hero h1 {
    font-family: 'Sora', sans-serif;
    font-size: clamp(28px, 4vw, 48px);
    font-weight: 800; line-height: 1.15; color: var(--hp-dark);
    margin-bottom: 18px; letter-spacing: -0.03em;
}
.hp-hero h1 .hp-gradient {
    background: linear-gradient(135deg, var(--hp-teal), var(--hp-blue));
    -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
}
.hp-hero > p { font-size: 17px; color: var(--hp-dark-500); max-width: 560px; margin: 0 auto 32px; line-height: 1.65; }
.hp-hero-stats {
    display: inline-flex; justify-content: center; align-items: center; gap: 32px;
    padding: 20px 32px; background: var(--hp-white);
    border: 1px solid var(--hp-dark-100); border-radius: var(--hp-radius-xl);
    box-shadow: var(--hp-shadow-sm); flex-wrap: wrap;
}
.hp-stat { text-align: center; }
.hp-stat-number { font-family: 'Sora', sans-serif; font-size: 22px; font-weight: 700; color: var(--hp-dark); }
.hp-stat-number span { color: var(--hp-teal); }
.hp-stat-label { font-size: 12px; color: var(--hp-dark-400); font-weight: 500; }
.hp-stat-divider { width: 1px; background: var(--hp-dark-100); align-self: stretch; min-height: 40px; }
.hp-section-wrap { max-width: 1200px; margin: 0 auto; padding: 0 24px; }
.hp-section-header { text-align: center; margin-bottom: 40px; }
.hp-section-tag {
    display: inline-block; font-size: 11px; font-weight: 700;
    letter-spacing: 0.1em; text-transform: uppercase; color: var(--hp-teal-mid); margin-bottom: 12px;
}
.hp-section-header h2 {
    font-family: 'Sora', sans-serif;
    font-size: clamp(24px, 3vw, 34px); font-weight: 700;
    color: var(--hp-dark); letter-spacing: -0.025em; margin-bottom: 12px;
}
.hp-section-header p { font-size: 16px; color: var(--hp-dark-500); max-width: 520px; margin: 0 auto; }
.hp-section-divider {
    height: 1px;
    background: linear-gradient(90deg, transparent, var(--hp-dark-100) 30%, var(--hp-dark-100) 70%, transparent);
}
.hp-plans-section { padding: 64px 0; }
.hp-plans-grid {
    display: grid; grid-template-columns: repeat(4, 1fr); gap: 0;
    border: 1px solid var(--hp-dark-100); border-radius: var(--hp-radius-xl);
    overflow: hidden; background: var(--hp-white); box-shadow: var(--hp-shadow-lg);
}
.hp-plan-card {
    padding: 32px 24px; border-right: 1px solid var(--hp-dark-100);
    display: flex; flex-direction: column; position: relative;
}
.hp-plan-card:last-child { border-right: none; }
.hp-plan-card.hp-popular {
    background: var(--hp-dark); border-right: 1px solid rgba(255,255,255,0.08); color: var(--hp-white);
}
.hp-popular-badge {
    position: absolute; top: -1px; left: 50%; transform: translateX(-50%);
    background: linear-gradient(90deg, var(--hp-teal), var(--hp-blue));
    color: white; font-size: 10px; font-weight: 700;
    letter-spacing: 0.08em; text-transform: uppercase;
    padding: 4px 14px; border-radius: 0 0 8px 8px; white-space: nowrap;
}
.hp-plan-tier {
    font-size: 11px; font-weight: 700; letter-spacing: 0.1em;
    text-transform: uppercase; color: var(--hp-dark-400); margin-bottom: 10px;
}
.hp-plan-card.hp-popular .hp-plan-tier { color: rgba(255,255,255,0.45); }
.hp-plan-name {
    font-family: 'Sora', sans-serif; font-size: 20px; font-weight: 700;
    color: var(--hp-dark); margin-bottom: 4px;
}
.hp-plan-card.hp-popular .hp-plan-name { color: var(--hp-white); }
.hp-plan-best { font-size: 12px; color: var(--hp-dark-400); margin-bottom: 20px; line-height: 1.4; }
.hp-plan-card.hp-popular .hp-plan-best { color: rgba(255,255,255,0.45); }
.hp-plan-price {
    font-family: 'Sora', sans-serif; font-size: 30px; font-weight: 800;
    color: var(--hp-dark); letter-spacing: -0.03em;
}
.hp-plan-card.hp-popular .hp-plan-price { color: var(--hp-white); }
.hp-plan-price .hp-currency { font-size: 17px; font-weight: 600; vertical-align: super; margin-right: 1px; }
.hp-plan-price-sub { font-size: 12px; color: var(--hp-dark-400); margin-bottom: 20px; }
.hp-plan-card.hp-popular .hp-plan-price-sub { color: rgba(255,255,255,0.4); }
.hp-plan-custom-price {
    font-family: 'Sora', sans-serif; font-size: 24px; font-weight: 700;
    color: var(--hp-teal); margin-bottom: 4px;
}
.hp-plan-divider { height: 1px; background: var(--hp-dark-100); margin: 0 0 18px; }
.hp-plan-card.hp-popular .hp-plan-divider { background: rgba(255,255,255,0.1); }
.hp-plan-cta {
    display: block; text-align: center; padding: 11px 20px;
    border-radius: var(--hp-radius-sm); font-size: 14px; font-weight: 600;
    text-decoration: none; transition: all .2s; margin-bottom: 20px;
}
.hp-plan-card:not(.hp-popular) .hp-plan-cta {
    background: var(--hp-bg); color: var(--hp-dark); border: 1px solid var(--hp-dark-200);
}
.hp-plan-card:not(.hp-popular) .hp-plan-cta:hover {
    background: var(--hp-dark); color: var(--hp-white); border-color: var(--hp-dark);
}
.hp-plan-card.hp-popular .hp-plan-cta {
    background: linear-gradient(135deg, var(--hp-teal), var(--hp-blue));
    color: var(--hp-white); border: none;
    box-shadow: 0 4px 14px rgba(46,196,182,0.35);
}
.hp-plan-card.hp-popular .hp-plan-cta:hover { opacity: .9; transform: translateY(-1px); }
.hp-plan-current {
    font-size: 10px; font-weight: 700; text-transform: uppercase;
    letter-spacing: 0.06em; color: var(--hp-teal); margin-bottom: 8px;
}
.hp-features-label {
    font-size: 11px; font-weight: 600; text-transform: uppercase;
    letter-spacing: 0.08em; color: var(--hp-dark-400); margin-bottom: 12px;
}
.hp-plan-card.hp-popular .hp-features-label { color: rgba(255,255,255,0.35); }
.hp-feature-list { list-style: none; display: flex; flex-direction: column; gap: 10px; flex: 1; padding: 0; margin: 0; }
.hp-feature-item {
    display: flex; align-items: flex-start; gap: 10px;
    font-size: 13px; color: var(--hp-dark-700); line-height: 1.4;
}
.hp-plan-card.hp-popular .hp-feature-item { color: rgba(255,255,255,0.8); }
.hp-check-icon {
    width: 18px; height: 18px; border-radius: 50%;
    background: var(--hp-teal-light); display: flex;
    align-items: center; justify-content: center; flex-shrink: 0; margin-top: 1px;
}
.hp-plan-card.hp-popular .hp-check-icon { background: rgba(46,196,182,0.2); }
.hp-check-icon svg { width: 10px; height: 10px; }
.hp-comparison-section { padding: 64px 0; background: var(--hp-white); }
.hp-compare-scroll {
    overflow-x: auto; border-radius: var(--hp-radius-lg);
    border: 1px solid var(--hp-dark-100); box-shadow: var(--hp-shadow-sm);
}
.hp-compare-table { width: 100%; border-collapse: collapse; font-size: 14px; min-width: 640px; }
.hp-compare-table th {
    padding: 14px 16px; text-align: center;
    font-family: 'Sora', sans-serif; font-size: 13px; font-weight: 700;
    border-bottom: 2px solid var(--hp-dark-100); color: var(--hp-dark);
}
.hp-compare-table th:first-child { text-align: left; color: var(--hp-dark-400); font-weight: 500; }
.hp-compare-table th.hp-popular-col {
    background: var(--hp-dark); color: var(--hp-white);
    border-radius: var(--hp-radius-sm) var(--hp-radius-sm) 0 0;
}
.hp-compare-table td {
    padding: 12px 16px; text-align: center;
    border-bottom: 1px solid var(--hp-dark-100); color: var(--hp-dark-700); vertical-align: middle;
}
.hp-compare-table td:first-child { text-align: left; color: var(--hp-dark-500); font-size: 13px; }
.hp-compare-table tr:last-child td { border-bottom: none; }
.hp-compare-table td.hp-popular-col { background: rgba(15,23,42,0.03); }
.hp-compare-table .hp-cat-row td {
    background: var(--hp-bg); font-weight: 600; font-size: 12px;
    letter-spacing: 0.06em; text-transform: uppercase; color: var(--hp-dark-500); padding: 10px 16px;
}
.hp-check { color: var(--hp-teal); font-size: 18px; font-weight: 700; }
.hp-dash { color: var(--hp-dark-200); font-size: 18px; }
.hp-partial { font-size: 12px; color: var(--hp-dark-400); }
.hp-pph-section { padding: 64px 0; }
.hp-pph-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px; }
.hp-pph-card {
    background: var(--hp-white); border: 1px solid var(--hp-dark-100);
    border-radius: var(--hp-radius-lg); padding: 24px 26px;
    box-shadow: var(--hp-shadow-sm); display: flex; align-items: center; gap: 18px;
    transition: box-shadow .2s, transform .2s;
}
.hp-pph-card:hover { box-shadow: var(--hp-shadow-md); transform: translateY(-2px); }
.hp-pph-icon {
    width: 48px; height: 48px; border-radius: var(--hp-radius-md);
    display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: 22px;
}
.hp-pph-icon.teal { background: var(--hp-teal-light); }
.hp-pph-icon.blue { background: var(--hp-blue-light); }
.hp-pph-level {
    font-size: 12px; font-weight: 700; text-transform: uppercase;
    letter-spacing: 0.08em; color: var(--hp-dark-400); margin-bottom: 4px;
}
.hp-pph-name { font-family: 'Sora', sans-serif; font-size: 15px; font-weight: 700; color: var(--hp-dark); margin-bottom: 4px; }
.hp-pph-price { font-size: 13px; color: var(--hp-dark-500); }
.hp-pph-price strong { font-family: 'Sora', sans-serif; font-size: 18px; font-weight: 700; color: var(--hp-teal-mid); }
.hp-pph-note {
    margin-top: 20px; background: var(--hp-white); border: 1px solid var(--hp-dark-100);
    border-radius: var(--hp-radius-lg); padding: 18px 24px;
    display: flex; align-items: center; gap: 16px; box-shadow: var(--hp-shadow-sm);
}
.hp-pph-note-icon {
    width: 40px; height: 40px;
    background: linear-gradient(135deg, var(--hp-teal-light), var(--hp-blue-light));
    border-radius: 10px; display: flex; align-items: center; justify-content: center;
    font-size: 18px; flex-shrink: 0;
}
.hp-addons-section { padding: 64px 0; background: var(--hp-white); }
.hp-addons-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; }
.hp-addon-card {
    border: 1px solid var(--hp-dark-100); border-radius: var(--hp-radius-lg);
    padding: 26px 22px; transition: all .2s; position: relative; overflow: hidden;
}
.hp-addon-card::before {
    content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px;
    background: linear-gradient(90deg, var(--hp-teal), var(--hp-blue));
    opacity: 0; transition: opacity .2s;
}
.hp-addon-card:hover::before { opacity: 1; }
.hp-addon-card:hover { box-shadow: var(--hp-shadow-md); transform: translateY(-2px); }
.hp-addon-card.hp-dark {
    background: linear-gradient(135deg, var(--hp-dark) 0%, #1E293B 100%); border: none;
}
.hp-addon-icon-wrap {
    width: 44px; height: 44px; border-radius: 10px; background: var(--hp-bg);
    display: flex; align-items: center; justify-content: center;
    font-size: 20px; margin-bottom: 14px; border: 1px solid var(--hp-dark-100);
}
.hp-addon-card.hp-dark .hp-addon-icon-wrap {
    background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.1);
}
.hp-addon-name { font-family: 'Sora', sans-serif; font-size: 15px; font-weight: 700; color: var(--hp-dark); margin-bottom: 4px; }
.hp-addon-card.hp-dark .hp-addon-name { color: var(--hp-white); }
.hp-addon-price { font-size: 13px; color: var(--hp-dark-400); margin-bottom: 10px; }
.hp-addon-card.hp-dark .hp-addon-price { color: rgba(255,255,255,0.5); }
.hp-addon-price strong { color: var(--hp-teal-mid); font-weight: 600; font-size: 15px; }
.hp-addon-desc { font-size: 12.5px; color: var(--hp-dark-500); line-height: 1.55; }
.hp-addon-card.hp-dark .hp-addon-desc { color: rgba(255,255,255,0.55); }
.hp-addon-link {
    display: inline-block; margin-top: 14px; font-size: 13px;
    font-weight: 600; color: var(--hp-teal); text-decoration: none;
}
.hp-cta-section { padding: 64px 0 48px; }
.hp-cta-inner {
    background: var(--hp-dark); border-radius: var(--hp-radius-xl);
    padding: 56px 32px; text-align: center; position: relative; overflow: hidden;
}
.hp-cta-inner::before {
    content: ''; position: absolute; top: -60px; left: -60px;
    width: 240px; height: 240px;
    background: radial-gradient(circle, rgba(46,196,182,0.2), transparent 70%);
    border-radius: 50%; pointer-events: none;
}
.hp-cta-inner::after {
    content: ''; position: absolute; bottom: -60px; right: -60px;
    width: 240px; height: 240px;
    background: radial-gradient(circle, rgba(58,125,255,0.2), transparent 70%);
    border-radius: 50%; pointer-events: none;
}
.hp-cta-tag {
    display: inline-block; background: rgba(46,196,182,0.15); color: var(--hp-teal);
    border: 1px solid rgba(46,196,182,0.25); border-radius: 100px;
    font-size: 11px; font-weight: 700; letter-spacing: 0.1em;
    text-transform: uppercase; padding: 5px 14px; margin-bottom: 18px; position: relative; z-index: 1;
}
.hp-cta-inner h2 {
    font-family: 'Sora', sans-serif;
    font-size: clamp(26px, 3.5vw, 38px); font-weight: 800;
    color: var(--hp-white); letter-spacing: -0.03em;
    margin-bottom: 14px; line-height: 1.2; position: relative; z-index: 1;
}
.hp-cta-inner > p {
    font-size: 16px; color: rgba(255,255,255,0.55);
    max-width: 480px; margin: 0 auto 32px; position: relative; z-index: 1;
}
.hp-cta-buttons {
    display: flex; align-items: center; justify-content: center;
    gap: 12px; flex-wrap: wrap; position: relative; z-index: 1;
}
.hp-btn-primary {
    background: linear-gradient(135deg, var(--hp-teal), var(--hp-blue));
    color: white; padding: 14px 28px; border-radius: var(--hp-radius-md);
    font-size: 15px; font-weight: 600; text-decoration: none;
    box-shadow: 0 4px 16px rgba(46,196,182,0.35);
    transition: transform .2s, opacity .2s;
}
.hp-btn-primary:hover { transform: translateY(-2px); opacity: .92; color: white; }
.hp-btn-secondary {
    background: rgba(255,255,255,0.08); color: rgba(255,255,255,0.85);
    padding: 14px 28px; border-radius: var(--hp-radius-md);
    font-size: 15px; font-weight: 600; text-decoration: none;
    border: 1px solid rgba(255,255,255,0.15); transition: all .2s;
}
.hp-btn-secondary:hover { background: rgba(255,255,255,0.14); color: white; }
.hp-cta-trust {
    display: flex; align-items: center; justify-content: center;
    gap: 20px; margin-top: 24px; flex-wrap: wrap; position: relative; z-index: 1;
}
.hp-trust-item {
    display: flex; align-items: center; gap: 7px;
    font-size: 13px; color: rgba(255,255,255,0.45);
}
.hp-trust-dot { width: 6px; height: 6px; background: var(--hp-teal); border-radius: 50%; opacity: 0.6; }
.hp-credits-box {
    background: var(--hp-white); border: 1px solid var(--hp-dark-100);
    border-radius: var(--hp-radius-lg); padding: 24px; margin-top: 32px;
    box-shadow: var(--hp-shadow-sm);
}
.hp-credits-box h3 { font-family: 'Sora', sans-serif; font-size: 16px; font-weight: 700; margin-bottom: 10px; }
.hp-credits-box ul { font-size: 13px; color: var(--hp-dark-500); margin-bottom: 16px; padding-left: 18px; }
@media (max-width: 1100px) {
    .hp-plans-grid { grid-template-columns: repeat(2, 1fr); }
    .hp-plan-card { border-bottom: 1px solid var(--hp-dark-100); }
    .hp-plan-card:nth-child(2) { border-right: none; }
    .hp-plan-card:nth-child(3) { border-bottom: none; }
    .hp-plan-card:nth-child(4) { border-right: none; border-bottom: none; }
}
@media (max-width: 768px) {
    .hp-hero { padding: 36px 16px 28px; }
    .hp-hero-stats { flex-direction: column; gap: 14px; padding: 18px 24px; }
    .hp-stat-divider { width: 60px; height: 1px; min-height: 0; }
    .hp-section-wrap { padding: 0 16px; }
    .hp-plans-grid { grid-template-columns: 1fr; }
    .hp-plan-card { border-right: none !important; }
    .hp-plan-card:last-child { border-bottom: none; }
    .hp-pph-grid, .hp-addons-grid { grid-template-columns: 1fr; }
    .hp-cta-inner { padding: 36px 20px; }
    .hp-employer-bar { flex-direction: column; align-items: flex-start; }
}
</style>
@endpush
