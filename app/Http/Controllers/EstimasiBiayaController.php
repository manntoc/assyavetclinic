<?php

namespace App\Http\Controllers;

use App\Models\EstimasiBiaya;
use App\Models\Owner;
use App\Models\booking;
use App\Models\Inventori;
use App\Models\layanan;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;
use Exception;

class EstimasiBiayaController extends Controller
{
    /**
     * Display a listing of estimation quotes.
     */
    public function index(Request $request)
    {
        $query = EstimasiBiaya::latest();

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('kode_estimasi', 'LIKE', "%{$search}%")
                  ->orWhere('nama_owner', 'LIKE', "%{$search}%")
                  ->orWhere('nama_hewan', 'LIKE', "%{$search}%")
                  ->orWhere('telepon', 'LIKE', "%{$search}%");
            });
        }

        $estimasis = $query->paginate(15)->withQueryString();

        return view('estimasi_biaya.index', compact('estimasis'));
    }

    /**
     * Show form to create a new cost estimate.
     */
    public function create(Request $request)
    {
        $owners = Owner::with('pets')->orderBy('nama', 'asc')->get();
        $layananList = layanan::orderBy('nama', 'asc')->get();
        $inventoriList = Inventori::orderBy('nama_barang', 'asc')->get();

        $selectedBooking = null;
        if ($request->filled('booking_id')) {
            $selectedBooking = booking::with(['owner', 'ownerPet'])->find($request->booking_id);
        }

        return view('estimasi_biaya.create', compact('owners', 'layananList', 'inventoriList', 'selectedBooking'));
    }

    /**
     * Store a newly created cost estimate in storage.
     * Note: Zero stock reduction. Product inventory remains completely untouched.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama_owner'  => 'required|string|max:255',
            'nama_hewan'  => 'required|string|max:255',
            'telepon'     => 'nullable|string|max:50',
            'jenis_hewan' => 'nullable|string|max:100',
            'berat_hewan' => 'nullable|string|max:50',
            'dokter'      => 'nullable|string|max:255',
            'diskon'      => 'nullable|numeric|min:0',
            'pajak'       => 'nullable|numeric|min:0',
            'catatan'     => 'nullable|string',
            'items'       => 'required|array|min:1',
            'items.*.nama'  => 'required|string',
            'items.*.type'  => 'required|string',
            'items.*.qty'   => 'required|numeric|min:1',
            'items.*.harga' => 'required|numeric|min:0',
        ]);

        try {
            $kodeEstimasi = 'EST-' . date('Ymd') . '-' . strtoupper(Str::random(4));

            $subtotalLayanan = 0;
            $subtotalObat = 0;
            $processedItems = [];

            foreach ($request->items as $item) {
                $qty = (float) $item['qty'];
                $harga = (float) $item['harga'];
                $subtotal = $qty * $harga;
                $type = $item['type'] ?? 'custom';

                if ($type === 'layanan') {
                    $subtotalLayanan += $subtotal;
                } else if ($type === 'obat') {
                    $subtotalObat += $subtotal;
                } else {
                    $subtotalLayanan += $subtotal;
                }

                $processedItems[] = [
                    'type'     => $type,
                    'item_id'  => $item['item_id'] ?? null,
                    'nama'     => $item['nama'],
                    'qty'      => $qty,
                    'harga'    => $harga,
                    'subtotal' => $subtotal,
                ];
            }

            $diskon = (float) ($request->diskon ?? 0);
            $pajak = (float) ($request->pajak ?? 0);
            $grandTotal = max(0, ($subtotalLayanan + $subtotalObat) - $diskon + $pajak);

            $estimasi = EstimasiBiaya::create([
                'kode_estimasi'    => $kodeEstimasi,
                'booking_id'       => $request->booking_id ?: null,
                'owner_id'         => $request->owner_id ?: null,
                'nama_owner'       => $request->nama_owner,
                'telepon'          => $request->telepon,
                'nama_hewan'       => $request->nama_hewan,
                'jenis_hewan'      => $request->jenis_hewan,
                'berat_hewan'      => $request->berat_hewan,
                'dokter'           => $request->dokter,
                'subtotal_layanan' => $subtotalLayanan,
                'subtotal_obat'    => $subtotalObat,
                'diskon'           => $diskon,
                'pajak'            => $pajak,
                'grand_total'      => $grandTotal,
                'catatan'          => $request->catatan,
                'items'            => $processedItems,
                'created_by'       => auth()->check() ? auth()->user()->name : 'Admin',
            ]);

            if ($request->input('action') === 'save_and_print') {
                return redirect()->route('estimasi-biaya.pdf', $estimasi->id);
            }

            return redirect()->route('estimasi-biaya.index')
                ->with('success', 'Nota Perkiraan Biaya (' . $kodeEstimasi . ') berhasil dibuat tanpa mengurangi stok inventori.');

        } catch (Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Gagal membuat nota perkiraan: ' . $e->getMessage());
        }
    }

    /**
     * Show form to edit an existing estimate.
     */
    public function edit($id)
    {
        $estimasi = EstimasiBiaya::findOrFail($id);
        $owners = Owner::with('pets')->orderBy('nama', 'asc')->get();
        $layananList = layanan::orderBy('nama', 'asc')->get();
        $inventoriList = Inventori::orderBy('nama_barang', 'asc')->get();

        return view('estimasi_biaya.edit', compact('estimasi', 'owners', 'layananList', 'inventoriList'));
    }

    /**
     * Update the specified estimate in storage.
     */
    public function update(Request $request, $id)
    {
        $estimasi = EstimasiBiaya::findOrFail($id);

        $request->validate([
            'nama_owner'  => 'required|string|max:255',
            'nama_hewan'  => 'required|string|max:255',
            'telepon'     => 'nullable|string|max:50',
            'jenis_hewan' => 'nullable|string|max:100',
            'berat_hewan' => 'nullable|string|max:50',
            'dokter'      => 'nullable|string|max:255',
            'diskon'      => 'nullable|numeric|min:0',
            'pajak'       => 'nullable|numeric|min:0',
            'catatan'     => 'nullable|string',
            'items'       => 'required|array|min:1',
            'items.*.nama'  => 'required|string',
            'items.*.type'  => 'required|string',
            'items.*.qty'   => 'required|numeric|min:1',
            'items.*.harga' => 'required|numeric|min:0',
        ]);

        try {
            $subtotalLayanan = 0;
            $subtotalObat = 0;
            $processedItems = [];

            foreach ($request->items as $item) {
                $qty = (float) $item['qty'];
                $harga = (float) $item['harga'];
                $subtotal = $qty * $harga;
                $type = $item['type'] ?? 'custom';

                if ($type === 'layanan') {
                    $subtotalLayanan += $subtotal;
                } else if ($type === 'obat') {
                    $subtotalObat += $subtotal;
                } else {
                    $subtotalLayanan += $subtotal;
                }

                $processedItems[] = [
                    'type'     => $type,
                    'item_id'  => $item['item_id'] ?? null,
                    'nama'     => $item['nama'],
                    'qty'      => $qty,
                    'harga'    => $harga,
                    'subtotal' => $subtotal,
                ];
            }

            $diskon = (float) ($request->diskon ?? 0);
            $pajak = (float) ($request->pajak ?? 0);
            $grandTotal = max(0, ($subtotalLayanan + $subtotalObat) - $diskon + $pajak);

            $estimasi->update([
                'nama_owner'       => $request->nama_owner,
                'telepon'          => $request->telepon,
                'nama_hewan'       => $request->nama_hewan,
                'jenis_hewan'      => $request->jenis_hewan,
                'berat_hewan'      => $request->berat_hewan,
                'dokter'           => $request->dokter,
                'subtotal_layanan' => $subtotalLayanan,
                'subtotal_obat'    => $subtotalObat,
                'diskon'           => $diskon,
                'pajak'            => $pajak,
                'grand_total'      => $grandTotal,
                'catatan'          => $request->catatan,
                'items'            => $processedItems,
            ]);

            if ($request->input('action') === 'save_and_print') {
                return redirect()->route('estimasi-biaya.pdf', $estimasi->id);
            }

            return redirect()->route('estimasi-biaya.index')
                ->with('success', 'Nota Perkiraan Biaya (' . $estimasi->kode_estimasi . ') berhasil diperbarui.');

        } catch (Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Gagal memperbarui nota perkiraan: ' . $e->getMessage());
        }
    }

    /**
     * Remove estimate from storage.
     */
    public function destroy($id)
    {
        try {
            $estimasi = EstimasiBiaya::findOrFail($id);
            $kode = $estimasi->kode_estimasi;
            $estimasi->delete();

            return redirect()->route('estimasi-biaya.index')
                ->with('success', 'Nota Perkiraan Biaya (' . $kode . ') berhasil dihapus.');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghapus nota perkiraan: ' . $e->getMessage());
        }
    }

    /**
     * Generate & stream PDF document for cost estimate quote.
     */
    public function pdf($id)
    {
        $estimasi = EstimasiBiaya::findOrFail($id);

        $html = view('PDF.nota_estimasi', compact('estimasi'))->render();

        $pdf = Pdf::loadHTML($html);
        $pdf->setBasePath(public_path());
        $pdf->set_option('isRemoteEnabled', true);

        return $pdf->stream('Nota_Estimasi_' . $estimasi->kode_estimasi . '.pdf');
    }
}
