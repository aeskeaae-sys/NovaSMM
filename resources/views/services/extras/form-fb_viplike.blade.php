<input type="hidden" id="form_viplike_loaded" value="1">
<div class="fw-bold text-danger text-center mb-2">
  {{ __t('จำนวนไลก์ต้องเป็นพหุคูณของ 50 (50 100 150 200...)') }}
</div>
<div class="row mb-3">
  <div class="col-md-6">
    <label for="duration" class="form-label">{{ __('ระยะเวลาเช่า') }} : </label>
    <select name="duration" id="duration" class="form-select">
      <option value="7">7 {{ __t('วัน') }}</option>
      <option value="15">15 {{ __t('วัน') }}</option>
      <option value="30">30 {{ __t('วัน') }}</option>
      <option value="60">60 {{ __t('วัน') }}</option>
      <option value="90">90 {{ __t('วัน') }}</option>
    </select>
  </div>
  <div class="col-md-6">
    <label for="num_post" class="form-label">{{ __t('จำนวนบทความ / วัน') }} : </label>
    <select name="num_post" id="num_post" class="form-select">
      <option value="1">1 {{ __t('บทความ') }}</option>
      <option value="2">2 {{ __t('บทความ') }}</option>
      <option value="3">3 {{ __t('บทความ') }}</option>
      <option value="4">4 {{ __t('บทความ') }}</option>
      <option value="5">5 {{ __t('บทความ') }}</option>
      <option value="6">6 {{ __t('บทความ') }}</option>
      <option value="7">7 {{ __t('บทความ') }}</option>
      <option value="8">8 {{ __t('บทความ') }}</option>
      <option value="9">9 {{ __t('บทความ') }}</option>
      <option value="10">10 {{ __t('บทความ') }}</option>
    </select>
  </div>
</div>
<script>
  $(document).ready(() => {
    "use strict";

    $("#num_post").change(() => SUM_PRICE_FNC())
    $("#duration").change(() => SUM_PRICE_FNC())
  })
</script>
