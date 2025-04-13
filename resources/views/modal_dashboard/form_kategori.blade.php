<div class="modal fade" id="kategoriFormModal" tabindex="-1" aria-labelledby="kategoriLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title" id="kategoriLabel">Manage Kategori</h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form id="kategoriForm">
          <div class="modal-body">
            <input type="hidden" id="kategori_id">
                    <!-- Nama Kategori -->
        <div class="form-floating mb-3">
          <input type="text" class="form-control" id="kategori_name" name="name" placeholder="Nama Kategori">
          <label for="kategori_name">Nama Kategori</label>
          <div class="invalid-feedback" id="error-name">
            @error('name') {{ $message }} @enderror
        </div>
          </div>

          <!-- Ikon Kategori -->
          <div class="form-group mb-3">
              <input type="file" class="form-control" id="kategori_icon" name="icon" accept="image/*">
              <div class="invalid-feedback" id="error-icon">
                @error('icon') {{ $message }} @enderror
            </div>
            </div>

          <!-- Preview Ikon -->
          <div class="preview mt-2 text-left">
              <img id="icon_preview" src="" class="img-thumbnail" style="max-width: 100px; display: none;">
          </div>
          </div>
            <button type="submit" class="btn mt-3 btn-danger w-100">Simpan</button>
        </form>
      </div>
    </div>
  </div>
</div>