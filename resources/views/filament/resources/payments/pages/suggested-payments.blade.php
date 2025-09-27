<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Header Information --}}
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-blue-800">
                        Payment Optimization Algorithm
                    </h3>
                    <div class="mt-2 text-sm text-blue-700">
                        <p>
                            These payments are calculated to settle all outstanding balances with the minimum number of transactions.
                            The algorithm matches the largest creditors with the largest debtors first for optimal settlement.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Filament Table --}}
        {{ $this->table }}
    </div>
</x-filament-panels::page>
