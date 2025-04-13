<div class="modal fade" id="bannerFormModal" tabindex="-1" aria-labelledby="bannerLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title" id="bannerLabel">Tambah Banner</h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form id="bannerForm">
          <div class="modal-body">
            <input type="hidden" id="banner_id">
    
          <!-- Ikon Banner -->
          <div class="form-group mb-3">
              <input type="file" class="form-control" id="banner_photo" name="photo" accept="image/*">
              <div class="invalid-feedback" id="error-photo">
                @error('photo') {{ $message }} @enderror
            </div>
            </div>

          <!-- Preview Ikon -->
          <div class="preview mt-2 text-left">
              <img id="photo_preview" src="" class="img-thumbnail" style="max-width: 100px; display: none;">
          </div>
          </div>
            <button type="submit" class="btn mt-3 btn-danger w-100">Simpan</button>
        </form>
      </div>
    </div>
  </div>
</div>