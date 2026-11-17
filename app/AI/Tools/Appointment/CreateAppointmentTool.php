<?php

namespace App\AI\Tools\Appointment;

use App\AI\Contracts\AIToolInterface;
use App\Models\booking;
use App\Models\Owner;
use App\Models\OwnerPet;
use App\Models\User;
use Illuminate\Support\Str;

class CreateAppointmentTool implements AIToolInterface
{
    public function getName(): string
    {
        return 'create_appointment';
    }

    public function getDescription(): string
    {
        return 'Buat janji temu/booking pasien baru ke klinik (Memerlukan Konfirmasi User).';
    }

    public function getParameters(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'owner'   => ['type' => 'string', 'description' => 'Nama owner / pemilik hewan'],
                'pet'     => ['type' => 'string', 'description' => 'Nama hewan pasien'],
                'date'    => ['type' => 'string', 'description' => 'Tanggal booking (YYYY-MM-DD)'],
                'doctor'  => ['type' => 'string', 'description' => 'Nama dokter pemeriksa'],
                'keluhan' => ['type' => 'string', 'description' => 'Keluhan utama pasien'],
            ],
            'required' => ['owner', 'pet'],
        ];
    }

    public function authorize(User $user): bool
    {
        return true;
    }

    public function requiresConfirmation(): bool
    {
        return true;
    }

    public function execute(array $parameters, User $user): array
    {
        $ownerName   = trim($parameters['owner'] ?? '');
        $petName     = trim($parameters['pet'] ?? '');
        $bookingDate = $parameters['date'] ?? date('Y-m-d');
        $doctor      = $parameters['doctor'] ?? 'Belum Ditentukan';
        $keluhan     = $parameters['keluhan'] ?? 'Pemeriksaan Umum';

        // Resolve owner from DB
        $ownerObj = Owner::where('nama', 'LIKE', "%{$ownerName}%")->first();
        if (!$ownerObj) {
            return [
                'status'  => 'error',
                'message' => "Owner dengan nama \"{$ownerName}\" tidak ditemukan di database. Pastikan owner sudah terdaftar terlebih dahulu.",
            ];
        }

        // Resolve pet from owner's pets
        $petObj = OwnerPet::where('owner_id', $ownerObj->id)
            ->where('nama', 'LIKE', "%{$petName}%")
            ->first();

        // Generate unique booking code
        do {
            $kodeBooking = 'BOOK' . strtoupper(Str::random(7));
        } while (booking::where('kode_booking', $kodeBooking)->exists());

        $booking = booking::create([
            'owner_id'         => $ownerObj->id,
            'owner_pet_id'     => $petObj?->id,
            'kode_booking'     => $kodeBooking,
            'tipe_kedatangan'  => 'Appointment',
            'nama'             => $ownerObj->nama,
            'nama_hewan'       => $petObj ? $petObj->nama : $petName,
            'jenis_hewan'      => $petObj ? ($petObj->jenis_hewan ?? 'Hewan') : 'Hewan',
            'berat_hewan'      => $petObj?->berat_hewan ?? '-',
            'telpon'           => $ownerObj->telepon ?? '-',
            'alamat'           => $ownerObj->alamat ?? '-',
            'tanggal'          => $bookingDate,
            'jam'              => '09:00:00',
            'status'           => 'belum dikerjakan',
            'karyawan1'        => $doctor,
            'keluhan'          => $keluhan,
            'jasa_dokter'      => 'Jasa Dokter Konsultasi',
            'jasa_dokter_kuantitas' => 1,
            'jasa_dokter_harga'     => 50000,
            'total'            => 0,
            'dp'               => 0,
            'diskon'           => 0,
            'pajak'            => 0,
        ]);

        return [
            'status'       => 'success',
            'id'           => $booking->id,
            'kode_booking' => $booking->kode_booking,
            'owner'        => $ownerObj->nama,
            'owner_id'     => $ownerObj->id,
            'pet'          => $booking->nama_hewan,
            'owner_pet_id' => $petObj?->id,
            'tanggal'      => $bookingDate,
            'dokter'       => $doctor,
            'status_booking' => $booking->status,
            'url'          => route('booking-uncompleted'),
        ];
    }
}
