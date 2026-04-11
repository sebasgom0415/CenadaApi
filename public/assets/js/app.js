$(function () {
    // Sidebar toggle
    $('#sidebarToggle').on('click', function () {
        $('body').toggleClass('sidebar-collapsed');
        localStorage.setItem('sidebar_collapsed', $('body').hasClass('sidebar-collapsed'));
    });

    // Restore sidebar state
    if (localStorage.getItem('sidebar_collapsed') === 'true') {
        $('body').addClass('sidebar-collapsed');
    }

    // CSRF token for AJAX
    $.ajaxSetup({
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
    });

    // Auto-dismiss alerts
    setTimeout(function () {
        $('.alert').fadeOut('slow');
    }, 4000);
});

// SweetAlert helpers
const Toast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 3000,
    timerProgressBar: true
});

function confirmDelete(url, itemName) {
    Swal.fire({
        title: '¿Eliminar?',
        text: 'Se eliminará: ' + itemName,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e53e3e',
        cancelButtonColor: '#718096',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        borderRadius: '10px'
    }).then(function (result) {
        if (result.isConfirmed) {
            $.ajax({
                url: url,
                type: 'DELETE',
                success: function () {
                    Toast.fire({ icon: 'success', title: 'Eliminado correctamente' });
                    setTimeout(function () { location.reload(); }, 1000);
                },
                error: function () {
                    Toast.fire({ icon: 'error', title: 'Error al eliminar' });
                }
            });
        }
    });
}
