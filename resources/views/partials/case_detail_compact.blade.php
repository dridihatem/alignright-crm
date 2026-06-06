{{-- Shared professional + compact styling for case detail pages across all roles.
     Scope: wrap the page container with class "case-detail-compact". --}}
@push('styles')
<style>
    :root { --cd-accent: #01b9c6; --cd-ink: #0f172a; --cd-muted: #64748b; --cd-line: #e9eef3; }

    .case-detail-compact { color: #334155; }
    .case-detail-compact .row { --bs-gutter-y: 1.25rem; }
    .case-detail-compact .row.g-6,
    .case-detail-compact .row.g-4,
    .case-detail-compact .row.g-3 { --bs-gutter-y: 1.25rem; --bs-gutter-x: 1.25rem; }

    /* Breadcrumb */
    .case-detail-compact .breadcrumb { font-size: 0.85rem; margin-bottom: 1rem; }
    .case-detail-compact .breadcrumb a { color: var(--cd-muted); text-decoration: none; }
    .case-detail-compact .breadcrumb a:hover { color: var(--cd-accent); }

    /* Cards: consistent, professional framing */
    .case-detail-compact .card {
        margin-bottom: 0;
        border: 1px solid var(--cd-line);
        border-radius: 14px;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04), 0 8px 24px rgba(15, 23, 42, 0.04);
        transition: box-shadow 0.2s ease, transform 0.2s ease;
        overflow: hidden;
    }
    .case-detail-compact > .container-xxl .card:hover,
    .case-detail-compact .row > [class*="col"] > .card:hover {
        box-shadow: 0 2px 4px rgba(15, 23, 42, 0.05), 0 12px 30px rgba(15, 23, 42, 0.08);
    }
    .case-detail-compact .card .card { box-shadow: none; }

    /* Card headers: light surface, accent rule, clear title */
    .case-detail-compact .card-header {
        padding: 0.8rem 1.15rem;
        min-height: auto;
        border-bottom: 1px solid var(--cd-line);
    }
    /* Light surface only for non-colored headers (keep bg-primary etc. intact) */
    .case-detail-compact .card-header:not([class*="bg-"]) { background-color: #fbfdfe; }
    /* Preserve flex headers that the markup already opts into */
    .case-detail-compact .card-header.d-flex { gap: 0.5rem; flex-wrap: wrap; }
    .case-detail-compact .card-body { padding: 1.15rem 1.15rem; }

    /* Section titles with accent marker */
    .case-detail-compact .card-title { font-size: 0.95rem; font-weight: 600; margin-bottom: 0; color: var(--cd-ink); letter-spacing: 0.2px; }
    .case-detail-compact h5 { font-size: 1rem; }
    .case-detail-compact h6 { font-size: 0.9rem; }
    .case-detail-compact .card-header > .card-title:not(.text-white),
    .case-detail-compact .card-header > h5:not(.text-white),
    .case-detail-compact .card-header > h6:not(.text-white) {
        position: relative;
        padding-left: 0.75rem;
    }
    .case-detail-compact .card-header > .card-title:not(.text-white)::before,
    .case-detail-compact .card-header > h5:not(.text-white)::before,
    .case-detail-compact .card-header > h6:not(.text-white)::before {
        content: "";
        position: absolute;
        left: 0; top: 50%;
        transform: translateY(-50%);
        width: 3px; height: 1.05em;
        border-radius: 3px;
        background-color: var(--cd-accent);
    }
    /* Keep colored headers (bg-primary etc.) clean: no accent bar, white text */
    .case-detail-compact .card-header[class*="bg-"] { background-image: none; }

    /* Tables: refined, readable */
    .case-detail-compact .table { font-size: 0.86rem; margin-bottom: 0; --bs-table-border-color: var(--cd-line); }
    .case-detail-compact .table > :not(caption) > * > * { padding: 0.55rem 0.7rem; vertical-align: middle; }
    .case-detail-compact .table th { font-weight: 600; }
    .case-detail-compact .table thead:not(.table-dark) th {
        color: var(--cd-muted);
        text-transform: uppercase;
        font-size: 0.72rem;
        letter-spacing: 0.4px;
        background-color: #f8fafc;
    }
    .case-detail-compact .table thead.table-dark th {
        color: #ffffff;
        background-color: #243042;
        text-transform: uppercase;
        font-size: 0.72rem;
        letter-spacing: 0.4px;
        border-color: #2f3c4f;
    }
    .case-detail-compact .table tbody tr { transition: background-color 0.15s ease; }
    .case-detail-compact .table-hover tbody tr:hover,
    .case-detail-compact .table tbody tr:hover { background-color: #f6fbfc; }
    .case-detail-compact .table.table-bordered { border-radius: 10px; overflow: hidden; }

    /* Key/value blocks, labels */
    .case-detail-compact p { margin-bottom: 0.45rem; }
    .case-detail-compact .form-label.fw-bold,
    .case-detail-compact label.fw-bold { color: var(--cd-muted); font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.4px; margin-bottom: 0.15rem; }
    .case-detail-compact .list-group-item { padding: 0.6rem 0.85rem; border-color: var(--cd-line); }
    .case-detail-compact dl, .case-detail-compact dd { margin-bottom: 0.35rem; }

    /* Badges */
    .case-detail-compact .badge { font-weight: 600; padding: 0.4em 0.7em; border-radius: 6px; letter-spacing: 0.2px; }

    /* Buttons: balanced toolbar */
    .case-detail-compact .btn { padding-top: 0.45rem; padding-bottom: 0.45rem; border-radius: 8px; font-weight: 500; }
    .case-detail-compact .btn-sm { padding-top: 0.25rem; padding-bottom: 0.25rem; }
    .case-detail-compact .card-header .btn { margin: 0; }

    /* Subtle dividers between stacked content */
    .case-detail-compact hr { border-color: var(--cd-line); opacity: 1; }
</style>
@endpush
