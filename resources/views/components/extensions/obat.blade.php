<script>
document.addEventListener('DOMContentLoaded', function () {
    @foreach ($booking as $item)
        (function() {
            const obatSelect = document.getElementById('modalObatSelect-{{ $item->id }}');
            const obatQty = document.getElementById('modalObatQty-{{ $item->id }}');
            const obatTotal = document.getElementById('modalObatTotal-{{ $item->id }}');
            const obatStok = document.getElementById('modalObatStok-{{ $item->id }}');
            const btnSubmit = document.getElementById('btnSubmitObat-{{ $item->id }}');

            function calcObat() {
                if (!obatSelect || !obatQty || !obatTotal) return;
                const opt = obatSelect.querySelector(`option[value="${obatSelect.value}"]`) || obatSelect.options[obatSelect.selectedIndex];
                if (opt && opt.value) {
                    const price = parseFloat(opt.getAttribute('data-price')) || 0;
                    const stok = parseInt(opt.getAttribute('data-stok')) || 0;
                    
                    if (obatStok) {
                        obatStok.innerHTML = `Stok Tersedia: <strong class="${stok < 5 ? 'text-danger' : 'text-success'}">${stok} item</strong> (Harga Satuan: Rp ${price.toLocaleString('id-ID')})`;
                    }
                    obatQty.setAttribute('max', stok);

                    const q = parseInt(obatQty.value) || 0;
                    obatTotal.value = q * price;
                } else {
                    if (obatStok) obatStok.innerHTML = 'Stok Tersedia: -';
                    obatTotal.value = '';
                }
            }

            if (obatSelect) obatSelect.addEventListener('change', calcObat);
            if (obatQty) obatQty.addEventListener('input', calcObat);

            if (btnSubmit) {
                btnSubmit.addEventListener('click', function() {
                    if (!obatSelect || !obatSelect.value) {
                        alert('Silakan pilih obat inventori terlebih dahulu.');
                        return;
                    }
                    const qtyVal = parseInt(obatQty.value) || 0;
                    if (qtyVal <= 0) {
                        alert('Jumlah obat harus minimal 1.');
                        return;
                    }

                    // Dynamic form submission to storetransaksi route while maintaining backend & frontend required validations
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = '{{ route("storetransaksi") }}';

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

                    const inventoriIdInput = document.createElement('input');
                    inventoriIdInput.type = 'hidden';
                    inventoriIdInput.name = 'inventori_id';
                    inventoriIdInput.value = obatSelect.value;
                    form.appendChild(inventoriIdInput);

                    const jumlahInput = document.createElement('input');
                    jumlahInput.type = 'hidden';
                    jumlahInput.name = 'jumlah';
                    jumlahInput.value = qtyVal;
                    form.appendChild(jumlahInput);

                    const totalInput = document.createElement('input');
                    totalInput.type = 'hidden';
                    totalInput.name = 'total';
                    totalInput.value = obatTotal.value;
                    form.appendChild(totalInput);

                    document.body.appendChild(form);
                    form.submit();
                });
            }
        })();
    @endforeach
});
</script>
