# Hirevo Bulk Job Import Guide

Import hundreds of job openings that look **identical to manually posted jobs** on `/job-openings` — same cards, salary tags, apply flow, and optional “X applied” counts.

---

## Quick start

```bash
cd c:\xampp\htdocs\themesdesign.in\hirevo
php artisan migrate
php artisan hirevo:import-employer-jobs-csv database/csv/employer_jobs_catalog_500.csv
```

Open [http://127.0.0.1:8000/job-openings](http://127.0.0.1:8000/job-openings) to verify jobs appear.

If Elasticsearch is enabled:

```bash
php artisan hirevo:search-reindex
```

---

## How imported jobs look real

Imported rows go into the same `employer_jobs` table as employer-posted jobs. There is no separate “fake” card or badge.

| Visitor sees | CSV / DB field |
|--------------|----------------|
| Company name (TCS, Infosys, …) | `company_name` per row |
| Location pill | `location_city`, `location_state` → JSON `location` |
| Salary range | `salary_min`, `salary_max`, `pay_type` |
| “Posted 5 days ago” | `posted_days_ago` → `created_at` |
| “287 applied” | `display_applications_count` |
| “Apply on company site” | `apply_link` |

---

## Import methods

### 1. CLI — CSV (recommended)

```bash
php artisan hirevo:import-employer-jobs-csv database/csv/employer_jobs_catalog_500.csv
php artisan hirevo:import-employer-jobs-csv my-jobs.csv --employer=catalog-employer@hirevo.com --skip-duplicates
php artisan hirevo:import-employer-jobs-csv my-jobs.csv --reindex
```

- Default employer: `catalog-employer@hirevo.com` (auto-created, approved, 100 credits)
- `--skip-duplicates` skips rows with the same `title` + `company_name` for that employer
- `--reindex` runs Elasticsearch reindex after import (optional; import is fast without it)
- Does **not** deduct posting credits

### 2. CLI — JSON

```bash
php artisan hirevo:import-employer-jobs-json database/json/my-jobs.json
```

JSON must be an array of objects with the same field names as CSV columns.

### 3. Employer portal — web upload

1. Log in as an approved employer (referrer role)
2. Go to **Employer → All Jobs → Bulk import** (`/employer/jobs/import`)
3. Download the CSV template
4. Upload your filled CSV

Jobs attach to the **logged-in employer account**. Use `company_name` in each row to show different company names on cards.

### 4. phpMyAdmin (not recommended)

Direct `INSERT` into `employer_jobs` is possible but error-prone (`location` must be valid JSON, `required_skills` must be a JSON array). Prefer CSV import.

---

## CSV column reference

| Column | Required | Example |
|--------|----------|---------|
| `company_name` | Yes | `Infosys` |
| `title` | Yes | `Senior Java Developer` |
| `job_department` | Yes | `Engineering` |
| `job_type` | Yes | `full_time` |
| `work_location_type` | Yes | `hybrid` |
| `pay_type` | Yes | `fixed` |
| `location_city` | Recommended | `Bangalore` |
| `location_state` | Recommended | `Karnataka` |
| `location_country` | No | `India` (default) |
| `salary_min` | Recommended | `900000` |
| `salary_max` | Recommended | `1600000` |
| `experience_years` | Recommended | `4` |
| `description` | Recommended | Full paragraph |
| `required_skills` | Recommended | `Java\|Spring\|SQL` (pipe-separated) |
| `perks` | No | `Health insurance, WFH` |
| `apply_link` | Recommended | `https://www.infosys.com/careers` |
| `joining_fee_required` | Yes | `0` or `1` |
| `is_night_shift` | No | `0` or `1` |
| `status` | No | `active` (default) |
| `display_applications_count` | No | `342` |
| `posted_days_ago` | No | `7` (0–365) |

### Valid enum values

- **job_type:** `full_time`, `part_time`, `contract`, `internship`, `temporary`, `volunteer`, `other`
- **work_location_type:** `office`, `remote`, `hybrid`
- **pay_type:** `fixed`, `hourly`, `negotiable`, `not_disclosed`, `other`
- **status:** `active`, `draft`, `closed`

### Salary rule

For `pay_type` = `fixed` or `negotiable`, `salary_min` must be at least **₹150,000** per annum (configurable in `config/hirevo.php`).

---

## Bundled files

| File | Purpose |
|------|---------|
| `database/csv/employer_jobs_catalog_500.csv` | 500 ready-to-import sample jobs |
| `database/csv/employer_jobs_template.csv` | Empty template (headers only) |
| `database/scripts/generate-employer-jobs-catalog-csv.php` | Regenerate the 500-row CSV |

Regenerate catalog CSV:

```bash
php database/scripts/generate-employer-jobs-catalog-csv.php
```

---

## External apply flow

When `apply_link` is set:

1. Candidate clicks **Apply on company site**
2. Submits resume on Hirevo (login required)
3. Hirevo saves `EmployerJobApplication`
4. Employer site opens in a **new tab**; you stay on Hirevo job listings

Candidates cannot skip the Hirevo apply step.

---

## Catalog employer login

Jobs imported via CLI default to:

- **Email:** `catalog-employer@hirevo.com`
- **Password:** `ChangeMeCatalog!` (change on production)

View imported jobs: **Employer dashboard** → All Jobs.

---

## Troubleshooting

| Problem | Fix |
|---------|-----|
| Import fails on salary | Ensure `salary_min` ≥ 150000 for `fixed` / `negotiable` pay |
| Jobs not on `/job-openings` | Check `status=active`; clear browser session; re-import triggers cache clear |
| Search missing jobs | Run `php artisan hirevo:search-reindex` if Elasticsearch enabled |
| Duplicate jobs on re-import | Use `--skip-duplicates` or web upload “Skip duplicate rows” |
| “X applied” not showing | Set `display_applications_count` > 0 in CSV |
| Wrong company on card | Set `company_name` in CSV (overrides poster profile name on the card) |

---

## Editing jobs after import

Imported jobs can be edited or deleted from **Employer → All Jobs** like any other posting.
