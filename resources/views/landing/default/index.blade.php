@php
  $siteTitle = setting('title') ?: config('app.name', 'NovaSMM');
  $siteDescription = setting('description') ?: 'แพลตฟอร์ม SMM ที่ทันสมัย ราคาดี และซัพพอร์ต 24/7';
  $icons = [
    'spark' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3l1.9 5.1L19 10l-5.1 1.9L12 17l-1.9-5.1L5 10l5.1-1.9L12 3z"/><path d="M19 16l.8 2.2L22 19l-2.2.8L19 22l-.8-2.2L16 19l2.2-.8L19 16z"/></svg>',
    'bolt' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M13 2L4 14h7l-1 8 9-12h-7l1-8z"/></svg>',
    'shield' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="M9 12l2 2 4-4"/></svg>',
    'wallet' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20 12V8H6a2 2 0 010-4h12v4"/><path d="M4 6v12a2 2 0 002 2h14v-4"/><path d="M18 12a2 2 0 000 4h4v-4h-4z"/></svg>',
    'api' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M8 6H5a2 2 0 00-2 2v3h3M16 6h3a2 2 0 012 2v3h-3M8 18H5a2 2 0 01-2-2v-3h3M16 18h3a2 2 0 002-2v-3h-3"/><circle cx="12" cy="12" r="3"/></svg>',
    'headset' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 14v-2a8 8 0 0116 0v2"/><path d="M4 14h3a2 2 0 012 2v3a2 2 0 01-2 2H6a2 2 0 01-2-2v-5zM20 14h-3a2 2 0 00-2 2v3a2 2 0 002 2h1a2 2 0 002-2v-5z"/></svg>',
    'chart' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3v18h18"/><path d="M7 15l4-5 3 3 5-7"/></svg>',
    'check' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>',
    'arrow' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>',
    'menu' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M4 7h16M4 12h16M4 17h16"/></svg>',
  ];
