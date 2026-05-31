<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Contract</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tinymce@6.8.3/skins/content/default/content.min.css">

    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

    <style>
        @media print {
            #print-button {
                display: none !important;
            }
            table {
                table-layout: fixed;
                width: 100%;
                border-collapse: collapse;
            }

            td, th {
                word-break: break-word;
                overflow-wrap: break-word;
                padding: 8px;
            }

            .tiny-output p {
                line-height: 1 !important;
            }

            body { margin: 5% !important; }
            p { margin: 0 !important; }

            .tiny-output tr[style*="height"] > td {
                height: inherit !important;
                vertical-align: middle;
            }

            .mce-pagebreak {
                display: none !important;
            }

            .print-pagebreak {
                page-break-before: always;
                break-before: page;
            }
        }

        .print-pagebreak {
            page-break-before: always;
            break-before: page;
        }

        table {
            table-layout: fixed;
            width: 100%;
            border-collapse: collapse;
        }

        td, th {
            word-break: break-word;
            overflow-wrap: break-word;
            padding: 8px;
        }

        .tiny-output p {
            line-height: 1 !important;
        }

        body { margin: 5% !important; }
        p { margin: 0 !important; }

        .tiny-output tr[style*="height"] > td {
            height: inherit !important;
            vertical-align: middle;
        }
    </style>
</head>
<body>
    <div style="margin-bottom: 1rem;">
        <div style="margin-bottom: 1rem;">
        <button id="print-button"
            onclick="window.print()"
            style="
                display: inline-block;
                padding: 0.375rem 0.75rem;
                font-weight: 400;
                line-height: 1.5;
                color: #212529;
                background-color: #f8f9fa;
                border: 1px solid #ced4da;
                border-radius: 0.375rem;
                transition: background-color 0.15s ease-in-out, border-color 0.15s ease-in-out;
                float: right;
                cursor: pointer;
            "
            onmouseover="this.style.backgroundColor='#e2e6ea';"
            onmouseout="this.style.backgroundColor='#f8f9fa';">
            Print / Save
        </button>

        <div style="clear: both;"></div>
        {{-- <button id="download-button"
            onclick="downloadContractPDF()"
            style="
                display: inline-flex;
                align-items: center;
                padding: 10px 20px;
                font-size: 14px;
                font-weight: 600;
                color: white;
                background-color: #16a34a; /* green-600 */
                border: none;
                border-radius: 8px;
                box-shadow: 0 1px 2px rgba(0,0,0,0.1);
                cursor: pointer;
                transition: background-color 0.2s ease;
            "
            onmouseover="this.style.backgroundColor='#15803d'" 
            onmouseout="this.style.backgroundColor='#16a34a'">
            Download as PDF
        </button> --}}
    </div>

    </div>

    {{-- <div id="contract-content" class="tiny-output">
        {!! $body !!}
    </div> --}}

    <div id="contract-content" class="tiny-output" style="max-width: 794px; width: 100%; margin: 0 auto;">
        {!! $body !!}
    </div>

    <script>
        function downloadContractPDF() {
            const element = document.getElementById('contract-content');

            const opt = {
                margin:       [0.5,0.5], 
                filename:     'contract.pdf',
                image:        { type: 'jpeg', quality: 0.98 },
                html2canvas:  {
                    scale: 2,
                    useCORS: true,
                    allowTaint: false,
                    logging: true
                },
                jsPDF: {
                    unit: 'in',
                    format: 'letter',
                    orientation: 'portrait'
                }
            };

            html2pdf().set(opt).from(element).save();
        }
    </script>
</body>
</html>
