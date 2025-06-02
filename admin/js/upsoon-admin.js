document.addEventListener('DOMContentLoaded', function () {
    const bannerTextInput = document.querySelector('input[name="upsoon_banner_text"]');
    const preview = document.getElementById('upsoon-banner-preview');
    const container = document.getElementById('upsoon-preview-container');
    if (bannerTextInput && preview) {
        bannerTextInput.addEventListener('input', function () {
            preview.textContent = bannerTextInput.value;
        });
    }
    function updatePreview() {
        const pos = document.querySelector('input[name="upsoon_banner_pos"]:checked')?.value;
        const bgColor = document.querySelector('input[name="upsoon_banner_color"]')?.value;
        const textColor = document.querySelector('input[name="upsoon_banner_text_color"]')?.value;
        const text = document.querySelector('input[name="upsoon_banner_text"]')?.value || 'Test';

        preview.textContent = text;
        preview.style.backgroundColor = bgColor || '#00008B';
        preview.style.color = textColor || '#FFFFFF';

        // Reset positioning
        preview.style.top = '';
        preview.style.bottom = '';
        preview.style.left = '';
        preview.style.right = '';
        preview.style.transform = '';

        switch (pos) {
            case 'Top Left':
                preview.style.top = '10px'; preview.style.left = '10px'; break;
            case 'Top Right':
                preview.style.top = '10px'; preview.style.right = '10px'; break;
            case 'Bottom Left':
                preview.style.bottom = '10px'; preview.style.left = '10px'; break;
            case 'Bottom Right':
                preview.style.bottom = '10px'; preview.style.right = '10px'; break;
            case 'Top':
                preview.style.top = '10px'; preview.style.left = '50%'; preview.style.transform = 'translateX(-50%)'; break;
            case 'Bottom':
                preview.style.bottom = '10px'; preview.style.left = '50%'; preview.style.transform = 'translateX(-50%)'; break;
            case 'Left':
                preview.style.top = '50%'; preview.style.left = '10px'; preview.style.transform = 'translateY(-50%)'; break;
            case 'Right':
                preview.style.top = '50%'; preview.style.right = '10px'; preview.style.transform = 'translateY(-50%)'; break;
        }
    }

    document.querySelectorAll('input[name="upsoon_banner_pos"], input[name="upsoon_banner_color"], input[name="upsoon_banner_text_color"], input[name="upsoon_banner_text"]').forEach(el => {
        el.addEventListener('input', updatePreview);
        el.addEventListener('change', updatePreview);
    });

    updatePreview(); // Init preview
});
