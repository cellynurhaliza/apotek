<?php

namespace App\Http\Controllers;

use PDF;
use App\Models\Medicine;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Order::with('user');

        if ($request->has('tanggal_filter')) {
            $tanggalFilter = $request->tanggal_filter;
            $query->whereDate('created_at', $tanggalFilter);
        }

        $orders = $query->simplePaginate(10);

        return view("order.kasir.index", compact("orders"));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $medicines = Medicine::all();
        return view("order.kasir.create", compact('medicines'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name_customer' => 'required',
            'medicines' => 'required',
        ]);

        $arrayDistinct = array_count_values($request->medicines); //menghitung banyak data pada array
        $arrayAssocMedicines = []; //penampung array assosiatif

        foreach ($arrayDistinct as $id => $count){
            $medicine = Medicine::where('id', $id)->first();
            $subPrice = $medicine['price'] * $count;
            $arrayItem = [
                "id" => $id,
                // "name_customer" => $medicine['name_customer'],
                "nama_medicine" => $medicine['name'],
                "qty" => $count,
                "price" => $medicine['price'],
                "sub_price" => $subPrice,
            ];
            
            array_push($arrayAssocMedicines, $arrayItem);
        }

        $totalPrice = 0;

        foreach ($arrayAssocMedicines as $item){
            $totalPrice += (int)$item['sub_price'];
        }

        $priceWithPPN = $totalPrice + ($totalPrice * 0.01);
        $proses = Order::create([
            'user_id' => Auth::user()->id,
            'medicines' => $arrayAssocMedicines,
            'name_customer' => $request->name_customer,
            'total_price' => $priceWithPPN,
        ]);

        if ($proses) {
            $order = Order::where('user_id', Auth::user()->id)->orderBy('created_at', 'DESC')->first();
            return redirect()->route('kasir.order.print', $order['id']);
        }else{
            return redirect()->back()->with('failed', 'Gagal membuat data pembelian. Silahkan coba kembali dengan data yang sesuai!');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $order = Order::find($id);
        return view('order.kasir.print', compact('order'));
    }

    public function downloadPDF($id){
        $order = Order::find($id)->toArray();
        view()->share('order', $order);
        $pdf = PDF::loadView('order.kasir.download-pdf', $order);
        return $pdf->download('receipt.pdf');
    }

    public function detail($id){
        $orders = Order::find($id);
        return view('order.kasir.detail', compact('orders'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Order $order)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Order $order)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Order $order, $id)
    {
        Order::where('id', $id)->delete();
        return redirect()->back()->with('deleted', 'Data berhasil di hapus!');
    }

    public function data()
    {
        $orders = Order::with('user')->simplePaginate(5);
        return view("order.admin.index", compact('orders'));
    }
}
