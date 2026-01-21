<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>500 - Server Error</title>
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

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }

        @keyframes pulse-glow {
            0%, 100% {
                box-shadow: 0 0 20px rgba(239, 68, 68, 0.5);
            }
            50% {
                box-shadow: 0 0 30px rgba(239, 68, 68, 0.8);
            }
        }

        .animate-slide-in-down {
            animation: slideInDown 0.6s ease-out;
        }

        .animate-fade-in {
            animation: fadeIn 0.8s ease-out;
        }

        .animate-shake {
            animation: shake 0.5s ease-in-out;
        }

        .error-code {
            font-size: 5rem;
            line-height: 1;
        }

        body {
            background: linear-gradient(135deg, #f43f5e 0%, #e11d48 100%);
            min-height: 100vh;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-red-500 to-rose-600 min-h-screen flex items-center justify-center p-4">
    <div class="animate-fade-in max-w-md w-full">
        <div class="bg-white rounded-lg shadow-2xl p-8 text-center">
            <!-- Error Code -->
            <div class="animate-slide-in-down mb-6">
                <div class="error-code font-bold text-red-600 mb-2 animate-shake">500</div>
            </div>

            <!-- Alert Icon -->
            <div class="mb-6 flex justify-center">
                <i class="fas fa-exclamation-triangle text-red-500 opacity-80 animate-shake" style="font-size: 5rem;"></i>
            </div>

            <!-- Title -->
            <h1 class="text-3xl font-bold text-gray-900 mb-3 animate-slide-in-down" style="animation-delay: 0.1s;">
                Server Error
            </h1>

            <!-- Description -->
            <p class="text-gray-600 mb-2 leading-relaxed animate-fade-in" style="animation-delay: 0.2s;">
                Something went wrong on our end. Our team has been notified and is working to fix the issue.
            </p>
            <p class="text-gray-500 text-sm mb-8 animate-fade-in" style="animation-delay: 0.25s;">
                Please try again later or contact support if the problem persists.
            </p>

            <!-- Action Buttons -->
            <div class="space-y-3 animate-fade-in" style="animation-delay: 0.3s;">
                <button type="button" onclick="location.reload()" style="background: linear-gradient(to right, #dc2626, #b91c1c); padding: 0.75rem; border-radius: 0.5rem; color: white; font-weight: 600; width: 100%; border: none; cursor: pointer; box-shadow: 0 4px 6px rgba(0,0,0,0.1); transition: all 300ms;" onmouseover="this.style.boxShadow='0 10px 15px rgba(0,0,0,0.2)'; this.style.transform='scale(1.05)'" onmouseout="this.style.boxShadow='0 4px 6px rgba(0,0,0,0.1)'; this.style.transform='scale(1)'">
                    Try Again
                </button>
                <a href="/" class="block w-full border-2 border-red-600 text-red-600 font-semibold py-3 rounded-lg hover:bg-red-50 transition-colors duration-300" style="text-align: center; padding: 0.75rem;">
                    Go to Home
                </a>
            </div>

            <!-- Decorative Element -->
            <div class="mt-8 pt-6 border-t border-gray-200">
                <p class="text-sm text-gray-500">
                    Error Code: <span class="font-mono font-semibold text-gray-700">500</span>
                </p>
            </div>
        </div>
    </div>
</body>
</html>
