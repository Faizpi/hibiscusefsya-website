<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no, maximum-scale=1, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Hibiscus Efsya</title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('assets/img/favicon-rounded.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('assets/img/favicon-rounded.png') }}">

    <link href="{{ asset('template/vendor/fontawesome-free/css/all.min.css') }}" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap"
        rel="stylesheet">
    <link href="{{ asset('template/css/sb-admin-2.min.css') }}" rel="stylesheet">
    {{-- Select2 CSS --}}
    <link href="{{ asset('assets/bundle/select2/dist/css/select2.min.css') }}" rel="stylesheet">

    <style>
        :root {
            --sidebar-bg: #ffffff;
            --sidebar-hover: #eff6ff;
            --sidebar-active: #3b82f6;
            --text-primary: #1f2937;
            --text-secondary: #6b7280;
            --text-muted: #9ca3af;
            --border-color: #e5e7eb;
            --bg-light: #f9fafb;
        }

        * {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        body {
            background: var(--bg-light);
            overflow-x: hidden;
        }

        /* Hide caret on custom dropdown buttons that use ellipsis icon */
        .dropdown-toggle.no-caret::after {
            display: none;
        }

        /* ========== WRAPPER ========== */
        #wrapper {
            display: flex;
            min-height: 100vh;
            width: 100%;
            overflow-x: hidden;
        }

        /* ========== SIDEBAR - White Blue Style (FIXED) ========== */
        .sidebar {
            background: var(--sidebar-bg) !important;
            height: 100vh;
            width: 14rem !important;
            min-width: 14rem;
            flex-shrink: 0;
            overflow-y: auto;
            overflow-x: hidden;
            border-right: 1px solid var(--border-color);
            transition: all 0.2s ease;
            z-index: 1000;
            position: fixed;
            top: 0;
            left: 0;
        }

        .sidebar .sidebar-brand {
            height: 65px;
            padding: 0 1rem;
            background: transparent;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
        }

        .sidebar .sidebar-brand-text {
            color: var(--sidebar-active);
            font-weight: 700;
            font-size: 1.1rem;
        }

        .sidebar .sidebar-brand-icon img {
            filter: none !important;
            height: 36px;
            border-radius: 8px;
        }

        .sidebar .nav-item {
            margin: 2px 8px;
        }

        .sidebar .nav-item .nav-link {
            color: var(--text-secondary);
            font-weight: 500;
            font-size: 0.875rem;
            padding: 0.75rem 1rem;
            display: flex;
            align-items: center;
            transition: all 0.15s ease;
            border-radius: 8px;
        }

        .sidebar .nav-item .nav-link i {
            width: 20px;
            font-size: 1rem;
            margin-right: 0.75rem;
            color: var(--text-muted);
        }

        .sidebar .nav-item .nav-link:hover {
            background: var(--sidebar-hover);
            color: var(--sidebar-active);
        }

        .sidebar .nav-item .nav-link:hover i {
            color: var(--sidebar-active);
        }

        .sidebar .nav-item.active .nav-link {
            background: var(--sidebar-hover);
            color: var(--sidebar-active);
            font-weight: 600;
        }

        .sidebar .nav-item.active .nav-link i {
            color: var(--sidebar-active);
        }

        .sidebar .sidebar-heading {
            color: var(--text-muted);
            font-size: 0.7rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            padding: 1.25rem 1rem 0.5rem;
        }

        .sidebar hr.sidebar-divider {
            border-top: 1px solid var(--border-color);
            margin: 0.5rem 1rem;
        }

        .sidebar #sidebarToggle {
            background: var(--sidebar-hover);
            border: 1px solid var(--border-color);
        }

        .sidebar #sidebarToggle::after {
            color: var(--sidebar-active);
        }

        .sidebar #sidebarToggle:hover {
            background: var(--sidebar-active);
        }

        .sidebar #sidebarToggle:hover::after {
            color: #fff;
        }

        /* ========== TOPBAR (FIXED) ========== */
        .topbar {
            background: #fff;
            height: 65px;
            padding: 0 1.5rem;
            box-shadow: none;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            position: fixed;
            top: 0;
            right: 0;
            left: 14rem;
            z-index: 999;
            transition: left 0.2s ease;
        }

        /* Adjust topbar when sidebar is toggled */
        .sidebar.toggled~#content-wrapper .topbar {
            left: 6.5rem;
        }

        .topbar .navbar-nav .nav-item .nav-link {
            color: var(--text-secondary);
            padding: 0.5rem;
        }

        .topbar .dropdown-toggle::after {
            display: none;
        }

        .topbar .user-info {
            display: flex;
            align-items: center;
            padding: 0.5rem 0.75rem;
            border-radius: 8px;
            transition: background 0.15s ease;
        }

        .topbar .user-info:hover {
            background: var(--bg-light);
        }

        .topbar .user-name {
            font-weight: 600;
            font-size: 0.875rem;
            color: var(--text-primary);
            margin-right: 0.5rem;
        }

        .topbar .user-role {
            font-size: 0.75rem;
            color: var(--text-muted);
        }

        .topbar .img-profile {
            width: 36px;
            height: 36px;
            margin-left: 0.75rem;
        }

        /* Notification Bell Styles */
        .topbar .nav-item.notification-bell {
            display: flex;
            align-items: center;
            margin-right: 0.5rem;
        }

        .topbar .nav-item.notification-bell .nav-link {
            position: relative;
            padding: 0;
            color: var(--text-secondary);
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            transition: all 0.15s ease;
            background: var(--bg-light);
        }

        .topbar .nav-item.notification-bell .nav-link:hover {
            color: var(--sidebar-active);
            background: #e5e7eb;
        }

        .topbar .nav-item.notification-bell .badge-counter {
            position: absolute;
            top: -2px;
            right: -4px;
            height: 16px;
            min-width: 16px;
            font-size: 0.6rem;
            font-weight: 700;
            padding: 0 4px;
            line-height: 16px;
            border-radius: 8px;
            background: #ef4444;
            color: #fff;
            border: 2px solid #fff;
            box-shadow: 0 1px 3px rgba(239, 68, 68, 0.4);
        }

        .topbar .notification-dropdown {
            min-width: 340px;
            max-width: 380px;
            padding: 0;
            border: 1px solid var(--border-color);
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
            border-radius: 12px;
            overflow: hidden;
            margin-top: 0.5rem;
        }

        .notification-dropdown .dropdown-header {
            background: var(--bg-light);
            padding: 0.85rem 1rem;
            font-weight: 600;
            font-size: 0.875rem;
            color: var(--text-primary);
            border-bottom: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .notification-dropdown .dropdown-header .badge {
            font-size: 0.7rem;
            padding: 0.25rem 0.5rem;
        }

        .notification-dropdown .notification-list {
            max-height: 320px;
            overflow-y: auto;
        }

        .notification-dropdown .notification-item {
            display: flex;
            align-items: center;
            padding: 0.75rem 1rem;
            border-bottom: 1px solid var(--border-color);
            text-decoration: none;
            transition: background 0.15s ease;
        }

        .notification-dropdown .notification-item:hover {
            background: var(--bg-light);
        }

        .notification-dropdown .notification-item:last-child {
            border-bottom: none;
        }

        .notification-dropdown .notification-icon {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 0.75rem;
            flex-shrink: 0;
            font-size: 0.9rem;
        }

        .notification-dropdown .notification-icon.bg-primary {
            background: rgba(59, 130, 246, 0.15) !important;
            color: #3b82f6;
        }

        .notification-dropdown .notification-icon.bg-success {
            background: rgba(16, 185, 129, 0.15) !important;
            color: #10b981;
        }

        .notification-dropdown .notification-icon.bg-warning {
            background: rgba(245, 158, 11, 0.15) !important;
            color: #f59e0b;
        }

        .notification-dropdown .notification-content {
            flex: 1;
            min-width: 0;
        }

        .notification-dropdown .notification-title {
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 2px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .notification-dropdown .notification-subtitle {
            font-size: 0.75rem;
            color: var(--text-muted);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .notification-dropdown .notification-meta {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            margin-left: 0.5rem;
            flex-shrink: 0;
        }

        .notification-dropdown .notification-amount {
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--text-primary);
            white-space: nowrap;
        }

        .notification-dropdown .notification-time {
            font-size: 0.7rem;
            color: var(--text-muted);
        }

        .notification-dropdown .dropdown-footer {
            background: var(--bg-light);
            padding: 0.75rem 1rem;
            text-align: center;
            border-top: 1px solid var(--border-color);
        }

        .notification-dropdown .dropdown-footer a {
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--sidebar-active);
            text-decoration: none;
        }

        .notification-dropdown .dropdown-footer a:hover {
            text-decoration: underline;
        }

        .notification-dropdown .empty-notification {
            padding: 2rem 1rem;
            text-align: center;
            color: var(--text-muted);
        }

        .notification-dropdown .empty-notification i {
            font-size: 2rem;
            margin-bottom: 0.5rem;
            opacity: 0.5;
        }

        .topbar .divider-vertical {
            display: none !important;
        }

        .topbar .dropdown-menu {
            border: 1px solid var(--border-color);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            padding: 0.5rem;
            min-width: 200px;
        }

        .topbar .dropdown-item {
            padding: 0.6rem 1rem;
            font-size: 0.875rem;
            color: var(--text-primary);
            border-radius: 6px;
        }

        .topbar .dropdown-item:hover {
            background: var(--bg-light);
        }

        .topbar .dropdown-item i {
            width: 20px;
            color: var(--text-muted);
        }

        /* ========== CONTENT ========== */
        #content-wrapper {
            background: var(--bg-light);
            flex: 1;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            width: 100%;
            margin-left: 14rem;
            transition: margin-left 0.2s ease;
        }

        /* Adjust content-wrapper when sidebar is toggled */
        .sidebar.toggled~#content-wrapper {
            margin-left: 6.5rem;
        }

        #content {
            padding-top: 65px;
            flex: 1;
        }

        .container-fluid {
            padding: 1.5rem;
            max-width: 100%;
            overflow-x: hidden;
            box-sizing: border-box;
        }

        /* Page Title */
        .page-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 1.5rem;
        }

        .h3.text-gray-800 {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--text-primary) !important;
        }

        /* ========== CARDS ========== */
        .card {
            border: 1px solid var(--border-color);
            border-radius: 12px;
            box-shadow: none;
            background: #fff;
        }

        .card-header {
            background: #fff;
            border-bottom: 1px solid var(--border-color);
            padding: 1rem 1.25rem;
            font-weight: 600;
            font-size: 0.875rem;
            color: var(--text-primary);
        }

        .card-header.bg-primary,
        .card-header.bg-gradient-primary {
            background: var(--sidebar-active) !important;
            border: none;
        }

        .card-body {
            padding: 1.25rem;
        }

        .card-body .table-responsive {
            overflow-x: auto;
        }

        /* ========== BUTTONS ========== */
        .btn {
            font-weight: 600;
            font-size: 0.875rem;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            border: none;
            transition: all 0.15s ease;
        }

        .btn-primary {
            background: var(--sidebar-active);
        }

        .btn-primary:hover {
            background: #2563eb;
        }

        .btn-success {
            background: #10b981;
        }

        .btn-success:hover {
            background: #059669;
        }

        .btn-danger {
            background: #ef4444;
        }

        .btn-danger:hover {
            background: #dc2626;
        }

        .btn-warning {
            background: #f59e0b;
            color: #fff;
        }

        .btn-warning:hover {
            background: #d97706;
            color: #fff;
        }

        .btn-secondary {
            background: #6b7280;
        }

        .btn-secondary:hover {
            background: #4b5563;
        }

        .btn-outline-primary {
            border: 1px solid var(--sidebar-active);
            color: var(--sidebar-active);
            background: transparent;
        }

        .btn-outline-primary:hover {
            background: var(--sidebar-active);
            color: #fff;
        }

        .btn-sm {
            padding: 0.375rem 0.75rem;
            font-size: 0.8125rem;
        }

        /* ========== FORMS ========== */
        .form-control {
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 0.5rem 0.75rem;
            font-size: 0.875rem;
            color: var(--text-primary);
            transition: border-color 0.15s ease;
            height: auto;
            min-height: 38px;
            line-height: 1.5;
        }

        .form-control:focus {
            border-color: var(--sidebar-active);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .form-control::placeholder {
            color: var(--text-muted);
        }

        /* Select / Dropdown */
        select.form-control {
            padding-right: 2rem;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 0.75rem center;
            background-size: 12px;
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
        }

        /* Action Dropdown Styling */
        .action-dropdown .dropdown-toggle {
            background: #f8f9fa;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 0.375rem 0.5rem;
            color: #64748b;
            transition: all 0.2s ease;
        }

        .action-dropdown .dropdown-toggle:hover {
            background: #e2e8f0;
            color: #334155;
            border-color: #cbd5e1;
        }

        .action-dropdown .dropdown-toggle:focus {
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
            border-color: #3b82f6;
        }

        .action-dropdown .dropdown-toggle::after {
            display: none;
        }

        .action-dropdown .dropdown-menu {
            min-width: 160px;
            padding: 0.5rem 0;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.12);
            margin-top: 4px;
        }

        .action-dropdown .dropdown-item {
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
            color: #334155;
            display: flex;
            align-items: center;
            gap: 0.625rem;
            transition: all 0.15s ease;
        }

        .action-dropdown .dropdown-item i {
            width: 16px;
            text-align: center;
            font-size: 0.8125rem;
        }

        .action-dropdown .dropdown-item:hover {
            background: #f1f5f9;
            color: #1e293b;
        }

        .action-dropdown .dropdown-item.text-primary:hover {
            background: rgba(59, 130, 246, 0.1);
        }

        .action-dropdown .dropdown-item.text-success:hover {
            background: rgba(34, 197, 94, 0.1);
        }

        .action-dropdown .dropdown-item.text-warning:hover {
            background: rgba(245, 158, 11, 0.1);
        }

        .action-dropdown .dropdown-item.text-danger:hover {
            background: rgba(239, 68, 68, 0.1);
        }

        .action-dropdown .dropdown-divider {
            margin: 0.375rem 0;
            border-color: #e2e8f0;
        }

        /* Select2 Styling */
        .select2-container--default .select2-selection--single {
            border: 1px solid var(--border-color) !important;
            border-radius: 8px !important;
            height: 38px !important;
            padding: 0.25rem 0.5rem !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 28px !important;
            padding-left: 0.25rem !important;
            color: var(--text-primary) !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__placeholder {
            color: var(--text-muted) !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 36px !important;
            right: 8px !important;
        }

        .select2-container--default.select2-container--focus .select2-selection--single {
            border-color: var(--sidebar-active) !important;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1) !important;
        }

        .select2-dropdown {
            border: 1px solid var(--border-color) !important;
            border-radius: 8px !important;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1) !important;
        }

        .select2-results__option {
            padding: 0.5rem 0.75rem !important;
            font-size: 0.875rem !important;
        }

        .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background: var(--sidebar-active) !important;
        }

        label {
            font-weight: 500;
            font-size: 0.875rem;
            color: var(--text-primary);
            margin-bottom: 0.375rem;
        }

        /* ========== TABLES ========== */
        .table {
            font-size: 0.875rem;
            width: 100%;
            max-width: 100%;
            table-layout: auto;
        }

        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            width: 100%;
            max-width: 100%;
            display: block;
        }

        /* Cards need overflow control for nested tables */
        .card {
            overflow: hidden;
            max-width: 100%;
        }

        .card-body {
            overflow-x: auto;
            max-width: 100%;
        }

        .table thead th {
            background: var(--bg-light);
            color: var(--text-secondary);
            font-weight: 600;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.025em;
            border-bottom: 1px solid var(--border-color);
            padding: 0.75rem 0.75rem;
            white-space: nowrap;
        }

        .table tbody td {
            padding: 0.75rem 0.75rem;
            color: var(--text-primary);
            border-bottom: 1px solid var(--border-color);
            vertical-align: middle;
            word-break: break-word;
        }

        .table tbody tr:hover {
            background: var(--bg-light);
        }

        .table tbody tr:last-child td {
            border-bottom: none;
        }

        /* ========== BADGES ========== */
        .badge {
            font-weight: 500;
            font-size: 0.75rem;
            padding: 0.25rem 0.625rem;
            border-radius: 6px;
        }

        .badge-primary {
            background: rgba(59, 130, 246, 0.1);
            color: var(--sidebar-active);
        }

        .badge-success {
            background: rgba(16, 185, 129, 0.1);
            color: #059669;
        }

        .badge-danger {
            background: rgba(239, 68, 68, 0.1);
            color: #dc2626;
        }

        .badge-warning {
            background: rgba(245, 158, 11, 0.1);
            color: #d97706;
        }

        /* ========== ALERTS ========== */
        .alert {
            border: none;
            border-radius: 8px;
            font-size: 0.875rem;
            padding: 0.875rem 1rem;
        }

        .alert-success {
            background: rgba(16, 185, 129, 0.1);
            color: #059669;
        }

        .alert-danger {
            background: rgba(239, 68, 68, 0.1);
            color: #dc2626;
        }

        .alert-warning {
            background: rgba(245, 158, 11, 0.1);
            color: #d97706;
        }

        .alert-info {
            background: rgba(59, 130, 246, 0.1);
            color: var(--sidebar-active);
        }

        /* ========== MODAL ========== */
        .modal-content {
            border: none;
            border-radius: 12px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }

        .modal-header {
            border-bottom: 1px solid var(--border-color);
            padding: 1rem 1.25rem;
        }

        .modal-title {
            font-weight: 600;
            font-size: 1rem;
            color: var(--text-primary);
        }

        .modal-body {
            padding: 1.25rem;
            color: var(--text-secondary);
        }

        .modal-footer {
            border-top: 1px solid var(--border-color);
            padding: 1rem 1.25rem;
        }

        /* ========== MOBILE RESPONSIVE FOR FORMS ========== */
        @media (max-width: 576px) {

            /* Smaller font sizes for mobile */
            body {
                font-size: 0.8125rem;
            }

            .h3,
            h3 {
                font-size: 1.1rem !important;
            }

            .h4,
            h4 {
                font-size: 1rem !important;
            }

            .h5,
            h5 {
                font-size: 0.9rem !important;
            }

            .h6,
            h6 {
                font-size: 0.8rem !important;
            }

            /* Card adjustments */
            .card-body {
                padding: 0.75rem !important;
            }

            .card-header {
                padding: 0.75rem !important;
                font-size: 0.8rem;
            }

            /* Form controls smaller */
            .form-control {
                font-size: 0.8rem !important;
                padding: 0.375rem 0.5rem !important;
                min-height: 34px !important;
            }

            label {
                font-size: 0.75rem !important;
                margin-bottom: 0.25rem !important;
            }

            /* Buttons smaller */
            .btn {
                font-size: 0.75rem !important;
                padding: 0.375rem 0.625rem !important;
            }

            .btn-sm {
                font-size: 0.7rem !important;
                padding: 0.25rem 0.5rem !important;
            }

            /* Tables on mobile */
            .table {
                font-size: 0.75rem !important;
            }

            .table thead th {
                padding: 0.5rem 0.375rem !important;
                font-size: 0.65rem !important;
            }

            .table tbody td {
                padding: 0.5rem 0.375rem !important;
            }

            /* Info table on show pages */
            .table-borderless td {
                padding: 0.25rem 0.5rem !important;
                font-size: 0.75rem !important;
                word-break: break-word !important;
            }

            .table-borderless td strong {
                font-size: 0.7rem !important;
            }

            /* Badges smaller */
            .badge {
                font-size: 0.65rem !important;
                padding: 0.2rem 0.4rem !important;
            }

            /* Alerts */
            .alert {
                font-size: 0.75rem !important;
                padding: 0.5rem 0.75rem !important;
            }

            /* Select2 mobile */
            .select2-container--default .select2-selection--single {
                height: 34px !important;
                font-size: 0.8rem !important;
            }

            .select2-container--default .select2-selection--single .select2-selection__rendered {
                line-height: 24px !important;
                font-size: 0.8rem !important;
            }

            /* Page header mobile */
            .d-sm-flex {
                flex-direction: column !important;
                align-items: flex-start !important;
                gap: 0.5rem;
            }

            .d-sm-flex .btn,
            .d-sm-flex a.btn {
                margin-bottom: 0.25rem;
            }

            /* Number inputs */
            input[type="number"] {
                font-size: 0.8rem !important;
            }

            /* Container padding */
            .container-fluid {
                padding: 0.75rem !important;
            }

            /* Total display */
            #grand-total-display,
            #grand-total-bottom {
                font-size: 1rem !important;
            }

            /* Row items on forms */
            .row {
                margin-left: -0.375rem;
                margin-right: -0.375rem;
            }

            .row>[class*="col-"] {
                padding-left: 0.375rem;
                padding-right: 0.375rem;
            }

            /* Textarea */
            textarea.form-control {
                font-size: 0.8rem !important;
            }

            /* Summary table */
            #summary-table td {
                padding: 0.375rem 0.5rem !important;
                font-size: 0.75rem !important;
            }
        }

        /* ========== MOBILE INPUT FOCUS FIX ========== */
        /* Mencegah keyboard tertutup saat mengetik di mobile */
        @media (max-width: 768px) {
            /* Pastikan input dan textarea dapat menerima fokus dengan benar */
            input[type="text"],
            input[type="email"],
            input[type="password"],
            input[type="number"],
            input[type="tel"],
            input[type="search"],
            input[type="url"],
            textarea,
            select {
                font-size: 16px !important; /* Mencegah auto-zoom pada iOS */
                -webkit-appearance: none;
                -moz-appearance: none;
                appearance: none;
                touch-action: manipulation;
            }

            /* Fix untuk form-control */
            .form-control {
                font-size: 16px !important;
                touch-action: manipulation;
            }

        }

        /* ========== MOBILE DROPDOWN BACKDROP & BOTTOM SHEET ========== */
        /* Ini perlu di luar media query agar selalu tersedia */
        
        /* Overlay backdrop untuk mobile dropdown */
        .mobile-dropdown-backdrop {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.3);
            z-index: 1055;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.2s ease, visibility 0.2s ease;
        }
        
        .mobile-dropdown-backdrop.show {
            opacity: 1;
            visibility: visible;
        }
        
        /* Mobile dropdown menu styling - bottom sheet style */
        .dropdown-menu.mobile-dropdown-active {
            position: fixed !important;
            bottom: 0 !important;
            left: 0 !important;
            right: 0 !important;
            top: auto !important;
            transform: none !important;
            z-index: 1060 !important;
            min-width: 100% !important;
            max-width: 100% !important;
            width: 100% !important;
            border-radius: 16px 16px 0 0 !important;
            padding: 1rem !important;
            box-shadow: 0 -4px 25px rgba(0, 0, 0, 0.15) !important;
            margin: 0 !important;
            background: #fff !important;
            border: none !important;
        }
        
        .dropdown-menu.mobile-dropdown-active .dropdown-item {
            padding: 0.875rem 1rem !important;
            font-size: 0.9375rem !important;
            border-radius: 8px !important;
            margin-bottom: 4px !important;
        }
        
        .dropdown-menu.mobile-dropdown-active .dropdown-divider {
            margin: 0.5rem 0 !important;
        }

        /* Touch device specific fixes */
        @media (hover: none) and (pointer: coarse) {
            input, textarea, select {
                touch-action: manipulation;
            }
            
            /* Mencegah double-tap zoom */
            * {
                touch-action: manipulation;
            }
        }

        /* ========== FOOTER ========== */
        .sticky-footer {
            background: #fff;
            border-top: 1px solid var(--border-color);
            padding: 1rem;
        }

        .sticky-footer .copyright {
            font-size: 0.8125rem;
            color: var(--text-muted);
        }

        /* ========== PAGINATION ========== */
        .pagination .page-link {
            border: 1px solid var(--border-color);
            color: var(--text-primary);
            font-size: 0.875rem;
            padding: 0.5rem 0.875rem;
        }

        .pagination .page-item.active .page-link {
            background: var(--sidebar-active);
            border-color: var(--sidebar-active);
        }

        .pagination .page-link:hover {
            background: var(--bg-light);
        }

        /* ========== UTILITIES ========== */
        .text-primary {
            color: var(--sidebar-active) !important;
        }

        .bg-primary {
            background: var(--sidebar-active) !important;
        }

        /* ========== SIDEBAR TOGGLED (Minimized) ========== */
        .sidebar.toggled {
            width: 6.5rem !important;
            min-width: 6.5rem;
            overflow-x: hidden;
            overflow-y: auto;
        }

        .sidebar.toggled~#content-wrapper {
            margin-left: 6.5rem;
        }

        .sidebar.toggled~#content-wrapper .topbar {
            left: 6.5rem;
        }

        .sidebar.toggled .sidebar-brand {
            padding: 1.25rem 0;
            justify-content: center;
        }

        .sidebar.toggled .sidebar-brand .sidebar-brand-icon {
            margin: 0 auto;
        }

        .sidebar.toggled .sidebar-brand .sidebar-brand-icon img {
            height: 28px !important;
            border-radius: 6px;
        }

        .sidebar.toggled .nav-item .nav-link {
            padding: 0.75rem 1rem;
            justify-content: center;
            text-align: center;
        }

        .sidebar.toggled .nav-item .nav-link i {
            margin-right: 0;
            font-size: 1.1rem;
            width: auto;
            flex-shrink: 0;
        }

        .sidebar.toggled .nav-item .nav-link span {
            display: none !important;
        }

        .sidebar.toggled .sidebar-brand-text {
            display: none !important;
        }

        .sidebar.toggled .sidebar-heading {
            display: none;
        }

        .sidebar.toggled hr.sidebar-divider {
            margin: 0.5rem 0.75rem;
        }

        /* Tooltip style - remove completely, use native title */
        .sidebar.toggled .nav-item {
            position: relative;
        }

        .sidebar.toggled .nav-item .nav-link::after {
            display: none !important;
            content: none !important;
        }

        /* ========== TABLES RESPONSIVE ========== */
        .table-responsive {
            overflow-x: auto !important;
            overflow-y: hidden !important;
            -webkit-overflow-scrolling: touch;
            position: relative;
            max-width: 100%;
        }

        /* Ensure parent containers don't overflow */
        #content-wrapper, #content, .container-fluid {
            overflow-x: hidden;
            max-width: 100%;
        }

        /* Mobile: enable horizontal scroll */
        @media (max-width: 768px) {
            .table-responsive {
                overflow-x: auto !important;
                overflow-y: hidden !important;
                -webkit-overflow-scrolling: touch;
                display: block;
                width: 100%;
                max-width: calc(100vw - 2rem);
            }

            .table-responsive .table {
                min-width: 700px;
            }
            
            /* Container adjustments for mobile */
            .container-fluid {
                padding: 0.75rem !important;
                width: 100%;
                max-width: 100%;
                overflow-x: hidden;
            }

            /* Card responsive adjustments */
            .card {
                width: 100%;
                max-width: 100%;
                overflow: hidden;
            }

            .card-body {
                overflow-x: auto;
                max-width: 100%;
                padding: 0.75rem;
            }

            /* Tombol di card-header wrap di mobile */
            .card-header .btn-group-mobile,
            .card-header > div {
                flex-wrap: wrap;
                gap: 0.5rem;
            }

            .card-header .btn {
                margin-bottom: 0 !important;
                margin-right: 0 !important;
            }
        }

        /* Tablet specific (768px - 1024px) */
        @media (min-width: 769px) and (max-width: 1024px) {
            .table-responsive {
                overflow-x: auto !important;
                max-width: 100%;
            }

            .table-responsive .table {
                min-width: 800px;
            }

            .container-fluid {
                padding: 1rem;
                max-width: 100%;
                overflow-x: hidden;
            }

            .card-body {
                overflow-x: auto;
            }
        }

        /* Dropdown yang dipindahkan ke body */
        .dropdown-menu.dropdown-menu-detached {
            position: fixed !important;
            z-index: 1060 !important;
            min-width: 160px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15) !important;
        }

        .table {
            min-width: 100%;
            white-space: nowrap;
        }

        .table td,
        .table th {
            white-space: nowrap;
        }

        /* Responsive */
        @media (max-width: 768px) {

            .topbar .user-name,
            .topbar .user-role {
                display: none;
            }

            /* Mobile: Topbar full width */
            .topbar {
                left: 0 !important;
            }

            /* Mobile: Sidebar hidden by default, slide from left */
            .sidebar,
            .sidebar.toggled {
                position: fixed !important;
                left: 0;
                top: 0;
                bottom: 0;
                height: 100vh;
                width: 16rem !important;
                min-width: 16rem !important;
                max-width: 80vw !important;
                transform: translateX(-100%);
                transition: transform 0.25s ease;
                z-index: 1050;
                box-shadow: none;
                overflow-y: auto !important;
                overflow-x: hidden !important;
            }

            /* Sidebar visible on mobile - full width menu */
            .sidebar.mobile-show,
            .sidebar.toggled.mobile-show {
                transform: translateX(0) !important;
                box-shadow: 4px 0 20px rgba(0, 0, 0, 0.15);
            }

            /* Reset toggled styles on mobile */
            .sidebar.mobile-show .nav-item .nav-link,
            .sidebar.toggled.mobile-show .nav-item .nav-link {
                padding: 0.75rem 1rem !important;
                justify-content: flex-start !important;
                text-align: left !important;
                width: 100% !important;
            }

            .sidebar.mobile-show .nav-item .nav-link span,
            .sidebar.toggled.mobile-show .nav-item .nav-link span {
                display: inline !important;
                visibility: visible !important;
                opacity: 1 !important;
                width: auto !important;
                height: auto !important;
                font-size: 0.875rem !important;
            }

            .sidebar.mobile-show .nav-item .nav-link i,
            .sidebar.toggled.mobile-show .nav-item .nav-link i {
                margin-right: 0.75rem !important;
                width: 20px !important;
            }

            .sidebar.mobile-show .sidebar-heading,
            .sidebar.toggled.mobile-show .sidebar-heading {
                display: block !important;
                text-align: left !important;
                padding: 1rem 1rem 0.5rem !important;
            }

            .sidebar.mobile-show .sidebar-brand,
            .sidebar.toggled.mobile-show .sidebar-brand {
                padding: 1rem !important;
                justify-content: flex-start !important;
            }

            /* Overlay when sidebar open */
            .sidebar-overlay {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(0, 0, 0, 0.5);
                z-index: 1040;
            }

            .sidebar-overlay.show {
                display: block;
            }

            /* Content wrapper full width on mobile */
            #content-wrapper {
                width: 100% !important;
                margin-left: 0 !important;
            }

            #content {
                padding-top: 65px;
            }

            .container-fluid {
                padding: 1rem;
            }

            /* Toggle button style */
            #sidebarToggleTop {
                background: var(--sidebar-hover);
                width: 40px;
                height: 40px;
                display: flex !important;
                align-items: center;
                justify-content: center;
                border-radius: 8px !important;
                margin-right: 0.5rem;
                border: none !important;
            }

            #sidebarToggleTop i {
                color: var(--sidebar-active);
                font-size: 1.1rem;
            }

            #sidebarToggleTop:hover {
                background: var(--sidebar-active);
            }

            #sidebarToggleTop:hover i {
                color: #fff;
            }

            /* Hide desktop toggle on mobile */
            #sidebarToggle {
                display: none !important;
            }
        }

        /* Desktop: ensure no horizontal scroll */
        @media (min-width: 769px) {
            #sidebarToggleTop {
                display: none !important;
            }

            .sidebar-overlay {
                display: none !important;
            }
        }

        /* Fix DataTables overflow */
        .dataTables_wrapper {
            overflow-x: auto;
        }

        /* ========== MOBILE FRIENDLY PRODUCT TABLE ========== */
        .mobile-product-cards {
            display: none;
        }

        .product-card-mobile {
            background: #fff;
            border: 1px solid var(--border-color);
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 0.75rem;
            position: relative;
        }

        .product-card-mobile .card-header-mobile {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 0.75rem;
            padding-bottom: 0.75rem;
            border-bottom: 1px solid var(--border-color);
        }

        .product-card-mobile .product-name {
            font-weight: 600;
            color: var(--text-primary);
            font-size: 0.9rem;
            flex: 1;
            padding-right: 0.5rem;
        }

        .product-card-mobile .remove-btn-mobile {
            flex-shrink: 0;
        }

        .product-card-mobile .card-body-mobile {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.75rem;
        }

        .product-card-mobile .field-group {
            display: flex;
            flex-direction: column;
        }

        .product-card-mobile .field-group.full-width {
            grid-column: span 2;
        }

        .product-card-mobile .field-label {
            font-size: 0.7rem;
            color: var(--text-muted);
            text-transform: uppercase;
            font-weight: 600;
            margin-bottom: 0.25rem;
        }

        .product-card-mobile .field-value {
            font-size: 0.875rem;
            color: var(--text-primary);
        }

        .product-card-mobile .field-group input,
        .product-card-mobile .field-group select {
            font-size: 0.875rem;
        }

        .product-card-mobile .total-row {
            margin-top: 0.75rem;
            padding-top: 0.75rem;
            border-top: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .product-card-mobile .total-label {
            font-weight: 600;
            color: var(--text-secondary);
        }

        .product-card-mobile .total-value {
            font-weight: 700;
            color: var(--sidebar-active);
            font-size: 1rem;
        }

        /* Show Detail (untuk halaman show) */
        .show-product-card {
            background: var(--bg-light);
            border: 1px solid var(--border-color);
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 0.75rem;
        }

        .show-product-card .item-name {
            font-weight: 600;
            color: var(--text-primary);
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }

        .show-product-card .item-code {
            font-size: 0.75rem;
            color: var(--text-muted);
        }

        .show-product-card .item-desc {
            font-size: 0.8rem;
            color: var(--text-secondary);
            margin-bottom: 0.75rem;
        }

        .show-product-card .item-details {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 0.5rem;
            font-size: 0.8rem;
        }

        .show-product-card .detail-item {
            text-align: center;
        }

        .show-product-card .detail-item .label {
            color: var(--text-muted);
            font-size: 0.7rem;
            text-transform: uppercase;
        }

        .show-product-card .detail-item .value {
            font-weight: 600;
            color: var(--text-primary);
        }

        .show-product-card .item-total {
            margin-top: 0.75rem;
            padding-top: 0.75rem;
            border-top: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .show-product-card .item-total .total-value {
            font-weight: 700;
            color: var(--sidebar-active);
            font-size: 1rem;
        }

        /* MOBILE: Hide table, show cards */
        @media (max-width: 768px) {
            .desktop-product-table {
                display: none !important;
            }

            .mobile-product-cards {
                display: block !important;
            }

            .product-card-mobile .card-body-mobile {
                grid-template-columns: 1fr 1fr;
            }

            /* Adjust form layout on mobile */
            .row>[class*='col-md-'] {
                margin-bottom: 0.5rem;
            }

            /* Make page header stack on mobile dengan spacing */
            .page-header-mobile {
                flex-direction: column !important;
                align-items: stretch !important;
                gap: 0.75rem !important;
            }

            .page-header-mobile > h1,
            .page-header-mobile > .h3 {
                margin-bottom: 0 !important;
                font-size: 1.25rem !important;
            }

            .page-header-mobile > a.btn,
            .page-header-mobile > div {
                align-self: flex-start;
            }

            /* Show page action buttons - wrap dan spacing */
            .show-action-buttons {
                display: flex !important;
                flex-wrap: wrap !important;
                gap: 0.35rem !important;
                margin-top: 0.5rem;
            }

            .show-action-buttons .btn {
                font-size: 0.7rem !important;
                padding: 0.3rem 0.5rem !important;
                white-space: nowrap;
            }

            .show-action-buttons form.d-inline {
                display: inline-flex !important;
            }
        }

        @media (max-width: 576px) {
            .product-card-mobile .card-body-mobile {
                grid-template-columns: 1fr;
            }

            .product-card-mobile .field-group.full-width {
                grid-column: span 1;
            }

            .show-product-card .item-details {
                grid-template-columns: repeat(2, 1fr);
            }

            /* Extra small mobile - buttons even smaller */
            .show-action-buttons .btn {
                font-size: 0.65rem !important;
                padding: 0.25rem 0.4rem !important;
            }
        }
    </style>
</head>

<body id="page-top">
    @guest
        <div class="container">
            @yield('content')
        </div>
    @endguest

    @auth
        <div id="wrapper">
            <!-- Sidebar Overlay (Mobile) -->
            <div class="sidebar-overlay" id="sidebarOverlay"></div>

            <!-- Sidebar -->
            <ul class="navbar-nav sidebar accordion" id="accordionSidebar">
                <a class="sidebar-brand d-flex align-items-center justify-content-center" href="{{ route('dashboard') }}">
                    <div class="sidebar-brand-icon">
                        <img src="{{ asset('assets/img/logoHE11.png') }}" alt="Logo" style="height: 36px;">
                    </div>
                </a>

                <div class="sidebar-heading">Menu Utama</div>

                <li class="nav-item {{ Route::is('dashboard') ? 'active' : '' }}">
                    <a class="nav-link" href="{{ route('dashboard') }}" title="Dashboard">
                        <i class="fas fa-home"></i>
                        <span>Dashboard</span>
                    </a>
                </li>

                <hr class="sidebar-divider">
                <div class="sidebar-heading">Transaksi</div>

                <li class="nav-item {{ Route::is('penjualan.*') ? 'active' : '' }}">
                    <a class="nav-link" href="{{ route('penjualan.index') }}" title="Penjualan">
                        <i class="fas fa-file-invoice-dollar"></i>
                        <span>Penjualan</span>
                    </a>
                </li>
                <li class="nav-item {{ Route::is('pembelian.*') ? 'active' : '' }}">
                    <a class="nav-link" href="{{ route('pembelian.index') }}" title="Pembelian">
                        <i class="fas fa-shopping-bag"></i>
                        <span>Pembelian</span>
                    </a>
                </li>
                <li class="nav-item {{ Route::is('biaya.*') ? 'active' : '' }}">
                    <a class="nav-link" href="{{ route('biaya.index') }}" title="Biaya">
                        <i class="fas fa-wallet"></i>
                        <span>Biaya</span>
                    </a>
                </li>
                <li class="nav-item {{ Route::is('pembayaran.*') ? 'active' : '' }}">
                    <a class="nav-link" href="{{ route('pembayaran.index') }}" title="Pembayaran">
                        <i class="fas fa-money-bill-wave"></i>
                        <span>Pembayaran</span>
                    </a>
                </li>
                <li class="nav-item {{ Route::is('penerimaan-barang.*') ? 'active' : '' }}">
                    <a class="nav-link" href="{{ route('penerimaan-barang.index') }}" title="Penerimaan Barang">
                        <i class="fas fa-truck-loading"></i>
                        <span>Penerimaan Barang</span>
                    </a>
                </li>
                <li class="nav-item {{ Route::is('kunjungan.*') ? 'active' : '' }}">
                    <a class="nav-link" href="{{ route('kunjungan.index') }}" title="Kunjungan">
                        <i class="fas fa-map-marker-alt"></i>
                        <span>Kunjungan</span>
                    </a>
                </li>

                @if(auth()->user()->role == 'super_admin')
                    <hr class="sidebar-divider">
                    <div class="sidebar-heading">Pengaturan</div>

                    <li class="nav-item {{ Route::is('users.*') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('users.index') }}" title="Pengguna">
                            <i class="fas fa-users"></i>
                            <span>Pengguna</span>
                        </a>
                    </li>
                    <li class="nav-item {{ Route::is('gudang.*') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('gudang.index') }}" title="Gudang">
                            <i class="fas fa-warehouse"></i>
                            <span>Gudang</span>
                        </a>
                    </li>
                    <li class="nav-item {{ Route::is('produk.*') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('produk.index') }}" title="Produk">
                            <i class="fas fa-box"></i>
                            <span>Produk</span>
                        </a>
                    </li>
                @endif

                @if(in_array(auth()->user()->role, ['admin', 'super_admin', 'spectator']))
                    @if(in_array(auth()->user()->role, ['admin', 'spectator']))
                        <hr class="sidebar-divider">
                        <div class="sidebar-heading">Master Data</div>
                    @endif

                    <li class="nav-item {{ Route::is('kontak.*') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('kontak.index') }}" title="Kontak">
                            <i class="fas fa-address-card"></i>
                            <span>Kontak</span>
                        </a>
                    </li>
                    <li class="nav-item {{ Route::is('stok.*') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('stok.index') }}" title="Stok Gudang">
                            <i class="fas fa-boxes"></i>
                            <span>Stok Gudang</span>
                        </a>
                    </li>
                @endif

                <hr class="sidebar-divider d-none d-md-block">
                <div class="text-center d-none d-md-inline">
                    <button class="rounded-circle border-0" id="sidebarToggle"></button>
                </div>
            </ul>
            <!-- End Sidebar -->

            <div id="content-wrapper" class="d-flex flex-column">
                <div id="content">
                    <!-- Topbar -->
                    <nav class="navbar navbar-expand navbar-light topbar static-top">
                        <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                            <i class="fa fa-bars"></i>
                        </button>

                        <ul class="navbar-nav ml-auto">
                            <!-- Notification Bell -->
                            @if(in_array(Auth::user()->role, ['super_admin', 'admin']))
                                <li class="nav-item dropdown no-arrow notification-bell">
                                    <a class="nav-link dropdown-toggle" href="#" id="alertsDropdown" role="button"
                                        data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <i class="fas fa-bell"></i>
                                        @if(isset($totalPending) && $totalPending > 0)
                                            <span class="badge-counter">{{ $totalPending > 99 ? '99+' : $totalPending }}</span>
                                        @endif
                                    </a>
                                    <div class="dropdown-menu dropdown-menu-right notification-dropdown"
                                        aria-labelledby="alertsDropdown">
                                        <div class="dropdown-header">
                                            <span>Menunggu Persetujuan</span>
                                            @if(isset($totalPending) && $totalPending > 0)
                                                <span class="badge badge-danger">{{ $totalPending }}</span>
                                            @endif
                                        </div>
                                        <div class="notification-list">
                                            @if(isset($pendingNotifications) && $pendingNotifications->count() > 0)
                                                @foreach($pendingNotifications as $notif)
                                                    <a href="{{ $notif['url'] }}" class="notification-item">
                                                        <div class="notification-icon bg-{{ $notif['color'] }}">
                                                            <i class="fas {{ $notif['icon'] }}"></i>
                                                        </div>
                                                        <div class="notification-content">
                                                            <div class="notification-title">{{ $notif['title'] }}</div>
                                                            <div class="notification-subtitle">{{ $notif['subtitle'] }}</div>
                                                        </div>
                                                        <div class="notification-meta">
                                                            <div class="notification-amount">Rp
                                                                {{ number_format($notif['amount'] ?? 0, 0, ',', '.') }}
                                                            </div>
                                                            <div class="notification-time">
                                                                {{ $notif['time']->diffForHumans(null, true, true) }}
                                                            </div>
                                                        </div>
                                                    </a>
                                                @endforeach
                                            @else
                                                <div class="empty-notification">
                                                    <i class="fas fa-check-circle d-block"></i>
                                                    <span>Tidak ada transaksi pending</span>
                                                </div>
                                            @endif
                                        </div>
                                        @if(isset($totalPending) && $totalPending > 0)
                                            <div class="dropdown-footer">
                                                <a href="{{ route('dashboard') }}">Lihat Semua Transaksi</a>
                                            </div>
                                        @endif
                                    </div>
                                </li>
                                <li class="divider-vertical d-none d-sm-block"></li>
                            @endif

                            <!-- User Dropdown -->
                            <li class="nav-item dropdown no-arrow">
                                <a class="nav-link dropdown-toggle user-info" href="#" id="userDropdown" role="button"
                                    data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <div class="d-none d-lg-block text-right mr-2">
                                        <div class="user-name">{{ Auth::user()->name }}</div>
                                        <div class="user-role">{{ ucfirst(str_replace('_', ' ', Auth::user()->role)) }}
                                        </div>
                                    </div>
                                    <img class="img-profile rounded-circle"
                                        src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&background=3b82f6&color=fff&size=128">
                                </a>
                                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="userDropdown">
                                    {{-- Profile untuk Admin / Spectator --}}
                                    @if(in_array(Auth::user()->role, ['admin', 'spectator']))
                                        <div class="dropdown-divider"></div>
                                        <h6 class="dropdown-header">{{ Auth::user()->name }}</h6>
                                        
                                        {{-- Show current gudang --}}
                                        @php
                                            $currentGudang = Auth::user()->getCurrentGudang();
                                            // Get gudangs based on role
                                            if (Auth::user()->role === 'admin') {
                                                $userGudangs = Auth::user()->gudangs()->get();
                                            } else {
                                                $userGudangs = Auth::user()->spectatorGudangs()->get();
                                            }
                                        @endphp
                                        
                                        @if($userGudangs->count() > 1)
                                            <div class="dropdown-divider"></div>
                                            <form method="POST" action="{{ route('switch-gudang') }}" id="switchGudangForm">
                                                @csrf
                                                <div class="px-3 py-2">
                                                    <small class="text-muted d-block mb-2">
                                                        <i class="fas fa-warehouse mr-1"></i> <strong>Pilih Gudang Aktif</strong>
                                                    </small>
                                                    <select name="gudang_id" class="custom-select custom-select-sm" onchange="document.getElementById('switchGudangForm').submit();">
                                                        @foreach($userGudangs as $gudang)
                                                            <option value="{{ $gudang->id }}" 
                                                                {{ $currentGudang && $currentGudang->id === $gudang->id ? 'selected' : '' }}>
                                                                {{ $gudang->nama_gudang }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </form>
                                        @elseif($currentGudang)
                                            <div class="dropdown-divider"></div>
                                            <div class="px-3 py-2">
                                                <small class="text-muted">
                                                    <i class="fas fa-warehouse mr-1"></i> <strong>{{ $currentGudang->nama_gudang }}</strong>
                                                </small>
                                            </div>
                                        @endif
                                        <div class="dropdown-divider"></div>
                                    @endif

                                    <a class="dropdown-item" href="#" data-toggle="modal" data-target="#logoutModal">
                                        <i class="fas fa-sign-out-alt mr-2"></i>
                                        Keluar
                                    </a>
                                </div>
                            </li>
                        </ul>
                    </nav>
                    <!-- End Topbar -->

                    <!-- Content -->
                    <div class="container-fluid">
                        @yield('content')
                    </div>
                </div>

                <!-- Footer -->
                <footer class="sticky-footer">
                    <div class="container my-auto">
                        <div class="copyright text-center my-auto">
                            &copy; {{ date('Y') }} Hibiscus Efsya. All rights reserved.
                        </div>
                    </div>
                </footer>
            </div>
        </div>

        <!-- Logout Modal -->
        <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title"><i class="fas fa-sign-out-alt mr-2"></i>Keluar dari Sistem</h5>
                        <button class="close text-white" type="button" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p>Apakah Anda yakin ingin <strong>keluar</strong> dari sistem?</p>
                        <p class="text-muted mb-0"><small>Anda perlu login kembali untuk mengakses sistem.</small></p>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-secondary" type="button" data-dismiss="modal">Batal</button>
                        <a class="btn btn-primary" href="#"
                            onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            <i class="fas fa-sign-out-alt mr-1"></i> Ya, Keluar
                        </a>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">@csrf
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endauth

    <script src="{{ asset('template/vendor/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('template/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('template/vendor/jquery-easing/jquery.easing.min.js') }}"></script>
    <script src="{{ asset('template/js/sb-admin-2.min.js') }}"></script>
    {{-- Select2 JS --}}
    <script src="{{ asset('assets/bundle/select2/dist/js/select2.min.js') }}"></script>

    {{-- Custom Mobile Sidebar Toggle --}}
    <script>
        $(document).ready(function () {
            var sidebar = $('.sidebar');
            var overlay = $('#sidebarOverlay');
            var toggleBtn = $('#sidebarToggleTop');

            // Mobile sidebar toggle
            toggleBtn.off('click').on('click', function (e) {
                e.preventDefault();
                e.stopPropagation();

                sidebar.toggleClass('mobile-show');
                overlay.toggleClass('show');

                // Change icon based on state
                var icon = $(this).find('i');
                if (sidebar.hasClass('mobile-show')) {
                    icon.removeClass('fa-bars').addClass('fa-times');
                } else {
                    icon.removeClass('fa-times').addClass('fa-bars');
                }
            });

            // Close sidebar when clicking overlay
            overlay.on('click', function () {
                sidebar.removeClass('mobile-show');
                overlay.removeClass('show');
                toggleBtn.find('i').removeClass('fa-times').addClass('fa-bars');
            });

            // Close sidebar when clicking a link (mobile)
            if ($(window).width() <= 768) {
                sidebar.find('.nav-link').on('click', function () {
                    if (!$(this).attr('href').includes('#')) {
                        sidebar.removeClass('mobile-show');
                        overlay.removeClass('show');
                        toggleBtn.find('i').removeClass('fa-times').addClass('fa-bars');
                    }
                });
            }

            // ========== MOBILE INPUT FOCUS FIX ==========
            // Mencegah keyboard tertutup saat mengetik di mobile
            if ('ontouchstart' in window || navigator.maxTouchPoints > 0) {
                // Prevent scroll when input is focused
                var inputSelectors = 'input[type="text"], input[type="email"], input[type="password"], input[type="number"], input[type="tel"], input[type="search"], input[type="url"], textarea';
                
                $(document).on('focus', inputSelectors, function(e) {
                    var $input = $(this);
                    var $scrollParent = $input.closest('.table-responsive, .card-body, .modal-body');
                    
                    // Store scroll position
                    if ($scrollParent.length) {
                        $scrollParent.data('scroll-left', $scrollParent.scrollLeft());
                    }
                    
                    // Slight delay to ensure keyboard is open
                    setTimeout(function() {
                        // Scroll input into view if needed
                        var rect = $input[0].getBoundingClientRect();
                        var viewportHeight = window.innerHeight;
                        
                        // If input is below half of viewport, scroll to bring it up
                        if (rect.top > viewportHeight * 0.5) {
                            $input[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
                        }
                    }, 300);
                });

                // Prevent touchmove from stealing focus
                $(document).on('touchmove', function(e) {
                    var $focused = $(':focus');
                    if ($focused.is(inputSelectors)) {
                        // Allow scroll only within the input's parent container
                        var $target = $(e.target);
                        if (!$target.closest('.table-responsive, .card-body, .modal-body').length) {
                            // Don't prevent default - let the browser handle it
                        }
                    }
                });

                // Re-focus input if accidentally blurred by touch
                var lastFocusedInput = null;
                $(document).on('focus', inputSelectors, function() {
                    lastFocusedInput = this;
                });

                $(document).on('blur', inputSelectors, function(e) {
                    var $this = $(this);
                    // Small delay to check if focus moved to another input
                    setTimeout(function() {
                        var $newFocus = $(':focus');
                        // If no new focus and the blur was due to scroll, refocus
                        if (!$newFocus.length && lastFocusedInput === e.target) {
                            // Check if input still exists and is visible
                            if ($this.is(':visible') && !$this.prop('disabled')) {
                                // Don't auto-refocus as it can cause issues
                                // Just ensure the input is still accessible
                            }
                        }
                    }, 100);
                });
            }

            // ========== SELECT2 MOBILE FIX ==========
            // Fix Select2 search input di mobile agar keyboard tidak tertutup
            if (typeof $.fn.select2 !== 'undefined') {
                // Override Select2 default options for mobile
                $.fn.select2.defaults.set('dropdownAutoWidth', true);
                
                // Fix Select2 search field on mobile
                $(document).on('select2:open', function() {
                    var searchField = document.querySelector('.select2-container--open .select2-search__field');
                    if (searchField) {
                        // Set font size to prevent zoom
                        searchField.style.fontSize = '16px';
                        
                        // Small delay to ensure proper focus
                        setTimeout(function() {
                            searchField.focus();
                        }, 100);
                    }
                });
            }

            // ========== FIX DROPDOWN DI TABEL ==========
            // DISABLED: Ditangani per-halaman di masing-masing view (penjualan/index.blade.php, dll)
            // untuk menghindari konflik dan memastikan behavior yang konsisten
            /*
            // Buat backdrop untuk mobile
            var $backdrop = $('<div class="mobile-dropdown-backdrop"></div>');
            $('body').append($backdrop);
            
            $backdrop.on('click', function() {
                var $menu = $('body > .dropdown-menu-detached');
                if ($menu.length && $menu.data('original-parent')) {
                    var $parent = $menu.data('original-parent');
                    $menu.removeClass('show dropdown-menu-detached mobile-dropdown-active');
                    $menu.css({
                        'position': '',
                        'top': '',
                        'left': '',
                        'right': '',
                        'bottom': '',
                        'transform': '',
                        'width': ''
                    });
                    $parent.append($menu);
                    $parent.removeClass('show');
                    $parent.find('.dropdown-toggle').attr('aria-expanded', 'false');
                }
                $backdrop.removeClass('show');
            });

            $(document).on('show.bs.dropdown', '.table-responsive .action-dropdown, .table .action-dropdown', function(e) {
                // disabled
            });

            $(document).on('shown.bs.dropdown', '.table-responsive .action-dropdown, .table .action-dropdown', function() {
                // disabled
            });

            $(document).on('hide.bs.dropdown', '.table-responsive .action-dropdown, .table .action-dropdown', function() {
                // disabled
            });
            
            var touchStartY = 0;
            $(document).on('touchstart', '.dropdown-menu.mobile-dropdown-active', function(e) {
                // disabled
            });
            
            $(document).on('touchmove', '.dropdown-menu.mobile-dropdown-active', function(e) {
                // disabled
            });
            */
        });
    </script>

    @stack('scripts')

    @yield('modals')
</body>

</html>