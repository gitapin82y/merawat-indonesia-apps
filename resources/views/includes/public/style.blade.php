<link rel="icon" type="image/png" href="{{asset('assets/img/favicon.png')}}">
<link rel="icon" type="image/png" sizes="16x16" href="{{asset('assets/img/favicon-16x16.png')}}">
<link rel="icon" type="image/png" sizes="32x32" href="{{asset('assets/img/favicon-32x32.png')}}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
<link rel="stylesheet" href="https://unpkg.com/swiper/swiper-bundle.min.css" />
<link rel="stylesheet" href="{{asset('assets/css/style.css')}}">
<style>
    body {
        font-family: Arial, sans-serif;
        background-color: #fce8e8;
        color: #333;
    }
    .donaturSwiper .swiper-slide {
    margin-right: 15px; /* Adjust this value for spacing */
  }
  .social-icon img {
        width: auto;
        height: 40px;
        object-fit: contain;
    }
    .image-campaign-card{
        width: 100%;
        aspect-ratio: 2 / 1.1; /* atau 4 / 3, tergantung proporsi gambar yang diinginkan */
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
        border-radius: 5px;
    }
    .count-notification{
        color: #FF4747;
        right: -5px;
        top: -5px;
        position: absolute !important;
        background-color: white;
        border-radius: 20px;
        width: 20px;
        font-size: 12px;
        text-align: center;
        height: 20px;
        border: 1px solid #FF4747;
    }
    .filter-popup {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background-color: #fff;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    border-radius: 0 0 8px 8px;
    padding: 20px;
    z-index: 1000;
    display: none;
    max-width: 100%;
    margin: 0 auto;
}

.filter-popup.show {
    display: block;
}

.filter-popup h5 {
    font-weight: 600;
    margin-bottom: 15px;
    color: #333;
}

.filter-options {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.form-check {
    display: flex;
    align-items: center;
    margin: 0;
}

.form-check-input {
    margin-right: 10px;
    width: 20px;
    height: 20px;
    cursor: pointer;
}

.form-check-label {
    font-size: 14px;
    cursor: pointer;
}

.btn-danger {
    background-color: #e74c3c;
    border: none;
    padding: 10px 25px;
    font-weight: 600;
    border-radius: 5px;
}

.btn-danger:hover {
    background-color: #c0392b;
}

/* Fix for navbar positioning */
.navbar {
    position: relative;
}

.alert-success{
    background-color: #198754 !important;
}

/* Mobile responsiveness */
@media (max-width: 768px) {
    .filter-popup .row .col-md-6:last-child {
        margin-top: 20px;
    }
}
</style>