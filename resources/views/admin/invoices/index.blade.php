@php use App\Helpers\Helper; @endphp
@extends('admin.layouts.master')
@section('title', 'Admin: Invoices Management')
@section('content')
  <div class="card custom-card nova-admin-card">
    <div class="card-header justify-content-between">
      <div class="card-title">จัดการใบแจ้งหนี้</div>
    </div>
    <div class="card-body">
      <div class="table-responsive theme-scrollbar">
        <table class="display table table-bordered table-stripped text-nowrap datatable">
          <thead>
            <tr>
              <th>#</th>
              <th>ดำเนินการ</th>
              <th>รหัสใบแจ้งหนี้</th>
              <th>จำนวนเงิน</th>
              <th>ประเภทสกุลเงิน</th>
              <th>ประเภทธุรกรรม</th>
              <th>ลูกค้า</th>
              <th>สถานะ</th>
              <th>คงเหลือ</th>
              <th>หมายเหตุ</th>
              <th>วันที่สร้าง</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($invoices as $item)
              <tr>
                <td>{{ $item->id }}</td>
                <td>
                  @if ($item->status === 'processing')
                    <a href="javascript:setStatus({{ $item->id }}, 'paid')" class="shadow text-white badge bg-success-gradient"><i class="fas fa-thumbs-up"></i></a>
                    <a href="javascript:setStatus({{ $item->id }}, 'cancelled')" class="shadow text-white badge bg-danger-gradient"><i class="fas fa-trash"></i></a>
                  @endif
                </td>
                <td>{{ $item->code }}</td>
                <td>{{ formatCurrency($item->amount) }}</td>
                <td>{{ $item->currency }}</td>
                <td>{{ Helper::formatTransType($item->type) }}</td>
                <td>{{ $item->username }}</td>
                <td>{!! Helper::formatStatus($item->status) !!}</td>
                <td>
                  @if ($item->is_expired)
                    <span class="text-danger">-</span>
                  @else
                    {{ $item->expired_str }}
                  @endif
                </td>
                <td>{{ $item->description }}</td>
                <td>{{ $item->created_at }}</td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>
@endsection
@section('scripts')

  <script>
    const setStatus = async (id, type) => {
      let simpleText = type === 'paid' ? 'ชำระเงินแล้ว' : 'ยกเลิกแล้ว'

      const confirm = await Swal.fire({
        title: 'คุณแน่ใจหรือไม่?',
        text: `เปลี่ยนสถานะใบแจ้งหนี้นี้เป็น ${simpleText}?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'ตกลง',
        cancelButtonText: 'ยกเลิก',
      })

      if (confirm.isConfirmed !== true)
        return

      try {
        const {
          data: result
        } = await axios.post('{{ route('admin.invoices.update') }}', {
          id,
          type
        })

        Swal.fire("สำเร็จ!", result.message, "success").then(() => {
          window.location.reload()
        })

      } catch (error) {
        Swal.fire("ล้มเหลว", $catchMessage(error), "error")
      }

    }
  </script>
@endsection