@endphp
<!DOCTYPE html>
<html lang="th">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="theme-color" content="#f4f8fd">

  @hasSection('description')
    <meta name="description" content="@yield('description')">
  @else
    <meta name="description" content="{{ $siteDescription }}">
  @endif
  @hasSection('keywords')
    <meta name="keywords" content="@yield('keywords')">
  @else
    <meta name="keywords" content="{{ setting('keywords') }}">
  @endif
  <meta name="author" content="{{ setting('author') }}">
  <meta name="robots" content="index, follow">
  <meta name="googlebot" content="index, follow">
  <meta name="google" content="notranslate">
  <meta name="generator" content="{{ strtoupper($_SERVER['HTTP_HOST']) }}">

  <meta name="application-name" content="{{ $siteTitle }}">
  <meta property="og:image" content="{{ asset(setting('favicon')) }}">
  <meta property="og:image:secure_url" content="{{ asset(setting('favicon')) }}">
  <meta property="og:image:width" content="128">
  <meta property="og:image:height" content="128">
  <meta property="og:image:alt" content="{{ $siteTitle }}">
  <meta property="og:title" content="{{ $siteTitle }}">
  <meta property="og:site_name" content="{{ $siteTitle }}">
  <meta property="og:description" content="{{ $siteDescription }}">
  <meta property="og:url" content="{{ url()->current() }}">
  <meta property="og:type" content="website">

  @if(setting('favicon'))
    <link rel="shortcut icon" href="{{ asset(setting('favicon')) }}" type="image/x-icon">
  @endif

  @hasSection('postTitle')
    <title>@yield('postTitle')</title>
  @endif
  @hasSection('title')
    <title>@yield('title') - {{ $siteTitle }}</title>
  @else
    @hasSection('pageTitle')
      <title>@yield('pageTitle')</title>
    @else
      <title>{{ $siteTitle }}</title>
    @endif
  @endif

  <style>
    :root {
      --bg: #f4f8fd;
      --bg-soft: #eaf2fb;
      --surface: #ffffff;
      --surface-hover: #f8fbff;
      --border: #dbe7f3;
      --purple: #2563eb;
      --blue: #2563eb;
      --cyan: #0ea5e9;
      --text: #1e3a5f;
      --muted: #64748b;
      --gradient: linear-gradient(135deg, #2563eb 0%, #0ea5e9 100%);
      --shadow: 0 12px 32px rgba(37, 99, 235, .10);
    }

    *, *::before, *::after { box-sizing: border-box; }
    html { scroll-behavior: smooth; }
    body {
      margin: 0;
      background: var(--bg);
      color: var(--text);
      font-family: Inter, system-ui, -apple-system, "Segoe UI", Roboto, "Noto Sans Thai", sans-serif;
      -webkit-font-smoothing: antialiased;
      line-height: 1.7;
      overflow-x: hidden;
    }
    a { color: inherit; text-decoration: none; }
    svg { width: 1.25rem; height: 1.25rem; display: block; }
    .wrap { width: min(1180px, calc(100% - 40px)); margin: 0 auto; }
    .section { padding: 100px 0; position: relative; }
    .section-head { max-width: 650px; margin: 0 auto 56px; text-align: center; }
    .eyebrow {
      display: inline-flex; align-items: center; gap: 8px;
      padding: 7px 14px; border-radius: 999px;
      background: rgba(37, 99, 235, .08); border: 1px solid rgba(37, 99, 235, .2);
      color: #1d4ed8; font-size: .8rem; font-weight: 700; letter-spacing: .02em;
    }
    .eyebrow svg { width: 15px; height: 15px; }
    h1, h2, h3, h4 { margin: 0; line-height: 1.3; letter-spacing: -.02em; }
    h1 { font-size: clamp(2.5rem, 5vw, 4.2rem); font-weight: 800; }
    h2 { font-size: clamp(1.9rem, 3.4vw, 2.8rem); font-weight: 800; }
    h3 { font-size: 1.1rem; font-weight: 700; }
    p { margin: 0; }
    .lead { color: var(--muted); font-size: 1.05rem; }
    .gradient-text {
      background: var(--gradient); -webkit-background-clip: text; background-clip: text;
      -webkit-text-fill-color: transparent; color: transparent;
    }
    .btn {
      display: inline-flex; align-items: center; justify-content: center; gap: 10px;
      padding: 13px 24px; border-radius: 12px; font-size: .95rem; font-weight: 700;
      border: 1px solid transparent; transition: transform .2s ease, box-shadow .2s ease, background .2s ease;
      white-space: nowrap;
    }
    .btn svg { width: 18px; height: 18px; }
    .btn-primary { background: var(--gradient); color: #fff; box-shadow: 0 8px 20px rgba(37, 99, 235, .18); }
    .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 12px 28px rgba(37, 99, 235, .28); }
    .btn-ghost { background: var(--surface); border-color: var(--border); color: var(--text); }
    .btn-ghost:hover { background: var(--surface-hover); transform: translateY(-2px); }
    .btn-sm { padding: 9px 16px; font-size: .85rem; border-radius: 10px; }

    .site-header {
      position: sticky; top: 0; z-index: 50;
      background: rgba(255, 255, 255, .86); backdrop-filter: blur(18px);
      border-bottom: 1px solid transparent; transition: border-color .25s ease, background .25s ease;
    }
    .site-header.scrolled { border-bottom-color: var(--border); background: rgba(255, 255, 255, .96); }
    .nav { height: 76px; display: flex; align-items: center; justify-content: space-between; gap: 24px; }
    .brand { display: flex; align-items: center; gap: 11px; font-weight: 800; font-size: 1.15rem; letter-spacing: -.02em; }
    .brand-mark {
      width: 38px; height: 38px; border-radius: 11px; background: var(--gradient);
      display: grid; place-items: center; font-size: 1.1rem; font-weight: 900; color: #fff;
      box-shadow: 0 8px 22px rgba(37, 99, 235, .2);
    }
    .nav-links { display: flex; align-items: center; gap: 30px; font-size: .92rem; color: var(--muted); }
    .nav-links a:hover { color: var(--text); }
    .nav-actions { display: flex; align-items: center; gap: 10px; }
    .nav-toggle { display: none; background: var(--surface); border: 1px solid var(--border); color: var(--text); border-radius: 10px; padding: 9px; cursor: pointer; }

    .hero { position: relative; padding: 96px 0 110px; overflow: hidden; }
    .hero::before {
      content: ''; position: absolute; inset: -30% 25% auto -10%; height: 780px;
      background: radial-gradient(circle, rgba(37, 99, 235, .12), transparent 65%); pointer-events: none;
    }
    .hero::after {
      content: ''; position: absolute; inset: 10% -20% auto 45%; height: 700px;
      background: radial-gradient(circle, rgba(14, 165, 233, .1), transparent 65%); pointer-events: none;
    }
    .hero-grid { position: relative; z-index: 1; display: grid; grid-template-columns: 1.05fr .95fr; gap: 64px; align-items: center; }
    .hero h1 { margin: 22px 0 20px; }
    .hero .lead { max-width: 540px; }
    .hero-buttons { display: flex; flex-wrap: wrap; gap: 12px; margin-top: 32px; }
    .hero-meta { display: flex; flex-wrap: wrap; gap: 26px; margin-top: 42px; }
    .hero-meta div { display: flex; align-items: center; gap: 10px; color: var(--muted); font-size: .88rem; }
    .hero-meta svg { color: var(--cyan); width: 18px; height: 18px; }

    .mock {
      position: relative; background: linear-gradient(155deg, #ffffff, #f7fbff);
      border: 1px solid var(--border); border-radius: 22px; padding: 20px; box-shadow: var(--shadow);
      transform: perspective(1200px) rotateY(-6deg) rotateX(3deg);
    }
    .mock-bar { display: flex; align-items: center; gap: 6px; padding-bottom: 16px; border-bottom: 1px solid var(--border); }
    .mock-bar i { width: 9px; height: 9px; border-radius: 50%; background: #ef4444; display: block; }
    .mock-bar i:nth-child(2) { background: #f59e0b; }
    .mock-bar i:nth-child(3) { background: #10b981; }
    .mock-bar span { margin-left: 10px; font-size: .8rem; color: var(--muted); }
    .mock-balance {
      margin-top: 18px; padding: 20px; border-radius: 16px; background: var(--gradient);
      display: flex; justify-content: space-between; align-items: flex-end; color: #fff;
    }
    .mock-balance small { font-size: .75rem; opacity: .85; }
    .mock-balance strong { display: block; font-size: 1.8rem; letter-spacing: -.03em; }
    .mock-balance span { font-size: .75rem; opacity: .85; }
    .mock-rows { margin-top: 18px; display: grid; gap: 10px; }
    .mock-row { display: flex; align-items: center; gap: 12px; padding: 11px 13px; border-radius: 12px; background: var(--surface); border: 1px solid var(--border); }
    .mock-dot { width: 32px; height: 32px; border-radius: 9px; background: rgba(37, 99, 235, .1); display: grid; place-items: center; color: #1d4ed8; font-size: .75rem; font-weight: 800; flex: none; }
    .mock-row div { flex: 1; min-width: 0; }
    .mock-row b { display: block; font-size: .83rem; }
    .mock-row small { color: var(--muted); font-size: .72rem; }
    .status { font-size: .68rem; font-weight: 700; padding: 4px 9px; border-radius: 999px; background: rgba(16, 185, 129, .12); color: #047857; }
    .status.wait { background: rgba(245, 158, 11, .12); color: #b45309; }
    .mock-float {
      position: absolute; right: -18px; bottom: 44px; padding: 13px 16px; border-radius: 14px;
      background: rgba(255, 255, 255, .96); border: 1px solid rgba(14, 165, 233, .3);
      box-shadow: var(--shadow); font-size: .8rem; display: flex; align-items: center; gap: 10px;
    }
    .mock-float svg { color: #0284c7; }
    .mock-float b { color: #0369a1; }

    .platforms { padding: 34px 0; border-top: 1px solid var(--border); border-bottom: 1px solid var(--border); background: rgba(255, 255, 255, .6); }
    .platforms .wrap { display: flex; align-items: center; justify-content: center; flex-wrap: wrap; gap: 18px 42px; }
    .platforms span { color: var(--muted); font-size: .8rem; letter-spacing: .06em; text-transform: uppercase; font-weight: 700; }
    .platforms b { font-size: 1.05rem; font-weight: 700; color: #475569; opacity: .9; }

    .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; }
    .stat { padding: 26px; border-radius: 18px; background: var(--surface); border: 1px solid var(--border); }
    .stat strong { display: block; font-size: 2rem; letter-spacing: -.04em; background: var(--gradient); -webkit-background-clip: text; background-clip: text; -webkit-text-fill-color: transparent; }
    .stat span { color: var(--muted); font-size: .88rem; }

    .features-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 18px; }
    .card {
      padding: 28px; border-radius: 18px; background: var(--surface); border: 1px solid var(--border);
      transition: transform .25s ease, border-color .25s ease, background .25s ease;
    }
    .card:hover { transform: translateY(-5px); background: var(--surface-hover); border-color: rgba(37, 99, 235, .35); }
    .icon-box {
      width: 48px; height: 48px; border-radius: 14px; display: grid; place-items: center;
      background: rgba(37, 99, 235, .1); color: #1d4ed8; margin-bottom: 20px;
    }
    .card p { color: var(--muted); font-size: .91rem; margin-top: 9px; }

    .services-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 18px; }
    .service { display: flex; align-items: center; gap: 16px; }
    .service .icon-box { margin: 0; flex: none; }
    .service small { color: var(--muted); font-size: .82rem; }
    .service-tag { margin-left: auto; font-size: .7rem; font-weight: 700; color: #0284c7; background: rgba(14, 165, 233, .1); padding: 5px 9px; border-radius: 999px; }

    .steps { display: grid; grid-template-columns: repeat(3, 1fr); gap: 18px; counter-reset: step; }
    .step { position: relative; padding: 32px 28px 28px; border-radius: 18px; background: var(--surface); border: 1px solid var(--border); }
    .step::before {
      counter-increment: step; content: '0' counter(step); position: absolute; top: 16px; right: 22px;
      font-size: 2.4rem; font-weight: 800; color: rgba(37, 99, 235, .14);
    }
    .step p { color: var(--muted); font-size: .9rem; margin-top: 10px; }

    .faq-wrap { display: grid; grid-template-columns: .8fr 1.2fr; gap: 60px; align-items: start; }
    .faq-wrap .section-head { text-align: left; margin: 0; }
    details {
      background: var(--surface); border: 1px solid var(--border); border-radius: 14px;
      margin-bottom: 12px; overflow: hidden;
    }
    summary {
      list-style: none; cursor: pointer; padding: 18px 20px; font-weight: 600; font-size: .96rem;
      display: flex; align-items: center; justify-content: space-between; gap: 16px;
    }
    summary::-webkit-details-marker { display: none; }
    summary::after { content: '+'; color: #2563eb; font-size: 1.4rem; line-height: 1; }
    details[open] summary::after { content: '\2212'; }
    details p { padding: 0 20px 18px; color: var(--muted); font-size: .91rem; }

    .cta { padding: 30px 0 110px; }
    .cta-panel {
      position: relative; overflow: hidden; border-radius: 26px; padding: 64px 48px; text-align: center;
      background: linear-gradient(120deg, rgba(37, 99, 235, .1), rgba(14, 165, 233, .08)), #ffffff;
      border: 1px solid rgba(37, 99, 235, .2);
    }
    .cta-panel::before {
      content: ''; position: absolute; width: 420px; height: 420px; border-radius: 50%;
      background: rgba(37, 99, 235, .1); filter: blur(70px); top: -220px; left: 50%; transform: translateX(-50%);
    }
    .cta-panel > * { position: relative; }
    .cta-panel p { color: #475569; margin: 16px auto 28px; max-width: 540px; }

    footer { border-top: 1px solid var(--border); padding: 48px 0 30px; background: rgba(255, 255, 255, .7); }
    .footer-grid { display: grid; grid-template-columns: 1.4fr 1fr 1fr; gap: 40px; }
    footer p { color: var(--muted); font-size: .88rem; margin-top: 14px; max-width: 340px; }
    .footer-col h4 { font-size: .9rem; text-transform: uppercase; letter-spacing: .08em; color: #1e3a5f; margin-bottom: 16px; }
    .footer-col a { display: block; color: var(--muted); font-size: .9rem; padding: 5px 0; }
    .footer-col a:hover { color: var(--text); }
    .copyright { margin-top: 38px; padding-top: 22px; border-top: 1px solid var(--border); color: var(--muted); font-size: .82rem; display: flex; justify-content: space-between; gap: 16px; flex-wrap: wrap; }
    .copyright a { color: #2563eb; }

    .reveal { opacity: 0; transform: translateY(18px); transition: opacity .6s ease, transform .6s ease; }
    .reveal.in { opacity: 1; transform: none; }

    @media (max-width: 980px) {
      .hero-grid, .faq-wrap { grid-template-columns: 1fr; gap: 48px; }
      .hero { padding-top: 64px; }
      .mock { transform: none; max-width: 540px; }
      .features-grid, .services-grid, .steps { grid-template-columns: repeat(2, 1fr); }
      .stats-grid { grid-template-columns: repeat(2, 1fr); }
      .footer-grid { grid-template-columns: 1fr 1fr; }
      .nav-links { display: none; position: absolute; top: 76px; left: 0; right: 0; padding: 18px 20px 24px; flex-direction: column; align-items: stretch; gap: 0; background: #ffffff; border-bottom: 1px solid var(--border); }
      .nav-links.open { display: flex; }
      .nav-links a { padding: 11px 0; }
      .nav-toggle { display: block; }
      .nav-actions .btn-ghost { display: none; }
    }
    @media (max-width: 640px) {
      .section { padding: 70px 0; }
      .features-grid, .services-grid, .steps, .stats-grid { grid-template-columns: 1fr; }
      .hero-buttons .btn { width: 100%; }
      .cta-panel { padding: 44px 24px; }
      .footer-grid { grid-template-columns: 1fr; }
      .mock-float { right: 6px; bottom: 20px; }
    }
    @media (prefers-reduced-motion: reduce) {
      html { scroll-behavior: auto; }
      .reveal { opacity: 1; transform: none; transition: none; }
    }
  </style>
</head>

<body>
  <header class="site-header" id="siteHeader">
    <nav class="nav wrap">
      <a href="{{ route('index') }}" class="brand">
        <span class="brand-mark">N</span>
        <span>{{ $siteTitle }}</span>
      </a>

      <div class="nav-links" id="navLinks">
        <a href="#features">ฟีเจอร์</a>
        <a href="#services">บริการ</a>
        <a href="#how">วิธีการทำงาน</a>
        <a href="#faq">คำถามที่พบบ่อย</a>
        <a href="{{ route('pages.api-docs') }}">เอกสาร API</a>
      </div>

      <div class="nav-actions">
        <a href="{{ route('login') }}" class="btn btn-ghost btn-sm">เข้าสู่ระบบ</a>
        <a href="{{ route('register') }}" class="btn btn-primary btn-sm">สมัครฟรี</a>
        <button class="nav-toggle" id="navToggle" type="button" aria-label="เปิดเมนู" aria-expanded="false">{!! $icons['menu'] !!}</button>
      </div>
    </nav>
  </header>

  <main>
    <section class="hero" id="home">
      <div class="wrap hero-grid">
        <div>
          <span class="eyebrow">{!! $icons['spark'] !!} แพลตฟอร์ม SMM ยุคใหม่</span>
          <h1>เติบโตบนโซเชียล <span class="gradient-text">เร็วยิ่งขึ้น</span>.</h1>
          <p class="lead">แดชบอร์ดเดียวสำหรับจัดการคำสั่ง TikTok, Instagram, Facebook, YouTube และแพลตฟอร์มอื่นๆ ราคาดี รวดเร็ว รองรับ 24/7</p>
          <div class="hero-buttons">
            <a href="{{ route('register') }}" class="btn btn-primary">เริ่มเลย {!! $icons['arrow'] !!}</a>
            <a href="{{ route('pages.services') }}" class="btn btn-ghost">ดูบริการ</a>
          </div>
          <div class="hero-meta">
            <div>{!! $icons['check'] !!} ไม่ต้องฝากขั้นต่ำ</div>
            <div>{!! $icons['check'] !!} API สำหรับเอเจนซี่</div>
            <div>{!! $icons['check'] !!} ชำระเงินปลอดภัย</div>
          </div>
        </div>

        <div class="mock" aria-hidden="true">
          <div class="mock-bar"><i></i><i></i><i></i><span>{{ $siteTitle }} — แดชบอร์ด</span></div>
          <div class="mock-balance">
            <div><small>ยอดคงเหลือ</small><strong>฿24,500</strong></div>
            <span>พร้อมใช้</span>
          </div>
          <div class="mock-rows">
            <div class="mock-row">
              <span class="mock-dot">TT</span>
              <div><b>TikTok · Followers</b><small>1,000 · คำสั่ง #1042</small></div>
              <span class="status">เสร็จสิ้น</span>
            </div>
            <div class="mock-row">
              <span class="mock-dot">IG</span>
              <div><b>Instagram · Likes</b><small>500 · คำสั่ง #1041</small></div>
              <span class="status wait">กำลังดำเนินการ</span>
            </div>
            <div class="mock-row">
              <span class="mock-dot">YT</span>
              <div><b>YouTube · Views</b><small>2,000 · คำสั่ง #1040</small></div>
              <span class="status">เสร็จสิ้น</span>
            </div>
          </div>
          <div class="mock-float">{!! $icons['chart'] !!} <span>คำสั่งใหม่วันนี้ <b>+128</b></span></div>
        </div>
      </div>
    </section>

    <section class="platforms">
      <div class="wrap">
        <span>รองรับทุกแพลตฟอร์ม</span>
        <b>TikTok</b><b>Instagram</b><b>Facebook</b><b>YouTube</b><b>Telegram</b><b>Twitter X</b><b>Spotify</b>
      </div>
    </section>

    <section class="section" id="stats">
      <div class="wrap stats-grid reveal">
        <div class="stat"><strong>50+</strong><span>บริการคุณภาพ</span></div>
        <div class="stat"><strong>120K+</strong><span>คำสั่งที่ดำเนินการแล้ว</span></div>
        <div class="stat"><strong>99.9%</strong><span>เวลาทำงานของระบบ</span></div>
        <div class="stat"><strong>24/7</strong><span>ซัพพอร์ตลูกค้า</span></div>
      </div>
    </section>

    <section class="section" id="features" style="padding-top:0">
      <div class="wrap">
        <div class="section-head reveal">
          <span class="eyebrow">{!! $icons['bolt'] !!} ทำไมต้องเลือกเรา</span>
          <h2 style="margin-top:18px">ทุกอย่างที่คุณต้องการเพื่อ <span class="gradient-text">เติบโต</span></h2>
          <p class="lead" style="margin-top:14px">ตั้งแต่คำสั่งแรกจนถึงระดับเอเจนซี่ เรามีเครื่องมือที่ช่วยให้คุณไปได้เร็วขึ้น</p>
        </div>

        <div class="features-grid">
          <div class="card reveal">
            <div class="icon-box">{!! $icons['spark'] !!}</div>
            <h3>บริการหลากหลาย</h3>
            <p>บริการนับร้อยสำหรับทุกแพลตฟอร์ม อัปเดตตามเทรนด์อยู่เสมอ</p>
          </div>
          <div class="card reveal">
            <div class="icon-box">{!! $icons['shield'] !!}</div>
            <h3>คุณภาพเสถียร</h3>
            <p>คำสั่งทำงานรวดเร็ว คงทน และปลอดภัยสำหรับบัญชีของคุณ</p>
          </div>
          <div class="card reveal">
            <div class="icon-box">{!! $icons['wallet'] !!}</div>
            <h3>ราคาแข่งขันได้</h3>
            <p>ราคาโปร่งใส ส่วนลดสำหรับเอเจนซี่ และช่องทางเติมเงินมากมาย</p>
          </div>
          <div class="card reveal">
            <div class="icon-box">{!! $icons['api'] !!}</div>
            <h3>API ทรงพลัง</h3>
            <p>เชื่อมต่อระบบของคุณด้วยโค้ดเพียงไม่กี่บรรทัด พร้อมเอกสารและการซัพพอร์ต</p>
          </div>
          <div class="card reveal">
            <div class="icon-box">{!! $icons['chart'] !!}</div>
            <h3>รายงานชัดเจน</h3>
            <p>ติดตามคำสั่ง ยอดคงเหลือ และประวัติธุรกรรมแบบเรียลไทม์ในหน้าเดียว</p>
          </div>
          <div class="card reveal">
            <div class="icon-box">{!! $icons['headset'] !!}</div>
            <h3>ซัพพอร์ตใส่ใจ</h3>
            <p>ทีมงานพร้อมช่วยแก้ปัญหาทุกเรื่อง ทุกที่ ทุกเวลา</p>
          </div>
        </div>
      </div>
    </section>

    <section class="section" id="services" style="background:rgba(255,255,255,.7)">
      <div class="wrap">
        <div class="section-head reveal">
          <span class="eyebrow">{!! $icons['spark'] !!} บริการยอดนิยม</span>
          <h2 style="margin-top:18px">เลือกแพลตฟอร์ม <span class="gradient-text">เลือกการเติบโต</span></h2>
        </div>
        <div class="services-grid">
          <div class="card service reveal"><div class="icon-box">T</div><div><h3>TikTok</h3><small>Followers · Views · Likes</small></div><span class="service-tag">HOT</span></div>
          <div class="card service reveal"><div class="icon-box">IG</div><div><h3>Instagram</h3><small>Followers · Likes · Story</small></div><span class="service-tag">HOT</span></div>
          <div class="card service reveal"><div class="icon-box">FB</div><div><h3>Facebook</h3><small>Likes · Followers · Share</small></div></div>
          <div class="card service reveal"><div class="icon-box">YT</div><div><h3>YouTube</h3><small>Views · Subscribers · Watch</small></div></div>
          <div class="card service reveal"><div class="icon-box">TG</div><div><h3>Telegram</h3><small>Members · Views · Reaction</small></div></div>
          <div class="card service reveal"><div class="icon-box">X</div><div><h3>Twitter / X</h3><small>Followers · Retweets · Likes</small></div></div>
        </div>
        <div style="text-align:center;margin-top:32px">
          <a href="{{ route('pages.services') }}" class="btn btn-ghost">ดูบริการทั้งหมด {!! $icons['arrow'] !!}</a>
        </div>
      </div>
    </section>

    <section class="section" id="how">
      <div class="wrap">
        <div class="section-head reveal">
          <span class="eyebrow">{!! $icons['bolt'] !!} วิธีการทำงาน</span>
          <h2 style="margin-top:18px">เพียง 3 ขั้นตอน <span class="gradient-text">เริ่มได้เลย</span></h2>
        </div>
        <div class="steps">
          <div class="step reveal">
            <div class="icon-box">{!! $icons['wallet'] !!}</div>
            <h3>สมัครและเติมเงิน</h3>
            <p>สร้างบัญชีฟรี แล้วเติมเงินด้วยช่องทางที่คุณต้องการ</p>
          </div>
          <div class="step reveal">
            <div class="icon-box">{!! $icons['spark'] !!}</div>
            <h3>เลือกบริการ</h3>
            <p>เลือกแพลตฟอร์ม ใส่ลิงก์และจำนวนที่ต้องการในไม่กี่วินาที</p>
          </div>
          <div class="step reveal">
            <div class="icon-box">{!! $icons['chart'] !!}</div>
            <h3>ติดตามผลลัพธ์</h3>
            <p>คำสั่งทำงานอัตโนมัติ ติดตามสถานะแบบเรียลไทม์บนแดชบอร์ด</p>
          </div>
        </div>
      </div>
    </section>

    <section class="section" id="faq" style="background:rgba(255,255,255,.7)">
      <div class="wrap faq-wrap">
        <div class="section-head reveal">
          <span class="eyebrow">ช่วยเหลือ</span>
          <h2 style="margin-top:18px">คำถาม <span class="gradient-text">ที่พบบ่อย</span></h2>
          <p class="lead" style="margin-top:14px">ไม่พบคำตอบ? ติดต่อเราได้ตลอดเวลา</p>
        </div>
        <div class="reveal">
          <details>
            <summary>SMM panel คืออะไร?</summary>
            <p>SMM panel คือแพลตฟอร์มออนไลน์ที่ให้บริการโซเชียลมีเดีย เช่น เพิ่ม followers, likes, views ด้วยค่าใช้จ่ายต่ำและรวดเร็ว</p>
          </details>
          <details>
            <summary>สั่งหลายคำสั่งพร้อมกันได้ไหม?</summary>
            <p>ได้ คุณสามารถสร้างคำสั่งหลายรายการพร้อมกัน หรือใช้ฟีเจอร์สั่งจำนวนมากในแดชบอร์ด</p>
          </details>
          <details>
            <summary>บริการปลอดภัยสำหรับบัญชีของฉันไหม?</summary>
            <p>ปลอดภัย บริการของเรามีความเสถียร ช่วยให้บัญชีของคุณเติบโตโดยไม่มีความเสี่ยง</p>
          </details>
          <details>
            <summary>Drip-feed ทำงานอย่างไร?</summary>
            <p>Drip-feed ช่วยกระจายคำสั่งตามเวลา ตัวอย่าง 2,000 likes สามารถแบ่งเป็น 200 likes ต่อวันเป็นเวลา 10 วัน</p>
          </details>
          <details>
            <summary>มี API สำหรับเอเจนซี่ไหม?</summary>
            <p>มี เรามี API ที่ครอบคลุม ดูรายละเอียดได้ที่หน้าเอกสาร API</p>
          </details>
        </div>
      </div>
    </section>

    <section class="cta">
      <div class="wrap cta-panel reveal">
        <span class="eyebrow">{!! $icons['spark'] !!} พร้อมเริ่มแล้ว</span>
        <h2 style="margin-top:20px">พาแบรนด์ของคุณ <span class="gradient-text">ไปให้ไกลขึ้น</span></h2>
        <p>สมัครฟรีวันนี้ แล้วสัมผัสแดชบอร์ด SMM ที่ทันสมัย รวดเร็ว และใช้งานง่าย</p>
        <a href="{{ route('register') }}" class="btn btn-primary">สร้างบัญชีฟรี {!! $icons['arrow'] !!}</a>
      </div>
    </section>
  </main>

  <footer>
    <div class="wrap">
      <div class="footer-grid">
        <div>
          <a href="{{ route('index') }}" class="brand"><span class="brand-mark">N</span><span>{{ $siteTitle }}</span></a>
          <p>{{ $siteDescription }}</p>
        </div>
        <div class="footer-col">
          <h4>ลิงก์ด่วน</h4>
          <a href="#features">ฟีเจอร์</a>
          <a href="{{ route('pages.services') }}">บริการ</a>
          <a href="{{ route('pages.api-docs') }}">เอกสาร API</a>
          <a href="{{ route('register') }}">สมัครสมาชิก</a>
        </div>
        <div class="footer-col">
          <h4>ข้อมูลทางกฎหมาย</h4>
          <a href="{{ route('pages.privacy') }}">นโยบายความเป็นส่วนตัว</a>
          <a href="{{ route('pages.tos') }}">ข้อกำหนดการใช้งาน</a>
          <a href="{{ route('login') }}">เข้าสู่ระบบ</a>
        </div>
      </div>
      <div class="copyright">
        <span>&copy; {{ date('Y') }} {{ $siteTitle }}. สงวนลิขสิทธิ์</span>
        <span>พัฒนาโดย <a href="{{ setting('footer_link', 'https://www.cmsnt.co/?utm=smmpanelv3&domain=' . domain()) }}">{{ setting('footer_text', 'CMSNT.CO LTD') }}</a></span>
      </div>
    </div>
  </footer>

  <script>
    (function () {
      const header = document.getElementById('siteHeader');
      const toggle = document.getElementById('navToggle');
      const links = document.getElementById('navLinks');

      toggle?.addEventListener('click', function () {
        const open = links.classList.toggle('open');
        toggle.setAttribute('aria-expanded', String(open));
      });

      links?.querySelectorAll('a').forEach(function (a) {
        a.addEventListener('click', function () {
          links.classList.remove('open');
          toggle?.setAttribute('aria-expanded', 'false');
        });
      });

      function onScroll() {
        header.classList.toggle('scrolled', window.scrollY > 12);
      }
      onScroll();
      window.addEventListener('scroll', onScroll, { passive: true });

      const items = document.querySelectorAll('.reveal');
      if ('IntersectionObserver' in window) {
        const observer = new IntersectionObserver(function (entries) {
          entries.forEach(function (entry) {
            if (entry.isIntersecting) {
              entry.target.classList.add('in');
              observer.unobserve(entry.target);
            }
          });
        }, { threshold: 0.12 });
        items.forEach(function (item) { observer.observe(item); });
      } else {
        items.forEach(function (item) { item.classList.add('in'); });
      }
    })();
  </script>
</body>

</html>
