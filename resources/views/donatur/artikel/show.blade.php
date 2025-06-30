@extends('layouts.public')

@section('title', $article->title)

@push('after-style')
<style>
    .share-buttons {
        position: sticky;
        top: 50%;
        transform: translateY(-50%);
        right: 20px;
        z-index: 1000;
        display: flex;
        flex-direction: column;
        gap: 10px;
    }
    
    .share-btn {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        color: white;
        font-size: 20px;
        transition: all 0.3s ease;
        box-shadow: 0 4px 8px rgba(0,0,0,0.2);
    }
    
    .share-btn:hover {
        transform: scale(1.1);
        color: white;
        box-shadow: 0 6px 12px rgba(0,0,0,0.3);
    }
    
    .share-whatsapp {
        background-color: #25D366;
    }
    
    .share-facebook {
        background-color: #1877F2;
    }
    
    .share-instagram {
        background: linear-gradient(45deg, #405DE6, #5851DB, #833AB4, #C13584, #E1306C, #FD1D1D);
    }
    
    .share-tiktok {
        background-color: #000000;
    }
    
    .share-copy {
        background-color: #6c757d;
    }
    
    /* Mobile responsive */
    @media (max-width: 768px) {
        .share-buttons {
            position: fixed;
            bottom: 100px;
            right: 15px;
            top: auto;
            transform: none;
        }
        
        .share-btn {
            width: 45px;
            height: 45px;
            font-size: 18px;
        }
    }
    
    /* Share section di bawah artikel */
    .share-section {
        border-top: 1px solid #e9ecef;
        border-bottom: 1px solid #e9ecef;
        padding: 20px 0;
        margin: 30px 0;
    }
    
    .share-horizontal .share-btn {
        width: 45px;
        height: 45px;
        font-size: 18px;
        margin-right: 10px;
    }
</style>
@endpush

@section('content')
    @include('includes.public.navbar-back', ['title' => 'Detail Artikel'])
     
<div class="container p-3">
    <h1 class="fw-bold mb-2">{{ $article->title }}</h1>
    <p class="text-muted" style="font-size:13px;">{{ $article->created_at->format('d M Y') }}</p>
    @if($article->image)
        <img src="/storage/{{ $article->image }}" class="img-fluid mb-3" style="border-radius:10px;">
    @endif
    <div class="article-content">
        {!! $article->content !!}
    </div>
    
    <!-- Share Section -->
    <div class="share-section text-center">
        <h5 class="mb-3">Bagikan Artikel</h5>
        <div class="share-horizontal d-flex justify-content-center align-items-center">
            <a href="#" class="share-btn share-whatsapp" onclick="shareToWhatsApp()" title="Share ke WhatsApp">
                <i class="fab fa-whatsapp"></i>
            </a>
            <a href="#" class="share-btn share-facebook" onclick="shareToFacebook()" title="Share ke Facebook">
                <i class="fab fa-facebook-f"></i>
            </a>
            <a href="#" class="share-btn share-copy" onclick="copyLink()" title="Copy Link">
                <i class="fas fa-link"></i>
            </a>
        </div>
    </div>
</div>


@include('includes.public.menu')
@endsection

@push('after-script')
<script>
    // Data artikel untuk sharing
    const articleData = {
        title: "{{ addslashes($article->title) }}",
        url: "{{ url()->current() }}",
        description: "{{ addslashes(strip_tags(Str::limit($article->content, 150))) }}",
        image: "{{ $article->image ? asset('storage/'.$article->image) : '' }}"
    };

    function shareToWhatsApp() {
        const text = `*${articleData.title}*\n\n${articleData.description}\n\nBaca selengkapnya: ${articleData.url}`;
        const whatsappUrl = `https://wa.me/?text=${encodeURIComponent(text)}`;
        window.open(whatsappUrl, '_blank');
    }

    function shareToFacebook() {
        const facebookUrl = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(articleData.url)}&quote=${encodeURIComponent(articleData.title)}`;
        window.open(facebookUrl, '_blank', 'width=600,height=400');
    }


    function copyLink() {
        navigator.clipboard.writeText(articleData.url).then(function() {
            Swal.fire({
                icon: 'success',
                title: 'Link Copied!',
                text: 'Link artikel berhasil dicopy ke clipboard.',
                timer: 2000,
                showConfirmButton: false
            });
        }).catch(function() {
            // Fallback untuk browser yang tidak support clipboard API
            const textArea = document.createElement('textarea');
            textArea.value = articleData.url;
            document.body.appendChild(textArea);
            textArea.select();
            document.execCommand('copy');
            document.body.removeChild(textArea);
            
            Swal.fire({
                icon: 'success',
                title: 'Link Copied!',
                text: 'Link artikel berhasil dicopy.',
                timer: 2000,
                showConfirmButton: false
            });
        });
    }

    // Web Share API (untuk mobile yang support)
    if (navigator.share) {
        function nativeShare() {
            navigator.share({
                title: articleData.title,
                text: articleData.description,
                url: articleData.url
            }).catch(console.error);
        }
        
        // Tambahkan tombol native share untuk mobile
        if (window.innerWidth <= 768) {
            const shareSection = document.querySelector('.share-horizontal');
            const nativeShareBtn = document.createElement('a');
            nativeShareBtn.href = '#';
            nativeShareBtn.className = 'share-btn';
            nativeShareBtn.style.backgroundColor = '#007bff';
            nativeShareBtn.innerHTML = '<i class="fas fa-share-alt"></i>';
            nativeShareBtn.title = 'Share';
            nativeShareBtn.onclick = nativeShare;
            shareSection.appendChild(nativeShareBtn);
        }
    }
</script>
@endpush