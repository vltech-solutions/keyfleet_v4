<div x-data="{
    printOnlyContract() {
        // Kunin ang contract content
        const contractContent = document.getElementById('printableContract').innerHTML;
        
        // Gumawa ng hidden iframe
        const iframe = document.createElement('iframe');
        iframe.style.position = 'absolute';
        iframe.style.width = '0';
        iframe.style.height = '0';
        iframe.style.border = 'none';
        document.body.appendChild(iframe);
        
        // Isulat ang content sa iframe
        const iframeDoc = iframe.contentWindow.document;
        iframeDoc.open();
        iframeDoc.write(`
            <!DOCTYPE html>
            <html>
            <head>
                <title>Print Contract</title>
                <style>
                    body { 
                        margin: 1.5cm; 
                        font-family: Arial, sans-serif; 
                        line-height: 1.5;
                    }
                    table { 
                        width: 100%; 
                        border-collapse: collapse; 
                    }
                    th, td { 
                        border: 1px solid #000; 
                        padding: 8px; 
                        text-align: left; 
                        vertical-align: top;
                    }
                    @media print { 
                        body { margin: 1.5cm; } 
                        table { page-break-inside: avoid; }
                    }
                </style>
            </head>
            <body>
                ${contractContent}
            </body>
            </html>
        `);
        iframeDoc.close();
        
        // I-print ang iframe - gamitan ng setTimeout para sure na loaded
        setTimeout(() => {
            iframe.contentWindow.focus();
            iframe.contentWindow.print();
        }, 100);
        
        // I-remove ang iframe pagkatapos
        setTimeout(() => {
            if (iframe && iframe.parentNode) {
                document.body.removeChild(iframe);
            }
        }, 5000);
    }
}">
    <div class="p-4 bg-white dark:bg-gray-800 rounded-lg overflow-y-auto max-h-[70vh] isolation-container">
        <div id="printableContract" class="contract-reset">
            {!! $body !!}
        </div>
    </div>

    <div class="flex justify-end mt-4 print:hidden">
        <x-filament::button 
            color="gray" 
            icon="heroicon-m-printer"
            @click="printOnlyContract()">
            Print Contract
        </x-filament::button>
    </div>
</div>