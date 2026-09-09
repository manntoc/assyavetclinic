<?php

namespace App\AI;

/**
 * AISkillManager : Registry and handler for modular AI Agent skills / modes.
 *
 * Enables slash commands (/karyawan, /owner, /booking, /emr, /inventori, /laporan, /layanan)
 * to switch the conversation into a dedicated specialist agent mode.
 */
class AISkillManager
{
    /**
     * Complete definition of available AI skills.
     */
    public static function getSkills(): array
    {
        return [
            'karyawan' => [
                'key'         => 'karyawan',
                'title'       => 'Dokter & Karyawan Specialist',
                'command'     => '/karyawan',
                'aliases'     => ['/karyawan', '/dokter', '/staff'],
                'icon'        => 'bi-people-fill',
                'color'       => '#2d8aba',
                'badge_class' => 'bg-primary',
                'description' => 'Fokus pada data dokter & staf, jadwal tugas hari ini, beban antrean dokter, dan SOP staf.',
                'greeting'    => 'Mode <strong>Dokter & Karyawan Specialist</strong> aktif.<br>Saya siap membantu melihat daftar staf/dokter jaga, jadwal penanganan booking per dokter, maupun SOP staf klinik.',
                'suggestions' => [
                    'Daftar staf & dokter jaga',
                    'Booking dokter hari ini',
                    'Cara tambah karyawan baru',
                    'Kembali ke mode umum (/general)',
                ],
                'tools'       => ['get_staff'],
                'docs'        => ['karyawan', 'user_akses'],
            ],
            'owner' => [
                'key'         => 'owner',
                'title'       => 'Owner & Pasien Specialist',
                'command'     => '/owner',
                'aliases'     => ['/owner', '/customer', '/pemilik', '/pasien'],
                'icon'        => 'bi-person-heart',
                'color'       => '#0d9488',
                'badge_class' => 'bg-success',
                'description' => 'Fokus pada pencarian profil pemilik hewan, data pasien multi-pet, riwayat kontak & alamat.',
                'greeting'    => 'Mode <strong>Owner & Pasien Specialist</strong> aktif.<br>Silakan tanyakan nama pemilik, nomor telepon, atau data hewan peliharaan yang terdaftar.',
                'suggestions' => [
                    'Cari owner Budi',
                    'Hewan milik Siti',
                    'Daftar customer terbaru',
                    'Kembali ke mode umum (/general)',
                ],
                'tools'       => ['search_owner'],
                'docs'        => ['owner_pet'],
            ],
            'booking' => [
                'key'         => 'booking',
                'title'       => 'Booking & Antrean Specialist',
                'command'     => '/booking',
                'aliases'     => ['/booking', '/antrean', '/antrian', '/queue'],
                'icon'        => 'bi-calendar-check-fill',
                'color'       => '#0284c7',
                'badge_class' => 'bg-info text-dark',
                'description' => 'Fokus pada antrean kunjungan hari ini, status 6 stage queue, pendaftaran booking baru, dan detail booking.',
                'greeting'    => 'Mode <strong>Booking & Antrean Specialist</strong> aktif.<br>Saya siap membantu monitoring antrean hari ini, detail status stage pasien, maupun registrasi booking baru.',
                'suggestions' => [
                    'Antrean hari ini',
                    'Detail booking BOOK123',
                    'Alur 6 stage booking',
                    'Kembali ke mode umum (/general)',
                ],
                'tools'       => ['get_today_appointments', 'get_appointments', 'get_booking_detail', 'create_appointment'],
                'docs'        => ['booking', 'queue_stages'],
            ],
            'emr' => [
                'key'         => 'emr',
                'title'       => 'Rekam Medis & SOAP Specialist',
                'command'     => '/emr',
                'aliases'     => ['/emr', '/rekam-medis', '/soap', '/medis'],
                'icon'        => 'bi-clipboard2-pulse-fill',
                'color'       => '#2d8aba',
                'badge_class' => 'bg-primary',
                'description' => 'Fokus pada catatan rekam medis SOAP, tanda vital pasien (suhu, denyut jantung, CRT, BB), dan riwayat diagnosa.',
                'greeting'    => 'Mode <strong>Rekam Medis & SOAP Specialist</strong> aktif.<br>Saya siap membantu pengecekan catatan klinis SOAP, riwayat diagnosa pasien, dan standar tanda vital hewan.',
                'suggestions' => [
                    'Rekam medis kucing Milo',
                    'Detail diagnosa terakhir',
                    'Standar tanda vital normal',
                    'Kembali ke mode umum (/general)',
                ],
                'tools'       => ['get_emr', 'get_booking_detail'],
                'docs'        => ['emr', 'rawat_inap'],
            ],
            'inventori' => [
                'key'         => 'inventori',
                'title'       => 'Inventori & Apotek Specialist',
                'command'     => '/inventori',
                'aliases'     => ['/inventori', '/obat', '/apotek', '/stok'],
                'icon'        => 'bi-capsule',
                'color'       => '#546e7a',
                'badge_class' => 'bg-secondary',
                'description' => 'Fokus pada stok obat apotek, harga satuan, status ketersediaan stok, dan transaksi pengambilan obat.',
                'greeting'    => 'Mode <strong>Inventori & Apotek Specialist</strong> aktif.<br>Silakan tanyakan nama obat untuk cek stok real-time, harga satuan, atau riwayat pengambilan obat klinik.',
                'suggestions' => [
                    'Cek stok amoxicillin',
                    'Daftar obat stok tipis',
                    'Log pengambilan obat',
                    'Kembali ke mode umum (/general)',
                ],
                'tools'       => ['search_inventory', 'get_transaksi_log'],
                'docs'        => ['inventori', 'transaksi'],
            ],
            'laporan' => [
                'key'         => 'laporan',
                'title'       => 'Keuangan & Omzet Specialist',
                'command'     => '/laporan',
                'aliases'     => ['/laporan', '/omzet', '/keuangan', '/kasir'],
                'icon'        => 'bi-cash-stack',
                'color'       => '#4f46e5',
                'badge_class' => 'bg-success',
                'description' => 'Fokus pada laporan omzet pendapatan klinik, komparasi bulanan, nota estimasi biaya, dan billing kasir.',
                'greeting'    => 'Mode <strong>Keuangan & Omzet Specialist</strong> aktif.<br>Saya siap membantu menghitung omzet pendapatan, komparasi kinerja antar-bulan, dan panduan cetak nota/laporan PDF.',
                'suggestions' => [
                    'Omzet hari ini',
                    'Bandingkan omzet bulan ini vs bulan lalu',
                    'Cetak nota laporan PDF',
                    'Kembali ke mode umum (/general)',
                ],
                'tools'       => ['get_omzet_report', 'compare_omzet', 'get_temporary_invoice', 'get_transaksi_log'],
                'docs'        => ['laporan', 'pembayaran', 'estimasi', 'nota_pdf'],
            ],
            'layanan' => [
                'key'         => 'layanan',
                'title'       => 'Tarif Layanan Specialist',
                'command'     => '/layanan',
                'aliases'     => ['/layanan', '/tarif', '/jasa'],
                'icon'        => 'bi-tags-fill',
                'color'       => '#0284c7',
                'badge_class' => 'bg-info text-dark',
                'description' => 'Fokus pada master data tarif layanan klinik (jasa dokter, vaksin, grooming, operasi, lab).',
                'greeting'    => 'Mode <strong>Tarif Layanan Specialist</strong> aktif.<br>Silakan tanyakan jenis atau nama layanan untuk mengecek tarif resmi klinik.',
                'suggestions' => [
                    'Tarif vaksin rabies',
                    'Daftar jasa dokter',
                    'Tarif grooming',
                    'Kembali ke mode umum (/general)',
                ],
                'tools'       => ['search_layanan'],
                'docs'        => ['layanan'],
            ],
        ];
    }

