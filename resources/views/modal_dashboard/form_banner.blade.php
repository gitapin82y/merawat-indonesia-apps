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

          <div class="form-group mb-3" id="photo_preview_container">
            <img id="photo_preview" src="" alt="Preview" class="img-thumbnail mt-2" style="max-width: 100%; display: none;">
          </div>

          </div>
            <button type="submit" class="btn mt-3 btn-danger w-100">Simpan</button>
        </form>
      </div>
    </div>
  </div>
</div>


<!-- Modal Crop Banner -->
<div class="modal fade" id="cropBannerModal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title">Crop Banner</h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div style="max-width: 100%; margin: 0 auto;">
          <img id="cropBannerImage" src="" style="max-width: 100%; display: block;" />
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
        <button type="button" id="cropBannerButton" class="btn btn-danger">Crop & Simpan</button>
      </div>
    </div>
  </div>
</div>