@extends('layouts.public')
 
@section('title', 'Kalkulator Zakat')

@push('after-style')

@endpush

@section('content')

    @include('includes.public.navbar-back', ['title' => 'Kalkulator Zakat'])

    <div class="row mx-0 container">
        <div class="card box-shadow my-4 pt-4 pb-2">
            <div class="row mx-0">
                <div class="col-4 col-md-3 align-self-center">
                    <img src="{{asset('assets/img/icon/form-data.svg')}}" width="100%">
                </div>
                <div class="col-8 col-md-9">
                    <h2>Kalkulator Zakat Profesi & Maal</h2>
                    <p>Kalkulator ini membantu Anda menghitung zakat profesi berdasarkan penghasilan bulanan dan
                        zakat maal berdasarkan total harta tahunan.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row container mb-4 mx-0 px-3" style="padding-bottom: 300px;">
        <div class="d-flex justify-content-center gap-2 mb-3">
            <button class="btn btn-outline-danger tab-button w-50" data-target="profesi">Profesi</button>
            <button class="btn btn-outline-danger tab-button w-50" data-target="maal">Maal</button>
        </div>

        <!-- Content Sections -->
        <div class="tab-content profesi">
            <form>
                <div class="form-floating mb-3">
                    <input type="text" class="form-control" id="penghasilan_utama"
                        placeholder="Total Penghasilan Utama">
                    <label for="penghasilan_utama">Total Penghasilan Utama</label>
                </div>
                <div class="form-floating mb-3">
                    <input type="text" class="form-control" id="penghasilan_lain"
                        placeholder="Total Penghasilan Lain (jika ada)">
                    <label for="penghasilan_lain">Total Penghasilan Lain (jika ada)</label>
                </div>
                <div class="form-floating mb-3">
                    <input type="text" class="form-control" id="hutang_profesi"
                        placeholder="Total Hutang/Cicilan (jika ada)">
                    <label for="hutang_profesi">Total Hutang/Cicilan (jika ada)</label>
                </div>
            </form>
        </div>

        <div class="tab-content maal d-none">
            <form>
                <div class="form-floating mb-3">
                    <input type="text" class="form-control" id="total_tabungan" placeholder="Total Tabungan">
                    <label for="total_tabungan">Total Tabungan</label>
                </div>
                <div class="form-floating mb-3">
                    <input type="text" class="form-control" id="total_emas"
                        placeholder="Total Emas & Perak (jika ada)">
                    <label for="total_emas">Total Emas & Perak (jika ada)</label>
                </div>
                <div class="form-floating mb-3">
                    <input type="text" class="form-control" id="total_properti"
                        placeholder="Total Properti & Aset Investasi">
                    <label for="total_properti">Total Properti & Aset Investasi</label>
                </div>
            </form>
        </div>
    </div>

    <div class="footer">
        <div class="main-menu row col-12 mx-0 justify-content-between d-flex">
            <a href="javascript:void(0)" id="hitungZakat" class="button w-100">Hitung Zakat</a>
        </div>
    </div>
    

@endsection

