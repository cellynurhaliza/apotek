@extends('layouts.template')

@section('content')
    <div class="my-5 d-flex justify-content-end">
        <a href="" class="btn btn-primary">Export Data (excel)</a>
    </div>
    <table class="table table-stripped table-bordered">
        <thead>
            <tr>
                <th>No</th>
                <th>Pembeli</th>
                <th>Obat</th>
                <th>Kasir</th>
                <th>Tanggal Pembelian</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($orders as $order)
                <tr>
                    {{-- menampilkan angka urutan berdasarkan page pagination (digunakan ketika mengambil data
                    dengan paginate / simplePaginate) --}}
                    <td>{{ ($orders->currentpage()-1) * $orders->perpage() + $loop->index + 1 }}</td>
                    <td>{{ $orders->name_customer }}</td>
                    <td>
                        {{-- nested loop : didalam looping ada looping --}}
                        {{-- karna column medicines tipe datanya berbentuk array json, maka untuk mengaksesnya
                        perlu di looping jg --}}
                        <ol>
                            @foreach ($orders['medicines'] as $medicine)
                                <li>
                                    {{-- hasil yang diinginkan : --}}
                                    {{-- 1. nama obat (Rp. 3000) : Rp. 15000 qty 1 --}}
                                    {{ $medicine['name_medicine'] }}
                                    ( Rp. {{ number_format($medicine['price'], 0, ',', '.') }} ) :
                                    Rp. {{ number_format($medicine['sub_price'], 0, ',', '.') }}
                                    <small>qty {{ $medicine['qty'] }}</small>
                                </li>
                            @endforeach
                        </ol>
                    </td>
                    <td>{{ $orders['user']['name'] }}</td>
                    {{-- carbon : package bawaan laravel untuk mengatur hal-hal yang berkaitan denga tipe data date/datetime --}}
                    @php 
                        // setting lokal time sebagai wilayah indonesia
                        setlocale(LC_ALL, 'IND')
                    @endphp
                    <td>{{ Carbon\Carbon::parse($order->created_at)->formatLocalized('%d %B %y') }}</td>
                    <td><a href="{{ route('kasir.order.download', $order['id']) }}" class="btn btn-secondary">Unduk (.pdf)</a></td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection