<!doctype html>
<html lang="tr">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Döner Çember (Loader) – Demo</title>
  <style>
    /* === Ayarlanabilir Değerler === */
    :root {
      --spinner-size: 40px;      /* Çemberin çapı */
      --spinner-thickness: 6px;  /* Kenar kalınlığı */
      --spinner-color: #00174A;  /* Üstte dönen renk */
      --spinner-track: #f8f8f8;  /* Alttaki iz rengi */
      --overlay-bg: rgba(255,255,255,0.8); /* Sayfa üstü maske rengi */
    }

    /* === Basit Çember === */
    .spinner {
      width: var(--spinner-size);
      aspect-ratio: 1;
      border-radius: 50%;
      border: var(--spinner-thickness) solid var(--spinner-track);
      border-top-color: var(--spinner-color);
      animation: spin 1s linear infinite;
    }

    @keyframes spin {
      to { transform: rotate(360deg); }
    }

    /* Hareketi azalt tercihine saygı */
    @media (prefers-reduced-motion: reduce) {
      .spinner { animation: none; }
    }

    /* === Tam Sayfa Yükleniyor Katmanı === */
    .loader-overlay {
      position: fixed;
      inset: 0;
      display: none;          /* .show eklenince görünür */
      place-items: center;
      background: var(--overlay-bg);
      backdrop-filter: blur(2px);
      z-index: 9999;
    }
    .loader-overlay.show { display: grid; }

    /* Demo yerleşimi */
    body { font-family: system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif; margin: 0; }
    .wrap { min-height: 100svh; display: grid; place-items: center; gap: 24px; padding: 24px; }
    .card { max-width: 680px; width: 100%; border-radius: 16px; box-shadow: 0 6px 24px rgba(0,0,0,.08); padding: 24px; }
    .row { display: flex; align-items: center; gap: 16px; flex-wrap: wrap; }
    button { cursor: pointer; border: 0; border-radius: 999px; padding: 10px 16px; font-weight: 600; }
    .btn { background: #111827; color: #fff; }
    .btn.secondary { background: #e5e7eb; color: #111827; }
    code { background: #f3f4f6; padding: 2px 6px; border-radius: 6px; }
  </style>
</head>
<body>
  <!-- Tam sayfa loader: Projeye kolay eklemek için kopyalayın -->
  <div class="loader-overlay" id="pageLoader" role="status" aria-live="polite" aria-busy="true">
    <div class="spinner" aria-label="Yükleniyor"></div>
  </div>

  <div class="wrap">
    <div class="card">
      <h1>Yuvarlak Çember Loader</h1>
      <p>
        En basit kullanım için şu HTML yeterli: <code>&lt;div class="spinner"&gt;&lt;/div&gt;</code>
        Boyut, kalınlık ve renkleri <code>:root</code> değişkenleriyle ayarlayabilirsin.
      </p>

      <h2>1) Basit yerinde kullanım</h2>
      <div class="row">
        <div class="spinner"></div>
        <span>İçerikte beklerken göstermek için.</span>
      </div>

      <h2>2) Tam sayfa yükleniyor katmanı</h2>
      <div class="row">
        <button class="btn" id="showLoaderBtn">Katmanı Göster</button>
        <button class="btn secondary" id="hideLoaderBtn">Gizle</button>
        <button class="btn" id="simulateBtn">Simüle Et (1.5s)</button>
      </div>

      <p>
        Bu katmanı, sayfa/istek başlarken <code>showPageLoader()</code> ve bittiğinde
        <code>hidePageLoader()</code> ile kontrol edebilirsin.
      </p>

      <h3>Hızlı entegrasyon</h3>
      <ol>
        <li>Şu CSS'i projenin stil dosyasına koy: <code>.spinner</code>, <code>.loader-overlay</code> ve <code>@keyframes spin</code>.</li>
        <li>Body içine şu yapıyı ekle: <code>&lt;div id="pageLoader" class="loader-overlay"&gt;...&lt;/div&gt;</code></li>
        <li>İstek başladığında <code>showPageLoader()</code>, bittiğinde <code>hidePageLoader()</code>.</li>
      </ol>

      <h3>Renk/ölçü özelleştirme</h3>
      <div class="row">
        <button class="btn secondary" data-size="40" data-thick="4">Küçük</button>
        <button class="btn secondary" data-size="64" data-thick="6">Orta</button>
        <button class="btn secondary" data-size="96" data-thick="8">Büyük</button>
      </div>
    </div>
  </div>

  <script>
    // === Yardımcı fonksiyonlar ===
    function setSpinnerVars({ size, thickness }) {
      if (size) document.documentElement.style.setProperty('--spinner-size', size + 'px');
      if (thickness) document.documentElement.style.setProperty('--spinner-thickness', thickness + 'px');
    }

    function showPageLoader() {
      document.getElementById('pageLoader').classList.add('show');
    }
    function hidePageLoader() {
      document.getElementById('pageLoader').classList.remove('show');
    }

    // === Demo butonları ===
    document.getElementById('showLoaderBtn').addEventListener('click', showPageLoader);
    document.getElementById('hideLoaderBtn').addEventListener('click', hidePageLoader);
    document.getElementById('simulateBtn').addEventListener('click', () => {
      showPageLoader();
      // Burada gerçek bir fetch/işlem yerine kısa bir simülasyon var
      setTimeout(hidePageLoader, 1500);
    });

    // Boyut örnekleri
    document.querySelectorAll('[data-size]').forEach(btn => {
      btn.addEventListener('click', () => {
        setSpinnerVars({ size: btn.dataset.size, thickness: btn.dataset.thick });
      });
    });
  </script>
</body>
</html>
