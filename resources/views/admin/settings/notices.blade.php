@extends('admin.layouts.master')
@section('title', 'Admin: Notices Settings')
@section('content')
  <div class="card custom-card nova-admin-card">
    <div class="card-header justify-content-between">
      <div class="card-title">{{ __t('ประกาศ') }} | {{ __t('หน้าสร้างคำสั่งซื้อ') }}</div>
    </div>
    <div class="card-body">
      <form action="{{ route('admin.settings.notices.update', ['type' => 'page_new_order']) }}" method="POST">
        @csrf
        <div class="mb-3">
          <textarea class="form-control" id="content" name="content" rows="5">{{ $page_new_order ?? '' }}</textarea>
        </div>
        <div class="mb-3 text-center">
          <button class="btn btn-primary" type="submit">อัปเดต</button>
        </div>
      </form>
    </div>
  </div>
  <div class="card custom-card">
    <div class="card-header justify-content-between">
      <div class="card-title">{{ __t('ประกาศ') }} | แสดงบนหน้าแรก</div>
    </div>
    <div class="card-body">
      <form action="{{ route('admin.settings.notices.update', ['type' => 'modal_dashboard']) }}" method="POST">
        @csrf
        <div class="mb-3">
          <textarea class="form-control" id="content" name="content" rows="5">{{ $modal_dashboard ?? '' }}</textarea>
        </div>
        <div class="mb-3 text-center">
          <button class="btn btn-primary" type="submit">อัปเดต</button>
        </div>
      </form>
    </div>
  </div>
  <div class="card custom-card">
    <div class="card-header justify-content-between">
      <div class="card-title">{{ __t('ประกาศ') }} | หน้าฝากเงิน</div>
    </div>
    <div class="card-body">
      <form action="{{ route('admin.settings.notices.update', ['type' => 'page_deposit']) }}" method="POST">
        @csrf
        <div class="mb-3">
          <textarea class="form-control" id="content" name="content" rows="5">{{ $page_deposit ?? '' }}</textarea>
        </div>
        <div class="mb-3 text-center">
          <button class="btn btn-primary" type="submit">อัปเดต</button>
        </div>
      </form>
    </div>
  </div>
  <div class="card custom-card">
    <div class="card-header justify-content-between">
      <div class="card-title">{{ __t('ประกาศ') }} | หน้าฝากเงินผ่าน crypto</div>
    </div>
    <div class="card-body">
      <form action="{{ route('admin.settings.notices.update', ['type' => 'page_deposit_crypto']) }}" method="POST">
        @csrf
        <div class="mb-3">
          <textarea class="form-control" id="content" name="content" rows="5">{{ $page_deposit_crypto ?? '' }}</textarea>
        </div>
        <div class="mb-3 text-center">
          <button class="btn btn-primary" type="submit">อัปเดต</button>
        </div>
      </form>
    </div>
  </div>
  <div class="card custom-card">
    <div class="card-header justify-content-between">
      <div class="card-title">{{ __t('ประกาศ') }} | หน้าฝากเงินผ่านบัตรเติมเงิน</div>
    </div>
    <div class="card-body">
      <form action="{{ route('admin.settings.notices.update', ['type' => 'page_deposit_card']) }}" method="POST">
        @csrf
        <div class="mb-3">
          <textarea class="form-control" id="content" name="content" rows="5">{{ $page_deposit_card ?? '' }}</textarea>
        </div>
        <div class="mb-3 text-center">
          <button class="btn btn-primary" type="submit">อัปเดต</button>
        </div>
      </form>
    </div>
  </div>
  <div class="card custom-card">
    <div class="card-header justify-content-between">
      <div class="card-title">{{ __t('ประกาศ') }} | หน้าโปรแกรม Affiliate</div>
    </div>
    <div class="card-body">
      <form action="{{ route('admin.settings.notices.update', ['type' => 'page_affiliate']) }}" method="POST">
        @csrf
        <div class="mb-3">
          <textarea class="form-control" id="content" name="content" rows="5">{{ $page_affiliate ?? '' }}</textarea>
        </div>
        <div class="mb-3 text-center">
          <button class="btn btn-primary" type="submit">อัปเดต</button>
        </div>
      </form>
    </div>
  </div>
  <div class="card custom-card">
    <div class="card-header justify-content-between">
      <div class="card-title">{{ __t('ประกาศ') }} | ข้อกำหนดการใช้งาน</div>
    </div>
    <div class="card-body">
      <form action="{{ route('admin.settings.notices.update', ['type' => 'page_privacy_policy']) }}" method="POST">
        @csrf
        <div class="mb-3">
          <textarea class="form-control" id="content" name="content" rows="5">{{ $page_privacy_policy ?? '' }}</textarea>
        </div>
        <div class="mb-3 text-center">
          <button class="btn btn-primary" type="submit">อัปเดต</button>
        </div>
      </form>
    </div>
  </div>
  <div class="card custom-card">
    <div class="card-header justify-content-between">
      <div class="card-title">ข้อมูลติดต่อ</div>
    </div>
    <div class="card-body">
      <form action="{{ route('admin.settings.notices.update', ['type' => 'page_supports']) }}" method="POST">
        @csrf
        <div class="mb-3">
          <textarea class="form-control" id="content" name="content" rows="5">{{ $page_supports ?? '' }}</textarea>
        </div>
        <div class="mb-3 text-center">
          <button class="btn btn-primary" type="submit">อัปเดต</button>
        </div>
      </form>
    </div>
  </div>
@endsection
@section('scripts')
  <script src="/plugins/ckeditor/ckeditor.js"></script>

  <script>
    $(function() {
      const editors = document.querySelectorAll('[id=content]');

      console.log(editors);
      for (const editor of editors) {
        const inde = CKEDITOR.replace(editor, {
          extraPlugins: 'notification',
          clipboard_handleImages: false,
          filebrowserImageUploadUrl: '/api/admin/tools/upload?form=ckeditor'
        });

        inde.on('fileUploadRequest', function(evt) {
          var xhr = evt.data.fileLoader.xhr;

          xhr.setRequestHeader('Cache-Control', 'no-cache');
          xhr.setRequestHeader('Authorization', 'Bearer ' + userData.access_token);
        })
      }
    })
  </script>
@endsection
