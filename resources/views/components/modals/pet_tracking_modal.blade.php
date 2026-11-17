<!-- MODAL TRACKING & STATISTIK PERKEMBANGAN PASIEN HEWAN -->
<div class="modal fade" id="modal-tracking-hewan" tabindex="-1" aria-labelledby="modalTrackingLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 14px;">
            
            <!-- MODAL HEADER (PRD Primary Color #2d8aba) -->
            <div class="modal-header text-white px-4 py-3" style="background-color: #2d8aba; border-top-left-radius: 14px; border-top-right-radius: 14px;">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-white rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 42px; height: 42px; flex-shrink: 0;">
                        <span id="tracking-pet-icon" style="font-size: 1.35rem; line-height: 1; display: flex; align-items: center; justify-content: center;">🐾</span>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-white mb-0" id="tracking-pet-name" style="font-size: 1.25rem;">
                            Grafik & Statistik Perkembangan Pasien
                        </h5>
                        <small class="text-white-50 d-block mt-1" id="tracking-pet-sub" style="font-size: 0.75rem;">
                            Memuat data rekam medis...
                        </small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- MODAL BODY -->
            <div class="modal-body p-4" style="background-color: #f8fafc;">
                
                <!-- LOADING SPINNER CONTAINER -->
                <div id="tracking-loading" class="text-center py-5">
                    <div class="spinner-border text-primary" role="status" style="width: 2.5rem; height: 2.5rem;">
                        <span class="visually-hidden">Memuat...</span>
                    </div>
                    <p class="text-muted mt-3 fw-medium" style="font-size: 0.875rem;">Mengambil data riwayat & statistik vital signs hewan...</p>
                </div>

                <!-- MAIN TRACKING CONTENT (Hidden during load) -->
                <div id="tracking-content" style="display: none;">
                    
                    <!-- TOP HEALTH METRICS CARDS (PRD Card & Font Hierarchy) -->
                    <div class="row g-3 mb-4">
                        <!-- Card 1: Berat Badan -->
                        <div class="col-12 col-sm-6 col-xl-3">
                            <div class="card border-0 shadow-sm h-100" style="border-radius: 12px; border-left: 4px solid #2d8aba !important;">
                                <div class="card-body p-3">
                                    <div class="mb-2">
                                        <span class="text-muted fw-semibold" style="font-size: 0.75rem;">Berat Badan Terakhir</span>
                                    </div>
                                    <h4 class="fw-bold mb-1" id="stat-weight-latest" style="color: #0f172a; font-size: 1.25rem;">-</h4>
                                    <div class="d-flex align-items-center justify-content-between text-muted" style="font-size: 0.75rem;">
                                        <span>Initial: <strong id="stat-weight-initial">-</strong></span>
                                        <span class="fw-bold ms-2" id="stat-weight-diff-badge">-</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Card 2: Suhu Tubuh -->
                        <div class="col-12 col-sm-6 col-xl-3">
                            <div class="card border-0 shadow-sm h-100" style="border-radius: 12px; border-left: 4px solid #ef4444 !important;">
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="text-muted fw-semibold" style="font-size: 0.75rem;">Suhu Tubuh</span>
                                        <span class="badge rounded-pill px-2 py-1 d-none" id="stat-temp-badge" style="font-size: 0.75rem;"></span>
                                    </div>
                                    <h4 class="fw-bold mb-1" id="stat-temp-latest" style="color: #0f172a; font-size: 1.25rem;">-</h4>
                                    <span class="text-muted d-block" style="font-size: 0.75rem;">Rentang Normal: 38.0 - 39.2 °C</span>
                                </div>
                            </div>
                        </div>

                        <!-- Card 3: Denyut Jantung -->
                        <div class="col-12 col-sm-6 col-xl-3">
                            <div class="card border-0 shadow-sm h-100" style="border-radius: 12px; border-left: 4px solid #10b981 !important;">
                                <div class="card-body p-3">
                                    <div class="mb-2">
                                        <span class="text-muted fw-semibold" style="font-size: 0.75rem;">Denyut Jantung</span>
                                    </div>
                                    <h4 class="fw-bold mb-1" id="stat-pulse-latest" style="color: #0f172a; font-size: 1.25rem;">-</h4>
                                    <span class="text-muted d-block" style="font-size: 0.75rem;">Frekuensi bpm terdeteksi</span>
                                </div>
                            </div>
                        </div>

                        <!-- Card 4: Total Kunjungan Klinik -->
                        <div class="col-12 col-sm-6 col-xl-3">
                            <div class="card border-0 shadow-sm h-100" style="border-radius: 12px; border-left: 4px solid #546e7a !important;">
                                <div class="card-body p-3">
                                    <div class="mb-2">
                                        <span class="text-muted fw-semibold" style="font-size: 0.75rem;">Total Kunjungan</span>
                                    </div>
                                    <h4 class="fw-bold mb-1" id="stat-visits-total" style="color: #0f172a; font-size: 1.25rem;">-</h4>
                                    <span class="text-muted d-block" style="font-size: 0.75rem;">Kunjungan EMR Tercatat</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- TAB SYSTEM INSIDE MODAL (PRD Custom Tabs) -->
                    <ul class="nav nav-pills custom-tracking-tabs mb-3 bg-white p-2 rounded-3 shadow-sm border" id="trackingTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active fw-semibold" id="tab-charts-btn" data-bs-toggle="pill" data-bs-target="#tab-charts-pane" type="button" role="tab" style="font-size: 0.875rem;">
                                <i class="bi bi-graph-up-arrow me-1"></i> Grafik Perkembangan Vital Signs
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link fw-semibold" id="tab-timeline-btn" data-bs-toggle="pill" data-bs-target="#tab-timeline-pane" type="button" role="tab" style="font-size: 0.875rem;">
                                <i class="bi bi-journal-medical me-1"></i> Timeline Rekam Medis & EMR
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content" id="trackingTabsContent">
                        
                        <!-- TAB 1: CHARTS -->
                        <div class="tab-pane fade show active" id="tab-charts-pane" role="tabpanel">
                            <div class="row g-3">
                                <!-- CHART 1: KURVA BERAT BADAN -->
                                <div class="col-12 col-lg-6">
                                    <div class="card border-0 shadow-sm p-3 h-100" style="border-radius: 12px;">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <h6 class="fw-bold mb-0 text-dark" style="font-size: 1.00rem;">
                                                <i class="bi bi-speedometer2 text-primary me-2"></i>Kurva Pertumbuhan Berat Badan (kg)
                                            </h6>
                                        </div>
                                        <div style="position: relative; min-height: 260px;">
                                            <canvas id="chart-weight-canvas"></canvas>
                                        </div>
                                    </div>
                                </div>

                                <!-- CHART 2: SUHU & DENYUT JANTUNG -->
                                <div class="col-12 col-lg-6">
                                    <div class="card border-0 shadow-sm p-3 h-100" style="border-radius: 12px;">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <h6 class="fw-bold mb-0 text-dark" style="font-size: 1.00rem;">
                                                <i class="bi bi-activity text-danger me-2"></i>Tren Vital Signs (Suhu & Denyut Jantung)
                                            </h6>
                                        </div>
                                        <div style="position: relative; min-height: 260px;">
                                            <canvas id="chart-vitals-canvas"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- TAB 2: TIMELINE REKAM MEDIS (SOAP) -->
                        <div class="tab-pane fade" id="tab-timeline-pane" role="tabpanel">
                            <div class="card border-0 shadow-sm p-4" style="border-radius: 12px;">
                                <h6 class="fw-bold text-dark mb-4" style="font-size: 1.00rem;">
                                    <i class="bi bi-clock-history text-primary me-2"></i>Riwayat Pemeriksaan Medis (SOAP Log)
                                </h6>
                                <div id="emr-timeline-container" class="timeline-wrapper">
                                    <!-- Dynamic Timeline Items inserted via JS -->
                                </div>
                            </div>
                        </div>

                    </div>

                </div>

            </div>

            <!-- MODAL FOOTER (PRD Secondary Button Style) -->
            <div class="modal-footer bg-white px-4 py-3 border-top">
                <button type="button" class="btn text-white px-4 fw-semibold shadow-sm" style="background-color: #546e7a; border-color: #546e7a; font-size: 0.875rem;" data-bs-dismiss="modal">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    .custom-tracking-tabs .nav-link {
        color: #64748b;
        border-radius: 8px;
        padding: 8px 16px;
        transition: all 0.2s ease;
    }
    .custom-tracking-tabs .nav-link.active {
        background-color: #2d8aba !important;
        color: #ffffff !important;
        box-shadow: 0 2px 6px rgba(45, 138, 186, 0.3);
    }
    
    /* Timeline Styling */
    .timeline-wrapper {
        position: relative;
        padding-left: 24px;
        border-left: 2px solid #e2e8f0;
    }
    .timeline-item {
        position: relative;
        margin-bottom: 24px;
    }
    .timeline-item:last-child {
        margin-bottom: 0;
    }
    .timeline-dot {
        position: absolute;
        left: -31px;
        top: 2px;
        width: 16px;
        height: 16px;
        border-radius: 50%;
        background-color: #2d8aba;
        border: 3px solid #ffffff;
        box-shadow: 0 0 0 2px #2d8aba;
    }
    .timeline-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 16px;
        box-shadow: 0 1px 2px rgba(0,0,0,0.02);
    }
</style>
