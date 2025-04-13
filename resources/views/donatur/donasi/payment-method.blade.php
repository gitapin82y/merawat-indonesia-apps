@extends('layouts.public')
 
@section('title', 'Pilih Metode Pembayaran')

@push('after-style')
<style>
    .payment-method-card {
        cursor: pointer;
        transition: all 0.3s ease;
        border: 2px solid #f8f9fa;
    }
    
    .payment-method-card.selected {
        border: 2px solid #dc3545;
        background-color: rgba(220, 53, 69, 0.1);
    }

    .payment-type-tabs .nav-link {
        color: #495057;
        background-color: #fff;
        border-color: #dee2e6 #dee2e6 #fff;
    }

    .payment-type-tabs .nav-link.active {
        color: #fff;
        background-color: #dc3545;
        border-color: #dc3545;
    }
</style>
@endpush

@section('content')

@include('includes.public.navbar-back', ['title' => 'Pilih Metode Pembayaran'])

<div class="container mb-5">
    <div class="card">
        <div class="card-body">
            <!-- Ringkasan Donasi -->
            <div class="donation-summary mb-4">
                <h5>Ringkasan Donasi:</h5>
                <div class="row">
                    <div class="col-7">
                        <p class="mb-1"><strong>Kampanye:</strong></p>
                        <p class="mb-1"><strong>Nama:</strong></p>
                        <p class="mb-1"><strong>Jumlah Donasi:</strong></p>
                    </div>
                    <div class="col-5 text-end">
                        <p class="mb-1">{{ $campaign->title }}</p>
                        <p class="mb-1">{{ $donation->is_anonymous ? 'Orang Baik' : $donation->name }}</p>
                        <p class="mb-1 text-danger fw-bold">Rp {{ number_format($donation->amount) }}</p>
                    </div>
                </div>
            </div>
            
            <!-- Tabs untuk tipe pembayaran -->
            <ul class="nav nav-tabs payment-type-tabs mb-3" id="paymentTypeTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="gateway-tab" data-bs-toggle="tab" data-bs-target="#gateway-content" type="button" role="tab" aria-controls="gateway-content" aria-selected="true">
                        <i class="fa-solid fa-credit-card me-1"></i> Pembayaran Otomatis
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="manual-tab" data-bs-toggle="tab" data-bs-target="#manual-content" type="button" role="tab" aria-controls="manual-content" aria-selected="false">
                        <i class="fa-solid fa-money-bill-transfer me-1"></i> Pembayaran Manual
                    </button>
                </li>
            </ul>
            
            <!-- Tab contents -->
            <div class="tab-content" id="paymentTypeTabsContent">
                <!-- Gateway Payment -->
                <div class="tab-pane fade show active" id="gateway-content" role="tabpanel" aria-labelledby="gateway-tab">
                    <form id="gatewayForm" action="{{ route('donations.process-payment') }}" method="POST">
                        @csrf
                        <input type="hidden" name="donation_id" value="{{ $donation->id }}">
                        <input type="hidden" name="payment_type" value="payment_gateway">
                        <input type="hidden" name="selected_payment_method" id="selected_gateway_method" value="">
                        
                        <div class="payment-methods">
                            @foreach($channels as $channel)
                            <div class="payment-method-card card mb-3" data-method="{{ $channel['code'] }}">
                                <div class="card-body d-flex justify-content-between align-items-center py-2">
                                    <div class="d-flex align-items-center">
                                        @if(isset($channel['icon_url']) && $channel['icon_url'])
                                            <img src="{{ $channel['icon_url'] }}" alt="{{ $channel['name'] }}" height="30" class="me-3">
                                        @endif
                                        <div>
                                            <h6 class="mb-0">{{ $channel['name'] }}</h6>
                                            @if(isset($channel['fee_merchant']['flat']))
                                                <small class="text-muted">Biaya: Rp {{ number_format($channel['fee_merchant']['flat']) }}</small>
                                            @endif
                                        </div>
                                    </div>
                                    <i class="fa-solid fa-circle-check text-success payment-check-icon d-none"></i>
                                </div>
                            </div>
                            @endforeach
                        </div>

                        <div class="footer mb-5 text-center">
                            <div class="main-menu row col-12 mx-0 justify-content-between d-flex ">
                                <button type="submit" id="continueGatewayBtn" class="button bg-danger w-100 d-flex align-items-center justify-content-center text-white shadow-sm" disabled>
                                    <img src="{{asset('assets/img/icon/edit-profile.svg')}}" alt="Kirim" style="width: 20px; height: 20px; margin-right: 8px;" />
                                    <span class="text-white">Lanjutkan Pembayaran</span>
                                </button>
                            </div>
                        </div>
                        
                    </form>
                </div>
                
                <!-- Manual Payment -->
                <div class="tab-pane fade" id="manual-content" role="tabpanel" aria-labelledby="manual-tab">
                    <h5 class="mb-3">Metode Pembayaran Manual (Verifikasi Admin 1x24 Jam)</h5>
                    <form id="manualForm" action="{{ route('donations.process-manual-payment') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="donation_id" value="{{ $donation->id }}">
                        <input type="hidden" name="payment_type" value="manual">
                        <input type="hidden" name="selected_payment_method" id="selected_manual_method" value="">
                        
                        <div class="payment-methods">
                            @foreach($manualMethods as $method)
                            <div class="payment-method-card card mb-3" data-method="{{ $method->id }}">
                                <div class="card-body d-flex justify-content-between align-items-center py-2">
                                    <div class="d-flex align-items-center">
                                        @if($method->icon)
                                            <img src="{{ asset('storage/' . $method->icon) }}" alt="{{ $method->name }}" height="30" class="me-3">
                                        @else
                                            <div class="bg-light rounded me-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 30px">
                                                <i class="fa-solid fa-building-columns text-muted"></i>
                                            </div>
                                        @endif
                                        <div>
                                            <h6 class="mb-0">{{ $method->name }}</h6>
                                            <small class="text-muted">{{ $method->account_number }} ({{ $method->account_name }})</small>
                                        </div>
                                    </div>
                                    <i class="fa-solid fa-circle-check text-success payment-check-icon d-none"></i>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        
                        <div id="manualPaymentDetails" class="mt-4 d-none">
                            <div class="alert alert-info">
                                <i class="fa-solid fa-info-circle me-1"></i> Silakan transfer sesuai nominal donasi dan upload bukti transfer
                            </div>
                            
                            <div class="selected-method-details mb-3">
                                <h6>Detail Pembayaran:</h6>
                                <div id="methodDetails" class="bg-light p-3 rounded">
                                    <!-- Will be filled by JavaScript -->
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="payment_proof" class="form-label">Upload Bukti Transfer <span class="text-danger">*</span></label>
                                <input type="file" class="form-control" id="payment_proof" name="payment_proof" required accept="image/*">
                                <div class="form-text">Format: JPG, PNG, JPEG (Maks. 2MB)</div>
                            </div>

                            <div class="footer mb-5 text-center">
                                <div class="main-menu row col-12 mx-0 justify-content-between d-flex ">
                                    <button type="submit" id="submitManualBtn" class="button bg-danger w-100 d-flex align-items-center justify-content-center text-white shadow-sm">
                                        <img src="{{asset('assets/img/icon/edit-profile.svg')}}" alt="Kirim" style="width: 20px; height: 20px; margin-right: 8px;" />
                                        <span class="text-white">Kirim Bukti Pembayaran</span>
                                    </button>
                                </div>
                            </div>

                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@include('includes.public.menu')