    /**
     * Find skill by key.
     */
    public static function getSkill(string $key): ?array
    {
        $skills = self::getSkills();
        return $skills[strtolower(trim($key))] ?? null;
    }

    /**
     * Detect if message is or starts with a slash command.
     * Returns:
     *   [
     *     'is_command' => bool,
     *     'action'     => 'switch' | 'reset' | 'help' | null,
     *     'skill'      => array | null,
     *     'remainder'  => string (query text after command if any)
     *   ]
     */
    public static function parseCommand(string $rawMessage): array
    {
        $trimmed = trim($rawMessage);
        if (!str_starts_with($trimmed, '/')) {
            return [
                'is_command' => false,
                'action'     => null,
                'skill'      => null,
                'remainder'  => $trimmed,
            ];
        }

        // Split into command and remainder
        $parts = preg_split('/\s+/', $trimmed, 2);
        $cmd   = strtolower(trim($parts[0]));
        $remainder = isset($parts[1]) ? trim($parts[1]) : '';

        // Check for Reset / General commands
        $resetCommands = ['/general', '/batal', '/reset', '/all', '/default'];
        if (in_array($cmd, $resetCommands, true)) {
            return [
                'is_command' => true,
                'action'     => 'reset',
                'skill'      => null,
                'remainder'  => $remainder,
            ];
        }

        // Check for Help / Skills list commands
        $helpCommands = ['/help', '/skills', '/mode', '/modul', '/menu'];
        if (in_array($cmd, $helpCommands, true)) {
            return [
                'is_command' => true,
                'action'     => 'help',
                'skill'      => null,
                'remainder'  => $remainder,
            ];
        }

        // Check matched skill by command or alias
        foreach (self::getSkills() as $skill) {
            if ($cmd === $skill['command'] || in_array($cmd, $skill['aliases'], true)) {
                return [
                    'is_command' => true,
                    'action'     => 'switch',
                    'skill'      => $skill,
                    'remainder'  => $remainder,
                ];
            }
        }

        // Unknown slash command: treat as help request
        return [
            'is_command' => true,
            'action'     => 'help',
            'skill'      => null,
            'remainder'  => $remainder,
        ];
    }

