@extends('layouts.app')

@section('title', 'Importar Boletín - SIMM CENADA')
@section('page-title', 'Importar Boletín')

@section('content')

<div class="page-header">
    <div>
        <h1 class="page-header-title">Importar Boletín PDF</h1>
        <p class="page-header-sub">Carga el PDF del SIMM para importar los precios automáticamente</p>
    </div>
    <a href="{{ route('admin.boletines.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Volver
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-12 col-lg-7">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-cloud-upload me-2 text-primary"></i>Seleccionar archivo PDF
            </div>
            <div class="card-body">
                <form action="{{ route('admin.boletines.store') }}" method="POST" enctype="multipart/form-data" id="formImport">
                    @csrf

                    <div class="upload-zone mb-3" id="uploadZone">
                        <i class="bi bi-file-earmark-pdf-fill text-danger"></i>
                        <p class="mb-1 fw-semibold" id="uploadText">Arrastra uno o varios PDFs aquí o haz clic para seleccionar</p>
                        <p class="text-muted small mb-0">Puedes seleccionar múltiples archivos .pdf a la vez</p>
                        <input type="file" name="pdfs[]" id="pdfInput" accept=".pdf" class="d-none" multiple required>
                    </div>

                    @error('pdfs')
                        <div class="alert alert-danger py-2 small">{{ $message }}</div>
                    @enderror
                    @error('pdfs.*')
                        <div class="alert alert-danger py-2 small">{{ $message }}</div>
                    @enderror

                    <div id="listaArchivos" class="mb-3"></div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary" id="btnImport">
                            <i class="bi bi-upload me-2"></i>Importar Boletines
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-body py-3">
                <div class="d-flex align-items-start gap-3">
                    <i class="bi bi-info-circle-fill text-primary mt-1"></i>
                    <div>
                        <p class="mb-1 fw-semibold small">¿Qué se importa?</p>
                        <ul class="mb-0 small text-muted ps-3">
                            <li>Fecha de plaza del boletín</li>
                            <li>Todos los productos con su unidad de medida</li>
                            <li>Precio mínimo, máximo, moda y promedio</li>
                            <li>Tipo de cambio USD del día</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
const uploadZone = $('#uploadZone');
const pdfInput   = $('#pdfInput');

uploadZone.on('click', function (e) {
    if ($(e.target).is(pdfInput)) return;
    pdfInput.trigger('click');
});

uploadZone.on('dragover', function (e) {
    e.preventDefault();
    uploadZone.addClass('dragover');
});

uploadZone.on('dragleave', function () {
    uploadZone.removeClass('dragover');
});

uploadZone.on('drop', function (e) {
    e.preventDefault();
    uploadZone.removeClass('dragover');

    const dt = e.originalEvent.dataTransfer;
    const pdfs = Array.from(dt.files).filter(f => f.type === 'application/pdf');
    if (pdfs.length) {
        pdfInput[0].files = dt.files;
        mostrarArchivos(pdfs);
    }
});

pdfInput.on('change', function () {
    if (this.files.length) mostrarArchivos(Array.from(this.files));
});

function mostrarArchivos(archivos) {
    const total = archivos.length;
    $('#uploadText').text(total === 1 ? '1 archivo seleccionado' : total + ' archivos seleccionados');

    let html = '';
    archivos.forEach(function (f) {
        const kb = (f.size / 1024).toFixed(0);
        html += `<div class="d-flex align-items-center gap-2 py-1 border-bottom">
            <i class="bi bi-file-earmark-pdf text-danger"></i>
            <span class="small flex-grow-1 text-truncate">${f.name}</span>
            <span class="text-muted small">${kb} KB</span>
        </div>`;
    });

    $('#listaArchivos').html(
        `<div class="card border rounded p-2">${html}</div>`
    );
}

$('#formImport').on('submit', function () {
    $('#btnImport').prop('disabled', true).html(
        '<span class="spinner-border spinner-border-sm me-2"></span>Procesando ' + pdfInput[0].files.length + ' archivo(s)...'
    );
});
</script>
@endpush
