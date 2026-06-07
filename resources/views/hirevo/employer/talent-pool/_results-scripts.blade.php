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
    var plansUrl = @json(route('employer.plans.index'));
    var highlightTerms = @json($tpHighlightTerms ?? []);
    var csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    var debounceResultsTimer;
    var debounceFacetsTimer;
    var debounceCountTimer;
    var debounceKeywordResultsTimer;
    var activeRow = null;
    var totalCountEl = document.getElementById('tp-total-count');
    var countCacheKey = 'tp_search_count_v1';
    var pendingRequest = null;

    var backdrop = document.getElementById('tp-drawer-backdrop');
    var drawer = document.getElementById('tp-drawer');
    var drawerLoading = document.getElementById('tp-drawer-loading');
    var drawerBody = document.getElementById('tp-drawer-body');

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
        fetch(searchUrl + '?' + collectParams(1, { withFacets: true, skipTotal: true }).toString(), {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data.filters_html) filtersEl.innerHTML = data.filters_html;
                bindAll();
            })
            .catch(function () {});
    }

    function fetchTotalCount() {
        showCachedCount();
        fetch(facetsUrl + '?' + collectParams(1).toString(), {
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
        debounceResultsTimer = setTimeout(function () { fetchResults(1, { skipTotal: true }); }, 500);
    }

    function debouncedKeywordResults() {
        clearTimeout(debounceKeywordResultsTimer);
        debounceKeywordResultsTimer = setTimeout(function () {
            fetchResults(1, { skipTotal: true });
            debouncedFacets();
        }, 1100);
    }

    function debouncedFacets() {
        clearTimeout(debounceFacetsTimer);
        debounceFacetsTimer = setTimeout(fetchFacets, 900);
    }

    function debouncedCount() {
        clearTimeout(debounceCountTimer);
        debounceCountTimer = setTimeout(fetchTotalCount, 800);
    }

    function isKeywordField(el) {
        return el && (el.id === 'tp-q' || el.id === 'tp-skills');
    }

    form?.addEventListener('submit', function (e) {
        e.preventDefault();
        fetchResults(1, { withFacets: true });
        fetchFacets();
        fetchTotalCount();
    });

    function bindLocationSelect() {
        var locSelect = document.getElementById('tp-location');
        if (!locSelect || locSelect.dataset.tpBound) return;
        locSelect.dataset.tpBound = '1';
        locSelect.onchange = function () {
            debouncedFetch();
            debouncedFacets();
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
                debouncedFacets();
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
                debouncedFacets();
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
                debouncedFacets();
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
                    debouncedFacets();
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

        var phoneHref = canViewContact && c.phone
            ? 'tel:' + String(c.phone).replace(/\D+/g, '')
            : plansUrl;
        var phoneLabel = canViewContact && c.phone ? 'View Phone Number' : 'View Phone Number';
        var phoneClass = 'tp-dr-phone-btn' + (canViewContact && c.phone ? '' : ' is-locked');
        var phoneIcon = canViewContact && c.phone ? 'mdi-phone' : 'mdi-lock-outline';

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
            + '<button type="button" class="btn btn-sm ' + (c.is_saved ? 'btn-warning' : 'btn-outline-secondary') + ' tp-save-btn" data-source="' + escapeHtml(c.source) + '" data-source-id="' + c.source_id + '"><i class="mdi mdi-bookmark-outline me-1"></i>Save</button>'
            + '<button type="button" class="btn btn-sm ' + (c.is_shortlisted ? 'btn-success' : 'btn-outline-success') + ' tp-shortlist-btn" data-source="' + escapeHtml(c.source) + '" data-source-id="' + c.source_id + '"><i class="mdi mdi-star-outline me-1"></i>Shortlist</button>'
            + '</div></div></div>'
            + '<div class="tp-dr-footer">'
            + '<a href="' + escapeHtml(phoneHref) + '" class="' + phoneClass + '">'
            + '<i class="mdi ' + phoneIcon + '"></i> ' + phoneLabel + '</a>'
            + '<div class="tp-dr-footer-note">'
            + (canViewContact ? '<span><i class="mdi mdi-check-circle-outline text-success"></i> Plan active</span>' : '<span><i class="mdi mdi-lock-outline"></i> Subscribe to unlock</span>')
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
                if (e.target.closest('a, button, .tp-save-btn, .tp-shortlist-btn, .tp-phone-plans-btn')) return;
                loadDetails(row.dataset.source, row.dataset.sourceId, row);
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
})();
</script>
