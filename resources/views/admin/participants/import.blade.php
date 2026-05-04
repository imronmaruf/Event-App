@extends('layouts.admin')
@section('title', 'Import Peserta Excel')
@section('page-title', 'Import Peserta dari Excel')

@push('styles')
    <style>
        .upload-zone {
            border: 2.5px dashed #e0e0e0;
            border-radius: 16px;
            padding: 40px 24px;
            text-align: center;
            background: #fafafa;
            cursor: pointer;
            transition: all .2s;
        }

        .upload-zone.drag-over,
        .upload-zone:hover {
            border-color: #c62828;
            background: #fff8f8;
        }

        .upload-zone .upload-icon {
            font-size: 52px;
            color: #ddd;
            margin-bottom: 12px;
        }

        .format-table th {
            background: #1565c0;
            color: #fff;
            font-size: 12px;
            padding: 10px 14px;
        }

        .format-table td {
            font-size: 12.5px;
            padding: 9px 14px;
            border-bottom: 1px solid #f5f5f5;
        }

        .format-table tr:last-child td {
            border-bottom: none;
        }

        .info-card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, .06);
            padding: 24px;
        }

        .form-control {
            border-radius: 10px;
            border: 1.5px solid #e0e0e0;
            font-size: 13.5px;
        }

        .form-control:focus {
            border-color: #c62828;
            box-shadow: 0 0 0 3px rgba(198, 40, 40, .12);
        }
    </style>
@endpush

@section('topbar-actions')
    <a href="{{ route('admin.participants.index', $event) }}" class="btn btn-sm btn-light" style="border-radius:10px;">
        <i class="bi bi-arrow-left me-1"></i> Kembali
    </a>
    <a href="{{ route('admin.participants.template', $event) }}" class="btn btn-sm fw-600"
        style="border-radius:10px;background:#e3f2fd;color:#1565c0;border:none;">
        <i class="bi bi-download me-1"></i> Download Template
    </a>
@endsection

