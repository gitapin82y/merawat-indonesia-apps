<script src="https://unpkg.com/swiper/swiper-bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js" integrity="sha384-7+zCNj/IqJ95wo16oMtfsKbZ9ccEh31eOz1HGyDuCQ6wgnyJNSYdrPa03rtR1zdB" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.min.js" integrity="sha384-QJHtvGhmr9XOIpI6YVutG+2QOK9T+ZnN4kzFN1RtK3zEFEIsxhlmWl5/YESvpZ13" crossorigin="anonymous"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    var swiper = new Swiper(".mySwiper", {
      spaceBetween: 20,
      navigation: {
        nextEl: ".swiper-button-next",
        prevEl: ".swiper-button-prev",
      },
      pagination: {
        el: ".swiper-pagination",
        clickable: true,
      },
      autoplay: {
      delay: 3000,
      disableOnInteraction: false,
    },
    loop: true, 
    slidesPerView: 1.8,
      breakpoints: {
        1024: {
          slidesPerView: 2.5,
        },
      }
    });

    var swiper = new Swiper(".sliderBanner", {
      spaceBetween: 20,
      navigation: {
        nextEl: ".swiper-button-next",
        prevEl: ".swiper-button-prev",
      },
      pagination: {
        el: ".swiper-pagination",
        clickable: true,
      },
      autoplay: {
      delay: 3000,
      disableOnInteraction: false,
    },
    loop: true, 
    slidesPerView: 1,
    });

    var swiper = new Swiper(".donaturSwiper", {
      navigation: {
        nextEl: ".swiper-button-next",
        prevEl: ".swiper-button-prev",
      },
      pagination: {
        el: ".swiper-pagination",
        clickable: true,
      },
      autoplay: {
      delay: 3000,
      disableOnInteraction: false,
    },
    loop: true, 
    slidesPerView: 5.5,
      breakpoints: {
        1024: {
          slidesPerView: 7.5,
        },
      }
    });

  </script>

<script>
  document.getElementById('phone').addEventListener('input', function (e) {
    let value = e.target.value.replace(/\D/g, ''); // Hapus karakter non-digit

    if (value.startsWith('62')) {
        value = '0' + value.substring(2);
    }

    e.target.value = value;
});
</script>

@if(session('toast'))
  <script>
      Swal.fire({
          icon: '{{ session('toast')['type'] }}',
          text: '{{ session('toast')['message'] }}',
          toast: true,
          position: 'top-end',
          showConfirmButton: false,
          timer: 3000
      });
  </script>
 @endif


 <script>
  // Tambahkan script ini di bagian bawah halaman Anda, sebelum tag </body>
document.addEventListener('DOMContentLoaded', function() {
    // Ambil elemen filter button dan popup
    const filterBtn = document.querySelector('.btn-rectangle:has(img[src*="filter.svg"])');
    const filterPopup = document.getElementById('filterPopup');
    const cariSekarangBtn = document.getElementById('cariSekarangBtn');
    const searchInput = document.querySelector('.search input');
    
    // Toggle popup ketika filter button diklik
    filterBtn.addEventListener('click', function(e) {
        e.preventDefault();
        filterPopup.style.display = filterPopup.style.display === 'none' ? 'flex' : 'none';
    });
    
    // Tutup popup ketika di klik di luar filter-container
    filterPopup.addEventListener('click', function(e) {
        if (e.target === filterPopup) {
            filterPopup.style.display = 'none';
        }
    });
    
    // Fungsi untuk mengumpulkan parameter filter
    function collectFilterParams() {
        const params = new URLSearchParams();
        
        // Ambil nilai dari input pencarian
        if (searchInput && searchInput.value) {
            params.append('title', searchInput.value);
        }
        
        // Ambil kategori yang dipilih
        const selectedCategories = Array.from(
            document.querySelectorAll('input[name="category[]"]:checked')
        ).map(cb => cb.value);
        
        if (selectedCategories.length > 0) {
            selectedCategories.forEach(category => {
                params.append('category[]', category);
            });
        }
        
        // Ambil filter lainnya yang dipilih
        const selectedFilters = Array.from(
            document.querySelectorAll('input[name="filter[]"]:checked')
        ).map(cb => cb.value);
        
        if (selectedFilters.length > 0) {
            selectedFilters.forEach(filter => {
                params.append('filter[]', filter);
            });
        }
        
        return params;
    }
    
    // Handle Cari Sekarang button
    cariSekarangBtn.addEventListener('click', function() {
        const params = collectFilterParams();
        window.location.href = `/eksplore?${params.toString()}`;
    });
    
    // Tambahkan event listener untuk pencarian langsung dari input
    if (searchInput) {
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                const params = new URLSearchParams();
                if (searchInput.value) {
                    params.append('title', searchInput.value);
                }
                window.location.href = `/eksplore?${params.toString()}`;
            }
        });
    }
});
 </script>


