document.addEventListener('DOMContentLoaded', function () {
    const fileInputs = document.querySelectorAll('input[type="file"]');

    fileInputs.forEach(input => {
        // Skip if already processed or explicitly excluded
        if (input.classList.contains('no-drag-drop') || input.closest('.drag-drop-zone')) return;

        createDragDropZone(input);
    });
});

function createDragDropZone(input) {
    // 1. Create Zone Elements
    const zone = document.createElement('div');
    zone.className = 'drag-drop-zone';

    const icon = document.createElement('i');
    icon.className = 'fa-solid fa-cloud-arrow-up icon';

    const text = document.createElement('div');
    text.className = 'text';
    text.textContent = 'ลากไฟล์มาวางที่นี่ หรือ คลิกเพื่อเลือกไฟล์';

    const subText = document.createElement('div');
    subText.className = 'text-sm';
    
    // Check for accept attribute to show helpful text
    const accept = input.getAttribute('accept');
    if (accept) {
        if (accept.includes('image')) {
            subText.textContent = 'รองรับไฟล์รูปภาพ (JPG, PNG, GIF)';
        } else if (accept.includes('pdf')) {
            subText.textContent = 'รองรับไฟล์เอกสาร PDF';
        } else {
            subText.textContent = 'รองรับไฟล์ ' + accept;
        }
    } else {
        subText.textContent = 'รองรับทุกประเภทไฟล์';
    }

    const previewContainer = document.createElement('div');
    previewContainer.className = 'drag-drop-preview';

    // 2. Insert Zone and Move Input
    input.parentNode.insertBefore(zone, input);
    zone.appendChild(input);
    zone.appendChild(icon);
    zone.appendChild(text);
    zone.appendChild(subText);
    zone.appendChild(previewContainer);

    // 3. Style Input
    input.classList.add('drag-drop-input');

    // 4. Check for existing image (if editing)
    // We look for a preceding <img> tag or div container that might hold the current image
    // This is heuristic-based on common patterns usually found in forms
    const container = zone.parentElement;
    const existingImg = container.querySelector('img:not(.logo-img)'); // Exclude logo if any
    if (existingImg && existingImg.parentNode !== zone) {
        // If there's an existing image right before/after specific labels, we might want to move it into preview
        // For now, let's just leave existing images where they are to avoid breaking layout, 
        // as the preview area is mostly for *newly* selected files.
    }

    // 5. Build Event Listeners
    zone.addEventListener('click', () => input.click());

    input.addEventListener('change', () => {
        handleFiles(input.files, previewContainer);
    });

    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        zone.addEventListener(eventName, preventDefaults, false);
    });

    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }

    ['dragenter', 'dragover'].forEach(eventName => {
        zone.addEventListener(eventName, highlight, false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        zone.addEventListener(eventName, unhighlight, false);
    });

    function highlight(e) {
        zone.classList.add('dragover');
    }

    function unhighlight(e) {
        zone.classList.remove('dragover');
    }

    zone.addEventListener('drop', handleDrop, false);

    function handleDrop(e) {
        const dt = e.dataTransfer;
        const files = dt.files;
        
        if (files.length > 0) {
            input.files = files; // Update input files
            handleFiles(files, previewContainer);
        }
    }
}

function handleFiles(files, previewContainer) {
    previewContainer.innerHTML = ''; // Clear previous previews

    [...files].forEach(file => {
        const reader = new FileReader();

        const previewItem = document.createElement('div');
        previewItem.className = 'preview-item';

        if (file.type.startsWith('image/')) {
            reader.readAsDataURL(file);
            reader.onloadend = function() {
                const img = document.createElement('img');
                img.src = reader.result;
                previewItem.appendChild(img);
                previewContainer.appendChild(previewItem);
            }
        } else {
            // Non-image file
            const fileInfo = document.createElement('div');
            fileInfo.className = 'file-info';
            
            const icon = document.createElement('i');
            icon.className = 'fa-solid fa-file-lines file-icon';
            
            const name = document.createElement('div');
            name.className = 'file-name';
            name.textContent = file.name;

            fileInfo.appendChild(icon);
            fileInfo.appendChild(name);
            previewItem.appendChild(fileInfo);
            previewContainer.appendChild(previewItem);
        }
    });
}
