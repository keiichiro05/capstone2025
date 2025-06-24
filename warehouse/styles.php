<?php
require_once('../konekdb.php');
$username = $_SESSION['username'];
$idpegawai = $_SESSION['idpegawai'];
?>
<style>
    :root {
        --primary: #4e73df;
        --success: #1cc88a;
        --info: #36b9cc;
        --warning: #f6c23e;
        --danger: #e74a3b;
        --dark: #5a5c69;
        --light: #f8f9fc;
    }
    
    body {
        font-family: 'Poppins', sans-serif;
        background-color: #f8f9fc;
        margin: 0;
        padding: 0;
    }
    .order_id-history-container {
                background: #fff;
                border_id-radius: 10px;
                box-shadow: 0 5px 20px rgba(0,0,0,0.1);
                overflow: hidden;
                margin-bottom: 30px;
            }

            .order_id-history-header {
                background: linear-gradient(135deg, #3498db, #2980b9);
                color: white;
                padding: 15px 20px;
                border_id-bottom: 1px solid rgba(255,255,255,0.2);
            }

            .order_id-history-header h3 {
                margin: 0;
                font-weight: 600;
                font-size: 18px;
                display: inline-block;
            }

            .filter-container {
                display: flex;
                align-items: center;
                justify-content: space-between;
                padding: 15px 20px;
                background-color: #f8f9fa;
                border_id-bottom: 1px solid #eee;
            }

            .filter-form {
                display: flex;
                align-items: center;
                gap: 10px;
            }

            .filter-form .form-control {
                min-width: 180px;
                border_id-radius: 4px;
                border_id: 1px solid #ddd;
                box-shadow: none;
            }

            .filter-form .btn {
                border_id-radius: 4px;
            }

            .order_id-history-table {
                width: 100%;
                border_id-collapse: collapse;
            }

            .order_id-history-table th {
                background-color: #2c3e50;
                color: white;
                font-weight: 600;
                padding: 12px 15px;
                text-align: left;
                position: sticky;
                top: 0;
            }

            .order_id-history-table td {
                padding: 12px 15px;
                border_id-bottom: 1px solid #eee;
                vertical-align: middle;
            }

            .order_id-history-table tr:nth-child(even) {
                background-color: #f9f9f9;
            }

            .order_id-history-table tr:hover {
                background-color: #f1f1f1;
            }

            /* Barcode Styles */
            .barcode-cell {
                text-align: center;
            }
            
            .barcode-container {
                display: inline-block;
                padding: 5px;
                background: white;
                border_id: 1px solid #ddd;
                border_id-radius: 4px;
                position: relative;
            }
            
            .barcode-img {
                height: 40px;
                width: auto;
                max-width: 150px;
                image-rendering: crisp-edges;
            }
            
            .barcode-text {
                font-size: 10px;
                font-family: monospace;
                margin-top: 3px;
                display: block;
            }

            /* Responsive Adjustments */
            @media (max-width: 768px) {
                .filter-container {
                    flex-direction: column;
                    align-items: flex-start;
                    gap: 10px;
                }
                
                .filter-form {
                    width: 100%;
                    flex-direction: column;
                }
                
                .filter-form .form-control,
                .filter-form .btn {
                    width: 100%;
                }
                
                .order_id-history-table {
                    display: block;
                    overflow-x: auto;
                }
            }

            /* Pagination Styles */
            .pagination-container {
                display: flex;
                justify-content: center;
                padding: 15px;
                background-color: #f8f9fa;
                border_id-top: 1px solid #eee;
            }

            .pagination {
                margin: 0;
            }

            .pagination > li > a,
            .pagination > li > span {
                color: #2c3e50;
                border_id: 1px solid #ddd;
                margin: 0 2px;
                border_id-radius: 4px !important;
            }

            .pagination > li.active > a,
            .pagination > li.active > span {
                background: linear-gradient(135deg, #3498db, #2980b9);
                border_id-color: #2980b9;
                color: white;
            }

            /* Empty State */
            .empty-state {
                padding: 40px 20px;
                text-align: center;
                color: #7f8c8d;
            }

            .empty-state i {
                font-size: 50px;
                margin-bottom: 20px;
                color: #bdc3c7;
            }

            .empty-state h4 {
                margin-bottom: 10px;
                color: #2c3e50;
            }
    
    /* Navbar Styles */
    .navbar {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        height: 60px;
        background: white;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        display: flex;
        align-items: center;
        padding: 0 20px;
        z-index: 1000;
    }
    
    .navbar-brand {
        font-size: 20px;
        font-weight: 600;
        color: var(--primary);
        text-decoration: none;
    }
    
    .navbar-right {
        margin-left: auto;
        display: flex;
        align-items: center;
    }
    
    .user-menu {
        display: flex;
        align-items: center;
    }
    
    .user-menu img {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        margin-right: 10px;
    }
    
    .user-name {
        margin-right: 15px;
        font-weight: 500;
    }
    
    .logout-btn {
        color: var(--danger);
        text-decoration: none;
        padding: 8px 15px;
        border-radius: 4px;
        transition: background-color 0.3s;
    }
    
    .logout-btn:hover {
        background-color: rgba(231, 74, 59, 0.1);
    }
    
    /* Sidebar Styles */
    .left-side {
        position: fixed;
        top: 60px;
        bottom: 0;
        width: 230px;
        overflow-y: auto;
        background: #222d32;
        transition: all 0.3s;
        z-index: 800;
    }
    
    .right-side {
        margin-left: 230px;
        padding: 20px;
        margin-top: 60px;
        min-height: calc(100vh - 60px);
        overflow-y: auto;
    }
    
    /* Dashboard Content Styles */
    .dashboard-header {
        background: linear-gradient(135deg, var(--primary) 0%, #224abe 100%);
        color: white;
        padding: 20px;
        margin-bottom: 20px;
        border-radius: 8px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    }
    
    .dashboard-header h1 {
        font-weight: 600;
        margin-bottom: 5px;
    }
    
    .dashboard-header h1 small {
        color: rgba(255,255,255,0.7);
        font-size: 16px;
        display: block;
        margin-top: 5px;
    }
    
    .header-date-time {
        font-size: 14px;
        opacity: 0.9;
    }
    
    /* Small Box Styles */
    .small-box {
        border-radius: 10px;
        box-shadow: 0 4px 10px rgba(0,0,0,0.08);
        transition: all 0.3s ease;
        border: none;
        overflow: hidden;
        margin-bottom: 20px;
    }
    
    /* Box Styles */
    .box {
        border-radius: 10px;
        box-shadow: 0 4px 10px rgba(0,0,0,0.08);
        border: none;
        margin-bottom: 20px;
        background: white;
    }
    
    /* Chart Styles */
    .chart-container {
        position: relative;
        height: 300px;
    }
    
    /* Responsive Styles */
    @media (max-width: 767px) {
        .left-side {
            transform: translateX(-100%);
        }
        
        .left-side.show {
            transform: translateX(0);
        }
        
        .right-side {
            margin-left: 0;
        }
        
        .navbar-toggle {
            display: block;
        }
    }

        
        /* Layout Fixes */
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f9fc;
            margin: 0;
            padding: 0;
        }
        
        .navbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: 60px;
            background: white;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            display: flex;
            align-items: center;
            padding: 0 20px;
            z-index: 1000;
        }
        
        .navbar-brand {
            font-size: 20px;
            font-weight: 600;
            color: var(--primary);
            text-decoration: none;
        }
        
        .navbar-right {
            margin-left: auto;
            display: flex;
            align-items: center;
        }
        
        .user-menu {
            display: flex;
            align-items: center;
        }
        
        .user-menu img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 10px;
        }
        
        .user-name {
            margin-right: 15px;
            font-weight: 500;
        }
        
        .logout-btn {
            color: var(--danger);
            text-decoration: none;
            padding: 8px 15px;
            border-radius: 4px;
            transition: background-color 0.3s;
        }
        
        .logout-btn:hover {
            background-color: rgba(231, 74, 59, 0.1);
        }
        
        .left-side {
            position: fixed;
            top: 60px;
            bottom: 0;
            width: 230px;
            overflow-y: auto;
            background: #222d32;
            transition: all 0.3s;
            z-index: 800;
        }
        
        .right-side {
            margin-left: 230px;
            padding: 20px;
            margin-top: 60px;
            min-height: calc(100vh - 60px);
            overflow-y: auto;
        }
        
        /* Responsive adjustments */
        @media (max-width: 767px) {
            .left-side {
                transform: translateX(-100%);
            }
            
            .left-side.show {
                transform: translateX(0);
            }
            
            .right-side {
                margin-left: 0;
            }
        }
        
        /* Dashboard Header */
        .dashboard-header {
            background: linear-gradient(135deg, var(--primary) 0%, #224abe 100%);
            color: white;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        
        .dashboard-header h1 {
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .dashboard-header h1 small {
            color: rgba(255,255,255,0.7);
            font-size: 16px;
            display: block;
            margin-top: 5px;
        }
        
        .header-date-time {
            font-size: 14px;
            opacity: 0.9;
        }
        
        .header-date-time i {
            margin-right: 5px;
        }
        
        /* Small Boxes */
        .small-box {
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            border: none;
            overflow: hidden;
            margin-bottom: 20px;
        }
        
        .small-box:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.15);
        }
        
        .small-box .inner {
            padding: 15px;
        }
        
        .small-box h3 {
            font-size: 28px;
            font-weight: 600;
            margin: 0 0 5px 0;
        }
        
        .small-box p {
            font-size: 15px;
            margin-bottom: 0;
        }
        
        .small-box .icon {
            font-size: 70px;
            position: absolute;
            right: 15px;
            top: 15px;
            transition: all 0.3s;
            opacity: 0.2;
        }
        
        .small-box:hover .icon {
            opacity: 0.3;
            transform: scale(1.1);
        }
        
        .small-box-footer {
            background: rgba(0,0,0,0.05);
            color: rgba(255,255,255,0.8);
            display: block;
            padding: 8px 0;
            text-align: center;
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .small-box-footer:hover {
            background: rgba(0,0,0,0.1);
            color: white;
        }
        
        /* Box Styling */
        .box {
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.08);
            border: none;
            margin-bottom: 20px;
            background: white;
        }
        
        .box-header {
            border-top-left-radius: 10px;
            border-top-right-radius: 10px;
            padding: 15px 20px;
            background-color: white;
            border-bottom: 1px solid rgba(0,0,0,0.05);
        }
        
        .box-header h3 {
            font-size: 18px;
            font-weight: 600;
            margin: 0;
            display: inline-block;
        }
        
        .box-header .box-tools {
            position: absolute;
            right: 20px;
            top: 15px;
        }
        
        .box-body {
            padding: 20px;
            background-color: white;
            border-bottom-left-radius: 10px;
            border-bottom-right-radius: 10px;
        }
        
        /* Chart Containers */
        .chart-container {
            position: relative;
            height: 300px;
        }
        
        /* Alert Items */
        .alert-item {
            border-left: 4px solid var(--danger);
            margin-bottom: 10px;
            border-radius: 6px;
            transition: all 0.3s;
            padding: 10px 15px;
        }
        
        .alert-item:hover {
            transform: translateX(5px);
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .stock-critical {
            background-color: #f8d7da;
            border-left-color: var(--danger);
        }
        
        .stock-warning {
            background-color: #fff3cd;
            border-left-color: var(--warning);
        }
        
        /* Products List */
        .products-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .products-list .item {
            padding: 10px 0;
            border-bottom: 1px solid rgba(0,0,0,0.05);
        }
        
        .products-list .item:last-child {
            border-bottom: none;
        }
        
        .product-title {
            font-weight: 500;
            display: block;
            margin-bottom: 5px;
        }
        
        .product-description {
            font-size: 13px;
            color: #6c757d;
        }
        
        /* Sidebar */
        .sidebar-menu > li > a {
            border-radius: 5px;
            margin: 5px 10px;
        }
        
        .sidebar-menu > li.active > a {
            background-color: var(--primary);
            color: white;
        }
        
        .sidebar-menu > li > a:hover {
            background-color: rgba(78, 115, 223, 0.1);
        }
        
        .user-panel {
            padding: 15px;
        }
        
        .skin-blue .sidebar-menu > li:hover > a, 
        .skin-blue .sidebar-menu > li.active > a {
            color: white;
            background: var(--primary);
            border-left-color: var(--primary);
        }
        
        /* Info Box */
        .info-box {
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 15px;
        }
        
        .info-box-icon {
            border-radius: 8px 0 0 8px;
            display: block;
            float: left;
            height: 90px;
            width: 90px;
            text-align: center;
            font-size: 45px;
            line-height: 90px;
            background: rgba(0,0,0,0.2);
        }
        
        .info-box-content {
            padding: 15px;
            margin-left: 90px;
        }
        
        .info-box-text {
            display: block;
            font-size: 16px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .info-box-number {
            display: block;
            font-size: 22px;
            font-weight: 600;
        }
        
        .progress-description {
            display: block;
            font-size: 12px;
            margin-top: 5px;
        }
        
        /* Mobile menu toggle button */
        .navbar-toggle {
            display: none;
            border: none;
            background: transparent;
            padding: 10px;
            margin-right: 15px;
        }
        
        .navbar-toggle .icon-bar {
            display: block;
            width: 22px;
            height: 2px;
            background-color: var(--dark);
            margin-bottom: 4px;
            border-radius: 1px;
        }
        
        @media (max-width: 767px) {
            .navbar-toggle {
                display: block;
            }
        }
    
</style>