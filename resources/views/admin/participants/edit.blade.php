@extends('layouts.admin')
@section('title', 'Edit Peserta')
@section('page-title', 'Edit Peserta')

@push('styles')
    <style>
        .form-card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, .06);
            padding: 28px;
        }

        .form-control,
        .form-select {
            border-radius: 10px;
            border: 1.5px solid #e0e0e0;
            font-size: 13.5px;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #c62828;
            box-shadow: 0 0 0 3px rgba(198, 40, 40, .12);
        }

        .form-label {
            font-size: 13px;
            font-weight: 600;
            color: #444;
        }
    </style>
@endpush

@section('topbar-actions')
    <a href="{{ route('admin.participants.index', $event) }}" class="btn btn-sm btn-light" style="border-radius:10px;">
        <i class="bi bi-arrow-left me-1"></i> Kembali
    </a>
@endsection

@section('content')
    <div class="row justify-content-center">
        <div class="col-12 col-lg-7">
            <div class="p-3 rounded-3 mb-3" style="background:#fff;box-shadow:0 2px 10px rgba(0,0,0,.05);">
                <div style="font-size:12px;color:#aaa;">Event</div>
                <div class="fw-bold">{{ $event->name }}</div>
                @if ($participant->attendance_code)
                    <div style="font-size:12px;color:#1565c0;">
                        Kode Absensi: <strong style="font-family:monospace;">{{ $participant->attendance_code }}</strong>
                    </div>
                @endif
            </div>

            <div class="form-card">
                <h6 class="fw-bold mb-4" style="color:#c62828;font-size:13px;text-transform:uppercase;letter-spacing:.5px;">
                    <i class="bi bi-pencil me-1"></i> Edit Data Peserta
                </h6>

                <form action="{{ route('admin.participants.update', [$event, $participant]) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">NOREG <span class="text-danger">*</span></label>
                            <input type="text" name="noreg" class="form-control"
                                value="{{ old('noreg', $participant->noreg) }}" required>
                            @error('noreg')
                                <div class="text-danger mt-1" style="font-size:12px;">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Nama Peserta <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control"
                                value="{{ old('name', $participant->name) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Kelas</label>
                            <input type="text" name="class" class="form-control"
                                value="{{ old('class', $participant->class) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Asal Sekolah</label>
                            <input type="text" name="school" class="form-control"
                                value="{{ old('school', $participant->school) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Ruang</label>
                            <input type="text" name="room" class="form-control"
                                value="{{ old('room', $participant->room) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Pengawas</label>
                            <input type="text" name="supervisor" class="form-control"
                                value="{{ old('supervisor', $participant->supervisor) }}">
                        </div>
                    </div>

                    <div class="d-flex gap-2 mt-4">
                        <a href="{{ route('admin.participants.index', $event) }}" class="btn btn-light flex-grow-1"
                            style="border-radius:10px;font-weight:600;">Batal</a>
                        <button type="submit" class="btn flex-grow-1 fw-bold"
                            style="background:linear-gradient(135deg,#c62828,#e64a19);color:#fff;border:none;border-radius:10px;">
                            <i class="bi bi-save me-1"></i> Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
