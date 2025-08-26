<!doctype html>
<html lang="tr">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Yorum Kutusu - Tek Dosya</title>
  <style>
    /* Tamamen izole sınıf adları: yorumkutusu- */

    .yorumkutusu-card {
      box-sizing: border-box;
      width: 100%;
      max-width: 720px;
      margin: 18px auto;
      padding: 20px;
      background: linear-gradient(180deg, #ffffff 0%, #fafafa 100%);
      border-radius: 14px;
      box-shadow: 0 8px 22px rgba(12, 18, 33, 0.08), inset 0 1px 0 rgba(255,255,255,0.6);
      border: 1px solid rgba(0,0,0,0.06);
      font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
    }

    .yorumkutusu-header {
      display: flex;
      align-items: center;
      gap: 12px;
      margin-bottom: 14px;
    }

    .yorumkutusu-title {
      font-size: 16px;
      font-weight: 700;
      color: #0f1a33;
    }

    .yorumkutusu-subtitle {
      font-size: 13px;
      color: #6b7280;
      margin-top: 2px;
      margin-left: 9px;
    }

    /* Yorum listesi kapsayıcısı */
    .yorumkutusu-list-wrap {
      border-radius: 10px;
      overflow: hidden;
      border: 1px solid rgba(8,12,20,0.04);
      box-shadow: inset 0 1px 0 rgba(255,255,255,0.6);
    }

    .yorumkutusu-list {
      max-height: 280px; /* kaydırma alanı */
      overflow-y: auto;
      padding: 10px;
      display: flex;
      flex-direction: column;
      gap: 12px;
      background: linear-gradient(180deg, rgba(250,250,252,1) 0%, rgba(248,249,250,1) 100%);
    }

    /* Her bir yorum kartı */
    .yorumkutusu-item {
      display: flex;
      gap: 12px;
      align-items: flex-start;
      padding: 10px;
      background: #fff;
      border-radius: 10px;
      border: 1px solid rgba(10,14,26,0.04);
      box-shadow: 0 6px 14px rgba(12,18,33,0.04);
    }

    .yorumkutusu-item-avatar {
      width: 44px;
      height: 44px;
      border-radius: 8px;
      background: #eef2ff;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: 700;
      color: #243b71;
      flex: 0 0 44px;
    }

    .yorumkutusu-item-body {
      flex: 1 1 auto;
      min-width: 0;
    }

    .yorumkutusu-item-head {
      display: flex;
      gap: 8px;
      align-items: baseline;
      margin-bottom: 6px;
    }

    .yorumkutusu-item-name {
      font-weight: 700;
      color: #09102a;
      font-size: 14px;
    }

    .yorumkutusu-item-time {
      color: #8b93a7;
      font-size: 12px;
    }

    .yorumkutusu-item-text {
      font-size: 13px;
      color: #12202f;
      line-height: 1.45;
      word-break: break-word;
    }

    /* Yorum eylem çubuğu (beğen, cevap gibi) */
    .yorumkutusu-actions {
      margin-top: 8px;
      display: flex;
      gap: 8px;
      align-items: center;
    }

    .yorumkutusu-btn {
      appearance: none;
      border: 0;
      background: linear-gradient(180deg,#0b2b6b,#0e2c6d);
      color: #fff;
      padding: 6px 10px;
      border-radius: 8px;
      box-shadow: 0 6px 12px rgba(11,43,107,0.18);
      font-size: 13px;
      cursor: pointer;
      transition: transform .12s ease, box-shadow .12s ease, opacity .12s ease;
    }

    .yorumkutusu-btn:active { transform: translateY(1px); }
    .yorumkutusu-btn:hover { opacity: 0.96; }

    .yorumkutusu-like {
      background: linear-gradient(180deg,#ffffff,#e8f0ff);
      color: #0b2b6b;
      border: 1px solid rgba(11,43,107,0.08);
      box-shadow: none;
      padding: 6px 8px;
    }

    /* Yorum gönder alanı */
    .yorumkutusu-form {
      display: flex;
      gap: 10px;
      align-items: flex-start;
      margin-top: 14px;
    }

    .yorumkutusu-input-wrap {
      flex: 1 1 auto;
      display: flex;
      flex-direction: column;
      gap: 8px;
    }

    .yorumkutusu-input {
      width: 100%;
      min-height: 48px;
      resize: vertical;
      padding: 10px 12px;
      border-radius: 10px;
      border: 1px solid rgba(10,14,26,0.06);
      background: #fff;
      font-size: 13px;
      box-shadow: 0 6px 14px rgba(12,18,33,0.04) inset;
      outline: none;
      box-sizing: border-box;
      transition: box-shadow .12s ease, border-color .12s ease;
      font-family: inherit;
    }

    .yorumkutusu-input:focus {
      border-color: rgba(11,43,107,0.14);
      box-shadow: 0 6px 18px rgba(11,43,107,0.06);
    }

    .yorumkutusu-submit {
      flex: 0 0 110px;
      display: flex;
      gap: 8px;
      align-items: center;
      justify-content: center;
      flex-direction: column;
    }

    .yorumkutusu-send-btn {
      width: 100%;
      padding: 10px 12px;
      border-radius: 10px;
      border: 0;
      font-weight: 700;
      background: linear-gradient(180deg,#112a64,#0b2150);
      color: #fff;
      cursor: pointer;
      box-shadow: 0 8px 20px rgba(11,43,107,0.18);
    }

    .yorumkutusu-send-btn:disabled {
      opacity: 0.5;
      cursor: not-allowed;
      box-shadow: none;
    }

    .yorumkutusu-meta {
      font-size: 12px;
      color: #7b8292;
      margin-top: 8px;
    }

    /* Küçük ekranlarda uyum */
    @media (max-width:480px){
      .yorumkutusu-card{ padding:12px; }
      .yorumkutusu-list{ max-height: 220px; }
      .yorumkutusu-submit{ flex-basis: 86px; }
    }
  </style>
</head>
<body>

  <div class="yorumkutusu-card" id="yorumkutusu-root">

    <div class="yorumkutusu-header">
      <div class="yorumkutusu-avatar"></div>
      <div>
        <div class="yorumkutusu-title">Yorumlar</div>
        <div class="yorumkutusu-subtitle"><span id="yorumkutusu-count">3</span> yorum</div>
      </div>
    </div>

    <div class="yorumkutusu-list-wrap">
      <div class="yorumkutusu-list" id="yorumkutusu-list">
        <!-- Örnek yorumlar (JS tarafından yönetilebilir) -->
        <div class="yorumkutusu-item">
          <div class="yorumkutusu-item-avatar">EÖ</div>
          <div class="yorumkutusu-item-body">
            <div class="yorumkutusu-item-head">
              <div class="yorumkutusu-item-name">Eren Özer</div>
              <div class="yorumkutusu-item-time"> · 2 saat önce</div>
            </div>
            <div class="yorumkutusu-item-text">Çok güzel bir yazı — anlatım akıcıydı. Özellikle örnekler çok işe yaradı.</div>
            <div class="yorumkutusu-actions">
              <button class="yorumkutusu-btn yorumkutusu-like">Beğen</button>
              <button class="yorumkutusu-btn">Cevapla</button>
            </div>
          </div>
        </div>

        <div class="yorumkutusu-item">
          <div class="yorumkutusu-item-avatar">AS</div>
          <div class="yorumkutusu-item-body">
            <div class="yorumkutusu-item-head">
              <div class="yorumkutusu-item-name">Ayşe S.</div>
              <div class="yorumkutusu-item-time"> · 1 gün önce</div>
            </div>
            <div class="yorumkutusu-item-text">Kaynakları nereden bulabilirim? Okuma listesi eklerseniz sevinirim.</div>
            <div class="yorumkutusu-actions">
              <button class="yorumkutusu-btn yorumkutusu-like">Beğen</button>
              <button class="yorumkutusu-btn">Cevapla</button>
            </div>
          </div>
        </div>

        <div class="yorumkutusu-item">
          <div class="yorumkutusu-item-avatar">MK</div>
          <div class="yorumkutusu-item-body">
            <div class="yorumkutusu-item-head">
              <div class="yorumkutusu-item-name">Mert K.</div>
              <div class="yorumkutusu-item-time"> · 3 gün önce</div>
            </div>
            <div class="yorumkutusu-item-text">Güzel olmuş, birkaç yazım hatası var ama genel olarak faydalı bir içerik.</div>
            <div class="yorumkutusu-actions">
              <button class="yorumkutusu-btn yorumkutusu-like">Beğen</button>
              <button class="yorumkutusu-btn">Cevapla</button>
            </div>
          </div>
        </div>

      </div>
    </div>

    <form class="yorumkutusu-form" id="yorumkutusu-form" onsubmit="return false;">
      <div class="yorumkutusu-input-wrap">
        <textarea class="yorumkutusu-input" id="yorumkutusu-text" placeholder="Yorumunuzu yazın..." rows="3" maxlength="800"></textarea>
        <div class="yorumkutusu-submit">
          <button class="yorumkutusu-send-btn" id="yorumkutusu-send" type="button">Gönder</button>
        </div>
        <div class="yorumkutusu-meta">Lütfen saygılı yorumlar yapmaya özen gösterin.</div>
      </div>
      
    </form>

  </div>

  <script>
    (function(){
      // Saf, tek dosya JS - yorumlar tarayıcıda tutulur (localStorage opsiyonel)
      const listEl = document.getElementById('yorumkutusu-list');
      const countEl = document.getElementById('yorumkutusu-count');
      const nameInput = document.getElementById('yorumkutusu-name');
      const textInput = document.getElementById('yorumkutusu-text');
      const sendBtn = document.getElementById('yorumkutusu-send');
      const clearBtn = document.getElementById('yorumkutusu-clear');

      // Başlangıç yorumlarını JavaScript ile yönetiyoruz (statik örnekler zaten HTML'de var)
      function updateCount(){
        const items = listEl.querySelectorAll('.yorumkutusu-item').length;
        countEl.textContent = items;
      }

      function createItem(name, text, timeLabel){
        const item = document.createElement('div');
        item.className = 'yorumkutusu-item';
        const initials = (name||'Kullanıcı').split(' ').map(s=>s[0]).slice(0,2).join('').toUpperCase();
        item.innerHTML = `
          <div class="yorumkutusu-item-avatar">${initials}</div>
          <div class="yorumkutusu-item-body">
            <div class="yorumkutusu-item-head">
              <div class="yorumkutusu-item-name">${escapeHtml(name||'Ziyaretçi')}</div>
              <div class="yorumkutusu-item-time"> · ${escapeHtml(timeLabel||'az önce')}</div>
            </div>
            <div class="yorumkutusu-item-text">${escapeHtml(text)}</div>
            <div class="yorumkutusu-actions">
              <button class="yorumkutusu-btn yorumkutusu-like">Beğen</button>
              <button class="yorumkutusu-btn">Cevapla</button>
            </div>
          </div>
        `;
        return item;
      }

      function escapeHtml(str){
        return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
      }

      // Gönder butonu davranışı
      sendBtn.addEventListener('click', ()=>{
        const name = nameInput.value.trim();
        const text = textInput.value.trim();
        if(!text){
          textInput.focus();
          return;
        }
        const timeLabel = 'az önce';
        const newItem = createItem(name || 'Ziyaretçi', text, timeLabel);
        // Yeni yorumu listenin başına ekle
        listEl.insertBefore(newItem, listEl.firstChild);
        // Temizle
        textInput.value = '';
        nameInput.value = '';
        updateCount();
        // scroll'u en üstte tutmak için
        listEl.scrollTop = 0;
      });

      clearBtn.addEventListener('click', ()=>{
        nameInput.value = '';
        textInput.value = '';
      });

      // Beğen butonları için event delegation
      listEl.addEventListener('click', (ev)=>{
        const btn = ev.target.closest('.yorumkutusu-btn');
        if(!btn) return;
        if(btn.classList.contains('yorumkutusu-like')){
          // Basit toggle
          if(btn.dataset.liked === '1'){
            btn.dataset.liked = '';
            btn.textContent = 'Beğen';
            btn.style.background = '';
            btn.style.color = '';
          } else {
            btn.dataset.liked = '1';
            btn.textContent = 'Beğenildi';
            btn.style.background = 'linear-gradient(180deg,#ffdede,#ffecec)';
            btn.style.color = '#a91b1b';
          }
        }
        if(btn.textContent.trim() === 'Cevapla'){
          // Basit: tıklanan yorumun altına alıntı ekle (örnek) - gelişmiş reply mekaniği ekleyebilirim
          const item = btn.closest('.yorumkutusu-item');
          const name = item.querySelector('.yorumkutusu-item-name').textContent || 'Kullanıcı';
          textInput.value = `@${name} `;
          textInput.focus();
        }
      });

      // Başlangıç: mevcut HTML örneklerinin sayısını güncelle
      updateCount();

    })();
  </script>

</body>
</html>