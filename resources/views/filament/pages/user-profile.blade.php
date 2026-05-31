<x-filament::page>
   @php $tenant = filament()->getTenant()?->slug; @endphp
   <div class="space-y-8">
      {{-- Header Section --}}
      <header class="flex flex-col gap-2">
         <h1
            class="text-2xl font-bold tracking-tight text-gray-950 dark:text-white sm:text-3xl"
         >
            Profile Settings
         </h1>
         <p class="text-sm text-gray-500 dark:text-gray-400">
            Manage your personal information, subscription, and appearance.
         </p>
      </header>

      <div class="grid grid-cols-1 gap-8 lg:grid-cols-12">
         {{-- Main Content: Column (12 units) --}}
         <div class="lg:col-span-12 space-y-6">
            {{-- Profile Form Section --}}
            <div
               class="overflow-hidden bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl dark:bg-gray-900 dark:ring-white/10"
            >
               <div
                  class="border-b border-gray-900/5 px-6 py-4 dark:border-white/10 bg-gray-50/50 dark:bg-white/5"
               >
                  <div class="flex items-center gap-x-3">
                     <div class="p-2 bg-primary-500/10 rounded-lg">
                        <x-filament::icon
                           icon="heroicon-s-user"
                           class="w-5 h-5 text-primary-600 dark:text-primary-400"
                        />
                     </div>
                     <div>
                        <h2
                           class="text-base font-semibold leading-7 text-gray-900 dark:text-white"
                        >
                           Personal Information
                        </h2>
                        <p class="text-xs text-gray-500">
                           Update your basic account details.
                        </p>
                     </div>
                  </div>
               </div>

               <div class="px-6 py-6">
                  <form wire:submit.prevent="submit" class="space-y-6">
                     <div class="grid grid-cols-1 gap-x-6 gap-y-4">
                        {{ $this->form }}
                     </div>

                     <div
                        class="flex items-center justify-end gap-x-3 pt-6 border-t border-gray-900/5 dark:border-white/10"
                     >
                        <x-filament::button
                           type="submit"
                           icon="heroicon-m-check-badge"
                           size="md"
                        >
                           Update Profile
                        </x-filament::button>
                     </div>
                  </form>
               </div>
            </div>

            {{-- Account Actions Section (Subscription, Referral, Appearance)
            --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
               {{-- Theme Switcher Card --}}
               <div
                  x-data="{ 
                    theme: localStorage.getItem('theme') || 'light',
                    toggle() {
                        this.theme = this.theme === 'light' ? 'dark' : 'light';
                        localStorage.setItem('theme', this.theme);
                        document.documentElement.classList.toggle('dark', this.theme === 'dark');
                    }
                }"
                  @click="toggle()"
                  class="relative flex items-center p-4 overflow-hidden transition-all duration-300 bg-white shadow-md cursor-pointer dark:bg-gray-900 rounded-2xl hover:border-primary-500 dark:hover:border-primary-400 group active:scale-[0.98]"
               >
                  <div
                     class="absolute inset-0 transition-opacity opacity-0 bg-gradient-to-br from-primary-500/5 to-indigo-500/5 group-hover:opacity-100 dark:from-primary-400/10"
                  ></div>

                  <div class="relative flex items-center w-full">
                     {{-- Icon Container with Smooth Rotation --}}
                     <div
                        class="relative w-12 h-12 flex items-center justify-center rounded-xl transition-all duration-500 overflow-hidden bg-gray-50 dark:bg-gray-800 group-hover:bg-primary-50 dark:group-hover:bg-primary-900/30"
                     >
                        <div
                           class="absolute transition-all duration-500 transform"
                           :class="theme === 'dark' ? 'translate-y-0 opacity-100 rotate-0' : 'translate-y-10 opacity-0 -rotate-90'"
                        >
                           <x-heroicon-o-sun class="w-6 h-6 text-amber-500" />
                        </div>

                        <div
                           class="absolute transition-all duration-500 transform"
                           :class="theme === 'light' ? 'translate-y-0 opacity-100 rotate-0' : '-translate-y-10 opacity-0 rotate-90'"
                        >
                           <x-heroicon-o-moon
                              class="w-6 h-6 text-indigo-600 dark:text-indigo-400"
                           />
                        </div>
                     </div>

                     {{-- Text Content --}}
                     <div class="flex-1 ml-4">
                        <h3
                           class="text-sm font-bold tracking-tight text-gray-900 transition-colors dark:text-gray-100 group-hover:text-primary-600 dark:group-hover:text-primary-400"
                        >
                           Appearance
                        </h3>
                        <div class="flex items-center space-x-2">
                           <span
                              class="text-[10px] font-medium uppercase tracking-widest transition-colors"
                              :class="theme === 'light' ? 'text-indigo-600' : 'text-gray-500'"
                              x-text="'Light'"
                           ></span>
                           <span class="text-[10px] text-gray-400">/</span>
                           <span
                              class="text-[10px] font-medium uppercase tracking-widest transition-colors"
                              :class="theme === 'dark' ? 'text-amber-500' : 'text-gray-500'"
                              x-text="'Dark'"
                           ></span>
                        </div>
                     </div>

                     {{-- Enhanced iOS-style Switch --}}
                     <div
                        class="relative inline-flex items-center cursor-pointer"
                     >
                        <div
                           class="w-11 h-6 transition-colors duration-300 rounded-full shadow-inner"
                           :class="theme === 'dark' ? 'bg-primary-500' : 'bg-gray-200 dark:bg-gray-700'"
                        ></div>
                        <div
                           class="absolute w-4 h-4 transition-all duration-300 transform bg-white rounded-full shadow-md left-1"
                           :class="theme === 'dark' ? 'translate-x-5' : 'translate-x-0'"
                        >
                           <div
                              class="flex items-center justify-center w-full h-full"
                           >
                              <div
                                 class="w-1 h-1 rounded-full bg-gray-200"
                                 :class="theme === 'dark' ? 'hidden' : ''"
                              ></div>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>

               {{-- Subscription Card --}}
               <a
                  href="/app/{{ $tenant }}/subscription-overview"
                  class="flex items-center p-4 bg-white dark:bg-gray-900 rounded-xl shadow-md hover:border-primary-500 dark:hover:border-primary-500 transition-all group"
               >
                  <div
                     class="w-10 h-10 flex items-center justify-center bg-green-50 dark:bg-green-900/30 rounded-lg mr-4 text-green-600 dark:text-green-400 group-hover:scale-110 transition-transform"
                  >
                     <x-heroicon-o-credit-card class="w-5 h-5" />
                  </div>
                  <div class="flex-1">
                     <h3
                        class="text-sm font-bold text-gray-900 dark:text-white"
                     >
                        Subscription
                     </h3>
                     <p class="text-[10px] text-gray-500">Manage your plan</p>
                  </div>
                  <x-heroicon-m-chevron-right class="w-4 h-4 text-gray-400" />
               </a>

               {{-- Referral Card --}}
               <a
                  href="/app/{{ $tenant }}/referral-dashboard"
                  class="flex items-center p-4 bg-white dark:bg-gray-900 rounded-xl shadow-md hover:border-primary-500 dark:hover:border-primary-500 transition-all group"
               >
                  <div
                     class="w-10 h-10 flex items-center justify-center bg-amber-50 dark:bg-amber-900/30 rounded-lg mr-4 text-amber-600 dark:text-amber-400 group-hover:scale-110 transition-transform"
                  >
                     <x-heroicon-o-trophy class="w-5 h-5" />
                  </div>
                  <div class="flex-1">
                     <h3
                        class="text-sm font-bold text-gray-900 dark:text-white"
                     >
                        Referrals
                     </h3>
                     <p class="text-[10px] text-gray-500">Earn rewards</p>
                  </div>
                  <x-heroicon-m-chevron-right class="w-4 h-4 text-gray-400" />
               </a>
              {{-- Logout Card --}}
              <form method="POST" action="{{ route('filament.app.auth.logout') }}" class="w-full">
                  @csrf
                  <button type="submit" 
                      class="w-full flex items-center p-4 bg-white dark:bg-gray-900 rounded-xl shadow-md hover:border-danger-500 dark:hover:border-danger-500 transition-all group">
                      <div class="w-10 h-10 flex items-center justify-center bg-danger-50 dark:bg-danger-900/30 rounded-lg mr-4 text-danger-600 dark:text-danger-400 group-hover:scale-110 transition-transform">
                          <x-heroicon-o-arrow-left-on-rectangle class="w-5 h-5" />
                      </div>
                      <div class="flex-1 text-left">
                          <h3 class="text-sm font-bold text-gray-900 dark:text-white">Sign Out</h3>
                          <p class="text-[10px] text-gray-500">Exit your session</p>
                      </div>
                      <x-heroicon-m-chevron-right class="w-4 h-4 text-gray-400" />
                  </button>
              </form>
            </div>
         </div>
      </div>
   </div>

   <div
      id="pwa-install-section"
      class="hidden px-6 py-3 transition-opacity duration-500"
   >
      <button
         id="pwa-install-btn"
         type="button"
         class="hidden w-full flex-row items-center justify-center gap-x-3 rounded-lg bg-primary-600 px-3 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-primary-500 active:scale-95 transition-transform"
      >
         <x-heroicon-s-device-phone-mobile class="h-5 w-5 shrink-0" />
         <span class="whitespace-nowrap leading-none"
            >Install Keyfleet App</span
         >
      </button>

      <button
          id="pwa-push-btn"
          type="button"
          class="hidden w-full flex flex-row items-center justify-center gap-x-2.5 rounded-lg bg-gray-800 px-4 py-3 text-sm font-semibold text-white shadow-sm hover:bg-gray-700 active:scale-95 transition-transform"
      >
          <x-heroicon-s-bell class="h-5 w-5 shrink-0 block" />

          <span class="whitespace-nowrap leading-tight">
              Enable Notifications
          </span>
      </button>

      <div
         id="ios-instruction"
         class="hidden rounded-lg bg-gray-100 p-3 text-xs text-gray-600 dark:bg-gray-800 dark:text-gray-400"
      >
         <div class="flex items-center gap-x-2 mb-2">
            <x-heroicon-s-information-circle
               class="h-4 w-4 shrink-0 text-primary-500"
            />
            <span class="font-bold leading-none uppercase tracking-wider"
               >Install Mobile App</span
            >
         </div>
         <p class="leading-relaxed">
            Tap the
            <span class="inline-flex items-center px-1">
               <x-heroicon-o-share class="h-4 w-4" />
            </span>
            icon and select <strong>"Add to Home Screen"</strong>.
         </p>
      </div>
   </div>

   <script>
      (function () {
         const section = document.getElementById("pwa-install-section");
         const installBtn = document.getElementById("pwa-install-btn");
         const pushBtn = document.getElementById("pwa-push-btn");
         const iosInfo = document.getElementById("ios-instruction");
         let deferredPrompt;

         const isStandalone =
            window.matchMedia("(display-mode: standalone)").matches ||
            window.navigator.standalone;
         const isIOS =
            /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;
         const isSafari = /^((?!chrome|android).)*safari/i.test(
            navigator.userAgent,
         );

         // --- 1. INITIAL LOGIC ---
         if (isStandalone) {
            section.classList.remove("hidden");
            pushBtn.classList.remove("hidden");
         } else {
            if (isIOS && isSafari) {
               section.classList.remove("hidden");
               iosInfo.classList.remove("hidden");
            }
         }

         // --- 2. INSTALLATION LOGIC ---
         window.addEventListener("beforeinstallprompt", (e) => {
            e.preventDefault();
            deferredPrompt = e;
            section.classList.remove("hidden");
            installBtn.classList.remove("hidden");
         });

         installBtn.addEventListener("click", async () => {
            if (!deferredPrompt) return;
            deferredPrompt.prompt();
            const { outcome } = await deferredPrompt.userChoice;
            if (outcome === "accepted") {
               section.classList.add("hidden");
            }
            deferredPrompt = null;
         });

         // --- 3. PUSH NOTIFICATION LOGIC ---
         pushBtn.addEventListener("click", async () => {
            try {
               // Ensure we are on HTTPS before proceeding
               if (
                  window.location.protocol === "http:" &&
                  window.location.hostname !== "localhost"
               ) {
                  window.location.href = window.location.href.replace(
                     "http:",
                     "https:",
                  );
                  return;
               }

               const registration = await navigator.serviceWorker.ready;
               const permission = await Notification.requestPermission();

               if (permission !== "granted") {
                  alert("Please allow notifications to receive updates.");
                  return;
               }

               const subscription = await registration.pushManager.subscribe({
                  userVisibleOnly: true,
                  applicationServerKey: urlBase64ToUint8Array(
                     "BO3PO4OTZNrgtxEg8XaxTeSJ6rQKQHxZPZmSulQXnzzxtdizPW8CgEXh2rxd0V1sl-83drvMILWjCn2UQ6sp4cE",
                  ),
               });

               // Use relative path to ensure it follows the current protocol/domain
               await fetch("/push-subscription", {
                  method: "POST",
                  body: JSON.stringify(subscription),
                  headers: {
                     "Content-Type": "application/json",
                     Accept: "application/json",
                     "X-CSRF-TOKEN": "{{ csrf_token() }}",
                  },
               });

               alert("Notifications enabled!");
               pushBtn.classList.add("hidden");
            } catch (error) {
               console.error("Push error:", error);
               alert(
                  "Failed to enable notifications. Please ensure you are using a secure connection.",
               );
            }
         });

         function urlBase64ToUint8Array(base64String) {
            const padding = "=".repeat((4 - (base64String.length % 4)) % 4);
            const base64 = (base64String + padding)
               .replace(/-/g, "+")
               .replace(/_/g, "/");
            const rawData = window.atob(base64);
            return Uint8Array.from(
               [...rawData].map((char) => char.charCodeAt(0)),
            );
         }

         window.addEventListener("appinstalled", () => {
            section.classList.add("hidden");
            deferredPrompt = null;
         });
      })();
   </script>

   {{-- Script remains same --}}
   <script>
      document.addEventListener("livewire:init", () => {
         Livewire.on("open-google-popup", (event) => {
            const url = Array.isArray(event) ? event[0] : event;
            const popup = window.open(
               url,
               "GoogleAuth",
               "width=600,height=700,top=100,left=100",
            );
            const checkClosed = setInterval(() => {
               if (!popup || popup.closed) {
                  clearInterval(checkClosed);
                  Livewire.dispatch("googleCalendarConnected");
               }
            }, 1000);
         });
         Livewire.on("reload-page", () => window.location.reload());
      });
   </script>
</x-filament::page>