@section('content')
    <div class="row g-4">
        {{-- Upload Form --}}
        <div class="col-12 col-lg-7">
            <div class="info-card">
                <h6 class="fw-bold mb-4"
                    style="color:#c62828;font-size:13px;text-transform:uppercase;letter-spacing:.5px;border-bottom:1.5px solid #ffebee;padding-bottom:10px;">
                    <i class="bi bi-file-earmark-arrow-up me-1"></i> Upload File Excel
                </h6>

                {{-- Event strip --}}
                <div class="p-3 rounded-3 mb-4" style="background:#f0f9ff;border:1.5px solid #b3e5fc;">
                    <div style="font-size:12px;color:#0277bd;font-weight:600;">Event Tujuan</div>
                    <div class="fw-bold">{{ $event->name }}</div>
                    <div style="font-size:12px;color:#aaa;">{{ $event->unit->name }}</div>
                </div>

                <form action="{{ route('admin.participants.import.process', $event) }}" method="POST"
                    enctype="multipart/form-data" id="importForm">
                    @csrf

                    {{-- Drop Zone --}}
                    <div class="upload-zone mb-3" id="dropZone" onclick="document.getElementById('fileInput').click()">
                        <div class="upload-icon">📂</div>
                        <div class="fw-bold mb-1" style="font-size:15px;">Klik atau drop file Excel di sini</div>
                        <div style="font-size:13px;color:#aaa;">Format yang diterima: .xlsx, .xls, .csv (maks. 10 MB)</div>
                        <div id="filePreview" class="mt-3" style="display:none;">
                            <div class="d-inline-flex align-items-center gap-2 px-4 py-2 rounded-3"
                                style="background:#e8f5e9;color:#2e7d32;">
                                <i class="bi bi-file-earmark-excel" style="font-size:20px;"></i>
                                <span id="fileName" class="fw-bold" style="font-size:13px;"></span>
                            </div>
                        </div>
                    </div>
                    <input type="file" name="file" id="fileInput" accept=".xlsx,.xls,.csv" style="display:none;"
                        onchange="handleFileSelect(this)">
                    @error('file')
                        <div class="text-danger mb-2" style="font-size:12px;">{{ $message }}</div>
                    @enderror

                    <button type="submit" id="uploadBtn" class="btn w-100 fw-bold"
                        style="background:linear-gradient(135deg,#c62828,#e64a19);color:#fff;border:none;border-radius:12px;padding:13px;font-size:15px;">
                        <i class="bi bi-cloud-upload me-1"></i> Import Sekarang
                    </button>
                </form>
            </div>
        </div>

        {{-- Format Panduan --}}
        <div class="col-12 col-lg-5">
            {{-- Panduan Format --}}
            <div class="info-card mb-4">
                <h6 class="fw-bold mb-3"
                    style="color:#1565c0;font-size:13px;text-transform:uppercase;letter-spacing:.5px;border-bottom:1.5px solid #e3f2fd;padding-bottom:10px;">
                    <i class="bi bi-table me-1"></i> Format File Excel
                </h6>
                <p style="font-size:12.5px;color:#555;margin-bottom:12px;">
                    Baris pertama wajib berisi header. Nama header dapat menggunakan variasi berikut:
                </p>
                <div class="table-responsive rounded-3 overflow-hidden">
                    <table class="format-table w-100">
                        <thead>
                            <tr>
                                <th>Kolom</th>
                                <th>Header yang Dikenali</th>
                                <th>Wajib</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="fw-bold">NOREG</td>
                                <td style="color:#555;">noreg, no reg, no. reg, nomor registrasi</td>
                                <td><span style="color:#2e7d32;font-weight:700;">✓</span></td>
                            </tr>
                            <tr>
                                <td class="fw-bold">NAMA</td>
                                <td style="color:#555;">nama, nama siswa, nama peserta, name</td>
                                <td><span style="color:#2e7d32;font-weight:700;">✓</span></td>
                            </tr>
                            <tr>
                                <td class="fw-bold">KELAS</td>
                                <td style="color:#555;">kelas, class, tingkat</td>
                                <td style="color:#aaa;">–</td>
                            </tr>
                            <tr>
                                <td class="fw-bold">SEKOLAH</td>
                                <td style="color:#555;">sekolah, asal sekolah, school</td>
                                <td style="color:#aaa;">–</td>
                            </tr>
                            <tr>
                                <td class="fw-bold">RUANG</td>
                                <td style="color:#555;">ruang, room, ruangan</td>
                                <td style="color:#aaa;">–</td>
                            </tr>
                            <tr>
                                <td class="fw-bold">PENGAWAS</td>
                                <td style="color:#555;">pengawas, supervisor</td>
                                <td style="color:#aaa;">–</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Info Tambahan --}}
            <div class="p-3 rounded-3" style="background:#fff8f0;border:1.5px solid #ffcc80;">
                <div class="fw-bold mb-2" style="font-size:13px;color:#e65100;">
                    <i class="bi bi-exclamation-triangle me-1"></i> Penting!
                </div>
                <ul style="font-size:12.5px;color:#555;padding-left:16px;margin:0;line-height:1.8;">
                    <li>Peserta dengan NOREG duplikat akan dilewati (tidak diganti).</li>
                    <li>Setelah import berhasil, kode absensi akan otomatis digenerate.</li>
                    <li>Anda bisa melakukan import berulang untuk menambah data baru.</li>
                    <li>Download template Excel di atas untuk format yang sudah siap pakai.</li>
                </ul>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function handleFileSelect(input) {
            const file = input.files[0];
            if (!file) return;
            document.getElementById('fileName').textContent = file.name;
            document.getElementById('filePreview').style.display = 'block';
            document.getElementById('dropZone').style.borderColor = '#2e7d32';
        }

        // Drag & Drop
        const dropZone = document.getElementById('dropZone');

        dropZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            dropZone.classList.add('drag-over');
        });

        dropZone.addEventListener('dragleave', () => {
            dropZone.classList.remove('drag-over');
        });

        dropZone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropZone.classList.remove('drag-over');
            const file = e.dataTransfer.files[0];
            if (file) {
                const input = document.getElementById('fileInput');
                const dt = new DataTransfer();
                dt.items.add(file);
                input.files = dt.files;
                handleFileSelect(input);
            }
        });

        // Loading state on submit
        document.getElementById('importForm').addEventListener('submit', function() {
            const btn = document.getElementById('uploadBtn');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Mengimport data...';
        });
    </script>
@endpush
