<script>
(function () {
    var form = document.getElementById('tp-search-form');
    var resultsEl = document.getElementById('tp-results');
    var filtersEl = document.getElementById('tp-filters-container');
    var loadingEl = document.getElementById('tp-loading');
    var searchUrl = @json(route('employer.talent-pool.search'));
    var facetsUrl = @json(route('employer.talent-pool.facets'));
    var detailsUrlTemplate = @json(route('employer.talent-pool.details', ['source' => '__SOURCE__', 'id' => '__ID__']));
    var saveUrl = @json(route('employer.talent-pool.save'));
    var shortlistUrl = @json(route('employer.talent-pool.shortlist'));
    var unlockUrl = @json(route('employer.talent-pool.unlock'));
    var downloadUrl = @json(route('employer.talent-pool.download'));
    var plansUrl = @json(route('employer.plans.index'));
    var viewTokenCost = @json((int) ($tpViewTokenCost ?? config('hirevo_plans.unlock_credit_cost', 1)));
    var downloadTokenCost = @json((int) ($tpDownloadTokenCost ?? config('hirevo_plans.excel_download_credit_cost', 2)));
    var highlightTerms = @json($tpHighlightTerms ?? []);
    var skipInitialFetch = @json(!empty($tpSkipInitialFetch));
    var csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    var debounceResultsTimer;
    var debounceFacetsTimer;
    var debounceCountTimer;
    var debounceKeywordResultsTimer;
    var activeRow = null;
    var totalCountEl = document.getElementById('tp-total-count');
    var countCacheKey = 'tp_search_count_v2';
    var pendingRequest = null;

    var backdrop = document.getElementById('tp-drawer-backdrop');
    var drawer = document.getElementById('tp-drawer');
    var drawerLoading = document.getElementById('tp-drawer-loading');
    var drawerBody = document.getElementById('tp-drawer-body');
    var tokenBalanceEl = document.getElementById('tp-token-balance');
    var topbarTokensEl = document.getElementById('tp-topbar-tokens-value');
    var creditsModal = document.getElementById('tp-credits-modal');

    function updateTokenBalance(n) {
        if (typeof n !== 'number') return;
        if (tokenBalanceEl) tokenBalanceEl.textContent = n.toLocaleString();
        if (topbarTokensEl) topbarTokensEl.textContent = n.toLocaleString();
    }

    function showCreditsModal() {
        if (!creditsModal) return;
        creditsModal.hidden = false;
        creditsModal.setAttribute('aria-hidden', 'false');
    }

    function hideCreditsModal() {
        if (!creditsModal) return;
        creditsModal.hidden = true;
        creditsModal.setAttribute('aria-hidden', 'true');
    }

    document.getElementById('tp-credits-modal-close')?.addEventListener('click', hideCreditsModal);
    creditsModal?.addEventListener('click', function (e) {
        if (e.target === creditsModal) hideCreditsModal();
    });

    function postJson(url, payload) {
        return fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrf,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(payload)
        }).then(function (r) {
            return r.json().then(function (data) {
                return { status: r.status, data: data };
            }).catch(function () {
                return { status: r.status, data: null };
            });
        });
    }

    function unlockContact(source, sourceId) {
        return postJson(unlockUrl, {
            source: source,
            source_id: parseInt(sourceId, 10)
        }).then(function (res) {
            if (res.status === 402 && res.data && res.data.message === 'insufficient_tokens') {
                showCreditsModal();
                return null;
            }
            if (res.data && typeof res.data.tokens_remaining === 'number') {
                updateTokenBalance(res.data.tokens_remaining);
            }
            return res.data;
        });
    }

    function downloadCandidate(source, sourceId) {
        return fetch(downloadUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'text/csv, application/json',
                'X-CSRF-TOKEN': csrf,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ source: source, source_id: parseInt(sourceId, 10) })
        }).then(function (r) {
            if (r.status === 402) {
                return r.json().then(function (data) {
                    if (data.message === 'insufficient_tokens') showCreditsModal();
                    return null;
                });
            }
            if (!r.ok) return null;
            var tokensRemaining = r.headers.get('X-Tokens-Remaining');
            if (tokensRemaining) updateTokenBalance(parseInt(tokensRemaining, 10));
            var disposition = r.headers.get('Content-Disposition') || '';
            var match = disposition.match(/filename="?([^";]+)"?/i);
            var filename = match ? match[1] : 'candidate.csv';
            return r.blob().then(function (blob) {
                var url = URL.createObjectURL(blob);
                var a = document.createElement('a');
                a.href = url;
                a.download = filename;
                document.body.appendChild(a);
                a.click();
                a.remove();
                URL.revokeObjectURL(url);
                return true;
            });
        });
    }

    function collectParams(page, opts) {
        opts = opts || {};
        var fd = new FormData(form);
        if (page) fd.set('page', page);
        var params = new URLSearchParams();
        fd.forEach(function (v, k) { if (v !== '' && v !== null) params.append(k, v); });
        if (!fd.get('saved_only')) params.delete('saved_only');
        if (!fd.get('shortlisted_only')) params.delete('shortlisted_only');
        if (opts.skipTotal) params.set('skip_total', '1');
        if (opts.withFacets) params.set('facets', '1');
        return params;
    }

    function paramsKey(page) {
        return collectParams(page || 1).toString();
    }

    function updateTotalCount(n) {
        if (typeof n !== 'number' || !totalCountEl) return;
        totalCountEl.textContent = n.toLocaleString() + ' ' + (n === 1 ? 'candidate' : 'candidates');
    }

    function showCachedCount() {
        try {
            var key = paramsKey(1);
            var raw = sessionStorage.getItem(countCacheKey);
            if (!raw) return;
            var cached = JSON.parse(raw);
            if (cached && cached.key === key && typeof cached.n === 'number') {
                updateTotalCount(cached.n);
            }
        } catch (e) {}
    }

    function storeCachedCount(n) {
        try {
            sessionStorage.setItem(countCacheKey, JSON.stringify({ key: paramsKey(1), n: n }));
        } catch (e) {}
    }

    function fetchFacets() {
        if (!filtersEl) return;
        fetch(facetsUrl + '?' + collectParams(1).toString(), {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data.filters_html) filtersEl.innerHTML = data.filters_html;
                if (Array.isArray(data.location_options)) {
                    updateLocationOptions(data.location_options);
                }
                bindAll();
            })
            .catch(function () {});
    }

    function fetchTotalCount() {
        showCachedCount();
        fetch(facetsUrl + '?' + collectParams(1).toString() + '&count_only=1', {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (typeof data.total_count === 'number') {
                    updateTotalCount(data.total_count);
                    storeCachedCount(data.total_count);
                }
            })
            .catch(function () {});
    }

    function fetchResults(page, opts) {
        if (!resultsEl) return;
        opts = opts || {};
        if (pendingRequest) pendingRequest.abort();
        loadingEl?.classList.add('show');
        var controller = new AbortController();
        pendingRequest = controller;
        fetch(searchUrl + '?' + collectParams(page || 1, opts).toString(), {
            signal: controller.signal,
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data.html) resultsEl.innerHTML = data.html;
                if (data.filters_html && filtersEl) filtersEl.innerHTML = data.filters_html;
                if (Array.isArray(data.location_options)) {
                    updateLocationOptions(data.location_options);
                }
                if (typeof data.total_count === 'number') {
                    updateTotalCount(data.total_count);
                    storeCachedCount(data.total_count);
                }
                bindAll();
                history.replaceState(null, '', form.action + '?' + collectParams(page || 1).toString());
            })
            .catch(function (err) {
                if (err && err.name === 'AbortError') return;
            })
            .finally(function () {
                if (pendingRequest === controller) pendingRequest = null;
                loadingEl?.classList.remove('show');
            });
    }

    function debouncedFetch() {
        clearTimeout(debounceResultsTimer);
        debounceResultsTimer = setTimeout(function () {
            fetchResults(1, { skipTotal: true, withFacets: true });
        }, 400);
    }

    function debouncedKeywordResults() {
        clearTimeout(debounceKeywordResultsTimer);
        debounceKeywordResultsTimer = setTimeout(function () {
            fetchResults(1, { skipTotal: true, withFacets: true });
        }, 550);
    }

    function debouncedFacets() {
        clearTimeout(debounceFacetsTimer);
        debounceFacetsTimer = setTimeout(fetchFacets, 700);
    }

    function debouncedCount() {
        clearTimeout(debounceCountTimer);
        debounceCountTimer = setTimeout(fetchTotalCount, 600);
    }

    function isKeywordField(el) {
        return el && (el.id === 'tp-q' || el.id === 'tp-skills');
    }

    form?.addEventListener('submit', function (e) {
        e.preventDefault();
        fetchResults(1, { withFacets: true });
    });

    function updateLocationOptions(options) {
        var select = document.getElementById('tp-location');
        if (!select || !Array.isArray(options)) return;
        var selected = select.value;
        var html = '<option value="">All cities</option>';
        options.forEach(function (loc) {
            if (!loc || !loc.label) return;
            var label = String(loc.label);
            var count = Number(loc.count || 0);
            var suffix = count > 0 ? ' (' + count.toLocaleString() + ')' : '';
            html += '<option value="' + label.replace(/"/g, '&quot;') + '"' + (selected === label ? ' selected' : '') + '>'
                + label + suffix + '</option>';
        });
        if (selected && !options.some(function (loc) { return loc && loc.label === selected; })) {
            html += '<option value="' + selected.replace(/"/g, '&quot;') + '" selected>' + selected + '</option>';
        }
        select.innerHTML = html;
    }

    function bindLocationSelect() {
        var locSelect = document.getElementById('tp-location');
        if (!locSelect || locSelect.dataset.tpBound) return;
        locSelect.dataset.tpBound = '1';
        locSelect.onchange = function () {
            debouncedFetch();
            debouncedCount();
        };
    }

    function bindExperienceRadios() {
        document.querySelectorAll('.tp-exp-radio').forEach(function (radio) {
            radio.onchange = function () {
                if (!this.checked) return;
                var minEl = document.getElementById('tp-exp-min');
                var maxEl = document.getElementById('tp-exp-max');
                if (minEl) minEl.value = this.dataset.min ?? '';
                if (maxEl) maxEl.value = this.dataset.max ?? '';
                debouncedFetch();
                debouncedCount();
            };
        });
    }

    function bindEducationRadios() {
        document.querySelectorAll('.tp-edu-radio').forEach(function (radio) {
            radio.onchange = function () {
                var edu = document.getElementById('tp-education');
                if (edu) edu.value = this.value;
                debouncedFetch();
                debouncedCount();
            };
        });
    }

    function bindFilters() {
        document.querySelectorAll('.tp-filter').forEach(function (el) {
            el.onchange = function () {
                if (isKeywordField(el)) {
                    debouncedCount();
                    debouncedKeywordResults();
                    return;
                }
                debouncedFetch();
                debouncedCount();
            };
            if (el.type === 'text' || el.type === 'number' || el.type === 'search') {
                el.oninput = function () {
                    if (isKeywordField(el)) {
                        showCachedCount();
                        debouncedCount();
                        debouncedKeywordResults();
                        return;
                    }
                    debouncedFetch();
                    debouncedCount();
                };
            }
        });
    }

    function bindPagination() {
        document.querySelectorAll('.tp-page-link').forEach(function (link) {
            link.onclick = function (e) {
                e.preventDefault();
                fetchResults(this.dataset.page || 1);
            };
        });
    }

    function openDrawer() {
        backdrop?.classList.add('show');
        drawer?.classList.add('show');
        document.body.style.overflow = 'hidden';
    }

    function closeDrawer() {
        backdrop?.classList.remove('show');
        drawer?.classList.remove('show');
        document.body.style.overflow = '';
        if (activeRow) {
            activeRow.classList.remove('tp-row-active');
            activeRow = null;
        }
        if (drawerBody) drawerBody.hidden = true;
        if (drawerLoading) drawerLoading.hidden = false;
    }

    backdrop?.addEventListener('click', closeDrawer);
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && drawer?.classList.contains('show')) closeDrawer();
    });

    function escapeHtml(str) {
        if (str == null) return '';
        return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    function highlightText(text) {
        if (!text || !highlightTerms.length) return escapeHtml(text);
        var escaped = escapeHtml(text);
        highlightTerms.forEach(function (term) {
            if (!term || term.length < 2) return;
            var re = new RegExp('(' + term.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + ')', 'gi');
            escaped = escaped.replace(re, '<span class="tp-dr-highlight">$1</span>');
        });
        return escaped;
    }

    function initials(name) {
        return String(name || '').split(/\s+/).filter(Boolean).slice(0, 2).map(function (w) {
            return w.charAt(0).toUpperCase();
        }).join('') || '?';
    }

    function renderWorkExperience(items) {
        if (!items || !items.length) {
            return '<p class="text-muted small mb-0">No work experience listed.</p>';
        }
        return '<div class="tp-dr-timeline">' + items.map(function (exp) {
            var title = highlightText(exp.title || '');
            var company = exp.company ? highlightText(exp.company) : '';
            var line = company
                ? '<div class="tp-dr-exp-title">' + title + ' at ' + company + '</div>'
                : '<div class="tp-dr-exp-title">' + title + '</div>';
            var period = exp.period ? '<div class="tp-dr-exp-period">' + escapeHtml(exp.period) + '</div>' : '';
            return '<div class="tp-dr-exp">' + line + period + '</div>';
        }).join('') + '</div>';
    }

    function renderPills(items) {
        if (!items || !items.length) return '<p class="text-muted small mb-0">—</p>';
        return '<div class="tp-dr-pills">' + items.map(function (item) {
            return '<span class="tp-dr-pill">' + highlightText(item) + '</span>';
        }).join('') + '</div>';
    }

    function renderDrawer(c, canViewContact) {
        var ini = initials(c.full_name);
        var avatar = c.profile_image
            ? '<img src="' + escapeHtml(c.profile_image) + '" alt="" class="tp-dr-avatar">'
            : '<div class="tp-dr-avatar-fallback">' + escapeHtml(ini) + '</div>';

        var phoneFooter;
        if (canViewContact && c.phone) {
            phoneFooter = '<a href="tel:' + String(c.phone).replace(/\D+/g, '') + '" class="tp-dr-phone-btn">'
                + '<i class="mdi mdi-phone"></i> ' + escapeHtml(c.phone) + '</a>';
        } else if (canViewContact) {
            phoneFooter = '<div class="tp-dr-cv-empty"><p class="mb-0">Phone not available for this candidate.</p></div>';
        } else {
            phoneFooter = '<button type="button" class="tp-dr-phone-btn is-locked tp-drawer-unlock-btn" data-source="'
                + escapeHtml(c.source) + '" data-source-id="' + c.source_id + '">'
                + '<i class="mdi mdi-phone-lock-outline"></i> Unlock phone (' + viewTokenCost + ' token)</button>';
        }

        var downloadAction = '';
        if (c.is_saved) {
            downloadAction = '<button type="button" class="btn btn-sm btn-outline-secondary tp-download-btn" data-source="'
                + escapeHtml(c.source) + '" data-source-id="' + c.source_id + '" data-can-download="'
                + (c.can_download ? '1' : '0') + '"><i class="mdi mdi-download-outline me-1"></i>'
                + (c.can_download ? 'Download data' : ('Download (' + downloadTokenCost + ' tokens)')) + '</button>';
        }

        var eduHtml = '';
        if (c.education) {
            eduHtml += '<p class="tp-dr-edu">' + highlightText(c.education) + '</p>';
        }
        (c.education_history || []).forEach(function (edu) {
            if (typeof edu === 'string') {
                eduHtml += '<p class="tp-dr-edu">' + highlightText(edu) + '</p>';
            } else if (edu && typeof edu === 'object') {
                var deg = edu.degree || edu.qualification || edu.title || '';
                var inst = edu.institution || edu.school || edu.college || '';
                var line = [deg, inst].filter(Boolean).join(' — ');
                if (line) eduHtml += '<p class="tp-dr-edu">' + highlightText(line) + '</p>';
            }
        });

        var profileTab = ''
            + '<div class="tp-dr-section"><div class="tp-dr-section-title">Work Experience</div>'
            + renderWorkExperience(c.work_experience_items || [])
            + '</div>';

        if ((c.industries || []).length) {
            profileTab += '<div class="tp-dr-section"><div class="tp-dr-section-title">Industries</div>' + renderPills(c.industries) + '</div>';
        }
        if ((c.departments || []).length) {
            profileTab += '<div class="tp-dr-section"><div class="tp-dr-section-title">Departments</div>' + renderPills(c.departments) + '</div>';
        }
        if ((c.skills || []).length) {
            profileTab += '<div class="tp-dr-section"><div class="tp-dr-section-title">Skills</div>' + renderPills(c.skills) + '</div>';
        }
        if (c.profile_summary) {
            profileTab += '<div class="tp-dr-section"><div class="tp-dr-section-title">About</div><p class="tp-dr-summary">' + highlightText(c.profile_summary) + '</p></div>';
        }
        if (eduHtml) {
            profileTab += '<div class="tp-dr-section"><div class="tp-dr-section-title">Education</div>' + eduHtml + '</div>';
        }

        var cvTab = c.resume_url && canViewContact
            ? '<div class="text-center py-2">'
                + '<a href="' + escapeHtml(c.resume_url) + '" target="_blank" rel="noopener" class="btn btn-success">'
                + '<i class="mdi mdi-file-document-outline me-1"></i> Open CV / Resume</a></div>'
            : '<div class="tp-dr-cv-empty">'
                + '<i class="mdi mdi-file-document-outline"></i>'
                + '<p class="mb-2">' + (canViewContact ? 'No resume attached for this candidate.' : 'Subscribe to a plan to view resumes.') + '</p>'
                + (!canViewContact ? '<a href="' + escapeHtml(plansUrl) + '" class="btn btn-sm btn-success">View plans</a>' : '')
                + '</div>';

        var html = ''
            + '<div class="tp-dr-top">'
            + '<button type="button" class="tp-dr-close" id="tp-drawer-close" aria-label="Close"><i class="mdi mdi-close"></i></button>'
            + '<div class="tp-dr-hero">' + avatar
            + '<div><h2 class="tp-dr-name">' + escapeHtml(c.full_name) + '</h2>'
            + '<div class="tp-dr-meta">'
            + (c.experience_label ? '<span><i class="mdi mdi-briefcase-outline"></i> ' + escapeHtml(c.experience_label) + '</span>' : '')
            + (c.expected_salary ? '<span><i class="mdi mdi-currency-inr"></i> ' + escapeHtml(c.expected_salary) + '</span>' : '')
            + (c.location ? '<span><i class="mdi mdi-map-marker-outline"></i> ' + escapeHtml(c.location) + '</span>' : '')
            + '</div>'
            + '<span class="hbadge hbadge-sm tp-badge-' + escapeHtml(c.badge_class || '') + ' mt-2">' + escapeHtml(c.badge) + '</span>'
            + '</div></div></div>'
            + '<div class="tp-dr-middle">'
            + '<div class="tp-dr-tabs" role="tablist">'
            + '<button type="button" class="tp-dr-tab active" data-tab="profile" role="tab">Full Profile</button>'
            + '<button type="button" class="tp-dr-tab" data-tab="cv" role="tab">CV / Resume</button>'
            + '</div>'
            + '<div class="tp-dr-scroll">'
            + '<div class="tp-dr-panel" data-panel="profile">' + profileTab + '</div>'
            + '<div class="tp-dr-panel" data-panel="cv" hidden>' + cvTab + '</div>'
            + '<div class="tp-dr-actions">'
            + downloadAction
            + '<button type="button" class="btn btn-sm ' + (c.is_saved ? 'btn-warning' : 'btn-outline-secondary') + ' tp-save-btn" data-source="' + escapeHtml(c.source) + '" data-source-id="' + c.source_id + '"><i class="mdi mdi-bookmark-outline me-1"></i>Save</button>'
            + '<button type="button" class="btn btn-sm ' + (c.is_shortlisted ? 'btn-success' : 'btn-outline-success') + ' tp-shortlist-btn" data-source="' + escapeHtml(c.source) + '" data-source-id="' + c.source_id + '"><i class="mdi mdi-star-outline me-1"></i>Shortlist</button>'
            + '</div></div></div>'
            + '<div class="tp-dr-footer">'
            + phoneFooter
            + '<div class="tp-dr-footer-note">'
            + (canViewContact ? '<span><i class="mdi mdi-check-circle-outline text-success"></i> Contact unlocked</span>' : '<span><i class="mdi mdi-lock-outline"></i> ' + viewTokenCost + ' token to view phone</span>')
            + (c.active_label ? '<span>Active on ' + escapeHtml(c.active_label) + '</span>' : '')
            + '</div></div>';

        drawerBody.innerHTML = html;
        drawerBody.hidden = false;
        if (drawerLoading) drawerLoading.hidden = true;

        document.getElementById('tp-drawer-close')?.addEventListener('click', closeDrawer);
        drawerBody.querySelectorAll('.tp-dr-tab').forEach(function (tab) {
            tab.onclick = function () {
                var target = tab.dataset.tab;
                drawerBody.querySelectorAll('.tp-dr-tab').forEach(function (t) { t.classList.toggle('active', t === tab); });
                drawerBody.querySelectorAll('.tp-dr-panel').forEach(function (panel) {
                    panel.hidden = panel.dataset.panel !== target;
                });
            };
        });

        drawerBody.querySelector('.tp-drawer-unlock-btn')?.addEventListener('click', function () {
            var btn = this;
            unlockContact(btn.dataset.source, btn.dataset.sourceId).then(function (data) {
                if (data && data.candidate) {
                    renderDrawer(data.candidate, true);
                }
            });
        });

        bindActions();
    }

    function setActiveRow(row) {
        if (activeRow) activeRow.classList.remove('tp-row-active');
        activeRow = row;
        if (row) row.classList.add('tp-row-active');
    }

    function loadDetails(source, sourceId, row) {
        setActiveRow(row || null);
        if (drawerLoading) drawerLoading.hidden = false;
        if (drawerBody) drawerBody.hidden = true;
        openDrawer();

        var url = detailsUrlTemplate.replace('__SOURCE__', encodeURIComponent(source)).replace('__ID__', encodeURIComponent(sourceId));
        fetch(url, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function (r) { return r.json().then(function (d) { return { status: r.status, data: d }; }); })
            .then(function (res) {
                if (res.data.candidate) {
                    renderDrawer(res.data.candidate, !!res.data.can_view_contact);
                    if (typeof res.data.tokens_remaining === 'number') {
                        updateTokenBalance(res.data.tokens_remaining);
                    }
                } else {
                    closeDrawer();
                }
            })
            .catch(function () { closeDrawer(); });
    }

    function postAction(url, source, sourceId, kind) {
        fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest' },
            body: JSON.stringify({ source: source, source_id: parseInt(sourceId, 10) })
        }).then(function (r) { return r.json(); }).then(function (data) {
            var sel = kind === 'save' ? '.tp-save-btn' : '.tp-shortlist-btn';
            document.querySelectorAll(sel + '[data-source="' + source + '"][data-source-id="' + sourceId + '"]').forEach(function (b) {
                if (kind === 'save') {
                    b.classList.toggle('btn-warning', !!data.is_saved);
                    b.classList.toggle('btn-outline-secondary', !data.is_saved);
                } else {
                    b.classList.toggle('btn-success', !!data.is_shortlisted);
                    b.classList.toggle('btn-outline-success', !data.is_shortlisted);
                }
            });
        });
    }

    function bindActions() {
        document.querySelectorAll('.tp-open-profile, .tp-view-btn').forEach(function (btn) {
            btn.onclick = function (e) {
                e.preventDefault();
                e.stopPropagation();
                var row = btn.closest('.tp-candidate-row');
                loadDetails(btn.dataset.source, btn.dataset.sourceId, row);
            };
        });

        document.querySelectorAll('.tp-candidate-row.tp-row-openable').forEach(function (row) {
            row.onclick = function (e) {
                if (e.target.closest('a, button, .tp-save-btn, .tp-shortlist-btn, .tp-phone-plans-btn, .tp-unlock-phone-btn, .tp-download-btn')) return;
                loadDetails(row.dataset.source, row.dataset.sourceId, row);
            };
        });

        document.querySelectorAll('.tp-unlock-phone-btn').forEach(function (btn) {
            btn.onclick = function (e) {
                e.preventDefault();
                e.stopPropagation();
                var source = btn.dataset.source;
                var sourceId = btn.dataset.sourceId;
                unlockContact(source, sourceId).then(function (data) {
                    if (!data || !data.candidate || !data.candidate.phone) return;
                    var phone = escapeHtml(data.candidate.phone);
                    var tel = 'tel:' + String(data.candidate.phone).replace(/\D+/g, '');
                    btn.outerHTML = '<a href="' + tel + '" class="btn btn-outline-primary btn-sm"><i class="mdi mdi-phone-outline me-1"></i> ' + phone + '</a>';
                });
            };
        });

        document.querySelectorAll('.tp-download-btn').forEach(function (btn) {
            btn.onclick = function (e) {
                e.preventDefault();
                e.stopPropagation();
                var source = btn.dataset.source;
                var sourceId = btn.dataset.sourceId;
                btn.disabled = true;
                downloadCandidate(source, sourceId).then(function (ok) {
                    btn.disabled = false;
                    if (ok) {
                        btn.dataset.canDownload = '1';
                        btn.innerHTML = '<i class="mdi mdi-download-outline me-1"></i> Download';
                    }
                });
            };
        });

        document.querySelectorAll('.tp-save-btn').forEach(function (btn) {
            btn.onclick = function (e) {
                e.stopPropagation();
                postAction(saveUrl, btn.dataset.source, btn.dataset.sourceId, 'save');
            };
        });

        document.querySelectorAll('.tp-shortlist-btn').forEach(function (btn) {
            btn.onclick = function (e) {
                e.stopPropagation();
                postAction(shortlistUrl, btn.dataset.source, btn.dataset.sourceId, 'shortlist');
            };
        });
    }

    function bindAll() {
        bindLocationSelect();
        bindExperienceRadios();
        bindEducationRadios();
        bindFilters();
        bindPagination();
        bindActions();
    }

    showCachedCount();
    bindAll();
    if (!skipInitialFetch) {
        if (filtersEl) fetchFacets();
        if (totalCountEl && totalCountEl.textContent.indexOf('Loading') >= 0) fetchTotalCount();
    }
})();
</script>