@endsection

@push('after-script')
<script>
$(document).ready(function() {
    // Gateway payment method selection
    $('#gateway-content .payment-method-card').click(function() {
        $('#gateway-content .payment-method-card').removeClass('selected');
        $('#gateway-content .payment-check-icon').addClass('d-none');
        
        $(this).addClass('selected');
        $(this).find('.payment-check-icon').removeClass('d-none');
        
        const selectedMethod = $(this).data('method');
        $('#selected_gateway_method').val(selectedMethod);
        $('#continueGatewayBtn').prop('disabled', false);
    });
    
    // Manual payment method selection
    $('#manual-content .payment-method-card').click(function() {
        $('#manual-content .payment-method-card').removeClass('selected');
        $('#manual-content .payment-check-icon').addClass('d-none');
        
        $(this).addClass('selected');
        $(this).find('.payment-check-icon').removeClass('d-none');
        
        const selectedMethod = $(this).data('method');
        $('#selected_manual_method').val(selectedMethod);
        
        // Show payment details section
        $('#manualPaymentDetails').removeClass('d-none');
        
        // Update method details
        const methodName = $(this).find('h6').text();
        const methodInfo = $(this).find('small').text();
        
        $('#methodDetails').html(`
            <p class="mb-1"><strong>Bank/E-wallet:</strong> ${methodName}</p>
            <p class="mb-1"><strong>Nomor Rekening:</strong> ${methodInfo}</p>
            <p class="mb-1"><strong>Nominal Transfer:</strong> <span class="text-danger">Rp {{ number_format($donation->amount) }}</span></p>
            <p class="mb-0 mt-2 small text-muted">Harap transfer dengan jumlah tepat sesuai nominal di atas.</p>
        `);
    });
    
    // Form validation
    $('#manualForm').submit(function(e) {
        if (!$('#selected_manual_method').val()) {
            e.preventDefault();
            alert('Silakan pilih metode pembayaran terlebih dahulu');
            return false;
        }
        
        if (!$('#payment_proof').val()) {
            e.preventDefault();
            alert('Silakan upload bukti pembayaran');
            return false;
        }
    });
    
    $('#gatewayForm').submit(function(e) {
        if (!$('#selected_gateway_method').val()) {
            e.preventDefault();
            alert('Silakan pilih metode pembayaran terlebih dahulu');
            return false;
        }
    });
});
</script>
@endpush