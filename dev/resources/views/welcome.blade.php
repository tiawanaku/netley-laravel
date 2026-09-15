<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Netley</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body { font-family: system-ui, sans-serif; background: #f4f6f9; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0; }
        .card { background: #fff; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,.15); padding: 2.5rem; text-align: center; }
        h1 { margin-top: 0; }
        .panels { display: flex; gap: 1rem; margin-top: 1.5rem; }
        .panels a { flex: 1; padding: .75rem 1.5rem; border-radius: 4px; text-decoration: none; color: #fff; font-weight: 600; }
        .admin { background: #343a40; }
        .staff { background: #007bff; }
        .portal { background: #28a745; }
    </style>
</head>
<body>
    <div class="card">
        <h1>Netley</h1>
        <p>Sistema de gestión legal — en construcción.</p>
        <div class="panels">
            <a class="admin" href="{{ route('admin.login') }}">Admin</a>
            <a class="staff" href="{{ route('staff.login') }}">Staff</a>
            <a class="portal" href="{{ route('portal.login') }}">Portal Cliente</a>
        </div>
    </div>
</body>
</html>
