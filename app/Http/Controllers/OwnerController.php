<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Owner;
use App\Models\OwnerPet;
use Illuminate\Support\Facades\DB;

class OwnerController extends Controller
{
    public function owner()
    {
        $owners = Owner::with('pets')->latest()->get();

        return view('owner.index', compact('owners'));
    }

    public function createowner()
    {
        return view('owner.create');
    }

    public function storeowner(Request $request)
    {
        $request->validate([
            'nama' => 'required',
            'telepon' => 'required',
            'alamat' => 'required',
            'pets' => 'required|array|min:1',
            'pets.*.nama' => 'required',
            'pets.*.jenis_hewan' => 'required',
        ]);

        DB::transaction(function () use ($request) {

            $owner = Owner::create([
                'nama' => $request->nama,
                'telepon' => $request->telepon,
                'email' => $request->email,
                'alamat' => $request->alamat,
                'catatan_khusus' => $request->catatan_khusus ?? null,
            ]);

            foreach ($request->pets as $pet) {

                $owner->pets()->create([
                    'nama' => $pet['nama'],
                    'jenis_hewan' => $pet['jenis_hewan'],
                    'spesies' => $pet['spesies'] ?? $pet['jenis_hewan'],
                    'berat_hewan' => $pet['berat_hewan'] ?? null,
                    'ras_hewan' => $pet['ras_hewan'] ?? null,
                    'jenis_kelamin' => $pet['jenis_kelamin'] ?? null,
                    'tanggal_lahir' => $pet['tanggal_lahir'] ?? null,
                    'umur' => $pet['umur'] ?? null,
                    'warna' => $pet['warna'] ?? null,
                    'foto_hewan' => $pet['foto_hewan'] ?? null,
                ]);
            }
        });

        return redirect()
            ->route('owner')
            ->with('success', 'Data owner dan hewan berhasil disimpan.');
    }

    public function showowner($id)
    {
        $owner = Owner::with('pets')->findOrFail($id);

        return view('owner.show', compact('owner'));
    }

    public function editowner($id)
    {
        $owner = Owner::with('pets')->findOrFail($id);

        return view('owner.edit', compact('owner'));
    }

    public function updateowner(Request $request, $id)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'telepon' => 'required|max:20',
            'email' => 'nullable|email',
            'alamat' => 'required|string',
            'pets' => 'required|array|min:1',
            'pets.*.nama' => 'required|string|max:255',
            'pets.*.jenis_hewan' => 'required|string|max:255',
        ]);

        DB::transaction(function () use ($request, $id) {

            $owner = Owner::with('pets')->findOrFail($id);

            $owner->update([
                'nama' => $request->nama,
                'telepon' => $request->telepon,
                'email' => $request->email,
                'alamat' => $request->alamat,
                'catatan_khusus' => $request->catatan_khusus ?? $owner->catatan_khusus,
            ]);

            $requestPetIds = collect($request->pets)
                ->pluck('id')
                ->filter()
                ->toArray();

            $owner->pets()
                ->whereNotIn('id', $requestPetIds)
                ->delete();

            foreach ($request->pets as $pet) {

                $petData = [
                    'nama' => $pet['nama'],
                    'jenis_hewan' => $pet['jenis_hewan'],
                    'spesies' => $pet['spesies'] ?? $pet['jenis_hewan'],
                    'berat_hewan' => $pet['berat_hewan'] ?? null,
                    'ras_hewan' => $pet['ras_hewan'] ?? null,
                    'jenis_kelamin' => $pet['jenis_kelamin'] ?? null,
                    'tanggal_lahir' => $pet['tanggal_lahir'] ?? null,
                    'umur' => $pet['umur'] ?? null,
                    'warna' => $pet['warna'] ?? null,
                    'foto_hewan' => $pet['foto_hewan'] ?? null,
                ];

                if (!empty($pet['id'])) {

                    OwnerPet::where('id', $pet['id'])
                        ->where('owner_id', $owner->id)
                        ->update($petData);

                } else {

                    $owner->pets()->create($petData);

                }

            }

        });

        return redirect()
            ->route('owner')
            ->with('success', 'Data owner berhasil diperbarui.');
    }
}
