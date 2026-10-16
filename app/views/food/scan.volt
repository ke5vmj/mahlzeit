{% extends "layouts/main.volt" %}

{% block content %}
<div class="level is-mobile mb-4">
    <div class="level-left">
        <div class="level-item">
            <a href="/diary/{{ date }}/add-food/{{ mealTypeId }}" class="button is-white">
                <span class="icon"><i class="fas fa-arrow-left"></i></span>
            </a>
        </div>
        <div class="level-item">
            <h1 class="title is-4 mb-0">Scan Barcode</h1>
        </div>
    </div>
</div>

<div class="box p-2 has-text-centered">
    <p class="has-text-grey mb-3 mt-2">Center the barcode in the camera view.</p>

    {# The div where the camera feed will render #}
    <div id="reader" width="100%" style="border-radius: 8px; overflow: hidden;"></div>
</div>

{# 1. Load the Html5-Qrcode library #}
<script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>

<script>
    //TODO: Test on a real phone to see if all of this works

    // 1. Setup the success callback
    function onScanSuccess(decodedText, decodedResult) {
        console.log("Found it!");
        // Stop scanning to prevent multiple triggers
        html5QrcodeScanner.clear();

        // Redirect to our Controller action with the scanned barcode!
        window.location.href = `/diary/{{ date }}/food/lookup-barcode/{{ mealTypeId }}?code=${decodedText}`;
    }

    function onScanFailure(error) {
        // We purposely leave this blank or commented out!
        // The scanner throws a "failure" every single frame it doesn't see a barcode,
        // which will completely freeze your browser console if you log it.
    }

    // 2. Explicitly define the standard grocery/product barcode formats
        const formatsToSupport = [
            Html5QrcodeSupportedFormats.UPC_A,
            Html5QrcodeSupportedFormats.UPC_E,
            Html5QrcodeSupportedFormats.EAN_13,
            Html5QrcodeSupportedFormats.EAN_8,
            Html5QrcodeSupportedFormats.CODE_128,
            Html5QrcodeSupportedFormats.CODE_39
        ];

        // 3. Initialize the Scanner with Aggressive Optimizations
        let html5QrcodeScanner = new Html5QrcodeScanner(
            "reader",
            {
                fps: 10,
                // Make the box wider and shorter, matching the shape of a barcode
                qrbox: { width: 400, height: 200 },
                formatsToSupport: formatsToSupport,
                rememberLastUsedCamera: true,

                // OPTIMIZATION 1: Force the camera to give us an HD feed for sharper lines
                videoConstraints: {
                    width: { min: 640, ideal: 1280, max: 1920 },
                    height: { min: 480, ideal: 720, max: 1080 },
                    facingMode: "environment" // Force the back camera on phones
                },

                // OPTIMIZATION 2: Use the browser's blazing-fast native hardware scanner if available
                experimentalFeatures: {
                    useBarCodeDetectorIfSupported: true
                }
            },
            false // Keep verbose false to prevent console spam
        );

        html5QrcodeScanner.render(onScanSuccess, onScanFailure);
</script>

<style>
    /* Clean up the default styling injected by the library */
    #reader button {
        background-color: #00d1b2;
        color: white;
        border: none;
        padding: 8px 16px;
        border-radius: 4px;
        cursor: pointer;
        margin-bottom: 10px;
        font-weight: bold;
    }
    #reader select {
        padding: 5px;
        margin-bottom: 10px;
    }
    #reader__dashboard_section_csr span {
        color: #f14668 !important; /* Make error text Bulma red */
    }
</style>
{% endblock %}