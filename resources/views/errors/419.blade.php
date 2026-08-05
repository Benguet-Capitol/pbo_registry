<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="{{ asset('benguetlogo.ico') }}">
    <title>419 - Session Expired</title>
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

        @keyframes spin {
            from {
                transform: rotate(0deg);
            }
            to {
                transform: rotate(360deg);
            }
        }

        .animate-slide-in-down {
            animation: slideInDown 0.6s ease-out;
        }

        .animate-fade-in {
            animation: fadeIn 0.8s ease-out;
        }

        .animate-spin-slow {
            animation: spin 2s linear infinite;
        }

        .error-code {
            font-size: 5rem;
            line-height: 1;
        }

        body {
            background: linear-gradient(135deg, #8b5cf6 0%, #6d28d9 100%);
            min-height: 100vh;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-violet-600 to-violet-700 min-h-screen flex items-center justify-center p-4">
    <div class="animate-fade-in max-w-md w-full">
        <div class="bg-white rounded-lg shadow-2xl p-8 text-center">
            <!-- Error Code -->
            <div class="animate-slide-in-down mb-6">
                <div class="error-code font-bold text-violet-600 mb-2">419</div>
            </div>

            <!-- Clock Icon -->
            <div class="mb-6 flex justify-center">
                <i class="fas fa-hourglass-end text-violet-500 opacity-80 animate-spin-slow" style="font-size: 5rem;"></i>
            </div>

            <!-- Title -->
            <h1 class="text-3xl font-bold text-gray-900 mb-3 animate-slide-in-down" style="animation-delay: 0.1s;">
                Session Expired
            </h1>

            <!-- Description -->
            <p class="text-gray-600 mb-8 leading-relaxed animate-fade-in" style="animation-delay: 0.2s;">
                Your session has expired due to inactivity. Please log in again to continue your work.
            </p>

            <!-- Action Buttons -->
            <div class="space-y-3 animate-fade-in" style="animation-delay: 0.3s;">
                <a href="{{ route('login') }}" style="background: linear-gradient(to right, #8b5cf6, #6d28d9); padding: 0.75rem; border-radius: 0.5rem; color: white; font-weight: 600; width: 100%; display: block; border: none; text-align: center; text-decoration: none; box-shadow: 0 4px 6px rgba(0,0,0,0.1); transition: all 300ms;" onmouseover="this.style.boxShadow='0 10px 15px rgba(0,0,0,0.2)'; this.style.transform='scale(1.05)'" onmouseout="this.style.boxShadow='0 4px 6px rgba(0,0,0,0.1)'; this.style.transform='scale(1)'">
                    Log In Again
                </a>
                <a href="/" class="block w-full border-2 border-violet-600 text-violet-600 font-semibold py-3 rounded-lg hover:bg-violet-50 transition-colors duration-300" style="text-align: center; padding: 0.75rem;">
                    Go to Home
                </a>
            </div>

            <!-- Decorative Element -->
            <div class="mt-8 pt-6 border-t border-gray-200">
                <p class="text-sm text-gray-500">
                    Error Code: <span class="font-mono font-semibold text-gray-700">419</span>
                </p>
            </div>
        </div>
    </div>
</body>
</html>
