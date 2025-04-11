<?php
require_once 'db.php';
$db = new Database();

// Check database connection
$dbConnected = $db->isConnected();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mosque Prayer Timings</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Header -->
    <header class="bg-white shadow-md">
        <nav class="container mx-auto px-4 py-4">
            <div class="flex justify-between items-center">
                <div class="flex items-center">
                    <i class="fas fa-mosque text-green-600 text-3xl mr-3"></i>
                    <h1 class="text-2xl font-semibold text-gray-800">Mosque Prayer Timings</h1>
                </div>
                <div class="space-x-4">
                    <a href="add_mosque.php" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition">
                        <i class="fas fa-plus mr-2"></i>Add Mosque
                    </a>
                    <a href="admin/login.php" class="text-gray-600 hover:text-gray-800">
                        <i class="fas fa-user-shield mr-2"></i>Admin
                    </a>
                </div>
            </div>
        </nav>
    </header>

    <!-- Hero Banner -->
    <div class="relative h-64 bg-cover bg-center" style="background-image: url('https://images.pexels.com/photos/2869318/pexels-photo-2869318.jpeg');">
        <div class="absolute inset-0 bg-black bg-opacity-50"></div>
        <div class="absolute inset-0 flex items-center justify-center">
            <div class="text-center text-white">
                <h2 class="text-3xl md:text-4xl font-bold mb-4">Find Mosques Near You</h2>
                <p class="text-lg md:text-xl">Accurate prayer timings for mosques in your area</p>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <main class="container mx-auto px-4 py-8">
        <?php if (!$dbConnected): ?>
            <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 mb-8" role="alert">
                <p class="font-bold">System Notice</p>
                <p>The mosque database is currently undergoing maintenance. Some features may be temporarily unavailable. Please check back later.</p>
            </div>
        <?php endif; ?>

        <!-- Location Status -->
        <div id="locationStatus" class="mb-8 text-center">
            <div class="animate-spin inline-block w-8 h-8 border-4 border-green-600 border-t-transparent rounded-full"></div>
            <p class="text-gray-600 mt-2">Detecting your location...</p>
        </div>

        <!-- Mosques Table -->
        <div class="overflow-x-auto bg-white rounded-lg shadow">
            <table class="min-w-full table-auto">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mosque Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fajar</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Zuhar</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Asar</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Maghrib</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ishaa</th>
                    </tr>
                </thead>
                <tbody id="mosquesTableBody" class="bg-white divide-y divide-gray-200">
                    <!-- Table rows will be dynamically populated -->
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="6" class="px-6 py-4 bg-gray-50">
                            <div class="text-center">
                                <span class="font-medium text-gray-500">Juma Prayer</span>
                                <span id="jumaTime" class="ml-2 text-gray-900"></span>
                            </div>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t mt-12">
        <div class="container mx-auto px-4 py-6">
            <p class="text-center text-gray-600">© 2024 Mosque Prayer Timings. All rights reserved.</p>
        </div>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const locationStatus = document.getElementById('locationStatus');
            const mosquesTableBody = document.getElementById('mosquesTableBody');

            function showError(message) {
                locationStatus.innerHTML = `
                    <div class="text-red-600">
                        <i class="fas fa-exclamation-circle text-2xl"></i>
                        <p class="mt-2">${message}</p>
                    </div>
                `;
            }

            function formatTime(timeStr) {
                return new Date('2000-01-01T' + timeStr).toLocaleTimeString([], {
                    hour: '2-digit',
                    minute: '2-digit'
                });
            }

            function loadMosques(position) {
                const { latitude, longitude } = position.coords;
                
                fetch(`get_mosques.php?lat=${latitude}&lng=${longitude}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.error) {
                            showError(data.error);
                            return;
                        }

                        if (data.length === 0) {
                            locationStatus.innerHTML = `
                                <div class="text-gray-600">
                                    <i class="fas fa-info-circle text-2xl"></i>
                                    <p class="mt-2">No mosques found in your area.</p>
                                    <p class="mt-2">
                                        <a href="add_mosque.php" class="text-green-600 hover:text-green-700">
                                            <i class="fas fa-plus-circle mr-1"></i>Add a mosque
                                        </a>
                                    </p>
                                </div>
                            `;
                            return;
                        }

                        locationStatus.style.display = 'none';
                        
                        mosquesTableBody.innerHTML = data.map(mosque => `
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">${mosque.name}</div>
                                            <div class="text-sm text-gray-500">${mosque.address}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${formatTime(mosque.fajar)}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${formatTime(mosque.zuhar)}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${formatTime(mosque.asar)}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${formatTime(mosque.maghrib)}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${formatTime(mosque.ishaa)}</td>
                            </tr>
                        `).join('');

                        // Update Juma time (using first mosque's time as reference)
                        if (data.length > 0) {
                            document.getElementById('jumaTime').textContent = formatTime(data[0].juma);
                        }
                    })
                    .catch(error => {
                        showError('Error connecting to the server. Please try again later.');
                        console.error('Error:', error);
                    });
            }

            // Request location permission
            if ("geolocation" in navigator) {
                navigator.geolocation.getCurrentPosition(
                    loadMosques,
                    (error) => {
                        let errorMessage = 'Error detecting location. ';
                        switch(error.code) {
                            case error.PERMISSION_DENIED:
                                errorMessage += 'Please enable location services to find nearby mosques.';
                                break;
                            case error.POSITION_UNAVAILABLE:
                                errorMessage += 'Location information is unavailable.';
                                break;
                            case error.TIMEOUT:
                                errorMessage += 'Location request timed out.';
                                break;
                            default:
                                errorMessage += 'An unknown error occurred.';
                        }
                        showError(errorMessage);
                    },
                    {
                        enableHighAccuracy: true,
                        timeout: 5000,
                        maximumAge: 0
                    }
                );
            } else {
                showError('Geolocation is not supported by your browser.');
            }
        });
    </script>
</body>
</html>
