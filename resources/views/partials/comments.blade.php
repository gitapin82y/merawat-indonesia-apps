@forelse($comments as $comment)
<div class="card box-shadow mb-2">
    <div class="d-flex justify-content-between">
        <h3>{{ $comment->is_anonymous ? 'Sahabat Baik' : $comment->name }}</h3>
        <small>{{ $comment->created_at->diffForHumans() }}</small>
    </div>
    
    @php
        $isLiked = false;
        
        if (auth()->check()) {
            $isLiked = $comment->donationLikes()->where('user_id', auth()->id())->exists();
        } elseif (isset($guestIdentifier) && $guestIdentifier) {
            $isLiked = $comment->donationLikes()->where('guest_identifier', $guestIdentifier)->exists();
        }
    @endphp

        <p>{{ $comment->doa }}</p>
    
    <div class="justify-content-between d-flex">
        <a href="javascript:void(0);" class="badge pt-1 px-3 rounded-pill bg-main-opacity text-color fw-normal like-button {{ $isLiked ? 'liked' : '' }}" 
            data-donation-id="{{ $comment->id }}">
            <i class="fa-solid fa-hands-praying"></i> &nbsp; 
            <span class="like-count">{{ $comment->donationLikes->count() }}</span> Aaminn
        </a>
        <h2 class="d-flex mb-0 align-self-center">
            Rp {{ number_format($comment->amount, 0, ',', '.') }} 
            <small class="fw-normal ms-1 mt-1">Donasi Terkirim</small>
        </h2>
    </div>
</div>
@empty
    <div class="text-center">
        <img src="{{ asset('assets/img/icon/success-data.svg') }}" alt="Not Found" class="mb-3" style="width: 150px; height: 150px;">
        <p>Belum ada doa sahabat baik, donasi sekarang dan jadilah orang pertama yang memberikan doa</p>
    </div>
@endforelse