@extends('layouts.public')
 
@section('title', $notification->title)

@push('after-style')
    <style>
        .notification-container {
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        
        .notification-title {
            font-weight: bold;
            margin-bottom: 20px;
            font-size: 1.5rem;
        }
        
        .notification-meta {
            color: #888;
            font-size: 0.9rem;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
        }
        
        .notification-body {
            line-height: 1.6;
            margin-bottom: 20px;
        }
        
        .notification-image {
            width: 100%;
            border-radius: 8px;
            margin: 20px 0;
        }
        
        .notification-actions {
            margin-top: 20px;
            display: flex;
            justify-content: flex-end;
        }
    </style>
@endpush

@section('content')
    @include('includes.public.navbar-back', ['title' => 'Detail Notifikasi'])
    <main class="container mt-3">
        <div class="notification-container">
            <h1 class="notification-title">{{ $notification->title }}</h1>
            
            <div class="notification-meta">
                <span>{{ $notification->created_at->format('d F Y, H:i') }}</span>
                <span>{{ ucfirst($notification->type) }}</span>
            </div>
            
            <div class="notification-body">
                {!! nl2br(e($notification->message)) !!}
            </div>
            
            @if($notification->image_path)
                <img src="{{ Storage::url($notification->image_path) }}" alt="Notification Image" class="notification-image">
            @endif
            
            <div class="notification-actions">
                <form action="{{ route('notifications.destroy', $notification) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus notifikasi ini?')">
                        <i class="fas fa-trash"></i> Hapus
                    </button>
                </form>
            </div>
        </div>
    </main>
@endsection