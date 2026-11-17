<?php

namespace App\AI\Tools\MasterData;

use App\AI\Contracts\AIToolInterface;
use App\Models\User;
use App\Models\booking;

class GetStaffTool implements AIToolInterface
{
    public function getName(): string
    {
        return 'get_staff';
    }

    public function getDescription(): string
    {
        return 'Lihat daftar karyawan, dokter, atau staf klinik beserta role dan booking yang mereka tangani hari ini.';
    }

    public function getParameters(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'query' => [
                    'type' => 'string',
                    'description' => 'Nama karyawan/dokter (opsional, kosong = semua staf)'
                ],
                'date' => [
                    'type' => 'string',
                    'description' => 'Tanggal berjaga (YYYY-MM-DD, opsional, kosong = hari ini)'
                ],
            ],
            'required' => []
        ];
    }

    public function authorize(User $user): bool
    {
        return true;
    }

    public function requiresConfirmation(): bool
    {
        return false;
    }

    public function execute(array $parameters, User $user): array
    {
        $query = trim($parameters['query'] ?? '');
        $date  = $parameters['date'] ?? date('Y-m-d');

        // Validate date format
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            $date = date('Y-m-d');
        }

        // Query staff users (exclude super-admin CMS roles)
        $staffQuery = User::whereHas('roles', function ($q) {
            $q->whereIn('name', ['superadmin', 'admin', 'karyawan']);
        });

        if (!empty($query)) {
            $staffQuery->where('name', 'LIKE', "%{$query}%");
        }

        $staff = $staffQuery->with('roles')->orderBy('name')->get();

        if ($staff->isEmpty()) {
            return [
                'found'   => false,
                'message' => empty($query)
                    ? 'Tidak ada karyawan terdaftar di sistem.'
                    : "Karyawan dengan nama '{$query}' tidak ditemukan.",
            ];
        }

        // For each staff, count bookings they handle on given date (karyawan1/2/3 columns)
        $result = $staff->map(function (User $s) use ($date) {
            $bookingCount = booking::whereDate('tanggal', $date)
                ->where(function ($q) use ($s) {
                    $q->where('karyawan1', $s->name)
                      ->orWhere('karyawan2', $s->name)
                      ->orWhere('karyawan3', $s->name);
                })->count();

            $roles = $s->roles->pluck('name')->implode(', ');

            return [
                'name'          => $s->name,
                'email'         => $s->email,
                'role'          => $roles ?: 'Tidak ada role',
                'booking_count' => $bookingCount,
                'date'          => $date,
            ];
        })->values()->toArray();

        return [
            'found'   => true,
            'date'    => $date,
            'total'   => count($result),
            'staff'   => $result,
        ];
    }
}
