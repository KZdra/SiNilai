
import Swal from 'sweetalert2';

export function showSuccess(message = 'Berhasil!', title = 'Sukses') {
  return Swal.fire({
    title,
    text: message,
    icon: 'success',
    showConfirmButton: false,
    timer: 1500
  });
}

export function showError(message = 'Terjadi kesalahan!', title = 'Error') {
    Swal.fire({
        icon: "error",
        title: title,
        text: message,
        footer: '<p>Ada Masalah? Hubungi Admin</p>'
      });
}

export function showWarning(message = 'Peringatan!', title = 'Warning') {
  return Swal.fire({
    title,
    text: message,
    icon: 'warning',
    confirmButtonText: 'OK'
  });
}

export function showInfo(message = 'Informasi', title = 'Info') {
  return Swal.fire({
    title,
    text: message,
    icon: 'info',
    confirmButtonText: 'OK'
  });
}

export function showConfirm(message = 'Apakah Anda yakin?', title = 'Konfirmasi') {
  return Swal.fire({
    title,
    text: message,
    icon: 'question',
    showCancelButton: true,
    confirmButtonText: 'Ya',
    cancelButtonText: 'Batal'
  });
}
