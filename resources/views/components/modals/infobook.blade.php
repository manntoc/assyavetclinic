@foreach ($booking as $item)
    <div class="modal fade text-left" id="Informasi-{{ $item->id }}" tabindex="-1" role="dialog"
        aria-labelledby="myModalLabel17" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="myModalLabel17">Informasi Pemesanan dan Perawatan</h4>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <i data-feather="x"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="card">
                        <div class="card-header bg-primary">
                            <h5 class="mb-0 text-white">Informasi Booking Perawatan</h5>
                        </div>
                        <div class="card-body mt-4">
                            <h6 class="card-title">Detail Pesanan:</h6>
                            <div class="list-group list-group-flush">
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <span><i class="fas fa-user"></i> Nama Pemilik:</span>
                                    <strong>{{ $item->nama }}</strong>
                                </div>
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <span><i class="fas fa-paw"></i> Nama Hewan:</span>
                                    <strong>{{ $item->namahewan }}</strong>
                                </div>
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <span><i class="fas fa-map-marker-alt"></i> Alamat:</span>
                                    <strong>{{ $item->alamat }}</strong>
                                </div>
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <span><i class="fas fa-calendar-alt"></i> Tanggal Booking:</span>
                                    <strong>{{ $item->tanggal }}</strong>
                                </div>
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <span><i class="fas fa-calendar-alt"></i> Tanggal keluar:</span>
                                    <strong>{{ $item->keluar }}</strong>
                                </div>
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <span><i class="fas fa-user-check"></i> Karyawan yang memeriksa:</span>
                                    <strong>
                                        @if($item->karyawan1) {{$item->karyawan1}} @endif
                                        @if($item->karyawan2) , {{$item->karyawan2}} @endif
                                        @if($item->karyawan3) , {{$item->karyawan3}} @endif
                                    </strong>
                                </div>
                                <!-- Contoh menambahkan status dengan badge -->
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <span><i class="fas fa-info-circle"></i> Status:</span>
                                    <span class="badge bg-success">{{ $item->status }}</span>
                                </div>
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <span><i class="far fa-sticky-note"></i> Keluhan Kucing:</span>
                                    <strong>{{ $item->keluhan }}</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light-secondary" data-bs-dismiss="modal">
                        <i class="bx bx-x d-block d-sm-none"></i>
                        <span class="d-none d-sm-block">Tutup</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
@endforeach