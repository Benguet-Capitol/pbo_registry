<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="{{ asset('benguetlogo.ico') }}">
    <title>503 - Service Unavailable</title>
    @vite('resources/css/app.css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @keyframes slideInDown {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }

        @keyframes pulse-dot {
            0%, 100% {
                opacity: 0.3;
            }
            50% {
                opacity: 1;
            }
        }

        .animate-slide-in-down {
            animation: slideInDown 0.6s ease-out;
        }

        .animate-fade-in {
            animation: fadeIn 0.8s ease-out;
        }

        .dot {
            animation: pulse-dot 1.4s ease-in-out infinite;
        }

        .dot:nth-child(2) {
            animation-delay: 0.2s;
        }

        .dot:nth-child(3) {
            animation-delay: 0.4s;
        }

        .error-code {
            font-size: 5rem;
            line-height: 1;
        }

        body {
            background: linear-gradient(135deg, #14b8a6 0%, #0d9488 100%);
            min-height: 100vh;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-teal-600 to-teal-700 min-h-screen flex items-center justify-center p-4">
    <div class="animate-fade-in max-w-md w-full">
        <div class="bg-white rounded-lg shadow-2xl p-8 text-center">
            <!-- Error Code -->
            <div class="animate-slide-in-down mb-6">
                <div class="error-code font-bold text-teal-600 mb-2">503</div>
            </div>

            <!-- Maintenance Icon -->
            <div class="mb-6 flex justify-center">
                <i class="fas fa-tools text-teal-500 opacity-80" style="font-size: 5rem;"></i>
            </div>

            <!-- Title -->
            <h1 class="text-3xl font-bold text-gray-900 mb-3 animate-slide-in-down" style="animation-delay: 0.1s;">
                Service Unavailable
            </h1>

            <!-- Description -->
            <p class="text-gray-600 mb-2 leading-relaxed animate-fade-in" style="animation-delay: 0.2s;">
                We're currently performing maintenance. We'll be back online shortly.
            </p>

            <!-- Loading Indicator -->
            <div class="flex justify-center gap-2 mb-8 animate-fade-in" style="animation-delay: 0.25s;">
                <div class="dot w-2 h-2 bg-teal-600 rounded-full"></div>
                <div class="dot w-2 h-2 bg-teal-600 rounded-full"></div>
                <div class="dot w-2 h-2 bg-teal-600 rounded-full"></div>
            </div>

            <!-- Action Buttons -->
            <div class="space-y-3 animate-fade-in" style="animation-delay: 0.3s;">
                <button type="button" onclick="window.location.reload()" style="background: linear-gradient(to right, #14b8a6, #0d9488); padding: 0.75rem; border-radius: 0.5rem; color: white; font-weight: 600; width: 100%; border: none; cursor: pointer; box-shadow: 0 4px 6px rgba(0,0,0,0.1); transition: all 300ms;" onmouseover="this.style.boxShadow='0 10px 15px rgba(0,0,0,0.2)'; this.style.transform='scale(1.05)'" onmouseout="this.style.boxShadow='0 4px 6px rgba(0,0,0,0.1)'; this.style.transform='scale(1)'">
                    Check Again
                </button>
                <a href="/" class="block w-full border-2 border-teal-600 text-teal-600 font-semibold py-3 rounded-lg hover:bg-teal-50 transition-colors duration-300" style="text-align: center; padding: 0.75rem;">
                    Go to Home
                </a>
            </div>

            <!-- Decorative Element -->
            <div class="mt-8 pt-6 border-t border-gray-200">
                <p class="text-sm text-gray-500">
                    Error Code: <span class="font-mono font-semibold text-gray-700">503</span>
                </p>
            </div>
        </div>
    </div>
</body>
</html>
