<?php
// Incluir auth si no fue cargado aún (protección por si config.php no lo incluye)
if (!function_exists('esAdministrador')) {
    include_once 'auth.php';
}
// Crear carpeta de imágenes si no existe
if (!is_dir('imagenes')) {
    mkdir('imagenes', 0755, true);
}
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <title>Sayagym | Sistema</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@400;500;600;700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">

    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --red:        #B71C1C;
            --red-dark:   #7F0000;
            --red-light:  #EF5350;
            --gold:       #F5A623;
            --gold-dark:  #D48806;
            --blue:       #1565C0;
            --blue-light: #1976D2;
            --bg:         #F0F0F0;
            --card:       #FFFFFF;
            --text:       #1A1A1A;
            --muted:      #6B7280;
            --border:     #E5E7EB;
            --green:      #15803D;
            --green-lt:   #DCFCE7;
            --danger:     #DC2626;
            --danger-lt:  #FEE2E2;
            --radius:     6px;
            --shadow:     0 2px 12px rgba(0,0,0,0.10);
        }

        body {
            font-family: 'DM Sans', sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* ─── NAVBAR ─────────────────────────────────── */
        .gym-navbar {
            background: linear-gradient(135deg, var(--red-dark) 0%, var(--red) 100%);
            box-shadow: 0 4px 16px rgba(0,0,0,0.3);
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        .gym-navbar .inner {
            max-width: 1280px;
            margin: 0 auto;
            padding: 0 20px;
            height: 62px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* Logo badge */
        .gym-brand-badge {
            display: flex;
            align-items: center;
            text-decoration: none;
            margin-right: 16px;
            flex-shrink: 0;
        }
        .gym-logo-img {
            height: 44px;
            width: auto;
            object-fit: contain;
            background: #ffffff;
            padding: 4px 8px;
            border-radius: var(--radius);
            box-shadow: 0 2px 6px rgba(0,0,0,0.15);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .gym-brand-badge:hover .gym-logo-img {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.25);
        }

        /* Nav links */
        .gym-nav {
            display: flex;
            gap: 2px;
            list-style: none;
            flex: 1;
        }
        .nav-item-drop {
            position: relative;
        }
        .nav-link-drop {
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 8px 14px;
            color: rgba(255,255,255,0.85);
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 500;
            border-radius: 5px;
            transition: background 0.2s, color 0.2s;
            white-space: nowrap;
            cursor: pointer;
        }
        .nav-link-drop i { font-size: 1rem; }
        .nav-link-drop:hover,
        .nav-link-drop.active {
            background: rgba(255,255,255,0.18);
            color: #fff;
        }
        .drop-arrow {
            font-size: 0.7rem !important;
            margin-left: 2px;
            opacity: 0.7;
            transition: transform 0.2s;
        }
        .nav-item-drop:hover .drop-arrow {
            transform: rotate(180deg);
        }

        /* Dropdown menu */
        .dropdown-menu {
            display: none;
            position: absolute;
            top: calc(100% + 6px);
            left: 0;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.15);
            border: 1px solid var(--border);
            min-width: 180px;
            overflow: hidden;
            z-index: 999;
        }
        .dropdown-menu a {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 16px;
            font-size: 0.875rem;
            color: var(--text);
            text-decoration: none;
            transition: background 0.15s;
        }
        .dropdown-menu a i { color: var(--red); font-size: 0.95rem; }
        .dropdown-menu a:hover { background: #FEF2F2; color: var(--red); }
        .nav-item-drop:hover .dropdown-menu { display: block; }

        /* User badge */
        .gym-user-badge {
            display: flex;
            align-items: center;
            gap: 8px;
            background: rgba(255,255,255,0.12);
            border: 1.5px solid rgba(255,255,255,0.25);
            border-radius: 8px;
            padding: 7px 14px;
            cursor: pointer;
            transition: background 0.2s;
            flex-shrink: 0;
        }
        .gym-user-badge:hover { background: rgba(255,255,255,0.22); }
        .user-avatar {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            background: rgba(255,255,255,0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.95rem;
            color: var(--gold);
        }
        .user-name {
            font-size: 0.85rem;
            font-weight: 600;
            color: #fff;
            letter-spacing: 0.3px;
        }

        /* ─── PAGE WRAPPER ────────────────────────────── */
        .page-wrapper {
            flex: 1;
            padding: 32px 0 48px;
        }
        .container-xl {
            max-width: 1280px;
            margin: 0 auto;
            padding: 0 24px;
        }

        /* ─── PAGE TITLE ──────────────────────────────── */
        .page-title {
            font-family: 'Oswald', sans-serif;
            font-size: 1.8rem;
            font-weight: 600;
            letter-spacing: 1px;
            color: var(--text);
        }
        .page-subtitle { color: var(--muted); font-size: 0.875rem; margin-top: 4px; }

        /* ─── CARDS ───────────────────────────────────── */
        .card {
            background: var(--card);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            border: 1px solid var(--border);
            overflow: hidden;
        }
        .card-header {
            padding: 18px 24px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .card-header.red   { background: linear-gradient(135deg, var(--red-dark), var(--red)); }
        .card-header.gold  { background: linear-gradient(135deg, #B45309, var(--gold)); }
        .card-header.blue  { background: linear-gradient(135deg, #1e3a8a, var(--blue-light)); }
        .card-header.gray  { background: #F8F8F8; }

        .card-header .card-title {
            font-family: 'Oswald', sans-serif;
            font-size: 1.2rem;
            font-weight: 600;
            letter-spacing: 0.5px;
        }
        .card-header.red   .card-title,
        .card-header.gold  .card-title,
        .card-header.blue  .card-title { color: #fff; }
        .card-header.gray  .card-title { color: var(--text); }

        .card-body  { padding: 24px; }
        .card-footer {
            padding: 16px 24px;
            border-top: 1px solid var(--border);
            background: #FAFAFA;
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 10px;
        }

        /* ─── TABLE ───────────────────────────────────── */
        .table-responsive { overflow-x: auto; }
        table.gym-table { width: 100%; border-collapse: collapse; }
        table.gym-table thead tr {
            background: #F9FAFB;
            border-bottom: 2px solid var(--border);
        }
        table.gym-table thead th {
            padding: 12px 16px;
            font-family: 'Oswald', sans-serif;
            font-size: 0.8rem;
            font-weight: 600;
            letter-spacing: 1px;
            text-transform: uppercase;
            color: var(--muted);
            white-space: nowrap;
        }
        table.gym-table tbody tr {
            border-bottom: 1px solid var(--border);
            transition: background 0.15s;
        }
        table.gym-table tbody tr:hover { background: #F9FAFB; }
        table.gym-table tbody td {
            padding: 14px 16px;
            vertical-align: middle;
            font-size: 0.9rem;
        }
        table.gym-table .td-name {
            font-weight: 600;
            color: var(--text);
        }
        table.gym-table .td-muted { color: var(--muted); font-size: 0.82rem; }

        /* ─── BUTTONS ─────────────────────────────────── */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 9px 20px;
            border-radius: var(--radius);
            font-size: 0.875rem;
            font-weight: 600;
            font-family: 'DM Sans', sans-serif;
            cursor: pointer;
            border: none;
            text-decoration: none;
            transition: transform 0.15s, box-shadow 0.15s, opacity 0.15s;
            letter-spacing: 0.3px;
        }
        .btn:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(0,0,0,0.15); }
        .btn:active { transform: translateY(0); }

        .btn-red     { background: var(--red);   color: #fff; }
        .btn-red:hover { background: var(--red-dark); color: #fff; }

        .btn-gold    { background: var(--gold);  color: #1A1A1A; }
        .btn-gold:hover { background: var(--gold-dark); color: #1A1A1A; }

        .btn-blue    { background: var(--blue);  color: #fff; }
        .btn-blue:hover { background: #0D47A1; color: #fff; }

        .btn-outline {
            background: transparent;
            border: 1.5px solid var(--border);
            color: var(--muted);
        }
        .btn-outline:hover { border-color: var(--red); color: var(--red); }

        .btn-link    { background: none; color: var(--muted); padding: 9px 12px; }
        .btn-link:hover { color: var(--red); box-shadow: none; transform: none; }

        .btn-icon {
            padding: 7px;
            width: 34px;
            height: 34px;
            border-radius: var(--radius);
            background: #F3F4F6;
            border: 1px solid var(--border);
            color: var(--muted);
            font-size: 1rem;
            justify-content: center;
        }
        .btn-icon:hover { background: #FEE2E2; border-color: var(--danger); color: var(--danger); }
        .btn-icon.edit:hover { background: #DBEAFE; border-color: var(--blue); color: var(--blue); }

        .btn-list { display: flex; gap: 6px; align-items: center; }

        /* ─── BADGES ──────────────────────────────────── */
        .badge {
            display: inline-flex;
            align-items: center;
            padding: 3px 10px;
            border-radius: 999px;
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        .badge-green   { background: var(--green-lt);  color: var(--green); }
        .badge-red     { background: var(--danger-lt); color: var(--danger); }
        .badge-gold    { background: #FEF3C7; color: #92400E; }
        .badge-blue    { background: #DBEAFE; color: var(--blue); }
        .badge-purple  { background: #EDE9FE; color: #6D28D9; }
        .badge-gray    { background: #F3F4F6; color: #374151; }
        .badge-secondary { background: #F3F4F6; color: #6B7280; }

        /* ─── FORM CONTROLS ───────────────────────────── */
        .form-label {
            display: block;
            font-size: 0.8rem;
            font-weight: 600;
            color: #374151;
            margin-bottom: 6px;
            letter-spacing: 0.3px;
            text-transform: uppercase;
        }
        .form-control, .form-select {
            width: 100%;
            padding: 10px 14px;
            border: 1.5px solid var(--border);
            border-radius: var(--radius);
            font-size: 0.9rem;
            font-family: 'DM Sans', sans-serif;
            color: var(--text);
            background: #fff;
            transition: border-color 0.2s, box-shadow 0.2s;
            outline: none;
            appearance: auto;
        }
        .form-control:focus, .form-select:focus {
            border-color: var(--red);
            box-shadow: 0 0 0 3px rgba(183,28,28,0.1);
        }
        .form-control::placeholder { color: #9CA3AF; }

        /* ─── GRID ────────────────────────────────────── */
        .row { display: flex; flex-wrap: wrap; gap: 16px 0; margin: 0 -8px; }
        [class*="col-"] { padding: 0 8px; }
        .col-12  { width: 100%; }
        .col-md-3  { width: 25%; }
        .col-md-4  { width: 33.333%; }
        .col-md-6  { width: 50%; }
        .col-md-8  { width: 66.666%; }
        .col-md-9  { width: 75%; }
        .col-md-10 { width: 83.333%; }
        .col-auto  { width: auto; }
        .col       { flex: 1; }
        .mb-3      { margin-bottom: 16px; }
        .mb-4      { margin-bottom: 24px; }
        .mx-auto   { margin-left: auto; margin-right: auto; }
        .text-end  { text-align: right; }
        .d-flex    { display: flex; }
        .align-items-center { align-items: center; }
        .gap-3     { gap: 12px; }

        /* ─── KPI CARDS ───────────────────────────────── */
        .kpi-card {
            background: var(--card);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            border: 1px solid var(--border);
            padding: 20px 24px;
            display: flex;
            align-items: center;
            gap: 16px;
            text-decoration: none;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .kpi-card:hover { transform: translateY(-3px); box-shadow: 0 8px 24px rgba(0,0,0,0.12); }
        .kpi-icon {
            width: 52px;
            height: 52px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
            flex-shrink: 0;
        }
        .kpi-icon.red    { background: var(--danger-lt); color: var(--danger); }
        .kpi-icon.green  { background: var(--green-lt);  color: var(--green); }
        .kpi-icon.blue   { background: #DBEAFE; color: var(--blue); }
        .kpi-icon.gold   { background: #FEF3C7; color: #B45309; }
        .kpi-icon.purple { background: #EDE9FE; color: #6D28D9; }
        .kpi-num {
            font-family: 'Oswald', sans-serif;
            font-size: 2rem;
            font-weight: 700;
            line-height: 1;
            color: var(--text);
        }
        .kpi-label { font-size: 0.8rem; color: var(--muted); font-weight: 500; margin-top: 3px; }

        /* ─── ALERTS ──────────────────────────────────── */
        .alert {
            padding: 12px 18px;
            border-radius: var(--radius);
            font-size: 0.875rem;
            margin-bottom: 20px;
            font-weight: 500;
        }
        .alert-success { background: var(--green-lt); color: var(--green); border-left: 4px solid var(--green); }
        .alert-danger  { background: var(--danger-lt); color: var(--danger); border-left: 4px solid var(--danger); }

        /* ─── HR TEXT ─────────────────────────────────── */
        .hr-text {
            position: relative;
            text-align: center;
            font-family: 'Oswald', sans-serif;
            font-size: 0.8rem;
            font-weight: 600;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            color: var(--red);
            margin: 8px 0 12px;
            width: 100%;
        }
        .hr-text::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background: var(--border);
        }
        .hr-text span {
            position: relative;
            background: var(--card);
            padding: 0 12px;
        }

        /* ─── MOTIVATIONAL PANEL ──────────────────────── */
        .motive-panel {
            background: #1A1A1A;
            border-radius: var(--radius);
            padding: 32px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            gap: 16px;
            min-height: 180px;
        }
        .motive-panel .line1 {
            font-family: 'Oswald', sans-serif;
            font-size: 2rem;
            font-weight: 700;
            color: #fff;
            line-height: 1.1;
        }
        .motive-panel .line2 {
            font-family: 'Oswald', sans-serif;
            font-size: 1.3rem;
            font-weight: 400;
            color: var(--gold);
            letter-spacing: 1px;
        }

        /* ─── UTILITIES ───────────────────────────────── */
        .text-red    { color: var(--red); }
        .text-green  { color: var(--green); }
        .text-gold   { color: var(--gold-dark); }
        .text-muted  { color: var(--muted); }
        .fw-bold     { font-weight: 700; }
        .font-oswald { font-family: 'Oswald', sans-serif; }
        .small       { font-size: 0.82rem; }
        .mt-1  { margin-top: 4px; }
        .mt-3  { margin-top: 12px; }
        .mt-4  { margin-top: 24px; }
        .me-1  { margin-right: 4px; }
        .me-2  { margin-right: 8px; }
        .ms-auto { margin-left: auto; }
        .p-0   { padding: 0; }
        .m-0   { margin: 0; }

        @media (max-width: 768px) {
            [class*="col-md-"] { width: 100%; }
            .gym-nav { display: none; }
            .kpi-num { font-size: 1.6rem; }
        }
    </style>
</head>
<body>

<nav class="gym-navbar">
    <div class="inner">

        <!-- Logo Badge -->
        <a href="index.php" class="gym-brand-badge">
            <img src="../Sayagym%20logo.png" alt="Sayagym Logo" class="gym-logo-img" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
            <span style="display:none; font-family:'Oswald',sans-serif; font-size:1.3rem; font-weight:700; color:#fff; letter-spacing:2px; padding:0 8px;">SAYAGYM</span>
        </a>

        <!-- Nav Items -->
        <ul class="gym-nav">
            <li class="nav-item-drop">
                <a href="index.php" class="nav-link-drop">
                    <i class="ti ti-layout-dashboard"></i>
                    Inicio
                </a>
            </li>
            <?php if (esAdministrador()): ?>
            <li class="nav-item-drop">
                <a href="#" class="nav-link-drop has-drop">
                    <i class="ti ti-users"></i>
                    Administración
                    <i class="ti ti-chevron-down drop-arrow"></i>
                </a>
                <div class="dropdown-menu">
                    <a href="socios.php"><i class="ti ti-user"></i> Socios</a>
                    <a href="entrenadores.php"><i class="ti ti-barbell"></i> Entrenadores</a>
                    <a href="membresias.php"><i class="ti ti-calendar-event"></i> Membresías</a>
                </div>
            </li>
            
            <li class="nav-item-drop">
                <a href="pagos.php" class="nav-link-drop">
                    <i class="ti ti-cash"></i>
                    Caja y Pagos
                </a>
            </li>
            <?php endif; ?>
        </ul>

        <!-- User Badge with Dropdown for Logout -->
        <ul style="list-style:none; margin:0; padding:0;">
            <li class="nav-item-drop">
                <div class="gym-user-badge">
                    <div class="user-avatar">
                        <i class="ti ti-user"></i>
                    </div>
                    <span class="user-name"><?php echo htmlspecialchars($_SESSION['nombre'] ?? 'Usuario'); ?> (<?php echo $_SESSION['rol'] ?? ''; ?>)</span>
                    <i class="ti ti-chevron-down" style="font-size:0.75rem; opacity:0.6;"></i>
                </div>
                <div class="dropdown-menu" style="right:0; left:auto; min-width:150px;">
                    <a href="logout.php"><i class="ti ti-logout text-red"></i> Cerrar Sesión</a>
                </div>
            </li>
        </ul>

    </div>
</nav>
