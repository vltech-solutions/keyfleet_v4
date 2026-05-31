<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Invoice</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="p-10 bg-gray-100">
  <div class="max-w-2xl mx-auto bg-white border border-gray-200 rounded-md shadow-sm">
    <div class="flex items-center p-6 border-b border-gray-200">
      <img src="https://st5.depositphotos.com/69915036/62675/v/450/depositphotos_626754468-stock-illustration-your-logo-here-placeholder-symbol.jpg" alt="Company Logo" class="w-auto h-10 mr-4">
      <div>
        <h2 class="text-xl font-bold">PixelCraft Studio</h2>
        <p class="text-sm text-gray-500">invoice@pixelcraft.com</p>
      </div>
    </div>
    <div class="p-6 space-y-4 text-sm">
      <div class="flex justify-between">
        <div>
          <p class="font-medium text-gray-600">Bill To:</p>
          <p>Client Name</p>
          <p>client@domain.com</p>
        </div>
        <div class="text-right">
          <p class="font-medium text-gray-600">Invoice Date:</p>
          <p>May 30, 2025</p>
        </div>
      </div>
      <table class="w-full mt-4 text-sm border-t border-b border-gray-200">
        <thead class="text-xs text-left text-gray-500 uppercase bg-gray-50">
          <tr>
            <th class="py-2">Item</th>
            <th class="py-2 text-right">Amount</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <tr>
            <td class="py-3">Landing Page Design</td>
            <td class="py-3 text-right">₱9,000</td>
          </tr>
          <tr>
            <td class="py-3">Responsive Testing</td>
            <td class="py-3 text-right">₱1,500</td>
          </tr>
        </tbody>
      </table>
      <div class="flex justify-end mt-4">
        <div class="w-full max-w-sm space-y-1">
          <div class="flex justify-between">
            <span>Subtotal</span>
            <span>₱10,500</span>
          </div>
          <div class="flex justify-between text-base font-semibold">
            <span>Total</span>
            <span>₱10,500</span>
          </div>
        </div>
      </div>
    </div>
    <div class="py-3 text-xs text-center text-gray-500 bg-gray-50">
      PixelCraft Studio • support@pixelcraft.com
    </div>
  </div>
</body>



</html>
