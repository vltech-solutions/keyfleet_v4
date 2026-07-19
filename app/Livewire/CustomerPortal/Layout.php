<?php

namespace App\Livewire\CustomerPortal;

use App\Models\Customer;
use Livewire\Component;

class Layout extends Component
{
    public $customer;
    public $repeatToken;

    public function mount($repeatToken)
    {
        $tenantId = session('tenant_id');
        
        if (!$tenantId) {
            abort(404, 'Company not found');
        }

        $this->customer = Customer::where('repeat_token', $repeatToken)
            ->where('company_id', $tenantId)
            ->firstOrFail();

        $this->repeatToken = $repeatToken;
    }

    public function logout()
    {
        // Clear all customer-related session data
        session()->forget(['customer_token', 'customer_id']);
        
        // Redirect to home
        return redirect()->to('/');
    }

    public function render()
    {
        return view('livewire.customer-portal.customer-layout', [
            'customer' => $this->customer,
            'repeatToken' => $this->repeatToken,
        ]);
    }
}