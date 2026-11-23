@php use App\Helpers\Helper; @endphp
@extends('admin.layouts.master')
@section('title', $pageTitle)
@section('content')
  <div class="card custom-card nova-admin-card">
    <div class="card-body">
      <div class="alert alert-danger">
        {{ __t('หมายเหตุ: ฟังก์ชันนี้ไม่สามารถเปลี่ยนสกุลเงินเริ่มต้นของระบบได้') }}
      </div>
      <form action="{{ route('admin.settings.currencies.update') }}" method="POST" class="axios-form" data-reload="1">
        @csrf
        <div class="row mb-3">
          <div class="col-md-4">
            <label for="currency_code" class="form-label">{{ __t('รหัสสกุลเงิน') }}</label>
            <select name="currency_code" id="currency_code" class="form-control">
              {{-- <option value="VND" @if (isset($config['currency_code']) ? $config['currency_code'] === 'VND' : false) selected @endif> VND - เงินดองเวียดนาม</option> --}}
              @foreach (currency_codes() as $code => $name)
                <option value="{{ $code }}" @if (isset($config['currency_code']) ? $config['currency_code'] === $code : false) selected @endif>{{ $code }} - {{ $name }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-md-4">
            <div class="form-group">
              <label class="form-label" for="currency_symbol">{{ __t('สัญลักษณ์สกุลเงิน') }}</label>
              <input class="form-control" name="currency_symbol" id="currency_symbol" value="{{ $config['currency_symbol'] ?? '₫' }}">
            </div>
          </div>
          <div class="col-md-4">
            <div class="form-group">
              <label class="form-label" for="currency_position">{{ __t('ตำแหน่งของสัญลักษณ์สกุลเงิน') }}</label>
              <select name="currency_position" id="currency_position" class="form-control square">
                <option value="left" @if (isset($config['currency_position']) ? $config['currency_position'] === 'left' : false) selected @endif> {{ __t('ซ้าย') }}</option>
                <option value="right" @if (isset($config['currency_position']) ? $config['currency_position'] === 'right' : false) selected @endif> {{ __t('ขวา') }}</option>
              </select>
            </div>
          </div>
        </div>
        <div class="row mb-3">

          <div class="col-md-4">
            <div class="form-group">
              <label class="form-label" for="currency_thousand_separator">{{ __t('ตัวคั่นหลักพัน') }}</label>
              <select name="currency_thousand_separator" id="currency_thousand_separator" value="{{ $config['currency_thousand_separator'] ?? '' }}" class="form-control square">
                <option value="dot" @if (isset($config['currency_thousand_separator']) ? $config['currency_thousand_separator'] === 'dot' : false) selected @endif> Dot</option>
                <option value="comma" @if (isset($config['currency_thousand_separator']) ? $config['currency_thousand_separator'] === 'comma' : false) selected @endif> Comma</option>
                <option value="space" @if (isset($config['currency_thousand_separator']) ? $config['currency_thousand_separator'] === 'space' : false) selected @endif> Space</option>
              </select>
            </div>
          </div>
          <div class="col-md-4">
            <div class="form-group">
              <label for="currency_decimal_separator" class="form-label">{{ __t('ตัวคั่นทศนิยม') }}</label>
              <select name="currency_decimal_separator" id="currency_decimal_separator" class="form-control square">
                <option value="dot" @if (isset($config['currency_decimal_separator']) ? $config['currency_decimal_separator'] === 'dot' : false) selected @endif> Dot</option>
                <option value="comma" @if (isset($config['currency_decimal_separator']) ? $config['currency_decimal_separator'] === 'comma' : false) selected @endif> Comma</option>
              </select>
            </div>
          </div>
          <div class="col-md-4">
            <div class="form-group">
              <label class="form-label" for="currency_decimal">{{ __('จำนวนตำแหน่งทศนิยมของสกุลเงิน') }}</label>
              <select name="currency_decimal" id="currency_decimal" class="form-control square">
                <option value="0" @if (isset($config['currency_decimal']) ? $config['currency_decimal'] === '0' : false) selected @endif> 0</option>
                <option value="1" @if (isset($config['currency_decimal']) ? $config['currency_decimal'] === '1' : false) selected @endif> 0.0</option>
                <option value="2" @if (isset($config['currency_decimal']) ? $config['currency_decimal'] === '2' : false) selected @endif> 0.00</option>
                <option value="3" @if (isset($config['currency_decimal']) ? $config['currency_decimal'] === '3' : false) selected @endif> 0.000</option>
                <option value="4" @if (isset($config['currency_decimal']) ? $config['currency_decimal'] === '4' : false) selected @endif> 0.0000</option>
              </select>
            </div>
          </div>

        </div>

        <div class="row mb-3">

          <div class="col-md-4">
            <label class="form-label" for="default_price_percentage_increase">{{ __t('ใช้สำหรับซิงก์และเพิ่มบริการเป็นชุด') }}</label>
            <input type="number" class="form-control" name="default_price_percentage_increase" id="default_price_percentage_increase" value="{{ $config['default_price_percentage_increase'] ?? 0 }}">
          </div>

          <div class="col-md-4">
            <label class="form-label" for="auto_rounding_x_decimal_places">{{ __t('ปัดเศษอัตโนมัติถึง X ตำแหน่งทศนิยม') }}</label>
            <select name="auto_rounding_x_decimal_places" id="auto_rounding_x_decimal_places" class="form-control square">
              <option value="1" @if (isset($config['auto_rounding_x_decimal_places']) ? $config['auto_rounding_x_decimal_places'] === '1' : false) selected @endif>1</option>
              <option value="2" @if (isset($config['auto_rounding_x_decimal_places']) ? $config['auto_rounding_x_decimal_places'] === '2' : false) selected @endif>2</option>
              <option value="3" @if (isset($config['auto_rounding_x_decimal_places']) ? $config['auto_rounding_x_decimal_places'] === '3' : false) selected @endif>3</option>
              <option value="4" @if (isset($config['auto_rounding_x_decimal_places']) ? $config['auto_rounding_x_decimal_places'] === '4' : false) selected @endif>4</option>
            </select>
          </div>

          <div class="col-md-4">
            <label class="form-label" for="new_currecry_rate">{{ __t('อัตราแลกเปลี่ยนสกุลเงิน') }} ({{ __t('ใช้เมื่อดึงข้อมูลและซิงก์บริการทั้งหมดจากผู้ให้บริการ SMM') }})</label>
            <input type="number" class="form-control" name="new_currecry_rate" id="new_currecry_rate" value="{{ $config['new_currecry_rate'] ?? 1 }}" required>
            <small class="text-muted"><span class="text-danger">*</span> {{ __t('หากไม่ต้องการเปลี่ยนอัตราแลกเปลี่ยนสกุลเงิน ให้ตั้งค่านี้เป็น 1') }}</small>
          </div>
        </div>

        <div class="text-center mb-3">
          <h3>{!! __t('รูปแบบตัวอย่าง: :example', ['example' => '<span class="text-danger">' . formatCurrency(1000000) . '</span>']) !!}</h3>
        </div>

        <div class="mb-3">
          <button class="btn btn-primary w-100" type="submit">อัปเดต</button>
        </div>
      </form>
    </div>
  </div>
@endsection