    /**
     * Build the Help / Skills List response array.
     */
    public static function buildHelpResponse(int $conversationId): array
    {
        $skills = self::getSkills();
        $lines = [
            '<strong>Modul &amp; Skill AI Agent Assyavet:</strong><br>',
            'Ketik perintah slash di bawah atau klik opsi untuk masuk ke sesi agen spesialis yang lebih terfokus:<br>',
        ];

        $chips = [];
        foreach ($skills as $s) {
            $lines[] = "• <strong>{$s['command']}</strong> : {$s['description']}";
            $chips[] = $s['command'];
        }
        $lines[] = "• <strong>/general</strong> : Kembali ke Asisten Umum klinik";

        return [
            'status'          => 'success',
            'reply'           => implode('<br>', $lines),
            'conversation_id' => $conversationId,
            'state'           => AIStateMachine::IDLE,
            'active_skill'    => null,
            'skill_title'     => null,
            'suggestions'     => array_merge($chips, ['/general']),
        ];
    }

    /**
     * Build the Reset / General Mode response array.
     */
    public static function buildResetResponse(int $conversationId): array
    {
        $reply = 'Mode <strong>Asisten Umum</strong> aktif.<br>'
               . 'Anda dapat menanyakan seluruh data klinik: owner, jadwal booking, rekam medis SOAP, stok apotek, maupun omzet keuangan.';

        return [
            'status'          => 'success',
            'reply'           => $reply,
            'conversation_id' => $conversationId,
            'state'           => AIStateMachine::IDLE,
            'active_skill'    => null,
            'skill_title'     => null,
            'suggestions'     => [
                'Antrean hari ini',
                'Cari owner Budi',
                'Cek stok obat',
                'Berapa omzet hari ini?',
                'Lihat daftar modul (/skills)',
            ],
        ];
    }

    /**
     * Build Switch Mode greeting response array.
     */
    public static function buildSwitchResponse(array $skill, int $conversationId): array
    {
        return [
            'status'          => 'success',
            'reply'           => $skill['greeting'],
            'conversation_id' => $conversationId,
            'state'           => AIStateMachine::IDLE,
            'active_skill'    => $skill['key'],
            'skill_title'     => $skill['title'],
            'skill_color'     => $skill['color'],
            'skill_badge'     => $skill['badge_class'],
            'suggestions'     => $skill['suggestions'],
        ];
    }
}
