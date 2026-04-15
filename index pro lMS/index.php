<?php
session_start();

// Validasi Bahasa (ID/EN)
if (isset($_GET['lang']) && in_array($_GET['lang'], ['id', 'en'])) {
    $_SESSION['lang'] = $_GET['lang'];
}

$lang = isset($_SESSION['lang']) ? $_SESSION['lang'] : 'id';

// KAMUS BAHASA
$i18n = [
    'id' => [
        'title' => 'PRO2LMS | Pembelajaran Masa Depan Berbasis AI',
        'nav_home' => 'Beranda',
        'nav_explore' => 'Eksplorasi',
        'nav_science' => 'Sains & AI',
        'nav_login' => 'Masuk',
        'nav_signup' => 'Mulai Gratis',
        'badge_hero' => 'LMS Generasi Baru',
        'hero_h1_1' => 'Belajar Tanpa Batas.',
        'hero_h1_2' => 'Bebaskan Pikiran.',
        'hero_p' => 'Hadirkan pengalaman kelas berstandar internasional yang menenangkan. Dilengkapi AI Mentor interaktif yang memahami gaya belajarmu, 24/7.',
        'hero_btn_start' => 'Mulai Perjalananmu',
        'hero_btn_demo' => 'Tonton Demo',
        'hero_trust' => 'Bergabung dengan 10.000+ mahasiswa global',
        'float_psy_title' => 'Psikologi Kognitif',
        'float_psy_desc' => 'Mereduksi Stres',
        'float_ai_title' => 'AI Mentor Aktif',
        'float_ai_desc' => 'Menganalisis progress...',
        'feat_h2' => 'Standar Kelas Dunia.',
        'feat_p' => 'Dirancang untuk mengeliminasi \'cognitive overload\'. Antarmuka luas, bersih, dan menenangkan agar otak siap menyerap ilmu komputasi tingkat tinggi.',
        'feat_1_title' => 'Psikologi Antarmuka Positif',
        'feat_1_desc' => 'Menggunakan komposisi warna light mode dengan studi kontras tinggi yang terbukti menurunkan rasa cemas. Tampilan rapi membuat mahasiswa lebih fokus.',
        'feat_2_title' => 'Personal AI Tutor',
        'feat_2_desc' => 'Deteksi otomatis gaya belajarmu. AI memberikan feedback real-time saat kamu terhenti di materi yang sulit.',
        'feat_3_title' => 'Interactive Runtime',
        'feat_3_desc' => 'Matematika & Fisika tidak lagi abstrak. Ubah rumus dan saksikan perubahannya langsung di browser.',
        'feat_4_title' => 'World-Class Metrik & Framework',
        'feat_4_desc' => 'Sistem analitik cerdas yang mengadopsi standar universitas global. Mendukung penuh metode Problem-Based Learning (PBL) dan ekosistem kampus modern.',
        'badge_comp' => 'Python di Browser',
        'comp_h2_1' => 'Simulasi Sains',
        'comp_h2_2' => 'Secara Langsung.',
        'comp_p' => 'Lupakan perpindahan aplikasi. PRO2LMS mengintegrasikan \'computing sandbox\' ke dalam setiap ruang materi. Eksperimen adalah cara terbaik belajar inovasi.',
        'comp_li_1' => 'Eksekusi script Matplotlib atau Plotly instan.',
        'comp_li_2' => 'AI Mentor menganalisis error kodemu.',
        'comp_li_3' => 'Tanpa perlu instalasi software di laptop.',
        'comp_term' => 'Berhasil',
        'cta_h2' => 'Raih Potensimu yang Sebenarnya',
        'cta_p' => 'Singkirkan platform yang membosankan. Saatnya beralih ke LMS masa depan yang menumbuhkan rasa ingin tahu dan kenyamanan psikologis.',
        'cta_btn' => 'Bergabung Sekarang Secara Gratis',
        'footer_desc' => 'Merevolusi EdTech dengan AI & Desain Psikologi Mahasiswa.',
        'footer_copy' => '&copy; 2026 PRO2LMS. Hak Cipta Dilindungi.',
    ],
    'en' => [
        'title' => 'PRO2LMS | AI-Powered Future of Learning',
        'nav_home' => 'Home',
        'nav_explore' => 'Explore',
        'nav_science' => 'Science & AI',
        'nav_login' => 'Log In',
        'nav_signup' => 'Start for Free',
        'badge_hero' => 'Next-Gen LMS',
        'hero_h1_1' => 'Learn Boundlessly.',
        'hero_h1_2' => 'Free Your Mind.',
        'hero_p' => 'Experience a soothing, international-standard classroom environment. Equipped with an interactive AI Mentor that understands your learning style, 24/7.',
        'hero_btn_start' => 'Start Your Journey',
        'hero_btn_demo' => 'Watch Demo',
        'hero_trust' => 'Join 10,000+ global students',
        'float_psy_title' => 'Cognitive Psychology',
        'float_psy_desc' => 'Stress Reduction',
        'float_ai_title' => 'Active AI Mentor',
        'float_ai_desc' => 'Analyzing progress...',
        'feat_h2' => 'World-Class Standards.',
        'feat_p' => 'Designed to eliminate cognitive overload. A spacious, clean, and soothing interface prepares the brain to absorb advanced computational knowledge.',
        'feat_1_title' => 'Positive Interface Psychology',
        'feat_1_desc' => 'Uses a light mode color composition based on high-contrast studies proven to reduce anxiety. A pristine layout keeps students focused.',
        'feat_2_title' => 'Personal AI Tutor',
        'feat_2_desc' => 'Automatically detects your learning style. Our AI provides real-time feedback whenever you get stuck on difficult material.',
        'feat_3_title' => 'Interactive Runtime',
        'feat_3_desc' => 'Math & Physics are no longer abstract. Tweak formulas and witness the changes rendered directly in your browser.',
        'feat_4_title' => 'World-Class Metrics & Framework',
        'feat_4_desc' => 'An intelligent analytics system adopting global university standards. Fully supports Problem-Based Learning (PBL) and modern campus ecosystems.',
        'badge_comp' => 'Python In Browser',
        'comp_h2_1' => 'Live Science',
        'comp_h2_2' => 'Simulation.',
        'comp_p' => 'Forget app switching. PRO2LMS integrates a computing sandbox into every learning module. Experimentation is the best way to learn innovation.',
        'comp_li_1' => 'Instantly execute Matplotlib or Plotly scripts.',
        'comp_li_2' => 'AI Mentor analyzes your code errors.',
        'comp_li_3' => 'No software installation required on your laptop.',
        'comp_term' => 'Success',
        'cta_h2' => 'Unleash Your True Potential',
        'cta_p' => 'Ditch the boring platforms. It\'s time to switch to the LMS of the future that fosters curiosity and psychological comfort.',
        'cta_btn' => 'Join Now For Free',
        'footer_desc' => 'Revolutionizing EdTech with AI & Student Psychology Design.',
        'footer_copy' => '&copy; 2026 PRO2LMS. All rights reserved.',
    ]
];

