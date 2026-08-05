<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="{{ asset('benguetlogo.ico') }}">
    <title>404 - Page Not Found</title>
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

        @keyframes pulse-glow {
            0%, 100% {
                box-shadow: 0 0 20px rgba(59, 130, 246, 0.5);
            }
            50% {
                box-shadow: 0 0 30px rgba(59, 130, 246, 0.8);
            }
        }

        @keyframes float {
            0%, 100% {
                transform: translateY(0px);
            }
            50% {
                transform: translateY(-10px);
            }
        }

        .animate-slide-in-down {
            animation: slideInDown 0.6s ease-out;
        }

        .animate-fade-in {
            animation: fadeIn 0.8s ease-out;
        }

        .animate-pulse-glow {
            animation: pulse-glow 2s ease-in-out infinite;
        }

        .animate-float {
            animation: float 3s ease-in-out infinite;
        }

        .error-code {
            font-size: 5rem;
            line-height: 1;
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-blue-600 to-purple-700 min-h-screen flex items-center justify-center p-4">
    <div class="animate-fade-in max-w-md w-full">
        <div class="bg-white rounded-lg shadow-2xl p-8 text-center">
            <!-- Error Code -->
            <div class="animate-slide-in-down mb-6">
                <div class="error-code font-bold text-blue-600 mb-2">404</div>
            </div>

            <!-- Floating Icon -->
            <div class="animate-float mb-6">
                <i class="fas fa-question-circle text-blue-500 opacity-80" style="font-size: 5rem;"></i>
            </div>

            <!-- Title -->
            <h1 class="text-3xl font-bold text-gray-900 mb-3 animate-slide-in-down" style="animation-delay: 0.1s;">
                Page Not Found
            </h1>

            <!-- Description -->
            <p class="text-gray-600 mb-8 leading-relaxed animate-fade-in" style="animation-delay: 0.2s;">
                We couldn't find the page you're looking for. It may have been moved or deleted. Please check the URL and try again.
            </p>

            <!-- Action Buttons -->
            <div class="space-y-3 animate-fade-in" style="animation-delay: 0.3s;">
                <a href="{{ route('dashboard') }}" class="block w-full bg-gradient-to-r from-blue-600 to-blue-700 text-white font-semibold py-3 rounded-lg hover:shadow-lg transform hover:scale-105 transition-all duration-300">
                    Go to Dashboard
                </a>
                <a href="/" class="block w-full border-2 border-blue-600 text-blue-600 font-semibold py-3 rounded-lg hover:bg-blue-50 transition-colors duration-300">
                    Back to Log in
                </a>
            </div>

            <!-- Decorative Element -->
            <div class="mt-8 pt-6 border-t border-gray-200">
                <p class="text-sm text-gray-500">
                    Error Code: <span class="font-mono font-semibold text-gray-700">404</span>
                </p>
            </div>
        </div>
    </div>
</body>
</html>
