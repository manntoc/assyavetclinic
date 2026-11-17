<?php

namespace App\AI\Tools\Appointment;

class GetTodayAppointmentsTool extends GetAppointmentsTool
{
    public function getName(): string
    {
        return 'get_today_appointments';
    }

    public function getDescription(): string
    {
        return 'Ambil daftar booking/antrean pasien hari ini secara cepat.';
    }
}
