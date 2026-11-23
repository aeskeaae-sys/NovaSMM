@php
  use App\Helpers\Helper;
@endphp
@extends('admin.layouts.master')
@section('title', $pageTitle)
@section('content')
  @foreach ($logs as $name => $data)
    <div class="card custom-card nova-admin-card">
      <div class="card-header justify-content-between">
        <div class="card-title">ประวัติการรับเงินผ่าน <span class="text-danger">{{ $name }}</span></div>
      </div>
      <div class="card-body">
        <div class="table-responsive theme-scrollbar">
          <table class="display table table-bordered table-stripped text-nowrap datatable">
            <thead>
              <tr>
                <th>#</th>
                <th>เวลา</th>
                <th>ธนาคาร</th>
                <th>จำนวนเงินฝาก</th>
                <th>รหัสธุรกรรม</th>
                <th>เนื้อหา</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($data as $log)
                <tr>
                  <td>{{ $log->id }}</td>
                  <td>{{ $log->created_at }} <small>({{ Helper::getTimeAgo($log->created_at) }})</small></td>
                  <td>{{ $log->name }}</td>
                  <td><b class="text-success">{{ formatCurrency($log->amount) }}</b></td>
                  <td>{{ $log->code }}</td>
                  <td>{{ $log->value }}</td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </div>
  @endforeach
@endsection
