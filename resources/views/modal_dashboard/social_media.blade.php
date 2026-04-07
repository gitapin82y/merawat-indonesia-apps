<div class="modal fade" id="socialMediaModal" tabindex="-1" aria-labelledby="socialMediaModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header bg-danger text-white">
          <h5 class="modal-title" id="socialMediaModalLabel">Pengaturan Media Sosial</h5>
          <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <form id="socialMediaForm">
            <div class="form-group">
              <label for="facebook_url">Facebook</label>
              <input type="url" class="form-control" id="facebook_url" name="social_media[facebook]" placeholder="https://facebook.com/merawatindonesia">
              <div id="error-facebook" class="invalid-feedback"></div>
            </div>
            
            <div class="form-group">
              <label for="instagram_url">Instagram</label>
              <input type="url" class="form-control" id="instagram_url" name="social_media[instagram]" placeholder="https://instagram.com/merawatindonesia">
              <div id="error-instagram" class="invalid-feedback"></div>
            </div>
            
            <div class="form-group">
              <label for="youtube_url">YouTube</label>
              <input type="url" class="form-control" id="youtube_url" name="social_media[youtube]" placeholder="https://youtube.com/merawatindonesia">
              <div id="error-youtube" class="invalid-feedback"></div>
            </div>
            
            <div class="form-group">
              <label for="tiktok_url">TikTok</label>
              <input type="url" class="form-control" id="tiktok_url" name="social_media[tiktok]" placeholder="https://tiktok.com/@merawatindonesia">
              <div id="error-tiktok" class="invalid-feedback"></div>
            </div>

            {{-- Tambahkan SEBELUM tombol Simpan, setelah field TikTok --}}
<div class="form-group">
  <label for="whatsapp_url">WhatsApp (Direct Link)</label>
  <input type="url" class="form-control" id="whatsapp_url" name="social_media[whatsapp]" placeholder="https://wa.me/6281234567890">
  <small class="form-text text-muted">Format: https://wa.me/62xxxx (tanpa tanda + atau spasi)</small>
  <div id="error-whatsapp" class="invalid-feedback"></div>
</div>
            
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
          <button type="button" class="btn btn-danger" id="saveSocialMediaBtn">Simpan</button>
        </div>
      </div>
    </div>
  </div>
