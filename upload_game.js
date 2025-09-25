// Upload Game Starting
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('upload-form');
    const steps = Array.from(document.querySelectorAll('.wizard-step'));
    const progressSteps = Array.from(document.querySelectorAll('.wizard-progress .step'));
    const progressBar = document.querySelector('.progress-bar-fill');
    let currentStep = 1;

    const navigateToStep = (stepNumber) => {
        steps.forEach(step => step.classList.toggle('active', parseInt(step.dataset.step) === stepNumber));
        progressSteps.forEach(step => {
            step.classList.toggle('active', parseInt(step.dataset.step) <= stepNumber);
            step.classList.toggle('completed', parseInt(step.dataset.step) < stepNumber);
        });
        progressBar.style.width = `${((stepNumber - 1) / (steps.length - 1)) * 100}%`;
        currentStep = stepNumber;
    };

    form.addEventListener('click', e => {
        if (e.target.classList.contains('next')) {
            if (validateStep(currentStep)) navigateToStep(currentStep + 1);
        }
        if (e.target.classList.contains('prev')) {
            navigateToStep(currentStep - 1);
        }
    });
    
    function validateStep(stepNumber) {
        const stepDiv = document.querySelector(`.wizard-step[data-step="${stepNumber}"]`);
        const inputs = Array.from(stepDiv.querySelectorAll('[required]'));
        let isValid = true;
        inputs.forEach(input => {
            if (!input.value.trim()) {
                input.classList.add('error');
                isValid = false;
            } else {
                input.classList.remove('error');
            }
        });
        if (!isValid) alert('Please fill out all required fields in this step.');
        if (stepNumber === 4) populateReview();
        return isValid;
    }

    function populateReview() {
        const title = document.getElementById('title').value;
        const price = parseFloat(document.getElementById('price').value).toFixed(2);
        const reviewDetails = document.getElementById('review-details');
        
        document.querySelector('.card-title-preview').textContent = title || "Your Game Title";
        document.querySelector('.card-price-preview').textContent = price > 0 ? `$${price}` : 'FREE';

        reviewDetails.innerHTML = `
            <h4>Summary:</h4>
            <ul>
                <li><strong>Title:</strong> ${title}</li>
                <li><strong>Price:</strong> $${price}</li>
                <li><strong>Description:</strong> ${document.getElementById('description').value.substring(0, 100)}...</li>
                <li><strong>Category:</strong> ${document.getElementById('category_id').options[document.getElementById('category_id').selectedIndex].text}</li>
                <li><strong>Status:</strong> ${document.querySelector('input[name="status"]:checked').value}</li>
                <li><strong>Thumbnail:</strong> ${document.getElementById('thumbnail').files[0]?.name || 'Not selected'}</li>
                <li><strong>Game File:</strong> ${document.getElementById('game_file').files[0]?.name || 'Not selected'}</li>
                <li><strong>Screenshots:</strong> ${Array.from(document.getElementById('screenshots').files).length} selected</li>
            </ul>`;
    }


    function setupFileDrop(dropAreaId, inputId, isMultiple, isImage) {
        const dropArea = document.getElementById(dropAreaId);
        const fileInput = document.getElementById(inputId);
        dropArea.addEventListener('click', () => fileInput.click());
        dropArea.addEventListener('dragover', e => { e.preventDefault(); dropArea.classList.add('active'); });
        dropArea.addEventListener('dragleave', () => dropArea.classList.remove('active'));
        dropArea.addEventListener('drop', e => {
            e.preventDefault();
            dropArea.classList.remove('active');
            fileInput.files = e.dataTransfer.files;
            fileInput.dispatchEvent(new Event('change'));
        });
        fileInput.addEventListener('change', () => {
            if (isImage) {
                const files = Array.from(fileInput.files);
                let previewHTML = files.map(file => `<div class="preview-image"><img src="${URL.createObjectURL(file)}" alt="${file.name}"></div>`).join('');
                if(!isMultiple && files.length > 0) {
                    document.querySelector('.card-image-preview img').src = URL.createObjectURL(files[0]);
                }
                dropArea.innerHTML = previewHTML || '<p>Drag & Drop here or click to select.</p>';
            } else {
                document.getElementById('game-file-name').textContent = fileInput.files[0] ? `Selected: ${fileInput.files[0].name}` : '';
            }
        });
    }

    setupFileDrop('thumbnail-drop-area', 'thumbnail', false, true);
    setupFileDrop('screenshots-drop-area', 'screenshots', true, true);
    setupFileDrop('game-file-drop-area', 'game_file', false, false);
});
// Upload Game Ending