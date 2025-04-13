@extends('layouts.public')
 
@section('title', 'Notifikasi')

@push('after-style')
    <style>
        .notification-container {
            padding: 20px;
            background-color: #f9f9f9;
            border-radius: 8px;
            margin-bottom: 30px;
        }
        
        .notification-item {
            padding: 15px;
            border-bottom: 1px solid #eee;
            margin-bottom: 10px;
            transition: background-color 0.3s;
        }
        
        .notification-item:hover {
            background-color: #f5f5f5;
            cursor: pointer;
        }
        
        .notification-item.unread {
            border-left: 4px solid #4a90e2;
            background-color: #f0f7ff;
        }
        
        .notification-title {
            font-weight: bold;
            margin-bottom: 8px;
        }
        
        .notification-text {
            color: #555;
            margin-bottom: 10px;
        }
        
        .notification-date {
            font-size: 12px;
            color: #888;
            text-align: right;
        }
        
        .img-fluid {
            max-width: 100%;
            height: auto;
            border-radius: 4px;
            margin: 10px 0;
        }
        
        .notification-actions {
            display: flex;
            justify-content: space-between;
            margin-top: 15px;
        }
    </style>
@endpush

@section('content')
    @include('includes.public.navbar-back', ['title' => 'Semua Notifikasi'])
    <main class="container mt-3">
        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif
        
        <div class="notification-actions mb-3">
            <span>{{ $notifications->total() }} Notifikasi</span>
            @if($notifications->where('read_at', null)->count() > 0)
                <form action="{{ route('notifications.mark-all-read') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-primary">
                        Tandai Semua Dibaca
                    </button>
                </form>
            @endif
        </div>
        
        <div class="notification-container">
            @forelse($notifications as $notification)
                <div class="notification-item {{ is_null($notification->read_at) ? 'unread' : '' }}" 
                     data-id="{{ $notification->id }}" 
                     onclick="location.href='{{ route('notifications.show', $notification) }}'">
                    <h5 class="notification-title">{{ $notification->title }}</h5>
                    <p class="notification-text">
                        {{ Str::limit($notification->message, 150) }}
                    </p>
                    @if($notification->image_path)
                        <img src="{{ Storage::url($notification->image_path) }}" alt="Notification Image" class="img-fluid">
                    @endif
                    <p class="notification-date">{{ $notification->created_at->format('d F Y') }}</p>
                    <hr>
                </div>
            @empty
                <div class="text-center p-5">
                    <p>Tidak ada notifikasi</p>
                </div>
            @endforelse
            
            <div class="d-flex justify-content-center mt-4">
                {{ $notifications->links() }}
            </div>
        </div>
    </main>
@endsection

@push('after-script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const markAsRead = function(id) {
                fetch(`/notifications/${id}/mark-as-read`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                }).then(response => response.json())
                  .then(data => {
                      if(data.success) {
                          document.querySelector(`.notification-item[data-id="${id}"]`).classList.remove('unread');
                      }
                  });
            };
            
            // Events are now handled by the onclick attribute on the div for better UX
        });
    </script>
@endpush