<!DOCTYPE html>
<html lang="en" dir="ltr" data-nav-layout="vertical" data-theme-mode="dark" data-header-styles="dark" data-menu-styles="dark" data-toggled="close">

<head>

  <!-- Meta Data -->
  <meta charset="UTF-8">
  <meta name='viewport' content='width=device-width, initial-scale=1.0, user-scalable=no'>
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title> @yield('title') </title>

  <!-- Favicon -->
  <link rel="icon" href="/assets/images/brand-logos/favicon.ico" type="image/x-icon">

  <!-- Choices JS -->
  <script src="/assets/libs/choices.js/public/assets/scripts/choices.min.js"></script>

  <!-- Main Theme Js -->
  <script src="/assets/js/main.js"></script>

  <!-- Bootstrap Css -->
  <link id="style" href="/assets/libs/bootstrap/css/bootstrap.min.css" rel="stylesheet">

  <!-- Style Css -->
  <link href="/assets/css/styles.min.css" rel="stylesheet">
  <link rel="stylesheet" href="/assets/css/nova-theme.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <script>
    document.documentElement.setAttribute('data-theme-mode', 'dark');
    try { localStorage.setItem('ynexdarktheme', 'true'); } catch(e) {}
  </script>
  <style>
    body, .page.login-page {
      background: #080B18 !important;
      min-height: 100vh;
    }
    .container-login100 {
      background: transparent !important;
    }
    .wrap-login100, .card.wrap-login100 {
      background: rgba(255,255,255,0.05) !important;
      border: 1px solid rgba(139,92,246,0.25) !important;
      border-radius: 20px !important;
      backdrop-filter: blur(16px);
      box-shadow: 0 16px 48px rgba(0,0,0,0.4), 0 0 0 1px rgba(139,92,246,0.1);
    }
    .login100-form-title {
      color: #F8FAFC !important;
      font-weight: 700 !important;
    }
    .input100, .form-control.input100 {
      background: rgba(16,21,42,0.8) !important;
      border: 1px solid rgba(255,255,255,0.1) !important;
      color: #F8FAFC !important;
      border-radius: 10px !important;
    }
    .login100-form-btn, .btn-primary.login100-form-btn {
      background: linear-gradient(135deg, #8B5CF6, #2563EB) !important;
      border: none !important;
      border-radius: 10px !important;
      font-weight: 700 !important;
      box-shadow: 0 4px 18px rgba(139,92,246,0.4);
    }
    .text-dark { color: #94A3B8 !important; }
    .card-footer { background: transparent !important; border-color: rgba(255,255,255,0.08) !important; }
  </style>

  <!-- Icons Css -->
  <link href="/assets/css/icons.css" rel="stylesheet">

  <!-- Node Waves Css -->
  <link href="/assets/libs/node-waves/waves.min.css" rel="stylesheet">

  <!-- Simplebar Css -->
  <link href="/assets/libs/simplebar/simplebar.min.css" rel="stylesheet">

  <!-- Color Picker Css -->
  <link rel="stylesheet" href="/assets/libs/flatpickr/flatpickr.min.css">
  <link rel="stylesheet" href="/assets/libs/@simonwep/pickr/themes/nano.min.css">

  <!-- Choices Css -->
  <link rel="stylesheet" href="/assets/libs/choices.js/public/assets/styles/choices.min.css">

  <link rel="preconnect" href="https://fonts.gstatic.com">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Archivo:wght@600;700;800&display=swap">

  <script>
    window.webData = @json([
        'csrfToken' => csrf_token(),
    ]);
    window.userData = @json(auth()->user());
  </script>

  @vite(['resources/css/app.css'])

  <style>
    :root {
      --primary: {{ setting('color_primary', '#240750') }};
      --primary-rgb: {{ hex2rgb(setting('color_primary', '#240750')) }};
      --primary-hover: {{ setting('color_primary_hover', '#240750') }};
    }

    body {
      font-family: 'Archivo', sans-serif;
      letter-spacing: 0.5px;
    }

    .login-img {
      background-image: url('{{ theme_setting('auth_bg', '/images/bg-auth.jpg') }}');
      background-size: cover;
      background-position: center;
      background-repeat: no-repeat;
      background-attachment: fixed;
    }
  </style>

  @yield('css')
  @yield('styles')
</head>

<body class="login-img">

  <!-- Loader -->
  <div id="loader">
    <img src="/assets/images/media/loader.svg" alt="">
  </div>
  <!-- Loader -->

  <div class="page">
    @yield('content')
  </div>

  <!-- Jquery Js -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
  <!-- extra js-->
  <script src="https://unpkg.com/clipboard@2/dist/clipboard.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/11.7.27/sweetalert2.min.js"></script>

  @vite(['resources/js/app.js', 'resources/js/functions.js'])

  @yield('scripts')

</body>

</html>
