@php
    $skillPresetGroups = config('hirevo.employer_job_skill_presets', []);
    $allPresetValues = [];
    foreach ($skillPresetGroups as $skills) {
        foreach ($skills as $s) {
            $allPresetValues[] = $s;
        }
    }
@endphp
<div class="mb-4" id="hirevo-skills-field">
    <label class="form-label fw-500">Skills &amp; certifications</label>
    <p class="small text-muted mb-2">Select <strong>one or more</strong> presets below and/or add extra skills in the box. Used for candidate matching and certification paths.</p>

    <div class="border rounded p-3 bg-light mb-3" style="max-height: 320px; overflow-y: auto;">
        @foreach($skillPresetGroups as $groupTitle => $skills)
            <div class="mb-3 @if($loop->last) mb-0 @endif">
                <h6 class="small text-muted fw-600 mb-2">{{ $groupTitle }}</h6>
                <div class="row g-2">
                    @foreach($skills as $skill)
                        @php
                            $cbId = 'skill_cb_' . $loop->parent->index . '_' . $loop->index;
                        @endphp
                        <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                            <div class="form-check">
                                <input class="form-check-input skill-preset-cb" type="checkbox"
                                       id="{{ $cbId }}"
                                       data-skill="{{ $skill }}"
                                       autocomplete="off">
                                <label class="form-check-label small" for="{{ $cbId }}">{{ $skill }}</label>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>

    <label for="required_skills" class="form-label fw-500 small text-muted mb-1">Skills summary (comma-separated)</label>
    <textarea class="form-control @error('required_skills') is-invalid @enderror"
              id="required_skills"
              name="required_skills"
              rows="3"
              maxlength="2000"
              placeholder="e.g. AWS, Python, SQL — presets sync here; you can edit or add more.">{{ $skillsValue }}</textarea>
    @error('required_skills')<div class="invalid-feedback">{{ $message }}</div>@enderror
    <p class="small text-muted mt-1 mb-0">Checkboxes update this list; you can still type custom skills.</p>

    <script>
        (function () {
            window.__hirevoSkillPresets = @json($allPresetValues);
            var root = document.getElementById('hirevo-skills-field');
            if (!root) return;
            var ta = document.getElementById('required_skills');
            var presetList = window.__hirevoSkillPresets || [];
            if (!ta || !presetList.length) return;

            var presetSet = {};
            presetList.forEach(function (p) { presetSet[p.toLowerCase()] = true; });

            function splitSkills(str) {
                return (str || '').split(/[\r\n,;|]+/).map(function (s) { return s.trim(); }).filter(Boolean);
            }

            function uniqueMerge(arr) {
                var seen = {}, out = [];
                arr.forEach(function (s) {
                    var k = s.toLowerCase();
                    if (!seen[k]) { seen[k] = true; out.push(s); }
                });
                return out;
            }

            function rebuildFromCheckboxes() {
                var checked = [];
                root.querySelectorAll('.skill-preset-cb:checked').forEach(function (cb) {
                    checked.push(cb.getAttribute('data-skill'));
                });
                var existing = splitSkills(ta.value);
                var nonPreset = existing.filter(function (s) {
                    return !presetSet[s.toLowerCase()];
                });
                ta.value = uniqueMerge(checked.concat(nonPreset)).join(', ');
            }

            function syncCheckboxesFromTextarea() {
                var existing = splitSkills(ta.value);
                var existingLower = {};
                existing.forEach(function (s) { existingLower[s.toLowerCase()] = true; });
                root.querySelectorAll('.skill-preset-cb').forEach(function (cb) {
                    var skill = cb.getAttribute('data-skill');
                    cb.checked = !!existingLower[skill.toLowerCase()];
                });
            }

            root.querySelectorAll('.skill-preset-cb').forEach(function (cb) {
                cb.addEventListener('change', rebuildFromCheckboxes);
            });
            ta.addEventListener('blur', syncCheckboxesFromTextarea);
            ta.addEventListener('input', function () {
                clearTimeout(ta._hirevoSyncT);
                ta._hirevoSyncT = setTimeout(syncCheckboxesFromTextarea, 200);
            });
            syncCheckboxesFromTextarea();
        })();
    </script>
</div>
