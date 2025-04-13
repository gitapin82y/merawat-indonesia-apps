@extends('layouts.public')
 
@section('title', 'Notifikasi')

@push('after-style')
<style>
          .notification-image {
            max-width: 100%;
            border-radius: 8px;
            margin: 20px 0;
            max-height: 300px;
            width: auto;
        }
      
</style>
@endpush

@section('content')

    @include('includes.public.navbar-back', ['title' => 'Semua Notifikasi'])
    <main class="container mt-3">
      {{-- <div class="notification-actions mb-3">
        <span>{{ $notifications->total() }} Notifikasi</span>
          @if($notifications->where('read_at', null)->count() > 0)
              <form action="{{ route('notifications.mark-all-read') }}" method="POST">
                  @csrf
                  <button type="submit" class="btn btn-sm btn-outline-primary">
                      Tandai Semua Dibaca
                  </button>
              </form>
          @endif
      </div> --}}

        <div class="notification-container">
          @forelse($notifications as $notification)
          <div class="notification-item">

            <h5 class="notification-title">{{ $notification->title }}</h5>

            <p class="notification-text mt-3">
              {{ Str::limit($notification->message, 150) }}
            </p>

            @if($notification->image_path)
                        <img src="{{ Storage::url($notification->image_path) }}" alt="Notification Image" class="notification-image">
            @endif

            <div class="row justify-content-between">
              <div class="col-4 align-self-center text-start">
                <p class="notification-date">{{ $notification->created_at->format('d F Y, H:i') }}</p>
              </div>
              <div class="col-4 align-self-center text-end">
                <form action="{{ route('notifications.destroy', $notification) }}" method="POST">
                  @csrf
                  @method('DELETE')
                      <button type="button" class="btn btn-outline-danger btn-konfirmasi-hapus">
                          <i class="fas fa-trash"></i> Hapus
                      </button>
                  </form>
              </div>
            </div>
            <hr>
          </div>

          @empty
            <div class="text-center p-5">
                <p>Tidak ada notifikasi</p>
            </div>
          @endforelse
{{-- 
          <div class="d-flex justify-content-center mt-4">
            {{ $notifications->links() }}
          </div> --}}

        </div>
    </main>
@endsection

@push('after-script')
<script>
  document.addEventListener('DOMContentLoaded', function () {
      document.querySelectorAll('.btn-konfirmasi-hapus').forEach(function (button) {
          button.addEventListener('click', function (e) {
              const form = this.closest('.form-hapus');
              Swal.fire({
                  title: 'Apakah Anda yakin?',
                  text: "Notifikasi yang dihapus tidak bisa dikembalikan.",
                  icon: 'warning',
                  showCancelButton: true,
                  confirmButtonColor: '#d33',
                  cancelButtonColor: '#6c757d',
                  confirmButtonText: 'Ya, hapus!',
                  cancelButtonText: 'Batal'
              }).then((result) => {
                  if (result.isConfirmed) {
                      form.submit();
                  }
              });
          });
      });
  });
  </script>  
@endpush

