<div class="modal fade" id="adsenseModal" tabindex="-1" aria-labelledby="adsenseModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header bg-danger text-white">
          <h5 class="modal-title" id="adsenseModalLabel">Pengaturan Iklan</h5>
          <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
            <form id="adsenseForm">
                @csrf
                <input type="hidden" id="adsense_id" name="id">

                @php
                    $fields = [
                        'tiktok_pixel' => 'TikTok Pixel',
                        'facebook_pixel' => 'Facebook Pixel',
                        'facebook_pixel_second' => 'Facebook Pixel 2',
                        'google_analytics_tag' => 'Google Analytics Tag',
                        'meta_token' => 'Meta Token',
                        'meta_endpoint' => 'Meta Endpoint',
                        'google_ads_id' => 'Google Ads ID',
                        'google_ads_label' => 'Google Ads Label',
                        'tiktok_token' => 'TikTok Token',
                        'tiktok_endpoint' => 'TikTok Endpoint'
                    ];
                @endphp

                @foreach ($fields as $name => $label)
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control" id="{{ $name }}" name="{{ $name }}" placeholder="{{ $label }}">
                        <label for="{{ $name }}">{{ $label }}</label>
                        <div class="invalid-feedback" id="error-{{ $name }}"></div>
                    </div>
                @endforeach

                <button type="submit" class="btn btn-danger w-100">Simpan</button>
            </form>
        </div>
      </div>
    </div>
  </div>