$t = $i18n[$lang];
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $t['title'] ?></title>
    
    <!-- Modern Font Imports -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Fira+Code:wght@400;500&display=swap" rel="stylesheet">
    
    <!-- Icon Font -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- External CSS (Separated for better performance & maintainability) -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

    <!-- Ambient Background -->
    <div class="ambient-bg">
        <div class="orb orb-1"></div>
        <div class="orb orb-2"></div>
        <div class="orb orb-3"></div>
    </div>

    <!-- NAVBAR -->
    <nav id="navbar">
        <a href="#" class="nav-brand">
            <i class="fa-solid fa-graduation-cap"></i>
            PRO2LMS
        </a>
        <ul class="nav-links">
            <li><a href="#beranda"><?= $t['nav_home'] ?></a></li>
            <li><a href="#fitur"><?= $t['nav_explore'] ?></a></li>
            <li><a href="#komputasi"><?= $t['nav_science'] ?></a></li>
            
            <!-- Language Switcher -->
            <li class="lang-switch">
                <a href="?lang=id" class="<?= $lang == 'id' ? 'active' : '' ?>">ID</a>
                <span style="color:var(--text-muted)">|</span>
                <a href="?lang=en" class="<?= $lang == 'en' ? 'active' : '' ?>">EN</a>
            </li>

            <li class="mobile-only-actions">
                <a href="#login" class="btn btn-outline"><?= $t['nav_login'] ?></a>
                <a href="#daftar" class="btn btn-primary"><?= $t['nav_signup'] ?></a>
            </li>
        </ul>
        <div class="nav-actions">
            <a href="#login" class="btn btn-outline" style="padding: 0.6rem 1.5rem;"><?= $t['nav_login'] ?></a>
            <a href="#daftar" class="btn btn-primary" style="padding: 0.6rem 1.5rem;"><?= $t['nav_signup'] ?></a>
        </div>
        <button class="mobile-menu-btn" aria-label="Toggle Menu"><i class="fa-solid fa-bars"></i></button>
    </nav>

    <!-- HERO SECTION -->
    <section id="beranda" class="hero">
        <div class="hero-content animate-fade-up">
            <div class="badge">
                <i class="fa-solid fa-sparkles"></i>
                <?= $t['badge_hero'] ?>
            </div>
            <h1><?= $t['hero_h1_1'] ?><br><span class="text-gradient"><?= $t['hero_h1_2'] ?></span></h1>
            <p><?= $t['hero_p'] ?></p>
            <div class="hero-actions">
                <a href="#mulai" class="btn btn-primary"><?= $t['hero_btn_start'] ?></a>
                <a href="#demo" class="btn btn-outline"><i class="fa-solid fa-play"></i> <?= $t['hero_btn_demo'] ?></a>
            </div>
            
            <div class="trusted-by">
                <div class="avatars">
                    <!-- Placeholder avatars for social proof -->
                    <img src="https://ui-avatars.com/api/?name=Alex&background=random" alt="User Alex">
                    <img src="https://ui-avatars.com/api/?name=Sarah&background=random" alt="User Sarah">
                    <img src="https://ui-avatars.com/api/?name=John&background=random" alt="User John">
                </div>
                <span><?= $t['hero_trust'] ?></span>
            </div>
        </div>

        <div class="hero-visual animate-fade-up delay-2">
            
            <div class="hero-image-wrapper">
                <!-- Using the generated AI premium image -->
                <img src="hero_image.png" alt="Modern LMS Dashboard UI">
            </div>

            <!-- Floating visual tags -->
            <div class="glass-card-float gcf-1">
                <div style="background: rgba(236, 72, 153, 0.1); padding: 12px; border-radius: 50%; color: var(--color-accent);">
                    <i class="fa-solid fa-brain" style="font-size: 1.5rem;"></i>
                </div>
                <div>
                    <h5 style="margin:0; font-size: 0.95rem;"><?= $t['float_psy_title'] ?></h5>
                    <p style="margin:0; font-size: 0.8rem; color: var(--text-muted);"><?= $t['float_psy_desc'] ?></p>
                </div>
            </div>

            <div class="glass-card-float gcf-2">
                <h5 style="margin:0; font-size: 0.95rem; color: var(--color-primary);"><?= $t['float_ai_title'] ?></h5>
                <div style="width: 100px; height: 6px; background: #EEF2FF; border-radius: 4px; overflow:hidden;">
                    <div style="width: 70%; height: 100%; background: linear-gradient(90deg, var(--color-primary), var(--color-secondary)); border-radius: 4px;"></div>
                </div>
                <p style="margin:0; font-size: 0.75rem; color: var(--text-muted);"><?= $t['float_ai_desc'] ?></p>
            </div>

        </div>
    </section>

    <!-- FEATURES BENTO GRID SECTION -->
    <section id="fitur" class="features">
        <div class="section-header animate-fade-up">
            <h2 class="text-gradient"><?= $t['feat_h2'] ?></h2>
            <p style="font-size: 1.2rem; color: var(--text-body);"><?= $t['feat_p'] ?></p>
        </div>

        <div class="bento-grid">
            
            <!-- Card 1: Large -->
            <div class="bento-card bento-large animate-fade-up delay-1">
                <div class="bento-icon ic-purple">
                    <i class="fa-solid fa-face-smile-beam"></i>
                </div>
                <h3><?= $t['feat_1_title'] ?></h3>
                <p><?= $t['feat_1_desc'] ?></p>
                
                <div class="bento-visual">
                    <!-- Abstract UI simulation -->
                    <div class="b-shape" style="top:20px; left:20px; width:60%; height:20px; background:#E0E7FF;"></div>
                    <div class="b-shape" style="top:55px; left:20px; width:80%; height:40px; background: white; border-radius: 12px;"></div>
                    <div class="b-shape" style="top:110px; left:20px; width:40%; height:15px; background: #C7D2FE;"></div>
                </div>
            </div>

            <!-- Card 2: Standard -->
            <div class="bento-card animate-fade-up delay-2">
                <div class="bento-icon ic-green">
                    <i class="fa-solid fa-microchip"></i>
                </div>
                <h3><?= $t['feat_2_title'] ?></h3>
                <p><?= $t['feat_2_desc'] ?></p>
            </div>

            <!-- Card 3: Standard -->
            <div class="bento-card animate-fade-up delay-3">
                <div class="bento-icon ic-pink">
                    <i class="fa-brands fa-python"></i>
                </div>
                <h3><?= $t['feat_3_title'] ?></h3>
                <p><?= $t['feat_3_desc'] ?></p>
            </div>

            <!-- Card 4: Wide -->
            <div class="bento-card bento-wide animate-fade-up delay-2">
                <div class="bento-icon ic-blue">
                    <i class="fa-solid fa-globe"></i>
                </div>
                <h3><?= $t['feat_4_title'] ?></h3>
                <p><?= $t['feat_4_desc'] ?></p>
            </div>

        </div>
    </section>

    <!-- INTERACTIVE COMPUTING SECTION (DARK IDE) -->
    <section id="komputasi" style="padding: 0;">
        <div class="computing-section">
            <div class="comp-content animate-fade-up">
                <div class="badge" style="background: rgba(236, 72, 153, 0.15); color: #F472B6; border: none;">
                    <i class="fa-solid fa-code"></i> <?= $t['badge_comp'] ?>
                </div>
                <h2><?= $t['comp_h2_1'] ?><br><?= $t['comp_h2_2'] ?></h2>
                <p><?= $t['comp_p'] ?></p>
                
                <ul class="feature-list-dark">
                    <li><i class="fa-solid fa-check-circle"></i> <?= $t['comp_li_1'] ?></li>
                    <li><i class="fa-solid fa-check-circle"></i> <?= $t['comp_li_2'] ?></li>
                    <li><i class="fa-solid fa-check-circle"></i> <?= $t['comp_li_3'] ?></li>
                </ul>
            </div>

            <div class="comp-visual animate-fade-up delay-2">
                <div class="code-window">
                    <div class="window-header">
                        <div class="mac-btn cb-red"></div>
                        <div class="mac-btn cb-yel"></div>
                        <div class="mac-btn cb-grn"></div>
                        <span class="window-title">simulasi_gelombang.py</span>
                        <i class="fa-solid fa-copy" style="color: #64748B; font-size: 0.8rem; cursor:pointer;" aria-label="Copy Code"></i>
                    </div>
                    <div class="code-content">
                        <span class="tok-cm"># Visualisasi Elektromagnetik PRO2LMS</span><br>
                        <span class="tok-kw">import</span> numpy <span class="tok-kw">as</span> np<br>
                        <span class="tok-kw">import</span> matplotlib.pyplot <span class="tok-kw">as</span> plt<br>
                        <br>
                        t <span class="tok-op">=</span> np.<span class="tok-fn">linspace</span>(<span class="tok-str">0</span>, <span class="tok-str">10</span>, <span class="tok-str">200</span>)<br>
                        amplitudo <span class="tok-op">=</span> <span class="tok-str">5.0</span><br>
                        frekuensi <span class="tok-op">=</span> <span class="tok-str">2.5</span><br>
                        <br>
                        <span class="tok-cm"># Coba ubah nilai frekuensi!</span><br>
                        y <span class="tok-op">=</span> amplitudo <span class="tok-op">*</span> np.<span class="tok-fn">sin</span>(<span class="tok-str">2</span> <span class="tok-op">*</span> np.pi <span class="tok-op">*</span> frekuensi <span class="tok-op">*</span> t)<br>
                        <br>
                        plt.<span class="tok-fn">plot</span>(t, y, color=<span class="tok-str">"#EC4899"</span>)<br>
                        plt.<span class="tok-fn">show</span>()
                    </div>
                </div>

                <div class="data-viz-dark">
                    <div style="font-size: 0.8rem; color: #94A3B8; margin-bottom: 0.8rem; font-weight: 600; display:flex; justify-content:space-between; align-items: center;">
                        <span>Terminal Output</span>
                        <div style="background: rgba(16, 185, 129, 0.2); color: #34D399; padding: 2px 8px; border-radius: 4px; font-size: 0.7rem;"><?= $t['comp_term'] ?></div>
                    </div>
                    <div class="wave-container"></div>
                </div>
            </div>
        </div>
    </section>

    <!-- CALL TO ACTION -->
    <section>
        <div class="cta-section animate-fade-up">
            <div class="cta-shape"></div>
            <div class="cta-content">
                <h2><?= $t['cta_h2'] ?></h2>
                <p><?= $t['cta_p'] ?></p>
                <a href="#daftar" class="btn btn-white" style="font-size: 1.1rem; padding: 1.25rem 3rem;"><?= $t['cta_btn'] ?></a>
            </div>
        </div>
    </section>

    <!-- FOOTER -->
    <footer>
        <div class="footer-brand">
            <i class="fa-solid fa-graduation-cap" style="color: var(--color-primary);"></i>
            PRO2LMS
        </div>
        <p><?= $t['footer_desc'] ?></p>
        <p style="margin-top: 1rem; color: var(--text-muted); font-size: 0.9rem;"><?= $t['footer_copy'] ?></p>
    </footer>

    <!-- External JS -->
    <script src="assets/js/main.js"></script>
</body>
</html>
