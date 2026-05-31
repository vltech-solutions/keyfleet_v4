<div class="p-5 bg-white border border-gray-200 shadow-sm rounded-xl dark:bg-gray-900 dark:border-gray-700">
    <h3 class="mb-4 text-lg font-semibold text-gray-800 dark:text-white">Order Summary</h3>

    <div class="space-y-3 text-sm text-gray-700 dark:text-gray-300">
        <div class="flex justify-between">
            <span class="text-gray-800 dark:text-white">Plan Price</span>
            <span class="font-medium text-gray-800 dark:text-white">₱{{ number_format($this->planPriceAmount ?? 0, 2) }}</span>
        </div>

        @if(!empty($this->selectedAddonsDetails))
            <div class="pt-2 pb-1 space-y-2 border-t border-dashed border-gray-200 dark:border-gray-700">
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Selected Add-ons</p>
                @foreach($this->selectedAddonsDetails as $addon)
                    <div class="flex justify-between pl-2">
                        <div class="flex flex-col">
                            <span class="text-gray-600 dark:text-gray-400">{{ $addon['name'] }}</span>
                            <span class="text-[10px] text-gray-400 italic">{{ $addon['breakdown'] }}</span>
                        </div>
                        <span class="font-medium text-gray-800 dark:text-white">
                            ₱{{ number_format($addon['price'], 2) }}
                        </span>
                    </div>
                @endforeach
            </div>
        @endif

        <div class="flex justify-between border-t border-gray-100 pt-2 dark:border-gray-800">
            <span class="flex items-center gap-1 text-gray-800 dark:text-white">
                Processing Fee
                <x-heroicon-o-information-circle 
                    class="w-4 h-4 text-gray-400 dark:text-gray-500" 
                    x-tooltip="'This fee helps cover payment gateway and transaction processing charges to ensure secure and reliable payments.'" 
                />
            </span>
            <span class="font-medium text-gray-800 dark:text-white">₱{{ number_format($this->processingFee ?? 0, 2) }}</span>
        </div>

        @if(($this->refundAmount ?? 0) > 0)
            <div class="flex justify-between">
                <span class="flex items-center gap-1 text-gray-800 dark:text-white">
                    Refund (Prorated)
                    <x-heroicon-o-information-circle 
                        class="w-4 h-4 text-gray-400 dark:text-gray-500" 
                        x-tooltip="'Refund is a prorated credit from your current subscription based on remaining days.'" 
                    />
                </span>
                <span class="text-red-600 dark:text-red-400">-₱{{ number_format($this->refundAmount, 2) }}</span>
            </div>
        @endif

        @if(($this->voucherDiscount ?? 0) > 0)
            <div class="flex justify-between">
                <span class="text-gray-800 dark:text-white font-medium">Voucher Discount</span>
                <span class="text-green-600 dark:text-green-400 font-bold">-₱{{ number_format($this->voucherDiscount, 2) }}</span>
            </div>
        @endif

        <div class="flex justify-between pt-3 text-base font-bold text-gray-900 border-t border-gray-200 dark:text-white dark:border-gray-700">
            <span>Total Due</span>
            <span class="text-primary-600 dark:text-primary-400">₱{{ number_format($this->totalDue ?? 0, 2) }}</span>
        </div>
    </div>
</div>