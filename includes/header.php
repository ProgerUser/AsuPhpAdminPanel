<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Абхазские Словари Онлайн</title>
    <link rel="shortcut icon" href="ico/Dictionary.ico" type="image/x-icon">
    <!-- Bootstrap Core CSS -->
    <link rel="stylesheet" href="assets/css/bootstrap.min.css"/>

    <!-- MetisMenu CSS -->
    <link href="assets/js/metisMenu/metisMenu.min.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link href="assets/css/sb-admin-2.css" rel="stylesheet">
    <link href="vki/keyboard.css" rel="stylesheet">
    <link rel="stylesheet" href="css/jquery-ui.min.css"/>

<!--    <link rel="stylesheet" type="text/css" href="css/dict.css">
    <link rel="stylesheet" type="text/css" href="css/main.css">-->

    <!-- Custom Fonts -->
    <link href="assets/fonts/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css">

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
    <script src="assets/js/jquery.min.js" type="text/javascript"></script>
    <script src="tinymce/tinymce.min.js" referrerpolicy="origin"></script>
    <script src="js/jquery-ui.min.js" type="text/javascript"></script>

    <script>tinymce.init({
            selector: 'textarea',
            language: 'ru',
            menubar: true,
            readonly: 1,
            toolbar: true,
            statusbar: true,
        });
        tinymce.activeEditor.setMode('design');
    </script>
    
    <script>
        // Автоматическое выделение строки при возврате с редактирования и применение темы
        document.addEventListener('DOMContentLoaded', function() {
            // Применяем тему из localStorage
            try {
                var savedTheme = localStorage.getItem('appTheme');
                if (savedTheme === 'dark') {
                    document.body.classList.add('theme-dark');
                }
                var el = document.getElementById('themeToggleText');
                if (el) { el.textContent = (savedTheme === 'dark') ? 'Светлая тема' : 'Тёмная тема'; }
            } catch (e) {}
            const hash = window.location.hash;
            if (hash && hash.startsWith('#row-')) {
                const rowId = hash.substring(1); // убираем #
                const row = document.getElementById(rowId);
                if (row) {
                    // Добавляем класс выделения
                    row.classList.add('row-highlighted');
                    
                    // Плавно прокручиваем к строке
                    row.scrollIntoView({ 
                        behavior: 'smooth', 
                        block: 'center' 
                    });
                    
                    // Оставляем подсветку подольше, чтобы было заметно
                    setTimeout(function() {
                        row.classList.remove('row-highlighted');
                    }, 20000);
                }
            }
        });

        // Переключение темы
        function toggleTheme() {
            var isDark = document.body.classList.toggle('theme-dark');
            try { localStorage.setItem('appTheme', isDark ? 'dark' : 'light'); } catch (e) {}
            var el = document.getElementById('themeToggleText');
            if (el) { el.textContent = isDark ? 'Светлая тема' : 'Тёмная тема'; }
        }
    </script>
    <style>
        @font-face {
            font-family: PT_Sans-Web-Regular; /* Гарнитура шрифта */
            src: url(fonts/PT_Sans-Web-Regular.ttf); /* Путь к файлу со шрифтом */
        }

        body {
            font-family: PT_Sans-Web-Regular;
        }

        .ui-autocomplete.ui-widget {
            font-family: PT_Sans-Web-Regular;
        }

        /* Поверх навигации/панелей для подсказок */
        .ui-autocomplete {
            z-index: 3000 !important;
            max-height: 260px;
            overflow-y: auto;
            border-radius: 6px;
            padding: 4px 0;
        }
        .ui-autocomplete .sugg-li { padding: 6px 12px; border-bottom: 1px solid #e6eaf0; }
        .ui-autocomplete .sugg-li:last-child { border-bottom: none; }
        .ui-autocomplete .sugg-item { line-height: 1.2; }
        .ui-autocomplete .sugg-word { font-weight: 600; color: #111827; }
        .ui-autocomplete .sugg-dict { color: #475569; font-size: 12px; }
        .ui-autocomplete .sugg-author { color: #64748b; font-size: 12px; }
        }

        /* Выделение изменённой строки: мягкое, но заметное */
        .row-highlighted {
            background-color: #fff3cd !important; /* тёплый мягкий жёлтый */
            border-left: 6px solid #f59e0b !important;
            box-shadow: inset 0 0 0 3px rgba(245, 158, 11, 0.25);
            transition: background-color 0.4s ease-in-out, box-shadow 0.4s ease-in-out;
            animation: highlightPulse 6s ease-in-out 1;
        }
        /* На таблицах у ячеек может быть свой фон — дублируем на td */
        tr.row-highlighted > td {
            background-color: #fff3cd !important;
            border-top: 2px solid #f59e0b !important;
            border-bottom: 2px solid #f59e0b !important;
        }
        tr.row-highlighted > td:first-child { border-left: 2px solid #f59e0b !important; }
        tr.row-highlighted > td:last-child { border-right: 2px solid #f59e0b !important; }

        @keyframes highlightPulse {
            0% { background-color: #ffe8a3; box-shadow: inset 0 0 0 4px rgba(245,158,11,0.35); }
            50% { background-color: #fff3cd; box-shadow: inset 0 0 0 2px rgba(245,158,11,0.2); }
            100% { background-color: transparent; box-shadow: inset 0 0 0 0 rgba(245,158,11,0); }
        }

        /* Современное оформление таблиц и форм */
        .table {
            border-radius: 6px;
            overflow: hidden;
            background-color: #fff;
        }
        .table > thead > tr > th {
            background: #f7f9fc;
            border-bottom: 1px solid #e6eaf0;
            color: #334155;
            font-weight: 600;
        }
        .table > tbody > tr:hover {
            background-color: #fafcff;
        }

        .well.filter-form {
            background: #f7f9fc;
            border: 1px solid #e6eaf0;
            border-radius: 8px;
        }
        .form-control {
            border-radius: 6px;
            border-color: #dfe3ea;
            box-shadow: none;
        }
        .form-control:focus {
            border-color: #4f46e5;
            box-shadow: 0 0 0 3px rgba(79,70,229,0.15);
        }

        .btn-primary {
            background: #3b82f6;
            border-color: #3b82f6;
            border-radius: 6px;
        }
        .btn-primary:hover {
            background: #2563eb;
            border-color: #2563eb;
        }
        .btn-default {
            background: #ffffff;
            border-color: #dfe3ea;
            color: #334155;
            border-radius: 6px;
        }
        .btn-default:hover {
            background: #f7f9fc;
            border-color: #cfd6e0;
        }
        .btn-danger {
            background: #ef4444;
            border-color: #ef4444;
            border-radius: 6px;
        }
        .btn-danger:hover { background: #dc2626; border-color: #dc2626; }

        /* Пагинация */
        .pagination > li > a, .pagination > li > span {
            border-color: #dfe3ea;
            color: #334155;
        }
        .pagination > .active > a, .pagination > .active > span {
            background-color: #3b82f6;
            border-color: #3b82f6;
        }

        /* По умолчанию используем стандартные стили Bootstrap/SB Admin (светлая тема) */

        /* Тёмная тема */
        body.theme-dark { background-color: #0b1220; color: #e2e8f0; }
        body.theme-dark #page-wrapper { background-color: #0b1220; }
        .theme-dark .navbar-default { background-color: #0b1220; border-color: #0b1220; }
        .theme-dark .navbar-default .navbar-brand, .theme-dark .navbar-default .navbar-nav > li > a { color: #cbd5e1; }
        .theme-dark .navbar-default .navbar-nav > li > a:hover { color: #ffffff; }
        .theme-dark .navbar-default.sidebar { background-color: #0b1220; }
        .theme-dark .sidebar .nav > li > a { color: #94a3b8; }
        .theme-dark .sidebar .nav > li > a:hover, .theme-dark .sidebar .nav > li.active > a { background: #111827; color: #e2e8f0; }
        .theme-dark .well.filter-form { background: #0f172a; border-color: #1f2937; }
        .theme-dark .table { background: #0f172a; }
        .theme-dark .table > thead > tr > th { background: #101826; border-bottom-color: #1f2937; color: #e2e8f0; }
        .theme-dark .table > tbody > tr:hover { background: #0c1424; }
        .theme-dark .form-control { background: #111827; color: #e2e8f0; border-color: #243247; }
        .theme-dark .form-control:focus { border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99,102,241,0.2); }
        .theme-dark .btn-default { background: #0f172a; color: #e2e8f0; border-color: #243247; }
        .theme-dark .btn-default:hover { background: #0c1424; }
        .theme-dark .row-highlighted { background-color: #0c1424 !important; border-left-color: #60a5fa !important; }
        .theme-dark .btn-primary { background: #2563eb; border-color: #2563eb; }
        .theme-dark .btn-primary:hover { background: #1d4ed8; border-color: #1d4ed8; }
        .theme-dark .btn-danger { background: #dc2626; border-color: #dc2626; }
        .theme-dark .pagination > .active > a, .theme-dark .pagination > .active > span { background-color: #2563eb; border-color: #2563eb; }

    </style>
</head>

<body>

<div id="wrapper">

    <!-- Navigation -->
    <?php if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] == true): ?>
        <nav class="navbar navbar-default navbar-static-top" role="navigation" style="margin-bottom: 0">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand" href="">Администратор</a>
            </div>
            <!-- /.navbar-header -->

            <ul class="nav navbar-top-links navbar-right">
                <li>
                    <a href="#" onclick="toggleTheme(); return false;">
                        <i class="fa fa-adjust"></i> <span id="themeToggleText">Тёмная тема</span>
                    </a>
                </li>
                <!-- /.dropdown -->

                <!-- /.dropdown -->
                <li class="dropdown">
                    <a class="dropdown-toggle" data-toggle="dropdown" href="#">
                        <i class="fa fa-user fa-fw"></i> <i class="fa fa-caret-down"></i>
                    </a>
                    <ul class="dropdown-menu dropdown-user">
                        <li><a href="#"><i class="fa fa-user fa-fw"></i> Профиль пользователя</a>
                        </li>
                        <li><a href="#"><i class="fa fa-gear fa-fw"></i> Настройки</a>
                        </li>
                        <li class="divider"></li>
                        <li><a href="logout.php"><i class="fa fa-sign-out fa-fw"></i> Выход</a>
                        </li>
                    </ul>
                    <!-- /.dropdown-user -->
                </li>
                <!-- /.dropdown -->
            </ul>
            <!-- /.navbar-top-links -->

            <div class="navbar-default sidebar" role="navigation">
                <div class="sidebar-nav navbar-collapse">
                    <ul class="nav" id="side-menu">

                        <li>
                            <a href="index.php"><i class="fa fa-dashboard fa-fw"></i> Главная</a>
                        </li>

                        <li <?php echo (CURRENT_PAGE == "dictlist.php" || CURRENT_PAGE == "add_dictlist.php") ? 'class="active"' : ''; ?>>
                            <a href="#"><i class="fa fa-book fa-fw"></i> Список словарей<span
                                        class="fa arrow"></span></a>
                            <ul class="nav nav-second-level">
                                <li>
                                    <a href="dictlist.php"><i class="fa fa-list fa-fw"></i>Весь список</a>
                                </li>
                                <li>
                                    <a href="add_dictlist.php"><i class="fa fa-plus fa-fw"></i>Добавить строку</a>
                                </li>
                            </ul>
                        </li>
                        <li <?php echo (CURRENT_PAGE == "wordlist.php" || CURRENT_PAGE == "add_wordlist.php") ? 'class="active"' : ''; ?>>
                            <a href="#"><i class="fa fa-file-word-o fa-fw"></i> Список слов<span
                                        class="fa arrow"></span></a>
                            <ul class="nav nav-second-level">
                                <li>
                                    <a href="wordlist.php"><i class="fa fa-list fa-fw"></i>Весь список</a>
                                </li>
                                <li>
                                    <a href="add_wordlist.php"><i class="fa fa-plus fa-fw"></i>Добавить строку</a>
                                </li>
                            </ul>
                        </li>
                        <li>
                            <a href="admin_users.php"><i class="fa fa-users fa-fw"></i>Пользователи</a>
                        </li>
                        <li>
                            <a href="transfer_words_authors.php"><i class="fa fa-exchange fa-fw"></i>Импорт/Экспорт (слова/авторы)</a>
                        </li>
                        <li>
                            <a href="groups_list.php"><i class="fa fa-lock fa-fw"></i>Группы доступа</a>
                        </li>
                    </ul>
                </div>
                <!-- /.sidebar-collapse -->
            </div>
            <!-- /.navbar-static-side -->
        </nav>
    <?php else: ?>
        <nav class="navbar navbar-default navbar-static-top" role="navigation" style="margin-bottom: 0">
            <div class="navbar-header">
                <a class="navbar-brand">
                    <img src="ico/gerb.svg" width="50px" hei>
                </a>
            </div>
        </nav>
    <?php endif; ?>
    <!-- The End of the Header -->