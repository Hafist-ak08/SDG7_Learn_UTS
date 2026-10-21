<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') — SDG7Learn</title>
    <style>
        body { font-family: system-ui, sans-serif; background: #f7f8f4; color: #1d2b22; margin: 0; }
        .wrap { max-width: 920px; margin: 0 auto; padding: 2rem 1rem; }
        h1 { margin-top: 0; }
        a { color: #1b7f4b; }
        table { width: 100%; border-collapse: collapse; background: #fff; }
        th, td { border: 1px solid #dde3da; padding: 10px; text-align: left; vertical-align: top; }
        th { background: #eef2ea; }
        .badge { padding: 2px 10px; border-radius: 999px; color: #fff; font-size: 12px; }
        .badge-terbit { background: #198754; }
        .badge-draft { background: #6c757d; }
        .alert { padding: 10px 14px; margin-bottom: 1rem; border-radius: 6px; }
        .alert-success { background: #d1e7dd; }
        .alert-error { background: #f8d7da; }
        .field { margin-bottom: 1rem; }
        .field label { display: block; font-weight: 600; margin-bottom: 4px; }
        .field input, .field textarea, .field select { width: 100%; padding: 8px; box-sizing: border-box; }
        .err { color: #b02a37; font-size: 13px; }
        .btn { padding: 8px 16px; border: 0; border-radius: 6px; background: #1b7f4b; color: #fff; cursor: pointer; text-decoration: none; display: inline-block; }
        .btn-danger { background: #b02a37; }
        .btn-small { padding: 4px 10px; font-size: 13px; }
        form.inline { display: inline; }
    </style>
</head>
<body>
    <div class="wrap">
        <p><a href="/">&larr; Kembali ke SDG7Learn</a></p>
        @yield('content')
    </div>
</body>
</html>