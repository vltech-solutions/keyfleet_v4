<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class CustomerLoginController extends Controller
{
    protected $maxAttempts = 5;
    protected $decayMinutes = 5;

    public function login(Request $request)
    {
        // Rate limiting
        $key = 'login_' . $request->ip() . '_' . session('tenant_id', 'unknown');
        
        if (RateLimiter::tooManyAttempts($key, $this->maxAttempts)) {
            $seconds = RateLimiter::availableIn($key);
            
            Log::warning('Rate limit exceeded for login', [
                'ip' => $request->ip(),
                'tenant' => session('tenant_id')
            ]);
            
            return response()->json([
                'success' => false,
                'message' => "Too many login attempts. Please try again in {$seconds} seconds."
            ], 429);
        }

        // Validation - allow token or image file
        $request->validate([
            'repeat_token' => 'required|string|max:255|min:5',
            'qr_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120' // 5MB max
        ]);

        $tenantId = session('tenant_id');

        if (!$tenantId) {
            return response()->json([
                'success' => false,
                'message' => 'Company not found. Please try again.'
            ], 401);
        }

        // If image is uploaded, process it
        if ($request->hasFile('qr_image')) {
            $image = $request->file('qr_image');
            
            // Additional security checks
            $this->validateImage($image);
            
            // Process image and extract token (you need to implement QR code reading)
            $token = $this->extractTokenFromImage($image);
            
            if (!$token) {
                RateLimiter::hit($key, $this->decayMinutes * 60);
                
                return response()->json([
                    'success' => false,
                    'message' => 'No QR code found in the image. Please try again.'
                ], 400);
            }
            
            $request->merge(['repeat_token' => $token]);
        }

        // Find customer
        $customer = Customer::where('repeat_token', $request->repeat_token)
            ->where('company_id', $tenantId)
            ->first();

        if (!$customer) {
            // Increment failed attempts
            RateLimiter::hit($key, $this->decayMinutes * 60);
            
            Log::warning('Failed login attempt', [
                'ip' => $request->ip(),
                'tenant' => $tenantId,
                'token' => substr($request->repeat_token, 0, 10) . '...'
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Invalid QR code. Please try again.'
            ], 401);
        }

        // Clear attempts on success
        RateLimiter::clear($key);

        // Set session
        session(['customer_token' => $customer->repeat_token]);
        session(['customer_id' => $customer->id]);

        Log::info('Customer login successful', [
            'customer_id' => $customer->id,
            'customer_name' => $customer->customer_name,
            'ip' => $request->ip()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Login successful!',
            'redirect_url' => route('customer.dashboard', ['repeatToken' => $customer->repeat_token])
        ]);
    }

    /**
     * Validate uploaded image for security
     */
    protected function validateImage($image)
    {
        // Check if file is a valid image
        if (!getimagesize($image->getRealPath())) {
            throw ValidationException::withMessages([
                'qr_image' => 'The uploaded file is not a valid image.'
            ]);
        }

        // Check MIME type again for security
        $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($image->getMimeType(), $allowedMimeTypes)) {
            throw ValidationException::withMessages([
                'qr_image' => 'Invalid image format. Allowed: JPEG, PNG, GIF, WEBP.'
            ]);
        }

        // Check file extension
        $allowedExtensions = ['jpeg', 'jpg', 'png', 'gif', 'webp'];
        if (!in_array($image->getClientOriginalExtension(), $allowedExtensions)) {
            throw ValidationException::withMessages([
                'qr_image' => 'Invalid file extension. Allowed: jpg, png, gif, webp.'
            ]);
        }

        // Check for malicious content (basic)
        $content = file_get_contents($image->getRealPath());
        if (strpos($content, '<?php') !== false || strpos($content, 'eval(') !== false) {
            throw ValidationException::withMessages([
                'qr_image' => 'The uploaded file contains suspicious content.'
            ]);
        }
    }

    /**
     * Extract QR code token from image
     * This method should be implemented with a QR code reading library
     */
    protected function extractTokenFromImage($image)
    {
        // You need to implement QR code reading here
        // Using a library like "simplesoftwareio/simple-qrcode" or "endroid/qr-code"
        // For now, return null or implement with your preferred library
        
        // Example with a QR code reader library
        // $qrReader = new \Zxing\QrReader($image->getRealPath());
        // return $qrReader->text();
        
        // For demo, return null
        return null;
    }
}