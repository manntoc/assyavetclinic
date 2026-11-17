<script>
document.addEventListener('DOMContentLoaded', function () {
    @foreach ($booking as $item)
        (function() {
            const btnLog = document.getElementById('btnSubmitDailyLog-{{ $item->id }}');
            if (btnLog) {
                btnLog.addEventListener('click', function() {
                    const suhu = document.getElementById('logSuhu-{{ $item->id }}') ? document.getElementById('logSuhu-{{ $item->id }}').value : '';
                    const pulse = document.getElementById('logPulse-{{ $item->id }}') ? document.getElementById('logPulse-{{ $item->id }}').value : '';
                    const berat = document.getElementById('logBerat-{{ $item->id }}') ? document.getElementById('logBerat-{{ $item->id }}').value : '';
                    const nafsu = document.getElementById('logNafsu-{{ $item->id }}') ? document.getElementById('logNafsu-{{ $item->id }}').value : '';
                    const catatan = document.getElementById('logCatatan-{{ $item->id }}') ? document.getElementById('logCatatan-{{ $item->id }}').value : '';

                    if (!suhu && !pulse && !berat && !catatan) {
                        alert('Silakan isi setidaknya satu data pengecekan harian (suhu, denyut, berat, atau catatan observasi).');
                        return;
                    }

                    // Create hidden form to post store-pet-log route
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = '{{ route("store-pet-log") }}';

                    const csrf = document.createElement('input');
                    csrf.type = 'hidden';
                    csrf.name = '_token';
                    csrf.value = '{{ csrf_token() }}';
                    form.appendChild(csrf);

                    const bookingIdInput = document.createElement('input');
                    bookingIdInput.type = 'hidden';
                    bookingIdInput.name = 'booking_id';
                    bookingIdInput.value = '{{ $item->id }}';
                    form.appendChild(bookingIdInput);

                    const suhuInput = document.createElement('input');
                    suhuInput.type = 'hidden';
                    suhuInput.name = 'suhu_tubuh';
                    suhuInput.value = suhu;
                    form.appendChild(suhuInput);

                    const pulseInput = document.createElement('input');
                    pulseInput.type = 'hidden';
                    pulseInput.name = 'denyut_jantung';
                    pulseInput.value = pulse;
                    form.appendChild(pulseInput);

                    const beratInput = document.createElement('input');
                    beratInput.type = 'hidden';
                    beratInput.name = 'berat_badan';
                    beratInput.value = berat;
                    form.appendChild(beratInput);

                    const nafsuInput = document.createElement('input');
                    nafsuInput.type = 'hidden';
                    nafsuInput.name = 'nafsu_makan';
                    nafsuInput.value = nafsu;
                    form.appendChild(nafsuInput);

                    const catatanInput = document.createElement('input');
                    catatanInput.type = 'hidden';
                    catatanInput.name = 'catatan_observasi';
                    catatanInput.value = catatan;
                    form.appendChild(catatanInput);

                    document.body.appendChild(form);
                    form.submit();
                });
            }
        })();
    @endforeach
});
</script>
