<?php
require_once 'db.php';
$db = new Database();

// Check database connection
$dbConnected = $db->isConnected();

// Check if we're editing an existing mosque
$mosque_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$editing_mosque = null;

if ($dbConnected && $mosque_id) {
    // Fetch mosque details if editing and database is connected
    $stmt = $db->conn->prepare("SELECT * FROM mosques WHERE id = ?");
    $stmt->execute([$mosque_id]);
    $editing_mosque = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $editing_mosque ? 'Edit' : 'Add'; ?> Mosque - Prayer Timings</title>
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
                    <a href="index.php" class="flex items-center">
                        <i class="fas fa-mosque text-green-600 text-3xl mr-3"></i>
                        <h1 class="text-2xl font-semibold text-gray-800">Mosque Prayer Timings</h1>
                    </a>
                </div>
                <a href="index.php" class="text-gray-600 hover:text-gray-800">
                    <i class="fas fa-arrow-left mr-2"></i>Back to List
                </a>
            </div>
        </nav>
    </header>

    <!-- Main Content -->
    <main class="container mx-auto px-4 py-8">
        <?php if (!$dbConnected): ?>
            <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 mb-8" role="alert">
                <p class="font-bold">System Notice</p>
                <p>The mosque database is currently undergoing maintenance. Submissions are temporarily disabled. Please check back later.</p>
                <p class="mt-2">
                    <a href="index.php" class="text-yellow-700 underline hover:text-yellow-800">Return to homepage</a>
                </p>
            </div>
        <?php else: ?>
            <div class="max-w-3xl mx-auto">
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-2xl font-semibold mb-6">
                        <?php echo $editing_mosque ? 'Edit Mosque Details' : 'Add New Mosque'; ?>
                    </h2>

                    <?php if (isset($_SESSION['error_message'])): ?>
                        <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
                            <?php echo htmlspecialchars($_SESSION['error_message']); ?>
                            <?php unset($_SESSION['error_message']); ?>
                        </div>
                    <?php endif; ?>

                    <form id="mosqueForm" action="process_mosque.php" method="POST" class="space-y-6">
                        <?php if ($editing_mosque): ?>
                            <input type="hidden" name="mosque_id" value="<?php echo $editing_mosque['id']; ?>">
                            <input type="hidden" name="submission_type" value="revision">
                        <?php else: ?>
                            <input type="hidden" name="submission_type" value="new">
                        <?php endif; ?>

                        <!-- Mosque Details -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="col-span-2">
                                <label class="block text-sm font-medium text-gray-700">Mosque Name</label>
                                <input type="text" name="name" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
                                    value="<?php echo $editing_mosque ? htmlspecialchars($editing_mosque['name']) : ''; ?>">
                            </div>

                            <div class="col-span-2">
                                <label class="block text-sm font-medium text-gray-700">Address</label>
                                <textarea name="address" required rows="3"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
                                ><?php echo $editing_mosque ? htmlspecialchars($editing_mosque['address']) : ''; ?></textarea>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Latitude</label>
                                <input type="number" name="latitude" id="latitude" required step="any"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
                                    value="<?php echo $editing_mosque ? $editing_mosque['latitude'] : ''; ?>">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Longitude</label>
                                <input type="number" name="longitude" id="longitude" required step="any"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
                                    value="<?php echo $editing_mosque ? $editing_mosque['longitude'] : ''; ?>">
                            </div>

                            <div class="col-span-2">
                                <button type="button" id="getLocation" 
                                    class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                    <i class="fas fa-location-arrow mr-2"></i>
                                    Get Current Location
                                </button>
                            </div>
                        </div>

                        <!-- Prayer Timings -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Fajar</label>
                                <input type="time" name="fajar" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
                                    value="<?php echo $editing_mosque ? $editing_mosque['fajar'] : ''; ?>">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Zuhar</label>
                                <input type="time" name="zuhar" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
                                    value="<?php echo $editing_mosque ? $editing_mosque['zuhar'] : ''; ?>">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Asar</label>
                                <input type="time" name="asar" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
                                    value="<?php echo $editing_mosque ? $editing_mosque['asar'] : ''; ?>">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Maghrib</label>
                                <input type="time" name="maghrib" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
                                    value="<?php echo $editing_mosque ? $editing_mosque['maghrib'] : ''; ?>">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Ishaa</label>
                                <input type="time" name="ishaa" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
                                    value="<?php echo $editing_mosque ? $editing_mosque['ishaa'] : ''; ?>">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Juma</label>
                                <input type="time" name="juma" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
                                    value="<?php echo $editing_mosque ? $editing_mosque['juma'] : ''; ?>">
                            </div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="flex justify-between items-center pt-4">
                            <button type="submit" class="inline-flex items-center px-6 py-3 border border-transparent rounded-md shadow-sm text-base font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                <?php if ($editing_mosque): ?>
                                    <i class="fas fa-save mr-2"></i>Submit Revision
                                <?php else: ?>
                                    <i class="fas fa-plus mr-2"></i>Add Mosque
                                <?php endif; ?>
                            </button>

                            <?php if ($editing_mosque): ?>
                                <button type="button" onclick="deleteMosque(<?php echo $editing_mosque['id']; ?>)" 
                                    class="inline-flex items-center px-6 py-3 border border-transparent rounded-md shadow-sm text-base font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                    <i class="fas fa-trash-alt mr-2"></i>Delete
                                </button>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t mt-12">
        <div class="container mx-auto px-4 py-6">
            <p class="text-center text-gray-600">© 2024 Mosque Prayer Timings. All rights reserved.</p>
        </div>
    </footer>

    <script>
        document.getElementById('getLocation')?.addEventListener('click', function() {
            if ("geolocation" in navigator) {
                navigator.geolocation.getCurrentPosition(function(position) {
                    document.getElementById('latitude').value = position.coords.latitude;
                    document.getElementById('longitude').value = position.coords.longitude;
                }, function(error) {
                    alert('Error getting location: ' + error.message);
                });
            } else {
                alert('Geolocation is not supported by your browser');
            }
        });

        function deleteMosque(mosqueId) {
            if (confirm('Are you sure you want to request deletion of this mosque?')) {
                const form = document.getElementById('mosqueForm');
                form.action = 'process_mosque.php?action=delete';
                form.submit();
            }
        }
    </script>
</body>
</html>
