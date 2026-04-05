<div class="ru-dropzone @error('resume') border-danger @enderror" id="uploadZone">
    <input type="file"
           name="resume"
           id="resumeFileInput"
           class="ru-dropzone-file-input"
           accept=".pdf,application/pdf"
           required>

    <div class="ru-dz-icon">
        <i class="uil uil-file-upload-alt" id="ruDzIcon"></i>
    </div>
    <p class="ru-dz-title mb-1" id="ruDzTitle">Drop your resume here</p>
    <p class="ru-dz-sub mb-2" id="ruDzSub">
        or <button type="button" class="ru-browse-btn" id="ruBrowseBtn">
            <i class="uil uil-folder-open me-1"></i>Browse files
        </button>
    </p>
    <p class="ru-dz-note mb-0">PDF only · Max 10 MB</p>

    <div class="ru-file-chip d-none" id="ru-file-chip">
        <i class="uil uil-file-alt text-success"></i>
        <span id="ru-file-text"></span>
        <span class="ru-remove-file ms-1" id="ruRemoveFile" title="Remove">
            <i class="uil uil-times"></i>
        </span>
    </div>
</div>
@error('resume')
    <div class="text-danger small mt-1"><i class="uil uil-exclamation-circle me-1"></i>{{ $message }}</div>
@enderror
