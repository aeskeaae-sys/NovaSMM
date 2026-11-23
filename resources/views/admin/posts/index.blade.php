@extends('admin.layouts.master')
@section('title', 'Admin: Posts Management')
@section('content')
  <div class="card custom-card nova-admin-card">
    <div class="card-header justify-content-between">
      <div class="card-title">จัดการรายการบทความ</div>
    </div>
    <div class="card-body">
      <div class="table-responsive theme-scrollbar">
        <table class="display table table-bordered table-stripped text-nowrap datatable">
          <thead>
            <tr>
              <th>#</th>
              <th>ดำเนินการ</th>
              <th>ภาพปก</th>
              <th>บทความ</th>
              <th>สถานะ</th>
              <th>ผู้เขียน</th>
              <th>วันที่เขียน</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($posts as $item)
              <tr>
                <td>{{ $item->id }}</td>
                <td>
                  <a href="{{ route('admin.posts.show', ['id' => $item->id]) }}" class="shadow btn btn-primary btn-sm sharp me-1"><i class="fa fa-pencil"></i> แก้ไข</a>
                  <a href="javascript:deleteRow({{ $item->id }})" class="shadow btn btn-danger btn-sm sharp me-1"><i class="fa fa-trash"></i> ลบ</a>
                </td>
                <td>
                  <img src="{{ asset($item->thumbnail) }}" width="25">
                </td>
                <td>{{ $item->title }}</td>
                <td>
                  @if ($item->status === true)
                    <span class="text-success">แสดงอยู่</span>
                  @else
                    <span class="text-danger">ซ่อนอยู่</span>
                  @endif
                </td>
                <td>{{ $item->author_name }}</td>
                <td>{{ $item->created_at }}</td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
    <div class="card-footer">
      <a href="{{ route('admin.posts.create') }}" class="btn btn-primary">สร้างบทความใหม่</a>
    </div>
  </div>
@endsection
@section('scripts')
  <script>
    const deleteRow = async (id) => {
      const confirmDelete = await Swal.fire({
        title: 'คุณแน่ใจหรือต้องการลบ?',
        text: "คุณจะไม่สามารถกู้คืนข้อมูลนี้ได้!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'ลบ',
        cancelButtonText: 'ยกเลิก'
      });

      if (!confirmDelete.isConfirmed) return;

      $showLoading();

      try {
        const {
          data: result
        } = await axios.post('{{ route('admin.posts.delete') }}', {
          id
        })

        Swal.fire('สำเร็จ', result.message, 'success').then(() => {
          window.location.reload();
        })
      } catch (error) {
        Swal.fire('ล้มเหลว', $catchMessage(error), 'error')
      }
    }
  </script>
@endsection
