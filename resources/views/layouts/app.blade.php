<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Municipal Building Permit Repository System' }}</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap');
        :root { --card:#fff; --ink:#17302a; --muted:#60756f; --line:#d7e1dd; --brand:#1f6f5f; --brand-soft:#d8ebe4; --warn:#d97706; --danger:#b91c1c; }
        * { box-sizing:border-box; }
        body { margin:0; font-family:'Poppins', sans-serif; background:linear-gradient(180deg,#f8fbf9,#eef3f0); color:var(--ink); }
        a { color:inherit; text-decoration:none; }
        .shell { display:grid; grid-template-columns:280px 1fr; min-height:100vh; }
        .sidebar { background:#163932; color:#eef7f4; padding:28px 22px; }
        .brand { font-size:1.3rem; font-weight:700; line-height:1.3; margin-bottom:24px; }
        .brand small { display:block; color:#bfd5cf; font-size:.82rem; margin-top:6px; }
        .nav-link { display:block; padding:12px 14px; border-radius:12px; margin-bottom:8px; color:#d6e6e1; }
        .nav-link:hover,.nav-link.active { background:rgba(255,255,255,.1); color:#fff; }
        .main { padding:28px; }
        .topbar { display:flex; justify-content:space-between; gap:16px; align-items:center; margin-bottom:24px; }
        .page-title { font-size:1.7rem; margin:0; }
        .muted { color:var(--muted); }
        .grid { display:grid; gap:20px; }
        .grid.cols-2 { grid-template-columns:repeat(2,minmax(0,1fr)); }
        .grid.cols-3 { grid-template-columns:repeat(3,minmax(0,1fr)); }
        .grid.cols-4 { grid-template-columns:repeat(4,minmax(0,1fr)); }
        .card { background:var(--card); border:1px solid var(--line); border-radius:18px; padding:20px; box-shadow:0 12px 30px rgba(17,24,39,.05); }
        .hero { background:radial-gradient(circle at top right,#eef9f3,#d9e9e0 45%,#fff); }
        .stat { font-size:2rem; font-weight:700; margin-top:10px; }
        .table-wrap { overflow-x:auto; }
        table { width:100%; border-collapse:collapse; font-size:.96rem; }
        th,td { padding:12px 10px; border-bottom:1px solid var(--line); text-align:left; vertical-align:top; }
        th { font-size:.82rem; text-transform:uppercase; letter-spacing:.06em; color:var(--muted); }
        .form-grid { display:grid; gap:14px; grid-template-columns:repeat(2,minmax(0,1fr)); }
        .form-grid.full { grid-template-columns:1fr; }
        label { display:block; font-size:.92rem; margin-bottom:6px; color:var(--muted); }
        input,select,textarea,button { width:100%; border-radius:12px; border:1px solid var(--line); padding:11px 12px; font:inherit; }
        textarea { min-height:92px; resize:vertical; }
        .btn { background:var(--brand); color:#fff; border:none; cursor:pointer; }
        .btn.secondary { background:var(--brand-soft); color:var(--ink); }
        .btn.warn { background:#fff7ed; color:var(--warn); border:1px solid #fdba74; }
        .btn.danger { background:#fef2f2; color:var(--danger); border:1px solid #fca5a5; }
        .btn.small { padding:8px 10px; font-size:.88rem; }
        .stack { display:flex; gap:10px; flex-wrap:wrap; }
        .badge { display:inline-flex; padding:6px 10px; border-radius:999px; font-size:.82rem; font-weight:700; }
        .badge.Pending { background:#fef3c7; color:#92400e; }
        .badge.Approved { background:#dcfce7; color:#166534; }
        .badge.Rejected { background:#fee2e2; color:#991b1b; }
        .badge.Returned { background:#ffedd5; color:#9a3412; }
        .badge.Expired { background:#dbeafe; color:#1d4ed8; }
        .alert { padding:14px 16px; border-radius:14px; margin-bottom:18px; }
        .alert.success { background:#ecfdf5; color:#166534; border:1px solid #86efac; }
        .alert.error { background:#fef2f2; color:#991b1b; border:1px solid #fca5a5; }
        .login-shell { min-height:100vh; display:grid; place-items:center; padding:32px; background:linear-gradient(135deg,#0d2b25,#2f6e5f 52%,#f0f5f1 52%); }
        .login-card { width:min(980px,100%); display:grid; grid-template-columns:1.1fr .9fr; background:#fff; border-radius:28px; overflow:hidden; box-shadow:0 30px 80px rgba(0,0,0,.18); }
        .login-art { background:linear-gradient(180deg,#173932,#28584e); color:#e9f7f2; padding:42px; }
        .login-form { padding:42px; }
        .global-modal-backdrop { position:fixed; inset:0; background:rgba(8, 20, 18, .55); display:none; align-items:center; justify-content:center; padding:24px; z-index:2000; }
        .global-modal-backdrop.open { display:flex; }
        .global-modal-card { width:min(420px, 100%); background:#fff; border-radius:22px; border:1px solid var(--line); box-shadow:0 24px 80px rgba(0,0,0,.18); padding:24px; }
        .global-modal-title { margin:0 0 8px; font-size:1.2rem; }
        .global-modal-text { margin:0 0 18px; color:var(--muted); line-height:1.5; }
        .delete-modal-actions { justify-content:flex-end; }
        .delete-modal-actions .btn { width:auto; }
        @media (max-width:920px){ .shell,.login-card,.grid.cols-2,.grid.cols-3,.grid.cols-4,.form-grid{ grid-template-columns:1fr; } .main{ padding:18px; } }
    </style>
</head>
<body>
@if (!empty($loginPage))
    @yield('content')
@else
    <div class="shell">
        <aside class="sidebar">
            <div class="brand">Municipal Building Permit Repository System<small>{{ auth()->user()->name }} · {{ auth()->user()->role?->name }}</small></div>
            @php($links = [
                ['label' => 'Dashboard', 'route' => 'dashboard', 'module' => 'dashboard'],
                ['label' => 'Building Permit', 'route' => 'building-permits.index', 'module' => 'building-permits'],
                ['label' => 'Building Type', 'route' => 'building-types.index', 'module' => 'building-types'],
                ['label' => 'Building Category', 'route' => 'building-categories.index', 'module' => 'building-categories'],
                ['label' => 'Permit Approval', 'route' => 'permit-approvals.index', 'module' => 'permit-approvals'],
                ['label' => 'Reports', 'route' => 'reports.index', 'module' => 'reports'],
                ['label' => 'User Management', 'route' => 'users.index', 'module' => 'users'],
            ])
            @foreach ($links as $link)
                @if (auth()->user()->canAccess($link['module']))
                    <a class="nav-link {{ request()->routeIs($link['route']) ? 'active' : '' }}" href="{{ route($link['route']) }}">{{ $link['label'] }}</a>
                @endif
            @endforeach
        </aside>
        <main class="main">
            <div class="topbar">
                <div>
                    <h1 class="page-title">{{ $title ?? 'Module' }}</h1>
                    @isset($subtitle)<div class="muted">{{ $subtitle }}</div>@endisset
                </div>
                <form action="{{ route('logout') }}" method="POST">@csrf<button class="btn secondary" type="submit">Logout</button></form>
            </div>
            @if (session('success'))<div class="alert success">{{ session('success') }}</div>@endif
            @if (session('error'))<div class="alert error">{{ session('error') }}</div>@endif
            @if ($errors->any())<div class="alert error">{{ $errors->first() }}</div>@endif
            @yield('content')
        </main>
    </div>

    <div class="global-modal-backdrop" id="delete-confirmation-modal" aria-hidden="true">
        <div class="global-modal-card" role="dialog" aria-modal="true" aria-labelledby="delete-confirmation-title">
            <h3 class="global-modal-title" id="delete-confirmation-title">Confirm Delete</h3>
            <p class="global-modal-text" id="delete-confirmation-message">Are you sure you want to delete this record?</p>
            <div class="stack delete-modal-actions">
                <button class="btn danger" id="delete-confirmation-submit" type="button">Delete</button>
                <button class="btn secondary" id="delete-confirmation-cancel" type="button">Cancel</button>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const deleteModal = document.getElementById('delete-confirmation-modal');
            const deleteMessage = document.getElementById('delete-confirmation-message');
            const deleteSubmit = document.getElementById('delete-confirmation-submit');
            const deleteCancel = document.getElementById('delete-confirmation-cancel');
            let pendingDeleteForm = null;

            if (!deleteModal || !deleteMessage || !deleteSubmit || !deleteCancel) {
                return;
            }

            const closeDeleteModal = () => {
                deleteModal.classList.remove('open');
                deleteModal.setAttribute('aria-hidden', 'true');
                pendingDeleteForm = null;
            };

            document.addEventListener('submit', function (event) {
                const form = event.target;

                if (!(form instanceof HTMLFormElement) || !form.hasAttribute('data-confirm-delete') || form.dataset.deleteConfirmed === 'true') {
                    return;
                }

                event.preventDefault();
                pendingDeleteForm = form;
                deleteMessage.textContent = form.getAttribute('data-confirm-message') || 'Are you sure you want to delete this record?';
                deleteModal.classList.add('open');
                deleteModal.setAttribute('aria-hidden', 'false');
            }, true);

            deleteSubmit.addEventListener('click', function () {
                if (!pendingDeleteForm) {
                    return;
                }

                pendingDeleteForm.dataset.deleteConfirmed = 'true';
                pendingDeleteForm.submit();
            });

            deleteCancel.addEventListener('click', closeDeleteModal);

            deleteModal.addEventListener('click', function (event) {
                if (event.target === deleteModal) {
                    closeDeleteModal();
                }
            });

            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape' && deleteModal.classList.contains('open')) {
                    closeDeleteModal();
                }
            });
        });
    </script>
@endif
</body>
</html>

