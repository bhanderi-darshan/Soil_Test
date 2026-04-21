<?php
session_start();
include "db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'] ?? 'Farmer';

// Determine current season
$month = (int)date('m');
$current_season = 'Kharif'; // Default
if ($month >= 3 && $month <= 5) $current_season = 'Summer';
elseif ($month >= 6 && $month <= 9) $current_season = 'Kharif';
else $current_season = 'Rabi';

// Handle form submission
if (isset($_POST['save_crops'])) {
    $district = mysqli_real_escape_string($conn, $_POST['district']);
    $taluka = mysqli_real_escape_string($conn, $_POST['taluka']);
    $hardware_id = mysqli_real_escape_string($conn, $_POST['hardware_id']);
    $selected_crops = isset($_POST['crops']) ? $_POST['crops'] : [];
    
    if (!empty($selected_crops) && !empty($district) && !empty($taluka) && !empty($hardware_id)) {
        // Save hardware ID to user profile
        mysqli_query($conn, "UPDATE users SET hardware_id = '$hardware_id' WHERE id = $user_id");

        $crop_list = mysqli_real_escape_string($conn, implode(',', $selected_crops));
        
        $check = mysqli_query($conn, "SELECT id FROM farmer_preferences WHERE user_id = $user_id");
        if (mysqli_num_rows($check) > 0) {
            mysqli_query($conn, "UPDATE farmer_preferences SET preferred_crops = '$crop_list', district = '$district', taluka = '$taluka' WHERE user_id = $user_id");
        } else {
            mysqli_query($conn, "INSERT INTO farmer_preferences (user_id, preferred_crops, district, taluka) VALUES ($user_id, '$crop_list', '$district', '$taluka')");
        }
        
        $_SESSION['preferences_set'] = true;
        header("Location: soil_select.php");
        exit();
    } else {
        $error = "Please fill all location fields and select at least one crop.";
    }
}

