<footer class="footer mt-auto py-3 text-center" style="background: transparent; border-top: 1px solid #dbe7f3;">
  <div class="container">
    <span class="text-muted" style="color: #64748b !important; font-size: 0.85rem;">
      Copyright © <span id="year"></span>
      <a href="javascript:void(0);" class="fw-semibold nova-text-gradient" style="text-decoration:none">NovaSMM</a>.
      <span class="d-none d-md-inline">แพลตฟอร์มเติบโตบนโซเชียลมีเดีย</span>
      @if(setting('footer_text'))
        · {{ setting('footer_text') }}
      @endif
    </span>
  </div>
</footer>
<script>
  document.getElementById('year') && (document.getElementById('year').textContent = new Date().getFullYear());
</script>
