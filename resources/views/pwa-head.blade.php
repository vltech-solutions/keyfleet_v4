<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="apple-mobile-web-app-title" content="Keyfleet">

<meta name="apple-mobile-web-app-capable" content="yes"> 

<link rel="manifest" href="/manifest.json">
<link rel="apple-touch-icon" href="/icons/icon.png">

<style>
  /* .fi-main-ctn header {
      background-color: white !important;
      z-index: 30 !important; 
  }

  .dark .fi-main-ctn header {
      background-color: #09090b !important; 
  } */

  .fi-topbar {
      backdrop-filter: none !important;
      -webkit-backdrop-filter: none !important;
  }
  /* Mobile responsive table styles */
    @media (max-width: 768px) {
        /* .fi-ta-row {
            margin-bottom: 16px;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        } */
        
        .fi-ta-col {
            padding: 8px !important;
        }
        
        .filament-mobile-stack {
            width: 100%;
        }
        
        /* Hide specific columns on mobile */
        .fi-ta-col:not(:first-child) {
            display: none;
        }

        .fi-ta-summary-row,.fi-ta-summary-header-row{
            display: none !important;
        }
    }

    /* Desktop styles */
    @media (min-width: 769px) {
        .filament-mobile-stack {
            display: none;
        }
    }

</style>