// Fetch all mapping data for JS
$mapping_result = mysqli_query($conn, "SELECT * FROM location_crop_mapping");
$mappings = [];
while ($row = mysqli_fetch_assoc($mapping_result)) {
    $mappings[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Location & Crops - SmartSoil Gujarat</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #1a2e1c;
            --accent: #2e7d32;
            --accent-light: #e8f5e9;
            --text-main: #334155;
            --text-muted: #64748b;
            --bg-page: #f8fafc;
            --border: #e2e8f0;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            background: var(--bg-page); 
            color: var(--text-main); 
            font-family: 'Inter', sans-serif;
            -webkit-font-smoothing: antialiased;
            background: radial-gradient(circle at top right, #e8f5e9, transparent),
                        radial-gradient(circle at bottom left, #f0fdf4, transparent);
            background-attachment: fixed;
            min-height: 100vh;
        }

        /* PREMIUM TOP BAR */
        .top-bar {
            position: sticky;
            top: 0;
            z-index: 1000;
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--border);
            padding: 12px 40px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: var(--shadow-sm);
        }

        .top-left h1 {
            font-family: 'Outfit', sans-serif;
            font-size: 1.4rem;
            font-weight: 800;
            color: var(--primary);
            letter-spacing: -0.5px;
        }

        .top-left span {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: var(--accent);
            font-weight: 700;
            display: block;
            margin-bottom: -2px;
        }

        .location-settings {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .setting-fg {
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 6px 14px;
            display: flex;
            flex-direction: column;
            min-width: 160px;
            transition: all 0.2s;
        }

        .setting-fg:focus-within {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(46, 125, 50, 0.1);
        }

        .setting-fg label {
            font-size: 0.65rem;
            font-weight: 800;
            text-transform: uppercase;
            color: var(--text-muted);
            margin-bottom: 2px;
        }

        .setting-fg select, .setting-fg input {
            border: none;
            outline: none;
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--primary);
            background: transparent;
            width: 100%;
            cursor: pointer;
        }

        .logout-btn {
            color: #ef4444; 
            font-size: 0.85rem; 
            font-weight: 700; 
            text-decoration: none;
            padding: 8px 16px;
            border-radius: 8px;
            transition: 0.2s;
        }
        .logout-btn:hover { background: #fee2e2; }

        /* MAIN CONTENT */
        .main-content {
            max-width: 1440px;
            margin: 0 auto;
            padding: 40px;
        }

        .category-section {
            margin-bottom: 60px;
        }

        .category-header {
            display: flex;
            align-items: baseline;
            gap: 12px;
            margin-bottom: 24px;
        }

        .category-header h2 {
            font-family: 'Outfit', sans-serif;
            font-size: 2rem;
            font-weight: 800;
            color: var(--primary);
        }

        .category-header .badge {
            font-size: 0.85rem;
            color: var(--text-muted);
            font-weight: 500;
        }

        .crop-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 24px;
        }

        .crop-card {
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 20px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .crop-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
            border-color: var(--accent);
        }

        .crop-card img {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 16px;
            margin-bottom: 16px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }

        .crop-card h3 {
            font-family: 'Outfit', sans-serif;
            font-size: 1rem;
            font-weight: 700;
            color: var(--primary);
        }

        .crop-card.active::before {
            content: 'SEASONAL';
            position: absolute;
            top: 12px;
            right: 12px;
            background: var(--accent);
            color: #fff;
            font-size: 0.6rem;
            font-weight: 900;
            padding: 2px 8px;
            border-radius: 20px;
            z-index: 10;
        }

        .crop-card.selected {
            background: var(--accent-light);
            border-color: var(--accent);
            border-width: 2px;
        }

        .crop-card.selected::after {
            content: '✓';
            position: absolute;
            top: -8px;
            right: -8px;
            background: var(--accent);
            color: #fff;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            border: 2px solid #fff;
            box-shadow: var(--shadow-md);
        }

        /* ACTIONS BAR */
        .actions-bar {
            position: fixed;
            bottom: 32px;
            left: 50%;
            transform: translateX(-50%);
            background: #1a2e1c;
            color: #fff;
            padding: 12px 32px;
            border-radius: 50px;
            display: flex;
            align-items: center;
            gap: 32px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.2);
            z-index: 2000;
        }

        .actions-bar .stats {
            display: flex;
            flex-direction: column;
        }

        .actions-bar .stats .count {
            font-size: 1.2rem;
            font-weight: 800;
            font-family: 'Outfit', sans-serif;
        }

        .actions-bar .stats .lbl {
            font-size: 0.65rem;
            text-transform: uppercase;
            font-weight: 700;
            opacity: 0.7;
            margin-top: -4px;
        }

        .btn-save {
            background: #fff;
            color: var(--primary);
            border: none;
            padding: 10px 28px;
            border-radius: 50px;
            font-weight: 800;
            font-family: 'Outfit', sans-serif;
            font-size: 0.95rem;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-save:hover {
            background: var(--accent-light);
            transform: scale(1.05);
        }

        .btn-save:disabled {
            background: #4a5568;
            color: #8a99af;
            cursor: not-allowed;
        }

        .error {
            background: #fee2e2;
            color: #b91c1c;
            padding: 12px 20px;
            border-radius: 12px;
            margin-bottom: 24px;
            font-weight: 600;
            font-size: 0.9rem;
            border: 1px solid #fecaca;
        }
    </style>
</head>
<body>

<div class="dashboard">
    <div class="top-bar">
        <div class="top-left">
            <span>Onboarding Wizard</span>
            <h1>Crop Intelligence</h1>
        </div>
        <form method="POST" id="mainForm" class="location-settings">
            <div class="setting-fg">
                <label>District</label>
                <select id="district" name="district" onchange="updateTalukas()">
                    <option value="">Select District</option>
                </select>
            </div>
            <div class="setting-fg">
                <label>Taluka</label>
                <select id="taluka" name="taluka" onchange="filterCrops()">
                    <option value="">Select Taluka</option>
                </select>
            </div>
            <div class="setting-fg">
                <label>Hardware ID</label>
                <input type="text" name="hardware_id" placeholder="e.g. ESP32_001" required>
            </div>
            <a href="logout.php" class="logout-btn">Logout &times;</a>
        </form>
    </div>

    <div class="main-content">
        <?php if (isset($error)): ?> <div class="error"><?php echo $error; ?></div> <?php endif; ?>

        <?php 
        $crop_categories = [
            '🌧️ Kharif Crops' => [
                ['id' => 'Cotton', 'img' => 'https://images.unsplash.com/photo-1594904351111-a072f80b1a71?auto=format&fit=crop&w=300&q=80'],
                ['id' => 'Groundnut', 'img' => 'https://images.unsplash.com/photo-1563821734614-727918d374fb?auto=format&fit=crop&w=300&q=80'],
                ['id' => 'Bajra', 'img' => 'https://images.unsplash.com/photo-1628164010534-754e17ea6588?auto=format&fit=crop&w=300&q=80'],
                ['id' => 'Maize', 'img' => 'https://images.unsplash.com/photo-1551754655-cd27e38d2a3e?auto=format&fit=crop&w=300&q=80'],
                ['id' => 'Rice', 'img' => 'https://images.unsplash.com/photo-1586201375761-83865001e31c?auto=format&fit=crop&w=300&q=80'],
                ['id' => 'Soybean', 'img' => 'https://images.unsplash.com/photo-1594611624608-aa750808246d?auto=format&fit=crop&w=300&q=80'],
                ['id' => 'Jowar', 'img' => 'https://images.unsplash.com/photo-1628164010534-754e17ea6588?auto=format&fit=crop&w=300&q=80'],
                ['id' => 'Sesamum', 'img' => 'https://images.unsplash.com/photo-1587334274328-64186a80aeee?auto=format&fit=crop&w=300&q=80'],
                ['id' => 'Castor', 'img' => 'https://images.unsplash.com/photo-1634125895782-951ee51a66fd?auto=format&fit=crop&w=300&q=80'],
                ['id' => 'Tur', 'img' => 'https://images.unsplash.com/photo-1585032226651-759b368d7246?auto=format&fit=crop&w=300&q=80'],
                ['id' => 'Moong', 'img' => 'https://images.unsplash.com/photo-1505253758473-96b7015fcd40?auto=format&fit=crop&w=300&q=80'],
                ['id' => 'Urad', 'img' => 'https://images.unsplash.com/photo-1599839619522-397514119094?auto=format&fit=crop&w=300&q=80'],
                ['id' => 'Guar', 'img' => 'https://images.unsplash.com/photo-1518133910546-b6c2fb7d79e3?auto=format&fit=crop&w=300&q=80'],
                ['id' => 'Chilli', 'img' => 'https://images.unsplash.com/photo-1588253508112-9c9861298497?auto=format&fit=crop&w=300&q=80']
            ],
            '❄️ Rabi Crops' => [
                ['id' => 'Wheat', 'img' => 'https://images.unsplash.com/photo-1501430654243-c93f867907ed?auto=format&fit=crop&w=300&q=80'],
                ['id' => 'Cumin', 'img' => 'https://images.unsplash.com/photo-1534422298379-3e35ae58f331?auto=format&fit=crop&w=300&q=80'],
                ['id' => 'Fennel', 'img' => 'https://images.unsplash.com/photo-1530263673322-a964f40d4f40?auto=format&fit=crop&w=300&q=80'],
                ['id' => 'Mustard', 'img' => 'https://images.unsplash.com/photo-1508748010834-39655685dfb9?auto=format&fit=crop&w=300&q=80'],
                ['id' => 'Gram', 'img' => 'https://images.unsplash.com/photo-1515544832961-29fd833034aa?auto=format&fit=crop&w=300&q=80'],
                ['id' => 'Garlic', 'img' => 'https://images.unsplash.com/photo-1540148426945-6cf22a6b2383?auto=format&fit=crop&w=300&q=80'],
                ['id' => 'Onion', 'img' => 'https://images.unsplash.com/photo-1508747703725-71977713d540?auto=format&fit=crop&w=300&q=80'],
                ['id' => 'Coriander', 'img' => 'https://images.unsplash.com/photo-1589315830021-f79b1695de0c?auto=format&fit=crop&w=300&q=80']
            ],
            '🥭 Horticulture (Fruits)' => [
                ['id' => 'Mango', 'img' => 'https://images.unsplash.com/photo-1553279768-865429fa0078?auto=format&fit=crop&w=300&q=80'],
                ['id' => 'Banana', 'img' => 'https://images.unsplash.com/photo-1571771894821-ad9902412746?auto=format&fit=crop&w=300&q=80'],
                ['id' => 'Papaya', 'img' => 'https://images.unsplash.com/photo-1526609132717-3abc3665492d?auto=format&fit=crop&w=300&q=80'],
                ['id' => 'Pomegranate', 'img' => 'https://images.unsplash.com/photo-1541344999736-83eca872f241?auto=format&fit=crop&w=300&q=80'],
                ['id' => 'Chikoo', 'img' => 'https://images.unsplash.com/photo-1622350796853-912f71f6bed3?auto=format&fit=crop&w=300&q=80'],
                ['id' => 'Guava', 'img' => 'https://images.unsplash.com/photo-1534073133331-c4b62aede5e9?auto=format&fit=crop&w=300&q=80'],
                ['id' => 'Coconut', 'img' => 'https://images.unsplash.com/photo-1543362906-acfc16c67564?auto=format&fit=crop&w=300&q=80']
            ],
            '🥦 Essential Vegetables' => [
                ['id' => 'Potato', 'img' => 'https://images.unsplash.com/photo-1518977676601-b53f02ac10dd?auto=format&fit=crop&w=300&q=80'],
                ['id' => 'Tomato', 'img' => 'https://images.unsplash.com/photo-1518977676601-b53f02ac10dd?auto=format&fit=crop&w=300&q=80'],
                ['id' => 'Brinjal', 'img' => 'https://images.unsplash.com/photo-1615484477778-ca3b779401d5?auto=format&fit=crop&w=300&q=80'],
                ['id' => 'Okra', 'img' => 'https://images.unsplash.com/photo-1449339043519-7d3a95baf50e?auto=format&fit=crop&w=300&q=80'],
                ['id' => 'Cabbage', 'img' => 'https://images.unsplash.com/photo-1591189863430-ab87e120f312?auto=format&fit=crop&w=300&q=80']
            ],
            '🌾 Fodder & Others' => [
                ['id' => 'Lucerne', 'img' => 'https://images.unsplash.com/photo-1515694346937-94d85e41e6f0?auto=format&fit=crop&w=300&q=80'],
                ['id' => 'Sugarcane', 'img' => 'https://images.unsplash.com/photo-1593113616828-6f22bca04804?auto=format&fit=crop&w=300&q=80'],
                ['id' => 'Tobacco', 'img' => 'https://images.unsplash.com/photo-1536640112957-671297c88ae0?auto=format&fit=crop&w=300&q=80']
            ]
        ];

        foreach($crop_categories as $catName => $crops): ?>
        <div class="category-section">
            <div class="category-header">
                <h2><?php echo $catName; ?></h2>
                <span class="badge"><?php echo count($crops); ?> varieties</span>
            </div>
            <div class="crop-grid" id="cropGrid">
                <?php foreach($crops as $c): ?>
                <div class="crop-card" id="card_<?php echo $c['id']; ?>" onclick="toggleCrop('<?php echo $c['id']; ?>', this)">
                    <img src="<?php echo $c['img']; ?>" alt="<?php echo $c['id']; ?>" loading="lazy">
                    <h3><?php echo $c['id']; ?></h3>
                    <input type="checkbox" name="crops[]" value="<?php echo $c['id']; ?>" id="check_<?php echo $c['id']; ?>" style="display:none">
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Actions Bar -->
    <div class="actions-bar">
        <div class="stats">
            <span class="count" id="count">0</span>
            <span class="lbl">Crops Selected</span>
        </div>
        <div class="stats">
            <span class="count" style="font-size: 0.9rem;"><?php echo $current_season; ?></span>
            <span class="lbl">Active Season</span>
        </div>
        <button type="submit" name="save_crops" id="saveBtn" class="btn-save" disabled form="mainForm">
            Save &amp; Continue &rarr;
        </button>
    </div>
</div>

<script>
const mappings = <?php echo json_encode($mappings); ?>;
const currentSeason = "<?php echo $current_season; ?>";

const gujaratData = [
  { district: "Ahmedabad", region: "Central", talukas: ["Ahmedabad City", "Daskroi", "Dholka", "Dhandhuka", "Sanand", "Viramgam", "Bavla", "Mandal", "Detroj-Rampura"] },
  { district: "Amreli", region: "Saurashtra", talukas: ["Amreli", "Babra", "Bagasara", "Dhari", "Jafarabad", "Khamba", "Kunkavav-Vadia", "Lathi", "Lilia", "Rajula", "Savarkundla"] },
  { district: "Anand", region: "Central", talukas: ["Anand", "Anklav", "Borsad", "Khambhat", "Petlad", "Sojitra", "Tarapur", "Umreth"] },
  { district: "Aravalli", region: "North", talukas: ["Bayad", "Bhiloda", "Dhansura", "Malpur", "Meghraj", "Modasa"] },
  { district: "Banaskantha", region: "North", talukas: ["Amirgadh", "Bhabhar", "Danta", "Dantiwada", "Deesa", "Deodar", "Kankrej", "Lakhani", "Palanpur", "Suigam", "Tharad", "Vadgam", "Vav"] },
  { district: "Bharuch", region: "South", talukas: ["Amod", "Ankleshwar", "Bharuch", "Hansot", "Jambusar", "Jhagadia", "Netrang", "Vagra", "Valia"] },
  { district: "Bhavnagar", region: "Saurashtra", talukas: ["Bhavnagar", "Gariadhar", "Ghogha", "Jesar", "Mahuva", "Palitana", "Shihor", "Sihor", "Talaja", "Umrala", "Vallabhipur"] },
  { district: "Botad", region: "Saurashtra", talukas: ["Barwala", "Botad", "Gadhada", "Ranpur"] },
  { district: "Chhota Udaipur", region: "Central", talukas: ["Bodeli", "Chhota Udaipur", "Jetpur Pavi", "Kawant", "Nasvadi", "Sankheda"] },
  { district: "Dahod", region: "Central", talukas: ["Dahod", "Devgadh Baria", "Dhanpur", "Fatepura", "Garbada", "Jhalod", "Limkheda", "Sanjeli"] },
  { district: "Dang", region: "South", talukas: ["Ahwa"] },
  { district: "Devbhumi Dwarka", region: "Saurashtra", talukas: ["Bhanvad", "Dwarka", "Jhadeshwar", "Kalyanpur", "Khambhalia"] },
  { district: "Gandhinagar", region: "North", talukas: ["Dahegam", "Gandhinagar", "Kalol", "Mansa"] },
  { district: "Gir Somnath", region: "Saurashtra", talukas: ["Kodinar", "Sutrapada", "Talala", "Una", "Veraval"] },
  { district: "Jamnagar", region: "Saurashtra", talukas: ["Dhrol", "Jamnagar", "Jodiya", "Kalavad", "Lalpur", "Okhamandal"] },
  { district: "Junagadh", region: "Saurashtra", talukas: ["Bhesan", "Junagadh", "Keshod", "Mangrol", "Manavadar", "Mendarda", "Vanthali", "Visavadar"] },
  { district: "Kheda", region: "Central", talukas: ["Kapadvanj", "Kathlal", "Kheda", "Matar", "Mehmedabad", "Nadiad", "Thasra", "Vaso"] },
  { district: "Kutch", region: "Kutch", talukas: ["Abdasa", "Anjar", "Bhachau", "Bhuj", "Dayapar", "Gandhidham", "Lakhpat", "Mandvi", "Mundra", "Nakhatrana", "Rapar"] },
  { district: "Mahisagar", region: "Central", talukas: ["Balasinor", "Kadana", "Khanpur", "Lunawada", "Santrampur", "Virpur"] },
  { district: "Mehsana", region: "North", talukas: ["Becharaji", "Kadi", "Kheralu", "Mehsana", "Satlasana", "Sidhpur", "Unjha", "Vadnagar", "Vijapur", "Visnagar"] },
  { district: "Morbi", region: "Saurashtra", talukas: ["Halvad", "Maliya Miyana", "Morbi", "Tankara", "Wankaner"] },
  { district: "Narmada", region: "South", talukas: ["Dediapada", "Garudeshwar", "Nandod", "Sagbara", "Tilakwada"] },
  { district: "Navsari", region: "South", talukas: ["Chikhli", "Gandevi", "Jalalpore", "Khergam", "Navsari", "Vansda"] },
  { district: "Panchmahal", region: "Central", talukas: ["Godhra", "Ghoghamba", "Halol", "Jambughoda", "Kalol", "Morwa Hadaf", "Shehera"] },
  { district: "Patan", region: "North", talukas: ["Chanasma", "Harij", "Patan", "Radhanpur", "Santalpur", "Saraswati", "Sami", "Sidhpur"] },
  { district: "Porbandar", region: "Saurashtra", talukas: ["Kutiyana", "Porbandar", "Ranavav"] },
  { district: "Rajkot", region: "Saurashtra", talukas: ["Dhoraji", "Gondal", "Jamkandorna", "Jasdan", "Jetpur", "Kotda Sangani", "Lodhika", "Paddhari", "Rajkot", "Upleta", "Vinchhiya"] },
  { district: "Sabarkantha", region: "North", talukas: ["Himatnagar", "Idar", "Khedbrahma", "Meghraj", "Prantij", "Talod", "Vadali", "Vijaynagar"] },
  { district: "Surat", region: "South", talukas: ["Bardoli", "Chorasi", "Kamrej", "Mandvi", "Mangrol", "Olpad", "Palsana", "Surat City", "Umarpada"] },
  { district: "Surendranagar", region: "Saurashtra", talukas: ["Chuda", "Chotila", "Dasada", "Dhrangadhra", "Halvad", "Lakhtar", "Limbdi", "Muli", "Sayla", "Thangadh", "Wadhwan"] },
  { district: "Tapi", region: "South", talukas: ["Dolvan", "Kukarmunda", "Nizar", "Songadh", "Uchchhal", "Valod", "Vyara"] },
  { district: "Vadodara", region: "Central", talukas: ["Dabhoi", "Karjan", "Padra", "Savli", "Shinor", "Vadodara", "Waghodiya"] },
  { district: "Valsad", region: "South", talukas: ["Dharampur", "Kaprada", "Pardi", "Umbergaon", "Valsad"] },
  { district: "Vav-Tharad", region: "North", talukas: ["Vav", "Tharad", "Bhabhar", "Suigam"] },
];

function initDistricts() {
    const distSelect = document.getElementById('district');
    gujaratData.forEach(item => {
        distSelect.innerHTML += `<option value="${item.district}">${item.district}</option>`;
    });
}

function updateTalukas() {
    const dist = document.getElementById('district').value;
    const talSelect = document.getElementById('taluka');
    talSelect.innerHTML = '<option value="">Select Taluka</option>';
    
    const districtData = gujaratData.find(item => item.district === dist);
    if (districtData) {
        districtData.talukas.forEach(t => {
            talSelect.innerHTML += `<option value="${t}">${t}</option>`;
        });
    }
    filterCrops();
}

function filterCrops() {
    const dist = document.getElementById('district').value;
    const tal = document.getElementById('taluka').value;
    const cards = document.querySelectorAll('.crop-card');
    const noCrops = document.getElementById('noCrops');

    // Reset active states (seasonal hints)
    cards.forEach(c => c.classList.remove('active'));
    noCrops.style.display = 'none';

    if (!dist || !tal) return;

    // Filter mappings: Case-insensitive check just in case, and Summer/Zaid mapping
    const seasonToMatch = currentSeason;
    const available = mappings.filter(m => 
        m.district_name.toLowerCase() === dist.toLowerCase() && 
        m.taluka_name.toLowerCase() === tal.toLowerCase() && 
        m.season.toLowerCase() === seasonToMatch.toLowerCase()
    );
    
    if (available.length > 0) {
        available.forEach(m => {
            const card = document.getElementById('card_' + m.crop_name);
            if (card) card.classList.add('active');
        });
    } else {
        // Fallback or message if needed, but we keep selection open
    }
}

function toggleCrop(id, card) {
    const check = document.getElementById('check_' + id);
    check.checked = !check.checked;
    card.classList.toggle('selected');
    updateUI();
}

function updateUI() {
    const count = document.querySelectorAll('input[name="crops[]"]:checked').length;
    document.getElementById('count').innerText = count;
    document.getElementById('saveBtn').disabled = count === 0;
}

// Initial state
window.onload = function() {
    initDistricts();
    filterCrops();
};
</script>

</body>
</html>