@push('after-script')
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js"
integrity="sha384-7+zCNj/IqJ95wo16oMtfsKbZ9ccEh31eOz1HGyDuCQ6wgnyJNSYdrPa03rtR1zdB" crossorigin="anonymous">
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.min.js"
integrity="sha384-QJHtvGhmr9XOIpI6YVutG+2QOK9T+ZnN4kzFN1RtK3zEFEIsxhlmWl5/YESvpZ13" crossorigin="anonymous">
</script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function () {
        const DEFAULT_GOLD_PRICE = 1000000; // Rp 1,000,000 per gram
        const DEFAULT_RICE_PRICE = 12000;   // Rp 12,000 per kg
        const GOLD_NISAB = 85;              // 85 grams
        const RICE_NISAB = 524;             // 524 kg

        let goldPrice = DEFAULT_GOLD_PRICE;
        let ricePrice = DEFAULT_RICE_PRICE;
        
        $(".tab-button").click(function () {
            $(".tab-button").removeClass("bg-white text-dark").addClass("bg-danger text-white");
            $(this).removeClass("bg-danger text-white").addClass("bg-white text-dark");

            $(".tab-content").addClass("d-none");
            $("." + $(this).data("target")).removeClass("d-none");
        });

        $("#penghasilan_utama").attr("required", true);
        $("#total_tabungan").attr("required", true);
        $("#total_emas").attr("required", true);
    

        $("#hitungZakat").click(function () {
            let activeTab = $(".tab-content:not(.d-none)").attr("class").split(" ")[1];
        let zakatAmount = 0;
        let explanation = "";
        let isValid = true;
        let missingFields = [];
        
        if (activeTab === "profesi") {
            if (!$("#penghasilan_utama").val()) {
                isValid = false;
                missingFields.push("Penghasilan Utama");
                $("#penghasilan_utama").addClass("is-invalid");
            } else {
                $("#penghasilan_utama").removeClass("is-invalid");
            }
        } else if (activeTab === "maal") {
            // For Maal, require at least one of the fields to have a value
            if (!$("#total_tabungan").val() && !$("#total_emas").val() && !$("#total_properti").val()) {
                isValid = false;
                missingFields.push("minimal satu jenis harta (Tabungan, Emas, atau Properti)");
                $("#total_tabungan, #total_emas, #total_properti").addClass("is-invalid");
            } else {
                $("#total_tabungan, #total_emas, #total_properti").removeClass("is-invalid");
            }
        }

        if (!isValid) {
            Swal.fire({
                icon: 'error',
                title: 'Form Belum Lengkap',
                html: `Mohon isi ${missingFields.join(", ")} untuk menghitung zakat.`,
                confirmButtonText: 'OK',
                confirmButtonColor: '#D80000'
            });
            return;
        }
        
        if (activeTab === "profesi") {
            // Get input values for profesi
            let penghasilanUtamaVal = $("#penghasilan_utama").val();
            let penghasilanLainVal = $("#penghasilan_lain").val();
            let hutangProfesiVal = $("#hutang_profesi").val();

            // Extract numeric values from formatted strings
            let penghasilanUtama = penghasilanUtamaVal ? parseFloat(penghasilanUtamaVal.replace(/[^\d]/g, '')) : 0;
            let penghasilanLain = penghasilanLainVal ? parseFloat(penghasilanLainVal.replace(/[^\d]/g, '')) : 0;
            let hutang = hutangProfesiVal ? parseFloat(hutangProfesiVal.replace(/[^\d]/g, '')) : 0;
            
            
            // Calculate total income minus debts
            let totalPenghasilan = penghasilanUtama + penghasilanLain - hutang;
            
            // Nisab calculation (85 grams of gold at approx Rp 1,000,000 per gram)
            const nisabBeras = RICE_NISAB * ricePrice;
            
            // If total income is above nisab, calculate 2.5% zakat
            if (totalPenghasilan >= nisabBeras) {
                zakatAmount = totalPenghasilan * 0.025;
                explanation = `Zakat Profesi: Penghasilan Rp ${(penghasilanUtama + penghasilanLain).toLocaleString('id-ID')} - Hutang Rp ${hutang.toLocaleString('id-ID')} × 2.5%`;
                isEligible = true;
            } else {
                zakatAmount = totalPenghasilan * 0.025;
                explanation = `Zakat kamu belum memenuhi nisab yang setara dengan harga ${RICE_NISAB}kg beras (Rp ${nisabBeras.toLocaleString('id-ID')})`;
                isEligible = false;
            }
        } else if (activeTab === "maal") {
            // Get input values for maal
            let totalTabunganVal = $("#total_tabungan").val();
            let totalEmasVal = $("#total_emas").val();
            let totalPropertiVal = $("#total_properti").val();

            let totalTabungan = totalTabunganVal ? parseFloat(totalTabunganVal.replace(/[^\d]/g, '')) : 0;
            let totalEmas = totalEmasVal ? parseFloat(totalEmasVal.replace(/[^\d]/g, '')) : 0;
            let totalProperti = totalPropertiVal ? parseFloat(totalPropertiVal.replace(/[^\d]/g, '')) : 0;
            
            
            // Calculate total assets
            let totalHarta = totalTabungan + totalEmas + totalProperti;
            
            // Nisab calculation for maal
            const nisabEmas = GOLD_NISAB * goldPrice;
            
            // If total assets are above nisab, calculate 2.5% zakat
            if (totalHarta >= nisabEmas) {
                zakatAmount = totalHarta * 0.025;
                explanation = `Zakat Maal: Total harta Rp ${totalHarta.toLocaleString('id-ID')} × 2.5%`;
                isEligible = true;
            } else {
                zakatAmount = totalHarta * 0.025;
                explanation = `Zakat kamu belum memenuhi nisab yang setara dengan harga ${GOLD_NISAB} gram emas (Rp ${nisabEmas.toLocaleString('id-ID')})`;
                isEligible = false;
            }
        }

        const formattedZakat = Math.round(zakatAmount).toLocaleString('id-ID');
        const rawZakat = Math.round(zakatAmount);


            Swal.fire({
                html: `
 <div style="text-align: center; padding-bottom:10px;">
        <h2>Hasil Perhitungan Zakat </h2>
          <p>${explanation}</p>
    </div>



    <div style="text-align: center;">
        <div style="display: inline-block; background-color: #FFECEC; color: #D80000; padding: 10px; border-radius: 10px; font-size: 20px; font-weight: bold;">
            Rp ${formattedZakat}
        </div>
    </div>

    <button id="copyNominal" style="margin-top: 10px; border: 1px solid #D80000; background: none; color: #D80000; font-size:10px;padding: 5px 10px; border-radius: 5px;">Salin Nominal</button>

     <p id="success-message" style="color: #49de67; font-size: 12px; margin-top: 7px; display: none;">Nominal berhasil disalin!</p>

    <div class="row pt-4 mx-0">
    <a id="cariProgram" class="button" style="width:100%">Cari Program Zakat</a>
    </div>
`,
                showConfirmButton: false,
                didOpen: () => {
                    $("#copyNominal").click(function () {
                        navigator.clipboard.writeText(rawZakat.toString()).then(() => {
                            $("#success-message")
                        .fadeIn(); // Menampilkan teks kecil dengan animasi
                        });
                    });

                    $("#cariProgram").click(function () {
                        window.location.href = '/eksplore-kampanye'; // Ganti dengan link tujuan
                    });
                }
            });

        });
    });



    // Format input as currency with automatic formatting
    $(".form-control").on('input', function(e) {
        // Store cursor position
        let cursorPos = this.selectionStart;
        let originalLength = this.value.length;
        
        // Get just the digits
        let value = this.value.replace(/[^\d]/g, '');
        
        // If empty, just clear the field
        if (value === '') {
            $(this).val('');
            return;
        }
        
        // Convert to number and format with dot separators
        let formattedValue = 'Rp ' + parseInt(value).toLocaleString('id-ID');
        
        // Update the value
        $(this).val(formattedValue);
        
        // Adjust cursor position based on length difference
        let newLength = formattedValue.length;
        let lengthDiff = newLength - originalLength;
        
        // Set cursor position
        cursorPos = cursorPos + lengthDiff;
        this.setSelectionRange(cursorPos, cursorPos);
    });

    // Handle focus - select all text when the input is focused
    $(".form-control").on('focus', function() {
        // If input is empty, don't do anything
        if (!this.value) return;
        
        // Otherwise, select all text
        this.select();
    });

</script>

<script>
    // Toggle icon on accordion collapse
    document.querySelectorAll('.accordion-button').forEach(button => {
        button.addEventListener('click', () => {
            const icon = button.querySelector('i');
            icon.classList.toggle('fa-chevron-up');
            icon.classList.toggle('fa-chevron-down');
        });
    });
</script>
@endpush

