<div class="flex flex-col h-[75vh]">
    <div class="flex justify-end p-2 bg-gray-100 border-b dark:bg-gray-800 dark:border-gray-700">
        <button 
            onclick="printIframe()" 
            class="inline-flex items-center px-4 py-2 text-sm font-bold text-white bg-success-600 rounded-lg hover:bg-success-500"
        >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
            Start Printing
        </button>
    </div>

    <iframe 
        id="pdfFrame"
        src="{{ $url }}" 
        class="flex-1 w-full border-0" 
        allow="print"
    ></iframe>
</div>

<script>
    function printIframe() {
        const frame = document.getElementById('pdfFrame');
        if (frame) {
            frame.contentWindow.focus();
            frame.contentWindow.print();
        }
    }

    // Auto-trigger print when the modal finishes loading the PDF
    document.getElementById('pdfFrame').onload = function() {
        setTimeout(printIframe, 1000);
    };
</script>